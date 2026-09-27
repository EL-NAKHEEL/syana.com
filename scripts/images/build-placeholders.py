#!/usr/bin/env python3
"""
Builds responsive AVIF/WebP/JPEG versions of the old site's stock photos, used as TEMPORARY placeholders
(listed in TODO.md) until real photos arrive. Output: public/images/placeholders/<name>-<width>.<ext>
plus resources/images/placeholders.json (name -> widths and intrinsic aspect) read by <x-picture>.
Requires Pillow with AVIF support.
"""
import json
import pathlib

from PIL import Image, ImageOps

ROOT = pathlib.Path(__file__).resolve().parents[2]
SRC = ROOT / "legacy/old-site/img"
OUT = ROOT / "public/images/placeholders"
MANIFEST = ROOT / "resources/images/placeholders.json"

# name: (source file, widths, crop aspect (w/h) or None)
IMAGES = {
    "split-ac-units-hot-and-cool-air": ("2306.q891.030.S.m004.c10.air conditioner split system realistic.jpg", [640, 1024, 1600, 1920], 16 / 9),
    "engineers-reviewing-site-plans": ("carousel-2.jpg", [768, 1366, 1920], 16 / 9),
    "technician-servicing-indoor-split-ac": ("full-shot-mean-cleaning-air.jpg", [320, 615], None),
    "technician-installing-outdoor-ac-unit": ("technician-working-air-conditioner.jpg", [320, 615], None),
    "technician-on-ladder-servicing-ac": ("hvac-technician-working-capacitor-part-condensing-unit.jpg", [480, 800, 1200], 2 / 3),
    "technician-checking-refrigerant-gauges": ("engineer-assembling-hvac-unit-manometers.jpg", [130, 260], 1),
    "technician-inspecting-condenser": ("expert-repairman-doing-condenser-investigations-filter-replacements-necessary-fixes-prevent-major-breakdowns-proficient-worker-checking-up-hvac-system-writing-findings-clipboard.jpg", [130, 260], 1),
    "technician-preparing-installation-tools": ("project-6.jpg", [130, 260], 1),
    "apartment-building-facade": ("wall-city-estate-background-office.jpg", [130, 260], 1),
    "indoor-ac-cleaning-square": ("full-shot-mean-cleaning-air.jpg", [130, 260], 1),
    "outdoor-unit-installation-square": ("technician-working-air-conditioner.jpg", [130, 260], 1),
    "technician-on-ladder-square": ("hvac-technician-working-capacitor-part-condensing-unit.jpg", [130, 260], 1),
}

QUALITY = {"avif": 50, "webp": 72, "jpg": 78}


def crop(img: Image.Image, aspect: float | None) -> Image.Image:
    if aspect is None:
        return img
    w, h = img.size
    return ImageOps.fit(img, (w, round(w / aspect)) if w / h < aspect else (round(h * aspect), h), centering=(0.5, 0.4))


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    manifest = {}
    for name, (source, widths, aspect) in IMAGES.items():
        img = crop(ImageOps.exif_transpose(Image.open(SRC / source)).convert("RGB"), aspect)
        ratio = img.height / img.width
        for width in widths:
            resized = img.resize((width, round(width * ratio)), Image.LANCZOS)
            resized.save(OUT / f"{name}-{width}.avif", quality=QUALITY["avif"])
            resized.save(OUT / f"{name}-{width}.webp", quality=QUALITY["webp"], method=6)
            resized.save(OUT / f"{name}-{width}.jpg", quality=QUALITY["jpg"], optimize=True, progressive=True)
        manifest[name] = {"widths": widths, "ratio": round(ratio, 4), "placeholder": True}
    MANIFEST.write_text(json.dumps(manifest, indent=2) + "\n")
    total = sum(p.stat().st_size for p in OUT.iterdir())
    print(f"{len(manifest)} images, {len(list(OUT.iterdir()))} files, {total / 1024:.0f} KB")


if __name__ == "__main__":
    main()
