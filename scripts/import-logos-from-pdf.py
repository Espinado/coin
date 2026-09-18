#!/usr/bin/env python3
"""Extract CloudFlops logo variants from source PDF and remove solid backgrounds."""

from __future__ import annotations

import argparse
from pathlib import Path

import cv2
import numpy as np
import pymupdf

ROOT = Path(__file__).resolve().parents[1]
OUT_DIR = ROOT / "public" / "cloudflops"
DEFAULT_PDF = Path.home() / "Desktop" / "Logo Mix.pdf"

VARIANTS: dict[str, pymupdf.Rect] = {
    "logo-horizontal.png": pymupdf.Rect(0, 0, 1080, 496),
    "logo-stacked.png": pymupdf.Rect(0, 496, 540, 1080),
    "logo-mark.png": pymupdf.Rect(540, 496, 1080, 1080),
}

SCALE = 3
BG_TOLERANCE = 34


def remove_solid_bg(image: np.ndarray, tol: int = BG_TOLERANCE) -> np.ndarray:
    if image.shape[2] == 3:
        image = cv2.cvtColor(image, cv2.COLOR_BGR2BGRA)

    height, width = image.shape[:2]
    bgr = image[:, :, :3].copy()
    background = np.zeros((height, width), dtype=bool)
    lo = (tol, tol, tol)
    hi = (tol, tol, tol)
    flags = 4 | cv2.FLOODFILL_FIXED_RANGE | cv2.FLOODFILL_MASK_ONLY | (255 << 8)

    for x, y in ((0, 0), (width - 1, 0), (0, height - 1), (width - 1, height - 1)):
        mask = np.zeros((height + 2, width + 2), np.uint8)
        cv2.floodFill(bgr, mask, (x, y), (0, 0, 0), lo, hi, flags)
        background |= mask[1:-1, 1:-1] != 0

    image[background, 3] = 0
    return image


def trim_transparent(image: np.ndarray, pad: int = 8) -> np.ndarray:
    alpha = image[:, :, 3]
    coords = cv2.findNonZero(alpha)
    if coords is None:
        return image

    x, y, w, h = cv2.boundingRect(coords)
    x0 = max(0, x - pad)
    y0 = max(0, y - pad)
    x1 = min(image.shape[1], x + w + pad)
    y1 = min(image.shape[0], y + h + pad)
    return image[y0:y1, x0:x1]


def render_variant(page: pymupdf.Page, clip: pymupdf.Rect, scale: int) -> np.ndarray:
    matrix = pymupdf.Matrix(scale, scale)
    pix = page.get_pixmap(matrix=matrix, clip=clip, alpha=False)
    data = np.frombuffer(pix.samples, dtype=np.uint8).reshape(pix.height, pix.width, pix.n)
    if pix.n == 4:
        image = cv2.cvtColor(data, cv2.COLOR_RGBA2BGRA)
    else:
        image = cv2.cvtColor(data, cv2.COLOR_RGB2BGRA)
    return image


def export_logos(pdf_path: Path, out_dir: Path) -> None:
    out_dir.mkdir(parents=True, exist_ok=True)
    doc = pymupdf.open(str(pdf_path))
    page = doc[0]

    for filename, clip in VARIANTS.items():
        rendered = render_variant(page, clip, SCALE)
        transparent = remove_solid_bg(rendered)
        trimmed = trim_transparent(transparent)
        target = out_dir / filename
        backup = target.with_suffix(target.suffix + ".bak")
        if target.exists() and not backup.exists():
            target.replace(backup)
        cv2.imwrite(str(target), trimmed)
        print(f"{filename}: {trimmed.shape[1]}x{trimmed.shape[0]} -> {target}")

    horizontal = out_dir / "logo-horizontal.png"
    logo = out_dir / "logo.png"
    if horizontal.exists():
        logo.write_bytes(horizontal.read_bytes())
        print(f"logo.png: copied from {horizontal.name}")

    doc.close()


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("pdf", nargs="?", default=str(DEFAULT_PDF), help="Source PDF path")
    parser.add_argument("--out", default=str(OUT_DIR), help="Output directory")
    args = parser.parse_args()

    pdf_path = Path(args.pdf).expanduser()
    if not pdf_path.exists():
        raise SystemExit(f"PDF not found: {pdf_path}")

    export_logos(pdf_path, Path(args.out))


if __name__ == "__main__":
    main()
