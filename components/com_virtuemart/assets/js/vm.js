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
			
			baseUrl: ''
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
			
				if( name.constructor == String){
					slots[name] = value;					
					return slots[name];
				}
				return;
			}
		}
		
	})();
	
	return {		
		
		/**
		 * Parse country-state dependent combos
		 * The Select Element must have class="dependent[parent_id]"
		 * 
		 * TODO: Performance issues
		 * 
		 * @author jseros
		 */
		countryStateList: function(){
			//Performace issue
			var successCallBack = (function(statesCombo, countryId){
				return function(states){
					var options = '',
					statesC = $(statesCombo),
					statesCache = VMCache.get('states');
					
					if( !statesCache[countryId] ){
						statesCache = statesCache.constructor === Array ? statesCache : [];						
						statesCache[countryId] = states;
						VMCache.set('states', statesCache); //store into the cache object	
					}
									
					statesC.empty().removeAttr('disabled');
									
					for(var i = 0, j = states.length; i < j; i++){
						options += '<option value="'+ states[i].state_id +'">'+ states[i].state_name +'</option>';
					}
									
					statesC.append(	options );					
				};
			});
						
			$( VMConfig.get('dependentSelector') ).each(function(){
						
				var params = VMConfig.get('dependentExpr').exec( this.className ), //parse parent id
				that = this;
						
				this.className = this.className || '';
							
				if( params && params[1]){					
					$('#'+ params[1]).change(function(){
						var countryId = $(this).val(),
						statesCache = VMCache.get('states'); //shortchut to [[this]] and scope solution
						
						//use cache solution to speed up the process
						if( statesCache[countryId] ){
							successCallBack(that, countryId)( statesCache[countryId] );
						}
						else{
							$(that).attr('disabled', 'disabled');
											
							$.ajax({
								url: VMConfig.get('countryStateURL') + countryId,
								dataType: 'json',
								success: successCallBack(that, countryId)
							});
						}
					});
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