$(function(){

    
    $(".codigoPostal").mask("9999-999",{placeholder:" "});
$(".codigoPostalES").mask("99999",{placeholder:" "});

    
    $(document).on("click", ".AbreMenuLateral", function(e){
        e.stopImmediatePropagation();
        if($(".MenuLateral").hasClass("open"))
            $(this).find("i").addClass("fa-align-justify").removeClass("fa-times");
        else
            $(this).find("i").removeClass("fa-align-justify").addClass("fa-times");
        $(".MenuLateral").toggleClass("open");
    });
    $(document).on("click", ".MenuLateral.open .BlocoConteudo ", function(e){       
        e.stopImmediatePropagation();
        $(this).find("i").removeClass("fa-align-justify").addClass("fa-times");
        $(".MenuLateral").toggleClass("open");
    });
    
    $(document).on("click", ".limparAlteracoes a", function(){
        $(this).closest('form').find("input[type=text], textarea").val("");
    });
    
    
    $(document).on("click", ".consultaEncomenda", function(){
        var e = $(this);
        $.post("/cliente/historico", {id: e.data("encomenda")}, function( data ){
            $(".detalheEncomenda").html(data);
            $(".listaEncomendas > div").removeClass("active");
            e.addClass("active");
        });
    });
    
  
    $(document).on("change", ".mudaCookie", function(){
        var e = $(this);
        $.post("", { 
            mudaCookie: e.attr("name"),
            status: e.is(":checked")
        });
    });
    
   
    $("#eliminarForm").validate({
        rules: {
            "eliminar[password]": "required",
        },
        highlight: function(element) {
            $(element).parent().addClass("error");
        },
        unhighlight: function(element) {
            $(element).parent().removeClass("error");
        }
    }); 
    
    /* 
     * Dados Acesso
     * 
    
    $("#dadosAcessoForm").validate({
        rules: {
            "passwordatual": "required",
            "novapassword": "required",
            "confirmarnovapassword": {
                required: true, 
                equalTo: ".novaPassord"
            }
        },
        highlight: function(element) {
            $(element).parent().addClass("error");
        },
        unhighlight: function(element) {
            $(element).parent().removeClass("error");
        }
    }); 
    */
    /* 
     * Dados Pessoais 
     * 
    
    $("#dadospessoaisForm").validate({
        rules: {
            "fatura_nome": {
                required: true,
                wordCount: ["2"]
            },
            "entrega_nome": {
                required: true,
                wordCount: ["2"]
            },
        },
        messages: {
            "fatura_nome": {
                required: "Preenchimento obrigatório.",
                wordCount: "Insira nome e apelido.",
            },
            "entrega_nome": {
                required: "Preenchimento obrigatório.",
                wordCount: "Insira nome e apelido.",
            },
        }
    
    }); 
    */
    /* 
     * Endereços 
     * */
    
    $("#enderecosForm").validate({
        rules: {
            "endereco[nome]": "required",
            "endereco[morada]": "required",
            "endereco[cpostal]": "required",
            "endereco[cidade]": "required",
        },
        highlight: function(element) {
            $(element).parent().addClass("error");
        },
        unhighlight: function(element) {
            $(element).parent().removeClass("error");
        }
    });
    $(".Registar").click(function(){
        return $("#enderecosForm").valid();
    });   
    
    $(document).on("click", ".limparAlteracoes a", function(){
        $(this).closest('form').find("input[type=text], textarea").val("");
    });
    $(document).on("change", ".Cliente .MudaMorada", function(){
        if($(this).val() == "")
            $(this).closest('form').find("input[type=text], textarea").val("");
        else{
            var m = cliente.moradas[$(this).val()];
            $("[name='endereco[nome]']").val(m.nome);
            $("[name='endereco[morada]']").val(m.morada);
            $("[name='endereco[cpostal]']").val(m.cpostal);
            $("[name='endereco[cidade]']").val(m.cidade);
        }
    });
    
     $("#registoCliente").validate({
        rules: {
            "criaConta[email]": {
                required: true,
                email: true,
            },
            "criaConta[nome]": "required",
            "criaConta[morada]": "required",
            "criaConta[cpostal]": "required",
            "criaConta[cidade]": "required",
            "criaConta[telemovel]": {
                required: true,
                Comeca9: true
            },
            "criaConta[nif]": {
                minlength: 9,
                maxlength: 9,
                number: true
            },
            "criaConta[password]": {
                required: true,
                minlength: 5
            },
            "criaConta[confpassword]": {
                required: true,
                minlength: 5,
                equalTo: "#password"
            }
        },
        highlight: function(element) {
            $(element).parent().addClass("error");
        },
        unhighlight: function(element) {
            $(element).parent().removeClass("error");
        }
    });
    
    
    
    
    

});

