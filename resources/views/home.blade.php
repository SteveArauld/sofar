@extends('layouts.app')

@section('title', 'DFP Interiores — Sofás e Mobiliário para a sua casa')

@push('mods-css')
<link href="/assets/css/pt/mods/home.css" rel="stylesheet">
@endpush

@section('content')
{{-- Hero : image (parmi 5) et vidéo (parmi 10) tirées au hasard à chaque chargement --}}
<div class="Home clearfix">
  <div class="FundoBanner">
    <div class="Banner">
      <div id="MainBanner" class="owl-carousel">
        @foreach ($hero as $i => $s)
          <div class="item {{ $i === 0 ? 'active' : '' }}">
            <a href="{{ $s['href'] }}" class="hero-slide">
              <img src="{{ $s['img'] }}" alt="{{ $s['h2'] }}" style="width:100%;display:block;"
                   @if ($i === 0) fetchpriority="high" loading="eager" @else loading="lazy" @endif />
              <div class="hero-caption">
                <h2>{{ $s['h2'] }}</h2>
                <p>{{ $s['p'] }}</p>
                <span class="hero-btn">{{ $s['cta'] }}</span>
              </div>
            </a>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="container" style="text-align:center">
    <video width="80%" style="margin-top:40px" preload="auto" loop playsinline autoplay muted>
      <source src="{{ $heroVideo }}" type="video/mp4" />
      Your browser does not support the video tag.
    </video>
  </div>
{{-- NB : <div class="Home clearfix"> reste ouvert et englobe la suite (rails,
     catégories…) — le CSS scrapé cible « .Home .Categorias », etc. Il est fermé
     tout en bas de la section. --}}

{{-- Rail dynamique : remplace les 72 cartes figées du miroir --}}
<x-product-rail title="Descontos até 70%" :tabs="$descTabs" />

{{-- Tuiles catégories + bannières secondaires : design officiel, statique --}}
@verbatim
<div class="container Categorias">
    <div class="title">Categorias</div>
    <div class="list">
             <div>
        <a href="/sofas">
          <img
            src="images/400-400/categorias/homepage/sofas.png"
            alt="Sofás"
          />
          <div class="nome" style="background-color: ;">
            Sofás
          </div>
        </a>
      </div>
              <div>
        <a href="/sofas-de-canto">
          <img
            src="images/400-400/categorias/homepage/sofa_canto.png"
            alt="Sofás de Canto"
          />
          <div class="nome" style="background-color: ;">
            Sofás de Canto
          </div>
        </a>
      </div>
              <div>
        <a href="/chaise-longue">
          <img
            src="images/400-400/categorias/homepage/chaise_long.png"
            alt="Chaise Longue"
          />
          <div class="nome" style="background-color: ;">
            Chaise Longue
          </div>
        </a>
      </div>
                <div>
        <a href="/salas-de-jantar">
          <img
            src="images/400-400/categorias/homepage/mesas_jantar.png"
            alt="Salas de Jantar"
          />
          <div class="nome" style="background-color: ;">
            Salas de Jantar
          </div>
        </a>
      </div>
              <div>
        <a href="/camas-mobiliario">
          <img
            src="images/400-400/categorias/homepage/camas.png"
            alt="Camas"
          />
          <div class="nome" style="background-color: ;">
            Camas
          </div>
        </a>
      </div>
              <div>
        <a href="/moveis-auxiliares">
          <img
            src="images/400-400/categorias/homepage/aparadores.png"
            alt="Móveis auxiliares"
          />
          <div class="nome" style="background-color: ;">
            Móveis auxiliares
          </div>
        </a>
      </div>
           </div>
  </div>
   



  
  <div class="container">

        <a style="display: block; margin-bottom: 20px;" href="/mobiliario-de-exterior" >
      <picture>
        <source
          media="(min-width: 769px)"
          srcset="/media/banners_sec/btn_mobiliario_exterior.avif"
          type="image/avif"
        />
        
        <source
          media="(min-width: 769px)"
          srcset="/media/banners_sec/btn_mobiliario_exterior.webp"
          type="image/webp"
        />
        
        
        <source
          media="(max-width: 768px)"
          srcset="/media/banners_sec/btn_mobiliario_exterior.avif"
          type="image/avif"
        />
        
        <source
          media="(max-width: 768px)"
          srcset="/media/banners_sec/btn_mobiliario_exterior.webp"
          type="image/webp"
        />
        

        <img
          style="width: 100%;"
          src="/media/banners_sec/btn_mobiliario_exterior.jpg"
          alt="Sec Mob. Exterior"
          loading="lazy"
        />
      </picture>
    </a>
        <a style="display: block; margin-bottom: 20px;" href="/procura/emma" >
      <picture>
        <source
          media="(min-width: 769px)"
          srcset="/media/banners/emma.avif"
          type="image/avif"
        />
        
        <source
          media="(min-width: 769px)"
          srcset="/media/banners/emma.webp"
          type="image/webp"
        />
        
        
        <source
          media="(max-width: 768px)"
          srcset="/media/banners/emma.avif"
          type="image/avif"
        />
        
        <source
          media="(max-width: 768px)"
          srcset="/media/banners/emma.webp"
          type="image/webp"
        />
        

        <img
          style="width: 100%;"
          src="/media/banners/emma.jpg"
          alt="Sec Emma"
          loading="lazy"
        />
      </picture>
    </a>
    
     </div>
  
   
  
      
  



<div class="container">

        <a style="display: block; margin-bottom: 20px;" href="/mobiliario" >
      <picture>
        <source
          media="(min-width: 769px)"
          srcset="/media/banners_sec/btn_mobiliario_exclusivo.avif"
          type="image/avif"
        />
        
        <source
          media="(min-width: 769px)"
          srcset="/media/banners_sec/btn_mobiliario_exclusivo.webp"
          type="image/webp"
        />
        
        
        <source
          media="(max-width: 768px)"
          srcset="/media/banners_sec/btn_mobiliario_exclusivo.avif"
          type="image/avif"
        />
        
        <source
          media="(max-width: 768px)"
          srcset="/media/banners_sec/btn_mobiliario_exclusivo.webp"
          type="image/webp"
        />

        <img
          style="width: 100%;"
          src="/media/banners_sec/btn_mobiliario_exclusivo.jpg"
          alt="Sec Mob. Exclusivo"
          loading="lazy"
        />
      </picture>
    </a>
        <a style="display: block; margin-bottom: 20px;" href="/packs" >
      <picture>
        <source
          media="(min-width: 769px)"
          srcset="/media/banners_sec/btn_super_packs.avif"
          type="image/avif"
        />
        
        <source
          media="(min-width: 769px)"
          srcset="/media/banners_sec/btn_super_packs.webp"
          type="image/webp"
        />
        
        
        <source
          media="(max-width: 768px)"
          srcset="/media/banners_sec/btn_super_packs.avif"
          type="image/avif"
        />
        
        <source
          media="(max-width: 768px)"
          srcset="/media/banners_sec/btn_super_packs.webp"
          type="image/webp"
        />

        <img
          style="width: 100%;"
          src="/media/banners_sec/btn_super_packs.jpg"
          alt="Sec. Super Packs"
          loading="lazy"
        />
      </picture>
    </a>
    
     </div>
@endverbatim
</div>{{-- ferme .Home.clearfix ouvert dans le bloc hero --}}
@endsection
