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
	
 });
 
 
(function($){
	

	/**
	 * Local variable and property attached to global object
	 * 
	 * @author jseros
	 */
	VM.add({});

	VMConfig.set({
		//URL to country/state AJAX Request
		countryStateURL: 'index.php?option=com_virtuemart&view=state&format=json&country_id='
	});
	
})(jQuery);