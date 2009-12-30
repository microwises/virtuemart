<?php
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**
 *
 * @version $Id: intershipper.php 1624 2009-02-04 19:28:48Z Milbo $
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
 * Class to connect to Intershipper and fetch live rates
 *
 */
class plgShippingIntershipper extends vmShippingPlugin {
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
	function plgShippingIntershipper( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}
	
	function get_shipping_rate_list( &$d ) {
		global $weight_total, $CURRENCY_DISPLAY, $vmLogger ;
		$d["ship_to_info_id"] = JRequest::getVar(  "ship_to_info_id" ) ;
		
		$dbv = new ps_DB( ) ;
//		$q = "SELECT * from #__{vm}_vendor, #__{vm}_country WHERE vendor_id='" . $_SESSION["ps_vendor_id"] . "' AND (vendor_country=country_2_code OR vendor_country=country_3_code)" ;
//		$dbv->query( $q ) ;
//		$dbv->next_record() ;
//TODO this should be here the vendor Id of the cart afaik
		$dbv = ps_vendor::get_vendor_details($_SESSION["ps_vendor_id"]); 
		
		$dbst = new ps_DB( ) ;
		$q = "SELECT * from #__{vm}_user_info, #__{vm}_country WHERE user_info_id='" . $d["ship_to_info_id"] . "' AND ( country=country_2_code OR country=country_3_code)" ;
		$dbst->query( $q ) ;
		$dbst->next_record() ;
		
		$carrier_arr = Array() ;
		$i = 0 ;
		if( $this->params->get( 'CARRIER1_NAME' ) != "" ) {
			$carrier_arr[$i]["name"] = $this->params->get( 'CARRIER1_NAME' ) ;
			$carrier_arr[$i]["invoice"] = $this->params->get( 'CARRIER1_INVOICE' ) ;
			$carrier_arr[$i]["account"] = $this->params->get( 'CARRIER1_ACCOUNT' ) ;
			$i ++ ;
		}
		if( $this->params->get( 'CARRIER2_NAME' ) != "" ) {
			$carrier_arr[$i]["name"] = $this->params->get( 'CARRIER2_NAME' ) ;
			$carrier_arr[$i]["invoice"] = $this->params->get( 'CARRIER2_INVOICE' ) ;
			$carrier_arr[$i]["account"] = $this->params->get( 'CARRIER2_ACCOUNT' ) ;
			$i ++ ;
		}
		if( $this->params->get( 'CARRIER3_NAME' ) != "" ) {
			$carrier_arr[$i]["name"] = $this->params->get( 'CARRIER3_NAME' ) ;
			$carrier_arr[$i]["invoice"] = $this->params->get( 'CARRIER3_INVOICE' ) ;
			$carrier_arr[$i]["account"] = $this->params->get( 'CARRIER3_ACCOUNT' ) ;
			$i ++ ;
		}
		if( $this->params->get( 'CARRIER4_NAME' ) != "" ) {
			$carrier_arr[$i]["name"] = $this->params->get( 'CARRIER4_NAME' ) ;
			$carrier_arr[$i]["invoice"] = $this->params->get( 'CARRIER4_INVOICE' ) ;
			$carrier_arr[$i]["account"] = $this->params->get( 'CARRIER4_ACCOUNT' ) ;
			$i ++ ;
		}
		if( $this->params->get( 'CARRIER5_NAME' ) != "" ) {
			$carrier_arr[$i]["name"] = $this->params->get( 'CARRIER5_NAME' ) ;
			$carrier_arr[$i]["invoice"] = $this->params->get( 'CARRIER5_INVOICE' ) ;
			$carrier_arr[$i]["account"] = $this->params->get( 'CARRIER5_ACCOUNT' ) ;
			$i ++ ;
		}
		$i = 0 ;
		$class_arr = Array() ;
		if( $this->params->get( 'SERVICE_CLASS1' ) != "" ) {
			$class_arr[$i] = $this->params->get( 'SERVICE_CLASS1' ) ;
			$i ++ ;
		}
		if( $this->params->get( 'SERVICE_CLASS2' ) != "" ) {
			$class_arr[$i] = $this->params->get( 'SERVICE_CLASS2' ) ;
			$i ++ ;
		}
		if( $this->params->get( 'SERVICE_CLASS3' ) != "" ) {
			$class_arr[$i] = $this->params->get( 'SERVICE_CLASS3' ) ;
			$i ++ ;
		}
		if( $this->params->get( 'SERVICE_CLASS4' ) != "" ) {
			$class_arr[$i] = $this->params->get( 'SERVICE_CLASS4' ) ;
			$i ++ ;
		}
		//Set your username and password.
		$username = $this->params->get( 'IS_USERNAME' ) ;
		$password = $this->params->get( 'IS_PASSWORD' ) ;
		
		// Build the query string to be sent to the IS server.
		//http://intershipper.com/Shipping/Intershipper/Website/MainPage.jsp?Page=Integrate
		// for additional information
		// for additional information
		

		$url = 'www.intershipper.com' ;
		$uri = '/Interface/Intershipper/XML/v2.0/HTTP.jsp?' . 'Username=' . $username . '&Password=' . $password . '&Version=' . '2.0.0.0' . '&ShipmentID=' . '1234' . '&QueryID=' . '23456' . '&TotalCarriers=' . count( $carrier_arr ) ;
		$i = 1 ;
		foreach( $carrier_arr as $carrier ) {
			$uri .= "&CarrierCode$i=" . $carrier["name"] . "&CarrierInvoiced$i=" . $carrier["invoice"] . "&CarrierAccount$i=" . $carrier["account"] ;
			$i ++ ;
		}
		$uri .= '&TotalClasses=' . count( $class_arr ) ;
		$i = 1 ;
		foreach( $class_arr as $k => $v ) {
			$uri .= "&ClassCode$i=" . $v ;
			$i ++ ;
		}
		$uri .= '&DeliveryType=' . 'COM' . '&ShipMethod=' . 'DRP' . '&OriginationName=' . urlencode( $dbv->f( "first_name" ) . '%20' . $dbv->f( "last_name" ) ) . '&OriginationAddress1=' . urlencode( $dbv->f( "address_1" ) ) . '&OriginationCity=' . urlencode( $dbv->f( "city" ) ) . '&OriginationState=' . urlencode( $dbv->f( "state" ) ) . '&OriginationPostal=' . $dbv->f( "zip" ) . '&OriginationCountry=' . $dbv->f( "country_2_code" ) . '&DestinationName=' . urlencode( $dbst->f( "first_name" ) . '%20' . $dbst->f( "last_name" ) ) . '&DestinationAddress1=' . urlencode( $dbst->f( "address_1" ) ) . '&DestinationCity=' . urlencode( $dbst->f( "city" ) ) . '&DestinationState=' . urlencode( $dbst->f( "state" ) ) . '&DestinationPostal=' . $dbst->f( "zip" ) . '&DestinationCountry=' . $dbst->f( "country_2_code" ) . '&Currency=' . $_SESSION['vendor_currency'] . '&TotalPackages=' . '1' . '&BoxID1=' . '1' . '&Weight1=' . $weight_total . '&WeightUnit1=' . WEIGHT_UOM . '&Length1=' . '10' . '&Width1=' . '10' . '&Height1=' . '10' . '&DimensionalUnit1=' . 'IN' . '&Packaging1=' . 'BOX' . '&Contents1=' . 'OTR' . '&Cod1=' . '0' . '&Insurance1=' . '0' . '&TotalOptions=' . '1' . '&OptionCode1=' . 'SDD' ;
		
		//Define some global vars for later use
		

		$state = array() ;
		global $state ;
		$quote = array() ;
		global $quote ;
		$quotes = array() ;
		global $quotes ;
		global $package_id ;
		global $boxID ;
		
		// funtion to handle the start elements for the XML data
		function startElement( &$Parser, &$Elem, $Attr ) {
			global $state ;
			if( ! is_array( $state ) )
				$state = array() ;
			array_push( $state, $Elem ) ;
			$states = join( ' ', $state ) ;
			//check what state we are in
			if( $states == "SHIPMENT PACKAGE" ) {
				global $package_id ;
				$package_id = $Attr['ID'] ;
			} //check what state we are in 
			elseif( $states == "SHIPMENT PACKAGE QUOTE" ) {
				global $package_id ;
				global $quote ;
				$quote = array( 'package_id' => $package_id , 'id' => $Attr['ID'] ) ;
			}
		}
		
		//funtion to parse the XML data. The routine does a series of conditional
		//checks on the data to determine where in the XML stack "we" are.
		//
		function characterData( $Parser, $Line ) {
			global $state ;
			$states = join( ' ', $state ) ;
			if( $states == "SHIPPMENT ERROR" ) {
				$error = $Line ;
			} elseif( $states == "SHIPMENT PACKAGE BOXID" ) {
				global $boxID ;
				$boxID = $Line ;
			} elseif( $states == "SHIPMENT PACKAGE QUOTE CARRIER NAME" ) {
				global $quote ;
				$quote["carrier_name"] = $Line ;
			} elseif( $states == "SHIPMENT PACKAGE QUOTE CARRIER CODE" ) {
				global $quote ;
				$quote["carrier_code"] = $Line ;
			} elseif( $states == "SHIPMENT PACKAGE QUOTE CLASS NAME" ) {
				global $quote ;
				$quote["class_name"] = $Line ;
			} elseif( $states == "SHIPMENT PACKAGE QUOTE CLASS CODE" ) {
				global $quote ;
				$quote["class_code"] = $Line ;
			} elseif( $states == "SHIPMENT PACKAGE QUOTE SERVICE NAME" ) {
				global $quote ;
				$quote["service_name"] = $Line ;
			} elseif( $states == "SHIPMENT PACKAGE QUOTE SERVICE CODE" ) {
				global $quote ;
				$quote["service_code"] = $Line ;
			} elseif( $states == "SHIPMENT PACKAGE QUOTE RATE AMOUNT" ) {
				global $quote ;
				$quote['amount'] = $Line ;
			}
		}
		
		// this function handles the end elements.
		// once encountered it sticks the quote into the hash $quotes
		// for easy access later
		function endElement( $Parser, $Elem ) {
			global $state, $vmLogger ;
			$states = join( ' ', $state ) ;
			if( $states == "SHIPMENT PACKAGE QUOTE" ) {
				global $quote ;
				global $boxID ;
				global $quotes ;
				unset( $quote['id'] ) ;
				unset( $quote['package_id'] ) ;
				// the $key is a combo of the carrier_code and service_code
				// this is the logical way to key each quote returned 
				$key = $quote['carrier_code'] . ' ' . $quote['service_code'] ;
				$quotes[$boxID][$key] = $quote ;
			}
			array_pop( $state ) ;
		}
		
		//Send the socket request with the uri/url
		$fp = fsockopen( "www.intershipper.com", 80, $errno, $errstr, 30 ) ;
		if( ! $fp ) {
			$html = "Error: $errstr ($errno)<br>\n" ;
			$error = true ;
		} else {
			//echo "<a href=\"http://".$url.$uri."\">URL</a>";
			$depth = array() ;
			fputs( $fp, "GET $uri HTTP/1.0\r\nHost: $url\r\n\r\n" ) ;
			//define the XML parsing routines/functions to call
			//based on the handler state
			$xml_parser = xml_parser_create() ;
			xml_set_element_handler( $xml_parser, "startElement", "endElement" ) ;
			xml_set_character_data_handler( $xml_parser, "characterData" ) ;
			//now lets roll through the data
			$error = false ;
			while( $data = fread( $fp, 8192 ) ) {
				
				$newdata = $data ;
				/*fsockopen returns more infomation than we'd like. here we 
				  remove the excess data. */
				$newdata = preg_replace( '/\r\n\r\n/', "", $newdata ) ;
				$newdata = preg_replace( '/HTTP.*\r\n/', "", $newdata ) ;
				$newdata = preg_replace( '/Date.*\r\n/', "", $newdata ) ;
				$newdata = preg_replace( '/Server.*\r\n/', "", $newdata ) ;
				$newdata = preg_replace( '/Via.*/', "", $newdata ) ;
				$newdata = preg_replace( '/Con.*/', "", $newdata ) ;
				$newdata = preg_replace( '/Set.*/', "", $newdata ) ;
				$newdata = preg_replace( '/\r/', "", $newdata ) ;
				$newdata = preg_replace( '/\n/', "", $newdata ) ;
				if( strstr( $newdata, "error" ) ) {
					$html = $newdata ;
					$error = true ;
				}
				/* if we properl cleaned up the XML stream/data we can now hand it off 
			  to an XML parser without error */
				if( ! xml_parse( $xml_parser, $newdata, feof( $fp ) ) ) {
					die( sprintf( "XML error: %s at line %d", xml_error_string( xml_get_error_code( $xml_parser ) ), xml_get_current_line_number( $xml_parser ) ) ) ;
				}
			}
			//clean up the parser object
			xml_parser_free( $xml_parser ) ;
		}
		
		/* Here we build a drop down menu list (as an example).
	  print_r $quotes
	  can help you debug or use the $quotes hash we built above.
	  a variety of info is included but mostly we probably want amount, carrier_name,
	  service_name. */
		$shipping_rate_id = urlencode( JRequest::getVar( "shipping_rate_id" ) ) ;
		if( ! $error ) {
			
			if( $_SESSION['auth']['show_price_including_tax'] != 1 ) {
				$taxrate = 1 ;
			} else {
				$taxrate = $this->get_shippingtax_rate() + 1 ;
			}
			$returnArr = array() ;
			while( list ( $quotedata, $boxID ) = each( $quotes ) ) {
				while( list ( $key, $bar ) = each( $boxID ) ) {
					
					$boxID[$key]['amount'] = ($boxID[$key]['amount'] / 100) * $taxrate ;
					$boxID[$key]['amount'] = number_format( $boxID[$key]['amount'], 2, '.', ' ' ) ;
					
					$shipping_rate_id = urlencode( $this->_name . "|" . $key . "|" . $boxID[$key]['service_name'] . "|" . $boxID[$key]['amount'] ) ;
					$_SESSION[$shipping_rate_id] = 1 ;
					
					$returnArr[] = array( 'shipping_rate_id' => $shipping_rate_id , 'carrier' => $boxID[$key]['carrier_name'] , 'rate_name' => $boxID[$key]['service_name'] , 'rate' => $boxID[$key]['amount'] ) ;
				}
			}
		}
		return $returnArr ;
	}
	
	function get_shipping_rate( &$d ) {
		$shipping_rate_id = JRequest::getVar( "shipping_rate_id" ) ;
		$is_arr = explode( "|", urldecode( urldecode( $shipping_rate_id ) ) ) ;
		$order_shipping = $is_arr[3] ;
		
		return $order_shipping ;
	}
	
	function get_shippingtax_rate() {
		
		if( intval( $this->params->get( 'IS_TAX_CLASS' ) ) == 0 )
			return (0) ;
		else {
			require_once (CLASSPATH . "ps_tax.php") ;
			$tax_rate = ps_tax::get_taxrate_by_id( intval( $this->params->get( 'IS_TAX_CLASS' ) ) ) ;
			return $tax_rate ;
		}
	}

}

?>