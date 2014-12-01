
//"use strict";
/**
 * /**
 * cart.js API Для работы корзины
 * @author Stanislav WEB
 * @date 01.10.2014
 * @type {{config: {dataType: string, method: string, timeout: number, async: boolean, cache: boolean, delay: number, limitmax: number, sameitems: number}, response: boolean, lookupId: boolean, get: Function, add: Function, addBefore: Function, delete: Function, recount: Function, sendRequest: Function, processRequest: Function, getResponseError: Function, getMessage: Function, hideMessage: Function, isNumber: Function, onItem: Function, isValid: Function}}
 */
var Cart = {

    debug   :   true,

    hash    :   false,

    /**
     * Настройки клиента Cart
     * @var object config
     */
    config: {
        dataType    : 'json',   // тип ответа (JSON рекомендовано)
        method      : 'POST',   // метод передачи
        timeout     : 10000,    // тайм аут ожидания ответа от сервера
        async       : true,     // асинхронный режим
        cache       : false,    // кеширование результата
        delay       : 100,     // задержка в мил.с при обновлении корзины

        // Настройки Server Side контроллера
        
        action      :   {
            set     :   '/customer/cart/set',
            get     :   '/customer/cart/get',
            delete  :   '/customer/cart/delete'
        }
    },

    /**
     * Ответ от сервера
     * @var object mixed JSON | STRING
     */
    response : false,

    /**
     * Timeout Id для setTimeout() при обновлении
     * @var int timeoutId
     */
    lookupId : false,

    /**
     * getMessage: function(message, element) Окно с информацией
     * @param string message собщение
     * @example
     *      this.getMessage({title : 'Ошибка', body : 'Переданны не верные данные при добавлении', class : 'error'});
     *      this.getMessage({title : 'Супер', body : 'Добавлен товар', class : 'success'});
     *      this.getMessage({message : 'Вы забыли выбрать размер', class : 'empty'}, object.find('.sizes'))
     *      this.getMessage(); // Секундочку....
     * @param jQuery object объект в котором выводиться сообщение
     * @return null
     */
    getMessage: function(message, element) {


        if(!element)    // по умолчанию
            var element = $('#cartNotify');

        // если переданно обьектом
        if(message instanceof Object) {

            if(message.message)
            {
                element.addClass(message.class);
                element.find('.message').html(message.message);
                return;
            }
            else
            {
                element.addClass('active '+message.class).css({'zIndex' : 9999999});
                element.find('.message > .title').html(message.title);
                element.find('.message > .body').html(message.body);
                return;
            }
        }
        else
        {
            if(!message)
                message =   'Секундочку...';

            element.addClass('active loading').find('.message').html(message);
        }
    },

    /**
     * clearMessage: function(object, className) Закрытие указанного окна
     * @param jquery object объект с окном
     * @param string className имя класса для удаления
     * @example
     *      this.clearMessage($('#cartNotify'), 'active loading');
     * @param jQuery object объект в котором выводиться сообщение
     * @throw new Error exception
     * @return null
     */
    clearMessage: function(object, className) {

        try
        {
            if(object instanceof jQuery)
                object.removeClass(className);
            else
                throw new Error("Только для объектов jQuery");
        }
        catch(e) {
            this.getMessage({title : 'Ошибка', body : 'Переданны не верные данные при добавлении: '+ e.name +' : '+ e.message, class : 'error'});
            return false;
        }
    },

    /**
     * sendRequest: function(uri, params) Отправка данных на сервер
     * @param string uri
     * @param string query
     * @return object Ajax ready state
     */
    sendRequest: function (uri, params) {

        return $.ajax({
            url         : uri,
            data        : params,
            type        : Cart.config.method,
            dataType    : Cart.config.dataType,
            timeout     : Cart.config.timeout,
            async       : Cart.config.async,
            cache       : Cart.config.cache
        });
    },

    /**
     * processRequest: function(response) Обработчик ответов сервера
     * @param jQuery object response
     * @return mixed
     */
    processRequest: function (response) {

        if(response)
        {
            if(response.status && response.status === 200)
            {
                if(response.responseText.length > 0)
                {
                    try
                    {
                        var json = JSON.parse(response.responseText);
                    }
                    catch(e)
                    {
                        this.getResponseError(response, $('#cartNotify'));
                        return false;
                    }
                    var string = '<ul>';
                    if(json.hasOwnProperty('errors'))
                    {
                        for(var i = 0; i<json.errors.length; i++)
                        {
                            string += '<li>'+json.errors[i]+'</li>';
                        }
                        string += '</ul>';
                        this.getMessage(string, $('#cartNotify'));
                    }
                    else
                    {
                        // ошибок сервера нет, парсим контейнер ответа
                        if(this.debug) console.info('Корректный ответ сервера', json.message);

                        // тут ошибка пользователя
                        if(json.message)
                            return this.getMessage(json.message);

                        return json;
                    }
                }
                else
                    this.getResponseError(response, $('#cart-errors'));
            }
        }
    },

    /**
     * getResponseError: function(response, element) Обработчки ошибок сервера
     * @param jQuery object response
     * @param jQuery object element
     * @return null
     */
    getResponseError: function(response, element) {
        if (typeof response == 'string' || response instanceof String)
            var message = response;
        else
        {
            var message = '';

            switch(response.statusText) {
                case 'timeout':
                    message = 'The request timed out.';
                    break;
                case 'notmodified':
                    message = 'The request was not modified but was not retrieved from the cache.';
                    break;
                case 'parsererror':
                    message = 'XML/Json format is bad.';
                    break;
                default:
                    message = 'HTTP Error (' + response.status + ' ' + response.statusText + ')';
            }
        }

        // throwing error message
        this.getMessage(message, element);
    },

    /**
     * addBefore: function(object) Проверка перед отправкой на сервер
     * используется для валидации параметров при добавлении
     *
     * @var jQuery object
     * @throw new Error exception
     * @return serialized to array object
     */
    addBefore : function(object)
    {
        if(this.debug) console.info('Форма:', object);

        try
        {
            if(object instanceof jQuery)
            {
                var data        =   object.serializeArray(),
                    quantity    =   '',
                    sizes       =   [];

                if(this.debug) console.info('Подготовленные данные:', data);

                for(i in data)
                {
                    if(data[i].name.indexOf("size") == 0)
                        sizes.push(data[i].value);
                    if(data[i].name == 'quantity' && data[i].value == 'multiple')
                        quantity    =   data[i].value;
                }

                // ошибка, если не выбран размер у товара где есть выбор размеров
                if(quantity === 'multiple' && sizes.length < 1)
                    this.getMessage({message : 'Вы забыли выбрать размер', class : 'empty'}, object.find('.sizes'));
                else
                {
                    this.clearMessage(object.find('.sizes'), 'empty');

                    // создаю запрос на сервер
                    this.set(object);
                }
            }
            else
                throw new Error("Только для объектов jQuery");
        }
        catch(e) {
            this.getMessage({title : 'Ошибка', body : 'Переданны не верные данные при добавлении: '+ e.name +' : '+ e.message, class : 'error'});
            return false;
        }
    },

    /**
     * set: function(item) Добавление в корзину
     * @var string itemParams Cart container (product)
     * @return html
     */
    set: function(item) {

        try {

            // параметры передачи

            var then    =   this;

            if(this.debug) console.info('Отправка на сервер:', item.serialize());

            then.hash   =   Math.floor((Math.random() * 100000000000) + 1);
            then.response = Cart.sendRequest(
                then.config.action.set+'?hash='+then.hash,
                item.serialize()
            );


            // получаю ответ от сервера
            $(document).ajaxStop($.proxy(function() {

                var response = then.response,
                    data = then.processRequest(response);

                if(then.hash == data.hash)
                {
                    if(then.debug) console.info('Получен ответ:', data);
                    $('#'+data.mode).html(data.cart).show();

                    global.positionate($('#'+data.mode)[0]);
                }

                delete(data);
                delete(response);
                delete(this.response);

            }, this));
        }
        catch(e) {
            this.getMessage({title : 'Ошибка', body : 'Переданны не верные данные при добавлении: '+ e.name +' : '+ e.message, class : 'error'});
            return false;
        }
    },












    /**
     * get: function() Load cart templates with recalculation
     * @return null
     */
    get: function() {
// Set -> request to server
        var requestData = {
            'hash' : Math.floor((Math.random() * 100000000000) + 1)
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
     * addBefore: function(item_id) Show preview to Cart
     * @var string item_id Cart container (item_id)
     * @return html
     */
    addBefore2: function(itemParams) {
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