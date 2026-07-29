from pathlib import Path


source_path = Path("/Users/ashekurrahman/Documents/PAIM/build_brd.py")
source = source_path.read_text(encoding="utf-8")

markers = {
    "phase16": "# 16 delivery and rollout",
    "testing": "# 14 Testing",
    "ux": "# 8 UX and responsive UI",
    "use_cases": "# 7 Detailed use cases",
    "business": "# 1-3 Business framing",
    "control": "# Document control and contents",
}
positions = {name: source.index(marker) for name, marker in markers.items()}

prefix = source[:positions["phase16"]]
control = source[positions["control"]:]
business = source[positions["business"]:positions["control"]]
use_cases = source[positions["use_cases"]:positions["business"]]
ux_to_reporting = source[positions["ux"]:positions["use_cases"]]
testing_traceability = source[positions["testing"]:positions["ux"]]
delivery_appendices_and_save = source[positions["phase16"]:positions["testing"]]

ordered_source = "\n".join([
    prefix,
    control,
    business,
    use_cases,
    ux_to_reporting,
    testing_traceability,
    delivery_appendices_and_save,
])

exec(compile(ordered_source, str(source_path), "exec"), {"__name__": "__main__"})
