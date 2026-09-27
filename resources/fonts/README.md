# Fonts

All fonts are licensed under the SIL Open Font License 1.1 (see `src/OFL-*.txt`, `src/LICENSE-OpenSans.txt`)
and were downloaded from Google Fonts.

| Family | Use | Served to browsers |
|---|---|---|
| Rubik 500/700 | headings, nav, numbers (the existing site's heading font) | yes, Latin/digits subset |
| Open Sans 400/600 | body Latin text and digits (the existing site's body font) | yes, Latin/digits subset |
| Noto Sans Arabic 600/700 | Arabic text in generated OG images (GD needs a TTF) | no |

Arabic text on the site uses the platform's Arabic system font, exactly like the existing site (Rubik and Open
Sans have no Arabic glyphs). Rebuild the subsets with `scripts/fonts/subset.sh` (`pip install fonttools brotli`).
