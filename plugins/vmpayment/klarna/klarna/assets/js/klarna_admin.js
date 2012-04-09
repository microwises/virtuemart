jQuery(function($){
	setTimeout('showCountries()', 10);
	flagListener();
	//setTimeout('setExtraInfo()', 10);
	setTimeout('showPclasses()', 10);

	$(".update_pclasses a").click( function(e){
		e.preventDefault();
		form = $(this).parents("form") ;

		var link = $(this).attr("href");
		var datas = $(this).parents("form").serializeArray();
			datas.push({"name":"redirect","value":"no"});
			datas.push({"name":"task","value":"save"});
		$.post(link,datas,function(data) {
			if (data = "ok") {
				console.log("update table");
				datas.push({"name":"view","value":"plugin"});
				datas.push({"name":"name","value":"klarna"});
				datas.push({"name":"task","value":"plugin"});
				$.getJSON(link , datas,function(update) {
					// update json array msg,notice,pclasses
					
					$('#PClassesSuccessResult').hide().html(update.msg+'</br>'+update.notice).slideToggle(1000).delay(2000).slideToggle(500);
					//console.log("update pclasse");
					$('#pclasses').html(update.pclasses);
				});
			}
		});
		return false;
	});

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
    jQuery('#pclass_field').click(function(){
		var pclass_field=jQuery(this);
        jQuery('#pclasses').slideToggle("fast", function() {
			if (pclass_field.find('span').hasClass('expand_arrow'))
				pclass_field.find('span').addClass('collapse_arrow').removeClass('expand_arrow')
			else 
				pclass_field.find('span').addClass('expand_arrow').removeClass('collapse_arrow')
               // jQuery(this).parent().find('#arrow').html('<img src="'+img_path+'expand_arrow.png" />');
            });
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
