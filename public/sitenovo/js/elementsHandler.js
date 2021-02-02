
$( document ).ready(function() {
    resizeHorizontalLine(); 
    positionPercent();    
});



function resizeHorizontalLine(){    
    $(".horizontal-line1").css('width',
        $(window).width() -
        parseInt($(".container").css('marginRight')) - 
        parseInt($(".container").css('paddingRight')) - 1        
    );

    if($(window).width()<=991){
        console.log("if");
        $(".horizontal-line2").css('width',
        $(window).width() -
        parseInt($("#section_content .container").css('marginRight')) - 
        parseInt($("#section_content .container").css('paddingRight')) -1
        
    );
    if($(window).width()<=575){
        console.log("aqui.");
        $(".horizontal-line3").css('width',
        $(window).width() -
        parseInt($("#section_content .container").css('marginRight')) - 
        parseInt($("#section_content .container").css('paddingRight')) -30
    );
    }
   
    }else{
        console.log("else");
        $(".horizontal-line2").css('width',
        $(window).width() -
        parseInt($("#section_content .container").css('marginRight')) - 
        parseInt($("#section_content .container").css('paddingRight')) -
        parseInt($("#sidebar").css('width'))-1
    );
     $(".horizontal-line3").css('width',
        $(window).width() -
        parseInt($("#section_content .container").css('marginRight')) - 
        parseInt($("#section_content .container").css('paddingRight')) -
        parseInt($(".user-info").css('width'))-35
    );
    }

    
}

function positionPercent(){
    var element = $("#page-conta .course-outter .number");
    element.each(function(){
        var value = Number($(this).text());
        console.log(value);
        
        if(value <= 10)
            $(this).parent().css('left', value + 10 + '%');
        else if(value >=90)
            $(this).parent().css('left', value - 20 + '%');
        else if(value > 10)
            $(this).parent().css('left', value-10 + '%');
    });
    
}



$( window ).resize(function() {
    resizeHorizontalLine();  
    positionPercent();
});



