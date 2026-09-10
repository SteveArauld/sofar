#!/bin/bash
cd "$(dirname "$0")/.."
UA="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/124 Safari/537.36"
URL="https://www.dfpinteriores.pt/videos/promo.mp4"
for try in $(seq 1 12); do
  curl -sS --fail --compressed --http1.1 -A "$UA" -m 600 -o /tmp/promo_dl.mp4 "$URL" && {
    sz=$(wc -c < /tmp/promo_dl.mp4)
    mv=$(python3 -c "print(b'moov' in open('/tmp/promo_dl.mp4','rb').read())")
    echo "$(date +%H:%M:%S) essai $try : ${sz} o  moov=$mv"
    if [ "$mv" = "True" ] && [ "$sz" -gt 500000 ]; then
      cp /tmp/promo_dl.mp4 storage/app/media/videos/promo.mp4
      cp /tmp/promo_dl.mp4 public/videos/promo.mp4
      echo "OK -> copié dans storage/ et public/videos/"
      exit 0
    fi
  }
  sleep 8
done
echo "ÉCHEC après 12 essais"
exit 1
