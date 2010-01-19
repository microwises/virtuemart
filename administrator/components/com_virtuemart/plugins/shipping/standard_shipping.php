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
class plgShippingStandard_Shipping extends vmShippingPlugin {
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
	function plgShippingStandard_Shipping( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}
	/**
	 * returns a html list with selectable rates
	 * $d[]: Array with search criteria
	 *             "country", "zip", "weight"
	 * @param array $d
	 * @return mixed
	 */
	function get_shipping_rate_list( &$d ) {
		global  $CURRENCY_DISPLAY, $vmLogger ;
		$auth = $_SESSION["auth"] ;
		
		$dbc = new ps_DB( ) ; // Carriers
		$dbr = new ps_DB( ) ; // Rates		

		$selected = False ;
		$d['ship_to_info_id'] = JRequest::getVar( 'ship_to_info_id' ) ;
		$q = "SELECT country,zip FROM #__{vm}_user_info WHERE user_info_id='" . $d['ship_to_info_id'] . "'" ;
		$dbc->query( $q ) ;
		$dbc->next_record() ;
		
		$zip = $dbc->f( "zip" ) ;
		$country = $dbc->f( "country" ) ;
		
		$q = "SELECT shipping_carrier_id,shipping_carrier_name FROM #__{vm}_shipping_carrier ORDER BY shipping_carrier_list_order ASC" ;
		$dbc->query( $q ) ;
		$i = 0 ;
		$returnArr = array();
		while( $dbc->next_record() ) {
			$q = "SELECT shipping_rate_id,shipping_rate_name,shipping_rate_value,shipping_rate_package_fee FROM #__{vm}_shipping_rate WHERE " ;
			$q .= "shipping_rate_carrier_id='" . $dbc->f( "shipping_carrier_id" ) . "' AND " ;
			$q .= "(shipping_rate_country LIKE '%" . $country . "%' OR " ;
			$q .= "shipping_rate_country = '') AND " ;
			if( is_numeric( $zip ) ) {
				$q .= "(shipping_rate_zip_start <= '" . $zip . "' OR  LENGTH(shipping_rate_zip_start) = 0 ) AND " ;
				$q .= "(shipping_rate_zip_end >= '" . $zip . "' OR  LENGTH(shipping_rate_zip_end) = 0 ) AND " ;
			}
			$q .= "shipping_rate_weight_start <= '" . $d["weight"] . "'AND " ;
			$q .= "shipping_rate_weight_end >= '" . $d["weight"] . "'" ;
			$q .= " ORDER BY shipping_rate_list_order ASC,  shipping_rate_name" ;
			$dbr->query( $q ) ;
			
			while( $dbr->next_record() ) {
				if( ! defined( "_SHIPPING_RATE_TABLE_HEADER" ) ) {
					$html = "<table width=\"100%\">\n<tr class=\"sectiontableheader\"><th>&nbsp;</th>" ;
					$html .= "<th>" . JText::_('VM_INFO_MSG_CARRIER') . "</th><th>" ;
					$html .= JText::_('VM_INFO_MSG_SHIPPING_METHOD') . "</th><th>" ;
					$html .= JText::_('VM_INFO_MSG_SHIPPING_PRICE') . "</th></tr>\n" ;
					define( "_SHIPPING_RATE_TABLE_HEADER", "1" ) ;
				}
				if( $i ++ % 2 )
					$class = "sectiontableentry1" ; else
					$class = "sectiontableentry2" ;
				if( $_SESSION['auth']['show_price_including_tax'] != 1 ) {
					$taxrate = 1 ;
				} else {
					$taxrate = $this->get_shippingtax_rate( $dbr->f( "shipping_rate_id" ) ) + 1 ;
				}
				$total_shipping_handling = $dbr->f( "shipping_rate_value" ) + $dbr->f( "shipping_rate_package_fee" ) ;
				$total_shipping_handling = $GLOBALS['CURRENCY']->convert( $total_shipping_handling ) ;
				$total_shipping_handling *= $taxrate ;
				
				// THE ORDER OF THOSE VALUES IS IMPORTANT:
				// ShippingClassName|carrier_name|rate_name|totalshippingcosts|rate_id
				$shipping_rate_id = urlencode( $this->_name . "|" . $dbc->f( "shipping_carrier_name" ) . "|" . $dbr->f( "shipping_rate_name" ) . "|" . number_format( $total_shipping_handling, 2, '.', '' ) . "|" . $dbr->f( "shipping_rate_id" ) ) ;
				
				$_SESSION[$shipping_rate_id] = 1 ;
				
				$returnArr[] = array('shipping_rate_id' => $shipping_rate_id,
													'carrier' => $dbc->f( "shipping_carrier_name" ),
													'rate_name' => $dbr->f( "shipping_rate_name" ),
													'rate' => $total_shipping_handling
												);
			}
		}
		if( ! empty( $returnArr ) ) {
			return $returnArr;
		} else {
			$vmLogger->debug( "The Shipping Module '" . __CLASS__ . "' couldn't 
				find a Shipping Rate that matches the current Checkout configuration:
				Weight: " . $d['weight'] . "
				Country: $country
				ZIP: $zip" ) ;
			return false;
		}
	}
	/**************************************************************************
	 * name: get_rate()
	 * created by: soeren
	 * description: returns the money to payfor from the given rate id
	 * parameters: $rate_id : The id of therate
	 * returns: a decimal value
	 **************************************************************************/
	function get_shipping_rate( &$d ) {
		
		$shipping_rate_id = $d["shipping_rate_id"] ;
		$is_arr = explode( "|", urldecode( urldecode( $shipping_rate_id ) ) ) ;
		$order_shipping = $is_arr[3] ;
		
		return $order_shipping ;
	
	}
	/**
	 * Retrieves the tax rate to apply to a shipping rate
	 *
	 * @param int $shipping_rate_id
	 * @return float
	 */
	function get_shippingtax_rate( $shipping_rate_id = 0 ) {
		$database = new ps_DB( ) ;
		
		if( $shipping_rate_id == 0 ) {
			$shipping_rate_id = JRequest::getVar( "shipping_rate_id" ) ;
			$ship_arr = explode( "|", urldecode( urldecode( $shipping_rate_id ) ) ) ;
			$shipping_rate_id = (int)$ship_arr[4] ;
		}
		$database->query( "SELECT tax_rate FROM #__{vm}_shipping_rate,#__{vm}_tax_rate WHERE shipping_rate_id='$shipping_rate_id' AND shipping_rate_vat_id=tax_rate_id" ) ;
		$database->next_record() ;
		if( $database->f( 'tax_rate' ) ) {
			return $database->f( 'tax_rate' ) ;
		} else {
			return 0.00 ;
		}
	}
	
	/**
	 * returns the money to payfor from the given rate id
	 *
	 * @param array $d
	 * @return array
	 */
	function get_shipping_rate_details( &$d ) {
		
		$rvalue["pure_rate"] = 0 ;
		$rvalue["pack_rate"] = 0 ;
		$rvalue["total_rate"] = 0 ;
		$rvalue["vat_rate"] = 0 ;
		$rvalue["vat_value"] = 0 ;
		$rvalue["rate_curr"] = 0 ;
		
		$details = explode( "|", urldecode( $d['shipping_rate_id'] ) ) ;
		$rate_id = $details[4] ;
		
		$dbr = new ps_DB( ) ; // Rates
		$q = "SELECT * FROM #__{vm}_shipping_rate WHERE " ;
		$q .= "shipping_rate_id='$rate_id'" ;
		$dbr->query( $q ) ;
		if( $dbr->next_record() ) {
			$rvalue["name"] = $dbr->f( "shipping_rate_name" ) ;
			$rvalue["pure_rate"] = $dbr->f( "shipping_rate_value" ) ;
			$rvalue["pack_rate"] = $dbr->f( "shipping_rate_package_fee" ) ;
			$rvalue["total_rate"] = $dbr->f( "shipping_rate_value" ) + $dbr->f( "shipping_rate_package_fee" ) ;
			$rvalue["vat_id"] = $dbr->f( "shipping_rate_vat_id" ) ;
			if( TAX_MODE == '1' ) {
				$dbv = new ps_DB( ) ;
				$q = "SELECT * FROM #__{vm}_tax_rate WHERE tax_rate_id ='" . $dbr->f( "shipping_rate_vat_id" ) . "'" ;
				$dbv->query( $q ) ;
				if( $dbv->next_record() ) {
					$rvalue["vat_rate"] = $dbv->f( "tax_rate" ) ;
					$rvalue["vat_value"] = ($rvalue["total_rate"] * $rvalue["vat_rate"]) / (100 + $rvalue["vat_rate"]) ;
				}
			}
			$dbc = new ps_DB( ) ;
			$q = "SELECT * FROM #__{vm}_shipping_carrier WHERE shipping_carrier_id ='" . $dbr->f( "shipping_rate_carrier_id" ) . "'" ;
			$dbc->query( $q ) ;
			if( $dbc->next_record() ) {
				$rvalue["carrier"] = $dbc->f( "shipping_carrier_name" ) ;
			}
			
			$q = "SELECT * FROM #__{vm}_currency WHERE currency_id ='" . $dbr->f( "shipping_rate_currency_id" ) . "'" ;
			$dbc->query( $q ) ;
			if( $dbc->next_record() ) {
				$rvalue["rate_curr"] = $dbc->f( "currency_code" ) ;
			}
		}
		return $rvalue ;
	}
	
	/**
	 * Validate a selected Shipping Rate
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate( &$d ) {
		global  $vmLogger ;
		$cart = $_SESSION['cart'] ;
		
		$d['shipping_rate_id'] = JRequest::getVar(  'shipping_rate_id' ) ;
		$d['ship_to_info_id'] = JRequest::getVar(  'ship_to_info_id' ) ;
		
		if( empty( $_SESSION[$d['shipping_rate_id']] ) ) {
			return false ;
		}
		
		$details = explode( "|", urldecode( $d['shipping_rate_id'] ) ) ;
		$rate_id = intval( $details[4] ) ;
		
		$totalweight = 0 ;
		require_once (CLASSPATH . 'shippingMethod.class.php') ;
		for( $i = 0 ; $i < $cart["idx"] ; $i ++ ) {
			$weight_subtotal = vmShippingMethod::get_weight( $cart[$i]["product_id"] ) * $cart[$i]['quantity'] ;
			$totalweight += $weight_subtotal ;
		}
		
		$dbu = new ps_DB( ) ; //DB User
		$q = "SELECT country,zip FROM #__{vm}_user_info WHERE user_info_id = '" . $dbu->getEscaped( $d["ship_to_info_id"] ) . "'" ;
		$dbu = new ps_DB( ) ; //DB User
		$dbu->query( $q ) ;
		if( ! $dbu->next_record() ) {
			/*$vmLogger->err( JText::_('VM_CHECKOUT_ERR_SHIPTO_NOT_FOUND',false) );
			return False;*/
		}
		
