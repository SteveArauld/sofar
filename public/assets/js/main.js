jQuery.validator.addMethod("Comeca9", function(phone_number, element) {
    phone_number = phone_number.replace(/\s+/g, ""); 
    return this.optional(element) || phone_number.match(/^9\d{8,}$/);
}, "Insira um numero de telemovel válido");
jQuery.validator.addMethod("Comeca6", function(phone_number, element) {
    phone_number = phone_number.replace(/\s+/g, ""); 
    return this.optional(element) || phone_number.match(/^6\d{8,}$/);
}, "Insira um numero de telemovel válido");

jQuery.validator.addMethod("phoneES", function(phone_number, element) {
    phone_number = phone_number.replace(/\s+/g, ""); 
    return this.optional(element) || phone_number.match(/^[6|7]\d{8,}$/);
}, "Insira um numero de telemovel válido");
/*
$.validator.addMethod("phoneES", function(value, element) {
    return this.optional(element) || /^((\+?34([ \t|\-])?)?[9|6|7]((\d{1}([ \t|\-])?[0-9]{3})|(\d{2}([ \t|\-])?[0-9]{2}))([ \t|\-])?[0-9]{2}([ \t|\-])?[0-9]{2})$/.test(value);
}, "Please specify a phone number");
*/
jQuery.validator.addMethod("extension", function(value, element, param) {
	param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
	return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
}, "Please enter a value with a valid extension.");

jQuery.validator.addMethod("cod_postal", function(cod_postal, element) {
    return this.optional(element) || cod_postal.match( /^\d{4}-\d{3}$/);
}, "Invalido codigo postal Formato xxxx-xxx");
jQuery.validator.addMethod("apenas_continente", function(cod_postal, element) {
    return this.optional(element) || cod_postal.match( /^[^9]/);
}, "Não realizamos envios para as ilhas.");

jQuery.validator.addMethod("cod_postal_es", function(cod_postal, element) {
    return this.optional(element) || cod_postal.match( /^\d{5}$/);
}, "Invalido codigo postal Formato xxxxx");

/*
jQuery.validator.addMethod("validate_email", function(value, element) {

    if (/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(value)) {
        return true;
    } else {
        return false;
    }
}, "Please enter a valid Email.");
*/
function getWordCount(wordString) {
  var words = wordString.split(" ");
  words = words.filter(function(words) { 
    return words.length > 0
  }).length;
  return words;
}
//add the custom validation method
jQuery.validator.addMethod("wordCount",
   function(value, element, params) {
      var count = getWordCount(value);
      if(count >= params[0]) {
         return true;
      }
   },
   jQuery.validator.format("A minimum of {0} words is required here.")
);






