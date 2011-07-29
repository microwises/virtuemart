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

var VM = (function($){
	
	var undefined,
	
	/**
	 * Configuration Class
	 */
	VMConfig = window.VMConfig = (function(){

		props = {			
			//CSS selector for dependent combos
			dependentSelector: 'select[class*=dependent]',
				
			//Regular Expr to parse parent list
			dependentExpr: /dependent\[(.*)\]/i,
			countryStateURL: 'index.php?option=com_virtuemart&view=state&format=json&virtuemart_country_id='
		};
		
		return {
			
			get: function(name){
				if( name.constructor == String && props[name] !== undefined ){
					return props[name];
				}
				return false;
			},
			
			set: function(items, value){
				if( items.constructor == String && props[items] !== undefined ){
				
					props[items] = value;
					return props[items];
				}
				else if(items.constructor === Object){
					for(var i in items){
						props[i] = items[i];
					}
				}
				return;
			}
		};
	})(),
	
	
	
	/**
	 * Cache namespace. Useful for temporal data
	 * 
	 * @author jseros
	 * 
	 */	
	VMCache = window.VMCache = (function(){
		var slots = {};
		
		return {
			get: function(name){
				if( name.constructor == String && slots[name] !== undefined ){
					return slots[name];
				}
				return false;
			},
		
			set: function(name, value){
			
				if( name.constructor == String ){
					slots[name] = value;					
					return slots[name];
				}
				return;
			},
			
			add: function(name, values, index){
				if( name.constructor == String ){	
					
					if( !slots[name] ){
						slots[name] = {};
					}
					
					if(index !== undefined){
						if(!slots[name][index]){
							return slots[name][index] = $.merge([], values);
						}
						else{
							return slots[name][index] = $.merge(slots[name][index], values);
						}
					}
				}
				
				return this.set(name, $.merge([], values));
			}
		}
		
	})();
	
	return {		
		
		inArray: function(obj, item){
			for(var i = 0, n = obj.length; i < n; i++){
				if(obj[i] ===  item){
					return i;
				}
			}
		},
		
		/**
		 * Parse country-state dependent combos
		 * The Select Element must have class="dependent[parent_id]"
		 * 
		 * TODO: Performance issues
		 * 
		 * @author jseros
		 */
		countryStateList: function(){
			
			var populateStates = function(statesCombo, countries){
				var statesC = $(statesCombo),
				statesCache = VMCache.get('states'),
				statesGroup = [],
				states2add = '';

				statesCombo.$countries = statesCombo.$countries ? statesCombo.$countries : [];
					
				for(var i = 0, n = statesCombo.$countries.length; i < n; i++){
				
					if( VM.inArray( countries, statesCombo.$countries[i]) === undefined ){
						for( var state in statesCache[ statesCombo.$countries[i] ] ){
							statesC.find('[value='+ statesCache[ statesCombo.$countries[i] ][state].virtuemart_state_id  +']').remove();
						}
					}
				}
				// set init values one time
				if ( typeof populateStates.stateid == 'undefined' ) {
				populateStates.selectedChar = 'selected="selected"';
				populateStates.stateid = $('#virtuemart_state_id').data('stateid');
				populateStates.states =  populateStates.stateid.length ? populateStates.stateid.toString().split(',') : [] ;
				}

				for(var i = 0, n = countries.length; i < n; i++){
				
					if( VM.inArray( statesCombo.$countries, countries[i]) === undefined ){
						statesGroup = statesCache[ countries[i] ];
						
						for(var j in statesGroup){
							if(statesGroup[j].virtuemart_state_id){
								
								var selected ='';
								if(VM.inArray(populateStates.states,statesGroup[j].virtuemart_state_id)  !== undefined){
									selected = populateStates.selectedChar; 
								}
								
								states2add += '<option value="'+ statesGroup[j].virtuemart_state_id +'" '+selected+'>'+ statesGroup[j].state_name +'</option>';
							}
						}
					}
				}

				statesCombo.$countries = countries;
				statesC.append( states2add ).removeAttr('disabled');
				statesC.trigger("liszt:updated");
			};
			
			$( VMConfig.get('dependentSelector') ).each(function(){
						
				var params = VMConfig.get('dependentExpr').exec( this.className ), //parse parent id
				that = this;
						
				this.className = this.className || '';
							
				if( params && params[1]){
					var chathing;
					$('#'+ params[1]).ready(chathing = function(){
						var countries = $(this).val(),
						statesCache = VMCache.get('states'), //shortchut to [[this]] and scope solution
						country = 0,
						countriesSend = [],
						cStack = [];
						if(!countries){	
							countries = jQuery('#virtuemart_country_id').val() || [];
						}
						countries = countries.push ? countries : [countries];
					
						$(that).attr('disabled', 'disabled');
						
						for(var i = 0, n = countries.length; i < n; i++){
							
							country = countries[i];
							
							//use cache solution to speed up the process
							if( statesCache[country] ){
								cStack.push( country );
							}
							else{
								countriesSend.push(country);
							}
						}
						
						if( countriesSend.length ){
							$.ajax({
								url: VMConfig.get('countryStateURL') + countriesSend.toString(),
								dataType: 'json',
								success: function(states){
									for(var country in states){
										cStack.push( country );
										VMCache.add('states', states[country], country);
									}
									
									populateStates( that, cStack );
									return true;
								}
							});
						}
						else{
							populateStates( that, cStack );
						}
					});
					$('#'+ params[1]).change(chathing);
				}
			});
			
			return this;
		},
		
		add: function(props){
			if(props.constructor === Object){
				for(var i in props){
					this[i] = props[i];
				}
			}
			return this;
		}
	};
	
})(jQuery);