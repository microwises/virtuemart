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
 * 
 *
 * This class will charge a fixed shipping rate for orders under a minimum sales
 * threshhold and a percentage of the total order price for orders over that
 * threshold.
 * @copyright (C) 2005 Micah Shawn
 * 
 *******************************************************************************
 */
class plgShippingFlex extends vmShippingPlugin {
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
	function plgShippingFlex( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}
	/**
	 * Lists all available shipping rates
	 *
	 * @param array $d
	 * @return boolean
	 */
	function get_shipping_rate_list( &$d ) {
		global $total, $tax_total, $CURRENCY_DISPLAY ;
		
		if( $_SESSION['auth']['show_price_including_tax'] != 1 ) {
			$taxrate = 1 ;
			$order_total = $total + $tax_total ;
		} else {
			$taxrate = $this->get_shippingtax_rate() + 1 ;
			$order_total = $total ;
		}
		
		//Charge minimum up to this value in cart
		$base_ship = $GLOBALS['CURRENCY']->convert( $this->params->get('FLEX_BASE_AMOUNT') ) ;
		
		//Flat rate shipping charge up to minimum value
		$flat_charge = $GLOBALS['CURRENCY']->convert( $this->params->get('FLEX_MIN_CHG') ) ;
		
		//Charge this percentage if cart value is greater than base amount
		$ship_rate_perc = ($this->params->get('FLEX_SHIP_PERC') / 100) ;
		
		//Flat rate handling fee
		$handling_fee = $GLOBALS['CURRENCY']->convert( $this->params->get('FLEX_HAND_FEE') ) ;
		
		if( $order_total < $base_ship ) {
			$flat_charge += $handling_fee ;
			$rate = $flat_charge * $taxrate ;
			$shipping_rate_id = urlencode( $this->_name . "|STD|Standard Shipping under " . $base_ship . "|" . $rate ) ;
			$_SESSION[$shipping_rate_id] = 1 ;
			$rate_name = 'Standard Shipping under ' . $CURRENCY_DISPLAY->getFullValue($base_ship);
			
		} else {
			
			$shipping_temp1 = ($order_total * $ship_rate_perc) ;
			$rate = $shipping_temp1 + ($handling_fee * $taxrate) ;
			$shipping_rate_id = urlencode( $this->_name . "|STD|Standard Shipping over " . $base_ship . "|" . $rate ) ;
			$_SESSION[$shipping_rate_id] = 1 ;
			$rate_name = 'Standard Shipping over  ' . $CURRENCY_DISPLAY->getFullValue($base_ship);
		}
		$returnArr[] = array('shipping_rate_id' => $shipping_rate_id,
									'carrier' => 'Standard Shipping',
									'rate_name' => $rate_name,
									'rate' => $rate
								);
		return $returnArr ;
	
	}
	/**
	 * Returns the rate for the selected shipping method
	 *
	 * @param array $d
	 * @return float
	 */
	function get_shipping_rate( &$d ) {
		
		$shipping_rate_id = $d["shipping_rate_id"] ;
		$is_arr = explode( "|", urldecode( urldecode( $shipping_rate_id ) ) ) ;
		
		$order_shipping = (float)$is_arr[3] ;
		
		return $order_shipping ;
	
	}
	
	function get_shippingtax_rate() {
		
		if( intval( $this->params->get('FLEX_TAX_CLASS') ) == 0 ) {
			return (0) ;
		} else {
			require_once (CLASSPATH . "ps_tax.php") ;
			$tax_rate = ps_tax::get_taxrate_by_id( intval( $this->params->get('FLEX_TAX_CLASS') ) ) ;
			return $tax_rate ;
		}
	}
	
}

?>