$(function(){




    /* Consent V2 */

    /* {
            ad_storage: 'granted',
            analytics_storage: 'granted',
            functionality_storage: 'granted', // basic functionality
            personalization_storage: 'granted',
            security_storage: 'granted'
        }
     window.dataLayer.push({
            event: 'consent',
            ...consent
        });  
 */
    
        

    
    

    
    
    /* Consent V2 */


    
    /* cookies */
    /* Cookies 
    function setCookie(name,value,days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }
    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }
    function eraseCookie(name) {   
        document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
    //setCookie("consentCookies", true, 20);
    //eraseCookie("consentCookies");
    //setCookie("consentCookies", false);
    let Consent = getCookie("consentCookies");
    
    //console.log("cookies:", Consent)
    
    if( Consent ){
        $(".cookies").hide();
    }else{
        setTimeout(function(){
            $(".cookies").slideDown("slow");
        },2000)
    }
    $(document).on("click", "._cookies_Rejeitar", function(){
        
        let consentCookies = {
            marketing: false,
            preferences: false,
            estatisticas: false,
            inclassificacos: false
        };
        setCookie("consentCookies", JSON.stringify(consentCookies), 2);
        $(".cookies").slideUp("slow");
    });
    $(document).on("click", "._cookies_PermitirSelecionados", function(){
        
        let consentCookies = {
            marketing: $("#cookies_necessarios").is("checked"),
            preferences: $("#consent_preferencias").is("checked"),
            estatisticas: $("#consent_estatisticas").is("checked"),
            inclassificacos: $("#consent_inclassificados").is("checked")
        };
        //console.log(consentCookies);
        setCookie("consentCookies", JSON.stringify(consentCookies), 2);
        $(".cookies").slideUp("slow");
    });
    $(document).on("click", "._cookies_PermitirTodos", function(){
        let consentCookies = {
            marketing: true,
            preferences: true,
            estatisticas: true,
            inclassificacos: true
        };
        //console.log(consentCookies);
        setCookie("consentCookies", JSON.stringify(consentCookies), 30);
        $(".cookies").slideUp("slow");
    });
    $(document).on("click", "._cookies_Personalizar", function(){
        $(".cookies .main").hide();
        $(".cookies .secondary").show();
    });


    */
    /* Facebook Login */
    
    window.fbAsyncInit = function() {
        // FB JavaScript SDK configuration and setup
        FB.init({
          appId      : '2147664568607670', // FB App ID
          cookie     : true,  // enable cookies to allow the server to access the session
          xfbml      : true,  // parse social plugins on this page
          version    : 'v3.2' // use graph api version 2.8
        });

        // Check whether the user already logged in
        if(!logged){
            FB.getLoginStatus(function(response) {
                if (response.status === 'connected') {
                    //display user data
                    getFbUserData();
                }
            });
        }
    };
    function fbLogin() {
        FB.login(function (response) {
            if (response.authResponse) {
                // Get and display the user profile data
                getFbUserData();
            } else {
                //document.getElementById('status').innerHTML = 'User cancelled login or did not fully authorize.';
            }
        }, {scope: 'email'});
    }

    // Fetch the user profile data from facebook
    function getFbUserData() {
        FB.api('/me', {locale: 'en_US', fields: 'id,first_name,last_name,email,link,gender,locale,picture'},
                function (response) {
                    $.post("", {
                        fbLogin_id: response.id,
                        fbLogin_nome: response.first_name,
                        fbLogin_apelido: response.last_name,
                        fbLogin_foto: response.picture.data.url,
                        fbLogin_email: response.email,
                        fbLogin_gender: response.gender,
                    }, function(){
                        location.reload();
                    });
                    /*
                    document.getElementById('fbLink').setAttribute("onclick", "fbLogout()");
                    document.getElementById('fbLink').innerHTML = 'Logout from Facebook';
                    document.getElementById('status').innerHTML = '<p>Thanks for logging in, ' + response.first_name + '!</p>';
                    document.getElementById('userData').innerHTML = '<h2>Facebook Profile Details</h2><p><img src="' + response.picture.data.url + '"/></p><p><b>FB ID:</b> ' + response.id + '</p><p><b>Name:</b> ' + response.first_name + ' ' + response.last_name + '</p><p><b>Email:</b> ' + response.email + '</p><p><b>Gender:</b> ' + response.gender + '</p><p><b>FB Profile:</b> <a target="_blank" href="' + response.link + '">click to view profile</a></p>';*/
                });
    }

    // Logout from facebook
    function fbLogout() {
        FB.logout(function () {
            window.location.href = "?logout=1";
        });
    } 
    
    $(document).on("click", ".fbLogin", function(){
        fbLogin();
    });
    /* 
$(".stars").starRating({
    starSize: 20,
    useFullStars: true,
    activeColor: "orange",
    ratedColor: "orange",
    callback: function(currentRating, $el){
        $(".startsRating").val(currentRating);
    }
});
$(".starsReadOnlyBig").starRating({
    starSize: 25,
    useFullStars: true,
    activeColor: "orange",
    ratedColor: "orange",
    readOnly: true
});
$(".starsReadOnlySmall").starRating({
    starSize: 15,
    useFullStars: true,
    activeColor: "#666666",
    ratedColor: "#999999",
    readOnly: true
}); */



$(".JanelaReview").fancybox({
    afterShow : function(){
        
        $(".ReviewsForm").validate({
            ignore:"",
            rules: {
                "Review[nome]": { required: true },
                "Review[email]": { required: true, email: true },
                "Review[check]": { required: true },
                "Review[stars]": { required: true },
                "g-recaptcha-response": { required: true },
            },
            messages: {
                "Review[stars]": "Com quantas estrelas avalia o artigo?",
                "g-recaptcha-response": "Tem que aceitar o reCaptcha",
            },
            errorLabelContainer: '.errorTxt'
        });
        
        $(".stars").starRating({
            starSize: 20,
            useFullStars: true,
            activeColor: "orange",
            ratedColor: "orange",
            callback: function(currentRating, $el){
                $(".startsRating").val(currentRating);
            }
        });
        $(".starsReadOnlyBig").starRating({
            starSize: 25,
            useFullStars: true,
            activeColor: "orange",
            ratedColor: "orange",
            readOnly: true
        });


        $(document).on("click", ".MostraFormularioReview", function(){
           $(".ListaReviews").hide(); 
           $(".ReviewsForm").show(); 
        });
        $(document).on("click", ".ReviewsForm .submit", function(e){
            e.stopImmediatePropagation();
            if($(".ReviewsForm").valid()){

                $.ajax({
                    url: "reviews/add",
                    type: "post",
                    data: $(".ReviewsForm").serialize(),
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    },
                    success: function(data){
                        $(".ReviewsForm").hide();
                        $("#JanelaReview").find(".Result").html(data.result).show();
                        setTimeout(function(){
                            $.fancybox.close();
                        }, 5000);
                    },
                    error: function(xhr, status, error) {
                        alert("Ocorreu um erro. Por favor tente novamente.");
                    }
                })
            }
            return false;
        });
    },
    helpers: { overlay: { locked: false } } 
});

    
  /*   
    
    $(".countdown").countdown({
        text: '%s%s:%s:%s'
    });
     */
    /*if($(".NavBar").length > 0){
        var MenuTop = $(".NavBar").offset().top;
        $(window).on("scroll", function (e) {
        
            if(MenuTop < $(document).scrollTop()){
                $(".Header").addClass("fixed");
                //$(".bigmenu").css({top: "auto" })
            }else{
                $(".Header").removeClass("fixed");
            // $(".bigmenu").css({top: 100 - $(document).scrollTop() + 60 } )
            }
            //console.log($(".NavBar").offset().top, $(document).scrollTop());
        });
    }*/
   
    var LazyOptions = {
        effect: "fadeIn",
        effectTime: 300,
        threshold: 0,
    }
   
    $('.lazy').Lazy( LazyOptions );


    $(".Janela").fancybox({
        helpers: { overlay: { locked: false } } 
    });
    
    $(document).on("click", ".FecharJanela", function(){
        $.fancybox.close();
    });
    
    $(".Window").fancybox({
        type: 'ajax',
        afterShow : function(){
            
        },
        helpers: { overlay: { locked: false } } 
    });
    
    
    $(".Open_EncomendasPorPagar").fancybox({
        helpers: { overlay: { locked: false } },
        afterClose: function() {
            $.post("", { EscondeAvisoEncomendasPagar: true });
        },
        afterShow: function(){
            $("#EncomendasPorPagar .close").click(function(){
                $.post("", { EscondeAvisoEncomendasPagar: true });
                $.fancybox.close();
            });
        }
    });
    $(".Open_EncomendasPorPagar").trigger("click"); 
    

    //$(document).on("keyup", ".ProcuraDesktop", )
/* 
    function Procurar(term){
        if(term != ""){
            
            var eventid = 'addcart'+Math.floor((Math.random() * 99999999999999) + 1);
            
            fbq('track', 'Search', {
                search_string: term
            }, { eventID: eventid });
            
            ttq.track('Search', {
                query: term
            }) 
            

        }else{
            $(".Body, .overlay").show();
            $(".ResultadosProcura").html("").hide();
        }
        return false;
    }
    $(document).on("click", ".ProcuraDesktop a", function(){
        var term = $(this).next("input").val();
        if(term == '')
            return false;
        Procurar(term);
      
    }); */
    

    
   /* 
    var timerid;
    $(document).on('keyup','.ProcuraDesktop input, .Procurar input', function(e) {
        var term = $(this).val();
        
        if(term == ''){
            $(".caixaProcura").hide();
            return false;
        }

        $(".caixaProcura").show();

s
        return false;
    });
     */
    
    $(".Open_MudaPais").fancybox({
       // helpers: { overlay: { locked: false } },
    });
    $(".Open_MudaPais").trigger("click"); 
    /*setTimeout(function(){
    
        $(".Open_HomeNewsletter").trigger("click"); 
    }, 6000);
    */


    /* Procurar artigos
    
    $(".Procurar a").click(function(){
        var ProcuraString = $(this).parents(".Procurar").find("input").val();
        if(ProcuraString != "")
            window.location = root+"catalogo/procura/"+ProcuraString;
        else
            return false;
    });
    $("body").on("keydown", ".Procurar input", function (e){
        if(e.keyCode == 13 && $(this).val() != "")
            window.location = root+"catalogo/procura/"+$(this).val();
    });
 */

    
    
    $(document).on("click", ".RemoveFromCart", function () {
        var parent = $(this).parents(".item");
        $.post("", {"RemoveFromCart": parent.data("id")}, function (data) {


            gtag('event', 'remove_from_cart', {
                currency: 'EUR',
                items: [{
                        item_id: parent.data("artigo"),
                        item_name: parent.data("nome"),
                        //item_category: 'pants',
                        //item_variant: 'black',
                        price: parent.data("preco"),
                        currency: 'EUR'
                    }],
                value: parent.data("preco")
            });

            updateCartTotals(data);
            $(".MiniCarrinho").html(data.minicarrinho);
           
        }, "json");
    });




   /*
    * Scroll to top
    */
   
    $(".backToTop a").click(function() {
        $("html, body").animate({ scrollTop: 0 }, "slow");
        return false;
    });
    

    
    $(document).on("click", ".ReenviarPagamentoMbway", function(){
        var numero = $(".ReenviarPagamentoMbwayNumero").val();
        var invoice = $(this).data("invoice");
        if(numero == ""){
            alert("Preencha o número de telefone");
            return false;
        }
        $.post("", { 
            ReenviarPagamentoMbway: numero,
            invoice: invoice
        }, function(data){
            if(data.result == 'ERRO')
                alert(data.result_message)
            else{
                $(".MBWayErro").addClass("hidden");
                $(".MBWaySucesso").removeClass("hidden");
            }
        }, 'json');
    });
    
    
    $(document).on("click", ".LinkRecuperarSenha, ._LinkRecuperarSenha", function(){
        $(".LoginCliente").hide();
        $(".RegistoCliente").hide();
        $(".RecuperarSenha").show();
        return false;
    });
    
    $(document).on("click", ".LinkRetroceder", function(){
        $(".LoginCliente").show();
        $(".RecuperarSenha").hide();
        return false;
    });
    
    $(document).on("click", ".MostraRegisto", function(){
        $(".LoginCliente").hide();
        $(".RegistoCliente").show();
        return false;
    });
    $(document).on("click", ".MostraLogin", function(){
        $(".LoginCliente").show();
        $(".RegistoCliente, .RecuperarSenha").hide();
        return false;
    });
    
  
    
    
    $(document).on("click", ".OpenFiltroCatalogo", function(){
        $(".offside > div:not(.FiltroCatalogo)").removeClass("open");
        var estado = $(".offside .FiltroCatalogo").hasClass("open") ? "" : "open";
        if($(".offside .FiltroCatalogo").hasClass("open")){
            $(".overlay").fadeOut("fast");
            $(".offside .FiltroCatalogo").removeClass("open");
        }else{
            $(".overlay").fadeIn("fast");
            $(".offside .FiltroCatalogo").addClass("open").find("input").focus();
        }
        return false;
    });
    /*
    $(document).on("click", ".offside .close, .offside .closebtn", function(){
        $(".overlay").fadeOut("fast");
        $(this).parents(".offside").children("div").removeClass("open");
    });
    
    $(document).on("click", ".Site > .overlay", function(){
        $(".overlay").fadeOut("fast");
        $(".offside").children("div").removeClass("open");
    });*/
    /*
    $(document).on("click", ".OpenCarrinho", function(e){
        e.stopImmediatePropagation();
        //$(".offside .Carrinho").addClass("open");
      
    
        //$(".offside > div:not(.Carrinho)").removeClass("open");
        //var estado = $(".offside .Carrinho").hasClass("open") ? "" : "open";
        if($(".offside .Carrinho").hasClass("open")){
            $(".overlay").fadeOut("fast");
            $(".offside .Carrinho").removeClass("open");
        }else{
            $(".overlay").fadeIn("fast");
            $(".offside .Carrinho").addClass("open");
            //alert("OK")
        }
        return false;
    });
    *//*
    $(document).on("click", ".OpenProcura", function(){
        $(".offside > div:not(.Procurar)").removeClass("open");
        var estado = $(".offside .Procurar").hasClass("open") ? "" : "open";
        if($(".offside .Procurar").hasClass("open")){
            $(".overlay").fadeOut("fast");
            $(".offside .Procurar").removeClass("open");
        }else{
            $(".overlay").fadeIn("fast");
            $(".offside .Procurar").addClass("open").find("input").focus();
        }
        return false;
    });
    
    $(document).on("click", ".OpenCliente", function(e){
        e.stopImmediatePropagation();
        $(".offside > div:not(.Cliente)").removeClass("open");
        //var estado = $(".offside .Cliente").hasClass("open") ? "" : "open";
        if($(".offside .Cliente").hasClass("open")){
            $(".overlay").fadeOut("fast");
            $(".offside .Cliente").removeClass("open");
        }else{
            $(".overlay").fadeIn("fast");
            $(".offside .Cliente").addClass("open");
        }
        return false;
    });*/
    
    $(document).on("click", ".OpenMenu", function(){
        
        $(".offside > div:not(.MobileMenu)").removeClass("open");
        //var estado = $(".offside .MobileMenu").hasClass("open") ? "" : "open";
        if($(".offside .MobileMenu").hasClass("open")){
            $(".overlay").fadeOut("fast");
            $(".offside .MobileMenu").removeClass("open");
        }else{           
            $(".overlay").fadeIn("fast");
            $(".offside .MobileMenu").addClass("open");
        }
        return false;
    });
    /*
    $(document).on("click", ".overlay", function(){
        $(this).fadeOut("fast");
        $(".offside .MobileMenu").removeClass("open");
        $(".offside .Cliente").removeClass("open");
        $(".offside .Carrinho").removeClass("open");
        $(".offside .Procurar").removeClass("open");
        $(".offside .FiltroCatalogo").removeClass("open");
        console.log("overlay");
    });
    */
    
    /*
    function OffsideRecuperarSenhaAlterar(){
        $("#OffsideRecuperarSenhaAlterar").validate({
            rules: {
                "RecuperarAcesso[novasenha]": {
                    required: true,
                    minlength: 6,
                    maxlength: 15,
                },
                "RecuperarAcesso[repetirsenha]": {
                    required: true,
                    minlength: 6,
                    maxlength: 15,
                    equalTo: ".novaSenha"
                }
            },
            submitHandler: function(form) {
                $.post($(form).attr("action"), $(form).serialize(), function(data){
                    if(typeof data.erro === "undefined") {
                        $(".RecuperarSenha").html(data.html);
                        $(".RecuperarSenha .erros").hide();
                    }else{
                        $(".RecuperarSenha .erros").html(data.erro).show();
                    }
                }, "json");
            }
        }); 
    }*/
    /*
    function OffsideRecuperarSenhaCodigo(){
        $("#OffsideRecuperarSenhaCodigo").validate({
            rules: {
                "RecuperarAcesso[codigo]": {
                    required: true,
                    minlength: 6,
                    maxlength: 6,
                    //remote: "/cliente"
                }
            },
            submitHandler: function(form) {
                $.post($(form).attr("action"), $(form).serialize(), function(data){
                    
                    if(typeof data.erro === "undefined") {
                        $(".RecuperarSenha").html(data.html);
                        $(".RecuperarSenha .erros").hide();
                        OffsideRecuperarSenhaAlterar();
                    }else{
                        $(".RecuperarSenha .erros").html(data.erro).show();
                    }
                }, "json");
                return false;
            }
        }); 
    }*/
    /*
    $(document).on("submit", "#OffsideRecuperarSenha", function(){
        if( $("#OffsideRecuperarSenha").valid ){
            $.post($(this).attr("action"), $(this).serialize(), function(data){
                if(typeof data.erro === "undefined") {
                    $(".RecuperarSenha").html(data.html);
                    $(".RecuperarSenha .erros").hide();
                    OffsideRecuperarSenhaCodigo();
                }else{
                    $(".RecuperarSenha .erros").html(data.erro).show();
                }
            }, "json");
        }
        return false;
    });
    */
    /*
    $("#OffsideLoginCliente").validate({
        rules: {
            "username": "required",
            "password": "required",
        },
        submitHandler: function(form){
             $.ajax({
                url: "/cliente/login",
                type: "POST",
                data: $(form).serialize(),
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': window.CSRF_TOKEN
                },
                success: function (data) {
                    location.reload();
                },
                error: function(response){
                    const errors = response.responseJSON && response.responseJSON.errors ? response.responseJSON.errors : {};

                    if(response.responseJSON.error) 
                        $(form).find(".erro").html(response.responseJSON.error).show();

                    Object.entries(errors).forEach(function([field, messages]) {
                        const inputField = $("[name='" + field + "']");
                        if (inputField.length) {
                            inputField.after("<label class='error' style='color:red; display:block;'>" + messages[0] + "</label>");
                        }
                    }); 
                }
            });
            return false;
        }
    }); 
    
    $("#OffsideRegistoCliente").validate({
        rules: {
            "nome": {
                required: true,
                wordCount: ["2"]
            },
            "username": {
                required: true,
                minlength: 9,
                maxlength: 9,
                Comeca9: true,
                remote: {
                    url: "/cliente/validarUsername",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    },
                    dataFilter: function(data) {
                        var json = JSON.parse(data);
                        if(typeof json.error === "undefined") 
                            return '"true"';
                        
                        return '"'+ json.error +'"';
                    }
                }
                
            },
            "email": {
                email: true,
            },
            "password": {
                required: true,
                minlength: 6,
                maxlength: 15
            },
        },
        submitHandler: function(form){
             $.ajax({
                url: "/cliente/registo",
                type: "POST",
                data: $(form).serialize(),
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': window.CSRF_TOKEN
                },
                success: function (data) {
                    $(".RegistoCliente")
                },
                error: function(response){
                    const errors = response.responseJSON && response.responseJSON.errors ? response.responseJSON.errors : {};

                    if(response.responseJSON.error) 
                        $(form).find(".erro").html(response.responseJSON.error).show();

                    Object.entries(errors).forEach(function([field, messages]) {
                        const inputField = $("[name='" + field + "']");
                        if (inputField.length) {
                            inputField.after("<label class='error' style='color:red; display:block;'>" + messages[0] + "</label>");
                        }
                    }); 
                }
            });
        },
    }); 
    */
    
    
    $(document).on("submit", "#OffsideRegistoCliente", function(){
        if( $("#OffsideRegistoCliente").valid ){
            
        }
        return false;
    });
    $("#OffsideRegistoClienteES").validate({
        rules: {
            "registo[nome]": {
                required: true,
                wordCount: ["2"]
            },
            "registo[username]": {
                required: true,
                minlength: 9,
                maxlength: 9,
                comeca6: true,
                remote: {
                    url: root + "/cliente",
                    data: {
                        // nick is id of input field from html
                        ValidarUsername: function() {
                            // get user input from field value
                            return $("input[name='registo[username]']").val();
                        }
                    },
                    type: 'POST',
                    dataFilter: function(data) {
                        
                        var json = JSON.parse(data);
                        
                        if(json.total == 0) {
                          return '"true"';
                      } else {
                          return '"'+ json.error +'"';
                      }
                    }
                }
                
            },
            "registo[email]": {
                email: true,
            },
            "registo[password]": {
                required: true,
                minlength: 6,
                maxlength: 15
            },
        },
        submitHandler: function(){
            $.post($("#OffsideRegistoClienteES").attr("action"), $("#OffsideRegistoClienteES").serialize(), function(data){
                $(".RegistoCliente").html(data.result)
            },"json");
        }
    }); 
    
    
    
    $(document).on("submit", "#OffsideRegistoClienteES", function(){
        if( $("#OffsideRegistoClienteES").valid ){
            
        }
        return false;
    });
    
    
   
   
    $('.telefone').keypress( function(e) {    
        if(!/[0-9]/.test( String.fromCharCode(e.which) )){
            return false;
        }
    });
    $('.SemEspacos').keypress( function(e) {    
        if(!/[0-9a-zA-Z-@._]/.test( String.fromCharCode(e.which) )){
            return false;
        }
    });
    $('.codigoPostal').keypress( function(e) {    
        if(!/[0-9-]/.test( String.fromCharCode(e.which) )){
            return false;
        }
    });
    $('.codigoPostalES').keypress( function(e) {    
        if($(this).val.length > 6)
            return false;
        
        if(!/[0-9-]/.test( String.fromCharCode(e.which) )){
            return false;
        }
    });
    
    /* $('.codigoPostalES').change(function(e) { 
        var e = $(this);
        if(e.val() != ""){
            $.post("", {
                verificaCPES : e.val()            
            }, function ( data ) {
                $( e.data("target") ).val(data.result);
                
                 if(data.ilhas == 1){
                    $(".Pagamento-COBRANCA").hide();
                    $(".Pagamento-CC").addClass("active").find("input").attr({"checked": "checked"});
                    if(typeof compra !== "undefined"){
                        compra.pagamento = 3;
                        $("[name='encomenda[pagamento]']").val( "3" );
                        //CalculaTotalCheckout();
                    }
                }else{
                    $(".Pagamento-COBRANCA").show().removeClass("active");
                }
                
            }, "json");
        }
    }); */
    /*/**/
    /*
     * Carrega mais artigos automaticamente
     */
    
    $(window).scroll(function() {
        var scroll = $(window).scrollTop();   
      
        if(scroll > 100)    
            $(".backToTop a").fadeIn("fast");
        else
            $(".backToTop a").fadeOut("fast");
    });
    
    
    
    
    
    /* Live sales */
    function MostraProximoDestaque(){
        var number = Math.floor( Math.random() * $('.UltimasVendas > div').length );
        var div = $('.UltimasVendas > div').eq(number);
        div.fadeIn("slow");
        setTimeout(EscondeDestaques, 8000);
    }
    function EscondeDestaques() {
        $('.UltimasVendas > div:visible').fadeOut("slow");
        setTimeout(MostraProximoDestaque, 15000);
    }
    $('.UltimasVendas .close').click(function(){
        $(this).parent().fadeOut("fast");
    });
    setTimeout(MostraProximoDestaque, 10000);
    /*
    $(".carousel-inner").swipe( {
        swipeLeft:function(event, direction, distance, duration, fingerCount) {
            $(this).parent().carousel('next'); 
        },
        swipeRight: function() {
            $(this).parent().carousel('prev'); 
        },
        //Default is 75px, set to 0 for demo so any distance triggers swipe
        threshold:0
    });*/
    /*-- Live sales */
    
    window.fadeIn = function(obj) {
        $(obj).fadeIn(1000);
    }
    
    $(".PopUpMessenger .Header > a").click(function(){
        var target = $(".PopUpMessenger .Content");
        $(".OpenMessenger").hide()
        
        if(target.is(":visible")){
            $(".OpenMessenger").show();
            $(".CloseMessenger").hide();
        }else{
            $(".OpenMessenger").hide();
            $(".CloseMessenger").show();
        }
        target.slideToggle();
        
        /*
        if(target.is(":visible"))
            target.slidedown();*/
    })
            
            
    
    
    
    
    $(".MostraTamanhos").click(function(){
       $(this).next().toggle(); 
    });
    
    
    
    
    //$(".BlocoDesktop").height( $(".Imagem").height()+"px" );
    
    $(".produto-miniaturas a").click(function(){
        $("#ImagemProduto").attr({ "href" : root + "media"+$(this).data("path")} );
        $("#ImagemProduto img").attr({ "src" : root + "images/600-600-R/"+$(this).data("path")} );
        $(".produto-miniaturas a").not(this).addClass("img-zoom");
        $(this).removeClass("img-zoom");
        return false;
    });
    
    $(document).on({
        mouseenter: function () {
            if($(this).data("hover") != ""){
                $(this).attr({"out": $(this).attr("src") });
                $(this).attr({"src": $(this).data("hover") });
            }
        },
        mouseleave: function () {
            if($(this).data("hover") != ""){
                $(this).attr({"src": $(this).attr("out") });
            }
        }
    }, ".preview-image img");
     
/*
    $(".MainMenuDesktop > ul > li").mouseover(function(){
        $(this).find("ul").show();
    }).mouseout(function() {
        $(this).find("ul").hide();
    });*/


    $(".mobileMenu a").click(function(e){
        e.stopImmediatePropagation();
        
        //console.log($(this).next("ul").length)
        if($(this).next("ul").length > 0){
            $(this).next("ul").slideToggle();
            $(this).toggleClass("open");
            return false; 
        }
    });

    $(".ExpandMenu").click(function(){
        if($(this).next("ul").is(":visible")){
            $(this).next("ul").slideUp();
            $(this).find("span").removeClass("minus"); //text("+");
            
        }else{
            $(this).next("ul").slideDown();
            $(this).find("span").addClass("minus");//.text("─");
        }
    });
    /*$(".ExpandMenu2").click(function(){
        $(this).next().next("ul").slideToggle();
    });*/
   
    /*$(".OpenMenu").click(function(){
        $(".MenuMobile > .Menu").slideToggle();
    });*/
    $(".CarrinhoMobileBtn").click(function(){
        $(".CarrinhoMobile").slideToggle();
    });
    $(".MenuMobile > ul > li > a").click(function(){
        var parent = $(this).parent();
        if( parent.find("ul").length > 0 ){
            parent.children("ul").slideToggle();
            return false;
        }else{
            return true;
        }  
    });
    
    
    
    
    
    $(".OpenWindowPedidoArtigo").fancybox({
        type: 'ajax',
        afterShow : function(){
            
             $(".WindowInfoArtigo").validate({
                rules: {
                    "InfoArtigo[nome]": "required",
                    "InfoArtigo[email]": { required: true, validate_email: true },
                    "InfoArtigo[mensagem]": "required",
                    "g-recaptcha-response": { required: true },
                },
                messages: {
                    "InfoArtigo[nome]": "Preenchimento obrigatório",
                    "InfoArtigo[email]": {
                        required: "Preenchimento obrigatório",
                        email: "Insira um email válido",
                        //remote: "Já existe uma conta com este email, clique <a href='"+root+"carrinho/checkout-metodo'>aqui</a> para fazer login."
                    },
                    "InfoArtigo[mensagem]": "Preenchimento obrigatório",
                    "g-recaptcha-response": "Tem que aceitar o reCaptcha",
                },
                submitHandler: function(form) {
                    $.post("", $(".WindowInfoArtigo").serialize(), function(data){
                        $(".WindowInfoArtigo").hide();
                        $(".WindowInfoArtigoResult").html(data.msg);
                        setTimeout(function(){
                            $.fancybox.close();
                        }, 5000);
                    }, "json");
                }
            });
        },
        helpers: { overlay: { locked: false } } 
    });
    
    $(".OpenWindowInfoArtigo").fancybox({
        type: 'ajax',
        afterShow : function(){
            $(".InfoArtigoSubmit").click(function(){
                
                $(".WindowInfoArtigo").validate({
                    rules: {
                        "InfoArtigo[nome]": { required: true },
                        "InfoArtigo[email]": { required: true, validate_email: true },
                        "InfoArtigo[mensagem]": { required: true }
                    },
                    messages: {
                        "InfoArtigo[nome]": "Preenchimento obrigatório",
                        "InfoArtigo[email]": {
                            required: "Preenchimento obrigatório",
                            email: "Insira um email válido",
                            //remote: "Já existe uma conta com este email, clique <a href='"+root+"carrinho/checkout-metodo'>aqui</a> para fazer login."
                        },
                        "InfoArtigo[mensagem]": "Preenchimento obrigatório",
                    }
                });
                if($(".WindowInfoArtigo").valid()){
                    $.post("", $(".WindowInfoArtigo").serialize(), function(data){
                        $(".WindowInfoArtigo").hide();
                        $(".WindowInfoArtigoResult").html(data.msg);
                        setTimeout(function(){
                            $.fancybox.close();
                        }, 5000);
                    }, "json");
                }
                return false;
            });
        },
        helpers: { overlay: { locked: false } } 
    });
    
    $(".OpenWindowAvisoStock").fancybox({
        //type: 'ajax',
        afterShow : function(){
            
            $(".AvisoStockForm").validate({
                rules: {
                    "nome": { required: true },
                    "email": { required: true, email: true },
                    //"g-recaptcha-response": { required: true },
                },
                messages: {
                   // "g-recaptcha-response": "Tem que aceitar o reCaptcha",
                },
            });
            
            $(document).on("click", ".AvisoStockForm .submit", function(){
                if($(".AvisoStockForm").valid()){
                    $.ajax({
                        url: "/catalogo/avisostock",
                        type: "POST",
                        data: $(".AvisoStockForm").serialize(),
                        dataType: "json",
                        headers: {
                            'X-CSRF-TOKEN': window.CSRF_TOKEN
                        },
                        success: function(data, textStatus, jqXHR){
                            $(".AvisoStockForm").hide();
                            $(".AvisoStockForm").parent().find(".Result").html(data.message).show();
                            setTimeout(function(){
                                $.fancybox.close();
                            }, 5000);
                        },
                        error: function(response, textStatus, errorThrown){
                            const errors = response.responseJSON && response.responseJSON.errors ? response.responseJSON.errors : {};
                            Object.entries(errors).forEach(function([field, messages]) {
                                const inputField = $("[name='" + field + "']");
                                if (inputField.length) {
                                    inputField.after("<label class='error' style='color:red; display:block;'>" + messages[0] + "</label>");
                                }
                            }); 
                        }
                    });
                    
                }
                return false;
            });
            return false; 
        },
        helpers: { overlay: { locked: false } } 
    });
    
    /*
     * Recuperar Senha
     */
    /**/
    
    
    function windowRecuperarSenhaAlterar(){
        $("#windowRecuperarSenhaAlterar").validate({
            rules: {
                "RecuperarAcesso[novasenha]": {
                    required: true,
                    minlength: 6,
                    maxlength: 15,
                },
                "RecuperarAcesso[repetirsenha]": {
                    required: true,
                    minlength: 6,
                    maxlength: 15,
                    equalTo: ".novaSenha"
                }
            },
            submitHandler: function(form) {
                $.post($(form).attr("action"), $(form).serialize(), function(data){
                    if(typeof data.erro === "undefined") {
                        $("#RecuperarSenha").html(data.w_html);
                        $("#RecuperarSenha .erros").hide();
                        $(".FecharELogin").click(function(){
                            $.fancybox.close();
                        });
                        
                        
                    }else{
                        $("#RecuperarSenha .erros").html(data.erro).show();
                    }
                }, "json");
            }
        }); 
    }
    
    function windowRecuperarSenhaCodigo(){
        $("#windowRecuperarSenhaCodigo").validate({
            rules: {
                "RecuperarAcesso[codigo]": {
                    required: true,
                    minlength: 6,
                    maxlength: 6,
                    //remote: "/cliente"
                }
            },
            submitHandler: function(form) {
                $.post($(form).attr("action"), $(form).serialize(), function(data){
                    
                    if(typeof data.erro === "undefined") {
                        $("#RecuperarSenha").html(data.w_html);
                        $("#RecuperarSenha .erros").hide();
                        windowRecuperarSenhaAlterar();
                    }else{
                        $("#RecuperarSenha .erros").html(data.erro).show();
                    }
                }, "json");
                return false;
            }
        }); 
    }
  
    
    $(".WRecuperaSenha").fancybox({
        type: 'ajax',
        //modal: true,
        autoDimensions : true,
        close: true,
        clickOutside: "",
        
        
        afterShow : function(){
            $("#RecuperarSenha form").validate({
                rules: {
                    "RecuperarAcesso[telemovel]": { 
                        required: true, 
                        Comeca9: true 
                    }
                },
                submitHandler: function(form){
                    
                    $.post($(form).attr("action"), $(form).serialize(), function(data){
                        if(typeof data.erro === "undefined") {
                            $("#RecuperarSenha").html(data.w_html);
                            $("#RecuperarSenha .erros").hide();
                            windowRecuperarSenhaCodigo();
                        }else{
                            $("#RecuperarSenha .erros").html(data.erro).show();
                        }
                    }, "json");
                    
                    return false;
                }
            });
        },
        helpers: { overlay: { locked: false } } 
    });
   
   
   /*
    * Filtros Catalogo
    */
    
    //SliderPrecoCatalogo();

    
    SideBarSlider('Preco');
    SideBarSlider('Altura');
    SideBarSlider('Largura');
    SideBarSlider('Comprimento');

/*
    $(".Filtro").on("change", function (e) {
        //alert('change');
        const elements = document.querySelectorAll('.Filtro:checked, .Filtro[type="text"]');
        console.log(elements);
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
        //console.log(result);
        const queryString = new URLSearchParams(result).toString();
        //console.log(queryString);

    });*/
    

  
    $(".FiltroCatalogo input:not(.Slider), .FiltroCatalogo select").on("change", function (e) {
        e.stopImmediatePropagation();   
        Filtro.replace = true;
        Filtro.page = 1;
        //Filtro.json = true;
        //Filtro.filtros = $(".FiltroCatalogo input, .FiltroCatalogo select").serialize();
        //Filtro.filtros[$(this).data("field")] = $(this).val();

        console.log(Filtro);

        if ( $(".AplicarFiltro").prev().is(":visible") ) {
           //$(".AplicarFiltro").css('visibility', 'visible');
        } else {
            ListaArtigos();
            //$(".AplicarFiltro").css('visibility', 'hidden');
        }
    });

    $(document).on("change", ".Ordenar", function (e) {
        e.stopImmediatePropagation();
        $(".Ordenar").val($(this).val());
        Filtro.replace = true;
        Filtro.page = 1;
        ListaArtigos();
        return false;
        
        if ( $(".AplicarFiltro").prev().is(":visible") ) {
           //$(".AplicarFiltro").css('visibility', 'visible');
        } else {
            ListaArtigos();
            //$(".AplicarFiltro").css('visibility', 'hidden');
        }
    });

    $(document).on("click", ".Filtro", function (e) {
        e.stopImmediatePropagation();
     /*
        var field = $(this).data("field");
        var tipo = $(this).data("tipo") == '' ? 'F' : $(this).data("tipo");
        var valor = $(this).data("id");
        var status = $(this).hasClass("active");
      
        if (typeof Filtro.filtros[field] == "undefined")
            Filtro.filtros[field] = [];

        var array = Filtro.filtros[field];
       

        if (!status) {
            $(this).addClass("active");
            array.push(valor);
        } else {
            $(this).removeClass("active");
            array = array.filter(function (value, index, arr) {
                return value != valor;
            });
        }
        
        

        Filtro.filtros[field] = array;
        
        
        
        Filtro.ListaArtigos = "update";
        Filtro.main = true;
        Filtro.page = 1;



        if ($(".AplicarFiltro").prev().is(":visible")) {
            //alert("mobile");
           // $(".AplicarFiltro").css('visibility', 'visible');

        } else {
            ListaArtigos();
          //  $(".AplicarFiltro").css('visibility', 'hidden');
        }*/
    });

    $(document).on("click", ".AplicarFiltro", function () {
        //Filtro.filtros = $(".FiltroCatalogo input, .FiltroCatalogo select").serialize();
        Filtro.replace = false;
        ListaArtigos();
        $(".overlay").fadeOut("fast");
        $(".FiltroCatalogo").removeClass("open");
      // $(".AplicarFiltro").css('visibility', 'hidden');
    });

    $(window).scroll(function () {
        //return false; 
        if ($('.CarregaMaisArtigos:visible').length > 0) {
            var hT = $('.CarregaMaisArtigos').offset().top,
                    hH = $('.CarregaMaisArtigos').outerHeight(),
                    wH = $(window).height(),
                    wS = $(this).scrollTop();
            if (wS > (hT + hH - wH)) {
                $('.CarregaMaisArtigos:not(.active)').addClass("active").trigger("click");
            }
        }
    });


    $(document).on("click", ".CarregaMaisArtigos", function (e) {
        e.stopImmediatePropagation();
        //Filtro.filtros = $(".FiltroCatalogo input, .FiltroCatalogo select").serialize();
        Filtro.replace = false;
        //Filtro.main = false;
        Filtro.page++;
        //console.log("Pagina", Filtro.page);
        //alert();
        ListaArtigos();
        return false; 
    });


    $(document).on("click", ".AbreFiltro", function () {
        $(".FiltrosMenu").addClass("open");
    });
    $(document).on("click", ".FiltrosMenu .bg", function () {
        $(this).parents().removeClass("open");
    });
   
    /*
    * FIM Filtros Catalogo
    */
   
   
   
    
    $(document).on("click", ".Wishlist", function(e){
        e.stopImmediatePropagation();
        var e = $(this);
        var eventid = 'addwish'+Math.floor((Math.random() * 99999999999999) + 1);

        $.ajax({
            url: "/wishlist/toggle",
            type: "POST",
            data: {
                product : e.data("artigo"),
                eventid : eventid
            },
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            },
            success: function (data) {
                if(e.hasClass('active')){
                    e.removeClass("active");
                    e.find("svg").attr("fill", "none");
                    var msg = "O artigo foi removido da sua Wishlist!";
                }else{
                    e.addClass("active");
                    e.find("svg").attr("fill", "#e5007d");
                    e.find("svg").attr("stroke", "#e5007d");
                    var msg = "O artigo foi adicionado à sua Wishlist!";
                }

                if(data.dl){
                    window.dataLayer = window.dataLayer || [];
                    dataLayer.push(data.dl);
                    console.log("DL", data.dl);
                }

                $(".WishlistItems").text(data.items);
            }
        });
        return false;
    });


    $('.Sliders').owlCarousel({
        loop:true,
        margin:10,
        nav:false,
        autoplay: true,
        responsive:{
            0:{
                items:2
            },
            700:{
                items:2
            },
            1000:{
                items:4
            }
        }
    });
    $('.SlidersMini').owlCarousel({
        loop:true,
        margin:10,
        nav:false,
        autoplay: true,
        responsive:{
            0:{
                items:2
            },
            700:{
                items:4
            },
            1000:{
                items:6
            }
        }
    });

    
    $(document).on("click", ".Preview", function(){
        var artigo = $(this).data("artigo");
        $.fancybox({
            href: "?WPreviewArtigo="+artigo,
            type: "ajax",
            minHeight: 0,
            margin: 0,
            padding: 0,
         
            afterShow : function(){ },
            helpers: { overlay: { locked: false } } 

        });
    });
    
    
    
    
    $(document).on("click", ".FancyClose", function(){
        $.fancybox.close();
    });
    
    $(document).on("click", ".Quantidade .plus", function(){
        var input = $(this).parents(".Quantidade").find("input");
        var max = Number(input.attr("max"));
        var min = Number(input.attr("min"));
        if( isNaN(max) || input.val() < max )
            input.val( Number(input.val()) + 1 ).change();
    });
    
    $(document).on("click", ".Quantidade .minus", function(){
        var input = $(this).parents(".Quantidade").find("input");
        if( input.val() > 1 )
            input.val( Number(input.val()) - 1 ).change();
    });
    
    $(document).on("click", ".Colapse", function(){
        var target = $(this).next("div");
        if(target.hasClass("open")){
            target.removeClass("open").hide();
            $(this).children("i").switchClass("fa-minus", "fa-plus");
        }else{
            target.addClass("open").show();
            $(this).children("i").switchClass("fa-plus", "fa-minus");
        }
    });
    
    $(".footer .expand").click(function(){
        if($(this).hasClass("fa-plus")){
            $(this).removeClass("fa-plus").addClass("fa-minus").parents(".Bloco").find("ul").slideDown();
        }else{
            $(this).removeClass("fa-minus").addClass("fa-plus").parents(".Bloco").find("ul").slideUp();
        }
    });
    
    
    
        
    $(".fancybox").fancybox({});
      
    $(".Categorias a.expand").click(function(){
        var ul = $(this).next();
        if(ul.is(":visible")){
            $(this).children("i").removeClass("fa-minus").addClass("fa-plus");
            $(this).next().slideUp();
        }else{
            $(this).children("i").removeClass("fa-plus").addClass("fa-minus");
            $(this).next().slideDown();
        }
        $(".expand").not(this).children("i").removeClass("fa-minus").addClass("fa-plus");
        $(".expand").not(this).next("ul").slideUp();
    });
	
	

    
    
    /* $('.VendaConjunta .MoveRight').click(function() {
            VendaConjunta.trigger('next.owl.carousel');
        });
        $('.VendaConjunta .MoveLeft').click(function() {
            VendaConjunta.trigger('prev.owl.carousel');
        });
        
        var VendaConjunta = $('.VendaConjunta .owl-carousel').owlCarousel({
            loop:true,
            margin:10,
            //nav:true,
            autoplay: false,
            lazyLoad : true,
            responsive:{
                0:{
                    items:2
                },
                600:{
                    items:2
                },
                1000:{
                    items:4
                }
            }
        });
     */
    

        
        

  
 
});

                    