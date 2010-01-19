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
global $mosConfig_live_site;

define('FEDEX_REQUEST_REFERER', $mosConfig_live_site);
define('FEDEX_REQUEST_TIMEOUT', 20);
define('FEDEX_IMG_DIR', '/tmp/');

// Include the FedExTags class
require_once( ADMINPATH.'plugins/shipping/fedex/fedex-tags.php' );

class plgShippingFedex extends vmShippingPlugin {
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
	function plgShippingFedex( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}

	/** 
	 * Echos a formatted list of shipping rates.
	 * 
	 * @param array $d
	 * @return boolean
	 */
    function get_shipping_rate_list( &$d ) {
		global $vendor_country_2_code, $vendor_currency, $vmLogger;
		global  $CURRENCY_DISPLAY;
		$db =& new ps_DB;
		$dbv =& new ps_DB;

		$cart = $_SESSION['cart'];
		
		// Include the main FedEx class
		require_once( ADMINPATH.'plugins/shipping/fedex/fedexdc.php' );
		
		// Get the meter number
		if( $this->params->get('FEDEX_METER_NUMBER')=='') {
			if( !$this->update_meter_number() ) {
				$vmLogger->err( JText::_('VM_FEDEX_ERR_METER_NUMBER',false) );
				return false;
			}
		}
		
		// Get the shopper's shipping address
		$q  = "SELECT * FROM #__{vm}_user_info, #__{vm}_country WHERE user_info_id='" . $d["ship_to_info_id"]."' AND ( country=country_2_code OR country=country_3_code)";
		$db->query($q);
		$db->next_record();

		// Get the vendor address
		$q  = "SELECT * FROM #__{vm}_vendor WHERE vendor_id='".$_SESSION['ps_vendor_id']."'";
		$dbv->query($q);
		$dbv->next_record();

		// Is this a residential delivery?
		$residential_delivery_flag = JRequest::getVar( 'address_type', 'residential') == 'residential' ? 'Y' : 'N';
		
		// Is this a domestic delivery?
		$recipient_country = $db->f('country_2_code');
		$domestic_delivery = ($recipient_country == 'US' || $recipient_country == 'CA') ? true : false;

		// Get the weight total
		if( $d['weight'] > 150) {
			$d['weight'] = 150;
		}
		if( $d['weight'] < 1) {
			$d['weight'] = 1;
		}
		$order_weight = number_format( (float)$d['weight'], 1, '.', '' );
		
		// Set units
		$weight_units = (WEIGHT_UOM == 'KG') ? 'KGS' : 'LBS';
		$dimension_units = (WEIGHT_UOM == 'KG') ? 'C' : 'I';
		
		// config values
		$fed_conf = array();
		
		// create new FedExDC object
		$meter_number = defined('FEDEX_METER_NUMBER_TEMP') ? FEDEX_METER_NUMBER_TEMP : $this->params->get('FEDEX_METER_NUMBER');
		$fed = new FedExDC( $this->params->get('FEDEX_ACCOUNT_NUMBER'), $meter_number, $fed_conf );
		
		// Set up the rate request array.
		// You can either use the FedEx tag value or the field name in the $FE_RE array
		$request_array = 
			array(
		       	'carrier_code' => ''//FDXE or FDXG or blank for both

		        ,'sender_state' => 	$dbv->f('vendor_state')
		        ,'sender_postal_code' => 	$dbv->f('vendor_zip')
		        ,'sender_country_code' =>	$vendor_country_2_code
		        
		        ,'recipient_state' =>   $db->f('state')
		        ,'recipient_postal_code' =>   $db->f('zip')
		        ,'recipient_country' =>   $db->f('country_2_code')

				,'residential_delivery_flag' => $residential_delivery_flag
				,'signature_option' => $this->params->get('FEDEX_SIGNATURE_OPTION')		        

//		        ,'dim_units' =>	$dimension_units
//		        ,'dim_height' =>	'12'
//		        ,'dim_width' =>	'24'
//		        ,'dim_length' =>	'10'

		        ,'weight_units' => $weight_units
		        ,'total_package_weight' =>	$order_weight

		        ,'drop_off_type' =>	'1'
			);
		
		// Get the rate quote
		$rate_Ret = $fed->services_rate ( $request_array );		
		
		if ($error = $fed->getError()) {
		    $vmLogger->err( $error );
			return false;
		} 
		elseif( DEBUG ) {
			echo "<pre>";
		    echo $fed->debug_str. "\n<br />";
		    print_r($rate_Ret);
		    echo "\n";
		    echo "ZONE: ".$rate_Ret[1092]."\n\n";
		
		    for ($i=1; $i<=$rate_Ret[1133]; $i++) {
		        echo "SERVICE : ".$fed->service_type($rate_Ret['1274-'.$i], $domestic_delivery)."\n";
		        echo "SURCHARGE : ".$rate_Ret['1417-'.$i]."\n";
		        echo "DISCOUNT : ".$rate_Ret['1418-'.$i]."\n";
		        echo "NET CHARGE : ".$rate_Ret['1419-'.$i]."\n";
		        echo "DELIVERY DAY : ".@$rate_Ret['194-'.$i]."\n";
		        echo "DELIVERY DATE : ".@$rate_Ret['409-'.$i]."\n\n";
		    }
		    echo "</pre>";
		}
		
		// Set the tax rate
		if ( $_SESSION['auth']['show_price_including_tax'] != 1 ) {
			$taxrate = 1;
		}
		else {
			$taxrate = $this->get_shippingtax_rate() + 1;
		}
		
		// Get a sort order array (by cost)
		$cost_array = array();
		for ($i=1; $i<=$rate_Ret[1133]; $i++) {
			$cost_array[$i] = $rate_Ret['1419-'.$i];
		}
		if($this->params->get('FEDEX_SORT_ORDER') == 'ASC') {
			asort($cost_array, SORT_NUMERIC);
		} else {
			arsort($cost_array, SORT_NUMERIC);
		}
		
		// Determine which services we can display
		$selected_services = explode(',', $this->params->get('FEDEX_SERVICES'));
		if($domestic_delivery) {
			$selected_services = preg_grep( '/^d/', $selected_services );
			array_walk($selected_services, create_function('&$v,$k', '$v = substr($v, 1);'));
			
			// If this is a residential delivery, then remove the business option; otherwise, remove the home delivery option.
			if($residential_delivery_flag == 'Y') {
				$remove = array("92");
				$selected_services = array_diff( $selected_services, array("92") );
			} else{
				$remove = array("90");
				$selected_services = array_diff( $selected_services, array("90") );
			}
		} else {
			$selected_services = preg_grep( '/^i/', $selected_services );
			array_walk($selected_services, create_function('&$v,$k', '$v = substr($v, 1);'));
		}
		$returnArr= array();
		// Display each rate
		foreach (array_keys($cost_array) as $i) {
			if( in_array($rate_Ret['1274-'.$i], $selected_services) ) {
				$charge = $rate_Ret['1419-'.$i] + floatval( $this->params->get('FEDEX_HANDLINGFEE') );
				$charge *= $taxrate;
				
				$shipping_rate_id = urlencode($this->_name."|FedEx|".$fed->service_type($rate_Ret['1274-'.$i], $domestic_delivery)."|".$charge);
				$_SESSION[$shipping_rate_id] = 1;
				
				$returnArr[] = array('shipping_rate_id' => $shipping_rate_id,
													'carrier' => 'FedEx',
													'rate_name' => $fed->service_type($rate_Ret['1274-'.$i], $domestic_delivery),
													'rate' => $charge
												);
			}
		}

		return $returnArr;
    }
    
