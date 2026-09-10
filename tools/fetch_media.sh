#!/bin/bash
# Télécharge depuis le site officiel les médias statiques absents du miroir
# (bannières avif/webp, images catégories home, vidéo promo…).
# Réexécutable : ne re-télécharge pas ce qui est déjà présent et non vide.
cd "$(dirname "$0")/.."
UA="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36"
get() {
  local rel="$1" dst="public/$1"
  if [ -s "$dst" ]; then echo "skip $rel"; return; fi
  mkdir -p "$(dirname "$dst")"
  for try in 1 2 3 4 5; do
    code=$(curl -s --compressed --http1.1 -A "$UA" -m 120 -o "$dst" -w "%{http_code}" "https://www.dfpinteriores.pt/$rel")
    sz=$(wc -c < "$dst" 2>/dev/null || echo 0)
    if [ "$code" = "200" ] && [ "$sz" -gt 100 ]; then echo "ok   $rel (${sz}b)"; return; fi
    sleep $((try*3))
  done
  rm -f "$dst"; echo "FAIL $rel ($code)"
}

# vidéo : hors public/ pour passer par la route Laravel (support Range/206)
mkdir -p storage/app/media/videos
if [ ! -s storage/app/media/videos/promo.mp4 ]; then
  curl -s --compressed --http1.1 -A "$UA" -m 180 -o storage/app/media/videos/promo.mp4 "https://www.dfpinteriores.pt/videos/promo.mp4" && echo "ok   promo.mp4" || echo "FAIL promo.mp4"
fi
get "social.jpg"
get "images/70-70/Desconto.jpg"
get "favicon/ms-icon-144x144.png"
for b in cofidis_desktop cofidis_mobile cozinhas_desktop cozinhas_mobiel desktop_scalapay \
         emma matosinhos_desktop matosinhos_mobile mobile_scalapay novos_saldos_desktop_ \
         novos_saldos_mobile pack_100_desconto pack_100_desconto_mobile sof_caixa_; do
  get "media/banners/$b.avif"; get "media/banners/$b.webp"
done
for b in btn_mobiliario_exclusivo btn_mobiliario_exterior btn_super_packs; do
  get "media/banners_sec/$b.avif"; get "media/banners_sec/$b.webp"
done
for c in aparadores camas chaise_long mesas_jantar sofa_canto sofas; do
  get "images/400-400/categorias/homepage/$c.png"
done
echo "=== terminé ==="
