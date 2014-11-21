
var global = new function(){

    this.init = function(){
        document.onkeydown = this.check_key;
        catalogue.init_search_button();
//        catalogue.loadHotLine();
        //$('#CONTAINER').height($('#CONTAINER [class="page"]').height());


        //this.drawTopLine();

        $(window).resize(function() {
            global.drawTopLine();
        });
    };

    this.hide_go_top = function() {
        $('.go-top-container').remove();
    };

    this.draw_go_top = function() {
        var margin = Math.max(100,parseInt((($(window).width() - $('.page_container').width())*0.5)));
        $('.go-top-container').css({'width':margin});

        $('.go-top-container').hover(function() {
                if ($('.go-top-container .button-down:visible').length == 0) {
                    $('.go-top-field').addClass('hover');
                } else {
                    $('.go-top-field').removeClass('hover');
                }
                $('.go-top-container').addClass('hover');
            },
            function() {
                if ($('.go-top-container .button-down:visible').length == 0) {
                    $('.go-top-field').removeClass('hover');
                } else {
                    $('.go-top-field').removeClass('hover');
                }
                $('.go-top-container').removeClass('hover');
            });

        $('.go-top-container').click(function () {
            if ($('.go-top-container .button-down:visible').length == 0) {
                $('.go-top-container .button-down')
                    .attr({t:$('body').scrollTop()})
                    .show();
                $('.go-top-field').removeClass('hover');
                $('.go-top-container .button-top').hide();
                $('body').scrollTop(0);
            } else {
                $('.go-top-container .button-down').hide();
                $('body').scrollTop($('.go-top-container .button-down').attr('t'));
                $('.go-top-container .button-top').show();
                $('.go-top-container').addClass('hover');
                $('.go-top-field').addClass('hover');
            }
        });

        $(window).scroll(function() {
            if ($('body').scrollTop() > 50) {
                $('.micro-cart').fadeIn();
                $('.go-top-container .button-down').fadeOut();
                $('.go-top-container, .go-top-container .button-top').fadeIn();
                if ($('.go-top-container').hasClass('hover')) {
                    $('.go-top-field').addClass('hover');
                }
                if (!!$('.go-top-container .button-down').attr('t')) {
                    $('.go-top-container .button-down').removeAttr('t');
                }
            } else {
                $('.micro-cart').fadeOut();
                if ($('.go-top-container .button-down:visible').length == 0) {
                    if (!$('.go-top-container .button-down').attr('t')) {
                        $('.go-top-container').fadeOut();
                    } else {
                        $('.go-top-container .button-down').fadeIn();
                        $('.go-top-container .button-top').fadeOut();
                        $('.go-top-field').removeClass('hover');
                    }
                }
            }
        });

        $(window).resize(function() {
            var margin = Math.max(100,parseInt((($(window).width() - $('.page_container').width())*0.5)));
            $('.go-top-container').css({'width':margin});
        });
    };

    this.drawTopLine = function(){
        if (!$('.hotline').length) { return; }

        $('.hotline').show();
        $('.hotline').css('width', $('.page_container').width());
        $('.hotline').css('left', $('.page_container').offset().left);
    };
    this.check_key = function(e){
        if (!document.getElementById) return;

        if (window.event) e = window.event;

        if (e.ctrlKey) {
            var link = null;
            var href = null;
            switch (e.keyCode ? e.keyCode : e.which ? e.which : null) {
                case 0x25:
                    link = document.getElementById ('prev_page');
                    break;
                case 0x27:
                    link = document.getElementById ('next_page');
                    break;
            }
            if (link && link.href) document.location = link.href;
            if (href) document.location = href;
        }
    };

    this.get_coords = function(obj) {
        var curleft = curtop = 0;
        if (obj.offsetParent) {
            do {
                curleft += obj.offsetLeft;
                curtop += obj.offsetTop;
            } while (obj = obj.offsetParent);
        }
        return [curleft,curtop];
    };

    this.word_ending = function(number, word){
        var letters = [];
        var group	= [];
        letters['СЏ'] = 1;
        letters['СЂ'] = 2;
        letters['СЊ'] = 3;
        group[1] = ['СЏ', 'Рё', 'Р№'];
        group[2] = ['СЂ', 'СЂР°', 'СЂРѕРІ'];
        group[3] = ['СЊ', 'Рё', 'РµР№'];

        var ending = word.substr((word.length-1), 1);
        var letter_group = ( letters[ending] != undefined ) ? letters[ending] : 0;
        var group_id = ( group[letter_group] != undefined ) ? group[letter_group] : 0;

        if( group_id != 0){
            var word = word.substr(0, word.length-1 );
            number += '';
            var two_last_digits = Number(number.substr(-2, 2));

            if(two_last_digits > 10 && two_last_digits < 20){
                ending = group_id[2];
            }else{

                var one_last_digit = Number(number.substr(-1, 1));

                if(one_last_digit == 1){
                    ending = group_id[0];
                } else if (one_last_digit > 1 && one_last_digit < 5){
                    ending = group_id[1];
                }else{
                    ending = group_id[2];
                }
            }
            word += ending;
        }
        return word;
    };

    this.positionate = function(obj, showTop){
        showTop = (showTop == undefined ? 0 : showTop);
        $(obj).css({'left':(parseInt((window.innerWidth - obj.offsetWidth)/2))});
        $(obj).css({'top':(showTop ? (Math.max(10, parseInt(showTop))) : Math.max(10, (window.innerHeight - obj.offsetHeight)/2))});
    };

    this.browserDetectNav = function(chrAfterPoint){
        var
            UA=window.navigator.userAgent,       // СЃРѕРґРµСЂР¶РёС‚ РїРµСЂРµРґР°РЅРЅС‹Р№ Р±СЂР°СѓР·РµСЂРѕРј СЋР·РµСЂР°РіРµРЅС‚
            OperaB = /Opera[ \/]+\w+\.\w+/i,     //
            OperaV = /Version[ \/]+\w+\.\w+/i,   //
            FirefoxB = /Firefox\/\w+\.\w+/i,     // С€Р°Р±Р»РѕРЅС‹ РґР»СЏ СЂР°СЃРїР°СЂСЃРёРІР°РЅРёСЏ СЋР·РµСЂР°РіРµРЅС‚Р°
            ChromeB = /Chrome\/\w+\.\w+/i,       //
            SafariB = /Version\/\w+\.\w+/i,      //
            IEB = /MSIE *\d+\.\w+/i,             //
            SafariV = /Safari\/\w+\.\w+/i,       //
            browser = new Array(),               //РјР°СЃСЃРёРІ СЃ РґР°РЅРЅС‹РјРё Рѕ Р±СЂР°СѓР·РµСЂРµ
            browserSplit = /[ \/\.]/i,           //С€Р°Р±Р»РѕРЅ РґР»СЏ СЂР°Р·Р±РёРІРєРё РґР°РЅРЅС‹С… Рѕ Р±СЂР°СѓР·РµСЂРµ РёР· СЃС‚СЂРѕРєРё
            OperaV = UA.match(OperaV),
            Firefox = UA.match(FirefoxB),
            Chrome = UA.match(ChromeB),
            Safari = UA.match(SafariB),
            SafariV = UA.match(SafariV),
            IE = UA.match(IEB),
            Opera = UA.match(OperaB);

        //----- Opera ----
        if ((!Opera=="")&(!OperaV=="")) browser[0]=OperaV[0].replace(/Version/, "Opera");
        else
        if (!Opera=="")	browser[0]=Opera[0];
        else
        //----- IE -----
        if (!IE=="") browser[0] = IE[0];
        else
        //----- Firefox ----
        if (!Firefox=="") browser[0]=Firefox[0];
        else
        //----- Chrom ----
        if (!Chrome=="") browser[0] = Chrome[0];
        else
        //----- Safari ----
        if ((!Safari=="")&&(!SafariV=="")) browser[0] = Safari[0].replace("Version", "Safari");
//------------ Р Р°Р·Р±РёРІРєР° РІРµСЂСЃРёРё -----------
        var
            outputData;                                      // РІРѕР·РІСЂР°С‰Р°РµРјС‹Р№ С„СѓРЅРєС†РёРµР№ РјР°СЃСЃРёРІ Р·РЅР°С‡РµРЅРёР№
        // [0] - РёРјСЏ Р±СЂР°СѓР·РµСЂР°, [1] - С†РµР»Р°СЏ С‡Р°СЃС‚СЊ РІРµСЂСЃРёРё
        // [2] - РґСЂРѕР±РЅР°СЏ С‡Р°СЃС‚СЊ РІРµСЂСЃРёРё
        if (browser[0] != null) outputData = browser[0].split(browserSplit);
        if ((chrAfterPoint==null)&&(outputData != null)) {
            chrAfterPoint=outputData[2].length;
            outputData[2] = outputData[2].substring(0, chrAfterPoint); // Р±РµСЂРµРј РЅСѓР¶РЅРѕРµ РєРѕ-РІРѕ Р·РЅР°РєРѕРІ
            return(outputData);
        } else return(false);
    };

    this.getBodyScrollTop = function() {
        var st = self.pageYOffset ? self.pageYOffset :
            document.documentElement ? document.documentElement.scrollTop :
                document.body ? document.body.scrollTop : 0;
        //alert(st);
        return parseInt(st);
    };

    this.getScreenHeight = function() {
        var screenH = 480;
        if (parseInt(navigator.appVersion)>3) {
            screenH = screen.height;
        } else if (navigator.appName == "Netscape" && parseInt(navigator.appVersion)==3 && navigator.javaEnabled() ) {
            var jToolkit = java.awt.Toolkit.getDefaultToolkit();
            var jScreenSize = jToolkit.getScreenSize();
            screenH = jScreenSize.height;
        }
        return screenH;
    };

    this.expand = function(c, el_id, collapse_html, expand_html) {
        var $c = $(c);
        var $el = $('#'+el_id);
        var collapse_html = collapse_html || '';
        var expand_html = expand_html || '';

        if ($el.length) {
            if ($c.hasClass('expand')) {
                $c.removeClass('expand').addClass('collapse').html(collapse_html);
                $el.show();
            } else if ($c.hasClass('collapse')) {
                $c.removeClass('collapse').addClass('expand').html(expand_html);
                $el.hide();
            }
        }
    };


    this.showStatus = function(type, message) {
        var type = type || 'common.loading';
        var message = message || '';

        this.hideStatus('all');

        $('.status.'+type).each(function (i, el) {
            if (message != '') { $(el).find('.message .body').html(message); }
            $(el).addClass('active');
        });
    };

    this.hideStatus = function(type) {
        var type = type || 'loading';

        $('.status'+(type != 'all' ? '.'+type : '')).each(function (i, el) {
            $(el).removeClass('active');
        });
    };

    this.go = function( obj ){
        if(obj) {
            if (obj.url) {
                window.location.href = obj.url;
            } else {
                window.location.href = obj;
            }
        }else{
            window.location.reload();
        }
    };
    this.showAjaxErrorStatus = function(textStatus) {
        switch (textStatus) {
            case 'timeout': {
                global.showStatus('timeout');
                break;
            }

            case 'abort': {
                break; // РќРёС‡РµРіРѕ РЅРµ РґРµР»Р°РµРј
            }
            // error, parseerror,
            default: {
                global.showStatus('server.error');
            }
        }
    };
};;/*! matchMedia() polyfill - Test a CSS media type/query in JS. Authors & copyright (c) 2012: Scott Jehl, Paul Irish, Nicholas Zakas. Dual MIT/BSD license */
/*! NOTE: If you're already including a window.matchMedia polyfill via Modernizr or otherwise, you don't need this part */
window.matchMedia=window.matchMedia||(function(e,f){var c,a=e.documentElement,b=a.firstElementChild||a.firstChild,d=e.createElement("body"),g=e.createElement("div");g.id="mq-test-1";g.style.cssText="position:absolute;top:-100em";d.style.background="none";d.appendChild(g);return function(h){g.innerHTML='&shy;<style media="'+h+'"> #mq-test-1 { width: 42px; }</style>';a.insertBefore(d,b);c=g.offsetWidth==42;a.removeChild(d);return{matches:c,media:h}}})(document);

