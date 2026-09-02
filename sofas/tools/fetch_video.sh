#!/bin/bash
# Télécharge (avec reprise) la vidéo promo jusqu'à obtenir la taille complète.
cd "$(dirname "$0")/.."
V=storage/app/media/videos/promo.mp4
URL="https://www.feiradossofas.pt/videos/promo.mp4"
UA="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36"
mkdir -p "$(dirname "$V")"
EXPECT=$(curl -sI --http1.1 -A "$UA" -m 60 "$URL" | awk 'tolower($1)=="content-length:"{print $2+0}' | tr -d '\r')
echo "taille attendue : ${EXPECT:-inconnue}"
for i in $(seq 1 40); do
  curl -s --http1.1 -A "$UA" --retry 5 --retry-delay 3 -m 400 -C - -o "$V" "$URL"
  sz=$(wc -c < "$V" 2>/dev/null || echo 0)
  echo "tentative $i : $sz octets"
  if [ -n "$EXPECT" ] && [ "$sz" = "$EXPECT" ]; then echo "COMPLET"; break; fi
  if [ -z "$EXPECT" ] && [ "$sz" -gt 4000000 ]; then echo "OK (>4 Mo)"; break; fi
  sleep 4
done
python3 - <<'PY'
d=open('storage/app/media/videos/promo.mp4','rb').read()
print("moov présent :", b'moov' in d, "| taille finale :", len(d))
PY
