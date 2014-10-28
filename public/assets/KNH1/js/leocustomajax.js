(function($) {
    $.LeoCustomAjax = function() {
        this.leoData = 'leoajax=1';
    };
    $.LeoCustomAjax.prototype = {
        processAjax: function() {
            var myElement = this;
        
            if (leoOption.productNumber && $("#categories_block_left .leo-qty").length) myElement.getCategoryList();
			else if($("#categories_block_left .leo-qty").length) $("#categories_block_left .leo-qty").remove();
            if (leoOption.productInfo && $(".leo-more-info").length) myElement.getProductListInfo();
			else if($(".leo-more-info").length) $(".leo-more-info").remove();
            if (leoOption.productTran && $(".product-additional").length) myElement.getProductListTran();
			else if($(".product-additional").length) $(".product-additional").remove();
			if (leoOption.productCdown && $(".leo-more-cdown").length) myElement.getProductCdownInfo();
			else if($(".leo-more-cdown").length) $(".leo-more-cdown").remove();
			if (leoOption.productCdown && $(".leo-more-color").length) myElement.getProductColorInfo();
			else if($(".leo-more-color").length) $(".leo-more-color").remove();
			
			if(myElement.leoData != "leoajax=1"){
            $.ajax({
                type: 'POST',
                headers: {"cache-control": "no-cache"},
                url: baseDir + 'modules/leocustomajax/leoajax.php' + '?rand=' + new Date().getTime(),
                async: true,
                cache: false,
                dataType: "json",
                data: myElement.leoData,
                success: function(jsonData) {
                    if (jsonData) {
                        if (jsonData.cat) {
                            for (i = 0; i < jsonData.cat.length; i++) {
                                $("#leo-cat-" + jsonData.cat[i].id_category).html(jsonData.cat[i].total);
                                $("#leo-cat-" + jsonData.cat[i].id_category).show();
                            }
                        }
                        if (jsonData.pro_info) {
                            var listProduct = new Array();
                            for (i = 0; i < jsonData.pro_info.length; i++) {
                                listProduct[jsonData.pro_info[i].id] = jsonData.pro_info[i].content;
                            }

                            $(".leo-more-info").each(function() {
                                $(this).html(listProduct[$(this).data("idproduct")]);
                            });
                            addEffectProducts();
                        }
						
						if (jsonData.pro_cdown) {
                            var listProduct = new Array();
                            for (i = 0; i < jsonData.pro_cdown.length; i++) {
                                listProduct[jsonData.pro_cdown[i].id] = jsonData.pro_cdown[i].content;
                            }

                            $(".leo-more-cdown").each(function() {
                                $(this).html(listProduct[$(this).data("idproduct")]);
                            });
                        }
						
						if (jsonData.pro_color) {
                            var listProduct = new Array();
                            for (i = 0; i < jsonData.pro_color.length; i++) {
                                listProduct[jsonData.pro_color[i].id] = jsonData.pro_color[i].content;
                            }

                            $(".leo-more-color").each(function() {
                                $(this).html(listProduct[$(this).data("idproduct")]);
                            });
                        }

                        if (jsonData.pro_add) {
                            var listProductImg = new Array();
                            for (i = 0; i < jsonData.pro_add.length; i++) {
                                listProductImg[jsonData.pro_add[i].id] = jsonData.pro_add[i].content;
                            }
                            $(".product-additional").each(function() {
                                if (listProductImg[$(this).data("idproduct")])
                                    $(this).html('<img class="img-responsive" title="" alt="" src="' + listProductImg[$(this).data("idproduct")] + '"/>');
                            });
                            //addEffOneImg();
                        }
                    }
                },
                error: function() {
                }
            });
            }
        },
        getCategoryList: function() {
            //get category id
            var leoCatList = "";
            $("#categories_block_left .leo-qty").each(function() {
                if (leoCatList)
                    leoCatList += "," + $(this).attr("id");
                else
                    leoCatList = $(this).attr("id");
            });
                       
            if (leoCatList) {
				leoCatList = leoCatList.replace(/leo-cat-/g, "");
                this.leoData += '&cat_list=' + leoCatList;
            }
            return false;
        },
        getProductListInfo: function() {
            var leoProInfo = "";
            $(".leo-more-info").each(function() {
                if (!leoProInfo)
                    leoProInfo += $(this).data("idproduct");
                else
                    leoProInfo += "," + $(this).data("idproduct");
            });
            if (leoProInfo) {
                this.leoData += '&pro_info=' + leoProInfo;
            }
            return false;
        },
		
		getProductCdownInfo: function() {
            var leoProCdown = "";
            $(".leo-more-cdown").each(function() {
                if (!leoProCdown)
                    leoProCdown += $(this).data("idproduct");
                else
                    leoProCdown += "," + $(this).data("idproduct");
            });
            if (leoProCdown) {
                this.leoData += '&pro_cdown=' + leoProCdown;
            }
            return false;
        },
		
		getProductColorInfo: function() {
            var leoProColor = "";
            $(".leo-more-color").each(function() {
                if (!leoProColor)
                    leoProColor += $(this).data("idproduct");
                else
                    leoProColor += "," + $(this).data("idproduct");
            });
            if (leoProColor) {
                this.leoData += '&pro_color=' + leoProColor;
            }
            return false;
        },
		
        getProductListTran: function() {
            //tranditional image
            var leoAdditional = "";
            $(".product-additional").each(function() {
                if (!leoAdditional)
                    leoAdditional += $(this).data("idproduct");
                else
                    leoAdditional += "," + $(this).data("idproduct");
            });
            if (leoAdditional) {
                this.leoData += '&pro_add=' + leoAdditional;
            }
            return false;
        }
    };
}(jQuery));

