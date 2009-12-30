/**
 * This file holds javscript functions that are used by the templates in the Theme
 * 
 */
 
 // AJAX FUNCTIONS 
function loadNewPage( el, url ) {
	
	var theEl = $(el);
	var callback = {
		success : function(o) {
			theEl.innerHTML = o.responseText;
		}
	}
	var opt = {
	    // Use POST
	    method: 'post',
	    // Handle successful response
	    onSuccess: callback.success,
	    // Handle 404
	    on404: function(t) {
	        alert('Error 404 ('+ t.statusText + '):\nThe requested URL could not be found!' );
	    }
	}
	new Ajax.Request( url + '&only_page=1', opt );
}

function handleGoToCart() { document.location = 'index.php?option=com_virtuemart&page=shop.cart&product_id=' + formCartAdd.product_id.value ; }

function handleAddToCart( formId, parameters ) {
	formCartAdd = document.getElementById( formId );
	
	var callback = {
		success : function(o) {
			updateMiniCarts();
			dlg = Dialog.confirm(o.responseText, 
				{windowParameters: {className:"mac_os_x", 
									width:440, modal: false,
									showEffect: Element.show }, 
				okLabel: " "+ ok_lbl +" ", 
				cancelLabel: " " + cart_title +" ", 
				buttonClass: "button", 
				id: 'confirmDialog', 
				cancel: handleGoToCart
				});
			dlg.setTitle( notice_lbl );
			setTimeout( 'dlg.hide()', 3000 );
			
		},
		failure : function(o) {
			Dialog.alert( 'Error: connection failed.' , 
							{windowParameters: {className: "mac_os_x", width:440}, 
							okLabel: "Close",
							id: 'failureInfo'
							});
		}
	}
	
	var opt = {
	    // Use POST
	    method: 'post',
	    // Send this lovely data
	    postBody: Form.Methods.serialize( formId ),
	    // Handle successful response
	    onSuccess: callback.success,
	    // Handle 404
	    on404: function(t) {
	        alert('Error 404 ('+ t.statusText + '):\nThe requested URL could not be found!' );
	    },
	    // Handle other errors
	    onFailure: callback.failure
	}

	new Ajax.Request('index2.php?ajax_request=1', opt);
}
/**
* This function searches for all elements with the class name "jmCartModule" and
* updates them with the contents of the page "shop.basket_short" after a cart modification event
*/
function updateMiniCarts() {
		var callbackCart = {  
			success : function(o) {
				carts = document.getElementsByClassName( 'jmCartModule' );
				if( carts ) {
						for (var i=0; i<carts.length; i++){
								carts[i].innerHTML = o.responseText;
								new Effect.Highlight( carts[i] );
						}
				}
			},
			failure : function( hxr ) { alert( hxr.statusText ) }
		}
		option = { method: 'get', onSuccess: callbackCart.success,onFailure: callbackCart.failure }
		new Ajax.Request('index2.php?only_page=1&page=shop.basket_short&option=com_virtuemart', option);
		
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