
"use strict";
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

    timeout :   5000,

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
        delay       : 15000,     // задержка в мил.с при обновлении корзины

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

                        if(json.message) // тут ошибка пользователя
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
     * addBefore: function(object) Проверка размеров перед отправкой на сервер
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
                    sizes       =   [],
                    check       =   [],
                    then        =   this;

                setTimeout(function() {

                    if(then.debug) console.info('Подготовленные данные:', data);

                    for(var i in data)
                    {
                        if(data[i].name.indexOf("size") == 0)
                        {
                            check.push(data[i].name);
                            sizes.push(data[i].value);
                        }
                        if(data[i].name == 'quantity' && data[i].value == 'multiple')
                            quantity    =   data[i].value;

                        if(data[i].name ==  'issue')
                            var issue   =   data[i].value;
                    }

                    // ошибка, если не выбран размер у товара где есть выбор размеров
                    if(quantity === 'multiple' && sizes.length < 1)
                        then.getMessage({message : 'Вы забыли выбрать размер', class : 'empty'}, object.find('.sizes'));
                    else
                    {
                        then.clearMessage(object.find('.sizes'), 'empty');

                        // создаю запрос на сервер если выбран размер или отправлен нулевой

                        if(check.length > 0)
                            then.set(object, check);
                        else
                            then.getMessage({title : 'Добавление', body : 'Товар уже находиться у вас в корзине', class : 'success'});
                    }
                }, this.delay);
            }
            else
                this.getMessage({title : 'Ошибка', body : 'Только для объектов jQuery', class : 'error'});
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
    set: function(item, checked) {
        try {
            // параметры передачи

            var then    =   this;

            if(item) {
                if(item instanceof jQuery)
                    var data =  item.serialize();
                else
                    var data    =   $.param(item);
            }
            else
            {
                if(this.debug) console.info('Чтение корзины');
                var data    =   {mode : 'small-cart', 'action' : 'read'};
            }
            if(then.debug) console.info('Отправка на сервер:', decodeURIComponent(data));

            then.hash   =   Math.floor((Math.random() * 100000000000) + 1);

            then.response = Cart.sendRequest(
                then.config.action.set+'?hash='+then.hash,
                data
            );

            // получаю ответ от сервера
            return $(document).ajaxStop($.proxy(function() {

                try {
                    var response = then.response;
                        var data = then.processRequest(response);

                    if(data && data.hasOwnProperty('hash') == true)
                        if(then.hash == data.hash)
                        {
                            if(then.debug) if(data) { console.info('Получен ответ:', data); }
                            if(item && item.length) {

                                var rel = item.data("send");

                                // отключаю инпуты с размерами

                                if(checked)
                                    checked.forEach(function(k) {

                                        $("input.s"+rel+"[name='"+k+"']").attr('disabled', 'disabled');
                                    });

                                // увеличиваю счетчик товара

                                if(typeof rel !== typeof undefined && rel !== false) {

                                    var counter = $('div[rel='+rel+']');
                                    if(isNaN(counter.html())) counter.addClass('on').html(1);
                                    else
                                        counter.html(parseInt(counter.html())+1);
                                }
                            }
                            $('#'+data.mode).html(data.cart).show();
                            global.positionate($('#'+data.mode)[0]);

                            if(data.minicart)
                            {
                                if(data.minicart.total > 0)
                                {
                                    $('#mini-cart').removeClass('hidden');
                                    if(this.debug) console.info('Чтение миникорзины:', data.minicart);
                                    $('span[rel=total]').html(then.declOfNum(data.minicart.total, ['вещь', 'вещи', 'вещей']));
                                    if(data.minicart.sum > 0) $('span[rel=sum]').html(then.numberFormat(data.minicart.sum));
                                    if(data.minicart.shop_discounts.discount_sum > 0) $('span[rel=discount_sum]').html(then.numberFormat(data.minicart.shop_discounts.discount_sum));
                                }
                            }
                            else
                                $('#mini-cart').addClass('hidden');
                        }

                    response = null;
                    if(typeof(data) !== "undefined" && data !== null)
                    {
                        if(typeof(data.minicart) !== "undefined" && data.minicart !== null )
                            delete(data.minicart);

                        if(typeof(data.hash) !== "undefined" && data.hash !== null )
                            delete(data.hash);

                        if(typeof(data.mode) !== "undefined" && data.mode !== null )
                            delete(data.mode);
                    }

                    if(typeof(then.response) !== "undefined" && then.response !== null )
                        delete(then.response);
                }
                catch(e) {
                    this.getMessage({title : 'Ошибка', body : 'Получены не валидные данные '+ e.name +' : '+ e.message, class : 'error'});
                    return false;
                }
            }, then));
            return false;
        }
        catch(e) {
            this.getMessage({title : 'Ошибка', body : 'Переданны не верные данные при добавлении: '+ e.name +' : '+ e.message, class : 'error'});
            return false;
        }
    },

    /**
     * Склонение числительных
     * @param number
     * @param titles
     * @returns {*}
     */
    declOfNum : function (number, titles) {
        var cases = [2, 0, 1, 1, 1, 2];
        return number + ' '+titles[ (number%100>4 && number%100<20)? 2 : cases[(number%10<5)?number%10:5] ];
    },

    /**
     * Форматирование цены
     * @param n
     * @returns {*}
     */
    numberFormat : function(n) {

        /**
         * Number.prototype.format(n, x, s, c)
         *
         * @param integer n: length of decimal
         * @param integer x: length of whole part
         * @param mixed   s: sections delimiter
         * @param mixed   c: decimal delimiter
         */
        Number.prototype.format = function(n, x, s, c) {
            var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
                num = this.toFixed(Math.max(0, ~~n));

            return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
        }
        return n.format(0, 3, ' ', ' ');
    },

    /**
     * isNumber: function(e) Check number value (input number as ex.)
     * @param window.event object event
     * @return boolean
     */
    isNumber: function(e, obj) {
        if(e) {
            if(this.debug) console.info('Ввод количества: '+event);

            var a = [];
            var k = e.which;

            for (var i = 48; i < 58; i++)
                a.push(i);

            if (!(a.indexOf(k)>=0))
                e.preventDefault();
        }
        return true;
    },

    /**
     * isValid : function(obj) Checking the number of updates for Multiple add
     * @param obj jQuery object
     * return null
     */
    isValid : function(event, obj) {
        var limitone = parseInt(obj.data('limitone')),
            limitmax = parseInt(obj.data('limitmax')),
            storage = parseInt(obj.data('storage')),
            valuesItems = parseInt(obj.val()),
            warning = $('#warning');

        var cart = 0;

        $('.itemsRange').each(function(){
            cart += Number($(this).val())
        });

        if(this.debug)
        {
            console.info('Максимально допустимо в корзине: '+limitmax);
            console.info('Максимально допустимо размера: '+limitone);
            console.info('Размеров на складе: '+storage);
            console.info('В корзине: '+cart);
            console.info('В текущем размере: '+valuesItems);
        }

        // проверка на количество размеров
        if(valuesItems > storage)
        {
            obj.val(valuesItems-1);
            obj.closest("td").find(".cart-bubble")
                .html("такого количества размеров не достаточно на нашем складе")
                .fadeIn().fadeOut(this.timeout);
            return false;
        }

        // проверка на количество размеров
        if(valuesItems > limitone)
        {
            obj.val(valuesItems-1);
            obj.closest("td").find(".cart-bubble")
                .html("Нельзя добавить в корзину более "+limitone+" вещей одного размера")
                .fadeIn().fadeOut(this.timeout);
            return false;
        }

        // проверка на количество в корзине
        if(cart > limitmax)
        {
            obj.val(valuesItems-1);
            obj.closest("td").find(".cart-bubble")
                .html("Нельзя добавить в корзину более "+limitmax+" вещей")
                .fadeIn().fadeOut(this.timeout);
            return false;
        }

        // передаю на сервер
        setTimeout(this.set(obj.closest('form')), this.timeout);

        return false;
    },

    /**
     * remove : function(obj) Удаление из корзины
     * @param obj jQuery object
     * return null
     */
    remove : function(obj) {
        // передаю на сервер
        this.set(obj);
    },

    /**
     * check : function(product_id) Разблокировка кнопки "Добавить в Корзину"
     * @param obj jQuery object
     * return null
     */
    check : function(product_id) {
        if($('input.s'+product_id+':checked').length > 0)
            $('#s'+product_id).removeAttr('disabled');
        else
            $('#s'+product_id).attr('disabled', 'disabled');
    }
};