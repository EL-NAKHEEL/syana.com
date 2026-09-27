#!/usr/bin/env bash
# Builds the self-hosted woff2 subsets in resources/fonts/web from the OFL source TTFs.
# Requires: pip install fonttools brotli
#
# The design (the existing site) uses Rubik for headings and Open Sans for body text. Neither has Arabic
# glyphs, so Arabic text uses the platform's Arabic system font exactly as on the existing site; these
# subsets only carry Latin letters, Western digits and punctuation (phone numbers, prices, model numbers).
set -euo pipefail
cd "$(dirname "$0")/../../resources/fonts"

LATIN="U+0020-007E,U+00A0,U+00AB,U+00B0,U+00BB,U+00D7,U+2013-2014,U+2018-201E,U+2022,U+2026"

subset() {
  pyftsubset "src/$1.ttf" --unicodes="$LATIN" --flavor=woff2 --layout-features='kern,liga,lnum,tnum' \
    --no-hinting --desubroutinize --output-file="web/$2.woff2"
  echo "web/$2.woff2 $(wc -c < "web/$2.woff2") bytes"
}

mkdir -p web
subset Rubik-Medium rubik-500
subset Rubik-Bold rubik-700
subset OpenSans-Regular open-sans-400
subset OpenSans-SemiBold open-sans-600
