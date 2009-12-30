<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
* @version $Id: shipvalue.php,v .1 2005/09  r_lewis
* @package VirtueMart
* @subpackage shipping
* @copyright (C) 2005 Rhys Lewis with due respect to Micah Shawn and Bret (allbloodrunsred)

* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Based on VirtueMart.  Thank you Soeren!
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
* www.virtuemart.net
******************************************************************************
*
* This class will charge a fixed shipping rate based on the total order value
* up to 10 thresholds for  total order value can be set in admin>store>shipping module list>shipvalue
*
*******************************************************************************
*/
class plgShippingShipvalue extends vmShippingPlugin {
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
	function plgShippingShipvalue( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}

	function get_shipping_rate_list( &$d ) {
		global $total, $tax_total, $CURRENCY_DISPLAY;
		$db =& new ps_DB;
		$dbv =& new ps_DB;

		$cart = $_SESSION['cart'];

		if ( $_SESSION['auth']['show_price_including_tax'] != 1 ) {
			$taxrate = 1;
			$order_total = $total + $tax_total;
		}
		else {
			$taxrate = $this->get_shippingtax_rate() + 1;
			$order_total = $total;
		}

		//Define shipping value breaks
		$base_ship1 = $this->params->get('BASE_SHIP1');
		$base_ship2 = $this->params->get('BASE_SHIP2');
		$base_ship3 = $this->params->get('BASE_SHIP3');
		$base_ship4 = $this->params->get('BASE_SHIP4');
		$base_ship5 = $this->params->get('BASE_SHIP5');
		$base_ship6 = $this->params->get('BASE_SHIP6');
		$base_ship7 = $this->params->get('BASE_SHIP7');
		$base_ship8 = $this->params->get('BASE_SHIP8');
		$base_ship9 = $this->params->get('BASE_SHIP9');
		$base_ship10 = $this->params->get('BASE_SHIP10');

		//Flat rate shipping charge up to minimum value
		$flat_charge1 = $this->params->get('BASE_CHARGE1');
		$flat_charge2 = $this->params->get('BASE_CHARGE2');
		$flat_charge3 = $this->params->get('BASE_CHARGE3');
		$flat_charge4 = $this->params->get('BASE_CHARGE4');
		$flat_charge5 = $this->params->get('BASE_CHARGE5');
		$flat_charge6 = $this->params->get('BASE_CHARGE6');
		$flat_charge7 = $this->params->get('BASE_CHARGE7');
		$flat_charge8 = $this->params->get('BASE_CHARGE8');
		$flat_charge9 = $this->params->get('BASE_CHARGE9');
		$flat_charge10 = $this->params->get('BASE_CHARGE10');
		
		$returnArr = array();
		
		for($i=1;$i <= 10;$i++) {
			$flat_charge_varname = 'flat_charge'.$i;
			$base_ship_varname = 'base_ship'.$i;
			if($order_total < $$base_ship_varname) {
				$$flat_charge_varname *= $taxrate;
				$shipping_rate_id = urlencode($this->_name."|STD|Standard Shipping under ".$$base_ship_varname."|".$$flat_charge_varname);
				
				$returnArr[] = array('shipping_rate_id' => $shipping_rate_id,
													'carrier' => 'Standard Shipping',
													'rate_name' => "Standard Shipping under ".$$base_ship_varname,
													'rate' => $$flat_charge_varname
												);
				return $returnArr;
			}
		}
		
		return false;

	}

	function get_shipping_rate( &$d ) {

		$shipping_rate_id = $d["shipping_rate_id"];
		$is_arr = explode("|", urldecode(urldecode($shipping_rate_id)) );
		$order_shipping = $is_arr[3];

		return $order_shipping;

	}


	function get_shippingtax_rate() {

		if( intval($this->params->get('SHIPVALUE_TAX_CLASS'))== 0 )
		return( 0 );
		else {
			require_once( CLASSPATH. "ps_tax.php" );
			$tax_rate = ps_tax::get_taxrate_by_id( intval($this->params->get('SHIPVALUE_TAX_CLASS')) );
			return $tax_rate;
		}
	}

	/* Validate this Shipping method by checking if the SESSION contains the key
	* @returns boolean False when the Shipping method is not in the SESSION
	*/
	function validate( $d ) {

		$shipping_rate_id = $d["shipping_rate_id"];

		if( array_key_exists( $shipping_rate_id, $_SESSION )) {
			return true;
		}
		else {
			return false;
		}
	}
	
}


?>