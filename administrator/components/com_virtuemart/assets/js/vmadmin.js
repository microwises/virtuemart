/**
 * this.js: General Javascript Library for VirtueMart Administration
 *
 *
 * @package	VirtueMart
 * @subpackage Javascript Library
 * @author jseros
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

 jQuery.noConflict();

	 jQuery(document).ready(function(){
		 VM.buildMenu();
		 VM.handleShowElements('click');

	jQuery('a.orderDown').click( function() {
		orderingUpDown( 1,jQuery(this));
	});

	jQuery('a.orderUp').click( function() {
		orderingUpDown(-1,jQuery(this));
	});


 });


(function($){
	// Quick and dirty way of testing if J1.5 or not
	var isJ15 = (MooTools.version.substr(0,3) == '1.1') ? true : false;
	var isJ15Up = (MooTools.version.substr(0,3) == '1.2') ? true : false;

	/**
	 * Local variable and property attached to global object
	 *
	 * @author jseros
	 */
	VM.add({

		/**
		 * Create admin accordion menu
		 *
		 * TODO: Standalone cookie storage
		 *
		 * @author jseros
		 */
		buildMenu: function(){

			if(isJ15){
				var cMAITC = parseInt( Cookie.get( VMConfig.get('menuActiveItemCookie') ) );
			} else {
				var cMAITC = parseInt( Cookie.read( VMConfig.get('menuActiveItemCookie') ) );
			}

			var that = this,
			actualItem = VMCache.set('activeMenuAdminItem ',cMAITC) , // Current selected item
			speed = VMConfig.get('menuAdminSpeed'),
			actualItemNode = $('#menu-toggler-'+ (actualItem || 1)); //shortcut. Performance issue!

			//set current state when document loads

		//	if (isJ15) {
		//		var actualItem = VMCache.set('activeMenuAdminItem ', parseInt( Cookie.get( VMConfig.get('menuActiveItemCookie') ) ) ); // Current selected item
		//	} else {
		//		var actualItem = VMCache.set('activeMenuAdminItem ', parseInt( Cookie.read( VMConfig.get('menuActiveItemCookie') ) ) ); // Current selected item
		//	}

		//	actualItemNode = jQuery('#menu-toggler-'+ (actualItem || 1)); //shortcut. Performance issue!

			//set current state when document loads
			if (isJ15) {
				this.showMenu(false, Cookie.get( VMConfig.get('menuStateCookie') ));
			} else {
				this.showMenu(false, Cookie.read( VMConfig.get('menuStateCookie') ));
			}


			$( VMConfig.get('menuContentSelector') ).addClass( VMConfig.get('hiddenElementClass') );//Hidding Panels
			actualItemNode.next().slideDown( speed );
			that.activeMenuItem( actualItemNode.get(0) );

			$( VMConfig.get('menuTitleSelector') ).click(function(){
				that.activeMenuItem( this );
			});

			this.setMenuClose();

			return this;
		},


		/**
		 * set the active menu item
		 *
		 * TODO: Standalone cookie storage
		 * @author jseros
		 *
		 * @param HTMLElement Toggler node
		 */
		activeMenuItem: function( toggler ){
			var ai = parseInt( VMCache.get('activeMenuAdminItem')), //shortcut. Performance issue!
			voir = parseInt( $( toggler ).addClass( VMConfig.get('menuActiveClass') ).attr('rel') ), //this Item
			speed = VMConfig.get('menuAdminSpeed'); //shortcut. Performance issue!

			if( ai !== voir ){
				$( toggler ).next().slideDown( speed );

				if(ai){
					$('#menu-toggler-'+ ai).removeClass( VMConfig.get('menuActiveClass') ).next().slideUp( speed );
				}

				if (isJ15) {
					Cookie.set( VMConfig.get('menuActiveItemCookie'), voir);
				} else {
					Cookie.write( VMConfig.get('menuActiveItemCookie'), voir);
				}
				VMCache.set('activeMenuAdminItem', voir);
			}

			return this;
		},


		/**
		 * Set the menu close action
		 *
		 * @author jseros
		 *
		 * @param bool with animation?
		 */
		setMenuClose: function(){
			var closerSel = VMConfig.get('menuCloserSelector'),
			that = this;

			$(closerSel).click(function(){
				that.showMenu(true);
				return false;
			});

			return this;
		},


		/**
		 * Set the menu close action
		 *
		 * TODO: Standalone cookie storage
		 * @author jseros
		 *
		 * @param bool with animation?
		 * @param bool only hide it
		 */
		showMenu: function(animation, hide){
			var leftPanel = $( VMConfig.get('layoutLeftSelector') ),
			rightPanel = $( VMConfig.get('layoutRightSelector') ),
			closerSel = VMConfig.get('menuCloserSelector');

			if(typeof hide !== 'undefined'){
				if(hide){
					leftPanel.css('width', '0px');
					$(closerSel).addClass( VMConfig.get('menuHandlerClass') );
				}
				return true;
			}

			if (isJ15) {
				var menuStateCookie = Cookie.get( VMConfig.get('menuStateCookie') );
			} else {
				var menuStateCookie = Cookie.read( VMConfig.get('menuStateCookie') );
			}

			if( !menuStateCookie ){
				if(animation){
					leftPanel.animate({ width: '0', opacity: 0}, VMConfig.get('menuToggleSpeed') );
				}
				else{
					leftPanel.css('width', '0px');
				}

				$(closerSel).addClass( VMConfig.get('menuHandlerClass') );
				if (isJ15) {
					Cookie.set( VMConfig.get('menuStateCookie') , 1);
				} else {
					Cookie.write( VMConfig.get('menuStateCookie') , 1);
				}
			}
			else{
				if(animation){
					leftPanel.animate({ width: '200px', opacity: 1}, VMConfig.get('menuToggleSpeed') );
				}
				else{
					leftPanel.css('width', '200px');
				}

				$(closerSel).removeClass( VMConfig.get('menuHandlerClass') );
				if (isJ15Up) {
					Cookie.dispose( VMConfig.get('menuStateCookie') );
				} else {
					Cookie.remove( VMConfig.get('menuStateCookie') );
				}
			}

			return this;
		},

		handleShowElements: function(e){
			$( VMConfig.get('showElementSelector') ).each(function(){
				this.className = this.className || '';

				var params = VMConfig.get('showElementExpr').exec( this.className ),
				that = this,
				pos = $(this).position(),
				height = $(this).height();

				if( params && params[1]){
					$(this).bind(e, function(){
						$('#'+params[1]).css({
							top: (pos.top + height) + 'px',
							left: pos.left + 'px'
						}).toggle().focus();
						return false;
					});
				}

				return true;
			});

			$(document).click(function(){
				$('.vm-showable').hide();
			});

			return true;
		}
	});

	VMConfig.set({
		//URL to country/state AJAX Request
		countryStateURL: 'index.php?option=com_virtuemart&view=state&task=getList&format=json&virtuemart_country_id=',

		//string-int Speed to Menu slide effect
		menuAdminSpeed: 'medium',

		// menu closer selector
		menuCloserSelector: '#vm-close-menu',

		//Menu state cookie name
		menuStateCookie: 'vmclosed',

		//Menu active item cookie name
		menuActiveItemCookie: 'voir',

		//Contentpane selector
		menuContentSelector: '.section-smenu',

		//Title selector
		menuTitleSelector: '.title-smenu',

		menuHandlerClass: 'vm-close-menu-show',

		menuToggleSpeed: 'medium',

		//Css selector to hidden elements
		hiddenElementClass: 'element-hidden',

		menuActiveClass: 'title-smenu-down',

		layoutLeftSelector: '.vm-layout-left',

		layoutRightSelector: '.vm-layout-right',

		showElementSelector: 'a[class*=show_element]',

		showElementExpr: /show_element\[(.*)\]/i
	});

})(jQuery);

function orderingUpDown($dir,button) {
	var ordering = button.closest('td').find('.ordering:first'),
	id = jQuery('.ordering').index(ordering);
	orderval = jQuery('.ordering').eq(id).val();
	orderval2 = jQuery('.ordering').eq(id+$dir).val();
	jQuery('.ordering').eq(id).val(orderval2);
	jQuery('.ordering').eq(id+$dir).val(orderval);
}