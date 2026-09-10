<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Boutique : rendu 100% Blade (layouts/app + vues home, category, product,
 * pages/*). Les données viennent de la base ; le CSS/JS officiel est servi tel quel.
 */
class StoreController extends Controller
{
    /** Palette de secours pour les pastilles couleur sans code hexa. */
    private const PT_COLORS = [
        'azul' => '#0000FF', 'bege' => '#F5F5DC', 'branco' => '#FFFFFF', 'castanho' => '#6c3b2a',
        'cinza' => '#808080', 'cinzento' => '#808080', 'preto' => '#000000', 'rosa' => '#FFC0CB',
        'verde' => '#008000', 'vermelho' => '#FF0000', 'amarelo' => '#FFD700', 'laranja' => '#FFA500',
        'natural' => '#D2B48C', 'carvalho' => '#C19A6B', 'nogueira' => '#5C4033', 'multicor' => '#DCDCDC',
        'dourado' => '#D4AF37', 'prateado' => '#C0C0C0', 'terracota' => '#E2725B', 'creme' => '#FFFDD0',
        'taupe' => '#B38B6D', 'antracite' => '#293133', 'mostarda' => '#E1AD01',
    ];



    // ------------------------------------------------------------------ pages

    public function home()
    {
        // Bloc « Descontos até 70% » : 3 onglets (comme le site officiel).
        $descTabs = collect([
            ['id' => 'sofas', 'label' => 'Sofás', 'slug' => 'sofas'],
            ['id' => 'mobiliario', 'label' => 'Mobiliário', 'slug' => 'mobiliario'],
            ['id' => 'descanso', 'label' => 'Descanso', 'slug' => 'descanso'],
        ])->map(function (array $t) {
            $cat = Category::where('slug', $t['slug'])->first();
            $ids = $cat ? $cat->children()->pluck('id')->push($cat->id) : collect();

            $t['products'] = $this->baseListingQuery('descontos70')
                ->when($ids->isNotEmpty(), fn ($q) => $q->where(fn ($w) => $w
                    ->whereIn('category_id', $ids)
                    ->orWhereHas('categories', fn ($c) => $c->whereIn('categories.id', $ids))))
                ->orderByRaw('COALESCE(views_20d, 0) DESC')->orderByDesc('id')->limit(12)->get();

            return $t;
        })->filter(fn ($t) => $t['products']->isNotEmpty())->values();

        // Hero : 6 slides, chacune tire une image au hasard dans son lot
        // (public/media/banners/hero-<slide>-*.jpg) -> visuel différent à chaque chargement.
        $slides = [
            ['n' => 1, 'href' => '/descontos70',            'h2' => 'Descontos até 70%',      'p' => 'Em sofás, mobiliário e descanso',                'cta' => 'Ver ofertas'],
            ['n' => 2, 'href' => '/descanso',               'h2' => 'Quarto completo',        'p' => 'Camas, colchões e roupeiros para o seu descanso', 'cta' => 'Descobrir'],
            ['n' => 3, 'href' => '/salas-de-jantar',        'h2' => 'Salas de jantar',        'p' => 'Mesas, cadeiras e aparadores com estilo',        'cta' => 'Ver coleção'],
            ['n' => 4, 'href' => '/decoracao',              'h2' => 'Decoração',              'p' => 'Os detalhes que fazem a sua casa',               'cta' => 'Explorar'],
            ['n' => 5, 'href' => '/roupeiros',              'h2' => 'Arrumação inteligente',  'p' => 'Roupeiros e closets à sua medida',               'cta' => 'Ver roupeiros'],
            ['n' => 6, 'href' => '/mobiliario-de-exterior', 'h2' => 'Mobiliário de exterior', 'p' => 'Aproveite o jardim e a varanda',                 'cta' => 'Ver exterior'],
        ];
        $hero = array_map(function ($s) {
            $pool = glob(public_path("media/banners/hero-{$s['n']}-*.jpg"))
                ?: glob(public_path("media/banners/hero-{$s['n']}.jpg"));
            $s['img'] = $pool ? '/media/banners/'.basename($pool[array_rand($pool)]) : '/media/banners/hero-1.jpg';
            return $s;
        }, $slides);

        // Vidéo : une au hasard parmi public/videos/promo-*.mp4 (repli sur promo.mp4).
        $vids = glob(public_path('videos/promo-*.mp4'));
        $heroVideo = $vids ? '/videos/'.basename($vids[array_rand($vids)]) : '/videos/promo.mp4';

        return view('home', compact('descTabs', 'hero', 'heroVideo'));
    }

    /** Pages institutionnelles -> vue Blade dans resources/views/pages/. */
    public function page(string $key)
    {
        // clé de route -> vue Blade dans resources/views/pages/
        $view = [
            'lojas'                                 => 'pages.lojas',
            'carrinho'                              => 'pages.carrinho',
            'wishlist'                              => 'pages.wishlist',
            'cliente'                               => 'pages.cliente',
            'ajuda__termos-e-condicoes'             => 'pages.ajuda-termos',
            'ajuda__politica-privacidade'           => 'pages.ajuda-privacidade',
            'ajuda__recrutamento'                   => 'pages.ajuda-recrutamento',
            'ajuda__resolucao-alternativa-litigios' => 'pages.ajuda-ral',
        ][$key] ?? null;

        abort_unless($view && view()->exists($view), 404);

        return view($view);
    }

    /** Listes de mise en avant (Descontos até 70%, Artigos Exclusivos Online). */
    public function listing(string $key)
    {
        [$title, $h1] = match ($key) {
            'descontos70'             => ['Descontos até 70% | DFP Interiores', 'Descontos até 70%'],
            'artigosExclusivosOnline' => ['Artigos Exclusivos Online | DFP Interiores', 'Artigos Exclusivos Online'],
            default                   => abort(404),
        };

        if ($json = $this->listingJson($key)) {
            return $json;
        }

        $products = $this->listingQuery($key)->paginate(24)->withQueryString();

        return $this->fillCategory($title, $h1, $products, $key);
    }

    /**
     * Réponse JSON attendue par le JS officiel (ListaArtigos() de functions.js) :
     * POST sur l'URL courante, filtres en query string, retour { html, page, pages, dataLayer }.
     * Renvoie null si la requête n'est pas un appel AJAX/POST.
     */
    private function listingJson(string $key)
    {
        $r = request();
        if (! $r->isMethod('post') && ! $r->ajax() && ! $r->wantsJson()) {
            return null;
        }

        $page = max(1, (int) $r->input('p', $r->input('page', 1)));
        $products = $this->listingQuery($key)->paginate(24, ['*'], 'page', $page);

        return response()->json([
            'html'      => $this->grid($products) ?: '',
            'page'      => $products->currentPage(),
            'pages'     => $products->lastPage(),
            'total'     => $products->total(),
            'artigos'   => $products->count(),
            'dataLayer' => '{"event":"view_item_list"}',
        ]);
    }

    /** Réponse JSON pour le « Ver mais artigos » / défilement infini. */
    public function more(Request $request, string $key)
    {
        $key = urldecode($key);
        $products = $this->listingQuery($key)->paginate(24, ['*'], 'page', max(1, (int) $request->get('page', 2)));

        return response()->json([
            'html'  => $this->grid($products),
            'page'  => $products->currentPage(),
            'pages' => $products->lastPage(),
            'total' => $products->total(),
        ]);
    }

    /** Construit la requête produits pour une clé de liste (slug catégorie ou page spéciale). */
    /** Requête « de base » d'une liste, sans les filtres de la barre latérale. */
    private function baseListingQuery(string $key)
    {
        if ($key === 'descontos70') {
            // « Descontos até 70% » est une campagne curée côté source (aucun flag
            // exploitable dans le scrape). On reprend donc la liste exacte du site
            // officiel, figée dans database/data/descontos70.json (ids produits).
            static $ids = null;
            if ($ids === null) {
                $file = database_path('data/descontos70.json');
                $ids = is_file($file) ? (array) json_decode((string) file_get_contents($file), true) : [];
            }

            $q = Product::query()->with('images');

            if ($ids) {
                return $q->whereIn('id', $ids);
            }

            // filet de secours si le fichier est absent
            return $q->where(fn ($w) => $w
                ->whereColumn('price_before', '>', 'price')
                ->orWhereNotNull('flags->saldos')
                ->orWhere('flags->outlet', 1)
                ->orWhere('flags->desconto70', '!=', '0'));
        }

        if ($key === 'artigosExclusivosOnline') {
            // flags.exclusivo_site vaut "Y" / "N" dans les données scrapées
            return Product::query()->with('images')->where('flags->exclusivo_site', 'Y');
        }

        if (str_starts_with($key, 'procura:')) {
            $term = trim(substr($key, 8));
            $q = Product::query()->with('images');
            if ($term !== '') {
                $q->where(fn ($s) => $s
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('ean', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%"));
            }
            return $q;
        }

        $category = Category::where('slug', $key)->firstOrFail();
        $ids = $category->children()->pluck('id')->push($category->id);

        return Product::query()->with('images')->where(fn ($q) => $q
            ->whereIn('category_id', $ids)
            ->orWhereHas('categories', fn ($c) => $c->whereIn('categories.id', $ids)));
    }

    /** Requête finale : base + filtres (marca, preço, dimensões, cores) + tri. */
    private function listingQuery(string $key)
    {
        $r = request();
        $q = $this->baseListingQuery($key);

        // le JS officiel envoie des valeurs jointes par virgule (data-group), notre
        // fetch peut envoyer des tableaux : on accepte les deux.
        $csv = function ($v): array {
            if (is_array($v)) {
                return array_values(array_filter($v, fn ($x) => $x !== '' && $x !== null));
            }
            return is_string($v) && $v !== '' ? array_filter(explode(',', $v), 'strlen') : [];
        };

        $brands = $csv($r->input('marcas', $r->input('m')));
        if ($brands) {
            $q->whereIn('brand', $brands);
        }

        foreach (['preco' => 'price', 'largura' => '$.largura', 'altura' => '$.altura', 'comprimento' => '$.comprimento'] as $param => $col) {
            $range = $r->input($param);
            if (! is_string($range) || ! str_contains($range, ',')) {
                continue;
            }
            [$min, $max] = array_map('floatval', explode(',', $range, 2));
            if ($col === 'price') {
                $q->whereBetween('price', [$min, $max]);
            } else {
                $q->whereRaw("CAST(json_extract(dimensions, '{$col}') AS REAL) BETWEEN ? AND ?", [$min, $max]);
            }
        }

        $colors = $csv($r->input('cores'));
        if ($colors) {
            $q->where(function ($sub) use ($colors) {
                foreach ($colors as $c) {
                    $sub->orWhere('variations', 'like', '%"color":"'.$c.'"%')
                        ->orWhere('variations', 'like', '%"name":"'.$c.'"%');
                }
            });
        }

        // Tri par défaut = ordre du site officiel : popularité (vues sur 20 jours) décroissante.
        $popular = fn ($qq) => $qq
            ->orderByRaw('COALESCE(views_20d, 0) DESC')
            ->orderByDesc('id');

        return match ($r->input('sort')) {
            'precoasc'  => $q->orderBy('price'),
            'precodesc' => $q->orderByDesc('price'),
            'recentes'  => $q->orderByDesc('id'),
            'populares' => $popular($q),
            default     => $popular($q),
        };
    }

    /** Options de la barre de filtres pour une liste (calculées sur la requête de base). */
    private function categoryFilters(string $key): array
    {
        try {
            $base = $this->baseListingQuery($key);
        } catch (\Throwable $e) {
            return [];
        }

        // Le filtre « Marca » ne liste que les vraies marques fabricant.
        // La marque « maison » (enseigne / sans marque) est exclue, comme sur le site officiel.
        $houseBrands = ['Feira dos Sofás', 'Feira dos Sofas', config('app.name'), 'DFP Interiores'];

        $brands = (clone $base)->whereNotNull('brand')->where('brand', '!=', '')
            ->whereNotIn('brand', $houseBrands)
            ->where('brand', 'not like', '%feira dos sof%')
            ->groupBy('brand')->selectRaw('brand, count(*) as c')
            ->orderBy('brand')->pluck('c', 'brand')->all();

        $priceMin = (int) floor((float) (clone $base)->min('price'));
        $priceMax = (int) ceil((float) (clone $base)->max('price'));

        $dim = [];
        foreach (['largura', 'altura', 'comprimento'] as $d) {
            $vals = (clone $base)->selectRaw("CAST(json_extract(dimensions, '$.{$d}') AS REAL) as v")
                ->whereRaw("v is not null")->pluck('v');
            $dim[$d] = [(int) floor((float) $vals->min()), (int) ceil((float) $vals->max())];
        }

        $colors = [];
        (clone $base)->whereNotNull('variations')->limit(600)->pluck('variations')->each(function ($v) use (&$colors) {
            $v = is_array($v) ? $v : json_decode((string) $v, true);
            foreach (data_get($v, 'variations', []) as $row) {
                foreach ($row as $opt) {
                    $name = data_get($opt, 'color') ?: data_get($opt, 'name');
                    $code = data_get($opt, 'colorcode');
                    if ($name && $code) {
                        $colors[$name] = $code;
                    }
                }
            }
        });
        ksort($colors);

        return compact('brands', 'priceMin', 'priceMax', 'dim', 'colors');
    }

    /** Construit le HTML de la barre de filtres (@@FILTERS@@). */
    private function filtersHtml(string $key): string
    {
        $f = $this->categoryFilters($key);
        if (! $f) {
            return '';
        }
        $r = request();
        $checked = fn ($group, $val) => in_array($val, (array) $r->input($group, []), true) ? ' checked' : '';

        // --- Marca : markup identique au site (data-group='marcas', name='m') ---
        $html = "<div class='BlocoFiltro categoires'>\n<h2>Marca</h2>\n";
        $i = 0;
        foreach ($f['brands'] as $brand => $count) {
            $id = 'marca'.$i++;
            $html .= "<input class='Filtro' data-group='marcas' id='{$id}' type='checkbox' value='".e($brand)."' name='m'".$checked('marcas', $brand).">"
                ." <label for=\"{$id}\">".e($brand)." ({$count})</label>\n";
        }
        $html .= "</div>\n";

        // --- Sliders prix/dimensions : markup EXACT du site (input text + data-slider-*) ---
        $slider = function ($cls, $name, $group, $title, $min, $max, $unit) {
            $max = max($max, $min + 1);
            return "<div class='BlocoFiltro'>\n<h2>".e($title)."</h2>\n"
                ."<div class='SliderHolder Slider{$cls}Holder'>\n"
                ."<input class=\"Slider{$cls} Slider Filtro\" name=\"{$name}\" data-group='{$group}' type=\"text\""
                ." data-slider-min=\"{$min}\" data-slider-max=\"{$max}\" data-slider-step=\"1\""
                ." data-slider-value=\"[{$min},{$max}]\" data-unit='{$unit}' />\n"
                ."<small style='display: block;' class='SliderFooter Slider{$cls}Valores'> {$min}{$unit} - {$max}{$unit} </small>\n"
                ."</div>\n</div>\n";
        };
        $html .= $slider('Preco', 'preco', 'preco', 'Preço', $f['priceMin'], $f['priceMax'], '€');
        $html .= $slider('Largura', 'largura', 'largura', 'Largura (cm)', $f['dim']['largura'][0], $f['dim']['largura'][1], 'cm');
        $html .= $slider('Altura', 'altura', 'altura', 'Altura (cm)', $f['dim']['altura'][0], $f['dim']['altura'][1], 'cm');
        $html .= $slider('Comprimento', 'comprimento', 'comprimento', 'Comprimento (cm)', $f['dim']['comprimento'][0], $f['dim']['comprimento'][1], 'cm');

        // --- Cores : toujours affiché (comme le site). Couleurs de la catégorie,
        //     sinon palette par défaut du site. ---
        $colors = $f['colors'] ?: [
            'Azul' => '#0000FF', 'Bege' => '#F5F5DC', 'Branco' => '#FFFFFF',
            'Castanho' => '#6c3b2a', 'Cinza' => '#808080', 'Preto' => '#000000',
            'Rosa' => '#FFC0CB', 'Verde' => '#008000',
        ];
        $html .= "<div class='BlocoFiltro cores'>\n<h2>Cores</h2>\n";
        $i = 0;
        foreach ($colors as $name => $code) {
            $id = 'cor'.$i++;
            $html .= "<input class='Filtro' data-group='cores' id='{$id}' type='checkbox' name='cores' data-field='cores' value='".e($name)."'".$checked('cores', $name).">"
                ." <label for='{$id}' data-id='{$i}' style='background-color: ".e($code).";' title='".e($name)."'></label>\n";
        }
        $html .= "</div>\n";

        return $html;
    }

    /** Schéma d'URL officiel : /{slug} = catégorie OU produit. */
    public function show(string $slug)
    {

    
        if ($category = Category::where('slug', $slug)->first()) {
            if ($json = $this->listingJson($slug)) {
                return $json;
            }
 
            return $this->renderCategory($category);
        }
        if ($product = Product::where('slug', $slug)->first()) {
        
        return $this->renderProduct($product);
        }
      

        // Lien vers un produit non importé (ex. « produits vus récemment » figés
        // dans le miroir) : on tente un rapprochement, sinon on bascule vers la
        // recherche plutôt qu'une 404 sèche.
        if (preg_match('/^(.*)-\d+$/', $slug, $m)) {
            $name = str_replace('-', ' ', $m[1]);
            if ($p = Product::where('slug', 'like', $m[1].'-%')->first()) {
                return redirect('/'.$p->slug, 301);
            }

            return redirect('/procura/'.rawurlencode($name));
        }

        abort(404);
    }

    public function search(Request $request, ?string $term = null)
    {
        $term = trim($term !== null ? urldecode($term) : (string) $request->get('q', ''));
        $key = 'procura:'.$term;

        if ($json = $this->listingJson($key)) {
            return $json;
        }

        $products = $this->listingQuery($key)->paginate(24)->withQueryString();

        return $this->fillCategory(
            "Procura: {$term} | DFP Interiores",
            "Resultados para “{$term}” ({$products->total()})",
            $products,
            $key
        );
    }

    /** Autocomplete de la barre de recherche : format attendu par dooSearch() (Meilisearch multi-search). */
    public function searchSuggest(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 3) {
            return response()->json(['results' => [['hits' => []], ['hits' => []]]]);
        }

        $cats = Category::where('name', 'like', "%{$q}%")->orderBy('name')->limit(8)
            ->get(['name', 'slug'])
            ->map(fn ($c) => ['name' => $c->name, 'title' => $c->name, 'link' => $c->slug, 'slug' => $c->slug])
            ->all();

        $prods = Product::query()->with('images')
            ->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%")->orWhere('brand', 'like', "%{$q}%")
            ->orderByRaw('COALESCE(views_20d, 0) DESC')->limit(8)->get()
            ->map(fn (Product $p) => [
                'name'       => $p->name,
                'title'      => $p->name,
                'link'       => $p->slug,
                'in_stock'   => (bool) $p->in_stock,
                'availability' => $p->in_stock ? 'INSTOCK' : 'OUTOFSTOCK',
                'image_link' => optional($p->images->first())->filename ? 'products/'.$p->images->first()->filename : '',
                'images'     => $p->images->map(fn ($i) => 'products/'.$i->filename)->values()->all(),
            ])->all();

        return response()->json(['results' => [['hits' => $cats], ['hits' => $prods]]]);
    }

    private function renderCategory(Category $category)
    {
        $products = $this->listingQuery($category->slug)->paginate(24)->withQueryString();

        // fil d'Ariane : chaîne des catégories parentes
        $crumbs = [];
        for ($c = $category; $c; $c = $c->parent) {
            array_unshift($crumbs, ['nome' => Str::upper($c->name), 'link' => $c->slug]);
        }
        array_pop($crumbs);   // la catégorie courante est déjà le <h1>

        return $this->fillCategory(
            Str::upper($category->name).' | DFP Interiores',
            $category->name,
            $products,
            $category->slug,
            $crumbs
        );
    }

    /** Rend la grille de cartes produits (fragment artigo.html) pour une page paginée. */
    /** Grille de cartes produits (réponse AJAX « Ver mais »). Rend le composant Blade. */
    private function grid($products): string
    {
        $out = '';
        foreach ($products as $p) {
            $out .= view('components.product-card', ['product' => $p])->render();
        }

        return $out;
    }

    /** Pastilles de couleur d'une carte produit (comme le site : <span> par variante COR). */

    private function fillCategory(string $title, string $h1, $products, ?string $key, array $breadcrumb = [])
    {
        $filtro = [
            'total'      => $products->total(),
            'page'       => $products->currentPage(),
            'perPage'    => $products->perPage(),
            'totalPages' => $products->lastPage(),
        ];

        return view('category', [
            'metaTitle'  => $title,
            'h1'         => $h1,
            'breadcrumb' => $breadcrumb,
            'products'   => $products,
            'filtro'     => $filtro,
            'filters'    => $key === null ? '' : $this->filtersHtml($key),
            'moreKey'    => $key,
            'moreScript' => $key === null ? '' : $this->moreScript($key, $filtro),
        ]);
    }

    private function moreScript(string $key, array $filtro): string
    {
        $endpoint = url('/_more/'.rawurlencode($key));
        $state = json_encode(['page' => $filtro['page'], 'pages' => $filtro['totalPages']]);

        return <<<HTML
<script>
(function(){
  var st = $state, loading = false;
  var btn  = document.querySelector('.CarregaMaisArtigos');
  var grid = document.getElementById('ListaArtigos');
  var pre  = document.getElementById('productsLoaderPreloader');
  if(!grid) return;

  function done(){ return st.page >= st.pages; }
  function sync(){ if(btn) btn.style.display = done() ? 'none' : ''; }
  sync();

  // rassemble tous les filtres de la barre latérale + le tri
  function params(){
    var p = new URLSearchParams();
    document.querySelectorAll('.RangeFiltro').forEach(function(box){
      var g = box.getAttribute('data-group');
      var mn = box.querySelector('.RangeMin'), mx = box.querySelector('.RangeMax');
      var hid = box.querySelector('.RangeVal');
      if(mn && mx){
        var v = (mn.value||mn.min) + ',' + (mx.value||mx.max);
        if(hid) hid.value = v;
        if(+mn.value !== +mn.min || +mx.value !== +mx.max) p.set(g, v);
      }
    });
    document.querySelectorAll(".Filtro[type=checkbox]:checked").forEach(function(el){
      p.append(el.getAttribute('data-group') || el.name, el.value);
    });
    var sort = document.querySelector('.Ordenar');
    if(sort && sort.value && sort.value !== 'relevante') p.set('sort', sort.value);
    return p;
  }

  function request(page, replace){
    if(loading) return;
    loading = true; if(pre) pre.style.display = 'block';
    var p = params(); p.set('page', page);
    fetch('$endpoint?' + p.toString(), {headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){ return r.json(); })
      .then(function(d){
        if(replace) grid.innerHTML = d.html || '<p class="SemArtigos">Sem artigos.</p>';
        else if(d.html) grid.insertAdjacentHTML('beforeend', d.html);
        st.page = d.page; st.pages = d.pages;
        var c = document.querySelector('.CatalogoTotal, .Total, .totalArtigos');
        if(c) c.textContent = d.total;
        loading = false; if(pre) pre.style.display = 'none';
        sync();
        if(window.jQuery && jQuery.fn.Lazy){ jQuery('.lazy:not(.Lazy)').Lazy({}); }
      })
      .catch(function(){ loading = false; if(pre) pre.style.display = 'none'; });
  }

  function reload(){ st.page = 0; request(1, true); }

  if(btn) btn.addEventListener('click', function(e){ e.preventDefault(); if(!done()) request(st.page + 1, false); });
  window.addEventListener('scroll', function(){
    if(done() || loading) return;
    if(window.innerHeight + window.scrollY >= document.body.offsetHeight - 900) request(st.page + 1, false);
  }, {passive:true});

  // Repli : si le JS officiel (ListaArtigos) n'est pas dispo, on gère nous-mêmes
  // le changement de filtre / tri.
  if (typeof window.ListaArtigos !== 'function') {
    document.addEventListener('change', function(e){
      if(e.target.classList && (e.target.classList.contains('Filtro') || e.target.classList.contains('Ordenar'))) reload();
    });
  }
})();
</script>
HTML;
    }

    private function renderProduct(Product $product)
    {
        $product->load('images', 'specs', 'category', 'extraServices', 'reviews');

        $variations = $product->variations ?: ['options' => [], 'variations' => []];
        // Le scraper met parfois une variante fictive (option = "") pour les produits
        // non personnalisables -> on la neutralise pour que le bloc « Personalização »
        // reste masqué, comme sur le site officiel.
        $opts = array_values(array_filter((array) ($variations['options'] ?? []), fn ($o) => trim((string) $o) !== ''));
        if ($opts === []) {
            // Pas de personnalisation : on masque le sélecteur (options vides) MAIS on
            // garde UNE variante « fictive » avec le prix/stock du produit — le JS
            // officiel (catalogo.js -> variacaoMaisBarata) plante si variations est vide.
            $imgs = $product->images->map(fn (ProductImage $i) => 'products/'.$i->filename)->values()->all();
            $variations = [
                'options'    => [],
                'variations' => [[
                    'image'        => ['name' => $imgs[0] ?? null],
                    'images'       => ['name' => $imgs],
                    'precoantes'   => (float) ($product->price_before ?? 0),
                    'preco'        => (float) $product->price,
                    'precopromo'   => 0,
                    'pid'          => (int) $product->id,
                    'stock'        => (int) $product->stock,
                    'stocklojas'   => 0,
                    'lojascomstock' => 0,
                    'prazo_entrega' => null,
                    'seguros'      => false,
                ]],
            ];
        }

        // Version (mtime) par nom de fichier, pour le cache-busting des URLs d'images.
        $verMap = [];
        foreach ($product->images as $pi) {
            $verMap[$pi->filename] = @filemtime(public_path($pi->path))
                ?: (optional($pi->updated_at)->timestamp ?: 1);
        }
        $verOf = fn (string $file) => $verMap[strtok($file, '?')] ?? 1;

        // Normalise les chemins d'images des variantes ("/products/x.jpg" ou "#/products/x.jpg"
        // -> "products/x.jpg") pour que le JS officiel construise "/images/70-70/products/x.jpg".
        $stripImg = function (&$node) use (&$stripImg) {
            if (is_array($node)) {
                foreach ($node as &$v) {
                    $stripImg($v);
                }
                return;
            }
            if (is_string($node) && preg_match('#(^|/)products/[^/]+\.(jpe?g|png|webp|avif)$#i', $node)) {
                $node = ltrim($node, '#/');
            }
        };
        $stripImg($variations);

        // Fichiers image réellement disponibles pour ce produit.
        $available = $product->images->pluck('filename')->flip();
        $firstImg = optional($product->images->first())->filename;

        // Pour chaque variante COR : garantir une vignette photo affichable
        // (le JS officiel affiche la photo si `thumbnail` existe, sinon la couleur).
        // Si la photo de la variante n'a pas été récupérée, on retombe sur une
        // photo dispo (image de la variante, sinon 1re photo du produit) et on
        // complète le code couleur.
        foreach ($variations['variations'] ?? [] as &$row) {
            foreach ($row as $optKey => &$opt) {
                if (! is_array($opt) || strtoupper((string) $optKey) !== 'COR') {
                    continue;
                }
                if (empty($opt['colorcode'])) {
                    $opt['colorcode'] = self::PT_COLORS[mb_strtolower(trim((string) ($opt['color'] ?? $opt['name'] ?? '')))] ?? '#cccccc';
                }

                $thumb = basename((string) ($opt['thumbnail'] ?? ''));
                if ($thumb === '' || ! $available->has($thumb)) {
                    $rowImgs = array_map(
                        fn ($n) => basename(ltrim((string) $n, '#/')),
                        (array) data_get($row, 'images.name', [])
                    );
                    $pick = collect($rowImgs)->first(fn ($f) => $available->has($f)) ?: $firstImg;
                    if ($pick) {
                        $opt['thumbnail'] = 'products/'.$pick;
                    } else {
                        unset($opt['thumbnail']);   // aucune photo -> le JS affichera la couleur
                    }
                }
            }
            unset($opt);
        }
        unset($row);

        // Cache-busting des images de variantes : "products/x.jpg" -> "products/x.jpg?v=<mtime>".
        $addVer = function (&$node) use (&$addVer, $verOf) {
            if (is_array($node)) {
                foreach ($node as $k => &$v) {
                    if ($k === 'thumbnail' && is_string($v) && str_contains($v, 'products/') && ! str_contains($v, '?v=')) {
                        $v .= '?v='.$verOf(basename($v));
                    } else {
                        $addVer($v);
                    }
                }
                return;
            }
            if (is_string($node) && preg_match('#^products/[^/?]+\.(jpe?g|png|webp|avif)$#i', $node)) {
                $node .= '?v='.$verOf(basename($node));
            }
        };
        $addVer($variations);

        // Galerie : toutes les photos du produit (chemins "products/x.jpg" sans "/" ni "#"
        // de tête, sinon le "#" est pris pour une ancre d'URL par le navigateur et
        // l'image renvoie le placeholder « sem imagem »).
        // ?v=<mtime> : cache-busting — si un fichier est (re)téléchargé, l'URL change
        // et le navigateur ne ressert plus un ancien placeholder mis en cache.
        $imgVer = fn (ProductImage $i) => 'products/'.$i->filename
            .'?v='.(@filemtime(public_path($i->path)) ?: (optional($i->updated_at)->timestamp ?: 1));
        $images = $product->images->map($imgVer)->unique()->values()->all();

        // window.artigo doit avoir la forme attendue par le JS officiel (#_Product / compiled.1.js)
        $artigo = array_merge((array) ($product->source_payload ?: []), [
            'id'              => (int) $product->id,
            'ref'             => (string) $product->sku,
            'ean'             => (string) $product->ean,
            'marca'           => (string) $product->brand,
            'nome'            => $product->name,
            'url'             => $product->slug,
            'textocurto'      => (string) $product->short_description_html,
            'textolongo'      => (string) $product->long_description_html,
            'dadoslogisticos' => (string) $product->logistic_data_html,
            'entregamontagem' => (string) $product->delivery_assembly_html,
            'categoria_nome'  => (string) $product->category_name,
            'categoria_id'    => (int) $product->category_id,
            'preco'           => (string) ($product->price ?? '0'),
            'preco_antes'     => $product->price_before ? (string) $product->price_before : 0,
            'preco_promo'     => 0,
            'iva'             => $product->vat_percent,
            'tax'             => $product->vat_percent,
            'stock'           => (int) $product->stock,
            'stock_global'    => (int) $product->stock_global,
            'inStock'         => (bool) $product->in_stock,
            'inStockLojas'    => false,
            'stocklojas'      => 0,
            // Le catalogue de la source se vend « por encomenda » : sauf indication
            // contraire (vende_apenas_stock = "Y"), l'article reste commandable même
            // à stock 0 — comme sur le site officiel.
            'vende_apenas_stock'      => data_get($product->source_payload, 'vende_apenas_stock') ?: 'N',
            'prazo_entrega_stock'     => data_get($product->source_payload, 'prazo_entrega_stock') ?: ($product->delivery_time_stock ?: '3-8'),
            'prazo_entrega_encomenda' => data_get($product->source_payload, 'prazo_entrega_encomenda') ?: ($product->delivery_time_order ?: '15-30'),
            'exclusivo_site'  => data_get($product->flags, 'exclusivo_site', 'N'),
            'rating'          => (float) $product->rating,
            'reviews'         => $product->reviews->map(fn ($r) => [
                'nome' => $r->author, 'nota' => $r->rating, 'texto' => $r->body, 'data' => optional($r->reviewed_at)->toDateString(),
            ])->values()->all(),
            'variations'      => $variations,
            'variationsJson'  => json_encode($variations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'specs'           => $product->specs->pluck('value', 'attr'),
            'servicosExtra'   => $product->extraServices->map(fn ($s) => [
                'nome' => $s->name, 'preco' => $s->price, 'variation' => $s->variation,
                'protec_id' => $s->protec_id, 'extra_erpid' => $s->extra_erpid, 'protec_erpid' => $s->protec_erpid,
            ])->values()->all(),
            'relacionados'    => [],
            'breadcrumb'      => $product->breadcrumb ?: [],
            'images'          => $images,
        ]);

        $short = trim(strip_tags((string) $product->short_description_html));

        $specsHtml = $product->specs
            ->filter(fn ($s) => trim((string) $s->value) !== '' && trim((string) $s->value) !== '0')
            ->map(fn ($s) => "<div class='atributo'><div>".e($s->attr).'</div><div>'.e($s->value).'</div></div>')
            ->implode('');

        $dataLayer = [
            'event'     => 'view_item',
            'event_id'  => Str::random(10),
            'ecommerce' => ['items' => [[
                'item_id'       => (int) $product->id,
                'item_name'     => $product->name,
                'price'         => (string) ($product->price ?? '0'),
                'item_category' => (string) ($product->category_name ?? ''),
            ]]],
        ];

        return view('product', [
            'metaTitle'       => $product->name.' | DFP Interiores',
            'metaDescription' => Str::limit($short !== '' ? $short : $product->name, 155),
            'canonical'       => url('/'.$product->slug),
            'schema'          => $this->productSchema($product),
            'dataLayer'       => $dataLayer,
            'artigo'          => $artigo,
            'prodVariations'  => $variations,
            'artigoMini'      => [
                'id'          => base64_encode('id:'.$product->id),
                'preco'       => (float) $product->price,
                'preco_antes' => (float) ($product->price_before ?? 0),
            ],
            'productToken'    => base64_encode('id:'.$product->id),
            'productName'     => $product->name,
            'productRef'      => (string) $product->sku,
            'productId'       => (int) $product->id,
            'productPrice'    => number_format((float) $product->price, 2, '.', ''),
            'productCategory' => (string) $product->category_name,
            'shareText'       => rawurlencode($product->name.' | '.url('/'.$product->slug)),
            'shortDesc'       => (string) $product->short_description_html,
            'longDesc'        => (string) $product->long_description_html,
            'specsHtml'       => $specsHtml,
            'hasSpecs'        => $specsHtml !== '',
            'hasDesc'         => trim(strip_tags((string) $product->long_description_html)) !== '',
        ]);
    }

    /**
     * Proxy image : le JS officiel demande /images/<taille>/products/<fichier>.
     * - fichier statique réel sous public/images/… -> servi tel quel
     * - sinon image produit connue (par nom de fichier) -> servie depuis public/assets/images/…
     * - sinon placeholder SVG (200) pour ne pas casser la mise en page
     *   (produit pas encore importé : le scraping/import est peut-être en cours)
     */
    public function image(string $path)
    {
        $static = public_path('images/'.$path);
        if (is_file($static)) {
            return response()->file($static, ['Cache-Control' => 'public, max-age=604800']);
        }

        // Recherche par nom de fichier (les URLs du front sont /images/<taille>/products/<file>).
        // basename() peut matcher plusieurs produits : on privilégie une ligne dont le
        // fichier existe réellement sur le disque.
        $file = basename($path);
        $img = ProductImage::where('filename', $file)
            ->get()
            ->first(fn ($i) => is_file(public_path($i->path)))
            ?? ProductImage::where('filename', $file)->first();

        if ($img && is_file(public_path($img->path))) {
            return response()->file(public_path($img->path), ['Cache-Control' => 'public, max-age=604800']);
        }

        // Dernier recours : servir la vraie photo depuis la source si on la connaît
        // (self-heal après un scrape --no-images), sinon placeholder NON caché.
        if ($img && $img->source_url) {
            try {
                $res = \Illuminate\Support\Facades\Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
                    ->timeout(15)->get($img->source_url);
                if ($res->ok() && $res->body() !== '' && md5($res->body()) !== 'ea548401ff2db836f0caec2cde79ace2') {
                    $dest = public_path($img->path);
                    @mkdir(dirname($dest), 0775, true);
                    @file_put_contents($dest, $res->body());
                    if (is_file($dest)) {
                        return response()->file($dest, ['Cache-Control' => 'public, max-age=604800']);
                    }
                }
            } catch (\Throwable $e) {
                // on tombe sur le placeholder ci-dessous
            }
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">'
            .'<rect width="400" height="400" fill="#f4f4f4"/>'
            .'<text x="50%" y="50%" fill="#bbb" font-family="sans-serif" font-size="20" '
            .'text-anchor="middle" dominant-baseline="middle">sem imagem</text></svg>';

        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',  // pas de cache d'une image absente
        ]);
    }

    /**
     * Vidéo hors public/ (storage/app/media/videos) servie par Symfony
     * BinaryFileResponse, qui répond aux requêtes Range en 206 —
     * nécessaire pour que <video> se lance dans le navigateur.
     */
    public function video(string $file)
    {
        $abs = storage_path('app/media/videos/'.$file);
        abort_unless(is_file($abs), 404);

        return response()->file($abs, [
            'Content-Type'  => 'video/mp4',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    // ------------------------------------------------------------------ helpers



    private function productSchema(Product $p): array
    {
        return [
            '@context' => 'https://schema.org', '@type' => 'Product',
            'name' => $p->name, 'sku' => $p->sku,
            'brand' => ['@type' => 'Brand', 'name' => $p->brand ?: 'DFP Interiores'],
            'image' => $p->images->map(fn ($i) => url($i->path))->values()->all(),
            'offers' => [
                '@type' => 'Offer', 'url' => url('/'.$p->slug),
                'priceCurrency' => $p->currency ?: 'EUR',
                'price' => (string) ($p->price ?? '0'),
                'availability' => $p->in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            ],
        ];
    }
}
