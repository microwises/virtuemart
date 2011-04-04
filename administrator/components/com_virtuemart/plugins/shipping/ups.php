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
* This is the Shipping class for 
* using a part of the UPS Online(R) Tools:
* = Rates and Service Selection =
*
* UPS OnLine(R) is a registered trademark of United Parcel Service of America. 
*
*/
class plgShippingUps extends vmShippingPlugin {
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
	function plgShippingUps( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}

	function get_shipping_rate_list( &$d ) {
		global $vendor_country_2_code, $vendor_currency, $vmLogger;
		global  $CURRENCY_DISPLAY, $mosConfig_absolute_path;
		$db =& new ps_DB;
		$dbv =& new ps_DB;

		$cart = $_SESSION['cart'];

		$q  = "SELECT * FROM #__{vm}_user_info, #__{vm}_country WHERE user_info_id='" . $d["ship_to_info_id"]."' AND ( country=country_2_code OR country=country_3_code)";
		$db->query($q);

		$q  = "SELECT * FROM #__{vm}_vendor WHERE vendor_id='".$_SESSION['ps_vendor_id']."'";
		$dbv->query($q);
		$dbv->next_record();

		$order_weight = $d['weight'];
		$html = "";
		if($order_weight > 0) {

			if( $order_weight < 1 ) {
				$order_weight = 1;
			}
			if( $order_weight > 150.00 ) {
				$order_weight = 150.00;
			}
			//Access code for online tools at ups.com
			$ups_access_code = $this->params->get('UPS_ACCESS_CODE');

			//Username from registering for online tools at ups.com
			$ups_user_id = $this->params->get('UPS_USER_ID');

			//Password from registering for online tools at ups.com
			$ups_user_password = $this->params->get('UPS_PASSWORD');

			//Title for your request
			$request_title = "Shipping Estimate";

			//The zip that you are shipping from
			// Add ability to override vendor zip code as source ship from...
			if ($this->params->get('OVERRIDE_SOURCE_ZIP') != '') {
				$source_zip = $this->params->get('OVERRIDE_SOURCE_ZIP');
			}
			else {
				$source_zip = $dbv->f("vendor_zip");
			}

			//The zip that you are shipping to
			$dest_country = $db->f("country_2_code");
			$dest_zip = substr($db->f("zip"), 0, 5); // Make sure the ZIP is 5 chars long

			//LBS  = Pounds
			//KGS  = Kilograms
			$weight_measure = (WEIGHT_UOM == 'KG') ? "KGS" : "LBS";

			// The XML that will be posted to UPS
			$xmlPost  = "<?xml version=\"1.0\"?>";
			$xmlPost .= "<AccessRequest xml:lang=\"en-US\">";
			$xmlPost .= " <AccessLicenseNumber>".$ups_access_code."</AccessLicenseNumber>";
			$xmlPost .= " <UserId>".$ups_user_id."</UserId>";
			$xmlPost .= " <Password>".$ups_user_password."</Password>";
			$xmlPost .= "</AccessRequest>";
			$xmlPost .= "<?xml version=\"1.0\"?>";
			$xmlPost .= "<RatingServiceSelectionRequest xml:lang=\"en-US\">";
			$xmlPost .= " <Request>";
			$xmlPost .= "  <TransactionReference>";
			$xmlPost .= "  <CustomerContext>".$request_title."</CustomerContext>";
			$xmlPost .= "  <XpciVersion>1.0001</XpciVersion>";
			$xmlPost .= "  </TransactionReference>";
			$xmlPost .= "  <RequestAction>rate</RequestAction>";
			$xmlPost .= "  <RequestOption>shop</RequestOption>";
			$xmlPost .= " </Request>";
			$xmlPost .= " <PickupType>";
			$xmlPost .= "  <Code>".$this->params->get('UPS_PICKUP_TYPE')."</Code>";
			$xmlPost .= " </PickupType>";
			$xmlPost .= " <Shipment>";
			$xmlPost .= "  <Shipper>";
			$xmlPost .= "   <Address>";
			$xmlPost .= "    <PostalCode>".$source_zip."</PostalCode>";
			$xmlPost .= "    <CountryCode>$vendor_country_2_code</CountryCode>";
			$xmlPost .= "   </Address>";
			$xmlPost .= "  </Shipper>";
			$xmlPost .= "  <ShipTo>";
			$xmlPost .= "   <Address>";
			$xmlPost .= "    <PostalCode>".$dest_zip."</PostalCode>";
			$xmlPost .= "    <CountryCode>$dest_country</CountryCode>";
			if( $this->params->get('UPS_RESIDENTIAL')=="yes" ) {
				$xmlPost .= "    <ResidentialAddressIndicator/>";
			}
			$xmlPost .= "   </Address>";
			$xmlPost .= "  </ShipTo>";
			$xmlPost .= "  <ShipFrom>";
			$xmlPost .= "   <Address>";
			$xmlPost .= "    <PostalCode>".$source_zip."</PostalCode>";
			$xmlPost .= "    <CountryCode>$vendor_country_2_code</CountryCode>";
			$xmlPost .= "   </Address>";
			$xmlPost .= "  </ShipFrom>";

			// Service is only required, if the Tag "RequestOption" contains the value "rate"
			// We don't want a specific servive, but ALL Rates
			//$xmlPost .= "  <Service>";
			//$xmlPost .= "   <Code>".$shipping_type."</Code>";
			//$xmlPost .= "  </Service>";

			$xmlPost .= "  <Package>";
			$xmlPost .= "   <PackagingType>";
			$xmlPost .= "    <Code>".$this->params->get('UPS_PACKAGE_TYPE')."</Code>";
			$xmlPost .= "   </PackagingType>";
			$xmlPost .= "   <PackageWeight>";
			$xmlPost .= "    <UnitOfMeasurement>";
			$xmlPost .= "     <Code>".$weight_measure."</Code>";
			$xmlPost .= "    </UnitOfMeasurement>";
			$xmlPost .= "    <Weight>".$order_weight."</Weight>";
			$xmlPost .= "   </PackageWeight>";
			$xmlPost .= "  </Package>";
			$xmlPost .= " </Shipment>";
			$xmlPost .= "</RatingServiceSelectionRequest>";

			// echo htmlentities( $xmlPost );
			$upsURL = "https://www.ups.com:443/ups.app/xml/Rate";
			require_once(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'connection.php');

			$error = false;

			$xmlResult = VmConnector::handleCommunication($upsURL, $xmlPost );

			if( !$xmlResult) {
				$vmLogger->err( JText::_('COM_VIRTUEMART_INTERNAL_ERROR',false)." UPS.com" );
				$error = true;
			}
			else {
				/* XML Parsing */
				require_once( $mosConfig_absolute_path. '/includes/domit/xml_domit_lite_include.php' );
				$xmlDoc =& new DOMIT_Lite_Document();
				$xmlDoc->parseXML( $xmlResult, false, true );

				/* Let's check wether the response from UPS is Success or Failure ! */
				if( strstr( $xmlResult, "Failure" ) ) {
					$error = true;
					$error_code = $xmlDoc->getElementsByTagName( "ErrorCode" );
					$error_code = $error_code->item(0);
					$error_code = $error_code->getText();

					$error_desc = $xmlDoc->getElementsByTagName( "ErrorDescription" );
					$error_desc = $error_desc->item(0);
					$error_desc = $error_desc->getText();

					$vmLogger->err( JText::_('COM_VIRTUEMART_UPS_RESPONSE_ERROR',false).'. '
					. JText::_('COM_VIRTUEMART_ERROR_CODE').": ".$error_code .', '
					. JText::_('COM_VIRTUEMART_ERROR_DESC').": ".$error_desc);
				}

			}

			if( $error ) {
				return false;
			}
			// retrieve the list of all "RatedShipment" Elements
			$rate_list =& $xmlDoc->getElementsByTagName( "RatedShipment" );
			$allservicecodes = array("UPS_Next_Day_Air",
														"UPS_2nd_Day_Air",
														"UPS_Ground",
														"UPS_Worldwide_Express_SM",
														"UPS_Worldwide_Expedited_SM",
														"UPS_Standard",
														"UPS_3_Day_Select",
														"UPS_Next_Day_Air_Saver",
														"UPS_Next_Day_Air_Early_AM",
														"UPS_Worldwide_Express_Plus_SM",
														"UPS_2nd_Day_Air_AM",
														"UPS_Saver",
														"na");
			$myservicecodes = array();
			foreach ($allservicecodes as $servicecode){
				if ($this->params->get($servicecode) != '') {
					$myservicecodes[] = $this->params->get($servicecode);
				}
			}
			if (DEBUG){
				echo "Cart Contents: ".$order_weight. " ".$weight_measure."<br><br>\n";
				echo "XML Post: <br>";
				echo "<textarea cols='80'>".$xmlPost."</textarea>";
				echo "<br>";
				echo "XML Result: <br>";
				echo "<textarea cols='80' rows='10'>".$xmlResult."</textarea>";
				echo "<br>";
			}
			$returnArr = array();
			
			// Loop through the rate List
			for ($i = 0; $i < $rate_list->getLength(); $i++) {
				$currNode =& $rate_list->item($i);
				if ( in_array($currNode->childNodes[0]->getText(),$myservicecodes) )  {
					$e = 0;
					// First Element: Service Code
					$shipment[$i]["ServiceCode"] = $currNode->childNodes[$e++]->getText();

					// Second Element: BillingWeight
					if( $currNode->childNodes[$e]->nodeName == 'RatedShipmentWarning') {
						$e++;
					}
					$shipment[$i]["BillingWeight"] = $currNode->childNodes[$e++];

					// Third Element: TransportationCharges
					$shipment[$i]["TransportationCharges"] = $currNode->childNodes[$e++];
					$shipment[$i]["TransportationCharges"] = $shipment[$i]["TransportationCharges"]->getElementsByTagName("MonetaryValue");
					$shipment[$i]["TransportationCharges"] = $shipment[$i]["TransportationCharges"]->item(0);
					if( is_object( $shipment[$i]["TransportationCharges"]) ) {
						$shipment[$i]["TransportationCharges"] = $shipment[$i]["TransportationCharges"]->getText();
					}

					// Fourth Element: ServiceOptionsCharges
					$shipment[$i]["ServiceOptionsCharges"] = $currNode->childNodes[$e++];

					// Fifth Element: TotalCharges
					$shipment[$i]["TotalCharges"] = $currNode->childNodes[$e++];

					// Sixth Element: GuarenteedDaysToDelivery
					$shipment[$i]["GuaranteedDaysToDelivery"] = $currNode->childNodes[$e++]->getText();

					// Seventh Element: ScheduledDeliveryTime
					$shipment[$i]["ScheduledDeliveryTime"] = $currNode->childNodes[$e++]->getText();

					// Eighth Element: RatedPackage
					$shipment[$i]["RatedPackage"] = $currNode->childNodes[$e++];

					// map ServiceCode to ServiceName
					switch( $shipment[$i]["ServiceCode"] ) {

						case "01": $shipment[$i]["ServiceName"] = "UPS Next Day Air"; break;
						case "02": $shipment[$i]["ServiceName"] = "UPS 2nd Day Air"; break;
						case "03": $shipment[$i]["ServiceName"] = "UPS Ground"; break;
						case "07": $shipment[$i]["ServiceName"] = "UPS Worldwide Express SM"; break;
						case "08": $shipment[$i]["ServiceName"] = "UPS Worldwide Expedited SM"; break;
						case "11": $shipment[$i]["ServiceName"] = "UPS Standard"; break;
						case "12": $shipment[$i]["ServiceName"] = "UPS 3 Day Select"; break;
						case "13": $shipment[$i]["ServiceName"] = "UPS Next Day Air Saver"; break;
						case "14": $shipment[$i]["ServiceName"] = "UPS Next Day Air Early A.M."; break;
						case "54": $shipment[$i]["ServiceName"] = "UPS Worldwide Express Plus SM"; break;
						case "59": $shipment[$i]["ServiceName"] = "UPS 2nd Day Air A.M."; break;
						case "64": $shipment[$i]["ServiceName"] = "n/a"; break;
						case "65": $shipment[$i]["ServiceName"] = "UPS Saver"; break;

					}
					unset( $currNode );
				}
			}
			if (!$shipment ) {
				//$vmLogger->err( "Error processing the Request to UPS.com" );
				/*$vmLogger->err( "We could not find a UPS shipping rate.
				Please make sure you have entered a valid shipping address.
				Or choose a rate below." );
				// Switch to StandardShipping on Error !!!
				require_once(ADMINPATH . 'plugins/shipping/standard_shipping.php' );
				$shipping =& new standard_shipping();
				$shipping->list_rates( $d );*/
				return;
			}

			// UPS returns Charges in USD ONLY.
			// So we have to convert from USD to Vendor Currency if necessary
			if( $_SESSION['vendor_currency'] != "USD" ) {
				$convert = true;
			}
			else {
				$convert = false;
			}

			if ( $_SESSION['auth']['show_price_including_tax'] != 1 ) {
				$taxrate = 1;
			}
			else {
				$taxrate = $this->get_shippingtax_rate() + 1;
			}

			foreach( $shipment as $key => $value ) {

				//Get the Fuel SurCharge rate, defined in config.
				$fsc = $value['ServiceName']."_FSC";
				$fsc = str_replace(" ","_",str_replace(".","",str_replace("/","",$fsc)));
				$fsc = $this->params->get($fsc);
				if( $fsc == 0 ) {
					$fsc_rate = 1;
				} else {
					$fsc_rate = $fsc / 100;
					$fsc_rate = $fsc_rate + 1;
				}


				if( $convert ) {
					$tmp = $GLOBALS['CURRENCY']->convert( $value['TransportationCharges'], "USD", $vendor_currency );

					// tmp is empty when the Vendor Currency could not be converted!!!!
					if( !empty( $tmp )) {
						$charge = $tmp;
						// add Fuel SurCharge
						$charge *= $fsc_rate;
						// add Handling Fee
						$charge += $this->params->get('UPS_HANDLING_FEE');
						$charge *= $taxrate;
						$value['TransportationCharges'] = $CURRENCY_DISPLAY->getFullValue($tmp);
					}
					// So let's show the value in $$$$
					else {
						$charge = $value['TransportationCharges'] + intval( $this->params->get('UPS_HANDLING_FEE') );
						// add Fuel SurCharge
						$charge *= $fsc_rate;
						// add Handling Fee
						$charge += $this->params->get('UPS_HANDLING_FEE');
						$charge *= $taxrate;
						$value['TransportationCharges'] = $value['TransportationCharges']. " USD";
					}

				}
				else {
					$charge = $charge_unrated = $value['TransportationCharges'];
					// add Fuel SurCharge
					$charge *= $fsc_rate;
					// add Handling Fee
					$charge += $this->params->get('UPS_HANDLING_FEE');
					$charge *= $taxrate;
					$value['TransportationCharges'] = $CURRENCY_DISPLAY->getFullValue($charge);
				}
				
				$shipping_rate_id = urlencode($this->_name."|UPS|".$value['ServiceName']."|".$charge);
				$_SESSION[$shipping_rate_id] = 1;
				
				if (DEBUG) {
					$value['ServiceName'] .= " - ".JText::_('COM_VIRTUEMART_PRODUCT_FORM_WEIGHT').": ".$order_weight." ". $weight_measure.
					", ".JText::_('COM_VIRTUEMART_RATE_FORM_VALUE').": [[".$charge_unrated."(".$fsc_rate.")]+".UPS_HANDLING_FEE."](".$taxrate.")]";
				}
				
				$rateArr = array('shipping_rate_id' => $shipping_rate_id,
													'carrier' => 'UPS',
													'rate_name' => $value['ServiceName'],
													'rate' => $charge
												);
				

				// DELIVERY QUOTE
				if ($this->params->get('SHOW_DELIVERY_DAYS_QUOTE') == 1) {
					if( !empty($value['GuaranteedDaysToDelivery'])) {
						$rateArr['rate_name'] .= "&nbsp;&nbsp;-&nbsp;&nbsp;".$value['GuaranteedDaysToDelivery']." ".JText::_('COM_VIRTUEMART_UPS_SHIPPING_GUARANTEED_DAYS');
					}
				}
				if ($this->params->get('SHOW_DELIVERY_ETA_QUOTE') == 1) {
					if( !empty($value['ScheduledDeliveryTime'])) {
						$rateArr['delivery_time'] = $value['ScheduledDeliveryTime'];
					}
				}
				if ($this->params->get('SHOW_DELIVERY_WARNING') == 1 && !empty($value['RatedShipmentWarning'])) {
					$rateArr['rate_tip'] = $value['RatedShipmentWarning'];
				}
				$returnArr[] = $rateArr;
			}
		}
		return $returnArr;
	}

	function get_shipping_rate( &$d ) {

		$shipping_rate_id = $d["shipping_rate_id"];
		$is_arr = explode("|", urldecode(urldecode($shipping_rate_id)) );
		$order_shipping = $is_arr[3];

		return $order_shipping;

	}

	function get_shippingtax_rate() {

		if( intval($this->params->get('UPS_TAX_CLASS'))== 0 ) {
			return( 0 );
		}
		else {
			require_once( CLASSPATH. "ps_tax.php" );
			$tax_rate = ps_tax::get_taxrate_by_id( intval($this->params->get('UPS_TAX_CLASS')) );
			return $tax_rate;
		}
	}

}

?>