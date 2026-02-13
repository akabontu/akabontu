from PIL import Image
from pathlib import Path

files = [Path('assets/images/adp_logo_final.png'), Path('assets/images/adp_icon_final.png')]

for p in files:
    if not p.exists():
        print(f"Not found: {p}")
        continue
    img = Image.open(p)
    out = p.with_name(p.stem + '_opt' + p.suffix)
    # convert to RGBA if has transparency issues
    if img.mode not in ('RGBA','RGB'):
        img = img.convert('RGBA')
    img.save(out, optimize=True)
    print(f"Saved: {out} ({out.stat().st_size} bytes)")
