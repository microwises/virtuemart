if (typeof jQuery.prototype.closest == "undefined") {
    jQuery.prototype.closest = jQuery.prototype.parents;
}

jQuery(document).ready(function () {
    // unbind click to prevent duplicate click functions in case of more
    // than one pp box
    jQuery('.klarna_PPBox_pull').unbind('click');
    jQuery('.klarna_PPBox_pull').click(function () {
        var kParent = jQuery(this).closest('.klarna_PPBox');
        var kBottom = kParent.find('.klarna_PPBox_bottom');
        if (kBottom.is(':visible')) {
            kBottom.slideUp('fast');
            kParent.find('#klarna_PPBox_pullUp').fadeOut('fast');
            kParent.find('#klarna_PPBox_pullDown').show();
        } else {
            kBottom.slideDown('fast');
            kParent.find('#klarna_PPBox_pullUp').fadeIn('fast');
            kParent.find('#klarna_PPBox_pullDown').hide();
        }
    });

    // unbind click to prevent duplicate click functions in case of more
    // than one pp box
    jQuery('.klarna_PPBox_top').unbind('click');
    jQuery('.klarna_PPBox_top').click(function () {
        var kParent = jQuery(this).closest('.klarna_PPBox');
        var kBottom = kParent.find('.klarna_PPBox_bottom');
        if (kBottom.is(':visible')) {
            kBottom.slideUp('fast');
            kParent.find('#klarna_PPBox_pullUp').fadeOut('fast');
            kParent.find('#klarna_PPBox_pullDown').show();
        } else {
            kBottom.slideDown('fast');
            kParent.find('#klarna_PPBox_pullUp').fadeIn('fast');
            kParent.find('#klarna_PPBox_pullDown').hide();
        }
    });
});
