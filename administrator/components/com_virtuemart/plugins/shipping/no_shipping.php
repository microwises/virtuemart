<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage shipping
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

/**
* Just a dummy class for "NO SHIPPING"
*/
class plgShippingNo_Shipping extends vmShippingPlugin {
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.2.0
	 */
	function plgShippingNo_Shipping( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}
  
  /**************************************************************************
  ** name: list_rates( $d )
  ** created by: soeren
  ***************************************************************************/  
  function get_shipping_rate_list( &$d ) {
      return array();
    }
    
  /**************************************************************************
  ** name: get_rate( $d )
  ** created by: soeren
  ***************************************************************************/
   function get_shipping_rate( &$d ) {
      return 0;
   }
  /**************************************************************************
  ** name: get_shippingtax_rate()
  ** created by: soeren
  ***************************************************************************/
   function get_shippingtax_rate() {
      return 0;
   }

}
?>