/**
 * VMAdmin.js: General Javascript Library for VirtueMart Administration
 *
 *
 * @package	VirtueMart
 * @subpackage Javascript Library
 * @author jseros
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */


/**
 * Auto-executable function to avoid conflict and global variables.
 * 
 * @author jseros
 * @param jQuery $ Solve the scope problem and jQuery-Mootools conflict
 */

jQuery.noConflict();

(function($){
	
	/**
	 * VMAdmin namespace. 
	 * Local variable and property attached to global object
	 * 
	 * @author jseros
	 */
	var VMAdmin = window.VMAdmin = {
		
		/**
		 * Configuration Object
		 */	
		config: {
			menuAdminSpeed: 'medium', //string-int Speed to Menu slide effect
			menuCloserSelector: '#vm-close-menu' // menu closer selector
		},
			
		
		/**
		 * cache namespace. Useful to temporal data
		 * 
		 * @author jseros
		 * 
		 */	
		cache: {
			states: [], //states cache
			activeMenuAdminItem: null //active item menu
		},
		
		/**
		 * URL namespace. URLs to use.
		 * 
		 * @author jseros
		 * 
		 */
		URL: {
			countryStates: 'index.php?option=com_virtuemart&view=state&task=getList&format=json&country_id='
			
		},
		
		/**
		 * util namespace
		 * 
		 * @author jseros
		 * 
		 */
		util: {
			
			/**
			 * Parse country-state dependent combos
			 * The Select Element must have class="dependent[parent_id]"
			 * 
			 * TODO: Performance issues
			 * 
			 * @author jseros
			 */
			countryStateList: function(){
		
				$(function(){
					
					//Performace issue
					var successCallBack = (function(statesCombo, countryId){
						return function(states){
							var options = '',
							statesC = $(statesCombo);
							
							if( !VMAdmin.cache.states[countryId] ){ 
								VMAdmin.cache.states[countryId] = states; //store into the cache object
							}
							
							statesC.empty().removeAttr('disabled');
							
							for(var i = 0, j = states.length; i < j; i++){
								options += '<option value="'+ states[i].state_id +'">'+ states[i].state_name +'</option>';
							}
							
							statesC.append(	options );					
						};
					});
					
					$('select[class*=dependent]').each(function(){
						this.className = this.className || '';
						
						var params = /dependent\[(.*)\]/i.exec( this.className ), //extracting parent id
						that = this; //shortchut to [[this]] and scope solution
						
						if( params && params[1]){
							       
							$('#'+ params[1]).change(function(){
								
								var countryId = $(this).val();
								
								//using cache to speed up the process
								if( VMAdmin.cache.states[countryId] ){
									successCallBack(that, countryId)( VMAdmin.cache.states[countryId] );
								}
								else{
									$(that).attr('disabled', 'disabled');
									
									$.ajax({
										url: VMAdmin.URL.countryStates + countryId,
										dataType: 'json',
										success: successCallBack(that, countryId)
									});
								}
							});
						}
					});
					
				});
				return true;
			},
			
			/**
			 * Create admin accordion menu
			 * 
			 * TODO: Standalone cookie storage
			 * 
			 * @author jseros
			 */
			buildAdminMenu: function(){
				var that = this; //that = VMAdmin.util
				
				$(function(){
					var actualItem = VMAdmin.cache.activeMenuAdminItem = parseInt(Cookie.get('voir')), // Current selected item
					speed = VMAdmin.config.menuAdminSpeed; //shortcut. Performance issue!
					
					//set current state when document loads
					VMAdmin.util.showMenu(false, Cookie.get('vmclosed'));
					
					$('.section-smenu').addClass('element-hidden');//Hidding Panels
					
					var actualItemNode = $('#menu-toggler-'+ (actualItem || 1));
					actualItemNode.next().slideDown( speed );
					
					that.activeMenuItem( actualItemNode.get(0) );
					
					$('.title-smenu').click(function(){
						that.activeMenuItem( this );
					});				
					
					VMAdmin.util.setMenuClose();
				});
				return true;
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
				
				var ai = VMAdmin.cache.activeMenuAdminItem, //shortcut. Performance issue!
				voir = parseInt( $( toggler ).addClass('title-smenu-down').attr('rel') ), //this Item
				speed = VMAdmin.config.menuAdminSpeed; //shortcut. Performance issue!
				
				if( ai !== voir ){
					$( toggler ).next().slideDown( speed );
					
					if(ai){
						$('#menu-toggler-'+ ai).removeClass('title-smenu-down').next().slideUp( speed );
					}
					
					Cookie.set('voir', voir);
					VMAdmin.cache.activeMenuAdminItem = voir;
				}
				return true;
			},
			

			/**
			 * Set the menu close action
			 * 
			 * @author jseros
			 * 
			 * @param bool with animation?
			 */
			setMenuClose: function(){
				var closerSel = VMAdmin.config.menuCloserSelector;
				
				$(closerSel).click(function(){
					VMAdmin.util.showMenu(true);				
					return false;
				});
				return true;
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
				var leftPanel = $('.vm-layout-left'),
				rightPanel = $('.vm-layout-right'),
				closerSel = VMAdmin.config.menuCloserSelector;
				
				if(typeof hide !== 'undefined'){
					if(hide){
						leftPanel.css('width', '0');
						rightPanel.css('width', '99%');
						$(closerSel).addClass('vm-close-menu-show');
					}
					return true;
				}			
				
				
				if( !Cookie.get('vmclosed')){
					if(animation){
						leftPanel.animate({ width: '0', opacity: 0}, 'medium');
						rightPanel.animate({ width: '99%'}, 'medium');
					}
					else{
						leftPanel.css('width', '0px');
						rightPanel.css('width', '99%');
					}
					
					$(closerSel).addClass('vm-close-menu-show');
					Cookie.set('vmclosed', 1);
				}
				else{
					
					if(animation){
						rightPanel.animate({ width: '77%'}, 'medium');
						leftPanel.animate({ width: '23%', opacity: 1}, 'medium');
					}
					else{
						rightPanel.css('width', '77%');
						leftPanel.css('width', '23%');
					}
					
					$(closerSel).removeClass('vm-close-menu-show');
					Cookie.remove('vmclosed');
				}
				
				return true;
			}
		}
	};
	
})(jQuery);