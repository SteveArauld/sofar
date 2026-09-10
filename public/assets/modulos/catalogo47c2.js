
$(document).on("click", ".filterOrder a", function (e) {
    //window.MoreContent = true;
    window.fOrder = $(this).data("value");
    window.fPage = 0;
    LoadMoreContent();
    e.stopPropagation();
});

$(document).on("click", ".tabs>.nav>a", function(e){
    e.preventDefault();
    $(this).parents(".nav").find("a").removeClass("active");
    $(this).addClass("active");
    $(this).parents(".tabs").children(".tab-content").find("> div").hide();
    $(this).parents(".tabs").find( $(this).attr("href") ).show();
});
/*
setTimeout(function(){
    container = $(".CatalogoSlider");
    container.scrollBy({
        left: 100, 
        behavior: 'smooth'
    });
}, 1000);
*/

if ($(".produtos-relacionados").length > 0) {
    /*
     $(window).scroll(function() {
     var distancia = $(".produtos-relacionados").offset().top;
     if($(window).height() - distancia + $(window).scrollTop() > 0) {
     
     if( !$(".produtos-relacionados").hasClass("loaded") ){
     
     $.post("", { CarregaRelacionados: $("#ArtigoId").val() }, function(data){
     $(".produtos-relacionados").addClass("loaded");
     $(".produtos-relacionados .GrelhaArtigos").html(data);
     });
     }
     }
     });*/
}



var optPreco = Number($(".Selects .OpcoesSelect:first-child select").find(':selected').data('preco'));
var optPrecoPromo = Number($(".Selects .OpcoesSelect:first-child select").find(':selected').data('precopromo'));


//console.log(optPreco);

if(!isNaN(optPreco)){
    if(optPrecoPromo != 0){
        $(".PrecoArtigo").text(optPrecoPromo.toFixed(2)+"€");
        $(".PrecoArtigoOld").text(optPreco.toFixed(2)+"€");
    }else if(optPreco != 0){
        $(".PrecoArtigo").text(optPreco.toFixed(2)+"€");
        $(".PrecoArtigoOld").text("");
    }
}
/*
const AtualizaPrecoArtigo2 = () => { 
    var preco = Number($(".PrecoArtigo").data("preco"));
    var precoantes = 0;
    if($(".Selects input").length > 0 || $(".Selects select").length > 0){
        if($(".Selects input").length > 0){
            var selected = $(".OpcoesArtigos input:checked");            
        }else if ($(".Selects select:visible:last").length > 0){
            var sel = $(".Selects select:visible:last");    
            if(sel.val() == '')
                var sel = $(".Selects select:visible").eq(-2);
            var selected = sel.find("option:selected");
        }
        
        if(Number(selected.data("precopromo")) != 0){
            preco = Number(selected.data("precopromo"));
            precoantes = Number(selected.data("preco"));
        }else if(Number(selected.data("preco")) != 0){
            preco = Number(selected.data("preco"));
        }
        
       
        
    }
    
    $(".formProduto .customField").each(function () {
        var precoOpcao = Number($(this).data("preco"));
        
        
        
        if (precoOpcao > 0 && $(this).val() != "") {
            preco += precoOpcao;
        }

        //Preços nas opções dos artigos
        if( Number($(this).find(':selected').data('preco') > 0)){
            preco = Number($(this).find(':selected').data('preco'));
        }
        
    });
    if(!isNaN(preco))
        $(".PrecoArtigo").text( Number(preco).toFixed(2) + "€");
    if( precoantes > 0 )
        $(".PrecoArtigoOld").text( Number(precoantes).toFixed(2) + "€");
    else
        $(".PrecoArtigoOld").text( "");
        
    
    $(".Selects select").removeClass(".error");
    $(".Selects div.error").remove();

}

*/
   
/*
$(document).on("change", ".OpcoesArtigos select", function(){
    
});
*/

window.addEventListener("pageshow", () => {
    $(".Selects select").val("");
});


