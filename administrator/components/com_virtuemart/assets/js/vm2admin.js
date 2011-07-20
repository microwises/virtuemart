(function($) {

  var methods = {
	tabs : function (cookie) {
		if ($.cookie(cookie) == null) var idx=0;
		else var idx = $.cookie(cookie);
		if (idx == null) idx=0;
		console.log(cookie+' index defaut:'+idx);
		var options = { path: '/', expires: 2},
			list = '<ul id="tabs">';
		this.find('div.tabs').each(
			function(i) { 
				list += '<li style="display:inline;">'+ $(this).attr('title')+'</li>' ;
			}
		);
		this.prepend(list+'</ul>');
		this.children('div').hide();
		// select & open menu
		var li = $('#tabs li'),
			div = this.children('div');
		li.eq(idx).addClass('current');
		div.eq(idx).slideDown(1000);

		li.click(
			function () {
				if ($(this).not("current")){
					var idx = li.index(this);
					if (cookie !== "" ) $.cookie(cookie, idx, options);
					console.log(cookie+' index:'+idx);
					oldIndex = $(this).addClass("current").siblings('li.current').removeClass("current").index();
					div.eq(idx).slideDown();
					div.eq( oldIndex ).slideUp();
				}
			}
		);
	},
    accordeon :  function () {
		var idx = $.cookie('accordeon'),
			options = { path: '/', expires: 2},
			div = this.children('div') ,
			h3 = this.children('h3') ;
		if (idx == null) idx=0;
		div.hide();
		h3.eq( idx ).addClass('current');
		div.eq(idx ).show();

		h3.click(
			function () {
				var menu = $(this) ;
				if (menu.not("current")){
					menu.siblings('h3.current').removeClass("current").next().slideUp(200);
					menu.addClass("current").next().slideDown(200);
					$.cookie('accordeon', h3.index(this), options);
				}
			}
		);
	}
  };

	$.fn.vm2admin = function( method ) {
		
		if ( methods[method] ) {
		  return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on Vm2 admin jQuery library' );
		}    
	  
	};

})(jQuery);

jQuery(document).ready( function() {

	jQuery('#admin-ui-menu').vm2admin('accordeon');

});

 