 	/**
 	 * Return the rate amount
 	 *
 	 * @param array $d
 	 * @return float Shipping rate value
 	 */
	function get_shipping_rate( &$d ) {

		$shipping_rate_id = $d["shipping_rate_id"];
		$is_arr = explode("|", urldecode(urldecode($shipping_rate_id)) );
		$order_shipping = $is_arr[3];

		return $order_shipping;

	} //end function get_shipping_rate

	/**
	 * Returns the tax rate for this shipping method
	 *
	 * @return float The tax rate (e.g. 0.16)
	 */
	function get_shippingtax_rate() {

		if( intval($this->params->get('FEDEX_TAX_CLASS'))== 0 )
		return( 0 );
		else {
			require_once( CLASSPATH. "ps_tax.php" );
			$tax_rate = ps_tax::get_taxrate_by_id( intval($this->params->get('FEDEX_TAX_CLASS')) );
			return $tax_rate;
		}
	}
   
	function update_meter_number() {
//		global $vendor_name,$vendor_address,$vendor_city,$vendor_state,$vendor_zip,
//			$vendor_country_2_code, $vendor_phone, $vmLogger;
		global $vmLogger;
		
		$fed = new FedExDC( $this->params->get('FEDEX_ACCOUNT_NUMBER') );
		$db = new ps_DB();
//		$db->query( ('SELECT `contact_first_name`, `contact_last_name` FROM `#__{vm}_vendor` WHERE `vendor_id` ='.intval($_SESSION['ps_vendor_id'])));
//		$db->next_record();
		//adjusted by Max Milbers
		$db = ps_vendor::get_vendor_details($db,intval($_SESSION['ps_vendor_id']));
	    $aRet = $fed->subscribe(
		    array(
		        1 => uniqid( 'vmFed_' ), // Don't really need this but can be used for ref
		        4003 => $db->f('first_name').' '.$db->f('last_name'),
		        4008 => $db->f('adress_1'),
		        4011 => $db->f('city'),
		        4012 => $db->f('state'),
		        4013 => $db->f('zip'),
		        4014 => $db->f('country_2_code'),
		        4015 => $db->f('vendor_phone')
		        
//		        4008 => $vendor_address,
//		        4011 => $vendor_city,
//		        4012 => $vendor_state,
//		        4013 => $vendor_zip,
//		        4014 => $vendor_country_2_code,
//		        4015 => $vendor_phone
		    )
		);
	    if ($error = $fed->getError() ) {
		    $vmLogger->err( $error );
		    return false;
	    }
	    $meter_number = $aRet[498];

		$db->query('UPDATE #__{vm}_plugins SET params = CONCAT(params, \'\nFEDEX_METER_NUMBER='.$meter_number.'\n\') WHERE id='.$this->_id);

		
		define( 'FEDEX_METER_NUMBER_TEMP', $meter_number );
		
		return true;
	}
	
}

?>