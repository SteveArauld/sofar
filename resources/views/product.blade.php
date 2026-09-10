@extends('layouts.app')

@section('title', $metaTitle)

@push('head')
<meta name="description" content="{{ $metaDescription }}">
<link rel="canonical" href="{{ $canonical }}">
<script type="application/ld+json">@json($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)</script>
@endpush

@push('mods-css')
<link href="/assets/css/pt/mods/catalogo.css" rel="stylesheet">
@endpush

@section('content')

                    
	<div id="_Product" v-cloak class="Produto">
		<form method="post" class="formProduto" enctype="multipart/form-data">
			<input id="ArtigoId" type="hidden" name="ProductToCart[id]" value="{{ $productId }}">
			<input type="hidden" name="ProductToCart[qtd]" value="1">
			<input type="hidden" name="ProductToCart[option]" value="" class="ProductToCartOption">
			<section class="container">

				<div class='row'>
					<div
						class="col-sm-7 Imagem" style='aspect-ratio:1;'>
												

						<div class="slider-container" 
							@mouseenter="stopAutoplay" 
							@mouseleave="startAutoplay" 
							@touchstart="handleTouchStart"
    						@touchend="handleTouchEnd">
							<div class="main-image">
							<button v-if="images.length > 1" @click="prev" class="nav-btn prev">❮</button>

							<transition name="fade" mode="out-in">
								<img v-if="images.length"
									:src="'/images/800-800/' + images[activeIndex]" :key="activeIndex"
									v-on:error="$event.target.src='/assets/images/no-photo.svg'"
									alt="${ artigo.nome }" />
								<img v-else src="/assets/images/no-photo.svg" class="no-photo"
									:key="'no-photo'" alt="Fotografia brevemente" />
							</transition>

							<button v-if="images.length > 1" @click="next" class="nav-btn next">❯</button>
							</div>

							<div class="thumbnails" v-if="images.length > 1">
							<div
								v-for="(img, index) in images"
								:key="index"
								:class="['thumb-item', { active: index === activeIndex }]"
								@click="setActive(index)"
							>
								<img :src="'/images/100-100/' + img" alt="Thumbnail"
									v-on:error="$event.target.src='/assets/images/no-photo.svg'" />
							</div>
							</div>
						</div>



					</div>
					<div class="col-sm-5 Conteudo">
						<div class="ProdutoNome"> {{ $productName }} <div class="ref">Ref:
									<span >{{ $productRef }}</span>
								</div>
						</div>

						<div class="Bloco1">
								
							<div>
								<div class="ProdutoPreco">
									<div v-if="precos?.antes" class="Old PrecoArtigoOld">
										${ precos?.antes + "€" }
									</div>
									<span class="Now PrecoArtigo">
										<div v-if="selectedPrice" class="Desde">
											${ Number(precos.atual) }€
										</div>

										<div v-else class="Desde">
											<small>DESDE</small>
											${ Number(minPrice).toFixed(0) }€
										</div>
										<span v-if="precos.desconto" class='percentDesconto'>${ precos.desconto }</span>
									</span>
								</div>
							</div>
							<div style="margin-right: 10px;">

								<div v-if="isInStock || artigo.vende_apenas_stock == 'N'" class='Disponibilidade available'>
									<svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 512 512"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/></svg>
									DISPONÍVEL
								</div>
								<div v-else class='Disponibilidade on-request'>
									<svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 512 512"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/></svg>
									INDISPONÍVEL
								</div>
								<div v-if="artigo.prazo_entrega" class="PrazoEntrega">
									<img src="/assets/images/fast.png" style="width: 20px; margin-right: 5px;">
									<small style="vertical-align: sub;"><b>Entrega entre ${ artigo?.prazo_entrega.replace('-', ' a ') } dias úteis</b></small>
								</div>

								<div v-if="selectedOption " class="ConsultaStockLoja">
									<a @click="consultaStockLojas" :class="{'loading': StockLojas.loading }">
										<div style="display:flex;">
											<div>
												<img src="/assets/images/store.png" style="width: 30px;margin-left: 5px;margin-right:5px;opacity: 0.1;padding: 2px;">
											</div>
											<div>
												<span v-if="selectedOption.lojascomstock">Este artigo também se encontra disponível em <b>${ selectedOption.lojascomstock } das nossas lojas físicas</b>. <b><span style="color: rgb(225 124 193);">Clica para saber mais!</span></b></span>
												<span v-else>Este artigo não está disponível em lojas físicas</span>
											</div>
										</div>
									</a>

									<div class="stock-overlay" :class="{'open': StockLojas.visible }" @click="StockLojas.visible = !StockLojas.visible"></div>
									<div class="stock-container" :class="{'open': StockLojas.visible }">
										<div class="stock-title"><h4>Disponibilidade em loja</h4></div>
										<div v-for="loja in StockLojas.lojas" class="store-row" :class="loja.stock > 0 ? 'available' : 'out-of-stock'">
											<div class="store-info">
												<span class="store-name" @click="loja.showDetails = !loja.showDetails">${loja.nome}<br><small v-if="loja.stock > 0">Disponível para levantamento</small></span>
												<p class="store-details" v-if="loja.showDetails"><b>Morada:</b>${loja.morada}, ${loja.cpostal}<br><b>Tel:</b> ${loja.telefone}</p>
											</div>
											<span class="status-badge">${loja.stock ? 'Disponível' : 'Esgotado'}</span>
										</div>
									</div>
								</div>
							</div>
						</div>

																			<b class="ExclusivoOnline">
								Artigo Exclusivo Online
							</b>
																		<div>
							<div class="DescricaoCurta">{!! $shortDesc !!}</div>
						</div>
						
						<small v-if="Object.keys(availableOptions) != ''"><b>Personalização:</b></small><br>
												<div v-if="Object.keys(availableOptions) != ''" 
							id="VariacoesArtigo" 
							class="VariacoesArtigo" 
							v-for="(values, option) in availableOptions" 
							:key="option">
							<label>${ option.charAt(0).toUpperCase() + option.slice(1) }:</label>
							<div v-if="values?.length < 9 || option === 'COR' || option === 'PADRÃO / COR'" class="color-options">
								<div v-if="values?.length > 0" v-for="value in values" :key="value?.name">

									<input 
										:disabled="!isOptionAvailable(option, value.name)"
									 type="checkbox" :id="`${option}-${value.name}`" :true-value="value?.name" false-value="" v-model="selected[option]"  :class="{ 'disabled' : !isOptionAvailable(option, value.name)}" />
																		
									<label 
										
										:class="[ 
											value.thumbnail ? 'thumbnail' : 
											option.toLowerCase() === 'cor' ? 'color' :
											option === 'PADRÃO / COR' ? 'thumbnail' : 
											'' 
										]" 
										:for="`${option}-${value?.name}`" 
										:style="{ backgroundColor: value.thumbnail ? '' : value.colorcode }" 
										:title='value.color'>
										<img v-if='value.thumbnail && (option.toLowerCase() === "cor" || option === "PADRÃO / COR")' :src="'/images/70-70/'+value.thumbnail" alt="${ value.name }">
										<span v-else>${ value.name }</span>
									</label>
								</div>

							</div> 
														<select v-else v-model="selected[option]" >
								<option v-if="values?.length > 0" v-for="value in values" :key="value?.name" :value="value?.name"  :class="{ 'disabled' : !isOptionAvailable(option, value.name) }" >
									${ value?.name } ${ !isOptionAvailable(option, value?.name) ? " (Esgotado)" : "" }
								</option> 
							</select>
						</div>
						<div v-if="modalFinalizar.visible" class="modal">
							<div class="modal-content">
								<button class="close-btn" @click="modalFinalizar.visible=false">✖</button>
								<h2>Adicionado com sucesso"</h2>
								<p>O artigo foi adicionado ao carrinho com sucesso.</p>
								<a class='btn btn-primary'>Continuar a comprar</a>
								<a class='btn btn-secondary'>Finalizar a encomenda</a>
							</div>
						</div>


						<div class="Bloco2">
														<div class="options"></div>
						</div>


						
						<!--
						Gravação na tampa
						-->
						
						<!--
						Custom options
						-->
						
						<div v-if="Imperline?.length > 0"  class="Impermeabilização">
							<label for="Impermeabilizacao" class="title">Impermeabilização e Proteção Anti-Mancha</label>
							<select id="Impermeabilizacao" v-model="selectedImper" class="form-control">
								<option value="">Escolha uma opção</option>
								<option value="SEM">Sem impermeabilização</option>
								<option v-for="s, k in Imperline" :value="s" :key="k">${ s.nome } (+${ s.preco }€)</option>
							</select>
							<small>Todos os tratamentos e impermeabilizações são realizadas na morada do cliente.</small>
						</div>
				

						<div v-if="selectedOption?.seguros" class="Seguros">
							<h3>Proteja o seu produto:</h3>
							<template v-for="seguro in selectedOption.seguros">
								<input :id="seguro.erpid" type="checkbox" @change="validaSeguro(seguro)" v-model="segurosSelecionados" :value="seguro.erpid">
								<label :for="seguro.erpid">
									<img src='/assets/icons/spb.png' style="width:46px;margin-right: 5px;">
									${ seguro.nome } (+${ seguro.preco }€)
								</label>
							</template>
							<div style="text-align: right;"><small><a href="https://spbseguros.com/pt-pt/" target="_blank">Saiba mais sobre os seguros SPB</a></small></div>
						</div>
						
						<!-- 
						                Adicionar ao carrinho
						                -->
						<div class="actions">
							<div>
								<a href="#" class="Wishlist " data-artigo="{{ $productId }}" title="Adicionar à wishlist">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#cccccc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
								</a>

								<a v-if="isInStock" class="AddCarrinho" @click="addProduct" :class="{'disabled' : !selectedOption?.pid}" data-mobile="true" data-artigo="{{ $productId }}" data-preco="{{ $productPrice }}" data-nome="{{ $productName }}" data-categoria="{{ $productCategory }}">
									<span>
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart-icon lucide-shopping-cart"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
										ADICIONE AO CARRINHO
									</span>
									<img v-if="loader" class='addCartLoader' src='/assets/loader.svg' style='height: 25px;'></a>
								<a v-else  class="AddCarrinho"  btn btn-primary">
									<i class='fa fa-bell' style='font-size:12px;' aria-hidden='true'></i> Temporariamente indisponível
								</a>
							</div>
						</div>

						

						
						<div v-if="prestacaoScalapay" class="credito">
							<img src='/assets/icons/scalapay.png'>
							<span>
								Pague em 3 prestações de <b>${ prestacaoScalapay.prestacao3  }€</b> sem juros.<br>
								Pague em 4 prestações de <b>${ prestacaoScalapay.prestacao4  }€</b> sem juros.
							</span>
						</div>
												<div v-if="prestacaoCofidis" class="credito">
							<img src='/assets/icons/sequra.png'>
							<span>Pague até 12 prestações de <b>${ prestacaoCofidis }€</b> sem juros.</span>
						</div>
						<div v-if="prestacaoCofidis" class="credito">
							<img src='/assets/icons/cofidis_produto.png'>
							<span>Pague em 12 prestações de <b>${ prestacaoCofidis }€</b> sem juros.</span>
						</div>

						
						



																		<!--	
						                Partilhar
						                -->
						<div class="descricao">
							<div class="title">Gostou? partilhe!</div>
							<a target='_blank' href="https://www.facebook.com/sharer/sharer.php?u=/catalogo/artigo/">
								<i style='font-size: 30px; margin-right: 10px;' class='fa fa-facebook-square'></i>
							</a>
							<a target='_blank' href="https://api.whatsapp.com/send?text={{ $shareText }}">
								<i style='font-size: 30px;' class='fa fa-whatsapp'></i>
							</a>
						</div>

						<!--
						                Medidas
						                -->
						
						<!--
						                Tags
						                -->
						
						<!--
						                Curiosidades
						                -->
						
						<!--
						                Artigos agrupados
						                -->
						
						<!--
						                Look
						                -->
						
					</div>
				</div>
			</section>
		</form>
		
		<div class="container descricaoCompleta">
			<h1>	 </h1>
