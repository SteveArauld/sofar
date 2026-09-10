$(function(){
    
    /*
    $('.Contador div').countdown('2022/04/26', function(event) {
        $(this).html(event.strftime('%d dias %H:%M:%S'));
    });*/
    
    //$('.carousel').carousel();
    $('#MainBanner').owlCarousel({
        loop:true,
        margin:0,
        nav:false,
        dots:false,
        autoplay: true,
        lazy: true,
        animateOut: 'fadeOut',
        responsive:{
            0:{
                items:1
            },
        }
    });
    
    
    
    
    
    $('.SliderColecoes').owlCarousel({
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
                items:6
            }
        }
    });   
    
    $(".Open_HomeNewsletter").fancybox({
        helpers: { overlay: { locked: false } },
        /*afterClose: function() {
            $.post("", { EscondePopupNewsletter: true });
        },*/
        afterShow: function(){
            $.post("", { EscondePopupNewsletter: true });
            $("#HomeNewsletter .form").validate({
                rules:{
                    "Newsletter[email]": {
                        required: true,
                        email: true
                    }
                },
                submitHandler: function (form){
                    $.post("", $(form).serialize(), function(data){
                        
                        $("#HomeNewsletter").html(data.result);
                        setTimeout(function(){
                            $.fancybox.close()
                        }, 6000);
                      
                    },"json");
                    return false;
                }
            });
        }
    });
    setTimeout(function(){
        $(".Open_HomeNewsletter").trigger("click"); 
    }, 6000);
    
    $(".Open_HomeAfiliado").fancybox({
        helpers: { overlay: { locked: false } },       
    });
    
        $(".Open_HomeAfiliado").trigger("click"); 
    
});
        
