/**
 * Object Common
 * Управляющий класс
 */
var Common =   {

    /**
     * switch : function(param) Data view switcher
     * @param param null string
     * @return null
     */
    action : function(url, params, callbackId) {
        try {

            global.showStatus('common.loading');

            // send request to server
            var jqxhr = $.post(url, params)

                // success execution

                .success(function(data) {
                    if(!callbackId)
                        global.showStatus('message.success', data.message);
                    else
                    {
                        var el = document.getElementById(callbackId);
                        el.innerHTML = data.message;
                    }
                    global.hideStatus('common.loading');
                })

                // error execution

                .error(function(jqXHR, textStatus) {
                    global.alert('Ошибка выполнения Ajax запроса: '+textStatus);
                    global.hideStatus('common.loading');
                })
        }
        catch(error) {
            global.alert('Возникла ошибка '+error);
        }
    },


    /**
     * redirect : function(param) Filter redirect
     * @param string param
     * @return null
     */
    redirect : function(param) {
        if(!param ) window.location.assign(location.pathname);
        else
        {
            // filter query or path

            param = param.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
            var regex = new RegExp("[\\?&]" + param + "=([^&#]*)"),
                results = regex.exec(location.search);

            if(results === null)    // redirect to direct path
                window.location.assign(param);
            else // redirect to query string
                window.location.assign(location.pathname +'?' +decodeURIComponent(results[1].replace(/\+/g, " ")));
        }
        return;
    },

    /**
     * typeNumber : function(e) onKeydown numeric validation
     * @param event e (onKeydown)
     * @access simulate private
     * return event
     */
    typeNumber : function(e) {
        var theEvent = e || window.event;
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode( key );
        var regex = /[A-Z]|\./;
        if(regex.test(key) )
        {
            theEvent.returnValue = false;
            if(theEvent.preventDefault) theEvent.preventDefault();
        }
    },

    /**
     * isNumber : function(n) Check num value
     * @var int n
     * @access simulate private
     * @return boolean
     */
    isNumber : function(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }
}