function addJSProduct(currentProduct) {
//    if (typeof serialScroll == 'function') { 
        $('.thumbs_list_' + currentProduct).serialScroll({
            items: 'li:visible',
            prev: '.view_scroll_left_' + currentProduct,
            next: '.view_scroll_right_' + currentProduct,
            axis: 'y',
            offset: 0,
            start: 0,
            stop: true,
            duration: 700,
            step: 1,
            lazy: true,
            lock: false,
            force: false,
            cycle: false
        });
        $('.thumbs_list_' + currentProduct).trigger('goto', 1);// SerialScroll Bug on goto 0 ?
        $('.thumbs_list_' + currentProduct).trigger('goto', 0);
 //   }   
}
function addEffectProducts(){
    if(leoOption != 'undefined' && leoOption.productInfo){
        $(".leo-more-info").each(function() {
            addJSProduct($(this).data("idproduct"));
        });
        addEffectProduct();
    }
}

function addEffectProduct() {
    var speed = 800;
    var effect = "easeInOutQuad";

    //$(".products_block .carousel-inner .ajax_block_product:first-child").mouseenter(function() {
        //$(".products_block .carousel-inner").css("overflow", "inherit");
    //});
    //$(".carousel-inner").mouseleave(function() {
        //$(".carousel-inner").css("overflow", "hidden");
    //});

    $(".leo-more-info").each(function() {
        var leo_preview = this;
        $(leo_preview).find(".leo-hover-image").each(function() {
            $(this).mouseover(function() {
                var big_image = $(this).attr("rel");
                imgElement = $(leo_preview).parent().find(".product_img_link img").first();
                if (!imgElement.length) {
                    imgElement = $(leo_preview).parent().find(".product_image img").first();
                }

                if (imgElement.length) {
                    $(imgElement).stop().animate({opacity: 0}, {duration: speed, easing: effect});
                    $(imgElement).first().attr("src", big_image);
                    $(imgElement).first().attr("data-rel", big_image);
                    $(imgElement).stop().animate({opacity: 1}, {duration: speed, easing: effect});
                }
            });
        });

        $('.thickbox-ajax-'+$(this).attr("rel")).fancybox({
            'hideOnContentClick': true,
            'transitionIn'  : 'elastic',
            'transitionOut' : 'elastic'
        });
    });
}

function addEffOneImg() {
    var speed = 800;
    var effect = "easeInOutQuad";

    $(".product-additional").each(function() {
        if ($(this).find("img").length) {
            var leo_hover_image = $(this).parent().find("img").first();
            var leo_preview = $(this);
            $(this).parent().mouseenter(function() {
                $(this).find("img").first().stop().animate({opacity: 0}, {duration: speed, easing: effect});
                $(leo_preview).stop().animate({opacity: 1}, {duration: speed, easing: effect});
            });
            $(this).parent().mouseleave(function() {
                $(this).find("img").first().stop().animate({opacity: 1}, {duration: speed, easing: effect});
                $(leo_preview).stop().animate({opacity: 0}, {duration: speed, easing: effect});
            });
        }
    });
}