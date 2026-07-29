# PAIM Deployment Guide  Cloud Run + Cloud SQL

This document describes how the PAIM (AI Subscription Control) Laravel application is deployed on Google Cloud, using a low-cost serverless architecture: **Cloud Run** for compute and **Cloud SQL (MySQL)** for the database.

## Architecture Overview

- **Compute**: Cloud Run (fully managed, scales to zero, pay-per-request)
- **Database**: Cloud SQL for MySQL 8.0, tier `db-f1-micro`, connected via Unix socket (Cloud SQL Auth Proxy built into Cloud Run)
- **Container Registry**: Artifact Registry (Docker format)
- **Build**: Cloud Build (builds image from source, pushes to Artifact Registry)
- **Secrets**: Secret Manager (DB password, Laravel APP_KEY)
- **Access**: Public (unauthenticated) via an org policy exception; Cloud Run IAM invoker role granted to allUsers

## Prerequisites

- A GCP project with billing enabled
- `gcloud` CLI authenticated (Cloud Shell is pre-authenticated)
- Owner or Editor role on the project
- (Optional, only if you need public access) Organization Policy Administrator role, if your org enforces Domain Restricted Sharing

## Resources Created

| Resource | Name | Notes |
|---|---|---|
| Cloud SQL instance | `paim-mysql` | MySQL 8.0, db-f1-micro, us-central1, HDD, zonal (no HA) |
| Database | `paim_db` | |
| DB user | `paim_user` | password stored in Secret Manager |
| Secret | `paim-db-password` | DB user password |
| Secret | `paim-app-key` | Laravel APP_KEY |
| Artifact Registry repo | `paim-repo` | us-central1, Docker format |
| Cloud Run service | `paim-app` | us-central1, min-instances=0, max-instances=3, 512Mi |

## Step-by-Step Setup

### 1. Enable required APIs

    gcloud services enable run.googleapis.com sqladmin.googleapis.com \
      artifactregistry.googleapis.com cloudbuild.googleapis.com \
      secretmanager.googleapis.com compute.googleapis.com

### 2. Clone the app source

    git clone https://github.com/Gonokjontro/paim.git
    cd paim

### 3. Create the Cloud SQL instance and database

    gcloud sql instances create paim-mysql \
      --database-version=MYSQL_8_0 --tier=db-f1-micro \
      --region=us-central1 --storage-size=10 --storage-type=HDD \
      --availability-type=ZONAL --no-backup

    gcloud sql databases create paim_db --instance=paim-mysql

### 4. Create secrets (DB password and Laravel APP_KEY)

    echo -n "<random-strong-password>" | gcloud secrets create paim-db-password --data-file=-
    gcloud sql users create paim_user --instance=paim-mysql --password="<same-password>"

    echo -n "base64:<32-random-bytes>" | gcloud secrets create paim-app-key --data-file=-

### 5. Create the Artifact Registry repository

    gcloud artifacts repositories create paim-repo \
      --repository-format=docker --location=us-central1

### 6. Add a Dockerfile (multi-stage: Composer build, then php-apache runtime)

Key points learned the hard way:
- Do **not** pass `--no-autoloader` to `composer install` unless you follow up with `composer dump-autoload`. Use `--optimize-autoloader` instead.
- Match the PHP base image version to what your dependencies actually require. This project's dependencies needed PHP 8.4 even though composer.json only declared `^8.3`  using `--ignore-platform-reqs` during composer install let newer packages slip in, and PHP 8.3 could not parse their syntax at runtime. Use `php:8.4-apache` as the base image.

### 7. Build and push the image via Cloud Build

    gcloud builds submit \
      --tag us-central1-docker.pkg.dev/PROJECT_ID/paim-repo/paim-app:latest .

If this fails with permission errors, grant the Cloud Build/compute default service account:

    gcloud projects add-iam-policy-binding PROJECT_ID \
      --member=serviceAccount:PROJECT_NUMBER-compute@developer.gserviceaccount.com \
      --role=roles/storage.objectViewer

    gcloud projects add-iam-policy-binding PROJECT_ID \
      --member=serviceAccount:PROJECT_NUMBER-compute@developer.gserviceaccount.com \
      --role=roles/artifactregistry.writer

### 8. Deploy to Cloud Run

    gcloud run deploy paim-app \
      --image=us-central1-docker.pkg.dev/PROJECT_ID/paim-repo/paim-app:latest \
      --region=us-central1 --platform=managed \
      --add-cloudsql-instances=PROJECT_ID:us-central1:paim-mysql \
      --set-env-vars="APP_ENV=production,APP_DEBUG=false,DB_CONNECTION=mysql,DB_DATABASE=paim_db,DB_USERNAME=paim_user,DB_SOCKET=/cloudsql/PROJECT_ID:us-central1:paim-mysql,LOG_CHANNEL=stderr" \
      --set-secrets="DB_PASSWORD=paim-db-password:latest,APP_KEY=paim-app-key:latest" \
      --min-instances=0 --max-instances=3 --memory=512Mi

