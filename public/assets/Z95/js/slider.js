function ss_init(){
    /* This code is executed after the DOM has been completely loaded */

    var totWidth=0;
    var positions = new Array();

    var btotWidth=0;
    var bpositions = new Array();
    $('#slides .slide').each(function(i){

        /* Traverse through all the slides and store their accumulative widths in totWidth */

        positions[i]= totWidth;
        totWidth += $(this).width();

    });

    $('#big_slides .slide').each(function(i){

        /* Traverse through all the slides and store their accumulative widths in totWidth */

        bpositions[i]= btotWidth;
        btotWidth += $(this).width();

    });

    $('#slides').width(totWidth);

    $('#big_slides').width(btotWidth);

    /* Change the cotnainer div's width to the exact width of all the slides combined */

    $('#PREVIEW span').unbind('click');
    $('#PREVIEW span').click(function(e,keepScroll){
        var height = 500;
        $('#gallery .slide img').each(function(i, el) {
            if ($(el).height() > height) { height = $(el).height(); }
        });
        $('#gallery').height(height);

        /* On a thumbnail click */

        $('#PREVIEW span').removeClass('selected');
        $(this).addClass('selected');

        var pos = $(this).prevAll().length;

        $('#slides').stop().animate({marginLeft:-positions[pos]+'px'},450);
        /* Start the sliding animation */

        e.preventDefault();
        /* Prevent the default action of the link */

    });

    $('#BIG_PREVIEW div').unbind('click');
    $('#BIG_PREVIEW div').click(function(e,keepScroll){

        /* On a thumbnail click */

        $('#BIG_PREVIEW div').removeClass('selected');
        $(this).addClass('selected');

        var pos = $(this).prevAll().length;

        if(bpositions[pos]<=0)
            bpositions[pos]=$('#img_big_main').width()*pos;

        $('#big_slides').stop().animate({marginLeft:-bpositions[pos]+'px'},450);
        /* Start the sliding animation */

        e.preventDefault();
        /* Prevent the default action of the link */

        $('#gallery_big').height($('#gallery_big .slide img').eq(pos).height());
    });

    $('#gallery').unbind('click');
    $('#gallery').click(function(e){
        var next_id = 0;
        var middle = $('#gallery').offset().left + $('#gallery').width() / 2;
        var images = document.getElementById('PREVIEW').getElementsByTagName('span');
        if(e.pageX<middle) {
            for( i=images.length-1; i >= 0; i--){
                if(images[i].className == 'selected'){
                    next_id = i-1;
                }
            }
            if(images[next_id] == undefined)
                next_id = images.length-1;
        } else {
            for( i=0; i < images.length; i++){
                if(images[i].className == 'selected'){
                    next_id = i+1;
                }
            }
            if(images[next_id] == undefined)
                next_id = 0;
        }
        $('#PREVIEW span').eq(next_id%$('#PREVIEW span').length).trigger('click',[true]);
    });

    $('#gallery_big').unbind('click');
    $('#gallery_big').click(function(e){
        var next_id = 0;
        var middle = $('#gallery_big').offset().left + $('#gallery_big').width() / 2;
        var images = document.getElementById('BIG_PREVIEW').getElementsByTagName('div');
        if(e.pageX<middle) {
            for( i=images.length-1; i >= 0; i--){
                if(images[i].className == 'selected'){
                    next_id = i-1;
                }
            }
            if(images[next_id] == undefined)
                next_id = images.length-1;
        } else {
            for( i=0; i < images.length; i++){
                if(images[i].className == 'selected'){
                    next_id = i+1;
                }
            }
            if(images[next_id] == undefined)
                next_id = 0;
        }
        $('#BIG_PREVIEW div').eq(next_id%$('#BIG_PREVIEW div').length).trigger('click',[true]);
    });

    /* On page load, mark the first thumbnail as active */

    /* End of customizations */

    return true;
}