#!/bin/bash
# Récupère du site officiel les assets absents du miroir pour les pages
# (CSS lojas, JS modules, photos des magasins).
cd "$(dirname "$0")/.."
UA="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36"
B="https://www.dfpinteriores.pt"
get() { # relpath
  local rel="$1" dst="public/$1"
  [ -s "$dst" ] && { echo "skip $rel"; return; }
  mkdir -p "$(dirname "$dst")"
  # encode espaces/accents dans le chemin
  local url="$B/$(python3 -c "import urllib.parse,sys;print(urllib.parse.quote(sys.argv[1]))" "$rel")"
  for t in 1 2 3 4 5; do
    code=$(curl -s --compressed --http1.1 -A "$UA" -m 120 -o "$dst" -w "%{http_code}" "$url")
    sz=$(wc -c < "$dst" 2>/dev/null || echo 0)
    [ "$code" = 200 ] && [ "$sz" -gt 80 ] && { echo "ok   $rel (${sz}o)"; return; }
    sleep 3
  done
  rm -f "$dst"; echo "FAIL $rel ($code)"
}
get "assets/css/pt/mods/lojas.css"
get "assets/modulos/ajuda.js"
get "assets/modulos/catalogo.js"
get "assets/images/fast.png"
get "assets/images/store.png"
while read -r p; do [ -n "$p" ] && get "${p#/}"; done < /tmp/lojas_imgs.txt
echo "=== terminé fetch_page_assets ==="
