# Rapport final — Conformité Google Merchant Center (DFP Interiores)

Date : 2026-09-11 · Marché : Portugal uniquement

Décisions client appliquées : livraison **gratuite pour tout le Portugal** (y compris
> 800 €) · marque de repli = **DFP Interiores** · domaine = **https://dfpinteriores.com**.

---

## 1. Flux produit

| Élément | Valeur |
|---|---|
| Fichier principal | `public/feeds/google-merchant.xml` — URL : `https://dfpinteriores.com/feeds/google-merchant.xml` |
| Copie (URL demandée) | `public/dfpinteriores-gmc-conforme.xml` — URL : `https://dfpinteriores.com/dfpinteriores-gmc-conforme.xml` |
| Secours TSV | `public/feeds/google-merchant.csv` |
| Format | RSS 2.0, `xmlns:g="http://base.google.com/ns/1.0"`, UTF-8, CDATA sur les champs texte |
| **Nombre d'`<item>`** | **980** (exactement la cible) |
| Régénération | `php artisan feed:build` + planifié tous les jours à 04:00 (`routes/console.php`, `Schedule::command('feed:build')`) |

### Attributs par article
`g:id`, `title` (≤150, casse normalisée), `description` (≤5000, sans HTML/lien/email/téléphone/prix),
`link` (absolu https), `g:image_link` + jusqu'à 10 `g:additional_image_link`,
`g:availability` (`in_stock`), `g:condition` (`new`), `g:price` (`"199.00 EUR"`),
`g:sale_price` si solde (472 articles), `g:brand`, `g:gtin` **ou** `g:mpn`,
`g:identifier_exists` (`no` — aucun GTIN au catalogue, MPN=SKU + marque fournis),
`g:google_product_category` (ID numériques taxonomie officielle),
`g:product_type` (fil d'Ariane), `g:item_group_id` (si variantes),
`g:shipping` (`PT` / `0.00 EUR`), `g:min/max_handling_time` (1/2), `g:min/max_transit_time` (1/2).

### Vérifications passées (`php artisan feed:build` + contrôles)
- XML **well-formed**, namespace `g:` correct, **980 `<item>`** comptés.
- **0** prix hors format, **0** article < 80 €.
- **980 / 980** : prix du flux **identique** au prix en base (contrôle exhaustif, pas seulement 30).
- **980 / 980** : fichier `image_link` présent sur le disque ; échantillon de 40 URL produit + 40 images → **HTTP 200** en local.
- **0** `<g:id>` en double, **0** `image_link` vide, **0** `google_product_category` vide, **0** `shipping` sans `PT`.
- Pages `/feeds/google-merchant.xml`, `/dfpinteriores-gmc-conforme.xml`, `/sitemap.xml`, `/robots.txt` → **200**.

> Contrôle HTTP 200 **en conditions réelles** (domaine de production) à relancer après
> mise en ligne : `php artisan feed:build` puis test des `link`/`image_link` depuis
> `https://dfpinteriores.com`. En local, la vérification a été faite en substituant l'hôte.

---

## 2. Sélection (traçabilité : `FEED-SELECTION.csv`, 5 280 lignes)

| Statut | Nombre |
|---|---|
| **INCLUS dans le flux** | **980** |
| Exclus | 4 300 |

### Motifs d'exclusion
| Motif | Nombre |
|---|---|
| Prix < 80,00 € | 2 380 |
| Aucune image | 1 495 |
| Hors stock | 264 |
| Éligibles non inclus (au-delà de la cible 980) | 159 |
| Catégorie restreinte Google | 3 |
| Prix nul / absent | 2 |

> **159 produits éligibles non inclus** : conformes mais au-delà de 980. Listés dans
> `FEED-SELECTION.csv` (statut `EXCLU`, motif « éligible non inclus »), triés par rang de
> qualité, prêts à être ajoutés si la cible est relevée.
>
> Aucune compensation n'a été nécessaire : 1 139 produits éligibles pour 980 places.

### Niveau de risque (parmi les 980 inclus)
| Niveau | Nombre | Traitement |
|---|---|---|
| OK | 46 | inclus tels quels |
| RISQUE MOYEN | 934 | **corrigé automatiquement** puis inclus |
| RISQUE ÉLEVÉ | 0 dans le flux | exclus (3 catégories restreintes) |

### Corrections automatiques appliquées au flux
| Correction | Occurrences |
|---|---|
| Description nettoyée (HTML, liens, emails, téléphones, mentions de prix retirés ; repli généré si vide) | 934 |
| Titre normalisé (sortie des MAJUSCULES intégrales) | 40 |
| Marque corrigée (« Feira dos Sofás » → « DFP Interiores ») | via `catalog:normalize-brands` en base : **4 974 produits** |

Catégories restreintes exclues : `COPO GIN 730CC` (verre à alcool), `CAIXA MEDICAMENTOS`
(boîte à médicaments), `PISTOLA C/BALAS SK` (réplique/jouet à billes).

### Marques présentes dans le flux
DFP Interiores 918 · MOLAFLEX 16 · ECOSLEEP 12 · MD 11 · PIKOLIN 7 · COLMED 7 · EMMA 4 ·
TEMPUR 2 · SLEEP PRO 1 · SEALY 1 · SANTI D`ITALIA 1.

---

## 3. Corrections appliquées au site (Étape 2)

### Pages légales — créées (portugais européen, mentions légales PT réelles)
| Page | URL | Contenu |
|---|---|---|
| Política de Envios e Entregas | `/ajuda/politica-de-envios` | Portugal uniquement · préparation 1-2 j · transport 1-2 j · **« Entrega em 2 a 4 dias úteis (preparação 1-2 dias + transporte 1-2 dias) »** · **envio gratuito para todo o Portugal** |
| Política de Devoluções e Reembolsos | `/ajuda/politica-de-devolucoes` | Livre resolução 14 dias (DL 24/2014) · conditions · qui paie le retour · remboursement 14 j · garantie légale 3 ans (DL 84/2021) · modèle de livre resolução · Livro de Reclamações + RAL + ODR |
| Política de Cookies | `/ajuda/politica-de-cookies` | Base légale (Lei 41/2004 + RGPD) · tableau des 4 catégories (finalité, consentement, durée) · cookies tiers · gestion/suppression |
| Contactos | `/contactos` | Raison sociale, NIF 504074571, capital social, sede, e-mail, **téléphone +351 912 026 453**, horaire, Google Maps, Livro de Reclamações, RAL, ODR |

Routage : `routes/web.php` + `StoreController::page()` (map de clés).

### Footer (`resources/views/layouts/app.blade.php`) — présent sur **toutes** les pages
Ajout des liens : Contactos · Política de Cookies · Política de Envios e Entregas ·
Política de Devoluções e Reembolsos · **Livro de Reclamações** (`livroreclamacoes.pt`) ·
**Resolução de Litígios em Linha (UE)** (`ec.europa.eu/consumers/odr`).
Bandeau de prix daté/expiré (« válidos de 01/08 a 31/08/2026 ») rendu générique.

### Livraison & délais visibles avant paiement
- `pages/checkout.blade.php` : ligne « Portes de envio : Grátis » + bloc
  « Envio gratuito para todo o Portugal / Entrega em 2 a 4 dias úteis (preparação 1-2 dias
  + transporte 1-2 dias) / Pagamento: Referência Multibanco ».
- `pages/carrinho.blade.php` : « Portes de envio : Grátis » + même mention de délai
  (remplace « Calculados no checkout »).
- `product.blade.php` : mention du délai total + « Envio grátis para todo o Portugal »
  sous le prazo de entrega.

### Marque en base
Nouvelle commande `php artisan catalog:normalize-brands` (idempotente, `--dry-run`
disponible). Exécutée : **4 974 / 5 280** produits passés de « Feira dos Sofás » à
« DFP Interiores » ; 306 marques fabricant réelles conservées.

### Consentement cookies (`public/assets/vue/cookies.js`)
Durée de mémorisation du choix (accepter / refuser / personnaliser) portée de **2-30 jours
à 180 jours** — évite la re-sollicitation quasi-immédiate en cas de refus (conformité CNPD).

### robots.txt + sitemap
`public/robots.txt` réécrit : `Allow: /`, `Googlebot` et `Googlebot-Image` explicitement
autorisés, zones privées (`/carrinho`, `/cliente`, `/wishlist`, `/procura`) exclues,
`Sitemap:` déclaré. Nouvelle route `/sitemap.xml` (pages institutionnelles + 1 918 fiches
produit avec image).

### Configuration (`.env.example` — template de production ; `.env` local inchangé)
`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://dfpinteriores.com`,
`FEED_BASE_URL=https://dfpinteriores.com`. Nouveau `config/feed.php` (base URL, prix
minimum, cible, délais, marques).

### Coquilles corrigées
`ajuda-termos` / `ajuda-recrutamento` : « pessoa coletiva n.º…504074571504074571… » et
« NIFNIF 504074571… » → texte propre.

### Nettoyage
Dossier dupliqué obsolète `sofas/` supprimé du dépôt (`git rm -r sofas/`).

---

## 4. Points nécessitant une décision / action de votre part

1. **Mise en production** : appliquer `.env` de production (voir `.env.example`),
   `php artisan config:cache route:cache view:cache`, puis
   `php artisan feed:build`, et **relancer la vérification HTTP 200** des `link` et
   `image_link` depuis le domaine réel.
2. **Cron** : activer `php artisan schedule:run` (crontab minute) sur le serveur pour la
   régénération automatique du flux ; relancer aussi `feed:build` après chaque
   `catalog:import`.
3. **Categoria Google trop générique** : 284 des 980 articles retombent sur
   `Furniture` (436) faute de catégorie source exploitable. Améliorable en enrichissant
   `products.category_name` / `breadcrumb` à l'import (n'empêche pas la validation).
