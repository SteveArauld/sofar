#!/bin/bash
cd "$(dirname "$0")/.."
UA="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/124 Safari/537.36"
while IFS= read -r f; do
  [ -z "$f" ] && continue
  dst="public/assets/images/lojas/$f"
  [ -s "$dst" ] && { echo "skip $f"; continue; }
  for t in 1 2 3 4; do
    code=$(curl -s -G --data-urlencode "x=" --compressed --http1.1 -A "$UA" -m 90 \
      -o "$dst" -w "%{http_code}" "https://www.feiradossofas.pt/assets/images/lojas/$f")
    sz=$(wc -c < "$dst" 2>/dev/null || echo 0)
    [ "$code" = 200 ] && [ "$sz" -gt 200 ] && { echo "ok   $f (${sz}o)"; break; }
    sleep 3
  done
  [ "${sz:-0}" -lt 200 ] && { rm -f "$dst"; echo "FAIL $f ($code)"; }
done < /tmp/lojas_files.txt
echo "=== fini ==="