$(document).on("change", ".Selects select", function(){
    var val = $(this).val();
    
    if($(this).find('option:selected').prop('disabled') == true)
        return false;
    
    if(val.startsWith("__")){
   
        var v = $(this).val().split("__");
        var path = "";
        $(".Selects select:not(.0)").parent().hide();
        //$(".Selects select.(."+$(".select.0").val()+")").parent().show();
        $.each(v, function(k, v){
            if(k > 0){
                path += "__"+v;
                //$(".__"+v).parent().show();
                $("."+path).parent().show();
              
            }
        });
        $("."+$(this).val()).val("").parent().show();
        $(".ProductToCartOption").val(""); 
    }else{
        $(".ProductToCartOption").val( $(this).val() ); 
    }
    AtualizaPrecoArtigo2();
    //validaSelects();
    
});

if ($('.SideMenu:visible').length > 0) {

    var menu = $('.SideMenu');

    var eTop = menu.parent().offset().top;
    var menuHeight = menu.height();

    var menuWidth = menu.width();
    var docHeight = $(document).height() + $(window).height();
    var move = 0;

    let top = 80;
    var bottom = 360;


    $(window).resize(function () {
        menuWidth = menu.parent().width();
        docHeight = $(document).height() + $(window).height();
        menu.css({width: menuWidth + "px"});
    });

    function ajustaFundo() {
        var janelaFundo = $(window).scrollTop() - $(document).height() + $(window).height() + $(".footer").outerHeight() + 40;
        menu.css({position: 'fixed', bottom: janelaFundo + "px", top: 'auto', width: $('.SideMenu').width() + "px"});
    }

    $(window).on("scroll", function (e) {

        var parentHeight = $(".CatalogoConteudo").parent().height();
        var src = $(window).scrollTop();

        if (move == 0) {
            eTop = menu.parent().offset().top;
            move = 1;
        }

        //if(docHeight != $(document).height())
        //    menu.animate({top: top}, 100);

        docHeight = $(document).height();
        winHeight = $(window).height();
        var DistanciaTopo = eTop - src;
        var DistanciaFundo = (src + menuHeight - docHeight);
        var limiteFundo = docHeight - (src + $(window).height() + $(window).height());
        var MenuFundo = 0 + limiteFundo;

        var dFundo = docHeight - src - menuHeight - top;
        var dTopo = eTop - src;
        var footer = $(".footer").outerHeight();

        var chegouFundo = footer - dFundo + 40;
        var janelaFundo = src - docHeight + winHeight + footer + 40;
        /*
         console.log(
         "DFundo", docHeight - src - menuHeight - top,
         "docheight", docHeight,
         "top", top,
         "DTopo", dTopo,
         "chegouFundo", chegouFundo,
         //"top", top,
         "src", src,
         "footer", footer,
         "janelaFundo", janelaFundo,
         "Parent", parentHeight,
         "Menu", menuHeight,
         )
         */


        if (parentHeight < menuHeight) {
            menu.css({position: '', width: 'auto'});
        } else if (dTopo <= top && chegouFundo < 0) {
            menu.css({position: 'fixed', top: top + "px", bottom: '', width: menuWidth + "px"});

        } else if (chegouFundo >= 0) {
            pos = janelaFundo;
            menu.css({position: 'fixed', bottom: pos + "px", top: '', width: menuWidth + "px"});
         
        } else {
            menu.css({position: '', width: 'auto'});
        }


    });
}
/*
 $(window).off('.affix');
 $(".FixedMenu").affix({
 offset: {
 top: 100,
 bottom: 350
 }
 }).on('affix.bs.affix', function () {
 setAffixContainerSize();
 });
 setAffixContainerSize();
 function setAffixContainerSize() {
 if ($(window).width() < 768) {
 $('.FixedMenu').css({position: 'initial', width: 'auto'});
 } else {
 $('.FixedMenu').css({position: 'fixed'});
 $('.FixedMenu').width($('.FixedMenu').parent().innerWidth() - 30);
 }
 }
 
 $(window).resize(function () {
 setAffixContainerSize();
 });
 */
/* */
 



/*
 $(".Filtro").click(function () {
 var tamanhos = Array();
 if ($(this).hasClass("active"))
 $(this).removeClass("active");
 else
 $(this).addClass("active");
 $(".FiltroTamanho.active").each(function () {
 tamanhos.push($(this).attr("data-valor"));
 });
 //document.location.hash = tamanhos.join(",");//tamanhos;
 window.fTamanho = tamanhos.join(",");
 window.fPage = 0;
 window.MoreContent = true;
 LoadMoreContent();
 return false;
 });*/