4. **CSRF désactivé globalement** : `bootstrap/app.php` exclut `'*'` du CSRF (hérité du
   JS catalogue). Sans impact Merchant Center mais à restreindre aux routes du JS
   officiel pour la sécurité du checkout.
5. **Livraison au domicile** : le checkout force « levantamento / Multibanco ». Cohérent
   avec « envio gratuito » mais si vous voulez proposer explicitement la livraison à
   domicile au client, il faudra réactiver le choix (constante `SHIPPING` de
   `CheckoutController`).
6. **Icônes réseaux sociaux** du footer : liens `href="#"` (à renseigner).
7. **`favicon.ico`** : fichier vide (0 octet) à remplacer.
8. **Politique de Privacidade** existante : synthétique — à compléter (responsable de
   traitement, bases légales, durées de conservation, sous-traitants, DPO) si vous visez
   une conformité RGPD exhaustive.
9. **Test** `tests/Feature/ExampleTest.php` échoue (pré-existant, sans lien avec ces
   modifications) : la base de test `:memory:` n'a pas les tables `categories`/`products`
   du catalogue scrappé. À adapter (seeder de test) ou retirer.

## 5. Données d'entreprise utilisées (aucune inventée)
DFP Interiores, Unipessoal Lda · NIF/NIPC **504074571** · Capital social 500.000,00 € ·
Rua José Francisco Fragoso, n.º 45, 7080-035 Vendas Novas · contacto@dfpinteriores.com ·
**+351 912 026 453** · Conservatória do Registo Comercial de Vendas Novas n.º 504074571.
Aucun placeholder `[[À COMPLÉTER]]` restant.
