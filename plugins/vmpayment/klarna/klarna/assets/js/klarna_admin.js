jQuery(document).ready(function() {
        setTimeout('showCountries()', 10);
        flagListener();
        setTimeout('setExtraInfo()', 10);
        setTimeout('showPclasses()', 10);
});

function showCountries() {
        jQuery('select[name=KLARNA_SELECTED_COUNTRIES[]] option:selected').each(function() {
                var value = jQuery(this).attr('value');
                var code = convert(value);
                var field = 'fieldset#'+code+'_settings';
                jQuery(field).removeClass('hide');
                jQuery('img#'+code).removeClass('inactive');
        });
}

// The ExtraInfo box in the backend empties itself every time you enter the
// backend if it contains php code, this function puts it back.
function setExtraInfo() {
        var einfo = "<"+"?php include(JPATH_SITE . '/components/com_klarna/extrainfo.php'); "+"?"+">";
        var current = jQuery('textarea').attr('name', 'payment_extrainfo').text();
        if (current.search('JPATH_SITE') <0 ) {
                jQuery('textarea').attr('name','payment_extrainfo').text(einfo+current);
        }
}

function showPclasses() {
    jQuery('fieldset#klarna_pclasses legend').click(function(){
        var field = jQuery(this).parent().find('#pclasses');
        var img_path = '../components/com_klarna/images/';
        if (jQuery(field).is(':visible')) {
            jQuery(this).parent().find('#pclasses').slideToggle("fast", function() {
                jQuery(this).parent().find('#arrow').html('<img src="'+img_path+'expand_arrow.png" />');
            });
        } else {
            jQuery(this).parent().find('#pclasses').slideToggle("fast", function() {
                jQuery(this).parent().find('#arrow').html('<img src="'+img_path+'collapse_arrow.png" />');
            });
        }
    });
}

function convert(country) {
        switch(country) {
                case "SWE":
                        return "SE";
                case "NOR":
                        return "NO";
                case "DNK":
                        return "DK";
                case "FIN":
                        return "FI";
                case "NLD":
                        return "NL";
                case "DEU":
                        return "DE";
                default:
                        return null; // not supported by Klarna yet
        }
}

function convert_twoletter(country) {
        switch(country) {
                case "SE":
                        return "SWE";
                case "NO":
                        return "NOR";
                case "DK":
                        return "DNK";
                case "FI":
                        return "FIN";
                case "NL":
                        return "NLD";
                case "DE":
                        return "DEU";
                default:
                        return null; // not supported by Klarna yet
        }
}


function flagListener() {
        jQuery('#klarna_countries').find('span').click(function() {
                var code = jQuery(this).attr('id');
                toggleActive(code);
        });
}

function toggleActive(code) {
        var field = 'fieldset#'+code+'_settings';
        var tlc = convert_twoletter(code);
        if (jQuery(field).is(':hidden')) {
                jQuery(field).removeClass('hide');
                jQuery('img#'+code).removeClass('inactive').addClass('active');
                jQuery('span#'+code).removeClass('inactive').addClass('active');
                jQuery('#KLARNA_SELECTED_COUNTRIES > option[value='+tlc+']').attr('selected', 'selected');
        } else if (jQuery(field).is(':visible')) {
                jQuery(field).addClass('hide');
                jQuery('img#'+code).addClass('inactive').removeClass('active');
                jQuery('span#'+code).addClass('inactive').removeClass('active');
                jQuery('#KLARNA_SELECTED_COUNTRIES > option[value='+tlc+']').removeAttr('selected');
        }
}
