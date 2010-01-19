<?php
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
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
 * Welcome To The Shipping Zone =]
 * @copyright (C) 2000 - 2004 devcompany.com  All rights reserved.
 * @author Mike Wattier - geek@devcompany.com
 */
class plgShippingZone_Shipping extends vmShippingPlugin {
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
	function plgShippingZone_Shipping( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}
	
	function get_shipping_rate_list( &$d ) {
		global $CURRENCY_DISPLAY ;
		$db = new ps_DB( ) ;
		
		$q = "SELECT country FROM #__{vm}_user_info WHERE " ;
		$q .= "user_info_id='" . $d["ship_to_info_id"] . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		$country = $db->f( "country" ) ;
		
		$q2 = "SELECT country_name, zone_id FROM #__{vm}_country WHERE country_3_code='$country' " ;
		$db->query( $q2 ) ;
		$db->next_record() ;
		$the_zone = $db->f( "zone_id" ) ;
		$country_name = $db->f( "country_name" ) ;
		
		if( $_SESSION['auth']['show_price_including_tax'] != 1 ) {
			$taxrate = 1 ;
		} else {
			$taxrate = $this->get_shippingtax_rate( $the_zone ) + 1 ;
		}
		
		$q3 = "SELECT * FROM #__{vm}_zone_shipping WHERE zone_id ='$the_zone' " ;
		$db->query( $q3 ) ;
		$db->next_record() ;
		
		$cost_low = $db->f( "zone_cost" ) * $d["zone_qty"] ;
		
		if( $cost_low < $db->f( "zone_limit" ) ) {
			$rate = $cost_low ;
		} else {
			$rate = $db->f( "zone_limit" ) ;
		}
		$rate *= $taxrate ;
		$rate = $GLOBALS['CURRENCY']->convert( $rate ) ;
		// THE ORDER OF THOSE VALUES IS IMPORTANT:
		// carrier_name|rate_name|totalshippingcosts|rate_id
		$shipping_rate_id = urlencode( $this->_name . "|" . $the_zone . "|" . $country . "|" . $rate . "|" . $the_zone ) ;
		$_SESSION[$shipping_rate_id] = "1" ;
		
		$returnArr[] = array( 'shipping_rate_id' => $shipping_rate_id , 
											'carrier' => 'Zone Shipping' , 
											'rate_name' => 'to ' . $country_name , 
											'rate' => $rate ) ;
		
		return $returnArr ;
	}
	
	function get_shipping_rate( &$d ) {
		
		$shipping_rate_id = JRequest::getVar( "shipping_rate_id" ) ;
		$zone_arr = explode( "|", urldecode( urldecode( $shipping_rate_id ) ) ) ;
		$order_shipping = $zone_arr[3] ;
		
		return $order_shipping ;
	}
	
	function get_shippingtax_rate( $zone_id = 0 ) {
		$db = new ps_DB( ) ;
		
		if( $zone_id == 0 ) {
			$shipping_rate_id = JRequest::getVar( "shipping_rate_id" ) ;
			$zone_arr = explode( "|", urldecode( urldecode( $shipping_rate_id ) ) ) ;
			$zone_id = (int)$zone_arr[4] ;
		}
		$db->query( "SELECT tax_rate FROM #__{vm}_zone_shipping,#__{vm}_tax_rate WHERE zone_id='$zone_id' AND zone_tax_rate=tax_rate_id" ) ;
		$db->next_record() ;
		if( $db->f( 'tax_rate' ) )
			return $db->f( 'tax_rate' ) ;
		else
			return 0 ;
	}

}
?>