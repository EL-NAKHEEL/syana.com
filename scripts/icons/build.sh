#!/usr/bin/env bash
# Renders the brand icons in public/ from the site's design (orange block, white «النخيل كوول»).
# Needs Chromium (with an Arabic system font such as Noto Sans Arabic) and Pillow.
set -euo pipefail
cd "$(dirname "$0")/../.."
CHROME="${CHROME:-/opt/pw-browsers/chromium-1194/chrome-linux/chrome}"
TMP="$(mktemp -d)"

render() { # size, html body, output
  cat > "$TMP/icon.html" <<HTML
<!doctype html><html lang="ar"><body style="margin:0;direction:ltr">
<div dir="rtl" style="width:$1px;height:$1px;display:grid;place-items:center;background:#FF5E14;color:#fff;
font-family:'Noto Sans Arabic','Segoe UI',sans-serif;font-weight:700;text-align:center;line-height:1.05">$2</div></body></html>
HTML
  # Headless Chrome's viewport is shorter than --window-size: render larger, then crop exactly.
  "$CHROME" --headless=new --no-sandbox --disable-gpu --hide-scrollbars \
    --window-size="$(( $1 + 200 )),$(( $1 + 200 ))" --screenshot="$TMP/shot.png" "file://$TMP/icon.html" >/dev/null 2>&1
  python3 -c "from PIL import Image; Image.open('$TMP/shot.png').crop((0, 0, $1, $1)).save('$3')"
  echo "$3"
}

render 512 '<div style="font-size:150px">النخيل<br>كوول</div>' public/images/logo-512.png
render 180 '<div style="font-size:54px">النخيل<br>كوول</div>' public/images/apple-touch-icon.png
render 192 '<div style="font-size:150px;margin-top:-20px">ن</div>' public/images/icon-192.png
render 64 '<div style="font-size:54px;margin-top:-8px">ن</div>' "$TMP/fav-64.png"
python3 -c "from PIL import Image; Image.open('$TMP/fav-64.png').save('public/favicon.ico', sizes=[(16,16),(32,32),(48,48)])"
echo public/favicon.ico
rm -rf "$TMP"
