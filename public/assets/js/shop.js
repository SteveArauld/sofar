/* DFP Interiores — favoris + retours visuels panier (vanilla, sans Vue) */
(function () {
    'use strict';

    var CSRF = window.CSRF_TOKEN || '';
    var WISH = Array.isArray(window.WISH_IDS) ? window.WISH_IDS.map(String) : [];

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify(body || {})
        }).then(function (r) {
            if (!r.ok) throw r;
            return r.json();
        });
    }

    /* ---------- Toast ---------- */
    var toastEl = null, toastTimer = null;
    function toast(msg) {
        if (!toastEl) {
            toastEl = document.createElement('div');
            toastEl.className = 'shop-toast';
            document.body.appendChild(toastEl);
        }
        toastEl.textContent = msg;
        requestAnimationFrame(function () { toastEl.classList.add('show'); });
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, 2600);
    }
    window.shopToast = toast;

    /* ---------- Badge favoris (header) ---------- */
    function setWishBadge(count) {
        var link = document.querySelector('.icons .wishlist-link');
        if (!link) return;
        var badge = link.querySelector('.wish-badge');
        if (count > 0) {
            if (!badge) {
                badge = document.createElement('small');
                badge.className = 'wish-badge';
                link.appendChild(badge);
            }
            badge.textContent = count;
        } else if (badge) {
            badge.remove();
        }
    }

    /* ---------- Synchronise tous les cœurs d'un même produit ---------- */
    function syncHearts(id, active) {
        document.querySelectorAll('.Wishlist[data-artigo="' + (window.CSS && CSS.escape ? CSS.escape(id) : id) + '"]')
            .forEach(function (el) { el.classList.toggle('is-active', active); });
    }

    function markInitial() {
        document.querySelectorAll('.Wishlist[data-artigo]').forEach(function (el) {
            if (WISH.indexOf(String(el.getAttribute('data-artigo'))) !== -1) {
                el.classList.add('is-active');
            }
        });
    }

    /* ---------- Clic sur un cœur ---------- */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('a.Wishlist, .Wishlist[data-artigo]');
        if (!btn) return;
        e.preventDefault();
        var id = btn.getAttribute('data-artigo');
        if (!id || btn.dataset.busy) return;
        btn.dataset.busy = '1';

        post('/wishlist/toggle', { product: id })
            .then(function (data) {
                syncHearts(id, !!data.added);
                setWishBadge(data.count);
                toast(data.added ? 'Adicionado aos favoritos ♥' : 'Removido dos favoritos');

                // Page /wishlist : on retire la carte en direct
                if (!data.added && location.pathname.replace(/\/$/, '') === '/wishlist') {
                    var card = btn.closest('.Artigo') || btn.closest('[data-wish-card]');
                    if (card) {
                        card.style.transition = 'opacity .25s, transform .25s';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(.95)';
                        setTimeout(function () {
                            card.remove();
                            if (!document.querySelector('.Wishlist.CatalogoGrid .Artigo, .CatalogoGrid .Artigo')) {
                                location.reload();
                            }
                        }, 260);
                    }
                }
            })
            .catch(function () { toast('Ocorreu um erro. Tente novamente.'); })
            .finally(function () { delete btn.dataset.busy; });
    });

    if (document.readyState !== 'loading') markInitial();
    else document.addEventListener('DOMContentLoaded', markInitial);
})();