@if($hasSpecs || $hasDesc)
<div class="Tabs">

		@if($hasSpecs)<input type="radio" name="tabs" id="caracteristicas"@if(!$hasDesc) checked @endif>
		<label class="tab-label" for="caracteristicas">Caracteristicas</label>
		<div class="tab-content specs"><div>{!! $specsHtml !!}</div></div>@endif

		@if($hasDesc)<input type="radio" name="tabs" id="descricao" checked>
		<label class="tab-label" for="descricao">Descrição</label>
		<div class="tab-content" id="descricao">{!! $longDesc !!}</div>@endif

</div>
@endif
		</div>

		<div style="margin-bottom: 30px;" class="sliderColecaoArtigo">
			<div class="container">
				
			</div>
		</div>

				<div class="descontos70">
			<div class="container">
				<div>
            <div class="title">DESCONTOS ATÉ 70%</div>
        <!--<div class="GrelhaArtigos row">-->
    <div class="owl-carousel Sliders">
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="bEFlV1FFNWZ3TkdkeDNiWU45Qk4zUT09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/fl-189-tapete-5268">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2026_02_00042000000019.jpg' srcset="
                                    /images/320-320/products/foto1_2026_02_00042000000019.jpg 320w,
                                    /images/180-180/products/foto1_2026_02_00042000000019.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2026_02_00042000000019.jpg" alt="FL 189 Tapete">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    FL 189 Tapete 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                        <span style="background: #FFFF00;" title="Amarelo"></span>
                                                                                                                       <span style="background: #0000FF;" title="Azul"></span>
                                                                                                                       <span style="background: #F5F5DC;" title="Bege"></span>
                                                                                                                       <span style="background: #6c3b2a;" title="Castanho"></span>
                                                                                                                       <span style="background: #808080;" title="Cinza"></span>
                                                                                                                       <span style="background: #000000;" title="Preto"></span>
                                                                                                                       <span style="background: #FFC0CB;" title="Rosa"></span>
                                                                                                                       <span style="background: #008000;" title="Verde"></span>
                                                                                           </div>
                                    </div>

                <div class="Preco">
                    <span>8€</span>

                                                                6€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="bEFlV1FFNWZ3TkdkeDNiWU45Qk4zUT09"></a>&nbsp;
                    <a class="vermais" href="/fl-189-tapete-5268">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="akkvTWtQOWJnT2hjRXhWTmlha1F3UT09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/arca-prateleira-3827">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2025_09_00072000000092.jpg' srcset="
                                    /images/320-320/products/foto1_2025_09_00072000000092.jpg 320w,
                                    /images/180-180/products/foto1_2025_09_00072000000092.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2025_09_00072000000092.jpg" alt="ARCA Prateleira">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    ARCA Prateleira 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                        <span style="background: #FFFFFF;" title="Branco"></span>
                                                                                                                       <span style="background: #F5F5DC;" title="Bege"></span>
                                                                                           </div>
                                    </div>

                <div class="Preco">
                    <span>49€</span>

                                                                39€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="akkvTWtQOWJnT2hjRXhWTmlha1F3UT09"></a>&nbsp;
                    <a class="vermais" href="/arca-prateleira-3827">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="NUJXNk9vQlBDYlBVWGtkdjk1RlN2QT09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/bar63-banco-alto-6042">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2026_08_00167000010000.jpg' srcset="
                                    /images/320-320/products/foto1_2026_08_00167000010000.jpg 320w,
                                    /images/180-180/products/foto1_2026_08_00167000010000.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2026_08_00167000010000.jpg" alt="BAR63 Banco Alto">
                                                <img class="SecondImage MostraOnHover" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto2_2026_08_00167000010000.jpg' srcset="
                                 
                                    /images/320-320/products/foto2_2026_08_00167000010000.jpg 320w,
                                    /images/180-180/products/foto2_2026_08_00167000010000.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px" alt="BAR63 Banco Alto">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    BAR63 Banco Alto 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                        <span style="background: #000000;" title="Preto"></span>
                                                                                                                       <span style="background: #FFFFFF;" title="Branco"></span>
                                                                                           </div>
                                    </div>

                <div class="Preco">
                    <span>69€</span>

                                                                49€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="NUJXNk9vQlBDYlBVWGtkdjk1RlN2QT09"></a>&nbsp;
                    <a class="vermais" href="/bar63-banco-alto-6042">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="Z1VhSmxFc2VnRUFvek44ck5iRUVjQT09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/bar60-banco-alto-6043">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2026_08_00167000010001.jpg' srcset="
                                    /images/320-320/products/foto1_2026_08_00167000010001.jpg 320w,
                                    /images/180-180/products/foto1_2026_08_00167000010001.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2026_08_00167000010001.jpg" alt="BAR60 Banco Alto">
                                                <img class="SecondImage MostraOnHover" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto2_2026_08_00167000010001.jpg' srcset="
                                 
                                    /images/320-320/products/foto2_2026_08_00167000010001.jpg 320w,
                                    /images/180-180/products/foto2_2026_08_00167000010001.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px" alt="BAR60 Banco Alto">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    BAR60 Banco Alto 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                        <span style="background: #FFFFFF;" title="Branco"></span>
                                                                                                                       <span style="background: #000000;" title="Preto"></span>
                                                                                                                       <span style="background: #808080;" title="Cinza"></span>
                                                                                           </div>
                                    </div>

                <div class="Preco">
                    <span>69€</span>

                                                                49€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="Z1VhSmxFc2VnRUFvek44ck5iRUVjQT09"></a>&nbsp;
                    <a class="vermais" href="/bar60-banco-alto-6043">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="ZTJzS08yYTF5dzF3L2pQcGlNeXdDdz09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/linea-ki-mesa-de-centro-620">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_00055001010268.jpg' srcset="
                                    /images/320-320/products/foto1_00055001010268.jpg 320w,
                                    /images/180-180/products/foto1_00055001010268.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_00055001010268.jpg" alt="LINEA KI Mesa de Centro">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    LINEA KI Mesa de Centro 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>169€</span>

                                                                55€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="ZTJzS08yYTF5dzF3L2pQcGlNeXdDdz09"></a>&nbsp;
                    <a class="vermais" href="/linea-ki-mesa-de-centro-620">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="eUsrMVdpR1RnUEtTWStlRmZuZXhOZz09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/za-90-cadeira-1404">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto2_2026_02_00033001010327.jpg' srcset="
                                    /images/320-320/products/foto2_2026_02_00033001010327.jpg 320w,
                                    /images/180-180/products/foto2_2026_02_00033001010327.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto2_2026_02_00033001010327.jpg" alt="ZA-90 Cadeira">
                                                <img class="SecondImage MostraOnHover" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_00033001010327.jpg' srcset="
                                 
                                    /images/320-320/products/foto1_00033001010327.jpg 320w,
                                    /images/180-180/products/foto1_00033001010327.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px" alt="ZA-90 Cadeira">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    ZA-90 Cadeira 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>149€</span>

                                                                59€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="eUsrMVdpR1RnUEtTWStlRmZuZXhOZz09"></a>&nbsp;
                    <a class="vermais" href="/za-90-cadeira-1404">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="dnkyRnpXa2l3QjRDVXlGNXFmRW5UZz09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/za-96-cadeira-1406">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2026_02_00033001010333.jpg' srcset="
                                    /images/320-320/products/foto1_2026_02_00033001010333.jpg 320w,
                                    /images/180-180/products/foto1_2026_02_00033001010333.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2026_02_00033001010333.jpg" alt="ZA-96 Cadeira">
                                                <img class="SecondImage MostraOnHover" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto2_2026_02_00033001010333.jpg' srcset="
                                 
                                    /images/320-320/products/foto2_2026_02_00033001010333.jpg 320w,
                                    /images/180-180/products/foto2_2026_02_00033001010333.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px" alt="ZA-96 Cadeira">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    ZA-96 Cadeira 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>149€</span>

                                                                69€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="dnkyRnpXa2l3QjRDVXlGNXFmRW5UZz09"></a>&nbsp;
                    <a class="vermais" href="/za-96-cadeira-1406">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="QUI3WUx1UG9pVlpPRDMzSjJncGZkdz09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/aura-secretaria-1544">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2026_04_00085001010813.jpg' srcset="
                                    /images/320-320/products/foto1_2026_04_00085001010813.jpg 320w,
                                    /images/180-180/products/foto1_2026_04_00085001010813.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2026_04_00085001010813.jpg" alt="AURA Secretária">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    AURA Secretária 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>86€</span>

                                                                69€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="QUI3WUx1UG9pVlpPRDMzSjJncGZkdz09"></a>&nbsp;
                    <a class="vermais" href="/aura-secretaria-1544">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="WWE3eVgzMlNNc2w1czhwUDV4d2dRdz09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/clyde-mesa-de-cabeceira-2713">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2025_06_00072001011166.jpg' srcset="
                                    /images/320-320/products/foto1_2025_06_00072001011166.jpg 320w,
                                    /images/180-180/products/foto1_2025_06_00072001011166.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2025_06_00072001011166.jpg" alt="CLYDE Mesa de Cabeceira">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    CLYDE Mesa de Cabeceira 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>86€</span>

                                                                69€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="WWE3eVgzMlNNc2w1czhwUDV4d2dRdz09"></a>&nbsp;
                    <a class="vermais" href="/clyde-mesa-de-cabeceira-2713">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="TWJiZTFiQ0RWQ0FQNjdiR0hBS3B5dz09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/mjd8420-cadeira-4050">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2025_09_00145000000023.jpg' srcset="
                                    /images/320-320/products/foto1_2025_09_00145000000023.jpg 320w,
                                    /images/180-180/products/foto1_2025_09_00145000000023.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2025_09_00145000000023.jpg" alt="MJD8420 Cadeira">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    MJD8420 Cadeira 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                        <span style="background: #808080;" title="Cinza"></span>
                                                                                           </div>
                                    </div>

                <div class="Preco">
                    <span>249€</span>

                                                                69€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="TWJiZTFiQ0RWQ0FQNjdiR0hBS3B5dz09"></a>&nbsp;
                    <a class="vermais" href="/mjd8420-cadeira-4050">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="ZkdPYzNLQ3dmdlBxd3krVlBTY1dxQT09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/damien-base-tv-4324">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2025_09_00072000000149.jpg' srcset="
                                    /images/320-320/products/foto1_2025_09_00072000000149.jpg 320w,
                                    /images/180-180/products/foto1_2025_09_00072000000149.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2025_09_00072000000149.jpg" alt="DAMIEN Base TV">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    DAMIEN Base TV 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                        <span style="background: #F5F5DC;" title="Bege"></span>
                                                                                                                       <span style="background: #FFFFFF;" title="Branco"></span>
                                                                                           </div>
                                    </div>

                <div class="Preco">
                    <span>109€</span>

                                                                69€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="ZkdPYzNLQ3dmdlBxd3krVlBTY1dxQT09"></a>&nbsp;
                    <a class="vermais" href="/damien-base-tv-4324">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="Ri9CQmd5QVpiVDEwUXlGQTVUNlVRQT09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/travis-mesa-de-cabeceira-4744">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2026_03_00133001010002.jpg' srcset="
                                    /images/320-320/products/foto1_2026_03_00133001010002.jpg 320w,
                                    /images/180-180/products/foto1_2026_03_00133001010002.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2026_03_00133001010002.jpg" alt="TRAVIS Mesa de Cabeceira">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    TRAVIS Mesa de Cabeceira 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>129€</span>

                                                                69€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="Ri9CQmd5QVpiVDEwUXlGQTVUNlVRQT09"></a>&nbsp;
                    <a class="vermais" href="/travis-mesa-de-cabeceira-4744">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="c2J3ZTIzZVROWDg1ZHptRUhJNkk3QT09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/prestige-cadeira-300">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2025_09_00055001010151.jpg' srcset="
                                    /images/320-320/products/foto1_2025_09_00055001010151.jpg 320w,
                                    /images/180-180/products/foto1_2025_09_00055001010151.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2025_09_00055001010151.jpg" alt="PRESTIGE Cadeira">
                                                <img class="SecondImage MostraOnHover" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto2_2025_09_00055001010151.jpg' srcset="
                                 
                                    /images/320-320/products/foto2_2025_09_00055001010151.jpg 320w,
                                    /images/180-180/products/foto2_2025_09_00055001010151.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px" alt="PRESTIGE Cadeira">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    PRESTIGE Cadeira 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>99€</span>

                                                                79€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="c2J3ZTIzZVROWDg1ZHptRUhJNkk3QT09"></a>&nbsp;
                    <a class="vermais" href="/prestige-cadeira-300">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="ajYwNGNNbW9HandPWGRGNlBqZHZhZz09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/piedra-ki-mesa-de-cabeceira-1391">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto5_2024_12_00055000000050.jpg' srcset="
                                    /images/320-320/products/foto5_2024_12_00055000000050.jpg 320w,
                                    /images/180-180/products/foto5_2024_12_00055000000050.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto5_2024_12_00055000000050.jpg" alt="PIEDRA KI Mesa de Cabeceira">
                                                <img class="SecondImage MostraOnHover" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto3_2024_11_00055000000050.jpg' srcset="
                                 
                                    /images/320-320/products/foto3_2024_11_00055000000050.jpg 320w,
                                    /images/180-180/products/foto3_2024_11_00055000000050.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px" alt="PIEDRA KI Mesa de Cabeceira">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    PIEDRA KI Mesa de Cabeceira 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>175€</span>

                                                                89€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="ajYwNGNNbW9HandPWGRGNlBqZHZhZz09"></a>&nbsp;
                    <a class="vermais" href="/piedra-ki-mesa-de-cabeceira-1391">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="WFdqNUt5amZIY3VqelpnSEhmakQyQT09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/impressio-jr-mesa-de-cabeceira-2033">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2024_11_00072001011107.jpg' srcset="
                                    /images/320-320/products/foto1_2024_11_00072001011107.jpg 320w,
                                    /images/180-180/products/foto1_2024_11_00072001011107.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2024_11_00072001011107.jpg" alt="IMPRESSIO JR Mesa de Cabeceira">
                                                <img class="SecondImage MostraOnHover" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto2_2024_11_00072001011107.jpg' srcset="
                                 
                                    /images/320-320/products/foto2_2024_11_00072001011107.jpg 320w,
                                    /images/180-180/products/foto2_2024_11_00072001011107.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px" alt="IMPRESSIO JR Mesa de Cabeceira">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    IMPRESSIO JR Mesa de Cabeceira 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>111€</span>

                                                                89€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="WFdqNUt5amZIY3VqelpnSEhmakQyQT09"></a>&nbsp;
                    <a class="vermais" href="/impressio-jr-mesa-de-cabeceira-2033">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="Z3BQdmh4OFZNc3ByTnVQSlA2elhDUT09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/mayra-ki-mesa-de-cabeceira-1386">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_00055000000045.jpg' srcset="
                                    /images/320-320/products/foto1_00055000000045.jpg 320w,
                                    /images/180-180/products/foto1_00055000000045.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_00055000000045.jpg" alt="MAYRA KI Mesa de Cabeceira">
                                                <img class="SecondImage MostraOnHover" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto2_2024_11_00055000000045.jpg' srcset="
                                 
                                    /images/320-320/products/foto2_2024_11_00055000000045.jpg 320w,
                                    /images/180-180/products/foto2_2024_11_00055000000045.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px" alt="MAYRA KI Mesa de Cabeceira">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    MAYRA KI Mesa de Cabeceira 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>199€</span>

                                                                99€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="Z3BQdmh4OFZNc3ByTnVQSlA2elhDUT09"></a>&nbsp;
                    <a class="vermais" href="/mayra-ki-mesa-de-cabeceira-1386">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="QmkwNXE0OFBpZVpXZGlobHMzZmFwUT09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/clyde-conjunto-2-gavetoes-2716">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2026_07_00072001011169.jpg' srcset="
                                    /images/320-320/products/foto1_2026_07_00072001011169.jpg 320w,
                                    /images/180-180/products/foto1_2026_07_00072001011169.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2026_07_00072001011169.jpg" alt="CLYDE Conjunto 2 Gavetões">
                                                <img class="SecondImage MostraOnHover" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto2_2026_07_00072001011169.jpg' srcset="
                                 
                                    /images/320-320/products/foto2_2026_07_00072001011169.jpg 320w,
                                    /images/180-180/products/foto2_2026_07_00072001011169.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px" alt="CLYDE Conjunto 2 Gavetões">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    CLYDE Conjunto 2 Gavetões 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>124€</span>

                                                                99€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="QmkwNXE0OFBpZVpXZGlobHMzZmFwUT09"></a>&nbsp;
                    <a class="vermais" href="/clyde-conjunto-2-gavetoes-2716">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="QlRnUDlQOGFmd0RqTTRsRTJXQ1ZFZz09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/corner-secretaria-3776">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2025_09_00072000000087.jpg' srcset="
                                    /images/320-320/products/foto1_2025_09_00072000000087.jpg 320w,
                                    /images/180-180/products/foto1_2025_09_00072000000087.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2025_09_00072000000087.jpg" alt="CORNER Secretária">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    CORNER Secretária 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                        <span style="background: #FFFFFF;" title="Branco"></span>
                                                                                                                       <span style="background: #6c3b2a;" title="Castanho"></span>
                                                                                           </div>
                                    </div>

                <div class="Preco">
                    <span>109€</span>

                                                                99€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="QlRnUDlQOGFmd0RqTTRsRTJXQ1ZFZz09"></a>&nbsp;
                    <a class="vermais" href="/corner-secretaria-3776">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="SXMyeS9YbndQblBxOVl5ZkxMSzFIQT09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/santi-ditalia-colchao-orthoclassic-4306">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2026_05_00014000000001.jpg' srcset="
                                    /images/320-320/products/foto1_2026_05_00014000000001.jpg 320w,
                                    /images/180-180/products/foto1_2026_05_00014000000001.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2026_05_00014000000001.jpg" alt="SANTI D`ITALIA Colchão Orthoclassic">
                                                <img class="SecondImage MostraOnHover" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto2_2026_08_00014000000001.jpg' srcset="
                                 
                                    /images/320-320/products/foto2_2026_08_00014000000001.jpg 320w,
                                    /images/180-180/products/foto2_2026_08_00014000000001.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px" alt="SANTI D`ITALIA Colchão Orthoclassic">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    SANTI D`ITALIA Colchão Orthoclassic 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>149€</span>

                                                                99€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="SXMyeS9YbndQblBxOVl5ZkxMSzFIQT09"></a>&nbsp;
                    <a class="vermais" href="/santi-ditalia-colchao-orthoclassic-4306">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="ZGc0V0JXSDc0T01MM2Y1R2xqY1U3dz09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/gh-25089-conjunto-de-mesa-e-cadeiras-5228">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2026_05_00033000000260.jpg' srcset="
                                    /images/320-320/products/foto1_2026_05_00033000000260.jpg 320w,
                                    /images/180-180/products/foto1_2026_05_00033000000260.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2026_05_00033000000260.jpg" alt="GH-25089 Conjunto de Mesa e Cadeiras">
                                                <img class="SecondImage MostraOnHover" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto2_2026_05_00033000000260.jpg' srcset="
                                 
                                    /images/320-320/products/foto2_2026_05_00033000000260.jpg 320w,
                                    /images/180-180/products/foto2_2026_05_00033000000260.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px" alt="GH-25089 Conjunto de Mesa e Cadeiras">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    GH-25089 Conjunto de Mesa e Cadeiras 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>124€</span>

                                                                99€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="ZGc0V0JXSDc0T01MM2Y1R2xqY1U3dz09"></a>&nbsp;
                    <a class="vermais" href="/gh-25089-conjunto-de-mesa-e-cadeiras-5228">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="dFNNczAvYWhnQ2VoNDlraUpvL3dKZz09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/vertina-jr-mesa-de-cabeceira-2038">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto2_2024_11_00072001011118.jpg' srcset="
                                    /images/320-320/products/foto2_2024_11_00072001011118.jpg 320w,
                                    /images/180-180/products/foto2_2024_11_00072001011118.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto2_2024_11_00072001011118.jpg" alt="VERTINA JR Mesa de Cabeceira">
                                                <img class="SecondImage MostraOnHover" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto3_2024_11_00072001011118.jpg' srcset="
                                 
                                    /images/320-320/products/foto3_2024_11_00072001011118.jpg 320w,
                                    /images/180-180/products/foto3_2024_11_00072001011118.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px" alt="VERTINA JR Mesa de Cabeceira">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    VERTINA JR Mesa de Cabeceira 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>131€</span>

                                                                105€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="dFNNczAvYWhnQ2VoNDlraUpvL3dKZz09"></a>&nbsp;
                    <a class="vermais" href="/vertina-jr-mesa-de-cabeceira-2038">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="clVHU2xCTDNPQ1F2Yi9RcVRESVJnZz09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/sleep-pro-colchao-5266">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto2_2026_06_00125000000116.jpeg' srcset="
                                    /images/320-320/products/foto2_2026_06_00125000000116.jpeg 320w,
                                    /images/180-180/products/foto2_2026_06_00125000000116.jpeg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto2_2026_06_00125000000116.jpeg" alt="SLEEP PRO Colchão">
                                                <img class="SecondImage MostraOnHover" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2026_02_00125000000116.jpg' srcset="
                                 
                                    /images/320-320/products/foto1_2026_02_00125000000116.jpg 320w,
                                    /images/180-180/products/foto1_2026_02_00125000000116.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px" alt="SLEEP PRO Colchão">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    SLEEP PRO Colchão 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>129€</span>

                                                                115€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="clVHU2xCTDNPQ1F2Yi9RcVRESVJnZz09"></a>&nbsp;
                    <a class="vermais" href="/sleep-pro-colchao-5266">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="WjRxdkJxWjlBVkozei82RWE5WDZtZz09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/wayne-mesa-de-jantar-551">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_00072001010564.jpg' srcset="
                                    /images/320-320/products/foto1_00072001010564.jpg 320w,
                                    /images/180-180/products/foto1_00072001010564.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_00072001010564.jpg" alt="WAYNE Mesa de Jantar">
                                                <img class="SecondImage MostraOnHover" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto2_00072001010564.jpg' srcset="
                                 
                                    /images/320-320/products/foto2_00072001010564.jpg 320w,
                                    /images/180-180/products/foto2_00072001010564.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px" alt="WAYNE Mesa de Jantar">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    WAYNE Mesa de Jantar 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                                            </div>
                                    </div>

                <div class="Preco">
                    <span>161€</span>

                                                                129€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="WjRxdkJxWjlBVkozei82RWE5WDZtZz09"></a>&nbsp;
                    <a class="vermais" href="/wayne-mesa-de-jantar-551">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            
        <div>     
            <div class="Artigo">
    <div>   
           

        <div class="EnvioImediato">
            <img src='/assets/stickers/EnvioImediato.png' alt='Envio Imediato'>
        </div> 
                        <a href="#" class="Wishlist " data-artigo="RWFxTVU3U3B2eStVVXB4WEN1WTJ4QT09" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>

        <div class="Selos">
                </div>
        
        <div class="preview ">
            <div class="box-image square">
                <div class="box-image-hover" style='aspect-ratio:1;'>
                    <a href="/basic-roupeiro-4027">
                        <img class="img-responsive" style='aspect-ratio:1;' loading="lazy"
                            src='/images/300-300/products/foto1_2025_10_00072000000112.jpg' srcset="
                                    /images/320-320/products/foto1_2025_10_00072000000112.jpg 320w,
                                    /images/180-180/products/foto1_2025_10_00072000000112.jpg 180w
                                    " sizes="(min-width:640px) 714px, 200px"
                            data-src="/images/400-400/products/foto1_2025_10_00072000000112.jpg" alt="BASIC Roupeiro">
                                            </a>
                </div>
            </div>

                    </div>

        <div class="ProductFooter">

            
            <div class="headProduto">
                <div class="nome">
                    BASIC Roupeiro 
                </div>        
            </div>

            
                        
            <div class="foot">
                <div>
                    <div class="colors">
                                                                        <span style="background: #F5F5DC;" title="Bege"></span>
                                                                                                                       <span style="background: #FFFFFF;" title="Branco"></span>
                                                                                           </div>
                                    </div>

                <div class="Preco">
                    <span>199€</span>

                                                                129€
                    
                </div>

                <div style='display:none;'>
                    <a href="#" class="fa fa-heart-o Wishlist"
                        data-artigo="RWFxTVU3U3B2eStVVXB4WEN1WTJ4QT09"></a>&nbsp;
                    <a class="vermais" href="/basic-roupeiro-4027">VER MAIS</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
            </div>