/*! Respond.js v1.1.0: min/max-width media query polyfill. (c) Scott Jehl. MIT/GPLv2 Lic. j.mp/respondjs */
(function(e){e.respond={};respond.update=function(){};respond.mediaQueriesSupported=e.matchMedia&&e.matchMedia("only all").matches;if(respond.mediaQueriesSupported){return}var w=e.document,s=w.documentElement,i=[],k=[],q=[],o={},h=100,f=w.getElementsByTagName("head")[0]||s,g=w.getElementsByTagName("base")[0],b=f.getElementsByTagName("link"),d=[],a=function(){var D=b,y=D.length,B=0,A,z,C,x;for(;B<y;B++){A=D[B],z=A.href,C=A.media,x=A.rel&&A.rel.toLowerCase()==="stylesheet";if(!!z&&x&&!o[z]){if(A.styleSheet&&A.styleSheet.rawCssText){m(A.styleSheet.rawCssText,z,C);o[z]=true}else{if((!/^([a-zA-Z:]*\/\/)/.test(z)&&!g)||z.replace(RegExp.$1,"").split("/")[0]===e.location.host){d.push({href:z,media:C})}}}}u()},u=function(){if(d.length){var x=d.shift();n(x.href,function(y){m(y,x.href,x.media);o[x.href]=true;u()})}},m=function(I,x,z){var G=I.match(/@media[^\{]+\{([^\{\}]*\{[^\}\{]*\})+/gi),J=G&&G.length||0,x=x.substring(0,x.lastIndexOf("/")),y=function(K){return K.replace(/(url\()['"]?([^\/\)'"][^:\)'"]+)['"]?(\))/g,"$1"+x+"$2$3")},A=!J&&z,D=0,C,E,F,B,H;if(x.length){x+="/"}if(A){J=1}for(;D<J;D++){C=0;if(A){E=z;k.push(y(I))}else{E=G[D].match(/@media *([^\{]+)\{([\S\s]+?)$/)&&RegExp.$1;k.push(RegExp.$2&&y(RegExp.$2))}B=E.split(",");H=B.length;for(;C<H;C++){F=B[C];i.push({media:F.split("(")[0].match(/(only\s+)?([a-zA-Z]+)\s?/)&&RegExp.$2||"all",rules:k.length-1,hasquery:F.indexOf("(")>-1,minw:F.match(/\(min\-width:[\s]*([\s]*[0-9\.]+)(px|em)[\s]*\)/)&&parseFloat(RegExp.$1)+(RegExp.$2||""),maxw:F.match(/\(max\-width:[\s]*([\s]*[0-9\.]+)(px|em)[\s]*\)/)&&parseFloat(RegExp.$1)+(RegExp.$2||"")})}}j()},l,r,v=function(){var z,A=w.createElement("div"),x=w.body,y=false;A.style.cssText="position:absolute;font-size:1em;width:1em";if(!x){x=y=w.createElement("body");x.style.background="none"}x.appendChild(A);s.insertBefore(x,s.firstChild);z=A.offsetWidth;if(y){s.removeChild(x)}else{x.removeChild(A)}z=p=parseFloat(z);return z},p,j=function(I){var x="clientWidth",B=s[x],H=w.compatMode==="CSS1Compat"&&B||(w.body?w.body[x]:0)||B,D={},G=b[b.length-1],z=(new Date()).getTime();if(H==0||(I&&l&&z-l<h)){clearTimeout(r);r=setTimeout(j,h);return}else{l=z}for(var E in i){var K=i[E],C=K.minw,J=K.maxw,A=C===null,L=J===null,y="em";if(!!C){C=parseFloat(C)*(C.indexOf(y)>-1?(p||v()):1)}if(!!J){J=parseFloat(J)*(J.indexOf(y)>-1?(p||v()):1)}if(!K.hasquery||(!A||!L)&&(A||H>=C)&&(L||H<=J)){if(!D[K.media]){D[K.media]=[]}D[K.media].push(k[K.rules])}}for(var E in q){if(q[E]&&q[E].parentNode===f){f.removeChild(q[E])}}for(var E in D){var M=w.createElement("style"),F=D[E].join("\n");M.type="text/css";M.media=E;f.insertBefore(M,G.nextSibling);if(M.styleSheet){M.styleSheet.cssText=F}else{M.appendChild(w.createTextNode(F))}q.push(M)}},n=function(x,z){var y=c();if(!y){return}y.open("GET",x,true);y.onreadystatechange=function(){if(y.readyState!=4||y.status!=200&&y.status!=304){return}z(y.responseText)};if(y.readyState==4){return}y.send(null)},c=(function(){var x=false;try{x=new XMLHttpRequest()}catch(y){x=new ActiveXObject("Microsoft.XMLHTTP")}return function(){return x}})();a();respond.update=a;function t(){j(true)}if(e.addEventListener){e.addEventListener("resize",t,false);e.addEventListener("load",t,false)}else{if(e.attachEvent){e.attachEvent("onresize",t);e.attachEvent("onload",t)}}})(this);;var customer = new function() {
    this.request = null;
    this.coupon_request = null;
    this.coupon_timeout = null;

    this.ajax_params = {
        type: 'POST',
        dataType: 'json',
        timeout: 20000,
        error: function(jqXHR, textStatus, errorThrown) {
            //global.showAjaxErrorStatus(textStatus);
            customer.request = null;
        }
    };

    this.init = function() {
        $.ajax($.extend(this.ajax_params, {
            url: '/ajax/customer/customer/init/'+(location.search.length ? location.search+'&hash=' : '?hash=')+Math.random()*100000,
            success: function(data) {
                if (data.status == 1) {
                    if (data.coupon != '') {
                        if ($('.coupon-container').length) {
                            $('.coupon-container').html(data.coupon).show();
                        }
                    }
                } else{

                }
            }
        }));
    };

    this.coupon_set = function() {
        if (this.request != null) { return false; }

        global.showStatus('loading');

        this.request = $.ajax($.extend(this.ajax_params, {
            type: 'POST',
            url: '/ajax/customer/customer/coupon_set/',
            data: $('#coupon_form').serialize(),
            success: function(data) {
                customer.request = null;
                global.hideStatus('loading');
                if (data.status == 1) {
                    $('.coupon-container').html('').hide();
                    global.showStatus('coupon.success', '');
                } else if (data.status == 2) {
                    $('.coupon-container').html('').hide();
                    global.showStatus('coupon.error', data.message);
                } else if (data.status == 3) {
                    global.showStatus('coupon.error', data.message);
                } else {
                    $('.coupon-container').html('').hide();
                    global.showStatus('coupon.error', data.message);
                }

            }
        }));

        return false;
    };

    this.coupon_cancel = function() {
        $.ajax($.extend(this.ajax_params, {
            url: '/ajax/customer/customer/coupon_cancel/',
            data: $('#coupon_form').serialize(),
            success: function(data) {
                if (data.status == 1) {
                    $('.coupon-container').html('').hide();
                } else {

                }
            }
        }));
    };

    this.coupon_check = function(delay) {
        var delay = delay || 0;

        if (delay == 0) {
            if (this.coupon_request != null) { this.coupon_request.abort(); }

            this.coupon_request = $.ajax($.extend(this.ajax_params, {
                url: '/ajax/customer/customer/coupon_check/',
                data: $('#order_form').serialize(),
                success: function(data) {
                    if (data.status == 1) {
                        $('.coupon-check-result').html(data.html);
                    } else {

                    }
                }
            }));
        } else {
            if (this.coupon_timeout != null) { clearTimeout(this.coupon_timeout); }
            this.coupon_timeout = setTimeout('customer.coupon_check(0)', delay);
        }
    };

    this.login = function(obj) {
        if (this.request != null) { return false; }

        global.showStatus('loading');

        this.request = $.ajax($.extend(this.ajax_params, {
            url: '/ajax/customer/customer/login',
            data: $(obj).serialize(),
            success: function(data) {
                customer.request = null;
                if (data.status == 1) {
                    global.go();
                } else{
                    global.showStatus('customer.error', data.message);
                }

            }
        }));

        return false;
    };

    this.password = function(obj) {
        if (this.request != null) { return false; }

        global.showStatus('loading');

        this.request = $.ajax($.extend(this.ajax_params, {
            url: '/ajax/customer/customer/password',
            data: $(obj).serialize(),
            success: function(data) {
                customer.request = null;
                if (data.status == 1) {
                    global.showStatus('password.success', data.message);
                    $('#password_form').hide();
                    $('#login_form').show();
                    $('#login_form [name="login"]').val($('#password_form [name="login"]').val());
                } else{
                    global.showStatus('password.error', data.message);
                }
            }
        }));

        return false;
    };

    this.logout = function(obj) {
        if (this.request != null) { return false; }

        global.showStatus('loading');

        this.request = $.ajax($.extend(this.ajax_params, {
            url: '/ajax/customer/customer/logout',
            success: function(data) {
                customer.request = null;
                if (data.status == 1) {
                    global.go();
                } else{
                    //global.showStatus('order.error', data.message);
                    global.hideStatus();
                }

            }
        }));

        return false;
    };

    this.save = function(form) {
        if (this.request != null) { return false; }

        $('.missing-value', form).removeClass('missing-value');

        $('.required', form).each(function (i, el) {
            if ($(el).val() == '') {
                $(el).addClass('missing-value');
            }
        });

        if ($('.missing-value', form).length) {
            return;
        }

        global.showStatus('loading');

        this.request = $.ajax($.extend(this.ajax_params, {
            url: '/ajax/customer/customer/save',
            data: $(form).serialize(),
            success: function(data) {
                customer.request = null;
                if (data.status == 1) {
                    customer.profile(true);
                } else if (data.status == 2) {
                    global.go('/customer/');
                } else {
                    //global.showStatus('password.error', data.message);
                    global.hideStatus();
                }
            }
        }));

        return false;
    };

    this.profile = function(toggle_view) {
        var toggle_view = toggle_view || false;

        $.ajax($.extend(this.ajax_params, {
            url: '/ajax/customer/customer/profile/'+(location.search.length ? location.search+'&hash=' : '?hash=')+Math.random()*100000,
            success: function(data) {
                if (data.status == 1) {
                    $('.content.profile .profile-view').html(data.view);
                    $('.content.profile .profile-edit').html(data.edit);

                    if (toggle_view) {
                        $('.profile-edit').hide();
                        $('.profile-view').show();
                    }
                    global.hideStatus();
                } else {

                }
            }
        }));
    };
};
;var order = new function() {

    this.ajax_timeout = null;
    this.form = null;
    this.currentRequest = null;

    this.send = function(obj, reset) {
        if (this.currentRequest != null) { return false; }

        var reset = reset || false;
        this.form = obj;

        if (reset) {
            this.resend_count = 0;
        }
        global.showStatus('loading');

        this.currentRequest = $.ajax({
            type: 'POST',
            url: obj.action,
            data: $(obj).serialize(),
            success: function(data) {
                order.currentRequest = null;
                if(data.status == 1)
                {
                    if(data.tracking_id)
                    {
                        window.location.href = '/customer/orders/'+data.tracking_id;
                    }
                    else
                    {
                        global.showStatus('order.success', data.message);
                    }
                }
                else if(data.status == 2 && order.resend_count < 2)
                {

                    order.resend_count++;
                    order.send(order.form, false);
                }
                else
                {
                    global.showStatus('order.error', data.message);
                }

            },
            dataType: 'json',
            timeout: 20000,
            error: function(jqXHR, textStatus, errorThrown) {
                global.showAjaxErrorStatus(textStatus);
                order.currentRequest = null;
            }
        });

        return false;
    }

    this.check = function() {
        $.get('/ajax/customer/orders/check', function(data){
            if (data.status) {
                window.location.href = '/customer/orders/' + data.id;
            }
        });
    }

    this.failed = function () {
        clearInterval(this.checkIntervalId);
        global.showStatus('order.error');
        $.get('/ajax/customer/orders/failed');
    }

    this.startCheck = function () {
        global.showStatus('order.loading');
        this.checkIntervalId = setInterval("order.check()", 1000);
        setTimeout('order.failed()', 3000);
    }
};teaser = function(teasers) {
    this.teasers = teasers || [];
    this.options = {
        preview: false, // РџРѕРєР°Р·С‹РІР°С‚СЊ РїСЂРµРІСЊСЋС€РєРё
        width: 0,
        height: 0
    };

    this.current = null;
    this.sliding = null;

    var self = this;

    var $c = $('.teaser')
        .append('<div class="nav-dots"><div class="nav-dots-content"></div></div><div class="nav-arrow left"></div><div class="nav-arrow right"></div>');

    for (i in this.teasers) {
        $c.append('<div class="container"><a href="'+this.teasers[i].url+'" title="'+(this.teasers[i].text || '')+'"><div class="header">'+(this.teasers[i].header || '')+'</div></a></div>');
        $('.nav-dots-content', $c).append('<div class="nav-dot out '+(this.options.preview ? 'preview' : '')+'" number="'+i+'"></div>');

    };

    this.options.width = this.options.width > 0 ? this.options.width : $c.width();
    this.options.height = this.options.height > 0 ? this.options.height : $c.height();

    $c = $('.teaser').css({height: this.options.height, width: this.options.width});

    $('.container a', $c).each(function (i, el) {
        $(el).css({
            height: self.options.height,
            width: self.options.width,
            'background-image': 'url('+self.teasers[i].src+')'
        })
            .bind('mouseenter', function() { self.stopSliding(); })
            .bind('mouseleave', function() { self.startSliding(10); });
    });

    $('.nav-dot').each(function(i, el) {
        if (self.options.preview) {
            $(el).css({'background-image': 'url('+self.teasers[i].src+')'});
        }
        $(el)
            .bind('mouseenter mouseleave', function() { $(this).toggleClass('over'); })
            .click(function() {
                $('.container').each(function(k, el1) {
                    if (i == k) {
                        $(el1).fadeIn();
                        self.current = i;
                    } else {
                        $(el1).fadeOut();
                    }
                });
                $('.nav-dot').each(function(k, el2) {
                    $(el2).removeClass('selected');
                });
                $(this).addClass('selected');
            });
    });

    $('.nav-arrow.left').click(function() {
        if (self.current == 0) {
            $('.nav-dot').last().click();
        } else {
            $('.nav-dot').eq(self.current-1).click();
        }
    });

    $('.nav-arrow.right').click(function() {
        if (self.current == self.teasers.length-1) {
            $('.nav-dot').first().click();
        } else {
            $('.nav-dot').eq(self.current+1).click();
        }
    });

    this.stopSliding = function() {
        if (self.sliding) { clearTimeout(self.sliding); clearInterval(self.sliding); self.sliding = null; }
    };

    this.startSliding = function(delay) {
        var delay = delay || 0;
        self.stopSliding();

        if (delay > 0) {
            self.sliding = setTimeout(self.startSliding, 1000*delay);
        } else {
            self.sliding = setInterval(function() { $('.nav-arrow.right').click(); }, 5000);
        }
    };

    $('.nav-dot').first().click();
    this.startSliding();
};;;var catalogue = new function(){
    this.search_string 	= '';
    this.result_id		= 'CATALOGUE_SEARCH_RESULT';
    this.content_id		= 'CONTENT';
    this.hints_id 		= 'CATALOGUE_SEARCH_HINT';
    this.main_container = 'CATALOGUE_SEARCH_RESULT';
    this.button_id 		= 'CATALOGUE_SEARCH_BUTTON';
    this.start_search	= false;
    this.search_timeout = null;
    this.search_hints_timeout = null;
    this.search_delay	= 600;
    this.search_jqxhr	= null;
    this.last_hint 		= -1;
    this.hints_hash		= '';
    this.hints_jqxhr	= null;
    this.sizes			= [];
    this.categories		= [];
    this.uri			= location.pathname + (location.search != '' ? '?'+location.search : '');
    this.current_category = 0;
    this.quantity		= 'single'; // РљРѕР»РёС‡РµСЃС‚РІРѕ Р·Р°РєР°Р·С‹РІР°РµРјС‹С… РІРµС‰РµР№ РѕРґРЅРѕРіРѕ СЂР°Р·РјРµСЂР° (single|multiple) - РґР»СЏ РїРµСЂРµРґР°С‡Рё РІ РїРѕРёСЃРєРѕРІС‹Рµ Р·Р°РїСЂРѕСЃС‹
    this.i = 0;

    this.location 		= location.href;
    this.scroll			= false;

    this.browser		= global.browserDetectNav();

    this.search_hints_timeout		= '';

    // search by size
    this.sbs_form_id	= 'SEARCH_SIZES_FORM';
    this.sbd_form_id	= 'SEARCH_DIMENSIONS_FORM';
    this.sbs_sizes		= '';
    this.SBS_LIMIT		= 20;
    this.SBS_START		= 0;
    this.sbs_result_id	= 'CATALOGUE_SEARCH_BY_SIZE_RESULT';
    this.SBS_TOTAL		= 0;
    this.sbs_scroll		= false;
    this.sbs_start_search	= true;

    this.big_photo_overlay_id = 'bphoto_overlay';
    this.big_photo_div_id = 'bphoto_div';

    this.ss_started 	= false;
    this.ss_timer 		= 0;

    this.hl_interval	= 0;
    this.hl_time		= 4; // РёРЅС‚РµСЂРІР°Р» СЃРјРµРЅС‹ Р±РµРіСѓС‰РµР№ СЃС‚СЂРѕРєРё
    this.hl_timeout 	= 0;

    this.restore_search = function(params) {
        if (params.search_string.length > 1) {
            this.search_string = params.search_string;
            this.sizes = params.sizes;
            $('#'+this.hints_id).hide();
            this.search_hints_request(this.search_string);
        }
    };

    this.search = function(e){
        var key = 0;
        var obj = $('#CATALOGUE_SEARCH')[0];
        if (e != undefined) { key = (window.event) ? event.keyCode : e.keyCode; }

        if (key != 0) {
            if (key == 27) { // esc
                this.clear_search();
                return;
            } else if (key == 13) { // enter
                this.start_search = true;
            } else if (key == 37 || key == 39) { // left or right key
                this.start_search = false;
            } else if (key == 38 || key == 40) { // up or down key
                this.move_on_hints(key);
                this.start_search = false;
            } else if (key == 224 || key == 18 || key == 17 || key == 16) {
                // do nothing
            } else {
                this.start_search = true;
                //document.getElementById(this.main_container).scrollTop = 0;
            }
        }
        //var wrongSymbols = /[\\\/\.\,\'\"\(\)\!\@\#\$\%\^\&\*\{\}\[\]\:\;\`\~\|\?]/ig;
        //if (wrongSymbols.test(obj.value)) { obj.value = obj.value.replace(wrongSymbols, ''); }

        if (obj.value.length > 1) {
            var new_sizes = [];
            var i = 0;
            $('#'+this.hints_id+' input:checked').each(function(i, el) {
                new_sizes[i++] = el.value;
            });

            new_sizes = new_sizes.join(',');

            if (this.start_search == true && ($.trim(obj.value) != this.search_string || new_sizes != this.sizes)) {
                this.search_string = obj.value;
                this.sizes = new_sizes;
                this.categories = '';

                if (this.search_hints_timeout) { clearTimeout(this.search_hints_timeout); }
                this.search_hints_timeout = setTimeout("catalogue.search_hints_request()", 450);

                if (this.search_timeout) { clearTimeout(this.search_timeout); }
                this.search_timeout = setTimeout("catalogue.search_request()", (key == 13 ? 50 : this.search_delay));

                if ((this.browser[0]!='MSIE') || (this.browser[0]=='MSIE' && this.browser[1]>8)) {
                    var data = {};
                    if (location.search) {
                        var pair = (location.search.substr(1)).split('&');
                        for(var i = 0; i < pair.length; i ++) {
                            var param = pair[i].split('=');
                            if (param[0] != '') {
                                data[param[0]] = param[1];
                            }
                        }
                    }
                    data['q'] = encodeURIComponent(this.search_string);
                    data['ss'] = encodeURIComponent(this.sizes);
                    data['sp'] = '';

                    var pair = [];
                    var i = 0;
                    for (k in data) {
                        pair[i++] = k+'='+(data[k] || '');
                    }
                    new_location = location.pathname+'?'+pair.join('&');
                    if (jQuery.isFunction(history.replaceState)) {
                        history.replaceState(null, '', new_location);
                    }
                }
            }
        } else {
            this.clear_search();
        }
    };

    this.reset_search_sizes = function() {
        $('#'+this.hints_id+' input:checked').each(function(i, el) {
            $(el).removeAttr('checked');
        });
        this.start_search = true;
        this.search();
    };

    this.search_request = function(value) {
        var value = value || this.search_string;
        $('#'+this.result_id).addClass('hidden_div');
        $('#'+this.content_id).addClass('hidden_div');
        //$('.data_loading').each(function (i, el) { $(el).addClass('active'); });
        global.showStatus('common.loading');

        var data = {
            'uri' : encodeURIComponent(this.uri),
            'target': this.result_id,
            'q' : encodeURIComponent(value),
            'sizes' : this.sizes,
            'params' : {'quantity' : this.quantity, 'ajax' : 1 }
        };

        if (this.search_jqxhr) { this.search_jqxhr.abort(); }

        this.search_jqxhr = $.ajax({
            type: 'GET',
            url: '/ajax/catalogue/search',
            data: data,
            success: function(data) { return catalogue.load_search_result(data); },
            dataType: 'json',
            timeout: 120000,
            error: function (data, textStatus, jqXHR) {
                global.showAjaxErrorStatus(textStatus);
                $('.hidden_div').removeClass('hidden_div');
                catalogue.search_jqxhr = null;
            }
        });
    };

    this.search_hints_request = function(value) {
        var value = value || this.search_string;

        if (this.hints_jqxhr) { this.hints_jqxhr.abort(); }

        this.hints_jqxhr = $.get('/ajax/catalogue/search_hints',
            {
                'url' : encodeURIComponent(location.pathname),
                'target': this.hints_id,
                'q' : encodeURIComponent(value),
                'sizes' : this.sizes,
                'params' : {'quantity' : this.quantity}
            },
            function(data) { return catalogue.load_search_hints(data); },
            'json'
        );
        $('#hints_loading').addClass('active');
    };

    this.search_by_dimensions = function(page, sex){
        var sizes = $('input[value!=""]', $('#'+this.sbd_form_id+sex)).serialize();

        if (sizes.length) {
            $('#'+this.sbs_result_id).addClass('hidden_div');
            $('#'+this.content_id).addClass('hidden_div');
            global.showStatus('common.loading');
        }


        var data = {
            'url' : encodeURIComponent(location.pathname),
            'target': this.sbs_result_id,
            'params' : {'quantity' : this.quantity},
            'r' : Math.random()
        };

        if (this.search_jqxhr) { this.search_jqxhr.abort(); }

        this.search_jqxhr = $.ajax({
            type: 'GET',
            url: '/ajax/catalogue/search_by_dimensions_items/?p='+(page || 1)+'&'+(sizes || 'dimensions='),
            data: data,
            success: function(data) { return catalogue.load_search_by_size_result(data); },
            dataType: 'json',
            timeout: 120000,
            error: function (data, textStatus, jqXHR) {
                global.showAjaxErrorStatus(textStatus);
                $('.hidden_div').removeClass('hidden_div');
            }
        });
    };

    this.search_by_size = function(page){
        //if ($('#'+this.sbs_form_id+' input:checked').length == 0) { return; }
        var sizes = $('#'+this.sbs_form_id+' input:checked').serialize();

        if (sizes.length) {
            $('#'+this.sbs_result_id).addClass('hidden_div');
            $('#'+this.content_id).addClass('hidden_div');
            global.showStatus('common.loading');
        }


        var data = {
            'url' : encodeURIComponent(location.pathname),
            'target': this.sbs_result_id,
            'params' : {'quantity' : this.quantity}
        };

        if (this.search_jqxhr) { this.search_jqxhr.abort(); }

        this.search_jqxhr = $.ajax({
            type: 'GET',
            url: '/ajax/catalogue/search_by_size_items/?p='+(page || 1)+'&'+(sizes || 'sizes='),
            data: data,
            success: function(data) { return catalogue.load_search_by_size_result(data); },
            dataType: 'json',
            timeout: 120000,
            error: function (data, textStatus, jqXHR) {
                global.showAjaxErrorStatus(textStatus);
                $('.hidden_div').removeClass('hidden_div');
            }
        });
    };

    this.load_search_result = function(d) {
        if (!d.success || d.q != this.search_string || d.sizes != this.sizes) {
            return false;
        }

        //this.start_search = false;

        if (d.total == 1 && d.href != '') {
            document.location.href = d.href;
            return;
        }

        $('#'+this.main_container)[0].scrollTop = 0;
        $('#'+this.content_id).hide()
            .removeClass('hidden_div');

        $('#'+d.target).html(d.items)
            .removeClass('hidden_div')
            .show();

        this.search_jqxhr = null;
        global.hideStatus('loading');
    };

    this.load_search_hints = function(d) {
        if (!d.success
            || d.q != this.search_string
            || d.sizes != this.sizes
            || d.hash == this.hints_hash) {
            return false;
        }

        this.last_hint = -1;
        this.hints_hash = d.hash;
        var $hints = $('#'+d.target);

        $hints.html(d.hints);
        if (d.hints == '') { $hints.hide(); } else if (this.start_search) { $hints.show(); }
        this.hints_jqxhr = null;
        $('#hints_loading').removeClass('active');
    };

    this.load_search_by_size_result = function(d){
        if (!d.success) {
            return false;
        }

        if (d.clear == undefined) { $('#'+this.main_container)[0].scrollTop = parseInt($('#'+d.target).offset().top); }
        //$('body').scrollTop(parseInt($('#'+d.target).offset().top));
        $('#'+this.content_id).removeClass('hidden_div');

        $('#'+d.target).html(d.items)
            .removeClass('hidden_div');

        global.hideStatus('loading');
    };

    this.clear_search = function() {
        this.search_string = '';
        this.hints_hash = '';
        this.sizes = '';
        if (this.search_timeout) { clearTimeout(this.search_timeout); }
        if (this.search_hints_timeout) { clearTimeout(this.search_hints_timeout); }
        if (this.hints_jqxhr) { this.hints_jqxhr.abort(); this.hints_jqxhr = null;}
        if (this.search_jqxhr) { this.search_jqxhr.abort(); this.search_jqxhr = null; }

        $('#hints_loading').removeClass('active');
        //$('.data_loading').each(function (i, el) { $(el).removeClass('active'); });
        global.hideStatus('all');
        $('#'+this.result_id).hide().removeClass('hidden_div');
        $('#'+this.content_id).removeClass('hidden_div').show();
        $('#'+this.hints_id).hide().html('');
        if ((this.browser[0]!='MSIE') || (this.browser[0]=='MSIE' && this.browser[1]>8)) {
            if (jQuery.isFunction(history.replaceState)) {
                if (location.search) {
                    var data = [];
                    var pair = (location.search.substr(1)).split('&');
                    for(var i = 0; i < pair.length; i ++) {
                        var param = pair[i].split('=');
                        if (param[0] != 'q' && param[0] != 'ss' && param[0] != 'sp' && param[0] != 'ssort' && param[0] != 'sup' && param[0] != 'sdown') {
                            data[param[0]] = param[1];
                        }
                    }
                }

                var pair = [];
                for (k in data) {
                    pair.push(k+'='+(data[k] || ''));
                }
                new_location = location.pathname+ (pair.length ? '?'+pair.join('&') : '');
                if (jQuery.isFunction(history.replaceState)) {
                    history.replaceState(null, '', new_location);
                }
            }
        }

        if ($('#form_sizes_filter input:checked').length > 0) {
            $('#clear_sizes_filter_button').click();
        }
    };

    this.set_hint = function(obj){
        $('#CATALOGUE_SEARCH').val($(obj).attr('search_value'));
        this.start_search = true;
        this.search();
    };

    this.move_on_hints = function(key){
        if (document.getElementById('HINTS')) {
            var hints = [];
            hints = document.getElementById('HINTS').getElementsByTagName('div');

            if(this.last_hint >= 0) hints[this.last_hint].className = '';

            if(key == 38){
                if(this.last_hint > 0)
                    this.last_hint--;
                else
                    this.last_hint = 0;
            } else if (key == 40) {
                if(this.last_hint < hints.length-1)
                    this.last_hint++;
                else
                    this.last_hint = hints.length-1;
            }
            if(hints[this.last_hint]){
                hints[this.last_hint].className = 'selected';
                $('#CATALOGUE_SEARCH').val($(hints[this.last_hint]).attr('search_value'));
            }

            if (key == 13) {
                this.start_search = true;
                this.search();
            }
        }
    };

    this.init_search_button = function() {
        // РѕРїСЂРµРґРµР»РёРј СЃС‚Р°СЂС‹Рµ Р±СЂР°СѓР·РµСЂС‹ Рё РїРѕРєР°Р¶РµРј РєРЅРѕРїРєСѓ РґР»СЏ РїРѕРёСЃРєР°
        if( ( this.browser[0]=='MSIE' && this.browser[1]<=6 ) || ( this.browser[0]=='Firefox' && this.browser[1]<=4 ) || ( this.browser[0]=='Safari' && this.browser[1]<=2 ) ) {
            document.getElementById(this.button_id).style.display = 'block';
        }
    };

    this.clearSearchForm = function () {
        $('#'+this.sbs_form_id+' input:checked').each(function(i, el) { el.checked = false; });
        $('[id^="'+this.sbd_form_id+'"] input').each(function(i, el) { el.value = ''; });
        $('#'+this.sbs_result_id).html('');
        this.search_by_size(1);
    };

    this.search_hide_hint = function() {
        this.search_hints_timeout = window.setTimeout("document.getElementById('"+this.hints_id+"').style.display = 'none';", 500);
    };

    this.search_show_hint = function() {
        if ($('#'+this.hints_id).html().length) {
            $('#'+this.hints_id).show();
        }
    };

    this.search_blur = function() {
        this.search_hide_hint();
    };

    this.search_focus = function() {
        this.search_show_hint();
    };

    this.search_hint_over = function() {
        if(this.search_hints_timeout>0)
            clearTimeout(this.search_hints_timeout);
    };

    this.search_hint_out = function() {
        if(document.activeElement.id!='CATALOGUE_SEARCH')
            this.search_hide_hint();
    };

    this.search_hint_move = function() {
        if(this.search_hints_timeout>0)
            clearTimeout(this.search_hints_timeout);
    };

    this.addFavorites = function(cat_id, img_obj) {
        if (cat_id) {
            $.get('/json/favorites/?item='+cat_id, '',
                function(data) {
                        if (data.status == 0) {
                            $('#fav_icon_'+data.id).attr('class', 'like_off');
                            $('#fav_text_'+data.id).html('Больше не нравится');
                        } else if (data.status == 1) {
                            $('#fav_icon_'+data.id).attr('class', 'like_on');
                            $('#fav_text_'+data.id).html('Понравилось');
                        }
                        $('#FavoritesCount').html(data.count);
                },
                'json'
            );
        }

    };

    this.rotate = function (obj_id) {
        // РћРїСЂРµРґРµР»СЏРµРј С‚РµРєСѓС‰РёР№ РїРѕРєР°Р·Р°РЅРЅС‹Р№ СЌР»РµРјРµРЅС‚
        var first = $('div#'+obj_id+' ul li:first');
        var current = ($('div#'+obj_id+' ul li.show') ? $('div#'+obj_id+' ul li.show') : first);

        if (first.length && current.find('img').length) {
            // РћРїСЂРµРґРµР»СЏРµРј СЃР»РµРґСѓСЋС‰РёР№ СЌР»РµРјРµРЅС‚ Рє РїРѕРєР°Р·Сѓ
            var next = (current.find('img')[0].complete		// Р•СЃР»Рё РєР°СЂС‚РёРЅРєР° РІ С‚РµРєСѓС‰РµРј СЌР»РµРјРµРЅС‚Рµ РїРѕРЅРѕСЃС‚СЊСЋ Р·Р°РіСЂСѓР¶РµРЅ
                && current.next().find('img').length)		// Рё РµСЃР»Рё СЃР»РµРґСѓСЋС‰РёР№ СЌР»РµРјРµРЅС‚ РЅРµ РїСѓСЃС‚РѕР№,
                ? current.next().find('img')[0].complete	// С‚Рѕ РµСЃР»Рё РєР°СЂС‚РёРЅРєР° РІ СЃР»РµРґСѓСЋС‰РµРј С‚РѕР¶Рµ РїРѕР»РЅРѕСЃС‚СЊСЋ Р·Р°РіСЂСѓР¶РµРЅР°,
                ? current.next()							// С‚Рѕ Р±РµСЂРµРј РµРіРѕ РІ РєР°С‡РµСЃС‚РІРµ СЃР»РµРґСѓСЋС‰РµРіРѕ СЌР»РµРјРµРЅС‚Р° Рє РїРѕРєР°Р·Сѓ
                : first										// РёРЅР°С‡Рµ Р±РµСЂРµРј РїРµСЂРІС‹Р№ СЌР»РµРјРµРЅС‚, РµСЃР»Рё РґРѕС€Р»Рё РґРѕ РєРѕРЅС†Р°
                : null;										// РёРЅР°С‡Рµ РЅРёС‡РµРіРѕ РЅРµ Р±СѓРґРµРј РїРѕРєР°Р·С‹РІР°С‚СЊ

            if (null != next) {
                current.removeClass('show');
                next.addClass('show');
            }
        }
    };

    this.delayStartSlideShow = function(obj_id) {
        catalogue.rotate(obj_id);
        this.ss_started = true;
    };

    this.startSlideShow = function(obj_id) {
        if(this.ss_started==false) {
            this.ss_timer = setTimeout('catalogue.delayStartSlideShow("'+obj_id+'")', 500);
        }
    };

    this.stopSlideShow = function(obj_id) {
        if(this.ss_started==true) {
            var first = $('div#'+obj_id+' ul li:first');
            var current = ($('div#'+obj_id+' ul li.show') ? $('div#'+obj_id+' ul li.show') : first);

            first.addClass('show');
            current.removeClass('show');

            this.ss_started = false;
        }
        if(this.ss_timer>0)
            clearTimeout(this.ss_timer);
    };

    this.showBigPhoto = function(cat_id) {
        $.get('/ajax/catalogue/zoom/'+cat_id);
        var div = document.getElementById(this.big_photo_div_id);

        // Р—Р°РіСЂСѓР·РєР° РѕСЃС‚Р°Р»СЊРЅС‹С… Р±РѕР»СЊС€РёС… РєР°СЂС‚РёРЅРѕРє (РєСЂРѕРјРµ РїРµСЂРІРѕР№)
        var $imgs = $('[delayed_src]', div);
        if ($imgs.length) {
            $imgs.each(function (i, el) {
                $(el).attr('src', $(el).attr('delayed_src'));
                $(el).removeAttr('delayed_src');
            });
        }
        document.getElementById(this.big_photo_overlay_id).style.display='block';
        div.style.display='block';
        $('#gallery_big').width($('#img_big_main').width());
        $('#gallery_big').height($('#img_big_main').height());

        var $bs = $('#big_slides .slide');
        $('#big_slides').width($bs.length * $($bs[0]).width());

        //global.positionate(div, 50);
    };

    this.hideBigPhoto = function() {
        document.getElementById(this.big_photo_div_id).style.display='none';
        document.getElementById(this.big_photo_overlay_id).style.display='none';
    };

    this.loadHotLine = function() {
        if (!$('.hotline').length) { return; }

        $.get(
            '/ajax/catalogue/hotline',
            {'target' : 'hot_line'},
            function (data) { return catalogue.updateHotLine(data); },
            'json'
        );
    };

    this.updateHotLine = function(data){
        $('#'+data.target).html(data.xml);
        $('.hotline').show();
        this.startHotLine();
    };

    this.startHotLine = function(){
        this.hl_interval = window.setInterval('catalogue.moveHotLine()', this.hl_time*1000);
    };

    this.moveHotLine = function(){
        if($("#hot_line_inner").height()+$("#hot_line_inner").position().top<=20)
            $("#hot_line_inner").css({'top':'20px'});

        this.hotLineAnimate('-=20');

    };

    this.hotLinePrev = function(){
        clearInterval(this.hl_interval);
        clearTimeout(this.hl_timeout);
        this.hl_timeout = window.setTimeout('catalogue.startHotLine()', 2000);

        if($("#hot_line_inner").position().top>=0)
            $("#hot_line_inner").css({'top':-$("#hot_line_inner").height()+'px'});

        this.hotLineAnimate('+=20');

    };

    this.hotLineNext = function(){
        clearInterval(this.hl_interval);
        clearTimeout(this.hl_timeout);
        this.hl_timeout = window.setTimeout('catalogue.startHotLine()', 2000);

        if($("#hot_line_inner").height()+$("#hot_line_inner").position().top<=20)
            $("#hot_line_inner").css({'top':'20px'});

        this.hotLineAnimate('-=20');

    };

    this.hotLineAnimate = function(top) {
        $('#hot_line_inner').animate({
            top: top
        }, 1000);
    };

    this.page = function(p) {
        if ($('form#filters').length) {
            $('form#filters [name="params[page]"]').val(p);
            this.filter(p);
            return false;
        }
        return true;
    };

    this.reset_filters = function() {
        $('form#filters input:checked').each(function(i, el) { el.checked = false; });
        this.filter();
    };

    /*
     * Р—Р°РіРѕС‚РѕРІРєРё РґР»СЏ AJAX-Р·Р°РіСЂСѓР·РєРё РґР°РЅРЅС‹С… РёР· РєР°С‚Р°Р»РѕРіР°
     */
    this.filter = function(p) {
        var p = p || 0;
        global.showStatus('common.loading');

        if (this.search_jqxhr) { this.search_jqxhr.abort(); }

        if (p == 0) { $('form#filters input[name="params[page]"]').val(''); }

        this.search_jqxhr = $.ajax({
            type: 'POST',
            url: '/ajax/catalogue/filter',
            data: $('form#filters').serialize(),
            success: function(data) { return catalogue.load_filter_result(data); },
            dataType: 'json',
            timeout: 120000,
            error: function (data, textStatus, jqXHR) {
                global.showAjaxErrorStatus(textStatus);
                $('.hidden_div').removeClass('hidden_div');
                catalogue.search_jqxhr = null;
            }
        });
    };

    this.load_filter_result = function(d) {
        if (d.success) {
            $('.items-wrapper').html(d.items).show();
            this.uri = d.base_url;

            if ((this.browser[0]!='MSIE') || (this.browser[0]=='MSIE' && this.browser[1]>8)) {
                if ($.isFunction(history.replaceState)) {
                    history.replaceState(null, '', d.base_url);
                }
            }
        }
        this.search_jqxhr = null;
        global.hideStatus('loading');
    };
};;
var basket = new function() {

    this.ajax_timeout = null;
    this.hash = '';
    this.currentFormId = null;
    this.currentRequest = null;

    this.get_small_basket = function() {
        this.hash = Math.random();
        $.ajax({
            type: 'GET',
            url: '/basket/update/?',
            data: {'mode': 'small', 'hash': this.hash},
            success: function(data) {
                console.log(data);

                return basket.update(data);
            },
            dataType: 'json',
            timeout: 20000,
            error: function (data, textStatus, jqXHR) {
                global.showAjaxErrorStatus(textStatus);
            }
        });
        global.showStatus();

    };

    this.recount = function(item_id, item_size, count, mode, delay) {
        delay = (delay == undefined ? 600 : delay);
        var request = ''+item_id+item_size+count+mode;

        if (delay > 0 && this.currentRequest == request) { return; }
        this.currentRequest = request;
        if (this.ajax_timeout) { clearTimeout(this.ajax_timeout); }

        count = parseInt(count);

        if (!isNaN(count)) {
            if (delay > 0) {
                this.ajax_timeout = setTimeout("basket.recount('"+item_id+"', '"+item_size+"', "+count+", '"+mode+"', 0)", delay);
            } else {
                this.hash = Math.random();
                $.ajax({
                    type: 'GET',
                    url: '/basket/update/?mode='+mode+'&item['+item_id+'][]='+item_size+'_'+count,
                    data: { 'hash': this.hash },
                    success: function(data) { return basket.update(data); },
                    dataType: 'json',
                    timeout: 20000,
                    error: function (data, textStatus, jqXHR) {
                        global.showAjaxErrorStatus(textStatus);
                    }
                });
                //$('.data_loading').each(function (i, el) { $(el).addClass('active'); });
                global.showStatus('common.loading');
            }
        }
    };

    this.update = function(d) {
        if (d.success && this.hash == d.hash) {
            switch (d.mode) {
                case 'small'	: { $target = $('#SMALL_BASKET');  break; }
                case 'basket'	: { $target = $('#SHOPPING_CART'); break; }
            }
            if ($target.attr('id') == 'SMALL_BASKET') {
                $('#_SMALL_BASKET').html(d.basket);
                $target.show();
                global.positionate($target[0]);
                if (d.selected.length > 0) {
                    $('#'+d.selected).focus();
                }
            } else {


                $target.html(d.basket);
            }
            this.update_sizes_button(d);
            this.update_basket_count(d);

            $('#BASKET_INFO').html(d.basket_info);
            global.hideStatus('loading');
        }
    };

    this.update_sizes_button = function(d) {
        var $sizes_button = $('#sizes_button_'+d.id);
        if ($sizes_button.length) {
            if (d.total > 0) {
                $sizes_button.addClass('on').html(d.total);
            } else {
                $sizes_button.removeClass('on').html('&nbsp;');
            }
        }
    };

    this.update_basket_count = function(d) {
        var containerId = 'sizes_' + d.id;
        var c = $('#'+containerId)[0];

        if (!c) { c = $('#sizes_perfume')[0]; }


    };

    this.getItems = function(containerId) {
        var c = $('#'+containerId)[0]; 	// РЈСЃС‚Р°РЅР°РІР»РёРІР°РµРј РєРѕРЅС‚РµРєСЃС‚ РґР»СЏ РїРѕРёСЃРєР° РІСЃРµС… СЌР»РµРјРµРЅС‚РѕРІ
        var items = []; 					// РњР°СЃСЃРёРІ РїРѕР·РёС†РёР№ СЂР°Р·РЅС‹С… СЂР°Р·РјРµСЂРѕРІ
        this.items = null;

        var quantity	= $(c).attr('quantity');	// Р’РѕР·РјРѕР¶РЅРѕСЃС‚СЊ Р·Р°РєР°Р·Р° РѕРґРЅРѕР№/РЅРµСЃРєРѕР»СЊРєРёС… С€С‚СѓРє РѕРґРЅРѕРіРѕ СЂР°Р·РјРµСЂР° РґРѕР»Р¶РЅР° Р±С‹С‚СЊ СѓСЃС‚Р°РЅРѕРІР»РµРЅР° РІ Р°С‚СЂРёР±СѓС‚Рµ quantity РєРѕРЅС‚РµР№РЅРµСЂР°
        var cat_id		= $(c).attr('cat_id');		// Р�РґРµРЅС‚РёС„РёРєР°С‚РѕСЂ С‚РѕРІР°СЂР° РґРѕР»Р¶РЅ Р±С‹С‚СЊ СѓСЃС‚Р°РЅРѕРІР»РµРЅ РІ Р°С‚СЂРёР±СѓС‚Рµ cat_id РєРѕРЅС‚РµР№РЅРµСЂР°
        var price		= $(c).attr('price');		// Р¦РµРЅР° РґРѕР»Р¶РЅР° Р±С‹С‚СЊ СѓСЃС‚Р°РЅРѕРІР»РµРЅР° РІ Р°С‚СЂРёР±СѓС‚Рµ price РєРѕРЅС‚РµР№РЅРµСЂР°

        if (quantity == 'single') {
            // Р­С‚Рѕ СЂРѕР·РЅРёС‡РЅС‹Р№ РјР°РіР°Р·РёРЅ, РІ РєРѕРЅС‚РµР№РЅРµСЂРµ РґРѕР»Р¶РЅС‹ Р±С‹С‚СЊ СЌР»РµРјРµРЅС‚С‹ type=checkbox
            $('input[type="checkbox"]',c) // Р’С‹Р±РёСЂР°РµРј РІСЃРµ С‡РµРєР±РѕРєСЃС‹
                .each(
                function(i, el) {
                    if ($(el).attr('disabled')) { return; }

                    var size 			= $(el).attr('value');
                    var max_count		= parseInt($(el).attr('max_count')) || 10;
                    var basket_count	= parseInt($(el).attr('basket_count')) || 0;

                    if ($(el).attr('cat_id') != undefined) { cat_id = $(el).attr('cat_id'); }

                    if (el.checked) {
                        var count			= 1;
                        var total_count		= count + basket_count;

                        if (total_count > 10) {
                            el.checked = false;
                            $('#warning',c)
                                .html("Превышено количество добавляемого товара")
                                .fadeIn();
                            setTimeout("$('#warning').hide()", 5000);
                        } else if (total_count > max_count) {
                            el.checked = false;
                            $('#warning',c)
                                .html("Вы не можете добавить больше вещей. Лимит: <b>"+max_count+"</b>")
                                .fadeIn();
                            setTimeout("$('#warning').hide()", 5000);
                        }

                        if (el.checked) {
                            items[i] = {
                                'id'	: cat_id,
                                'size'	: size,
                                'count'	: total_count,
                                'el'	: el
                            };
                        }

                    } else {
                        if (basket_count >= Math.min(10, max_count)) {
                            el.checked = true;
                            el.disabled = true;
                        }
                    }

                    if (el.checked) {
                        $(el).attr('checked', 'checked');
                    } else {
                        $(el).removeAttr('checked');
                    }

                });
            //}

        } else if (quantity == 'multiple') {
            // Р­С‚Рѕ РѕРїС‚РѕРІС‹Р№ РјР°РіР°Р·РёРЅ, РІ РєРѕРЅС‚РµР№РЅРµСЂРµ РґРѕР»Р¶РЅС‹ Р±С‹С‚СЊ СЌР»РµРјРµРЅС‚С‹ type=text
            $('input[type="text"]',c) // Р’С‹Р±РёСЂР°РµРј РІСЃРµ РЅРµРїСѓСЃС‚С‹Рµ РїРѕР»СЏ
                .each(
                function(i, el) {
                    var size 			= $(el).attr('item_size');
                    var count			= parseInt($(el).val()) || 0;
                    var max_count		= parseInt($(el).attr('max_count'));
                    var basket_count	= parseInt($(el).attr('basket_count'));
                    var total_count		= count + basket_count;

                    if ($(el).attr('cat_id') != undefined) { cat_id = $(el).attr('cat_id'); }

                    if (isNaN(count)) {
                        el.value = '';
                    } else if (count <= 0) {
                        el.value = '';
                    } else {
                        if (total_count > 10) {
                            el.value = Math.min(10, max_count) - basket_count;
                            $('#warning',c)
                                .html("РќРµР»СЊР·СЏ РґРѕР±Р°РІРёС‚СЊ РІ РєРѕСЂР·РёРЅСѓ Р±РѕР»РµРµ 10 РІРµС‰РµР№ РѕРґРЅРѕРіРѕ СЂР°Р·РјРµСЂР°")
                                .fadeIn();
                            setTimeout("$('#warning').hide()", 5000);
                        } else if (total_count > max_count) {
                            el.value = max_count - basket_count;
                            $('#warning',c)
                                .html("Р—Р°РєР°Р·Р°РЅРЅРѕРµ Р’Р°РјРё РєРѕР»РёС‡РµСЃС‚РІРѕ РїСЂРµРІС‹С€Р°РµС‚ РёРјРµСЋС‰РµРµСЃСЏ РЅР° СЃРєР»Р°РґРµ.<br/>Р”РѕСЃС‚СѓРїРЅРѕ: <b>"+max_count+"</b>")
                                .fadeIn();
                            setTimeout("$('#warning').hide()", 5000);
                        } else {
                            el.value = count;
                        }
                    }

                    if (el.value != '') {
                        if (parseInt(el.value) > 0) {
                            items[i] = {
                                'id'	: cat_id,
                                'size'	: size,
                                'count'	: total_count,
                                'el'	: el
                            };
                        }
                    }
                }
            );

        }
        // Р Р°Р·СЂРµС€РёС‚СЊ/Р·Р°РїСЂРµС‚РёС‚СЊ СЌР»РµРјРµРЅС‚-РєРЅРѕРїРєСѓ РґРѕР±Р°РІР»РµРЅРёСЏ РІ РєРѕСЂР·РёРЅСѓ
        $('#add',c).attr({'disabled' : (items.length == 0)});

        this.items = items;
        return this.items;
    };

    this.beforeAddToBasket = function(containerId) {
        var c 			= $('#'+containerId)[0];
        var quantity	= $(c).attr('quantity');	// Р’РѕР·РјРѕР¶РЅРѕСЃС‚СЊ Р·Р°РєР°Р·Р° РѕРґРЅРѕР№/РЅРµСЃРєРѕР»СЊРєРёС… С€С‚СѓРє РѕРґРЅРѕРіРѕ СЂР°Р·РјРµСЂР° РґРѕР»Р¶РЅР° Р±С‹С‚СЊ СѓСЃС‚Р°РЅРѕРІР»РµРЅР° РІ Р°С‚СЂРёР±СѓС‚Рµ quantity РєРѕРЅС‚РµР№РЅРµСЂР°
        var items = this.getItems(containerId);
        var sizes = $(c).attr('sizes');

        // Р•РЎР›Р� С„РѕСЂРјР° РІС‹Р±РѕСЂР° СЂР°Р·РјРµСЂРѕРІ РЅРµ РїРѕРєР°Р·Р°РЅР°,
        if ($(c).css('display') == 'none') {

            // РўРћ Р•РЎР›Р� СЂР°Р·РјРµСЂ С‚РѕР»СЊРєРѕ 'СѓРЅРёРІРµСЂСЃР°Р»СЊРЅС‹Р№' Рё РЅРµР»СЊР·СЏ Р·Р°РєР°Р·Р°С‚СЊ СЃСЂР°Р·Сѓ РЅРµСЃРєРѕР»СЊРєРѕ С€С‚СѓРє
            if (quantity == 'single' && sizes == 0) {
                // РўРћ СЃСЂР°Р·Сѓ РґРѕР±Р°РІР»СЏРµРј РїРѕР·РёС†РёСЋ СЌС‚РѕРіРѕ СЂР°Р·РјРµСЂР° РІ РєРѕСЂР·РёРЅСѓ, РЅРµ РїРѕРєР°Р·С‹РІР°СЏ С„РѕСЂРјСѓ
                this.addToBasket(containerId);
            } else {
                // Р�РќРђР§Р• РїРѕРєР°Р·С‹РІР°РµРј С„РѕСЂРјСѓ РІС‹Р±РѕСЂР° СЂР°Р·РјРµСЂРѕРІ (Рё РєРѕР»РёС‡РµСЃС‚РІ, РµСЃР»Рё quantity = 'multiple')
                //$(c).show();

                this.showSizesForm(containerId);
                $('#SMALL_BASKET').hide();
            }
        } else {

            // Р�РќРђР§Р• СЃРєСЂС‹РІР°РµРј С„РѕСЂРјСѓ РІС‹Р±РѕСЂР° СЂР°Р·РјРµСЂРѕРІ
            //$(c).hide();
            this.hideSizesForm(containerId);
        }
    };

    this.showSizesForm = function(containerId) {
        var c = $('#'+containerId)[0];
        if (null != this.currentFormId) {
            this.hideSizesForm(this.currentFormId);
        }

        var formLeft = $(c).parent().offset().left;

        if (true || $(c).attr('quantity') == 'multiple') {
            if ($(document).width() - formLeft < 500) {
                if ($(c).attr('layout_mode') == 'right') {
                    this.changeFormLayout(containerId);
                }
            } else {
                if ($(c).attr('layout_mode') == 'left') {
                    this.changeFormLayout(containerId);
                }
            }
        }

        if ($(document).scrollTop() + 50 > $(c).parent().offset().top) {
            $(document).scrollTop(Math.max(30, $(c).parent().offset().top - 50));
        }

        $(c).show();

        this.currentFormId = containerId;
    };

    this.changeFormLayout = function(containerId) {
        var c = $('#'+containerId)[0];
        var currentLayout = $(c).attr('layout_mode');
        var newLayout = (currentLayout == 'right') ? 'left' : 'right';

        var $td1 = $('#leftPart' , c);
        var $td2 = $('#rightPart' , c);

        // РќРµ РѕСЃРѕР±Рѕ РєСЂР°СЃРёРІРѕРµ СЂРµС€РµРЅРёРµ, РЅРѕ СЂР°Р±РѕС‚Р°РµС‚
        // РќРµРѕР±С…РѕРґРёРјРѕ, С‡С‚РѕР±С‹ С‚Р°Р±Р»РёС†Р° СЃ СЂР°Р·РјРµСЂР°РјРё РёРјРµР»Р° С€РёСЂРёРЅСѓ 250px + 10px РЅР° padding СЃ РѕР±РѕРёС… СЃС‚РѕСЂРѕРЅ Рё +1px РЅР° РіСЂР°РЅРёС†Сѓ
        if (newLayout == 'left') {
            $td1.insertAfter($td2);
            $(c).css({left:'-271px'});
            $('.new_flag', c).css({left:'270px'});
        } else {
            $td2.insertAfter($td1);
            $(c).css({left:'-11px'});
            $('.new_flag', c).css({left:'0px'});
        }
        $(c).attr({'layout_mode':newLayout});
    };

    this.hideSizesForm = function(containerId) {
        var c = $('#'+containerId)[0];

        $(c).hide();

        this.currentFormId = null;
    };

    /**
     * Добавление вещи без размера
     * @param product_id
     * @author Stanislav WEB
     */
    this.addToBasketSingle = function(product_id)
    {
        var items = [];
       items.push({
            'el'    :   type = 'input',
            'id'    :   ""+product_id+"",
            'size'  :   "0",
            'count' :   1
        });
        this.addToBasket(false, true, items);
    }

    this.addToBasket = function(containerId, hideContainer, items) {

        var hideContainer = hideContainer || false;				        // РЎРєСЂС‹РІР°С‚СЊ С„РѕСЂРјСѓ РІС‹Р±РѕСЂР° СЂР°Р·РјРµСЂРѕРІ?
        var items = (!items) ? this.getItems(containerId) : items;  	// РџСЂРѕРІРµСЂРёРј РїРѕСЃР»РµРґРЅРёРµ РёР·РјРµРЅРµРЅРёСЏ РІ С„РѕСЂРјРµ

        if (items.length > 0) {

            var itemsQueryString = [];
            var c = $('#'+containerId)[0];
            for (i in items) {
                itemsQueryString.push('item['+items[i].id+'][]='+items[i].size+'_'+encodeURIComponent(items[i].count));

                if (true || $(c).attr('quantity') == 'multiple') {
                    //$(items[i].el).attr({'basket_count': items[i].count});
                    // TODO: РџСЂРёСЃРІР°РёРІР°С‚СЊ РЅРѕРІРѕРµ Р·РЅР°С‡РµРЅРёРµ РїРѕСЃР»Рµ РґРѕР±Р°РІР»РµРЅРёСЏ РІ РєРѕСЂР·РёРЅСѓ
                    //$('#basket_count_'+items[i].size, c).html(items[i].count > 0 ? items[i].count : '&mdash;');
                }
            }

            if (itemsQueryString.length) {
                this.hash = Math.random();
                $.ajax({
                    type: 'GET',
                    url: '/basket/update/?'+itemsQueryString.join('&'),
                    data: {'mode': 'small', 'hash': this.hash},
                    success: function(data) { return basket.update(data); },
                    dataType: 'json',
                    timeout: 20000,
                    error: function (data, textStatus, jqXHR) {
                        global.showAjaxErrorStatus(textStatus);
                    }
                });
                //$('.data_loading').each(function (i, el) { $(el).addClass('active'); });
                global.showStatus();
            }

            if (hideContainer) {
                this.hideSizesForm(containerId);
            }

            $('#'+containerId+' .sizes').removeClass('empty');
        } else { console.log('FAIL');
            $('#'+containerId+' .sizes').addClass('empty');
        }
    };

    this.goToSizes = function(id) {
        var $b = $('#sizes_button_'+id);
        if ($b.length > 0) {
            $(document).scrollTop($b.offset().top - $b.parent().parent().height());
            $b.trigger('click');
            return false;
        } else {
            $b = $('#sizes_'+id);
            if ($b.length > 0) {
                $('#SMALL_BASKET').hide();
                return false;
            }
        }
        return true;
    };

    this.small_item_click = function(i) {
        var $clicked = $('.small-basket div.small_item .preview.click');
        if (!$clicked.length) {
            $('.small-basket div.small_item .preview').eq(i).addClass('click');
        } else {
            $('.small-basket div.small_item .preview').eq(i).addClass('click');
            $clicked.removeClass('click');
        }
        this.small_item_select(i);
    };

    this.small_item_hover = function(i) {
        if (!$('.small-basket div.small_item .preview.click').length) {
            this.small_item_select(i);
        }
    };

    this.small_item_select = function(i) {
        $('.small-basket div.small_item .preview').removeClass('selected');
        $('.small-basket div.small_item .preview').eq(i).addClass('selected');
        $('.small-basket .item-row').hide();
        $('.item-row').eq(i).show();
    };
};;

get_client_info = function(){
    this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
    this.version = this.searchVersion(navigator.userAgent)
        || this.searchVersion(navigator.appVersion)
        || "an unknown version";
    this.OS = this.searchString(this.dataOS) || "an unknown OS";
    this.OSVersion = this.searchString(this.dataOSVer) || "an unknown ver";
    this.host = location.host;
    this.pathname = location.pathname;
    this.title = document.title;
}

get_client_info.prototype = {

    searchString : function (data)
    {
        for (var i=0;i<data.length;i++)
        {
            var dataString = data[i].string;
            var dataProp = data[i].prop;
            this.versionSearchString = data[i].versionSearch || data[i].identity;
            if (dataString)
            {
                if (dataString.indexOf(data[i].subString) != -1)
                    return data[i].identity;
            }
            else if (dataProp)
                return data[i].identity;
        }
    },

    searchVersion : function (dataString)
    {
        var index = dataString.indexOf(this.versionSearchString);
        if (index == -1) return;
        return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
    },

    dataBrowser :
        [
            {
                string: navigator.userAgent,
                subString: "Chrome",
                identity: "Chrome"
            },
            {   string: navigator.userAgent,
                subString: "OmniWeb",
                versionSearch: "OmniWeb/",
                identity: "OmniWeb"
            },
            {
                string: navigator.vendor,
                subString: "Apple",
                identity: "Safari",
                versionSearch: "Version"
            },
            {
                prop: window.opera,
                identity: "Opera"
            },
            {
                string: navigator.vendor,
                subString: "iCab",
                identity: "iCab"
            },
            {
                string: navigator.vendor,
                subString: "KDE",
                identity: "Konqueror"
            },
            {
                string: navigator.userAgent,
                subString: "Firefox",
                identity: "Firefox"
            },
            {
                string: navigator.vendor,
                subString: "Camino",
                identity: "Camino"
            },
            {       // for newer Netscapes (6+)
                string: navigator.userAgent,
                subString: "Netscape",
                identity: "Netscape"
            },
            {
                string: navigator.userAgent,
                subString: "MSIE",
                identity: "IE",
                versionSearch: "MSIE"
            },
            {
                string: navigator.userAgent,
                subString: "Gecko",
                identity: "Mozilla",
                versionSearch: "rv"
            },
            {       // for older Netscapes (4-)
                string: navigator.userAgent,
                subString: "Mozilla",
                identity: "Netscape",
                versionSearch: "Mozilla"
            }
        ],

    dataOS : [
        {
            string: navigator.platform,
            subString: "Win",
            identity: "Windows"
        },
        {
            string: navigator.platform,
            subString: "Mac",
            identity: "Mac"
        },
        {
            string: navigator.userAgent,
            subString: "iPhone",
            identity: "iPhone/iPod"
        },
        {
            string: navigator.platform,
            subString: "Linux",
            identity: "Linux"
        }],

    dataOSVer : [
        {
            string: navigator.appVersion,
            subString: "NT 5.1",
            identity: "XP"
        },
        {
            string: navigator.appVersion,
            subString: "NT 6.0",
            identity: "Vista"
        },
        {
            string: navigator.appVersion,
            subString: "NT 6.1",
            identity: "7"
        },
        {
            string: navigator.appVersion,
            subString: "NT 6.2",
            identity: "8"
        },
        {
            string: navigator.userAgent,
            subString: "Mac OS X 10.0",
            identity: "Cheetah"
        },
        {
            string: navigator.userAgent,
            subString: "Mac OS X 10.1",
            identity: "Puma"
        },
        {
            string: navigator.userAgent,
            subString: "Mac OS X 10.2",
            identity: "Jaguar"
        },
        {
            string: navigator.userAgent,
            subString: "Mac OS X 10.3",
            identity: "Panther"
        },
        {
            string: navigator.userAgent,
            subString: "Mac OS X 10.4",
            identity: "Tiger"
        },
        {
            string: navigator.userAgent,
            subString: "Mac OS X 10.5",
            identity: "Leopard"
        },
        {
            string: navigator.userAgent,
            subString: "Mac OS X 10.6",
            identity: "Snow Leopard"
        },
        {
            string: navigator.userAgent,
            subString: "Mac OS X 10.7",
            identity: "Lion"
        },
        {
            string: navigator.userAgent,
            subString: "Mac OS X 10.8",
            identity: "Mountain Lion"
        },
        {
            string: navigator.userAgent,
            subString: "Mac OS X 10.9",
            identity: "Mavericks"
        }]
};chat = function(setting){
    var handle = this;

    for(var item in setting)
        this._observers[item] = setting[item];

    var socket = io.connect(this._address);

    socket.on('connect', function (){
        var chat_cookie = handle._getcookie();
        var data = new get_client_info();

        socket.send(JSON.stringify({event: 'start_chat', id:((chat_cookie === false) ? 'new' : chat_cookie), data: data}));
    });

    socket.on('message', function (msg){
        switch(msg.event){
            case 'chat_id':
                handle._setcookie(msg.id);
                handle.raise('onconnect', msg.data);
                break;
            default:
                handle.raise('onmessage', msg);
        }
    });

    socket.on('error', function (){
        handle.raise('onerror', null);
    });

    socket.on('disconnect', function (){
        handle.raise('ondisconnect', null);
    });

    socket.on('reconnect', function (){
        handle.raise('onreconnect', null);
    });

    this.send = function(msg){
        var chat_cookie = handle._getcookie();
        socket.send(JSON.stringify({event: 'client_mess', id:((chat_cookie === false) ? 'new' : chat_cookie), data: msg}));
    }
}

chat.prototype = {
    _address : 'http://z95.grif.dev95.ru:4000',
    _cookie : {
        name : '__chat'
    },
    _observers : {
        onconnect:      null,
        onerror:        null,
        onmessage:      null,
        ondisconnect:   null,
        onreconnect:    null
    },
    raise : function(name, data){
        var data = data || null;
        if(this._observers[name])
            this._observers[name].call(null, data);
    },
    _setcookie : function (value) {
        document.cookie = this._cookie.name + '=' + value + '; path=/';
        return true;
    },
    _getcookie : function () {
        if (document.cookie.length > 0) {
            var c_start = document.cookie.indexOf(this._cookie.name + '=');
            if (c_start != -1) {
                c_start = c_start + this._cookie.name.length + 1;
                var c_end = document.cookie.indexOf(';', c_start);
                if (c_end == -1) {
                    c_end = document.cookie.length;
                }
                return unescape(document.cookie.substring(c_start, c_end));
            }
        }
        return false;
    }
}

    /*
     // РЎРѕР·РґР°РµРј С‚РµРєСЃС‚ СЃРѕРѕР±С‰РµРЅРёР№ РґР»СЏ СЃРѕР±С‹С‚РёР№
     strings = {
     'connected': '[sys][time]%time%[/time]: Р’С‹ СѓСЃРїРµС€РЅРѕ СЃРѕРµРґРёРЅРёР»РёСЃСЊ Рє СЃРµСЂРІРµСЂРѕРј РєР°Рє [user]%name%[/user].[/sys]',
     'userJoined': '[sys][time]%time%[/time]: РџРѕР»СЊР·РѕРІР°С‚РµР»СЊ [user]%name%[/user] РїСЂРёСЃРѕРµРґРёРЅРёР»СЃСЏ Рє С‡Р°С‚Сѓ.[/sys]',
     'messageSent': '[out][time]%time%[/time]: [user]%name%[/user]: %text%[/out]',
     'messageReceived': '[in][time]%time%[/time]: [user]%name%[/user]: %text%[/in]',
     'userSplit': '[sys][time]%time%[/time]: РџРѕР»СЊР·РѕРІР°С‚РµР»СЊ [user]%name%[/user] РїРѕРєРёРЅСѓР» С‡Р°С‚.[/sys]'
     };
     window.onload = function() {
     ch = new chat({
     onmessage: function(msg){
     switch(msg.event){
     case 'delivered':
     var d = new Date(msg.time);
     $('<div class="mess_1"><span>'+d.toLocaleString()+'</span>'+msg.data+'</div>').appendTo($('.messages_area'));
     break;
     case 'message':
     var d = new Date(msg.time);
     $('<div class="mess_0"><span>'+d.toLocaleString()+' : '+msg.name+'</span>'+msg.data+'</div>').appendTo($('.messages_area'));
     break;
     default:
     console.log(msg);
     }
     },
     ondisconnect: function(){
     console.log('disconnect');
     },
     onconnect: function(data){
     for(var i in data){
     var msg = data[i];
     var d = new Date(msg.time);
     $('<div class="mess_'+(Math.abs(msg.role-1))+'"><span>'+d.toLocaleString()+((msg.role == 1) ? ' : '+msg.name : '')+'</span>'+msg.text+'</div>').appendTo($('.messages_area'));
     }
     },
     onerror: function(){
     console.log('error');
     },
     onreconnect: function(){
     console.log('reconnect');
     }
     });

     $('#send').click(function() {
     ch.send($('#sendText').val());
     $('#sendText').val('');
     });

     $('#sendText').show();
     $('#send').show();

     };
     */;