/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).on("click", ".copyClipboard", function () {
    $("body").append("<input id='clip' value='" + $(this).data("copy") + "'>");
    /* Select the text field */
    copyText = document.getElementById("clip");
    copyText.select();
    copyText.setSelectionRange(0, 99999); /*For mobile devices*/
    document.execCommand("copy");
    $("#clip").remove();
    alert("Link copiado");
});


/* 
function listaCarrinho(json, cartElement){
    json.forEach(function(item) {
        var elemento = $(cartElement).find(".applicant").clone();

        $(newApplicant).find(".name").append(applicant.name);
        $(newApplicant).find(".email").append(applicant.email);
        $(newApplicant).find(".gender").append(applicant.gender);
        $(newApplicant).find(".age").append(applicant.age);
        $(newApplicant).appendTo(".applicant-list");
    });
} */
/*
function updateCartTotals(data) {
    //console.log(data);
    var total = 0;
    var pacotes = 0;

    data.subtotal = typeof data.subtotal == 'undefined' ? 0 : data.subtotal;
    data.total = typeof data.total == 'undefined' ? 0 : data.total;

    if ($(".CarrinhoCouponValor").val() != "")
        total = total - Number($(".CarrinhoCouponValor").val());

    $(".Subtotal span").text(Number(data.subtotal, 2).toFixed(2) + "€");
    $(".total span").text(Number(data.total, 2).toFixed(2) + "€");

    if (Number(data.desconto) > 0)
        $(".Desconto").show().find("span").text("-" + Number(data.desconto, 2).toFixed(2) + "€");
    else
        $(".Desconto").hide();

    $(".MiniCarrinho").html(data.minicarrinho);
    $(".CarrinhoTotalItems").text(data.items);


    //$.post("", { UpdateCarrinho: carrinho.artigosArray }, function(data){
    $(".CarrinhoArtigos ul").html(data.artigos);
    if (data.items > 0) {
        $(".CarrinhoArtigos .empty, .CarrinhoVazio").hide();
        $(".CarrinhoArtigos .actions, .AvancaPagamento").show();
        $(".Vazio").hide();
    } else {
        $(".Vazio").show();
        $(".CarrinhoArtigos .empty, .CarrinhoVazio").show();
        $(".CarrinhoArtigos .actions, .AvancaPagamento").hide();
    }

    //}, "json");
}
*/
/* 
function LoadMoreContent() {
    if (window.MoreContent) {
        $('#productsLoaderPreloader').show();
        if (window.fCategoria == "")
            window.fCategoria = window.fMainCategoria;

        $.post("", {
            loadMoreContent: true,
            categoria: window.fCategoria,
            tag: window.fTag,
            page: window.fPage + 1,
            //preco: window.fPrecos,
            tamanho: window.fTamanho,
            //cor: window.fCor,
            marca: window.fMarca,
            order: window.fOrder,
            artigosPorLinha: window.artigosPorLinha,
            ProductsPerPage: window.ProductsPerPage,
            search: window.fProcura,
            Idioma: window.Idioma,
            destaque: window.fDestaque,
            promo: window.fPromo
        }, function (data) {
            window.loading = false;
            if (data.html == "" && window.fPage == 0) {
                $(".SemArtigos").show();
                $("#productsLoader").html("")
            } else {
                if (data.html != "") {
                    //console.log("Page" + window.fPage);
                    if (window.fPage == 0)
                        $("#productsLoader").html(data.html);
                    else
                        $("#productsLoader").append(data.html);


                    $('.lazy').Lazy({
                        effect: "fadeIn",
                        effectTime: 500,
                        threshold: 0,
                    });

                    $(".countdown").countdown({
                        text: '%s%s:%s:%s'
                    });
                    

                    $(".SemArtigos").hide();
                    window.fPage = window.fPage + 1;
                    var vars = window.location;
                    var Qtamanho = '';
                    if (window.fTamanho != "")
                        Qtamanho = '&tamanho=' + window.fTamanho;
                    window.history.pushState({"html": "HTML", "pageTitle": "TITULO"}, "", vars.origin + vars.pathname + "?page=" + window.fPage + Qtamanho;
                } else {
                    window.MoreContent = false;

                }
            }
            if (data.artigos < 12)
                $(".CarregaMaisArtigos").hide();
            else
                $(".CarregaMaisArtigos").show();

            $('#productsLoaderPreloader').fadeOut("fast");
            $(".CarregaMaisArtigos").removeClass("active");
        }, "json");
    }
} */




