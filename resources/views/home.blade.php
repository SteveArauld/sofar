@extends('layouts.app')

@section('title', 'DFP Interiores — Sofás e Mobiliário para a sua casa')

@push('mods-css')
<link href="/assets/css/pt/mods/home.css" rel="stylesheet">
@endpush

@section('content')
{{-- Bloc hero (carrousel promo + vidéo) : design officiel, statique --}}
@verbatim

                    
<div class="Home clearfix">
  <div class="FundoBanner">
    <div class="Banner" >
      
        <div id="MainBanner" class="owl-carousel">
                    <div class="item  active ">
            <a href="/lojas" >
              <picture>
                
                <source
                  media="(min-width: 769px)"
                  srcset="/media/banners/matosinhos_desktop.avif"
                  type="image/avif"
                />
                
                <source
                  media="(min-width: 769px)"
                  srcset="/media/banners/matosinhos_desktop.webp"
                  type="image/webp"
                />
                
                
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/matosinhos_mobile.avif"
                  type="image/avif"
                />
                
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/matosinhos_mobile.webp"
                  type="image/webp"
                />
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/matosinhos_mobile.jpg"
                />

                <img
                  style="width: 100%;"
                  src="/media/banners/matosinhos_desktop.jpg"
                  alt="Banner Feira em Festa"
                   fetchpriority="high" loading="eager"                 />
              </picture>
            </a>
          </div>
                    <div class="item ">
            <a href="/descontos70" >
              <picture>
                
                <source
                  media="(min-width: 769px)"
                  srcset="/media/banners/novos_saldos_desktop_.avif"
                  type="image/avif"
                />
                
                <source
                  media="(min-width: 769px)"
                  srcset="/media/banners/novos_saldos_desktop_.webp"
                  type="image/webp"
                />
                
                
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/novos_saldos_mobile.avif"
                  type="image/avif"
                />
                
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/novos_saldos_mobile.webp"
                  type="image/webp"
                />
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/novos_saldos_mobile.jpg"
                />

                <img
                  style="width: 100%;"
                  src="/media/banners/novos_saldos_desktop_.jpg"
                  alt="Banner Saldos_"
                   loading="lazy"                 />
              </picture>
            </a>
          </div>
                    <div class="item ">
            <a href="/procura/pikolin" >
              <picture>
                
                <source
                  media="(min-width: 769px)"
                  srcset="/media/banners/pack_100_desconto.avif"
                  type="image/avif"
                />
                
                <source
                  media="(min-width: 769px)"
                  srcset="/media/banners/pack_100_desconto.webp"
                  type="image/webp"
                />
                
                
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/pack_100_desconto_mobile.avif"
                  type="image/avif"
                />
                
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/pack_100_desconto_mobile.webp"
                  type="image/webp"
                />
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/pack_100_desconto_mobile.jpg"
                />

                <img
                  style="width: 100%;"
                  src="/media/banners/pack_100_desconto.jpg"
                  alt="Banner 100€"
                   loading="lazy"                 />
              </picture>
            </a>
          </div>
                    <div class="item ">
            <a href="/descontos70" >
              <picture>
                
                <source
                  media="(min-width: 769px)"
                  srcset="/media/banners/desktop_scalapay.avif"
                  type="image/avif"
                />
                
                <source
                  media="(min-width: 769px)"
                  srcset="/media/banners/desktop_scalapay.webp"
                  type="image/webp"
                />
                
                
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/mobile_scalapay.avif"
                  type="image/avif"
                />
                
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/mobile_scalapay.webp"
                  type="image/webp"
                />
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/mobile_scalapay.jpg"
                />

                <img
                  style="width: 100%;"
                  src="/media/banners/desktop_scalapay.jpg"
                  alt="Banner Scalapay"
                   loading="lazy"                 />
              </picture>
            </a>
          </div>
                    <div class="item ">
            <a href="/lojas" >
              <picture>
                
                <source
                  media="(min-width: 769px)"
                  srcset="/media/banners/cozinhas_desktop.avif"
                  type="image/avif"
                />
                
                <source
                  media="(min-width: 769px)"
                  srcset="/media/banners/cozinhas_desktop.webp"
                  type="image/webp"
                />
                
                
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/cozinhas_mobiel.avif"
                  type="image/avif"
                />
                
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/cozinhas_mobiel.webp"
                  type="image/webp"
                />
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/cozinhas_mobiel.jpg"
                />

                <img
                  style="width: 100%;"
                  src="/media/banners/cozinhas_desktop.jpg"
                  alt="Banner Cozinas"
                   loading="lazy"                 />
              </picture>
            </a>
          </div>
                    <div class="item ">
            <a href="/" >
              <picture>
                
                <source
                  media="(min-width: 769px)"
                  srcset="/media/banners/cofidis_desktop.avif"
                  type="image/avif"
                />
                
                <source
                  media="(min-width: 769px)"
                  srcset="/media/banners/cofidis_desktop.webp"
                  type="image/webp"
                />
                
                
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/cofidis_mobile.avif"
                  type="image/avif"
                />
                
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/cofidis_mobile.webp"
                  type="image/webp"
                />
                <source
                  media="(max-width: 768px)"
                  srcset="/media/banners/cofidis_mobile.jpg"
                />

                <img
                  style="width: 100%;"
                  src="/media/banners/cofidis_desktop.jpg"
                  alt="Banner Cofidis"
                   loading="lazy"                 />
              </picture>
            </a>
          </div>
                    </div>
      
    </div>
  </div>

  	<div class="container" style="text-align:center">
	<video width="80%" style="margin-top:40px" preload="auto" loop playsinline autoplay muted>
		<source src="/videos/promo.mp4?v=4" type="video/mp4" />
	Your browser does not support the video tag.
	</video>
		
		</div>
@endverbatim

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
