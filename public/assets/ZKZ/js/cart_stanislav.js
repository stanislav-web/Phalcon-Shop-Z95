/**
 * Cart.js API More implemented product Cart management
 * @author Stanislav WEB
 * @date 18.07.2014
 */
"use strict";
var Cart = {
    /**
     * @var object config
     * Description of settings cart transmission methods
     */
    config: {
        dataType : 'json', // response data type (JSON recomended)
        method : 'POST', // request methid
        timeout : 10000, // time out request - response mil.s
        async : 'true', // enable request - response asynxronym mode
        cache : 'false', // caching response
        delay : 1000, // delay before updating basket
        maxquant : 25, // max count to be placed to quantity by 1 item
        sameitems : 10 // max count of the same items to be placed int the cart
    },
    /**
     * @var object mixed JSON | STRING
     * Response from server
     */
    response : false,
    /**
     * @var timeoutId for add request lookupId
     * Timeout Id for setTimeout()
     */
    lookupId : false,
    /**
     * get: function() Load cart templates with recalculation
     * @return null
     */
    get: function() {
// Set -> request to server
        var requestData = {
            'hash' : Math.floor((Math.random() * 100000000000) + 1),
        };
        this.response = Cart.sendRequest(
            '/ajax/customer/cart/get/',
            requestData
        );
// Get <- response from server
        $(document).ajaxStop($.proxy(function() {
// place code to be executed on completion of last outstanding ajax call here
            if(this.response)
            {
// if isset the response from ajax
                var response = this.response,
                    data = Cart.processRequest(response);
// if isset valid data from pre process result
                if(data)
                {
// insert the result in places then clear response
                    for(var key in data.content)
                    {
                        $('#'+key).html(data.content[key]).show();
                    }
                    delete(data);
                    delete(response);
                    delete(this.response);
                }
            }
        }, this));
    },
    /**
     * add: function(itemParams) Adding to cart
     * @var string itemParams Cart container (product)
     * @return html
     */
    add: function(itemParams) {
        if(itemParams.length > 0) {
// Set -> request to server
            var requestData = {
                'hash' : Math.floor((Math.random() * 100000000000) + 1),
                'params' : decodeURI(itemParams)
            };
            this.response = Cart.sendRequest(
                '/ajax/customer/cart/add/',
                requestData
            );
// Get <- response from server
            $(document).ajaxStop($.proxy(function() {
// place code to be executed on completion of last outstanding ajax call here
                var response = this.response,
                    data = Cart.processRequest(response);
// if isset valid data from pre process result
                if(data)
                {
// insert the result in places then clear response
                    for(var key in data.content)
                    {
                        $('#'+key).html(data.content[key]).show();
                    }
                    delete(data);
                    delete(response);
                    delete(this.response);
                }
            }, this));
        }
    },
    /**
     * addBefore: function(item_id) Show preview to Cart
     * @var string item_id Cart container (item_id)
     * @return html
     */
    addBefore: function(itemParams) {
        if(itemParams.length > 0) {
// Set -> request to server
            var requestData = {
                'hash' : Math.floor((Math.random() * 100000000000) + 1),
                'params' : decodeURI(itemParams)
            };
            this.response = Cart.sendRequest(
                '/ajax/customer/cart/addBefore/',
                requestData
            );
// Get <- response from server
            $(document).ajaxStop($.proxy(function() {
// place code to be executed on completion of last outstanding ajax call here
                var response = this.response,
                    data = Cart.processRequest(response);
// if isset valid data from pre process result
                if(data)
                {
// insert the result in places then clear response
                    for(var key in data.content)
                    {
                        $('#'+key).html(data.content[key]).show();
                    }
                    delete(data);
                    delete(response);
                    delete(this.response);
                }
            }, this));
        }
    },
    /**
     * delete: function(obj) Delete selected item
     * @var jQuery object element
     * @return html
     */
    delete: function(obj) {
        var item = obj.data('item'),
            size = obj.data('size');
        if(item !== 'undefined' && size !=='undefined')
        {
// Set -> request to server
            var requestData = {
                'hash' : Math.floor((Math.random() * 100000000000) + 1),
                'params' : {'item_id' : item, 'size': size}
            };
            this.response = Cart.sendRequest(
                '/ajax/customer/cart/delete/',
                requestData
            );
// Get <- response from server
            $(document).ajaxStop($.proxy(function() {
// place code to be executed on completion of last outstanding ajax call here
                var response = this.response,
                    data = Cart.processRequest(response);
// if isset valid data from pre process result
                if(data)
                {
// insert the result in places then clear response
                    for(var key in data.content)
                    {
                        $('#'+key).html(data.content[key]).show();
                    }
                    delete(data);
                    delete(response);
                    delete(this.response);
                }
            }, this));
        }
    },
    /**
     * recount: function(obj) Recount selected item
     * @var jQuery object element
     * @return html
     */
    recount: function(obj) {
        if(obj.length > 0)
        {
// destroy delay after use
            if(this.lookupId) {
                clearTimeout(this.lookupId);
            }
            var elementData = obj.data('params');
            if(typeof(elementData.mode) !== "undefined")
            {
// if button pressed, find depended field
                var inputRange = $('input[name="counter['+elementData.item_id+']['+elementData.size+']"]'),
                    counter = inputRange.val(),
                    mode = elementData.mode;
            }
            else
            {
// input active - is it depended field
                var counter = obj.val();
            }
            var stock = parseInt(elementData.stock),
                item_id = parseInt(elementData.item_id),
                size = elementData.size;
            if(stock < 1)
            {
// show short message
                $("[data-declined="+item_id+"-"+size+"]")
                    .html('Извините, этой вещи уже нет на складе. Кто-то ее купил раньше вас.')
                    .fadeIn();
                return false;
            }
            if(!isNaN(counter))
            {
// change input range
                switch(mode) {
                    case '-': // -1
                        if(counter > 0) {
                            counter = parseInt(counter)-1;
                            inputRange.val(counter);
                        }
                        break;
                    case '+': // +1
                        if(counter < stock) // check if stock is available
                        {
                            if(counter < this.config.maxquant) {
                                counter = parseInt(counter)+1;
                                inputRange.val(counter);
                            }
                        }
                        else
                        {
// show short message
                            $("[data-declined="+item_id+"-"+size+"]")
                                .html('Извините, этой вещи доступно только '+stock+' шт.')
                                .fadeIn();
                            return false;
                        }
                        break;
                    default:
                        counter = parseInt(counter);
                        if(counter > stock)
                        {
// show short message
                            $("[data-declined="+item_id+"-"+size+"]")
                                .html('Извините, этой вещи доступно только '+stock+' шт.')
                                .fadeIn();
                            obj.val(stock);
                            return false;
                        }
                        break;
                }
// if is valid do the look up after typing numbers
                this.lookupId = setTimeout(function() {
// do the Request to server
                    elementData.count = counter;
                    var requestData = {
                        'hash' : Math.floor((Math.random() * 100000000000) + 1),
                        'params' : $.makeArray(elementData)
                    };
                    this.response = Cart.sendRequest(
                        '/ajax/customer/cart/recount/',
                        requestData
                    );
// Get <- response from server
                    $(document).ajaxStop($.proxy(function() {
// place code to be executed on completion of last outstanding ajax call here
                        var response = this.response,
                            data = Cart.processRequest(response);
// if isset valid data from pre process result
                        if(data)
                        {
// insert the result in places then clear response
                            for(var key in data.content)
                            {
                                $('#'+key).html(data.content[key]).show();
                            }
                            delete(data);
                            delete(response);
                            delete(this.response);
                        }
                    }, this));
                }, this.config.delay);
            }
        }
        return false;
    },
    /**
     * sendRequest: function(uri, params) Ajax request -> response action
     * @param string uri
     * @param string query
     * @return object Ajax ready state
     */
    sendRequest: function (uri, params) {
// configure ajax request
        return $.ajax({
            url : uri,
            data : params,
            type : Cart.config.method,
            dataType : Cart.config.dataType,
            timeout : Cart.config.timeout,
            async : Cart.config.async,
            cache : Cart.config.cache,
            beforeSend: function (jqXHR, settings) {
// Handle the beforeSend event
                Cart.getMessage('Секундочку ...');
            },
            complete: function (jqXHR, textStatus) {
// Handle the complete event
                Cart.hideMessage();
            }
        });
    },
    /**
     * processRequest: function(response) Process ajax response result
     * @param jQuery object response
     * @return mixed
     */
    processRequest: function (response) {
        if(response.status && response.status === 200)
        {
// alow connection
            if(response.responseText.length > 0)
            {
                try
                {
                    var json = JSON.parse(response.responseText);
                }
                catch(e)
                {
                    this.getResponseError(response, $('#cart-errors'));
                    return false;
                }
                var string = '<ul>';
                if(json.hasOwnProperty('errors'))
                {
// detecting Cart errors, do the terminate process
                    for(var i = 0; i<json.errors.length; i++)
                    {
                        string += '<li>'+json.errors[i]+'</li>';
                    }
                    string += '</ul>';
// throwing error to #cart-errors
                    this.getMessage(string, $('#cart-errors'));
                }
                else
                {
// return data to the callable function back
                    return json;
                }
            }
            else
            {
                this.getResponseError(response, $('#cart-errors'));
            }
        }
    },
    /**
     * getResponseError: function(response, element) Ajax response error switcher
     * @param jQuery object response
     * @param jQuery object element
     * @return null
     */
    getResponseError: function(response, element) {
        var message = '<ul>';
        message += '<li><b>There was an error with the AJAX request:</b></li>';
        switch (response.statusText) {
            case 'timeout':
                message += '<li>The request timed out.</li>';
                break;
            case 'notmodified':
                message += '<li>The request was not modified but was not retrieved from the cache.</li>';
                break;
            case 'parsererror':
                message += '<li>XML/Json format is bad.</li>';
                break;
            default:
                message += '<li>HTTP Error (' + response.status + ' ' + response.statusText + ').</li>';
        }
        message += '</ul>';
// throwing error message
        this.getMessage(message, element);
    },
    /**
     * getMessage: function(message, element) Show status message
     * @param string message message string
     * @param jQuery object element
     * @return null
     */
    getMessage: function(message, element) {
        if(!element) {
// loading message as default
            var element = $('#cart-loader');
        }
        if(message !== "undefined") {
            element.addClass('active').find('.message').html(message).css({'zIndex' : 9999999});
        }
        else {
            element.addClass('active').find('.message').html('Секундочку ...');
        }
    },
    /**
     * hideMessage: function(params) Hide message
     * @param jquery object element
     * @return null
     */
    hideMessage: function (element) {
        if(!element) {
// loading message as default
            var element = $('#cart-loader');
        }
        element.removeClass('active').find('.message').html('');
    },
    /**
     * isNumber: function(obj, event) Check number value (input number as ex.)
     * @param window.event object event
     * @return boolean
     */
    isNumber: function(event) {
        if(event)
        {
            var charCode = (event.which) ? event.which : event.keyCode;
            if(charCode !== 190 && charCode > 31 &&
                (charCode < 48 || charCode > 57) &&
                (charCode < 96 || charCode > 105) &&
                (charCode < 37 || charCode > 40) &&
                charCode !== 110 && charCode !== 8 && charCode !== 46) {
                return false;
            }
        }
        return true;
    },
    /**
     * onItem : function(obj) show item position under event
     * @param obj jQuery object
     * return null
     */
    onItem : function(obj) {
        var previewObj = obj.attr('class'),
            previewRel = obj.attr('rel'),
            rows = $('[data-row]');
// remove before all selected classes
        $('.'+previewObj).removeClass('click selected');
// check relation tr row for display this by preview
        rows.css("display", "none");
        rows.filter("[data-row='"+previewRel+"']").css("display", "table-row");
// add after one selected
        obj.addClass('click selected');
    },
    /**
     * isValid : function(obj) Checking the number of updates for Multiple add
     * @param obj jQuery object
     * return null
     */
    isValid : function(obj, event) {
        var stockItems = parseInt(obj.data('max')),
            incartItems = parseInt(obj.data('incart')),
            valuesItems = parseInt(obj.val()),
            warning = $('#warning');
        var totalItems = incartItems+valuesItems;
// check max items by size
        if(totalItems > this.config.sameitems)
        {
            obj.val('');
            warning
                .html("Нельзя добавить в корзину более "+this.config.sameitems+" вещей одного размера")
                .fadeIn();
        }
// check items in stock
        if(totalItems > stockItems)
        {
            obj.val('');
            warning
                .html("Заказанное Вами количество превышает имеющееся на складе. Доступно: <b>"+stockItems+"</b>")
                .fadeIn();
        }
    }
};