<?php
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**
 *
 * @version $Id$
 * @package VirtueMart
 * @subpackage shipping
 * @copyright Copyright (C) 2006 Ben Wilson. All rights reserved.
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
 * This class will charge a shipping rate determined by passing parameters to 
 * Australia Post eDeliver Calculator located at http://drc.edeliver.com.au/ 
 * @copyright (C) 2006 Ben Wilson, ben@diversionware.com.au
 * 
 *******************************************************************************
 */
class plgShippingAuspost extends vmShippingPlugin {
	
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
	function plgShippingAuspost( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}
	
	function get_shipping_rate_list( &$d ) {
		global $total, $tax_total, $CURRENCY_DISPLAY ;
		
		$cart = $_SESSION['cart'] ;
		
		if( $_SESSION['auth']['show_price_including_tax'] != 1 ) {
			$taxrate = 1 ;
			$order_total = $total + $tax_total ;
		} else {
			$taxrate = $this->get_shippingtax_rate() + 1 ;
			$order_total = $total ;
		}
		
		//Create DB User Object for Current User
		$dbu = new ps_DB( ) ;
		$q = "SELECT country,zip FROM #__{vm}_user_info WHERE user_info_id = '" . $d["ship_to_info_id"] . "'" ;
		$dbu->query( $q ) ;
		if( ! $dbu->next_record() ) {
			/*$vmLogger->err( JText::_('COM_VIRTUEMART_CHECKOUT_ERR_SHIPTO_NOT_FOUND',false) );
			return False;*/
		}
		
		//Create DB Vendor Object for Shop Vendor
		$dbv = new ps_DB( ) ;
		$q = "SELECT * from #__{vm}_vendor, #__{vm}_country WHERE vendor_id='" . $_SESSION["ps_vendor_id"] . "' AND (vendor_country=country_2_code OR vendor_country=country_3_code)" ;
		$dbv->query( $q ) ;
		$dbv->next_record() ;
		
		//$dbv = new ps_DB
		//$q  = "SELECT * FROM #__{vm}_vendor WHERE vendor_id='".$_SESSION['ps_vendor_id']."'";
		//$dbv->query($q);
		//$dbv->next_record();
		

		//set up the variables for Australia Post Query
		

		//Postcode of the pick-up address (e.g. 3015)
		//$Order_Pickup_Postcode = '2615';
		//$Order_Pickup_Postcode = Pickup_Postcode;
		$Order_Pickup_Postcode = $dbv->f( "vendor_zip" ) ;
		
		//Postcode of the delivery destination (e.g. 2615)
		//$Order_Destination_Postcode = '2001';
		$Order_Destination_Postcode = $dbu->f( "zip" ) ;
		
		//The country of delivery destination designated by two alpha characters. For example, AU stands for Australia
		$Order_Country = 'AU' ;
		
		//The weight of the parcel or item measured in grams (g)
		//$Order_Weight = '10000';
		$Order_WeightKG = $d['weight'] ;
		$Order_Weight = $Order_WeightKG * 1000 ;
		
		//The type of servive, available types are "Standard", "Express", "Air", "Sea", and "Economy"
		//$Order_Service_Type = Service_Type;
		$Order_Service_Type = 'STANDARD' ;
		
		//The length of the item or parcel in millimetres (mm)
		//Auspost returns same value so long as this is valid ie between 100 and 500, so we use a fixed 250 as a placeholder
		$Order_Length = '250' ;
		
		//The width of the item or parcel in millimetres (mm)
		$Order_Width = '250' ;
		
		//The height of the item or parcel in millimetres (mm)
		$Order_Height = '250' ;
		
		//This is the quantity of items for which the customer is estimating the delivery charges
		//Always set to one, as virtuemart does the multiplying for us based on quantity in cart
		$Order_Quantity = '1' ;
		
		//Fee for packaging and handling, added to the delivery costs returned by auspost
		$Order_Handling_Fee = Handling_Fee ;
		
		// Collect variables into the query URI for Australia Post
		$myfile = file( 'http://drc.edeliver.com.au/ratecalc.asp?Pickup_Postcode=' . $Order_Pickup_Postcode . '&Destination_Postcode=' . $Order_Destination_Postcode . '&Country=' . $Order_Country . '&Weight=' . $Order_Weight . '&Service_Type=' . $Order_Service_Type . '&Length=' . $Order_Length . '&Width=' . $Order_Width . '&Height=' . $Order_Height . '&Quantity=' . $Order_Quantity ) ;
		
		// Get Australia Post charge value separate to 'charge='
		$APchargeArray = split( '=', $myfile[0] ) ;
		$APcharge = $APchargeArray[1] ;
		
		// Get Australia Post Time separate to 'days='
		$APtimeArray = split( '=', $myfile[1] ) ;
		$APtime = $APtimeArray[1] ;
		
		// error message
		$APerrorArray = split( '=', $myfile[2] ) ;
		$APerrorMessage = $APerrorArray[1] ;
		(string)$strAPerrorMessage = $APerrorMessage ; //necessary to type cast this to a string otherwise below comparator doesn't work ???
		

		$returnArr = array() ;
		
		if( substr( $strAPerrorMessage, 0, 2 ) === "OK" ) {
			$Total_Shipping_Handling = $APcharge + $Order_Handling_Fee ;
			
			$_SESSION[$shipping_rate_id] = 1 ;
			
			// THE ORDER OF THOSE VALUES IS IMPORTANT:
			// ShippingClassName|carrier_name|rate_name|totalshippingcosts|rate_id
			$shipping_rate_id = urlencode( $this->classname . "|auspost|standard|" . number_format( $Total_Shipping_Handling, 2 ) ) ;
			
			$_SESSION[$shipping_rate_id] = 1 ;
			
			$returnArr[] = array( 'shipping_rate_id' => $shipping_rate_id , 
												'carrier' => 'Australia Post' , 
												'rate_name' => "(" . $Order_WeightKG . " kg)" , 
												'rate' => $Total_Shipping_Handling 
									);
			
			return $returnArr ;
		} else {
			$GLOBALS['vmLogger']->err( "Australia Post shipping calculator failed, reason: " . $APerrorMessage ) ;
			return false ;
		}
	}
	
	function get_shipping_rate( &$d ) {
		
		$shipping_rate_id = $d["shipping_rate_id"] ;
		$is_arr = explode( "|", urldecode( urldecode( $shipping_rate_id ) ) ) ;
		$order_shipping = $is_arr[3] ;
		
		return $order_shipping ;
	
	}
	
	function get_shippingtax_rate() {
		
		
		if( intval( $this->params->get('AUSPOST_TAX_CLASS') ) == 0 )
			return (0) ;
		else {
			require_once (CLASSPATH . "ps_tax.php") ;
			$tax_rate = ps_tax::get_taxrate_by_id( intval( $this->params->get('AUSPOST_TAX_CLASS') ) ) ;
			return $tax_rate ;
		}
	}
	
	/* Validate this Shipping method by checking if the SESSION contains the key
	* @returns boolean False when the Shipping method is not in the SESSION
	*/
	function validate( $d ) {
		
		$shipping_rate_id = $d["shipping_rate_id"] ;
		
		if( array_key_exists( $shipping_rate_id, $_SESSION ) ) {
			
			return true ;
		} else {
			return false ;
		}
	}
}

?>