$(document).ready(function(){
    $('#panelTab a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    });
    $('#panelTab a:first').tab('show');
    $(".bg-config").hide();
    var $MAINCONTAINER = $("html");

    /**
     * BACKGROUND-IMAGE SELECTION
     */
    $(".background-images").each(function() {
        var $parent = this;
        var $input = $(".input-setting", $parent);
        $(".bi-wrapper > div", this).click(function() {
            $input.val($(this).data('val'));
            $(".bg-config",$parent).show();
            $('.bi-wrapper > div', $parent).removeClass('active');
            $(this).addClass('active');

            if ($input.data('selector')) {
                $($input.data('selector'), $($MAINCONTAINER)).css($input.data('attrs'), 'url(' + $(this).data('image') + ')');
            }
        });
        $(".bg-config select", this).change(function(){
            if ($input.data('selector')) {
                $($input.data('selector'), $($MAINCONTAINER)).css($(this).data('attrs'), $(this).val());
            }
        });
    });

    $(".clear-bg").click(function() {
        var $parent = $(this).parent();
        var $input = $(".input-setting", $parent);
        if ($input.val('')) {
            
            if ($parent.hasClass("background-images")) {
                $('.bi-wrapper > div', $parent).removeClass('active');
                $($input.data('selector'), $($MAINCONTAINER)).css($input.data('attrs'), 'none');
                $('ul select', $parent).each(function(){
                    $($input.data('selector'), $($MAINCONTAINER)).css($(this).data('attrs'), '');
                });
                $('ul.bg-config', $parent).hide();
                $('ul select', $parent).val("");
            } else {
                $input.attr('style', '')
            }
            $($input.data('selector'), $($MAINCONTAINER)).css($input.data('attrs'), 'inherit');

        }
        $input.val('');

        return false;
    });

    $('.accordion-group input.input-setting').each(function() {
        var input = this;
        $(input).attr('readonly', 'readonly');
        $(input).ColorPicker({
            onChange: function(hsb, hex, rgb) {
                $(input).css('backgroundColor', '#' + hex);
                $(input).val(hex);
                if ($(input).data('selector')) {
                    $($MAINCONTAINER).find($(input).data('selector')).css($(input).data('attrs'), "#" + $(input).val())
                }
            }
        });
    });

    $('.accordion-group select.input-setting').change(function() {
        var input = this;
        if ($(input).data('selector')) {
            var ex = $(input).data('attrs') == 'font-size' ? 'px' : "";
            $($MAINCONTAINER).find($(input).data('selector')).css($(input).data('attrs'), $(input).val() + ex);
        }
    });
    $(".paneltool .panelbutton").click(function() {
        $(this).parent().toggleClass("active");
    });
});