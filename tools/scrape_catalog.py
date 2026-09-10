#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Scraper complet pour https://www.dfpinteriores.com/

Récupère :
  - Toutes les catégories (arborescence parent/enfant) -> output/categories.json
  - Tous les produits + tous les détails                -> output/products.json
  - Toutes les images de chaque produit, rangées dans   -> output/images/<categorie>/<produit>/

Le site est un CMS PHP maison (Vue.js côté client). Chaque fiche produit contient
un objet JSON complet dans `window.artigo = {...};` : c'est notre source de données.
La liste des produits vient de /sitemap.xml. L'arborescence des catégories vient
du méga-menu de la page d'accueil.

Sortie pensée pour un import Laravel (voir NOTES en bas de fichier).

Dépendances : requests
    pip install requests

Usage :
    python3 scrape_dfpinteriores.py                 # tout
    python3 scrape_dfpinteriores.py --limit 20      # test rapide sur 20 produits
    python3 scrape_dfpinteriores.py --no-images     # métadonnées seulement
    python3 scrape_dfpinteriores.py --workers 6     # parallélisme (défaut 5)
    python3 scrape_dfpinteriores.py --img-size 1000-1000
"""

import argparse
import json
import os
import re
import sys
import time
import unicodedata
from concurrent.futures import ThreadPoolExecutor, as_completed
from html.parser import HTMLParser

import requests

# Domaine SOURCE du scrape (outil de dev uniquement, jamais appelé par l'app).
# Surchargeable : SCRAPE_BASE=https://autre-domaine python3 tools/scrape_catalog.py
BASE = os.environ.get("SCRAPE_BASE", "https://www.feiradossofas.pt").rstrip("/")
SITEMAP_URL = f"{BASE}/sitemap.xml"
APP_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT_DIR = os.path.join(APP_DIR, "database", "data")          # products.json / categories.json
PUBLIC_DIR = os.path.join(APP_DIR, "public")                 # les images vont directement là
IMAGES_DIR = os.path.join(PUBLIC_DIR, "assets", "images")    # public/assets/images/<cat>/<prod>/*.jpg
CACHE_DIR = os.path.join(OUT_DIR, "_cache_html")   # HTML brut mis en cache pour reprise

HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
                  "(KHTML, like Gecko) Chrome/124.0 Safari/537.36",
    "Accept": "text/html,application/xhtml+xml",
    "Accept-Language": "pt-PT,pt;q=0.9,fr;q=0.8,en;q=0.7",
}

# Un produit a une URL du type  /adriano-sofa-chaise-longue-4316  (slug + '-' + id numérique)
PRODUCT_URL_RE = re.compile(r"^/([a-z0-9]+(?:-[a-z0-9]+)*)-(\d+)$")

session = requests.Session()
session.headers.update(HEADERS)


# --------------------------------------------------------------------------- #
#  Utilitaires réseau
# --------------------------------------------------------------------------- #
def fetch(url, tries=4, timeout=60, binary=False):
    """GET avec retries (le serveur coupe souvent la connexion)."""
    last = None
    for attempt in range(1, tries + 1):
        try:
            r = session.get(url, timeout=timeout)
            if r.status_code == 200 and (binary or r.text):
                return r.content if binary else r.text
            last = f"HTTP {r.status_code}"
        except requests.RequestException as exc:
            last = str(exc)
        time.sleep(min(2 * attempt, 8))
    print(f"  !! échec {url} ({last})", file=sys.stderr)
    return None


def slugify(value):
    value = unicodedata.normalize("NFKD", str(value)).encode("ascii", "ignore").decode()
    value = re.sub(r"[^\w\s-]", "", value).strip().lower()
    value = re.sub(r"[-\s]+", "-", value)
    return value or "sans-nom"


# --------------------------------------------------------------------------- #
#  1. Arborescence des catégories (méga-menu de la home)
# --------------------------------------------------------------------------- #
class MenuParser(HTMLParser):
    """
    Parcourt le <ul class="mobileMenu"> ... </ul> de la page d'accueil.
    Chaque <li> contenant <a href="/catalogo/SLUG">Nom</a> devient une catégorie.
    Le parent est le <li>/catégorie qui l'englobe.
    """

    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.in_menu = False
        self.ul_depth = 0          # profondeur de <ul> à l'intérieur du menu
        self.li_stack = []         # pile de slugs de catégories (contexte parent)
        self.cur_href = None
        self.cur_text = []
        self.capturing_a = False
        self.categories = {}       # slug -> dict

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == "ul" and attrs.get("class") == "mobileMenu":
            self.in_menu = True
            self.ul_depth = 1
            return
        if not self.in_menu:
            return
        if tag == "ul":
            self.ul_depth += 1
        elif tag == "li":
            self.li_stack.append(None)          # placeholder, rempli au </a>
        elif tag == "a":
            href = attrs.get("href", "")
            if href.startswith("/catalogo/"):
                self.cur_href = href
                self.cur_text = []
                self.capturing_a = True

    def handle_data(self, data):
        if self.capturing_a:
            self.cur_text.append(data)

    def handle_endtag(self, tag):
        if not self.in_menu:
            return
        if tag == "a" and self.capturing_a:
            self.capturing_a = False
            slug = self.cur_href.split("/catalogo/", 1)[1].strip("/")
            name = re.sub(r"\s+", " ", "".join(self.cur_text)).strip()
            # parent = catégorie du <li> englobant (dernier slug non nul sous le courant)
            parent = None
            for s in reversed(self.li_stack[:-1]):
                if s:
                    parent = s
                    break
            if slug and slug not in self.categories:
                self.categories[slug] = {
                    "id": None,                       # complété plus tard via les produits
                    "name": name,
                    "slug": slug,
                    "parent_slug": parent,
                    "level": self.ul_depth - 1,       # 1 = racine
                    "url": f"{BASE}/{slug}",
                    "catalog_url": f"{BASE}{self.cur_href}",
                }
            if self.li_stack:
                self.li_stack[-1] = slug
            self.cur_href = None
        elif tag == "li":
            if self.li_stack:
                self.li_stack.pop()
        elif tag == "ul":
            self.ul_depth -= 1
            if self.ul_depth == 0:
                self.in_menu = False


def build_categories():
    print("→ Récupération de l'arborescence des catégories…")
    html = fetch(BASE + "/")
    if not html:
        print("Impossible de charger la page d'accueil, arborescence vide.", file=sys.stderr)
        return {}
    p = MenuParser()
    p.feed(html)
    print(f"  {len(p.categories)} catégories trouvées dans le menu.")
    return p.categories


# --------------------------------------------------------------------------- #
#  2. Liste des produits : sitemap + parcours des listes de catégories
# --------------------------------------------------------------------------- #
_PROD_LINK_RE = re.compile(r'href="(?:' + re.escape(BASE) + r')?/([a-z0-9][a-z0-9-]*-(\d+))"')


def _slug_id_from_path(path):
    m = PRODUCT_URL_RE.match(path if path.startswith("/") else "/" + path)
    return (path.lstrip("/"), int(m.group(2))) if m else (None, None)


def list_products_from_sitemap():
    xml = fetch(SITEMAP_URL)
    if not xml:
        return {}
    out = {}
    for loc in re.findall(r"<loc>\s*([^<\s]+)\s*</loc>", xml):
        path = loc.replace(BASE, "")
        slug, pid = _slug_id_from_path(path)
        if pid:
            out[pid] = {"url": f"{BASE}/{slug}", "slug": slug, "id": pid}
    return out


def crawl_listing(key, max_pages=200):
    """Parcourt une liste paginée ({BASE}/{key}, POST p=1..N) -> {id: slug}."""
    found, page, empty_streak = {}, 1, 0
    while page <= max_pages:
        try:
            r = session.post(f"{BASE}/{key}", data={"p": page},
                             headers={"X-Requested-With": "XMLHttpRequest"}, timeout=60)
            html = r.json().get("html", "") if r.status_code == 200 else ""
        except Exception:
            html = ""
        hits = {}
        for m in _PROD_LINK_RE.finditer(html):
            slug, pid = m.group(1), int(m.group(2))
            hits[pid] = slug
        new = [pid for pid in hits if pid not in found]
        found.update(hits)
        if not hits or not new:
            empty_streak += 1
            if empty_streak >= 2:
                break
        else:
            empty_streak = 0
        page += 1
    return found


def list_product_urls(categories, extra_keys=("descontos70", "artigosExclusivosOnline")):
    print("→ Lecture du sitemap…")
    prods = list_products_from_sitemap()
    print(f"  {len(prods)} produits dans le sitemap.")

    # Parcours de toutes les listes de catégories (récupère les produits absents du
    # sitemap ET l'appartenance multi-catégories, comme le site officiel).
    print("→ Parcours des listes de catégories…")
    membership = {}          # id produit -> set(slugs de catégories)
    listing_keys = list(dict.fromkeys(list(categories.keys()) + list(extra_keys)))
    with ThreadPoolExecutor(max_workers=8) as pool:
        futs = {pool.submit(crawl_listing, k): k for k in listing_keys}
        for i, fut in enumerate(as_completed(futs), 1):
            k = futs[fut]
            try:
                hits = fut.result()
            except Exception:
                hits = {}
            for pid, slug in hits.items():
                membership.setdefault(pid, set())
                if k not in extra_keys:
                    membership[pid].add(k)
                prods.setdefault(pid, {"url": f"{BASE}/{slug}", "slug": slug, "id": pid})
            if i % 25 == 0 or i == len(futs):
                print(f"  {i}/{len(futs)} listes parcourues, {len(prods)} produits connus")

    # sauvegarde de la liste « Descontos até 70% » exacte du site
    d70 = sorted(crawl_listing("descontos70").keys())
    if d70:
        with open(os.path.join(OUT_DIR, "descontos70.json"), "w", encoding="utf-8") as fh:
            json.dump(d70, fh)
        print(f"  descontos70.json : {len(d70)} produits")

    for pid, p in prods.items():
        p["categories"] = sorted(membership.get(pid, set()))

    print(f"  {len(prods)} produits au total (sitemap + catégories).")
    return sorted(prods.values(), key=lambda p: p["id"])


# --------------------------------------------------------------------------- #
#  3. Extraction d'une fiche produit
# --------------------------------------------------------------------------- #
def extract_js_object(html, marker):
    """Renvoie la sous-chaîne { ... } qui suit `marker` en équilibrant les accolades."""
    i = html.find(marker)
    if i == -1:
        return None
    i = html.index("{", i)
    depth = 0
    in_str = False
    esc = False
    for j in range(i, len(html)):
        c = html[j]
        if in_str:
            if esc:
                esc = False
            elif c == "\\":
                esc = True
            elif c == '"':
                in_str = False
        else:
            if c == '"':
                in_str = True
            elif c == "{":
                depth += 1
            elif c == "}":
                depth -= 1
                if depth == 0:
                    return html[i:j + 1]
    return None


def parse_jsonld_product(html):
    for block in re.findall(
        r'<script[^>]*application/ld\+json[^>]*>(.*?)</script>', html, re.S
    ):
        try:
            data = json.loads(block.strip())
        except json.JSONDecodeError:
            continue
        items = data if isinstance(data, list) else [data]
        for it in items:
            if isinstance(it, dict) and it.get("@type") == "Product":
                return it
    return None


def collect_image_names(artigo):
    """Toutes les images du produit (principales + variantes), dédoublonnées, ordre conservé."""
    names = []
    seen = set()

    def add(n):
        if not n or not isinstance(n, str):
            return
        n = n.strip()
        # on ne garde que de vrais fichiers image (évite les placeholders type "/", "1"…)
        if not re.search(r"\.(jpe?g|png|webp|gif)$", n, re.I):
            return
        if n not in seen:
            seen.add(n)
            names.append(n)

    for n in artigo.get("images") or []:
        add(n)
    variations = artigo.get("variations") or {}
    for v in variations.get("variations", []) or []:
        if isinstance(v, dict):
            img = v.get("image")
            if isinstance(img, dict):
                add(img.get("name"))
            imgs = v.get("images")
            if isinstance(imgs, dict):
                for n in imgs.get("name") or []:
                    add(n)
            for key, val in v.items():
                if isinstance(val, dict) and val.get("thumbnail"):
                    add(val["thumbnail"])
    return names


def scrape_product(prod, categories, img_size, want_images):
    url = prod["url"]
    cache_file = os.path.join(CACHE_DIR, f"{prod['id']}.html")
    html = None
    if os.path.isfile(cache_file):
        with open(cache_file, "r", encoding="utf-8", errors="replace") as fh:
            html = fh.read()
    if not html:
        html = fetch(url)
        if not html:
            return None
        with open(cache_file, "w", encoding="utf-8") as fh:
            fh.write(html)

    raw = extract_js_object(html, "window.artigo = ")
    if not raw:
        print(f"  !! window.artigo introuvable pour {url}", file=sys.stderr)
        return None
    try:
        artigo = json.loads(raw)
    except json.JSONDecodeError as exc:
        print(f"  !! JSON invalide pour {url}: {exc}", file=sys.stderr)
        return None

    ld = parse_jsonld_product(html) or {}
    offers = ld.get("offers") or {}

    breadcrumb = artigo.get("breadcrumb") or []
    category_path = [b.get("link") for b in breadcrumb if b.get("link")]
    category_slug = category_path[-1] if category_path else slugify(
        artigo.get("categoria_nome") or "sans-categorie"
    )

    # enrichir la catégorie feuille avec l'id numérique du site
    if category_slug and artigo.get("categoria_id"):
        cat = categories.get(category_slug)
        if cat is None:
            cat = categories[category_slug] = {
                "id": None, "name": artigo.get("categoria_nome") or category_slug,
                "slug": category_slug,
                "parent_slug": category_path[-2] if len(category_path) > 1 else None,
                "level": len(category_path), "url": f"{BASE}/{category_slug}",
                "catalog_url": f"{BASE}/catalogo/{category_slug}",
            }
        if cat.get("id") is None:
            cat["id"] = artigo.get("categoria_id")

    # ---- images ----
    image_names = collect_image_names(artigo)
    images = []
    prod_folder_rel = os.path.join(slugify(category_slug), slugify(prod["slug"]))
    for name in image_names:
        clean = name.lstrip("/")                       # "products/foto1_x.jpg"
        src = f"{BASE}/images/{img_size}/{clean}"
        fname = os.path.basename(clean)
        # chemin relatif à public/ : les images sont servies directement, pas de copie à l'import
        local_rel = os.path.join("assets", "images", prod_folder_rel, fname)
        images.append({"source_url": src, "local_path": local_rel, "filename": fname})

    if want_images and images:
        dest_dir = os.path.join(IMAGES_DIR, prod_folder_rel)
        os.makedirs(dest_dir, exist_ok=True)
        for im in images:
            dest = os.path.join(PUBLIC_DIR, im["local_path"])
            if os.path.isfile(dest) and os.path.getsize(dest) > 0:
                continue
            data = fetch(im["source_url"], tries=3, binary=True)
            if data:
                with open(dest, "wb") as fh:
                    fh.write(data)

    # ---- prix ----
    def num(v):
        try:
            return float(v)
        except (TypeError, ValueError):
            return None

    price_now = num(artigo.get("preco")) or num(offers.get("price"))
    price_before = num(artigo.get("preco_antes")) or num(artigo.get("preco_pvp"))
    # Le prix barré est souvent uniquement dans les variations (precoantes vs preco) :
    # on retient le plus grand "precoantes" réellement supérieur au prix courant.
    if not price_before:
        _cand = [
            num(v.get("precoantes"))
            for v in ((artigo.get("variations") or {}).get("variations") or [])
            if isinstance(v, dict)
        ]
        _cand = [c for c in _cand if c and price_now and c > price_now]
        if _cand:
            price_before = max(_cand)

    # Toutes les catégories où le produit apparaît (menu de la fiche + listes crawlées).
    all_categories = sorted(set(category_path) | set(prod.get("categories") or []))

    record = {
        "id": artigo.get("id") or prod["id"],
        "erp_id": artigo.get("erp_id"),
        "name": artigo.get("nome"),
        "slug": artigo.get("url") or prod["slug"],
        "url": url,
        "sku": artigo.get("ref") or ld.get("sku"),
        "ean": artigo.get("ean"),
        "brand": artigo.get("marca") or (ld.get("brand") or {}).get("name"),
        "supplier_id": artigo.get("fornecedor"),

        "category_id": artigo.get("categoria_id"),
        "category_name": artigo.get("categoria_nome"),
        "category_slug": category_slug,
        "category_path": category_path,          # ["sofas", "chaise-longue"]
        "categories": all_categories,            # toutes les catégories (multi-appartenance)
        "breadcrumb": breadcrumb,

        "price": price_now,
        "price_before": price_before,
        "currency": offers.get("priceCurrency") or "EUR",
        "vat_percent": artigo.get("iva"),
        "availability": offers.get("availability", "").split("/")[-1] or None,
        "in_stock": bool(artigo.get("inStock")),
        "stock": artigo.get("stock"),
        "stock_global": artigo.get("stock_global"),
        "delivery_time_stock": artigo.get("prazo_entrega_stock"),
        "delivery_time_order": artigo.get("prazo_entrega_encomenda"),
        "manufacturing_days": artigo.get("dias_fabrico"),

        "short_description_html": artigo.get("textocurto"),
        "long_description_html": artigo.get("textolongo"),
        "logistic_data_html": artigo.get("dadoslogisticos"),
        "delivery_assembly_html": artigo.get("entregamontagem"),

        "specs": artigo.get("specs") or {},       # dict attribut -> valeur
        "dimensions": {
            "altura": artigo.get("altura"),
            "largura": artigo.get("largura"),
            "comprimento": artigo.get("comprimento"),
            "profundidade": artigo.get("profundidade"),
            "peso": artigo.get("peso"),
            "volumes": artigo.get("volumes"),
            "unidades_pack": artigo.get("unidades_pack"),
        },

        "variations": artigo.get("variations") or {},
        "extra_services": artigo.get("servicosExtra") or [],
        "related": artigo.get("relacionados"),

        "rating": artigo.get("rating"),
        "reviews": artigo.get("reviews") or [],
        "views_20d": artigo.get("views20"),

        # Disponibilité réelle : le site vend « por encomenda » si vende_apenas_stock = "N".
        "vende_apenas_stock": artigo.get("vende_apenas_stock"),
        "prazo_entrega_stock": artigo.get("prazo_entrega_stock"),
        "prazo_entrega_encomenda": artigo.get("prazo_entrega_encomenda"),
        "sticker": artigo.get("sticker"),
        "preco_promo": num(artigo.get("preco_promo")),

        "flags": {
            "novidade": artigo.get("novidade"),
            "outlet": artigo.get("outlet"),
            "saldos": artigo.get("saldos"),
            "promocao": artigo.get("promocao"),
            "desconto70": artigo.get("desconto70"),
            "exclusivo_site": artigo.get("exclusivo_site"),
            "personalizavel": artigo.get("personalizavel"),
        },
        "status": artigo.get("status"),
        "created_at": artigo.get("createdh"),
        "updated_at": artigo.get("updatedh"),

        "images": images,
        "images_count": len(images),
    }
    return record


# --------------------------------------------------------------------------- #
#  Programme principal
# --------------------------------------------------------------------------- #
def main():
    ap = argparse.ArgumentParser(description="Scraper dfpinteriores.com")
    ap.add_argument("--limit", type=int, default=0, help="limiter le nombre de produits (test)")
    ap.add_argument("--workers", type=int, default=5, help="threads (défaut 5)")
    ap.add_argument("--img-size", default="800-800",
                    help="taille des images: 300-300 | 400-400 | 800-800 | 1000-1000")
    ap.add_argument("--no-images", action="store_true", help="ne pas télécharger les images")
    args = ap.parse_args()

    os.makedirs(OUT_DIR, exist_ok=True)
    os.makedirs(IMAGES_DIR, exist_ok=True)
    os.makedirs(CACHE_DIR, exist_ok=True)

    categories = build_categories()
    products = list_product_urls(categories)
    if args.limit:
        products = products[: args.limit]

    results = []
    want_images = not args.no_images
    done = 0
    total = len(products)

    def checkpoint():
        """Écrit products.json partiel pour suivre l'avancement / importer sans attendre la fin."""
        snap = sorted(results, key=lambda r: r["id"])
        tmp = os.path.join(OUT_DIR, "products.json.tmp")
        with open(tmp, "w", encoding="utf-8") as fh:
            json.dump(snap, fh, ensure_ascii=False, indent=2)
        os.replace(tmp, os.path.join(OUT_DIR, "products.json"))

    with ThreadPoolExecutor(max_workers=args.workers) as pool:
        futures = {
            pool.submit(scrape_product, p, categories, args.img_size, want_images): p
            for p in products
        }
        for fut in as_completed(futures):
            p = futures[fut]
            done += 1
            try:
                rec = fut.result()
            except Exception as exc:                      # noqa: BLE001
                print(f"  !! exception {p['url']}: {exc}", file=sys.stderr, flush=True)
                rec = None
            if rec:
                results.append(rec)
            if done % 25 == 0 or done == total:
                print(f"  {done}/{total} produits traités ({len(results)} ok)", flush=True)
            if done % 100 == 0:
                checkpoint()

    results.sort(key=lambda r: r["id"])

    # Les fils d'Ariane des produits font autorité sur la hiérarchie :
    # on complète / corrige l'arborescence issue du menu.
    for rec in results:
        chain = rec.get("breadcrumb") or []
        for pos, node in enumerate(chain):
            slug = node.get("link")
            if not slug:
                continue
            parent = chain[pos - 1].get("link") if pos > 0 else None
            cat = categories.get(slug)
            if cat is None:
                cat = categories[slug] = {
                    "id": None, "name": node.get("nome") or slug, "slug": slug,
                    "parent_slug": parent, "level": pos + 1,
                    "url": f"{BASE}/{slug}", "catalog_url": f"{BASE}/catalogo/{slug}",
                }
            if not cat.get("parent_slug") and parent:
                cat["parent_slug"] = parent
                cat["level"] = pos + 1
            if not cat.get("name") and node.get("nome"):
                cat["name"] = node["nome"]
        # id numérique de la catégorie feuille
        if rec.get("category_id") and rec.get("category_slug") in categories:
            leaf = categories[rec["category_slug"]]
            if leaf.get("id") is None:
                leaf["id"] = rec["category_id"]

    # catégories -> liste, avec parent_id résolu
    slug_to_id = {s: c["id"] for s, c in categories.items()}
    cat_list = []
    for c in categories.values():
        c = dict(c)
        c["parent_id"] = slug_to_id.get(c.get("parent_slug"))
        cat_list.append(c)
    cat_list.sort(key=lambda c: (c["level"], c["slug"]))

    with open(os.path.join(OUT_DIR, "categories.json"), "w", encoding="utf-8") as fh:
        json.dump(cat_list, fh, ensure_ascii=False, indent=2)
    with open(os.path.join(OUT_DIR, "products.json"), "w", encoding="utf-8") as fh:
        json.dump(results, fh, ensure_ascii=False, indent=2)

    print(f"\n✔ Terminé : {len(results)} produits, {len(cat_list)} catégories.")
    print(f"  {OUT_DIR}/products.json")
    print(f"  {OUT_DIR}/categories.json")
    print(f"  {IMAGES_DIR}/<categorie>/<produit>/*.jpg")


if __name__ == "__main__":
    main()

# --------------------------------------------------------------------------- #
# NOTES pour l'import Laravel
# --------------------------------------------------------------------------- #
# categories.json : [{id, name, slug, parent_id, parent_slug, level, url}]
#   -> table `categories` (id, name, slug unique, parent_id nullable self-ref).
#      Importer d'abord level 1, puis 2, puis 3 (ou désactiver la contrainte FK).
#
# products.json : un objet par produit. Champs directement mappables sur une
#   table `products`. Relations conseillées :
#     - product.category_id           -> categories.id (catégorie feuille)
#     - product.category_path[]       -> table pivot category_product (multi-catégories)
#     - product.images[]              -> table `product_images`
#         (product_id, filename, local_path, source_url, position)
#     - product.specs {}              -> table `product_specs` (product_id, attr, value)
#         ou colonne JSON `specs`
#     - product.variations {}         -> table `product_variations` (colonne JSON simple au début)
#     - product.extra_services[]      -> table `product_extra_services`
#     - product.reviews[]             -> table `product_reviews`
#   Les descriptions sont du HTML : les stocker tel quel (champ TEXT) et purifier à l'affichage.
#   Les images locales sont dans output/images/... : copier vers storage/app/public/products/.