</div>

			</div>
		</div>
		
		
		<!-- JANELAS -->
		<div style="display: none;">


			<div id="JanelaReview">
    <!-- Lista Reviews -->  
    <div class='ListaReviews'>
        <div class="card mb-3" style="max-width: 540px;">
            <div class="row g-0">
              <div class="col-md-4">
                <img src="/images/100-100//products/foto1_2025_09_00125000000107.jpg" class="img-fluid rounded-start" alt="{{ $productName }}">
              </div>
              <div class="col-md-8">
                <div class="card-body">
                  <h5 class="card-title">{{ $productName }}</h5>
                </div>
              </div>
            </div>
        </div>
        
        
        
        <div class='Lista' style="max-width: 540px;">
                        <p>Ainda não existem avaliações a este artigo.<br>
                <a class='MostraFormularioReview' style='color: #18a19a !important; cursor: pointer; text-decoration: underline;'>Seja o primeiro a avaliar este produto</a>.</p>
                    </div>

    </div>
    
    <!-- Formulario de nova review -->  
    <form action='/reviews/add' method='post' class='ReviewsForm form WindowForm' style='display: none;'>
        <input type='hidden' name='token' value=''>
        <input type='hidden' name='artigo' value="{{ $productId }}">
        <h3>Adicionar critica</h3>

        <div class="card mb-3" style="max-width: 540px;">
            <div class="row g-0">
              <div class="col-md-4">
                <img src="/images/100-100//products/foto1_2025_09_00125000000107.jpg" class="img-fluid rounded-start" alt="{{ $productName }}">
              </div>
              <div class="col-md-8">
                <div class="card-body">
                  <h5 class="card-title">{{ $productName }}</h5>
                </div>
              </div>
            </div>
        </div>
       
        <div class='form-group'>
            <label>Nome</label>
            <input type='text' 
                   style='text-indent: 10px;'
                   class='form-control' 
                   name='nome' 
                   placeholder='O seu nome'
                   data-msg-required="Preencha o seu nome" />
        </div>
        <div class='form-group'>
            <label>Email</label>
            <input type='text' 
                   style='text-indent: 10px;'
                   class='form-control' 
                   name='email' 
                   placeholder='O seu email'
                   data-msg-required="Preencha o seu email"
                   data-msg-email="Insira um email válido"
                   >
        </div>

        <div class="star-rating">
            <input type="radio" id="star5" name="rating" value="5" />
            <label for="star5" title="5 estrelas">★</label>
            <input type="radio" id="star4" name="rating" value="4" />
            <label for="star4" title="4 estrelas">★</label>
            <input type="radio" id="star3" name="rating" value="3" />
            <label for="star3" title="3 estrelas">★</label>
            <input type="radio" id="star2" name="rating" value="2" />
            <label for="star2" title="2 estrelas">★</label>
            <input type="radio" id="star1" name="rating" value="1" />
            <label for="star1" title="1 estrela">★</label>
        </div>
        
        
        <div class='form-group'>
            <label>Comentário</label>
            <textarea type='text' 
                   style='text-indent: 10px;'
                   class='form-control' 
                   placeholder='Exemplo: Comprei este produto há um mês e estou muito contente'
                   name='comentario' 
                   ></textarea>
        </div>
        
        
        <div class="g-recaptcha" data-sitekey="6LcPQNoUAAAAAOHN7PqwEvafPhunhalUNLfjGEXj" data-msg-required="Tem que aceitar o Captcha"></div>

        <label>
            <input type='checkbox' name='check' data-msg-required="Tem que aceitar os termos.">
            Li e concordo com os termos e condições e com a política de privacidade
        </label>
        <div class="errorTxt"></div>
    
        <div class='form-group'>
            <div class='erros'></div>
            <button class='btn btn-default submit'>Enviar avaliação</button>
        </div>
    </form>
    
    <!-- Resultado do formulario -->  
    <div class='Result'></div>
