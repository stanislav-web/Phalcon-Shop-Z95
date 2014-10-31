var cart = {
    articul : null,
    product_id : null,
    init : function() {
//        this.add_to_cart();
        this.remove_from_cart();
        this.quantity();
        this.procced_to_back();
    },
    add_to_cart : function () {

        var data = {
                        product_id : cart.product_id,
                        articul : cart.articul,
                        quantity_wanted :$('#quantity_wanted').val(),
                        sizes : $('#group_1').val()
                    };
        $.ajax({
            type: "POST",
            url: '/cart/addToCart',
            data: data,
            success: function (data) {

                if(data.result) {
                    window.location.href = '/cart';
                }
                return false;
            },
            dataType: 'json'
        });

    },
    remove_from_cart : function () {
        $('body .cart_quantity_delete').on('click', function() {
            var removeHtml = $(this).closest('tr');
            var data = {product_id : $(this).data().product_id}

            $.ajax({
                type: "POST",
                url: '/cart/removeFromCart',
                data: data,
                success: function (data) {
                    if($(removeHtml).next().length == 0 && $(removeHtml).prev().length == 0) {
                        $('table#cart_summary').remove()
                        $('#header_right .ajax_cart_quantity').html(0);
                        $('#summary_products_quantity span').html(0);
                        cart.recountGrandTotal();
                    } else {
                        $('#summary_products_quantity span').html(parseInt($('#summary_products_quantity span').html()) - parseInt($(removeHtml).find('.cart_quantity_input').val()));
                        $('#header_right .ajax_cart_quantity').html(parseInt($('#header_right .ajax_cart_quantity').html()) - parseInt($(removeHtml).find('.cart_quantity_input').val()));
                        $(removeHtml).remove();
                        cart.recountGrandTotal();
                    }
                    return false;
                },
                dataType: 'json'
            });
            return false;
        });
    },

    quantity : function () {
        $('body .cart_quantity_down').on('click', function(){
            var quantity = $(this).parent().parent().find('.cart_quantity_input');
            if(quantity.val() > 0) {
                quantity.val(quantity.val() - 1);
                $('#summary_products_quantity span').html(parseInt($('#summary_products_quantity span').html()) - 1);
                $('#header_right .ajax_cart_quantity').html(parseInt($('#header_right .ajax_cart_quantity').html()) - 1);
                cart.recountPrice(quantity.parent().prev(), quantity.parent().next(), quantity.val());
                cart.recountGrandTotal();
            }
            return false;
        });
        $('body .cart_quantity_up').on('click', function(){
            var quantity = $(this).parent().parent().find('.cart_quantity_input');
            quantity.val(parseInt(quantity.val()) + 1);
            $('#summary_products_quantity span').html(parseInt($('#summary_products_quantity span').html()) + 1);
            $('#header_right .ajax_cart_quantity').html(parseInt($('#header_right .ajax_cart_quantity').html()) + 1);
            cart.recountPrice(quantity.parent().prev(), quantity.parent().next(), quantity.val());
            cart.recountGrandTotal();
            return false;
        })

    },

    quantityKeyPress : function (handler) {
        if(parseInt($(handler).val()) % 1 === 0) {
            $('#summary_products_quantity span').html(parseInt($('#summary_products_quantity span').html()) - parseInt($(handler).val()));
            $('#header_right .ajax_cart_quantity').html(parseInt($('#header_right .ajax_cart_quantity').html()) - parseInt($(handler).val()));
        } else {
            $('#summary_products_quantity span').html($('#summary_products_quantity span').html());
            $('#header_right .ajax_cart_quantity').html($('#header_right .ajax_cart_quantity').html());
        }
    },
    quantityKeyUp : function(handler) {
        if(parseInt($(handler).val()) % 1 === 0) {
            $('#summary_products_quantity span').html(parseInt($('#summary_products_quantity span').html()) + parseInt($(handler).val()));
            $('#header_right .ajax_cart_quantity').html(parseInt($('#header_right .ajax_cart_quantity').html()) + parseInt($(handler).val()));
            cart.recountPrice($(handler).parent().prev(), $(handler).parent().next(), parseInt($(handler).val()));
            cart.recountGrandTotal();
        } else {
            $('#summary_products_quantity span').html($('#summary_products_quantity span').html());
            $('#header_right .ajax_cart_quantity').html($('#header_right .ajax_cart_quantity').html());
        }
    },
    recountPrice : function(unit, total, count) {

        total.find('.price span').html((parseFloat(unit.find('.price span').html()) * count) +'.00');
    },
    recountGrandTotal : function() {
        var total = 0;
        $('td.cart_total').each(function(i, v){
            total += parseFloat($(v).find('.price span').html());
        })
        $('#total_product span').html(total + '.00');
        $('#total_price_container span span').html(total + '.00');
    },
    procced_to_back : function() {
        $('body #procced_to_back').on('click', function(){

            var data = new Array();
            $('table#cart_summary tbody tr').each(function(iterator, value){
                data[$(value).data().articul] = {count : $(value).find('input.cart_quantity_input').val(),
                                                size : $(value).find('.js-product_size span').html()};


            })

            console.log(data);

            return false;
            $.ajax({
                type: "POST",
                url: url,
                data: data,
                success: function (data) {

                },
                dataType: 'json'
            });
        })
    }
}
$(document).ready(function(){
    cart.init();
})
