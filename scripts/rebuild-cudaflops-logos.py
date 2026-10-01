#!/usr/bin/env python3
"""Rebuild horizontal/stacked logos with CudaFlops wordmark from existing mark."""

from __future__ import annotations

import shutil
from pathlib import Path

import numpy as np
from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parents[1]
OUT_DIR = ROOT / "public" / "cloudflops"
MARK_PATH = OUT_DIR / "logo-mark.png"
LANDING_DIR = ROOT / "public" / "coin" / "landing"

CYAN = (67, 228, 236)
WHITE = (255, 255, 255)
BG = (0, 0, 0)

FONT_CANDIDATES = [
    Path(r"C:\Windows\Fonts\segoeuib.ttf"),
    Path(r"C:\Windows\Fonts\arialbd.ttf"),
    Path(r"C:\Windows\Fonts\calibrib.ttf"),
]


def resolve_font() -> Path:
    for path in FONT_CANDIDATES:
        if path.exists():
            return path
    raise FileNotFoundError("No bold TrueType font found for wordmark")


def render_wordmark(font_path: Path, font_size: int) -> Image.Image:
    font = ImageFont.truetype(str(font_path), font_size)
    dummy = ImageDraw.Draw(Image.new("RGB", (1, 1)))
    full = dummy.textbbox((0, 0), "CudaFlops", font=font)
    pad = max(12, font_size // 6)
    width = full[2] - full[0] + pad * 2
    height = full[3] - full[1] + pad * 2
    rgb = Image.new("RGB", (width, height), BG)
    draw = ImageDraw.Draw(rgb)
    x, y = pad - full[0], pad - full[1]
    draw.text((x, y), "Cuda", font=font, fill=WHITE)
    cuda = dummy.textbbox((0, 0), "Cuda", font=font)
    draw.text((x + cuda[2] - cuda[0], y), "Flops", font=font, fill=CYAN)

    arr = np.array(rgb)
    lum = arr.max(axis=2)
    alpha = np.where(lum < 18, 0, 255).astype(np.uint8)
    mid = (lum >= 18) & (lum < 80)
    alpha[mid] = (lum[mid].astype(np.float32) / 80.0 * 255.0).astype(np.uint8)
    return Image.fromarray(np.dstack([arr, alpha]), "RGBA")


def compose_horizontal(mark: Image.Image, font_path: Path) -> Image.Image:
    word = render_wordmark(font_path, max(72, int(mark.height * 0.5)))
    target_h = max(int(mark.height * 0.58), word.height)
    scale = target_h / word.height
    word = word.resize((max(1, int(word.width * scale)), target_h), Image.Resampling.LANCZOS)
    gap = max(28, mark.width // 7)
    height = max(mark.height, word.height)
    width = mark.width + gap + word.width
    canvas = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    canvas.paste(mark, (0, (height - mark.height) // 2), mark)
    canvas.paste(word, (mark.width + gap, (height - word.height) // 2), word)
    pad = 28
    out = Image.new("RGBA", (width + pad * 2, height + pad * 2), (0, 0, 0, 0))
    out.paste(canvas, (pad, pad), canvas)
    return out


def compose_stacked(mark: Image.Image, font_path: Path) -> Image.Image:
    word = render_wordmark(font_path, max(64, int(mark.width * 0.26)))
    target_w = int(mark.width * 1.2)
    scale = target_w / word.width
    word = word.resize((target_w, max(1, int(word.height * scale))), Image.Resampling.LANCZOS)
    gap = max(24, mark.height // 9)
    width = max(mark.width, word.width)
    height = mark.height + gap + word.height
    canvas = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    canvas.paste(mark, ((width - mark.width) // 2, 0), mark)
    canvas.paste(word, ((width - word.width) // 2, mark.height + gap), word)
    pad = 28
    out = Image.new("RGBA", (width + pad * 2, height + pad * 2), (0, 0, 0, 0))
    out.paste(canvas, (pad, pad), canvas)
    return out


def backup_once(path: Path) -> None:
    bak = path.with_suffix(path.suffix + ".pre-cuda.bak")
    if path.exists() and not bak.exists():
        shutil.copy2(path, bak)


def write_landing_svg() -> None:
    LANDING_DIR.mkdir(parents=True, exist_ok=True)
    svg = """<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="280" height="48" viewBox="0 0 280 48" fill="none">
  <text x="0" y="34" font-family="Segoe UI, Arial, Helvetica, sans-serif" font-size="32" font-weight="700">
    <tspan fill="#FFFFFF">Cuda</tspan><tspan fill="#43E4EC">Flops</tspan>
  </text>
</svg>
"""
    (LANDING_DIR / "CudaFlops_logo.svg").write_text(svg, encoding="utf-8")
    old_path = LANDING_DIR / "CloudFlops_logo.svg"
    if old_path.exists():
        backup_once(old_path)
        old_path.write_text(svg, encoding="utf-8")


def rebuild_og(mark: Image.Image, font_path: Path) -> None:
    og = Image.new("RGBA", (1200, 630), (6, 14, 28, 255))
    overlay = Image.new("RGBA", og.size, (0, 0, 0, 0))
    ImageDraw.Draw(overlay).ellipse((700, -80, 1400, 700), fill=(10, 60, 70, 90))
    og = Image.alpha_composite(og, overlay)

    scaled = mark.copy()
    scale = 160 / scaled.height
    scaled = scaled.resize((int(scaled.width * scale), 160), Image.Resampling.LANCZOS)
    og.paste(scaled, (80, 140), scaled)

    font_title = ImageFont.truetype(str(font_path), 72)
    font_sub = ImageFont.truetype(str(Path(r"C:\Windows\Fonts\segoeui.ttf")), 28)
    font_small = ImageFont.truetype(str(Path(r"C:\Windows\Fonts\segoeui.ttf")), 22)
    draw = ImageDraw.Draw(og)
    tx, ty = 80, 340
    draw.text((tx, ty), "Cuda", font=font_title, fill=WHITE + (255,))
    cuda = ImageDraw.Draw(Image.new("RGBA", (1, 1))).textbbox((0, 0), "Cuda", font=font_title)
    draw.text((tx + cuda[2] - cuda[0], ty), "Flops", font=font_title, fill=CYAN + (255,))
    draw.text((tx, ty + 90), "Investment plans with daily APR accruals", font=font_sub, fill=(94, 210, 214, 255))
    draw.text((tx, ty + 140), "USDT / BTC · Dashboard · Withdraw to your wallet", font=font_small, fill=(94, 210, 214, 200))
    draw.text((tx, 580), "Investing involves risk. See /legal/risks", font=font_small, fill=(140, 150, 160, 255))

    path = OUT_DIR / "og-default.png"
    backup_once(path)
    og.convert("RGB").save(path, "PNG")


def main() -> None:
    font_path = resolve_font()
    mark = Image.open(MARK_PATH).convert("RGBA")
    bbox = mark.getbbox()
    if bbox:
        mark = mark.crop(bbox)

    horizontal = compose_horizontal(mark, font_path)
    stacked = compose_stacked(mark, font_path)

    for name, img in (
        ("logo-horizontal.png", horizontal),
        ("logo-stacked.png", stacked),
        ("logo.png", horizontal),
    ):
        path = OUT_DIR / name
        backup_once(path)
        img.save(path, "PNG")
        print(f"wrote {path} {img.size}")

    rebuild_og(mark, font_path)
    write_landing_svg()
    print("og + landing SVG updated")


if __name__ == "__main__":
    main()
