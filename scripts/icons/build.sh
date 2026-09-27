#!/usr/bin/env bash
# Rasterizes resources/images/logo.svg into the PNG/ICO icons in public/ (needs Chromium + Pillow).
set -euo pipefail
cd "$(dirname "$0")/../.."
CHROME="${CHROME:-/opt/pw-browsers/chromium-1194/chrome-linux/chrome}"
TMP="$(mktemp -d)"

render() { # size, background, padding%, output
  cat > "$TMP/icon.html" <<HTML
<!doctype html><html><body style="margin:0;background:$2">
<div style="width:$1px;height:$1px;display:grid;place-items:center">
<img src="file://$PWD/resources/images/logo.svg" style="width:calc(100% - $3%);height:calc(100% - $3%)"></div></body></html>
HTML
  "$CHROME" --headless=new --no-sandbox --disable-gpu --hide-scrollbars --default-background-color=00000000 \
    --window-size="$1,$1" --screenshot="$4" "file://$TMP/icon.html" >/dev/null 2>&1
  echo "$4"
}

render 512 '#FFF6E8' 12 public/images/logo-512.png
render 180 '#FFF6E8' 16 public/images/apple-touch-icon.png
render 64 'transparent' 0 "$TMP/fav-64.png"
python3 -c "from PIL import Image; Image.open('$TMP/fav-64.png').save('public/favicon.ico', sizes=[(16,16),(32,32),(48,48)])"
echo public/favicon.ico
rm -rf "$TMP"
