"""Parse FlowGuard price list Excel and export JSON for Laravel import."""
import json
import os
import re
import sys

import pandas as pd

DEFAULT_PATH = r"E:\نماذج منتجات تاسيتي\Al waab flowguard pipe price list.xlsx"
OUTPUT_PATH = os.path.join(os.path.dirname(__file__), "..", "database", "data", "flowguard_price_list.json")


def map_type(type_raw: str | None, desc: str) -> str:
    t = (type_raw or "").lower().strip()
    d = desc.lower()
    if "pipe" in t or d.startswith("cpvc pipe") or d.startswith("pipe "):
        return "pipe"
    if "valve" in t or "valve" in d:
        return "valve"
    if "solvent" in t or "cement" in d:
        return "adhesive"
    return "fitting"


def extract_size(desc: str) -> str | None:
    match = re.search(r"(\d+(?:\.\d+)?)\s*mm", desc, re.I)
    return f"{match.group(1)}mm" if match else None


def extract_pressure(desc: str) -> str | None:
    match = re.search(r"PN\d+", desc, re.I)
    return match.group(0).upper() if match else None


def section_slug(section: str) -> str:
    return "cold-water" if "Cold" in section else "hot-water"


def parse(path: str) -> list[dict]:
    df = pd.read_excel(path, sheet_name="Product Price list", header=None)
    products = []
    current_section = "General"

    for _, row in df.iterrows():
        sno, type_raw, desc, unit, price, markup = row[0], row[2], row[3], row[5], row[6], row[7]

        if pd.notna(desc) and pd.isna(sno) and pd.isna(price):
            label = str(desc).strip()
            if label in ("Cold Water", "Hot Water"):
                current_section = label
            continue

        if pd.isna(sno):
            continue

        try:
            sno_int = int(float(sno))
        except (TypeError, ValueError):
            continue

        if not desc or str(desc) == "nan":
            continue

        desc = str(desc).strip()
        type_raw = str(type_raw).strip() if pd.notna(type_raw) else None

        try:
            price_val = float(price)
        except (TypeError, ValueError):
            continue

        markup_val = None
        try:
            if pd.notna(markup) and str(markup).lower() not in ("done", "nan"):
                markup_val = float(markup)
        except (TypeError, ValueError):
            pass

        unit_str = str(unit).strip() if pd.notna(unit) else "pcs"

        products.append({
            "sku": f"FG-{sno_int:04d}",
            "source_sno": sno_int,
            "section": current_section,
            "section_slug": section_slug(current_section),
            "fitting_type": type_raw,
            "type": map_type(type_raw, desc),
            "name_en": desc,
            "name_ar": desc,
            "size": extract_size(desc),
            "pressure_rating": extract_pressure(desc),
            "unit": unit_str,
            "price_aed": round(price_val, 2),
            "price_with_markup_aed": round(markup_val, 2) if markup_val else round(price_val * 1.02, 2),
            "certifications": ["FM", "NSF", "UL"],
        })

    return products


if __name__ == "__main__":
    source = sys.argv[1] if len(sys.argv) > 1 else DEFAULT_PATH
    products = parse(source)
    os.makedirs(os.path.dirname(OUTPUT_PATH), exist_ok=True)
    with open(OUTPUT_PATH, "w", encoding="utf-8") as f:
        json.dump(products, f, ensure_ascii=False, indent=2)
    print(f"Exported {len(products)} products")
