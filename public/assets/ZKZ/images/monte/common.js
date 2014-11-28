function animateFrame(selector){
    function getTextBlock(obj, visible){
        if(obj !== null){
            var result = {};
            if(obj.find('.container').length){
                result.obj =  obj.find('.container');
                result.cssRules = {
                    'margin-top' : visible ? '0' : '10px',
                    'opacity' : visible ? 1 : 0
                };
            }else{
                if(obj.find('.incut').length){
                    result.obj = obj.find('.incut');
                    result.cssRules = {
                        'top' : visible ? '25%' : '28%',
                        'opacity' : visible ? 0.8 : 0
                    };
                }else
                    result = null;
            }
        }else
            result = null;
        return result;
    }

    var inObj = $('div[name='+selector+']');
    if(inObj.length == 0)
        return console.log('"'+selector+'" is not found');
    var inTextObj = getTextBlock(inObj, true);
    var offset_top = inObj.position().top;

    var outTopObj = inObj.prev().length ? inObj.prev() : null;
    var outTopTextObj = getTextBlock(outTopObj, false);

    var outBottomObj = inObj.next().length ? inObj.next() : null;
    var outBottomTextObj = getTextBlock(outBottomObj, false);

    $('.page_container').animate(
        {
            'margin-top': '-'+offset_top+'px'
        },
        {
            duration: 1000,
            start: function(){
                animate_busy = true;
            },
            complete: function(){
                if(inTextObj !== null){
                    inTextObj.obj.animate(
                        inTextObj.cssRules,
                        {
                            duration: 1000,
                            complete: function(){
                                animate_busy = false;
                            }
                        }
                    );
                }else{
                    animate_busy = false;
                }
                if(outTopTextObj !== null){
                    outTopTextObj.obj.css(outTopTextObj.cssRules);

                }
                if(outBottomTextObj !== null){
                    outBottomTextObj.obj.css(outBottomTextObj.cssRules);

                }
            }
        }
    );
}

function load_content(params){
    params = params || {};
    params.url = params.url || null;
    params.selector = params.selector || null;
    params.animate = params.animate || true;
    if(params.url === null)
        return console.log('url is not defined');
    $.ajax({
        url: params.url,
        success: function(response){
            if(params.selector !== null){
                if($(params.selector).length == 0){
                    return console.log('"'+params.selector+'" is not found');
                }
                $(params.selector).html(response);
                if(params.animate){
                    var parent = $(params.selector).parents('.page');
                    if(parent.length == 0)
                        return console.log('parent page for "'+params.selector+'" is not found');
                    animateFrame(parent.attr('name'));
                }
            }
        }
    });
}

function scrollToAnchor(sender, selector){
    var aTag = $(sender).data('anchor_name') || selector;
    animateFrame(aTag);
    location = location.protocol+'//'+location.host+location.pathname+'#'+aTag;
    return false;
}

$(document).ready(function(){

    $('.page').css('min-height', $(window).height()+'px' );
    animate_busy = false;

    var hash = location.hash.split('#');
    hash = (hash.length == 2) ? hash[1] : 'home';
    scrollToAnchor(null, hash);

    $('.page').mousewheel(function(event){
        if(animate_busy) return;
        var delta = event.originalEvent.wheelDelta;
        if(delta == undefined)
            delta = (event.originalEvent.deltaY)*-1;
        if((($(this).offset().top+$(this).height()) <= $(window).height()) && ($(this)[0] !== $('.page').last()[0]) && (delta < 0)){
            return scrollToAnchor(null, $(this).next().attr('name'));
        }
        if(($(this).offset().top > 0) && ($(this)[0] !== $('.page').first()[0]) && (delta > 0)){
            return scrollToAnchor(null, $(this).prev().attr('name'));
        }
        var stepValue = 200;
        var container_offset = parseInt($('.page_container').css('margin-top').split('px')[0], 10);
        var offset_top = container_offset;
        if(delta > 0) {
            offset_top += stepValue;
        } else{
            offset_top -= stepValue;
        }
        if(offset_top > 0) offset_top = 0;

        if((offset_top*-1 - $('.page').last().position().top) > stepValue/2){
            offset_top = ($('.page_container').height()-$(window).height())*-1;
        }
        $('.page_container').animate(
            {
                'margin-top': offset_top+'px'
            },
            {
                duration: 200,
                start: function(){
                    animate_busy = true;
                },
                complete: function(){
                    animate_busy = false;
                }
            }
        );
    });
});