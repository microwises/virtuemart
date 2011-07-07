function tabs(pages,cookieName) {
	
	var options = { path: '/', expires: 1};
	var clicked = 0;
	var n = 1;
	
	pages.addClass("dyn-tabs");

	var tabNavigation = jQuery('<ul id="tabs" />').insertBefore(pages.first());

	pages.each(function() {
		var listElement = jQuery("<li />");
		var label = jQuery(this).attr("title") ? jQuery(this).attr("title")
				: "Kein Label";
		listElement.text(label);
		listElement.attr('id', 'tab-'+n);
		tabNavigation.append(listElement);
		n++;
	});

	var items = tabNavigation.find("li");

	// Check if we have Cookie - if Yes => set the right tab as current ELSE set the first item as current
	var isCokkieSetUl = jQuery.cookie(cookieName) ? jQuery('#admin-ui-tabs ul#tabs li#'+jQuery.cookie(cookieName)).addClass("current")
			: jQuery('#admin-ui-tabs ul#tabs li#tab-1').addClass("current");
	var isCokkieSetDiv = jQuery.cookie(cookieName) ? jQuery('#admin-ui-tabs div#'+jQuery.cookie(cookieName)).show()
			: jQuery('#admin-ui-tabs div#tab-1').show();

	items.click(function(e) {
		items.removeClass("current");
		jQuery(this).addClass("current");
		pages.hide();
		pages.eq(jQuery(this).index()).fadeIn(200);
		
		var id = jQuery(this).attr('id');
		
		e.preventDefault();
		clicked++;
		jQuery.cookie(cookieName, id, options);
		
	});
}