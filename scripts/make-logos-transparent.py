#!/usr/bin/env python3
"""Remove baked-in solid backgrounds from CloudFlops logo PNGs."""

from __future__ import annotations

from pathlib import Path

import cv2
import numpy as np

ROOT = Path(__file__).resolve().parents[1] / "public" / "cloudflops"
FILES = [
    "logo-horizontal.png",
    "logo-mark.png",
    "logo-stacked.png",
    "logo.png",
]


def remove_solid_bg(path: Path, tol: int = 28) -> None:
    image = cv2.imread(str(path), cv2.IMREAD_UNCHANGED)
    if image is None:
        raise RuntimeError(f"Unable to read {path}")

    if image.shape[2] == 3:
        image = cv2.cvtColor(image, cv2.COLOR_BGR2BGRA)

    height, width = image.shape[:2]
    bgr = image[:, :, :3].copy()
    bg = bgr[0, 0].astype(np.int16)
    background = np.zeros((height, width), dtype=bool)
    lo = (tol, tol, tol)
    hi = (tol, tol, tol)
    flags = 4 | cv2.FLOODFILL_FIXED_RANGE | cv2.FLOODFILL_MASK_ONLY | (255 << 8)

    for x, y in ((0, 0), (width - 1, 0), (0, height - 1), (width - 1, height - 1)):
        pixel = bgr[y, x].astype(np.int16)
        if np.all(np.abs(pixel - bg) <= tol):
            mask = np.zeros((height + 2, width + 2), np.uint8)
            cv2.floodFill(bgr, mask, (x, y), (0, 0, 0), lo, hi, flags)
            background |= mask[1:-1, 1:-1] != 0

    image[background, 3] = 0

    backup = path.with_suffix(path.suffix + ".bak")
    if not backup.exists():
        path.replace(backup)
    else:
        path.unlink()

    cv2.imwrite(str(path), image)
    print(f"{path.name}: bg={tuple(int(v) for v in bg)}, saved (backup: {backup.name})")


def main() -> None:
    for name in FILES:
        path = ROOT / name
        if path.exists():
            remove_solid_bg(path)


if __name__ == "__main__":
    main()