</div>   

			<div id="WindowInfoArtigo">
				<form method='post' class='WindowInfoArtigo'>
					<input type='hidden' name='token' value=''>
					<input type='hidden' name='InfoArtigo[artigo]' value="{{ $productId }}">
					<h3>Pedido de informações</h3>
					<div class='form-group'>
						<label>Qual o seu nome?</label>
						<input type='text' class='form-control' data-msg-required="Preenchimento obrigatório" name='InfoArtigo[nome]'>
					</div>
					<div class='form-group'>
						<label>Qual o seu email?</label>
						<input type='text' class='form-control' data-msg-required="Preenchimento obrigatório" name='InfoArtigo[email]'>
					</div>
					<div class='form-group'>
						<label>Qual o seu contacto? (opcional)</label>>
						<input type='text' class='form-control' name='InfoArtigo[contacto]'>
					</div>
					<div class='form-group'>
						<label>Qual a sua questão?</label>
						<textarea class='form-control' name='InfoArtigo[mensagem]' data-msg-required="Preenchimento obrigatório"></textarea>
					</div>

					<div class='erros'></div>
					<button class='btn btn-default InfoArtigoSubmit'>Enviar</button>
				</form>
				<div class='WindowInfoArtigoResult'></div>
			</div>


			<div id="JanelaAvisoStock">
				<form method='post' class='AvisoStockForm form WindowForm'>
					<input type='hidden' name='token' value=''>
					<input type='hidden' name='product_id' value="{{ $productId }}">
					<h3>Avisar quando entrar em stock</h3>

										<div class='form-group'>
						<label>Qual o seu nome?</label>
						<input type='text' class='form-control' name='nome' data-msg-required="Preenchimento obrigatório">
					</div>
					<div class='form-group'>
						<label>Qual o seu email?</label>
						<input type='text' class='form-control' name='email' data-msg-required="Preenchimento obrigatório" data-msg-email="Email inválido">
					</div>
					<div class='form-group'>
						<label>Qual o seu contacto? (opcional)</label>
						<input type='text' class='form-control' name='contato'>
					</div>


					<div class='form-group'>
						<div class='erros'></div>
						<button class='btn btn-primary submit'>Enviar</button>
					</div>
				</form>
				<div class='WindowInfoArtigoResult Result'></div>
			</div>

			<div id='WindowConfirmaCompra'>
				<div class="WAlerta ConfirmaCarrinho">
					O artigo foi adicionado ao seu Carrinho, o que pretende fazer agora?<br>
					<a class="FancyClose btn btn-dark">Continuar a comprar</a>
					<a href="/carrinho" class="btn btn-light">Finalizar encomenda</a>
				</div>
			</div>
		</div>
	</div>


                </div>
@endsection

@push('page-scripts')
<script>
    dataLayer = window.dataLayer || [];
    dataLayer.push(@json($dataLayer));
    window.artigo = @json($artigo, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    window.Imperline = {};
    const productId = '{{ $productToken }}';
    const ProdVariations = @json($prodVariations, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    let _artigo = @json($artigoMini);
    let variations = ProdVariations;
</script>
<script src="/assets/vue/catalogo.js"></script>
@endpush

@push('scripts')
<script src="/assets/js/main.js"></script>
<script src="/assets/modulos/catalogo.js"></script>
@endpush
