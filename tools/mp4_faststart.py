#!/usr/bin/env python3
"""Déplace l'atome `moov` avant `mdat` (faststart) pour que la vidéo démarre
sans attendre le téléchargement complet. Patche les offsets stco/co64."""
import struct, shutil, sys

def atoms(buf, start, end):
    i = start
    while i + 8 <= end:
        sz = struct.unpack('>I', buf[i:i+4])[0]
        typ = bytes(buf[i+4:i+8])
        hdr = 8
        if sz == 1:
            sz = struct.unpack('>Q', buf[i+8:i+16])[0]
            hdr = 16
        yield typ, i, sz, hdr
        if sz < hdr:
            return
        i += sz

def patch(buf, start, end, shift):
    for typ, o, s, h in atoms(buf, start, end):
        if typ in (b'trak', b'mdia', b'minf', b'stbl'):
            patch(buf, o + h, o + s, shift)
        elif typ == b'stco':
            n = struct.unpack('>I', buf[o+h+4:o+h+8])[0]
            p = o + h + 8
            for _ in range(n):
                v = struct.unpack('>I', buf[p:p+4])[0] + shift
                buf[p:p+4] = struct.pack('>I', v)
                p += 4
        elif typ == b'co64':
            n = struct.unpack('>I', buf[o+h+4:o+h+8])[0]
            p = o + h + 8
            for _ in range(n):
                v = struct.unpack('>Q', buf[p:p+8])[0] + shift
                buf[p:p+8] = struct.pack('>Q', v)
                p += 8

def faststart(path):
    data = bytearray(open(path, 'rb').read())
    top = {t: (o, s, h) for t, o, s, h in atoms(data, 0, len(data))}
    if b'moov' not in top or b'mdat' not in top:
        print('  moov/mdat manquant, ignoré')
        return False
    mo, ms, mh = top[b'moov']
    if mo < top[b'mdat'][0]:
        print('  déjà faststart, rien à faire')
        return False

    moov = bytearray(data[mo:mo+ms])
    patch(moov, mh, len(moov), ms)   # tout mdat descend de ms octets

    out = bytearray()
    for t, o, s, h in atoms(data, 0, len(data)):
        if t == b'ftyp':
            out += data[o:o+s]
    out += moov
    for t, o, s, h in atoms(data, 0, len(data)):
        if t == b'ftyp' or o == mo:
            continue
        out += data[o:o+s]

    shutil.copy(path, path + '.bak')
    open(path, 'wb').write(out)
    order = [bytes(t).decode('latin1') for t, _, _, _ in atoms(bytearray(out), 0, len(out))]
    print(f'  OK  {path}  atomes: {order}  ({len(out)} o)')
    return True

if __name__ == '__main__':
    for p in sys.argv[1:] or ['public/videos/promo.mp4']:
        print(p)
        faststart(p)
