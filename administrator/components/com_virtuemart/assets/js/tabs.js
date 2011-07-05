
function tabs(pages) {

	pages.addClass("dyn-tabs");
	pages.first().show();

	var tabNavigation = jQuery('<ul id="tabs" />').insertBefore(pages.first());

	pages.each(function() {
		var listElement = jQuery("<li />");
		var label = jQuery(this).attr("title") ? jQuery(this).attr("title")
				: "Kein Label";
		listElement.text(label);
		tabNavigation.append(listElement);
	});

	var items = tabNavigation.find("li");
	items.first().addClass("current");

	
	var adminmenu = jQuery('.menu-wrapper');
	var admincontent = jQuery('#admin-content-wrapper');
	//adminmenu.css({'min-height': '500px',});
	//admincontent.css({'min-height': '500px',});
	
	if(adminmenu.height() < admincontent.height()) {
		adminmenu.css({'height': admincontent.height()+'px',});
	} else {
		admincontent.css({'height': adminmenu.height()+'px',});
	}
	
	items.click(function() {
		adminmenu.css({'height': '0px',});
		//admincontent.css({'height': '0px',});
		
		items.removeClass("current");
		jQuery(this).addClass("current");
		pages.hide();
		pages.eq(jQuery(this).index()).fadeIn(200);

		if(adminmenu.height() < admincontent.height()) {
			adminmenu.css({'height': admincontent.height()+'px',});
		}

	});

}

jQuery(document).ready(function() {

	tabs(jQuery("#admin-ui-tabs .tabs"));

});
