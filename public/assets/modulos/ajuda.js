$(".Ajuda .Bloco a").click(function(e){
            var bloco = $(this).parents("div");
        
            if($(e.target).is('.active')) {
                bloco.find(".open").slideUp(300).removeClass("open");
                bloco.find(".active").removeClass("active").children("i").addClass('fa-minus').removeClass('fa-plus');
            }else {
                bloco.find(".open").slideUp(300).removeClass("open");
                if(bloco.find(".active").length > 0)
                    bloco.find(".active").removeClass("active").children("i").addClass('fa-minus').removeClass('fa-plus');
            
                $(this).addClass("active").children("i").addClass('fa-plus').removeClass('fa-minus');
                $(this).next().slideDown(300).addClass('open'); 
                
            }
            e.preventDefault();
        });