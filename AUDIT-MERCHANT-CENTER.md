# Audit Google Merchant Center — DFP Interiores

Date : 2026-09-11
Périmètre : conformité fiches gratuites + annonces Shopping, marché Portugal uniquement.
**Étape 1 — audit seul. Aucune modification de code n'a été faite à ce stade.**

---

## 1. Stack détectée

| Élément | Valeur |
|---|---|
| Framework | Laravel 12.43.1 (composer demande `^13.17`, installé 12.x) — PHP `^8.3` |
| Front | Blade + Vue 3 (CDN/local `public/assets/vue`), pas de build Vite pour le store |
| Base de données | **SQLite** (`database/database.sqlite`), `DB_CONNECTION=sqlite` |
| Sessions / cache / queue | `database` |
| Mail | SMTP `127.0.0.1:1025` (Mailpit/dev) — `contacto@dfpinteriores.com` |
| E-commerce | 100 % custom (pas de WooCommerce/Shopify/PrestaShop). Contrôleurs `StoreController`, `CartController`, `CheckoutController`, `FeedController` |
| Import catalogue | `php artisan catalog:import` (`app/Console/Commands/ImportCatalog.php`) depuis un scrape JSON de `dfpinteriores.com` |
| Flux produit existant | `app/Http/Controllers/FeedController.php` → routes `/feed/produtos.xml` et `/feed/produtos.xml/download` (RSS 2.0 + `g:`) |
| Nouveau fichier | `public/dfpinteriores-gmc-conforme.xml` (2,9 Mo, statique) + route `/dfpinteriores-gmc-conforme.xml` |

### Où sont les données
- **Produits** : table `products` (PK = id du site source). 5 280 lignes.
- **Prix** : `products.price`, `products.price_before` (decimal 10,2), `currency` (='EUR' partout), `vat_percent` (=23 partout).
- **Stock** : `products.in_stock` (bool), `products.stock`, `products.source_payload->vende_apenas_stock`.
- **Images** : table `product_images` (`path` relatif à `public/`, ex. `assets/images/<cat>/<slug>/foto1.jpg`). 16 001 lignes, mais **seulement 1 919 produits ont au moins une image**.
- **Catégories** : `categories` + pivot `category_product` + `products.breadcrumb` (JSON).

### Chiffres catalogue (SQLite, live)
| Filtre | Nombre |
|---|---|
| Produits total | 5 280 |
| `price > 0` | 5 278 |
| `price = 0` ou NULL | 2 (à exclure) |
| `price >= 80 €` | 2 898 |
| Avec ≥ 1 image | 1 919 |
| `price >= 80` **ET** image | 1 403 |
| `price >= 80` **ET** `in_stock` **ET** image | **1 139** ← pool éligible réel avant contrôle qualité/HTTP |
| `in_stock = true` | 3 275 |
| EAN / GTIN renseigné | **0** |
| SKU renseigné | 5 280 (100 %) |
| `brand` NULL | 0 |
| `price_before > price` (soldes) | 657 |
| Devise ≠ EUR | 0 |

> **Le pool éligible (1 139) est supérieur à l'objectif de 980, mais la marge est faible.**
> Après nettoyage qualité + vérification HTTP 200 sur chaque `link` et `image_link`, le
> nombre final pourrait passer sous 980. Dans ce cas, conformément à la consigne :
> aucune compensation, le flux contiendra le nombre réel.

---

## 2. Problèmes pouvant causer refus / suspension Merchant Center

### 🔴 BLOQUANT

