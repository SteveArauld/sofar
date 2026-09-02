#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Prépare les gabarits de la boutique à partir du miroir officiel
(resources/views/mirror/source) : on conserve le HTML / CSS / JS du site officiel
et on y insère des jetons `@@…@@` que le contrôleur remplace par nos données.

Les fichiers produits ne sont PAS du Blade (le miroir contient de la syntaxe Vue
`@click`, `{{ }}`, `${ }` qui casserait la compilation Blade). Ce sont des .html
rendus par simple str_replace côté PHP.

Sortie -> resources/views/mirror/ :
  home.html       (index.html officiel, URLs réécrites, <title> = @@META_TITLE@@)
  category.html   (page catégorie ; grille = @@GRID@@ ; pager JS = @@FILTRO_JSON@@)
  product.html    (fiche produit ; window.artigo = @@ARTIGO_JSON@@ ; etc.)
  artigo.html     (fragment d'une carte produit, jetons @@P_*@@)

Relancer après toute mise à jour du miroir :
  python3 tools/build_from_mirror.py
"""

import os
import re
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
APP = os.path.dirname(HERE)
MIRROR = os.path.join(APP, "resources", "views", "mirror", "source")
OUT = os.path.join(APP, "resources", "views", "mirror")

HOME_SRC = os.path.join(MIRROR, "index.html")
CATEGORY_SRC = os.path.join(MIRROR, "sofas-cama.html")
PRODUCT_SRC = os.path.join(MIRROR, "adriano-sofa-chaise-longue-4316.html")


def rewrite_urls(html: str) -> str:
    html = re.sub(r"https?://www\.feiradossofas\.pt/", "/", html)
    html = re.sub(r'(["\'])\.\./([a-z0-9.-]+\.[a-z]{2,})/', r"\1https://\2/", html)
    html = html.replace("%40", "@")
    # liens « protocol-relative » du miroir : //assets/… -> /assets/… (sinon lu comme un domaine)
    html = re.sub(r'(["\'])//+(assets|media|img|favicon|videos)/', r"\1/\2/", html)
    html = re.sub(r'(["\'])(assets|media|img|favicon|videos)/', r"\1/\2/", html)
    # <link rel="stylesheet" href="/"> résiduel (CSS de module vide côté site officiel)
    html = re.sub(r'<link\b[^>]*\bhref="/"[^>]*\brel="stylesheet"[^>]*>', "", html)
    html = re.sub(r'<link\b[^>]*\brel="stylesheet"[^>]*\bhref="/"[^>]*>', "", html)
    # liens internes protocol-relative sans domaine (//carrinho -> /carrinho)
    html = re.sub(r'href="//([a-z0-9][a-z0-9-]*)"', r'href="/\1"', html)
    # Livro de Reclamações : retiré du pied de page (ne faisait que rediriger vers un site externe)
    html = re.sub(r'<li>\s*<a[^>]*livroreclamacoes\.pt[^>]*>.*?</a>\s*</li>', "", html, flags=re.S | re.I)
    html = re.sub(r'<li>\s*<a[^>]*href="/livro-de-reclamacoes"[^>]*>.*?</a>\s*</li>', "", html, flags=re.S | re.I)
    html = html.replace('href="index.html"', 'href="/"')
    # /catalogo/<slug>(.html) -> /<slug>   (menu catégories)
    html = re.sub(r'href="/?catalogo/([A-Za-z0-9-]+)(?:\.html)?"', r'href="/\1"', html)
    # <slug>.html -> /<slug>   (majuscules incluses : artigosExclusivosOnline.html)
    html = re.sub(r'href="([A-Za-z0-9][A-Za-z0-9-]*)\.html"', r'href="/\1"', html)
    html = re.sub(r'href="ajuda/([a-z0-9-]+)(?:\.html)?"', r'href="/ajuda/\1"', html)
    html = re.sub(r"<script[^>]+cloudflareinsights[^>]+></script>", "", html)
    # autocomplete de recherche : le miroir tape un Meilisearch externe (search.grupofeira.ovh),
    # on le redirige vers notre endpoint local qui renvoie le même format {results:[{hits},{hits}]}
    html = re.sub(
        r"axios\.post\(\s*['\"]https://search\.grupofeira\.ovh/multi-search['\"].*?\}\s*\)",
        "axios.get('/procura/sugestoes?q=' + encodeURIComponent(query))",
        html, flags=re.S,
    )
    return html


def strip_httrack(html: str) -> str:
    return re.sub(r"<!--\s*(?:Mirrored from|/?Added by HTTrack).*?-->", "", html, flags=re.S)


def balanced_div(html: str, start: int) -> int:
    depth = 0
    for m in re.finditer(r"<div\b|</div>", html[start:]):
        depth += 1 if m.group(0) == "<div" else -1
        if depth == 0:
            return start + m.end()
    return len(html)


def balanced_brace(html: str, start: int) -> int:
    i = html.index("{", start)
    depth, in_str, esc = 0, "", False
    for j in range(i, len(html)):
        c = html[j]
        if in_str:
            if esc:
                esc = False
            elif c == "\\":
                esc = True
            elif c == in_str:
                in_str = ""
        elif c in "\"'":
            in_str = c
        elif c == "{":
            depth += 1
        elif c == "}":
            depth -= 1
            if depth == 0:
                return j + 1
    return len(html)


def read(path: str) -> str:
    with open(path, "r", encoding="utf-8", errors="replace") as fh:
        return strip_httrack(fh.read())


def write(name: str, content: str) -> None:
    dest = os.path.join(OUT, name)
    os.makedirs(os.path.dirname(dest), exist_ok=True)
    with open(dest, "w", encoding="utf-8") as fh:
        fh.write(content)
    print(f"  écrit {os.path.relpath(dest, APP)}  ({len(content):,} o)")


# --------------------------------------------------------------------------- #
def build_home():
    h = rewrite_urls(read(HOME_SRC))
    h = re.sub(r"<title>.*?</title>", "<title>@@META_TITLE@@</title>", h, count=1, flags=re.S)
    write("home.html", h)


def build_card(category_html: str):
    i = category_html.find('<div id="ListaArtigos"')
    g0 = category_html.find('<div class="Artigo">', i)
    g1 = balanced_div(category_html, g0)
    card = rewrite_urls(category_html[g0:g1])

    card = re.sub(r'href="/[a-z0-9-]+-\d+"', 'href="@@P_URL@@"', card)
    card = re.sub(
        r"<img\b[^>]*\bclass=\"img-responsive\"[^>]*>",
        '<img class="img-responsive" style="aspect-ratio:1;" loading="lazy" src="@@P_IMG@@" alt="@@P_NAME@@">',
        card, count=1,
    )
    card = re.sub(r'(<div class="nome">)\s*.*?\s*(</div>)', r"\1@@P_NAME@@\2", card, count=1, flags=re.S)
    card = re.sub(r'(<div class="Preco">).*?(</div>)', r"\1@@P_PRICE_HTML@@\2", card, count=1, flags=re.S)
    card = re.sub(r'(<div class="colors">).*?(</div>)', r"\1@@P_COLORS@@\2", card, count=1, flags=re.S)
    card = re.sub(r'data-artigo="[^"]*"', 'data-artigo="@@P_ID@@"', card)
    write("artigo.html", card.strip() + "\n")


def build_category():
    h = read(CATEGORY_SRC)
    build_card(h)

    h = rewrite_urls(h)
    h = re.sub(r"<title>.*?</title>", "<title>@@META_TITLE@@</title>", h, count=1, flags=re.S)
    h = re.sub(r"(<h1[^>]*>).*?(</h1>)", r"\1@@H1@@\2", h, count=1, flags=re.S)

    i = h.find('<div id="ListaArtigos"')
    open_end = h.index(">", i) + 1
    close = balanced_div(h, i)
    h = h[:i] + h[i:open_end] + "\n@@GRID@@\n</div>" + h[close:]

    h = re.sub(r"var Filtro = \{[^}]*\};", "var Filtro = @@FILTRO_JSON@@;", h, count=1)

    # barre de filtres figée (marques/prix/dimensions/cores du produit source) -> jeton
    h = re.sub(
        r"<div class='BlocoFiltro categoires'>.*?(?=<div class='BlocoFiltro OrdenarMobile'>)",
        "@@FILTERS@@\n", h, count=1, flags=re.S,
    )
    write("category.html", h)


def build_product():
    h = rewrite_urls(read(PRODUCT_SRC))

    m = re.search(r"window\.artigo\s*=\s*", h)
    e = balanced_brace(h, m.end())
    rest = h[e:]
    rest = rest[rest.index(";") + 1:] if ";" in rest[:4] else rest
    h = h[:m.start()] + "window.artigo = @@ARTIGO_JSON@@;" + rest

    # autres blobs JS figés sur le produit source
    h = re.sub(r"const productId\s*=\s*'[^']*';", "const productId = '@@PRODUCT_TOKEN@@';", h, count=1)
    def swap_object(src: str, prefix: str, token: str) -> str:
        mm = re.search(re.escape(prefix) + r"\s*", src)
        if not mm:
            return src
        end = balanced_brace(src, mm.end())
        after = src[end:]
        after = after[after.index(";") + 1:] if ";" in after[:4] else after
        return src[:mm.start()] + prefix + " " + token + ";" + after

    h = swap_object(h, "const ProdVariations =", "@@PROD_VARIATIONS_JSON@@")
    h = swap_object(h, "let _artigo =", "@@ARTIGO_MINI_JSON@@")
    h = swap_object(h, "let variations =", "@@PROD_VARIATIONS_JSON@@")

    # descriptions rendues côté serveur (onglets Características / Descrição)
    h = re.sub(r'(<div class="DescricaoCurta">).*?(</div>)', r"\1@@SHORT_DESC_HTML@@\2", h, count=1, flags=re.S)
    h = re.sub(r'(<div class="DescricaoCompleta">).*?(</div>\s*</div>)', r"\1@@LONG_DESC_HTML@@\2", h, count=1, flags=re.S)
    h = re.sub(r'(<div class="tab-content" id="descricao">).*?(</div>)', r"\1@@LONG_DESC_HTML@@\2", h, count=1, flags=re.S)

    # champs cachés des formulaires (panier, pedido de info, avisar) figés sur l'id source
    h = re.sub(r"""value=(["'])\d+\1(?=[^>]*\bname=(["'])(?:ProductToCart\[id\]|artigo|InfoArtigo\[artigo\]|product_id)\2)""",
               'value="@@PRODUCT_ID@@"', h)
    h = re.sub(r"""(name=(["'])(?:ProductToCart\[id\]|artigo|InfoArtigo\[artigo\]|product_id)\2[^>]*\bvalue=)(["'])\d+\3""",
               r'\1"@@PRODUCT_ID@@"', h)
    h = h.replace('id="ArtigoId" type="hidden" name="ProductToCart[id]" value="4316"',
                  'id="ArtigoId" type="hidden" name="ProductToCart[id]" value="@@PRODUCT_ID@@"')

    # gabarit caché de la fenêtre d'avis (#JanelaReview) figé sur le produit source
    h = re.sub(r'(<h5 class="card-title">)[^<]*(</h5>)', r"\1@@PRODUCT_NAME@@\2", h)
    h = h.replace('alt="ADRIANO Sofá Chaise Longue"', 'alt="@@PRODUCT_NAME@@"')

    h = re.sub(r"<title>.*?</title>", "<title>@@META_TITLE@@</title>", h, count=1, flags=re.S)
    h = re.sub(r'(<meta property="og:title" content=")[^"]*(")', r"\1@@META_TITLE@@\2", h, count=1)
    h = re.sub(r'(<meta name="description" content=")[^"]*(")', r"\1@@META_DESCRIPTION@@\2", h, count=1)
    h = re.sub(r'(<meta property="og:description" content=")[^"]*(")', r"\1@@META_DESCRIPTION@@\2", h, count=1)
    h = re.sub(
        r'(<div class="ProdutoNome">)\s*.*?\s*(<div class="ref">Ref:\s*<span[^>]*>).*?(</span>)',
        r"\1 @@PRODUCT_NAME@@ \2@@PRODUCT_REF@@\3",
        h, count=1, flags=re.S,
    )
    # 1er bloc ld+json -> jeton ; les blocs suivants (données du produit source) supprimés
    h = re.sub(
        r'(<script type="application/ld\+json">).*?(</script>)',
        r"\1@@SCHEMA_JSON@@\2", h, count=1, flags=re.S,
    )
    h = re.sub(
        r'<script type="application/ld\+json">(?!@@SCHEMA_JSON@@).*?</script>',
        "", h, flags=re.S,
    )

    # dataLayer view_item figé -> jeton
    h = re.sub(
        r'dataLayer\.push\(\{"event":"view_item".*?\}\);',
        "dataLayer.push(@@DATALAYER_JSON@@);", h, count=1, flags=re.S,
    )

    # métadonnées Schema.org / OpenGraph encore figées sur le produit source
    h = re.sub(r'(<meta itemprop="name" content=")[^"]*(")', r"\1@@META_TITLE@@\2", h, count=1)
    h = re.sub(r'(<meta itemprop="description" content=")[^"]*(")', r"\1@@META_DESCRIPTION@@\2", h, count=1, flags=re.S)
    h = re.sub(r'(<meta property="og:site_name" content=")[^"]*(")', r"\1Feira dos Sofás\2", h, count=1)
    h = re.sub(r'(<meta property="og:url" content=")[^"]*(")', r"\1@@CANONICAL@@\2", h, count=1)
    h = re.sub(r'(<link rel="canonical" href=")[^"]*(")', r"\1@@CANONICAL@@\2", h, count=1)
    h = re.sub(r'(<meta name="twitter:title" content=")[^"]*(")', r"\1@@META_TITLE@@\2", h, count=1)
    h = re.sub(r'(<meta name="twitter:description" content=")[^"]*(")', r"\1@@META_DESCRIPTION@@\2", h, count=1)

    # bouton « ajouter au panier » : attributs data-* figés sur le produit source
    h = re.sub(r'data-artigo="\d+"', 'data-artigo="@@PRODUCT_ID@@"', h)
    h = re.sub(r'data-preco="[\d.]*"', 'data-preco="@@PRODUCT_PRICE@@"', h)
    h = re.sub(r'data-nome="[^"]*"', 'data-nome="@@PRODUCT_NAME@@"', h)
    h = re.sub(r'data-categoria="[^"]*"', 'data-categoria="@@PRODUCT_CATEGORY@@"', h)

    # lien de partage WhatsApp figé
    h = re.sub(r'href="https://api\.whatsapp\.com/send\?text=[^"]*"',
               'href="https://api.whatsapp.com/send?text=@@SHARE_TEXT@@"', h, count=1)

    # onglet « Características » : tableau de specs figé sur le produit source.
    # On borne tout l'onglet (radio + label + contenu) par des marqueurs pour
    # pouvoir le retirer entièrement quand le produit n'a aucune caractéristique.
    h = re.sub(
        r'(<input type="radio" name="tabs" id="caracteristicas"[^>]*>\s*'
        r'<label[^>]*for="caracteristicas"[^>]*>.*?</label>\s*'
        r'<div class="tab-content specs">)[\s\S]*?</div>\s*</div>(\s*<input type="radio" name="tabs" id="descricao")',
        r'@@CARACT_OPEN@@\1<div>@@SPECS_HTML@@</div></div>@@CARACT_CLOSE@@\2', h, count=1,
    )

    # les vignettes de variante (Personalização/COR) doivent montrer TOUTE la photo,
    # sans recadrage, dans leur petit carré.
    h = h.replace("</head>", (
        "<style>"
        ".Selects label.thumbnail,.Selects .OpcoesSelect label.thumbnail,"
        "label.thumbnail{overflow:hidden;}"
        "label.thumbnail img{width:100%!important;height:100%!important;"
        "object-fit:contain!important;object-position:center!important;background:#fff;}"
        "</style></head>"
    ), 1)

    write("product.html", h)


# Pages « statiques » (contenu institutionnel) : fichier miroir -> route Laravel
STATIC_PAGES = {
    "descontos70": "descontos70.html",
    "artigosExclusivosOnline": "artigosExclusivosOnline.html",
    "lojas": "lojas.html",
    "carrinho": "carrinho.html",
    "wishlist": "wishlist.html",
    "cliente": "cliente.html",
    "ajuda/termos-e-condicoes": "ajuda/termos-e-condicoes.html",
    "ajuda/politica-privacidade": "ajuda/politica-privacidade.html",
    "ajuda/recrutamento": "ajuda/recrutamento.html",
    "ajuda/resolucao-alternativa-litigios": "ajuda/resolucao-alternativa-litigios.html",
}


def build_static_pages():
    for route, rel in STATIC_PAGES.items():
        src = os.path.join(MIRROR, rel)
        if not os.path.isfile(src) or os.path.getsize(src) < 2000:
            print(f"  (ignoré, miroir absent/vide) {rel}")
            continue
        h = rewrite_urls(read(src))
        h = re.sub(r"<title>.*?</title>", "<title>@@META_TITLE@@</title>", h, count=1, flags=re.S)
        out = "pages/" + route.replace("/", "__") + ".html"
        write(out, h)


def main():
    for p in (HOME_SRC, CATEGORY_SRC, PRODUCT_SRC):
        if not os.path.isfile(p):
            sys.exit(f"Fichier miroir manquant : {p}")
    print("Génération des gabarits depuis le miroir officiel…")
    build_home()
    build_category()
    build_product()
    build_static_pages()
    print("OK.")


if __name__ == "__main__":
    main()
