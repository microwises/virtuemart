
function bandoneon(content,bar) {
	if (!content.length && !bar.length) return;
	
	var adminmenu = 'admin-menu';
	var options = { path: '/', expires: 2};
	var clicked = 0;
	
	//content.hide();
	jQuery('#admin-ui-menu div').hide();
	jQuery('#admin-ui-menu h3#'+jQuery.cookie(adminmenu)).addClass('current');
	jQuery('#admin-ui-menu div#'+jQuery.cookie(adminmenu)).show();

	bar.click( function(e) {

		bar.removeClass("current");
		content.not(":hidden").slideUp(200);
		jQuery(this).next().not(":visible").slideDown(200).prev().addClass("current");
		var current = jQuery(this);
		jQuery(this).next().not(":visible").slideDown(200,function() {
			current.addClass("current");
		});
		
		var id = jQuery(this).attr('id');
		
		e.preventDefault();
		clicked++;
		jQuery.cookie(adminmenu, id, options);
		
	});
}

jQuery(document).ready( function() {

	bandoneon(jQuery(".menu-list"),jQuery(".menu-title"));

});