var Look = $('.BlocoLook .owl-carousel').owlCarousel({
    loop: true,
    margin: 10,
    //nav:true,
    autoplay: true,
    responsive: {
        0: {
            items: 2
        },
        600: {
            items: 2
        },
        1000: {
            items: 3
        }
    }
});

$('.BlocoLook .MoveRight').click(function () {
    Marcas.trigger('next.owl.carousel');
});
$('.BlocoLook .MoveLeft').click(function () {
    Marcas.trigger('prev.owl.carousel');
});

/*
 * Adicionar artigos ao carrinho
 */

function validaSelects() {
    var erros = 0;
    $(".Selects .OpcoesSelect:visible").each(function(){
        if($(this).children("select").val() == ''){
            if($(this).find("div.error").length == 0){
                $(this).append("<div class='error'>Escolha a opção</div>");
                $(this).children("select").addClass(".error");
            }  
            erros++;               
        }else{
            $(this).children("div.error").remove();
            $(this).children("select").removeClass("error");
        }
    });
    return erros;
}

$(".formProduto").validate({
    rules: {
    },
    errorPlacement: function (error, element) {
        if (element.attr("name") == "ProductToCart[option]") {
            error.insertAfter(".OpcoesArtigos");
        }/*else if (element.attr("name") == "ProductToCart[select]"){
            if(element.parent().is(":visible"))
                error.insertAfter(".OpcoesSelect");
        }*/else {
            error.insertAfter(element);
        }
        var $container = $("html,body");
        var $scrollTo = $('.error');
        $container.animate({scrollTop: $scrollTo.offset().top - $container.offset().top + $container.scrollTop() - 80, scrollLeft: 0},300);
    },
    submitHandler: function () {
        
     
        if(validaSelects() > 0) {
            return false;
        }
        
        return false;
        
        
        var eventid = 'addcart'+Math.floor((Math.random() * 99999999999999) + 1);
        
        $(".formProduto").ajaxSubmit({
            dataType: "json",
            data: { eventid: eventid },
            beforeSerialize: function() {
                $(".AddCarrinho span").hide();
                $(".addCartLoader").show();
            },
            success: function(data){
        
                $(".AddCarrinho span").show();
                $(".addCartLoader").hide();
   
                var e = $(".AddCarrinho");
                var ArtigoId = e.data("artigo");
                var ArtigoPreco = e.data("preco");

                

                ttq.track('AddToCart', {
                    content_id: e.data("artigo"),
                    content_type: 'product',
                    quantity: 1,
                    price: e.data("preco"),
                    value: e.data("preco"),
                    currency: 'EUR'
                });

                fbq('track', 'AddToCart', {
                    content_ids: [e.data("artigo")], 
                    content_type: 'product',
                    value: e.data("preco"),
                    currency: 'EUR'
                }, { eventID: eventid });

                gtag('event', 'add_to_cart', {
                    currency: 'EUR',
                    items: [{
                            item_id: e.data("artigo"),
                            item_name: e.data("nome"),
                            coupon: '',
                            discount: 0,
                            item_category: e.data("categoria"),
                            item_variant: 'black',
                            price: e.data("preco"),
                            currency: 'EUR'
                        }],
                    value: e.data("preco")
                });

                dataLayer.push({
                    'event': 'add_to_cart',
                    'ecommerce': {
                        'items': [{
                                'item_name': e.data("nome"), // Name or ID is required.
                                'item_id': e.data("artigo"),
                                'price': e.data("preco"),
                                'item_category': e.data("categoria"),
                                'index': 1,
                                'quantity': 1
                            }]
                    }
                });
                
                
                

                $(".CarrinhoTotalItems").text(data.artigosQtd);
                
                $(".CarrinhoArtigos ul").html(data.artigos);
                $(".CarrinhoArtigos .empty").hide();
                $(".CarrinhoArtigos .actions").show();
                $(".offside .MiniCarrinho").html(data.minicarrinho).parents(".Carrinho").addClass("open");
                alert("Carrinho")
                $(".overlay").fadeIn("fast");
                $(".offside .Carrinho").animate({ scrollTop: $(".offside .Carrinho").height() }, 1000);
            }
        });
    }
});
/* 

const AtualizaPrecoArtigo = () => { 
    var preco = Number($(".PrecoArtigo").data("preco"));
    if($(".OpcoesArtigos input").length > 0 || $(".OpcoesArtigos select").length > 0){
        if($(".OpcoesArtigos input").length > 0){
            var selected = $(".OpcoesArtigos input:checked");            
        }else if ($(".OpcoesArtigos select").length > 0){
            var selected = $(".OpcoesArtigos input:selected");       
        }
        if(Number(selected.data("preco")) != 0){
            //$(".PrecoArtigo").text(selected.data("preco")+"€").attr({ "data-preco": selected.data("preco") });
            preco = Number(selected.data("preco"));
        }
        if(Number(selected.data("precopromo")) != 0){
            //$(".PrecoArtigo").text(selected.data("precopromo")+"€").attr({ "data-preco": selected.data("precopromo") });
            preco = selected.data("precopromo");
        }
    }
    
    $(".formProduto .customField").each(function () {
        var precoOpcao = Number($(this).data("preco"));
        if (precoOpcao > 0 && $(this).val() != "") {
            preco += precoOpcao;
        }

        //Preços nas opções dos artigos
        if(Number($(this).find(':selected').data('preco')) > 0)
            preco = Number($(this).find(':selected').data('preco'));

    });
    $(".PrecoArtigo").text(preco.toFixed(2) + "€");
}
   

$(document).on("change", ".OpcoesArtigos select", function(){
    AtualizaPrecoArtigo();
});
$(document).on("click", ".OpcoesArtigos label", function(){
    if($(this).hasClass("disabled"))
        return false;
    $(".OpcoesArtigos label").removeClass("active");
    $(".OpcoesArtigos input").prop("checked", 0);
    $(this).addClass("active").find("input").prop("checked", 1);
    AtualizaPrecoArtigo();
    return false;
});
     */

