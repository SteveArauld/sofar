$(function () {

    /* $(".codigoPostal").mask("9999-999", { placeholder: " " });
    $(".codigoPostalES").mask("99999", { placeholder: " " });

 */
   /*
    $("#CheckoutForm input").on("blur", function () {
        $.ajax({
            url: "/carrinho/tmp",
            type: "POST",
            data: $(form).serialize(),
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            },
            success: function (data) {
                
            },
        });
    });*/



    /* Verifica pagamento MBWay 
    function startCountdown(durationInSeconds, elementId) {
        let timer = durationInSeconds;
        const display = document.getElementById(elementId);
        if(!display)
            return;

        const interval = setInterval(function () {
            // Cálculo de minutos e segundos
            let minutes = Math.floor(timer / 60);
            let seconds = timer % 60;

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            display.textContent = minutes + ":" + seconds;

            if(seconds % 10 === 0) { 
                $.post(root + "carrinho", { VerificaPagamentoMBWay: $(".VerificaPagamentoMBWay").data("hash") }, function (data) {
                    if (data.pago == "1") {
                        $(".porpagar").hide();
                        $(".pago").show();
                        clearInterval(interval);
                    }
                }, "json");
            }

            // Lógica de paragem
            if (--timer < 0) {
                clearInterval(interval);
                display.textContent = "00:00";
                display.style.color = "red";
            }
        }, 1000);
    }

    startCountdown(240, "countdown");
       */

/*

    var interval;
    if ($(".VerificaPagamentoMBWay .NaoPago:visible").length > 0) {
        interval = setInterval(function () {
            $.post(root + "carrinho", { VerificaPagamentoMBWay: $(".VerificaPagamentoMBWay").data("hash") }, function (data) {
                if (data.pago == "1") {
                    $(".NaoPago").hide();
                    $(".Pago").show();
                    clearInterval(interval);
                }
            }, "json");
        }, 5000);
    }
*/
/* 
    $(document).on("click", ".CupaoDesconto", function () {
        $(this).next().show().find("input").focus();
    });

    $(document).on("keypress", ".Cupao input", function (e) {
        if (e.keyCode === 13 && $(this).val() != "")
            $(".InserirCupao").trigger("click");
    });

    $(document).on("click", ".InserirCupao", function () {
        var cupao = $(this).next().val();
        if (cupao == "")
            alert("Insira um código válido!");
        else {
            $.post("", { consultaCupao: cupao }, function (data) {
                if (data.erro == "ERRO") {
                    alert(data.mensagem);
                } else {
                    var lCupao = $(".CarrinhoCouponLinha");
                    lCupao.show();

                    lCupao.find(".nome").text(data.nome);
                    lCupao.find(".preco b").text("-" + Number(data.coupon.valorDesconto, 2) + "€");
                    $(".CarrinhoCouponValor").val(data.valor);

                    $(".Desconto").show().find("span").text("-" + Number(data.coupon.valorDesconto, 2) + "€");

                    updateCartTotals(data);
                    $(".CarrinhoLista").html(data.carrinhoLista);
                }
            }, "json");
        }
        return false;
    }); */

    /*
     * Checkout
     */
/* 
    $(document).on("change", ".usacredito", function (e) {
        let credito = 0;
        let portes = Number(_Cart.portes) || 0;
        let total = _Cart.total + portes;
        let pontos = Number(_Cliente.pontos.credito_pontos) || 0;
        let afiliados = Number(_Cliente.pontos.credito_afiliados) || 0;

        console.log(_Cart);
        $(".CreditoPontos").hide()
        if ($(this).is(":checked")) {

            if (_Cart.total > (pontos + afiliados)) {
                credito = pontos + afiliados;
            } else {
                credito = _Cart.total;
            }

            $(".CreditoPontos").show().find("span").text("-" + credito.toFixed(2) + "€");


            total -= credito;
        }

        $(".total span").text(Number(total, 2).toFixed(2) + "€");
    });

    if ($(".contagemEnvio").length > 0) {
        var data = $(".contagemEnvio").data("hora").split("-");

        var countDownDate = new Date(data[0], data[1] - 1, data[2], data[3], data[4], 0)//.getTime();

        var now = new Date()//.getTime();
        var distance = countDownDate - now;
        if (distance < 0) {
            $(".BlocoEnvioHoje").html("Encomende agora e enviamos a sua encomenda já <b>amanhã</b>!")
        } else {
            var x = setInterval(function () {
                var now = new Date()//.getTime();
                var distance = countDownDate - now;

                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                var txt = "";
                if (days > 0)
                    txt += days + "d ";
                if (hours > 0)
                    txt += hours + "h ";
                if (minutes > 0)
                    txt += minutes + "m ";
                txt += seconds + "s ";

                $(".contagemEnvio").text(txt); //days + "d " + hours + "h "+ minutes + "m " + seconds + "s ")

                if (distance < 0) {
                    clearInterval(x);
                    $(".BlocoEnvioHoje").html("Encomende agora e enviamos a sua encomenda já <b>amanhã</b>!")
                }
            }, 1000);
        }
    } */

/* 


    $(".RemoveCoupon").click(function () {
        var row = $(this).parents("tr");
        $.post("", { "RemoveCoupon": "" }, function (data) {
            row.fadeOut("normal", function () {
                $(".CarrinhoCouponValor").val(0);
                updateCartTotals(data);
            });
        }, "json");
    });

    $(".btnCoupon").click(function () {
        if ($("#coupon").val() == "")
            alert("Insira um código válido!");
        else {
            $.post("", { consultaCupao: $("#coupon").val() }, function (data) {
                if (data.erro == "ERRO") {
                    alert(data.mensagem);
                } else {
                    var lCupao = $(".CarrinhoCouponLinha");
                    lCupao.show();

                    lCupao.find(".nome").text(data.nome);
                    lCupao.find(".valor").text(Number(data.valor, 2));
                    $(".CarrinhoCouponValor").val(data.valor);

                    updateCartTotals(data);
                }
            }, "json");
        }
        return false;
    });


    function JanelaPersonalizar() {


        $(".JanelaPersonalizar").fancybox({
            afterLoad: function (d, e) {

                var dataset = this.opts.$orig[0].dataset;


                $(".PersonalizarId").val(dataset.id);
                $(".TextoPersonalizar").val(dataset.texto);

                if (dataset.personalizavelimg != "")
                    $(".personalizavelimg").attr({ "src": dataset.personalizavelimg }).show();
                else
                    $(".personalizavelimg").attr({ "src": "" }).hide();

                if (dataset.texto != "")
                    $("div.RemovePersonalizacao").show();
                else
                    $("div.RemovePersonalizacao").hide();
            }
        });

    }

    JanelaPersonalizar();

    $(document).on("click", "a.RemovePersonalizacao", function (e) {
        $.post("", {
            personalizarTexto: "",
            personalizarId: $(".PersonalizarId").val(),
        }, function (data) {
            updateCartTotals(data);
            $(".CarrinhoLista").html(data.carrinhoLista);
            $.fancybox.close();
            JanelaPersonalizar();
        }, "json");
    });

    $(document).on("click", ".AplicaPersonalizacao", function (e) {
        e.stopImmediatePropagation();
        if ($("#JanelaPersonalizar").valid()) {
            $.post("", {
                personalizarTexto: $(".TextoPersonalizar").val(),
                personalizarId: $(".PersonalizarId").val(),
            }, function (data) {
                updateCartTotals(data);
                $(".CarrinhoLista").html(data.carrinhoLista);
                $.fancybox.close();
                JanelaPersonalizar();
            }, "json");
        }
    }); */
/*
    $("form.CheckoutLogin").validate({
        rules: {
            "username": {
                required: true,
                Comeca9: true
            },
            "password": {
                required: true,
            },
        },
        submitHandler: function (form) {
            $.ajax({
                url: "/cliente/login",
                type: "POST",
                data: $(form).serialize(),
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': window.CSRF_TOKEN
                },
                success: function (data) {
                    if (typeof data.erro === "undefined" || data.erro == '') {

                        if (data.valido) {

                            $("input[name='cliente[fatura][nome]'").val(data.cliente.fatura_nome);
                            $("input[name='cliente[fatura][morada]'").val(data.cliente.fatura_morada);
                            $("input[name='cliente[fatura][cpostal]'").val(data.cliente.fatura_cpostal);
                            $("input[name='cliente[fatura][cidade]'").val(data.cliente.fatura_cidade);
                            $("input[name='cliente[fatura][telefone]'").val(data.cliente.fatura_telefone);
                            $("input[name='client[email]'").val(data.cliente.fatura_email);
                            $("input[name='client[nif]'").val(data.cliente.fatura_nif);

                            $("input[name='cliente[entrega][nome]'").val(data.cliente.entrega_nome);
                            $("input[name='cliente[entrega][morada]'").val(data.cliente.entrega_morada);
                            $("input[name='cliente[entrega][cpostal]'").val(data.cliente.entrega_cpostal);
                            $("input[name='cliente[entrega][cidade]'").val(data.cliente.entrega_cidade);
                            $("input[name='cliente[entrega][telefone]'").val(data.cliente.entrega_telefone);

                            for (const [k, v] of Object.entries(data.cliente)) {
                                $("input[name='cliente[" + k + "]'").val(v);
                            };

                            $(".BlocoLogin").hide();
                            $(".inativo").animate({ "opacity": '1' }).find("input, button").prop('disabled', false);
                            $(".CheckoutLogin .Erros").html(data.erro).hide();
                        } else {
                            $(".CheckoutLogin .Erros").html(data.erro).show();
                        }

                    } else {
                        $(".CheckoutLogin .Erros").html(data.erro).show();
                    }
                }
            });
            return false;
        }
    });
*/
   /*  $(document).on("click", ".ClienteSim", function () {
        $(".ClienteNao i").removeClass("fa-check-square-o").addClass("fa-square-o");
        $(this).children("i").removeClass("fa-square-o").addClass("fa-check-square-o");
        $("#JaCliente").show();
        $(".inativo").css({ "opacity": '0.2' }).find("input, button").prop('disabled', true);
        return false;
    });
    $(document).on("click", ".ClienteNao", function () {
        $(".ClienteSim i").removeClass("fa-check-square-o").addClass("fa-square-o");
        $(this).children("i").removeClass("fa-square-o").addClass("fa-check-square-o");
        $("#JaCliente").hide();
        $(".inativo").css({ "opacity": '1' }).find("input, button").prop('disabled', false);
        return false;
    });

    $(document).on("change", ".Toggle", function () {
        var alvo = $(this).data("target");
        if ($(this).is(":checked"))
            $(alvo).show();
        else
            $(alvo).hide();
    });


    $(".ToggleEntrega").change(function () {
        if ($(this).is(":checked"))
            $(".DadosEntrega").hide();
        else
            $(".DadosEntrega").show();
    });

    $(".MetodosEnvioPT input").change(function () {
        if ($(".ToggleEntrega").prop("checked"))
            var cp = $("#cpostal").val();
        else
            var cp = $("#envioCpostal").val();

        if ($(".mostrapudos").is(":checked")) {
            $.post("", { listapudos: cp }, function (lista) {
                $(".listaPudos").html(lista).show().selectize({
                    valueField: "value",
                    labelField: "text",
                    searchField: "",
                    //options: [],
                    //create: false,
                    render: {
                        option: function (option, escape) {

                            console.log(option)

                            var t = option.text.split('---');
                            return (
                                "<div>" +
                                '<span class="title">' +
                                escape(t[0]) +
                                "</span>" +
                                '<span class="description">' +
                                escape(escape(t[1]) || "Morada indisponível.") +
                                "</span>" +
                                "</div>"
                            );
                        },
                        item: function (option, escape) {

                            console.log(option)

                            var t = option.text.split('---');
                            return (
                                "<div>" +
                                '<span class="title">' +
                                escape(t[0]) +
                                "</span>" +
                                '<span class="description">' +
                                escape(escape(t[1]) || "Morada indisponível.") +
                                "</span>" +
                                "</div>"
                            );
                        },
                    }
                });
            });
        } else {
            $(".listaPudos").hide();
        }


    });

    $(".MetodosEnvioES input").change(function () {
        if ($(".ToggleEntrega").prop("checked"))
            var cp = $("#cpostal").val();
        else
            var cp = $("#envioCpostal").val();

        if ($(".mostrapudos").is(":checked")) {
            $.post("", { listapudoses: cp }, function (lista) {
                $(".listaPudos").html(lista).show().selectize({
                    valueField: "value",
                    labelField: "text",
                    searchField: "",
                    //options: [],
                    //create: false,
                    render: {
                        option: function (option, escape) {
                            var t = option.text.split('---');
                            return (
                                "<div>" +
                                '<span class="title">' +
                                escape(t[0]) +
                                "</span>" +
                                '<span class="description">' +
                                escape(escape(t[1]) || "Dirección no disponible.") +
                                "</span>" +
                                "</div>"
                            );
                        },
                        item: function (option, escape) {
                            var t = option.text.split('---');
                            return (
                                "<div>" +
                                '<span class="title">' +
                                escape(t[0]) +
                                "</span>" +
                                '<span class="description">' +
                                escape(escape(t[1]) || "Dirección no disponible.") +
                                "</span>" +
                                "</div>"
                            );
                        },
                    }
                });
            });
        } else {
            $(".listaPudos").hide();
        }


    });


    function validaMetodoEntrega() {
        return false;
        var total = Number($(".CheckoutTotal").attr("data-valor"));
        if ($(".ToggleEntrega").prop("checked"))
            var cp = $("#cpostal").val();
        else
            var cp = $("#envioCpostal").val();

        if (typeof cp === "undefined")
            return false;

        if (cp.charAt(0) == "9") {
        
            //compra.transporte = Number($(".MetodoEnvio input:checked").data("custo2"));
            //$("#Pagamento-COBRANCA").parents("li").hide();
            //$("#Pagamento-MB").prop("checked", true);
            //compra.pagamento = 0;
        } else {
            $("#Pagamento-COBRANCA").parents("li").show();
           
            //compra.transporte = Number($(".MetodoEnvio input:checked").data("custo1"));

            if (compra.ofertaEntrega > 0)
                if (compra.ofertaEntrega > 0 && compra.ofertaEntrega < compra.total)
                    compra.transporte = 0;

        }
        $(".CustoTransporte").text(compra.transporte.toFixed(2));
        CalculaTotal();
        //console.log(compra.transporte);
    }

    function CalculaTotal() {
        console.log(compra);
        total = compra.subtotal + compra.pagamento + compra.transporte;
        var iva = total - (total / 1.23);
        $(".CustoTotal").text(total.toFixed(2));
        $(".CustoIVA").text(iva.toFixed(2));
        if (typeof compra.pagamento !== "undefined")
            $(".CustoPagamento").text(compra.pagamento.toFixed(2));
    }


    $(".codigoPostal").change(function () {
        validaMetodoEntrega();
    }); */

    /* Taxa de Pagamento */
    //console.log(compra.subtotal);
    //console.log($(".MetodoPagamento input:checked").data("minimo"));

    //CalculaTotal();

    /* Taxa de Entrega */
    //validaMetodoEntrega();
    /*
     $(".EscolhaEnvio").click(function () {
     validaMetodoEntrega();
     });
     */

    /*
     $(".MetodoEnvio input").click(function(){
     
     if(compra.subtotal < compra.ofertaEntrega)
     validaMetodoEntrega();
     else
     compra.transporte = 0;
     
     $(".CustoTransporte").text(compra.transporte.toFixed(2));
     total = compra.subtotal + compra.pagamento + compra.transporte;
     var iva = total - (total / 1.23);
     $(".CustoTotal").text(total.toFixed(2));
     $(".CustoIVA").text(iva.toFixed(2));
     });
     */
    /* if ($("#PagarEncomendaForm").length > 0) {

        $("#PagarEncomendaForm").validate({
            rules: {
                "pagarencomenda[fatura_nome]": {
                    required: true,
                    wordCount: ['2']
                },
                "pagarencomenda[fatura_apelido]": "required",
                "pagarencomenda[fatura_morada]": "required",
                "pagarencomenda[fatura_cidade]": "required",
                "pagarencomenda[fatura_cpostal]": {
                    required: true,
                    cod_postal: true
                },
                "pagarencomenda[fatura_email]": {
                    email: true,
                },
                "pagarencomenda[fatura_telefone]": {
                    required: true,
                    number: true,
                    Comeca9: true,
                    minlength: 9
                },

                "pagarencomenda[entrega_nome]": {
                    required: {
                        depends: function (element) {
                            return $(".ToggleEntrega").is(":not(:checked)");
                        }
                    }
                },
                "pagarencomenda[entrega_apelido]": {
                    required: {
                        depends: function (element) {
                            return $(".ToggleEntrega").is(":not(:checked)");
                        }
                    }
                },
                "pagarencomenda[entrega_morada]": {
                    required: {
                        depends: function (element) {
                            return $(".ToggleEntrega").is(":not(:checked)");
                        }
                    }
                },
                "pagarencomenda[entrega_cpostal]": {
                    required: {
                        depends: function (element) {
                            return $(".ToggleEntrega").is(":not(:checked)");
                        }
                    }
                },
                "pagarencomenda[entrega_cidade]": {
                    required: {
                        depends: function (element) {
                            return $(".ToggleEntrega").is(":not(:checked)");
                        }
                    }
                },
                "pagarencomenda[mbway]": "required"

            },
            errorPlacement: function (error, element) {
                if (element.attr("name") == "pagarencomenda[termos]") {
                    error.insertAfter("#labelTermos");
                } else {
                    error.insertAfter(element);
                }
            },
      
        });
    } */



});