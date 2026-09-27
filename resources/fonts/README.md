# Fonts

All fonts are licensed under the SIL Open Font License 1.1 (see `src/OFL-*.txt`) and were downloaded from Google Fonts.

| Family | Use | Weights |
|---|---|---|
| Changa | headings | 700, 800 |
| IBM Plex Sans Arabic | body / UI | 400, 500, 600 |
| Handjet | LCD numerals only (digits, `°`, space) | 500 |

`src/` holds the full TTFs (also used by the OG image generator). `web/` holds the woff2 subsets served to browsers;
rebuild them with `scripts/fonts/subset.sh` (needs `pip install fonttools brotli`).