Grant Secret Manager and Cloud SQL access to the runtime service account if deployment/runtime fails:

    gcloud projects add-iam-policy-binding PROJECT_ID \
      --member=serviceAccount:PROJECT_NUMBER-compute@developer.gserviceaccount.com \
      --role=roles/secretmanager.secretAccessor

    gcloud projects add-iam-policy-binding PROJECT_ID \
      --member=serviceAccount:PROJECT_NUMBER-compute@developer.gserviceaccount.com \
      --role=roles/cloudsql.client

The `roles/cloudsql.client` grant is essential  without it Cloud Run returns HTTP 500 with a `cloudsql.instances.get` permission error, even though everything else deploys successfully.

### 9. Run migrations and seed demo data

Cloud Run does not offer an interactive shell, so migrations are run from Cloud Shell via the Cloud SQL Auth Proxy:

    cloud-sql-proxy --port 3307 PROJECT_ID:us-central1:paim-mysql &

    DBPASS=$(gcloud secrets versions access latest --secret=paim-db-password)
    APPKEY=$(gcloud secrets versions access latest --secret=paim-app-key)

    docker run --rm --network host \
      -e APP_KEY="$APPKEY" -e DB_CONNECTION=mysql -e DB_HOST=127.0.0.1 -e DB_PORT=3307 \
      -e DB_DATABASE=paim_db -e DB_USERNAME=paim_user -e DB_PASSWORD="$DBPASS" \
      us-central1-docker.pkg.dev/PROJECT_ID/paim-repo/paim-app:latest \
      php artisan migrate --force

    # Seed demo users/data (admin@paim.ai, manager@paim.ai, viewer@paim.ai  password: "password")
    docker run --rm --network host \
      -e APP_KEY="$APPKEY" -e DB_CONNECTION=mysql -e DB_HOST=127.0.0.1 -e DB_PORT=3307 \
      -e DB_DATABASE=paim_db -e DB_USERNAME=paim_user -e DB_PASSWORD="$DBPASS" \
      us-central1-docker.pkg.dev/PROJECT_ID/paim-repo/paim-app:latest \
      php artisan db:seed --force

### 10. Make the app publicly accessible (optional)

Many organizations enforce a Domain Restricted Sharing org policy that blocks `allUsers` from being granted IAM roles. If `gcloud run services add-iam-policy-binding ... --member=allUsers` fails with `FAILED_PRECONDITION`, you need an org policy exception:

    # Requires roles/orgpolicy.policyAdmin at the org level
    cat > policy.yaml << 'EOF'
    name: projects/PROJECT_ID/policies/iam.allowedPolicyMemberDomains
    spec:
      rules:
      - allowAll: true
    EOF
    gcloud org-policies set-policy policy.yaml

    gcloud run services add-iam-policy-binding paim-app \
      --region=us-central1 --member=allUsers --role=roles/run.invoker

Note: policy changes can take up to a minute to propagate; retry if you see a permission error immediately after setting the policy.

### 11. Fix HTTPS proxy trust (important for forms/CSRF)

Cloud Run terminates TLS and forwards requests internally over HTTP. Laravel must be told to trust this proxy, or it will generate `http://` URLs for redirects and form actions, causing browsers to block form submissions ("Form is not secure") and CSRF/session issues. In `bootstrap/app.php`:

    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })

Rebuild and redeploy after this change.

## Troubleshooting Reference

| Symptom | Cause | Fix |
|---|---|---|
| Cloud Build fails: `storage.objects.get` denied | Compute SA lacks read access to source bucket | Grant `roles/storage.objectViewer` |
| Cloud Build fails: upload artifacts denied | Compute SA lacks Artifact Registry write | Grant `roles/artifactregistry.writer` |
| Cloud Run deploy fails: secret access denied | Compute SA lacks Secret Manager access | Grant `roles/secretmanager.secretAccessor` |
| HTTP 500, logs show `cloudsql.instances.get` denied | Compute SA lacks Cloud SQL Client role | Grant `roles/cloudsql.client` |
| `Failed opening required vendor/autoload.php` | `composer install --no-autoloader` with no follow-up dump-autoload | Remove the flag, use `--optimize-autoloader` |
| PHP syntax error in vendor code | Base PHP image older than what dependencies require | Match Dockerfile PHP version to actual requirements |
| `add-iam-policy-binding --member=allUsers` fails: `FAILED_PRECONDITION` | Org policy blocks public IAM bindings | Add project-level org policy exception (needs Org Policy Admin) |
| Browser blocks login form: "Form is not secure" / 419 error | Laravel doesn't trust Cloud Run's proxy, generates http:// URLs | Add `trustProxies(at: '*')` in bootstrap/app.php |

## Live Service

- URL: https://paim-app-4z376zbsfa-uc.a.run.app/
- Demo logins: admin@paim.ai / manager@paim.ai / viewer@paim.ai (password: `password`)

## Cost Notes

This setup was chosen to minimize cost: Cloud Run scales to zero when idle, Cloud SQL uses the smallest tier (`db-f1-micro`) with HDD storage and no HA. Suitable for demos/low-traffic use, not for production-grade availability.