		$zip = $dbu->f( "zip" ) ;
		$country = $dbu->f( "country" ) ;
		
		$q = "SELECT shipping_rate_id FROM #__{vm}_shipping_rate WHERE shipping_rate_id = '$rate_id'" ;
		$dbs = new ps_DB( ) ; // DB Shiping_rate
		$dbs->query( $q ) ;
		if( ! $dbs->next_record() ) {
			$vmLogger->err( JText::_('VM_CHECKOUT_ERR_RATE_NOT_FOUND',false) ) ;
			return False ;
		}
		
		return $this->rate_id_valid( $rate_id, $country, $zip, $totalweight ) ;
	}
	
	/**
	 * checks if the rate is valid for the country, zip and weight
	 *
	 * @param int $rate_id
	 * @param string $country
	 * @param int $zip
	 * @param float $weight
	 * @return boolean
	 */
	function rate_id_valid( $rate_id, $country, $zip, $weight ) {
		global  $vmLogger ;
		$db = new ps_DB( ) ; // Rates
		$q = "SELECT * FROM #__{vm}_shipping_rate WHERE shipping_rate_id=$rate_id" ;
		
		$db->query( $q ) ;
		if( $db->next_record() ) {
			$valid = true ;
			if( ! stristr( $db->f( "shipping_rate_country" ), $country ) && $db->f( 'shipping_rate_country' ) != "" ) {
				$vmLogger->debug( 'The country ' . $country . ' is not supported by this shipping rate.' ) ;
				$valid = false ;
			}
			if( $db->f( "shipping_rate_weight_start" ) > $weight ) {
				$vmLogger->debug( 'The weight ' . $weight . ' is not enough for this shipping rate.' ) ;
				$valid = false ;
			
			}
			if( $db->f( "shipping_rate_weight_end" ) < $weight ) {
				$vmLogger->debug( 'The weight ' . $weight . ' is too high for this shipping rate.' ) ;
				$valid = false ;
			
			}
			if( is_numeric( $zip ) ) {
				if( $db->f( "shipping_rate_zip_start" ) > $zip ) {
					$vmLogger->debug( 'The ZIP ' . $zip . ' is smaller than the supported ZIP code range of this shipping rate.' ) ;
					$valid = false ;
				
				}
				if( $db->f( "shipping_rate_zip_end" ) < $zip ) {
					$vmLogger->debug( 'The ZIP ' . $zip . ' is higher than the supported ZIP code range of this shipping rate.' ) ;
					$valid = false ;
				}
			}
			if( ! $valid ) {
				$vmLogger->err( JText::_('VM_CHECKOUT_ERR_OTHER_SHIP',false) ) ;
			}
			return $valid ;
		
		} else {
			$vmLogger->debug( 'The rate id ' . $rate_id . ' is not a valid shipping rate' ) ;
			return false ;
		}
	}

}

?>