$(document).on("change", ".formProduto .customField", function (data) {
    /*var preco = $(".PrecoArtigo").data("preco");
    
    $(".formProduto .customField").each(function () {
        var precoOpcao = Number($(this).data("preco"));
        if (precoOpcao > 0 && $(this).val() != "") {
            Artigo.preco += precoOpcao;
        }

        //Preços nas opções dos artigos
        if(Number($(this).find(':selected').data('preco')) > 0)
            artigoPreco = Number($(this).find(':selected').data('preco'));

    });
    $(".Now").text(artigoPreco.toFixed(2) + "€");
    
    */
    //AtualizaPrecoArtigo2();
});
/*
$(document).on("click", ".AddCarrinho", function () {
    $(this).parents("form").trigger("submit");
});*/




//$(".FiltroCatalogo").removeClass("open");

/*
$('#CarrouselProduto').owlCarousel({
    items:1,
    loop:true,
 

    pagination:false,
    autoplay: true,
    merge: true,
    autoplayTimeout:4000,
    responsive:{
        678:{
            mergeFit:true
        },
    }
});
*/
 var Categorias = $('.CatalogoCategorias').owlCarousel({
    items:5,
    loop:true,
    margin:10,
    nav:false,
    pagination:false,
    autoplay: true,
    merge: true,
    autoplayTimeout:4000,
    responsive:{
        678:{
            mergeFit:true
        },
    }
});



/* Slider Artigo */


var SliderArtigo = $('.SliderArtigo').owlCarousel({
    loop:true,
    margin:0,
    padding:0,
    nav:false,
    dots:false,
    autoplay: true,
    responsive:{
        0:{
            items:1
        },
    }
});
$(document).on("click", ".MudaSlider", function(){
    $(".MudaSlider").removeClass("active");
    $(this).addClass("active");
    SliderArtigo.trigger("to.owl.carousel", [$(this).data('slide'), 300]);
});

SliderArtigo.on('changed.owl.carousel', function(event) {
    var index = event.item.index - event.item.count;
    $(".MudaSlider").removeClass("active");
    $("div[data-slide='"+index+"']").addClass("active"); 
})