const ListaArtigos = () => {


    const elements = document.querySelectorAll('.Filtro:checked, .Filtro[type="text"], select.Filtro');
    //console.log(elements);
    const result = Array.from(elements).reduce((acc, el) => {
        const group = el.dataset.group; // Obtém o data-group
        const value = el.value || el.textContent.trim(); // Obtém o valor ou texto
        //console.log(group, value);
        if (!acc[group]) {
            acc[group] = [];
        }
        
        acc[group].push(value);
            return acc;
    }, {});

    // Converte os arrays em strings separadas por vírgulas
    for (let group in result) {
        result[group] = result[group].join(',');
    }
    result['sort'] = $('.Ordenar:visible').val();
    //console.log(result);
    const FiltroUrl = new URLSearchParams(result).toString();


    /*
    const form = document.querySelector('.FiltroCatalogo');
    const data = {};

    form.querySelectorAll('input[type="text"], input[type="checkbox"]:checked, select, textarea').forEach(element => {
        let key = element.name; 
        let value = element.value;
        if (key.endsWith('[]')) {
            const arrayKey = key.slice(0, -2); // Remover `[]` do nome
            if (!data[arrayKey]) {
                Filtro.filtros[arrayKey] = [];
            }
            Filtro.filtros[arrayKey].push(value);
        } else {
            Filtro.filtros[key] = value;
        }
    });*/

    //let FiltroVars = $(".FiltroCatalogo input, .FiltroCatalogo select, .Ordenar:visible").serializeArray();
    //let FiltroUrl = $(".FiltroCatalogo input, .FiltroCatalogo select, .Ordenar:visible").serialize();
    //console.log(FiltroVars);
//, .Ordenar:visible
    $('#productsLoaderPreloader').show();
    
    /* Atualizar o URL */
    let URL = window.location.protocol + "//" + window.location.host + window.location.pathname;
    
    window.history.pushState({"html": "HTML", "pageTitle": "TITULO"}, "", URL + "?p="+(Filtro.page ?? 1) +"&"+ FiltroUrl);
             

     $.ajax({
            url: "",
            type: "POST",
            //data: { Filtro: FiltroVars },
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            },
            success: function (data) {
                $('.CarregaMaisArtigos').removeClass("active");
                window.loading = false;
                if (data.html == "" && Filtro.main) {
                    $(".SemArtigos").show();
                    $("#ListaArtigos").html("");
                    return false; 
                }
                
                if( data.page >= data.pages ) 
                    $(".CarregaMaisArtigos").hide();
                else
                    $(".CarregaMaisArtigos").show();
                    
                $(".SemArtigos").hide();
                //console.log(data.html);
                if (Filtro.replace) { 
                    $("#ListaArtigos").html(data.html);
                } else {
                    $("#ListaArtigos").append(data.html);
                }

                /* $(".countdown").countdown({
                    text: '%s%s:%s:%s'
                }); */
                $('#productsLoaderPreloader').fadeOut("fast");
                window.dataLayer = window.dataLayer || [];
			    dataLayer.push(JSON.parse(data.dataLayer)); //dataLayer.push(response.data.dl);
                //console.log(JSON.parse(data.dataLayer));
            }
        });

/*
    $.post("", { Filtro: FiltroVars }, function (data) {
        window.loading = false;
        if (data.html == "" && Filtro.main) {
            $(".SemArtigos").show();
            $("#ListaArtigos").html("");
            return false; 
        }
        
        if( data.page >= data.pages ) 
            $(".CarregaMaisArtigos").hide();
        else
            $(".CarregaMaisArtigos").show();
            
        $(".SemArtigos").hide();
        //console.log(data.html);
        if (Filtro.replace) { 
            $("#ListaArtigos").html(data.html);
        } else {
            $("#ListaArtigos").append(data.html);
        }

        $(".countdown").countdown({
            text: '%s%s:%s:%s'
        });*/

       // let filtro = $(".FiltroCatalogo input, .FiltroCatalogo select").serialize();
        /* 
        const qr = {}
        Object.entries(Filtro.filtros).forEach(entry => {
            const [key, value] = entry;
            if (value && typeof value != 'string')
            qr[key] = Object.values(value).join();
            else
            qr[key] = value;
    });*/
    
   
        
        /*
        if (data.artigos < 12)
            $(".CarregaMaisArtigos").hide();
        else
            $(".CarregaMaisArtigos").show();*/

        
     //   $(".CarregaMaisArtigos").removeClass("active");
   // }, "json");
    //}
};


/*
function SliderPrecoCatalogo() {

    var mySlider = $(".SliderPreco").bootstrapSlider();
    $(".SliderPreco").on('slide', function () {
        let v = $(this).val();
        v = v.split(",");
        if(v.length > 0)
            $(".SliderPrecoValores").text(`${v[0]}€ - ${v[1]}€`);
    });

    $(".SliderPreco").on('slideStop', function (e) {
        e.stopImmediatePropagation();

            Filtro.replace = true;
  
            Filtro.page = 1;
            ListaArtigos();
           
    });
}*/


function SideBarSlider(name) {
    /* */
    var mySlider = $(".Slider"+name).bootstrapSlider({
        tooltip:'hide'  
    });
    $(".Slider"+name).on('slide', function () {
        let v = $(this).val();
        let unit = $(this).data("unit");
        v = v.split(",");
        if(v.length > 0)
            $(".Slider"+name+"Valores").text(`${v[0]}${unit} - ${v[1]}${unit}`);
    });

    $(".Slider"+name).on('slideStop', function (e) {
        e.stopImmediatePropagation();   
        Filtro.replace = true;
        Filtro.page = 1; 
        ListaArtigos();
        return false;
    });
}