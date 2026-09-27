#!/usr/bin/env bash
# Builds the self-hosted woff2 subsets in resources/fonts/web from the OFL source TTFs.
# Requires: pip install fonttools brotli
set -euo pipefail
cd "$(dirname "$0")/../../resources/fonts"

# Arabic + basic Latin + punctuation used in copy (Western digits only).
TEXT_RANGES="U+0020-007E,U+00A0,U+00AB,U+00B0,U+00BB,U+00D7,U+0600-06FF,U+0750-077F,U+08A0-08FF,U+200C-200F,U+2010-2014,U+2018-201E,U+2022,U+2026,U+2066-2069,U+FD3E-FD3F"
# Handjet is only used for LCD numerals: digits, degree sign and space.
LCD_RANGES="U+0020,U+0030-0039,U+00B0"

subset() {
  pyftsubset "src/$1.ttf" --unicodes="$2" --flavor=woff2 --layout-features='*' \
    --no-hinting --desubroutinize --output-file="web/$3.woff2"
  echo "web/$3.woff2 $(wc -c < "web/$3.woff2") bytes"
}

mkdir -p web
subset Changa-Bold "$TEXT_RANGES" changa-700
subset Changa-ExtraBold "$TEXT_RANGES" changa-800
subset IBMPlexSansArabic-Regular "$TEXT_RANGES" plex-arabic-400
subset IBMPlexSansArabic-Medium "$TEXT_RANGES" plex-arabic-500
subset IBMPlexSansArabic-SemiBold "$TEXT_RANGES" plex-arabic-600
subset Handjet-Medium "$LCD_RANGES" handjet-lcd

# IBM Plex has the Reserved Font Name "Plex": a subset is a modified version under the OFL,
# so the subsets are renamed (copyright and license notices are kept).
python3 - <<'PY'
from fontTools.ttLib import TTFont
for weight in ("400", "500", "600"):
    path = f"web/plex-arabic-{weight}.woff2"
    font = TTFont(path)
    for record in font["name"].names:
        if record.nameID in (1, 3, 4, 6, 16, 17):
            value = "NakheelBody-" + weight if record.nameID in (3, 6) else "Nakheel Body " + weight
            record.string = value
    font.flavor = "woff2"
    font.save(path)
    print(f"{path} renamed")
PY