| # | Problème | Détail | Fichier / donnée |
|---|---|---|---|
| B1 | **`brand` incohérent** | Marque dominante = `Feira dos Sofás` (ancien nom du site scrapé), présente sur la majorité du catalogue. Google désapprouve « marque incohérente / générique ». Les vraies marques fabricants ne sont présentes que sur la literie (COLMED, MOLAFLEX, EMMA, TEMPUR, PIKOLIN, SEALY, SLEEP PRO, ECOSLEEP, SANTI D`ITALIA, MD). `FeedController` a un pansement (`str_contains 'Feira dos Sof' → app.name`) mais **la base n'est pas corrigée** et le pansement ne couvre pas les variantes. | `products.brand`, `FeedController::item()` |
| B2 | **Aucun identifiant produit (GTIN/EAN)** | 0 produit sur 5 280 a un EAN. Le flux passe donc `identifier_exists=no` en masse. Acceptable pour du mobilier sur-mesure, mais **doit être associé à `brand` + `mpn` (SKU) fiables** — or `brand` est faux (voir B1). Sans marque correcte, `identifier_exists=no` + MPN seul est souvent refusé. | `products.ean`, `products.sku` |
| B3 | **Prix incohérent page vs flux possible** | Page produit : le prix est rendu côté JS (`precos.atual`) depuis un payload ; le flux lit `products.price`. Il faut **garantir** que les deux sortent de la même colonne. Certains produits ont `price` sans être réellement achetables (voir B4). À vérifier sur échantillon ≥ 30. | `product.blade.php` (l.76-88) vs `FeedController` |
| B4 | **Produits « sob consulta » / prix aberrants inclus** | `price` min > 0 = **0,80 €** ; 2 produits à 0/NULL ; des dizaines sous 80 €. Le flux actuel inclut **tout `price > 0` avec image** (pas de plancher, pas de filtre stock strict, pas de filtre HTTP). | `FeedController::stream()` |
| B5 | **Frais de livraison non affichés avant paiement** | La page `checkout` n'affiche **ni méthode de livraison, ni ligne de port, ni délai**. `Total = Subtotal`. Le serveur force `LEVANTAMENTO` (gratuit) + `MULTIBANCO`, sans que le client le voie. GMC exige des frais de port visibles/paramétrés. Le flux actuel **ne contient aucune balise `shipping`**. | `resources/views/pages/checkout.blade.php` (l.118-124), `CheckoutController` |
| B6 | **Pages produit : couverture image insuffisante** | 3 361 produits `price>0` **sans image** → seraient refusés « image manquante ». Doivent être exclus du flux (déjà le cas via `whereHas('images')`), mais confirme qu'aucun `path` ne pointe vers un placeholder / logo générique. | `product_images` |
| B7 | **`Política de Envios e Entregas` : page inexistante** | Aucune page dédiée. Contenu partiel noyé dans les Termos. GMC + droit PT exigent une politique de livraison claire et liée en footer. | `resources/views/pages/` |
| B8 | **`Política de Devoluções e Reembolsos` : page dédiée inexistante** | Le droit de libre résolution 14 j / DL 24/2014 est mentionné **uniquement** dans `ajuda-termos` (art. 22). Pas de page autonome ni de lien footer dédié. | idem |
| B9 | **`Política de Cookies` : page inexistante** | Bandeau de consentement présent (`public/assets/vue/cookies.js`) mais **aucune page de politique cookies** et aucun lien. De plus le bandeau : « accepter » = 30 j, « rejeter » / « sélection » = **2 j seulement** (re-sollicitation quasi-immédiate → conformité RGPD/CNPD douteuse). | `cookies.js`, footer |

### 🟠 MAJEUR

| # | Problème | Détail |
|---|---|---|
| M1 | **Page `Contactos` absente** | Les coordonnées existent, mais éclatées : footer (`layouts/app.blade.php` l.1493-1497), `pages/lojas`, `pages/apoio-cliente`. Pas de page `/contactos` unique. Téléphone `+351 912 026 453` vient d'être ajouté (footer, apoio-cliente, lojas, bouton WhatsApp) mais **pas encore sur une page Contactos dédiée**. |
| M2 | **Lien `Livro de Reclamações` absent du footer** | Présent une seule fois, en texte, dans `ajuda-termos` (l.272). Obligation légale PT : lien visible sur **toutes** les pages. |
| M3 | **Lien RAL absent du footer global** | La page `/ajuda/resolucao-alternativa-litigios` existe et est liée en footer — OK — mais vérifier que l'entité compétente selon la morada (Vendas Novas / Évora → **CIMAAL**, Centro de Arbitragem de Conflitos de Consumo do Algarve n'est pas la bonne ; la page liste Lisboa, Coimbra, Ave, CNIACC). Manque le **CNIACC comme entité par défaut** clairement désigné + le lien direct. |
| M4 | **Titres produit en capitales** | 3 698 produits `name = upper(name)` (dont beaucoup sont des codes courts type `BAR63`, mais aussi des libellés entiers en MAJ). GMC pénalise les titres tout-capitales. À normaliser en casse de phrase dans le flux (auto-corrigeable). |
| M5 | **`google_product_category` en texte libre, mapping approximatif** | `FeedController::googleCategory()` renvoie un libellé anglais deviné par mots-clés, `default = 'Home & Garden > Furniture'`. Acceptable mais imprécis ; risque « catégorie incohérente » sur les cas `default`. Préférer les **ID numériques** de la taxonomie officielle. |
| M6 | **Descriptions = `strip_tags` du HTML source** | Risque de résidus (entités, listes, mentions de prix, URL, téléphone) dans `short_description_html` / `long_description_html`. À auditer produit par produit et nettoyer (pas de HTML, pas de lien, pas d'email/tél, pas de prix). |
| M7 | **`link` du flux non canonique / HTTP non vérifié** | `url('/'.$p->slug)` dépend de `APP_URL` (= `http://localhost:8000` en `.env`). En prod il faut une **URL absolue https + domaine réel**, et chaque `link` doit renvoyer 200 (certains slugs peuvent être en 404 si le produit est dépublié). |
| M8 | **`APP_ENV=local`, `APP_DEBUG=true`** | En production : `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<domaine>`. Sinon pages d'erreur verbeuses = motif de refus « site non fiable ». |
| M9 | **Checkout force méthode livraison + paiement** | `CheckoutController::place()` ignore tout choix : `shipMethod='LEVANTAMENTO'`, `payMethod='MULTIBANCO'`. La constante `SHIPPING['ENTREGA']` (45 €) existe mais **n'est jamais proposée**. Incohérent avec une politique d'envois « livraison partout au Portugal ». |
| M10 | **`public/storage` : symlink cassé** | `public/storage` → `/var/www/html/sofas/sofas/storage/app/public` : **cible inexistante** (dossier jamais présent). Sans impact sur les images produit (servies depuis `public/assets`, `public/images`, `public/media`), mais à recréer proprement (`php artisan storage:link`) ou supprimer. |

### 🟡 MINEUR

| # | Problème | Détail |
|---|---|---|
| m1 | `robots.txt` = `User-agent: *` / `Disallow:` → tout autorisé (OK pour Googlebot / Googlebot-Image), mais **pas de `Sitemap:`** déclaré et **pas de sitemap.xml**. |
| m2 | `.htaccess` racine force HTTPS (301) — OK — mais redirige aussi `^$ public/` : à confirmer selon l'hébergement (vhost pointant déjà sur `public/` = double préfixe possible). |
| m3 | `favicon.ico` = fichier **vide** (0 octet). |
| m4 | Icônes réseaux sociaux du footer pointent toutes vers `href="#"` (liens morts). |
| m5 | Footer : bandeau « Preços válidos de 01/08/2026 a 31/08/2026 » **daté/expiré**, à rendre dynamique ou générique. |
| m6 | `additional_image_link` limité à 10 dans le flux (OK, max Google = 10) mais l'ordre dépend de `product_images.position` — vérifier qu'aucune image secondaire n'est un visuel « ambiance » sans le produit. |
| m7 | `condition` toujours `new` en dur — OK ici (mobilier neuf), à garder. |
| m8 | `ffmpeg_...deb` (2 Mo) et `fetch_video.log` traînent à la racine du repo. |
| m9 | Dossier dupliqué `sofas/` (ancienne version) : **supprimé pendant cette session** (`git rm -r sofas/`), suppression en attente de commit. |
| m10 | `composer.json` exige `laravel/framework ^13.17` alors que 12.43.1 est installé + dépendance `laravel/pao` (typo probable de `laravel/pail`/`pint`) — à fiabiliser. |

---

## 3. Pages obligatoires (droit PT + GMC) — état

| Page | État | Ce qui manque |
|---|---|---|
| Termos e Condições | ✅ existe (`/ajuda/termos-e-condicoes`, liée footer) | Contenu dense OK. Coquilles dans `ajuda-termos` l.26 (« pessoa coletiva n.ºpessoa coletiva n.º… 504074571504074571 »). Références RAL à recentrer sur CNIACC. |
| Política de Privacidade (RGPD) | ✅ existe (`/ajuda/politica-privacidade`, liée footer) | Vérifier : identité du responsable de traitement, base légale, DPO/contact, durées de conservation, droits, transferts. Actuellement synthétique. |
| Política de Cookies | ❌ **manquante** | Page dédiée + tableau des cookies (nom, finalité, durée, tiers) + lien footer. Bandeau existant à revoir (durée du refus = 2 j). |
| Bandeau consentement cookies | ⚠️ partiel | Présent et fonctionnel (`cookies.js`), catégories marketing/preferences/estatísticas. Mais : refus stocké 2 j seulement ; pas de lien « política de cookies » dans le bandeau ; pas de blocage réel des scripts tiers avant consentement (à vérifier — GTM `GTM-NB25HK7` est chargé). |
| Política de Envios e Entregas | ❌ **manquante** | Page dédiée avec : zone = Portugal uniquement ; préparation 1-2 j ouvrés ; transport 1-2 j ouvrés ; **délai total « Entrega em 2 a 4 dias úteis »** ; grille de frais (gratuit < 800 € ; au-dessus : règle à confirmer — voir question ci-dessous) ; lien footer. |
| Política de Devoluções e Reembolsos | ⚠️ incomplète | Contenu présent dans Termos art. 22 (livre resolução 14 j, DL 24/2014). **Pas de page autonome** ni de lien footer. Manque : formulaire/modèle de livre resolução, adresse de retour, délai de remboursement (14 j), qui paie le retour. |
| Contactos | ⚠️ éclatée | Raison sociale ✅ (DFP Interiores, Unipessoal Lda), NIF ✅ (504074571), morada ✅ (Rua José Francisco Fragoso, n.º 45, 7080-035 Vendas Novas), email ✅ (contacto@dfpinteriores.com), téléphone ✅ (+351 912 026 453, ajouté). **Manque : page `/contactos` unique + lien footer.** |
| Livro de Reclamações eletrónico | ⚠️ | Lien présent 1× dans Termos. **Manque lien footer global** vers `https://www.livroreclamacoes.pt/`. |
| Plateforme RAL / entité résolution litiges | ✅ page existe et liée footer | Recentrer sur l'entité compétente + lien plateforme UE ODR (`https://ec.europa.eu/consumers/odr`). |
| Méthodes de paiement affichées | ✅ footer (`layouts/app.blade.php` l.1522-1530 : MB, MBWay, Visa, Cofidis Pay, Scalapay) | Cohérence à vérifier avec ce qui est réellement encaissé (checkout ne propose que MULTIBANCO). |

---

## 4. Points nécessitant une décision avant l'Étape 2

1. **Frais de livraison au-dessus de 800 € (BLOQUANT pour la config livraison).**
   La consigne : gratuit < 800 €, « règle de frais existante du site » au-dessus.
   La seule règle existante dans le code est `SHIPPING['ENTREGA'] = 45,00 € forfait`
   (livraison domicile Portugal Continental), **mais elle n'est jamais proposée au
   checkout** (forcé « levantamento gratuito »).
   → Que doit faire le site pour une commande ≥ 800 € : appliquer **45 € forfait** ?
   Un autre montant ? Rester gratuit ? Livraison « sous devis » ?
   → Faut-il **réactiver le choix « Entrega ao domicílio »** au checkout (aujourd'hui
   désactivé) ou garder uniquement le retrait en magasin + livraison gérée hors-ligne ?

2. **Marque (`brand`) pour les produits sans marque fabricant réelle.**
   Confirmer : remplacer `Feira dos Sofás` par **`DFP Interiores`** partout où il n'y a
   pas de marque fabricant (COLMED, MOLAFLEX, EMMA, TEMPUR, PIKOLIN, SEALY, etc. étant
   conservées) ? C'est l'option recommandée et acceptée par Google.

3. **Domaine de production + HTTPS.**
   `APP_URL` doit être l'URL réelle (`https://dfpinteriores.com` ?) pour générer des
   `link` / `image_link` absolus valides. Confirmer le domaine.

4. **Portée exacte de l'Étape 2.**
   Les corrections « site » (checkout, pages légales, footer, `.env` prod, casse des
   titres en base) touchent beaucoup de fichiers. Confirmer qu'on procède maintenant,
   par commits atomiques, dans l'ordre du brief.

> Données d'entreprise trouvées dans le code (aucune invention nécessaire) :
> - Raison sociale : **DFP Interiores, Unipessoal Lda**
> - NIF/NIPC : **504074571**
> - Capital social : **500.000,00 €**
> - Morada : **Rua José Francisco Fragoso, n.º 45, 7080-035 Vendas Novas** (Évora)
> - Email : **contacto@dfpinteriores.com**
> - Téléphone : **+351 912 026 453**
> - Registo comercial : Conservatória de Vendas Novas, n.º 504074571
>
> Manquant / à confirmer : **domaine de production**, **règle de frais > 800 €**,
> éventuel **numéro de registo/alvará spécifique** si requis sur la page Contactos.
