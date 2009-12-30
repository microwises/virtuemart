/**
 * This file holds javscript functions that are used by the templates in the Theme
 * 
 */
 
 // AJAX FUNCTIONS 
function loadNewPage( el, url ) {
	
	var theEl = $(el);
	var callback = {
		success : function(responseText) {
			theEl.innerHTML = responseText;
			if( Slimbox ) Slimbox.scanPage();
		}
	}
	var opt = {
	    // Use POST
	    method: 'get',
	    // Handle successful response
	    onComplete: callback.success
    }
	new Ajax( url + '&only_page=1', opt ).request();
}

function handleGoToCart() { document.location = live_site + '/index.php?option=com_virtuemart&page=shop.cart&product_id=' + formCartAdd.product_id.value + '&Itemid=' +formCartAdd.Itemid.value; }
var timeoutID = 0;

function handleAddToCart( formId, parameters ) {
	formCartAdd = document.getElementById( formId );
	
	var callback = function(responseText) {
		updateMiniCarts();
		// close an existing mooPrompt box first, before attempting to create a new one (thanks wellsie!)
		if (document.boxB) {
			document.boxB.close();
			clearTimeout(timeoutID);
		}

		document.boxB = new MooPrompt(notice_lbl, responseText, {
				buttons: 2,
				width:400,
				height:150,
				overlay: false,
				button1: ok_lbl,
				button2: cart_title,
				onButton2: 	handleGoToCart
			});
			
		timeoutID = setTimeout( 'document.boxB.close()', 3000 );
	}
	
	var opt = {
	    // Use POST
	    method: 'post',
	    // Send this lovely data
	    data: $(formId),
	    // Handle successful response
	    onComplete: callback,
	    
	    evalScripts: true
	}

	new Ajax(formCartAdd.action, opt).request();
}

function handleGoToFavourites() { document.location = live_site + '/index.php?option=com_virtuemart&page=shop.favourites&product_id=' + formfavouriteAdd.product_id.value + '&Itemid=' +formfavouriteAdd.Itemid.value; }

function handleAddToFavourites( formId, parameters ) {
	formFavouriteAdd = document.getElementById( formId );
	
	var callback = function(responseText) {
		updateMinifavourites();
		// close an existing mooPrompt box first, before attempting to create a new one (thanks wellsie!)
		if (document.boxC) {
			document.boxC.close();
			clearTimeout(timeoutID);
		}

		document.boxC = new MooPrompt(notice_lbl, responseText, {
				buttons: 2,
				width:400,
				height:150,
				overlay: false,
				button1: ok_lbl,
				button2: favs_title,
				onButton2: 	handleGoToFavourites
			});
			
		timeoutID = setTimeout( 'document.boxC.close()', 3000 );
	}
	
	var opt = {
	    // Use POST
	    method: 'post',
	    // Send this lovely data
	    data: $(formId),
	    // Handle successful response
	    onComplete: callback,
	    
	    evalScripts: true
	}

	new Ajax(formFavouriteAdd.action, opt).request();
}
/**
* This function searches for all elements with the class name "jmCartModule" and
* updates them with the contents of the page "shop.basket_short" after a cart modification event
*/
function updateMiniCarts() {
	var callbackCart = function(responseText) {
		carts = $$( '.vmCartModule' );
		if( carts ) {
			try {
				for (var i=0; i<carts.length; i++){
					carts[i].innerHTML = responseText;
		
					try {
						color = carts[i].getStyle( 'color' );
						bgcolor = carts[i].getStyle( 'background-color' );
						if( bgcolor == 'transparent' ) {
							// If the current element has no background color, it is transparent.
							// We can't make a highlight without knowing about the real background color,
							// so let's loop up to the next parent that has a BG Color
							parent = carts[i].getParent();
							while( parent && bgcolor == 'transparent' ) {
								bgcolor = parent.getStyle( 'background-color' );
								parent = parent.getParent();
							}
						}
						var fxc = new Fx.Style(carts[i], 'color', {duration: 1000});
						var fxbgc = new Fx.Style(carts[i], 'background-color', {duration: 1000});

						fxc.start( '#222', color );				
						fxbgc.start( '#fff68f', bgcolor );
						if( parent ) {
							setTimeout( "carts[" + i + "].setStyle( 'background-color', 'transparent' )", 1000 );
						}
					} catch(e) {}
				}
			} catch(e) {}
		}
	}
	var option = { method: 'post', onComplete: callbackCart, data: { only_page:1,page: "shop.basket_short", option: "com_virtuemart" } }
	new Ajax( live_site + '/index2.php', option).request();
} 

/**
* This function searches for all elements with the class name "vmFavouritesModule" and
* updates them with the contents of the page "shop.favourites_short" after a favourites modification event
*/
function updateMiniFavourites() {
	var callbackFavs = function(responseText) {
		favs = $$( '.vmFavouritesModule' );
		if( favs ) {
			try {
				for (var i=0; i<favs.length; i++){
					favs[i].innerHTML = responseText;
		
					try {
						color = favs[i].getStyle( 'color' );
						bgcolor = favs[i].getStyle( 'background-color' );
						if( bgcolor == 'transparent' ) {
							// If the current element has no background color, it is transparent.
							// We can't make a highlight without knowing about the real background color,
							// so let's loop up to the next parent that has a BG Color
							parent = favs[i].getParent();
							while( parent && bgcolor == 'transparent' ) {
								bgcolor = parent.getStyle( 'background-color' );
								parent = parent.getParent();
							}
						}
						var fxc = new Fx.Style(favs[i], 'color', {duration: 1000});
						var fxbgc = new Fx.Style(favs[i], 'background-color', {duration: 1000});

						fxc.start( '#222', color );				
						fxbgc.start( '#fff68f', bgcolor );
						if( parent ) {
							setTimeout( "favs[" + i + "].setStyle( 'background-color', 'transparent' )", 1000 );
						}
					} catch(e) {}
				}
			} catch(e) {}
		}
	}
	var option = { method: 'post', onComplete: callbackFavs, data: { only_page:1,page: "shop.favourites_short", option: "com_virtuemart" } }
	new Ajax( live_site + '/index2.php', option).request();
} 
/**
* This function allows you to present contents of a URL in a really nice stylish dhtml Window
* It uses the WindowJS, so make sure you have called
* vmCommonHTML::loadWindowsJS();
* before
*/
function fancyPop( url, parameters ) {
	
	parameters = parameters || {};
	popTitle = parameters.title || '';
	popWidth = parameters.width || 700;
	popHeight = parameters.height || 600;
	popModal = parameters.modal || false;
	
	window_id = new Window('window_id', {className: "mac_os_x", 
										title: popTitle,
										showEffect: Element.show,
										hideEffect: Element.hide,
										width: popWidth, height: popHeight}); 
	window_id.setAjaxContent( url, {evalScripts:true}, true, popModal );
	window_id.setCookie('window_size');
	window_id.setDestroyOnClose();
}