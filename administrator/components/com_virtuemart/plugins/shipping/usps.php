<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*  MODIFIED BY Corey Koltz (http://www.koltz.com)
*  Code updated to work with multiple shipping options and
*  updated for May 2007 changes by USPS.
*
* @version $Id$ 
* @author Corey Koltz
* @author  Soeren Eberhardt-Biermann
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
* using a part of the USPS Online Tools:
* = Rates and Service Selection =
*
* @copyright (C) 2005 E-Z E
*/
class plgShippingUsps extends vmShippingPlugin {
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
	function plgShippingUsps( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}

	function get_shipping_rate_list( &$d ) {
		global  $CURRENCY_DISPLAY, $mosConfig_absolute_path;
		$db = new ps_DB;
		$dbv = new ps_DB;
		$dbc = new ps_DB;

		$q  = "SELECT * FROM `#__{vm}_user_info`, `#__{vm}_country` WHERE user_info_id='" . $db->getEscaped($d["ship_to_info_id"])."' AND ( country=country_2_code OR country=country_3_code)";
		$db->query($q);
		$db->next_record();

		$q  = "SELECT * FROM #__{vm}_vendor WHERE vendor_id='".$_SESSION['ps_vendor_id']."'";
		$dbv->query($q);
		$dbv->next_record();

		$order_weight = $d['weight'];

		if($order_weight > 0) {

			//USPS Username
			$usps_username = $this->params->get('USPS_USERNAME');

			//USPS Password
			$usps_password = $this->params->get('USPS_PASSWORD');

			//USPS Server
			$usps_server = $this->params->get('USPS_SERVER');

			//USPS Path
			$usps_path = $this->params->get('USPS_PATH');

			//USPS package size
			$usps_packagesize = $this->params->get('USPS_PACKAGESIZE');

			//USPS Package ID
			$usps_packageid = 0;

			//USPS International Per Pound Rate
			$usps_intllbrate = $this->params->get('USPS_INTLLBRATE');

			//USPS International handling fee
			$usps_intlhandlingfee = $this->params->get('USPS_INTLHANDLINGFEE');

			//Pad the shipping weight to allow weight for shipping materials
			$usps_padding = USPS_PADDING;
			$usps_padding = $usps_padding * 0.01;
			$order_weight = ($order_weight * $usps_padding) + $order_weight;
			
			//USPS Machinable for Parcel Post
			$usps_machinable = $this->params->get('USPS_MACHINABLE');
			if ($usps_machinable == '1') $usps_machinable = 'TRUE';
			else $usps_machinable = 'FALSE';
			
			//USPS Shipping Options to display
			for( $i=0; $i <= 10; $i++ ) {
				if ($this->params->get('USPS_SHIP'.$i) == '1') $usps_ship[$i] = 'TRUE';
				else $usps_ship[$i] = 'FALSE';
			}
			for( $i=0; $i <= 8; $i++ ) {
				if ($this->params->get('USPS_INTL'.$i) == '1') $usps_intl[$i] = 'TRUE';
				else $usps_intl[$i] = 'FALSE';
			}
			//Title for your request
			$request_title = "Shipping Estimate";

			//The zip that you are shipping from
			$source_zip = substr($dbv->f("vendor_zip"),0,5);

			$shpService = 'All'; //"Priority";
			
			//The zip that you are shipping to
			$dest_country = $db->f("country_2_code");
			if ($dest_country == "GB") {
				$q  = "SELECT state_name FROM #__{vm}_state WHERE state_2_code='".$db->f("state")."'";
				$dbc->query($q);
				$dbc->next_record();
				$dest_country_name = $dbc->f("state_name");
			}
			else {
				$dest_country_name = $db->f("country_name");
			}
			$dest_state = $db->f("state");
			$dest_zip = substr($db->f("zip"),0,5);
			//$weight_measure
	        if ($order_weight < 1) { 
                      $shipping_pounds_intl = 0;
            } else {
                      $shipping_pounds_intl = ceil ($order_weight);
            }
			if ($order_weight < 0.88) {
				$shipping_pounds = 0;
				$shipping_ounces = round(16 * ($order_weight - floor($order_weight)));
			}
			else	{
				$shipping_pounds = ceil ($order_weight);
				$shipping_ounces = 0;
			}

			$os = array("Mac", "NT", "Irix", "Linux");
			$states = array("AL","AK","AR","AZ","CA","CO","CT","DC","DE","FL","GA","HI","IA","ID","IL","IN","KS","KY","LA","MA","MD","ME","MI","MN","MO","MS","MT","NC","ND","NE","NH","NJ","NM","NV","NY","OH","OK","OR","PA","RI","SC","SD","TN","TX","UT","VT","VA","WA","WI","WV","WY");
			//If weight is over 70 pounds, round down to 70 for now.
			//Will update in the future to be able to split the package or something?
			if( $order_weight > 70.00 ) {
				echo "We are unable to ship USPS as the package weight exceeds the 70 pound limit,<br>please select another shipping method.";
			}
			else 	{
			if( ( $dest_country == "US") && in_array($dest_state,$states) )	{
				/******START OF DOMESTIC RATE******/
				//the xml that will be posted to usps
				$xmlPost = 'API=RateV2&XML=<RateV2Request USERID="'.$usps_username.'" PASSWORD="'.$usps_password.'">';
				$xmlPost .= '<Package ID="'.$usps_packageid.'">';
				$xmlPost .= "<Service>".$shpService."</Service>";
				$xmlPost .= "<ZipOrigination>".$source_zip."</ZipOrigination>";
				$xmlPost .= "<ZipDestination>".$dest_zip."</ZipDestination>";
				$xmlPost .= "<Pounds>".$shipping_pounds."</Pounds>";
				$xmlPost .= "<Ounces>".$shipping_ounces."</Ounces>";
				$xmlPost .= "<Size>".$usps_packagesize."</Size>";
				$xmlPost .= "<Machinable>".$usps_machinable."</Machinable>";
				$xmlPost .= "</Package></RateV2Request>";

				// echo htmlentities( $xmlPost );
				$host = $usps_server;
				//$host = "production.shippingapis.com";
				$path = $usps_path; //"/ups.app/xml/Rate";
				//$path = "/ShippingAPI.dll";
				$port = 80;
				$protocol = "http";
				
				$html = "";
				
				//echo "<textarea>".$protocol."://".$host.$path."?API=Rate&XML=".$xmlPost."</textarea>";
				// Using cURL is Up-To-Date and easier!!
				if( function_exists( "curl_init" )) {
					$CR = curl_init();
					curl_setopt($CR, CURLOPT_URL, $protocol."://".$host.$path); //"?API=RateV2&XML=".$xmlPost);
					curl_setopt($CR, CURLOPT_POST, 1);
					curl_setopt($CR, CURLOPT_FAILONERROR, true);
					curl_setopt($CR, CURLOPT_POSTFIELDS, $xmlPost);
					curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);

					$xmlResult = curl_exec( $CR );


					$error = curl_error( $CR );
					if( !empty( $error )) {
						$vmLogger->err( curl_error( $CR ) );
						$vmLogger->info( JText::_('VM_INTERNAL_ERROR')." USPS.com" );
						$error = true;
					}
					else {
						/* XML Parsing */
						require_once( $mosConfig_absolute_path. '/includes/domit/xml_domit_lite_include.php' );
						$xmlDoc = new DOMIT_Lite_Document();
						$xmlDoc -> parseXML( $xmlResult, false, true );
						/* Let's check wether the response from USPS is Success or Failure ! */
						if( strstr( $xmlResult, "Error" ) ) {
							$error = true;
							
							$error_code = $xmlDoc->getElementsByTagName( "Number" );
							$error_code = $error_code->item(0);
							$error_code = $error_code->getText();
							$message = JText::_('VM_ERROR_CODE').": ".$error_code.". ";

							$error_desc = $xmlDoc->getElementsByTagName( "Description" );
							$error_desc = $error_desc->item(0);
							$error_desc = $error_desc->getText();
							$message .= JText::_('VM_ERROR_DESC').": ".$error_desc;
							$vmLogger->err( $message );
						}
					}
					curl_close( $CR );
				}
				else {
					$protocol = "http";
					$fp = fsockopen($protocol."://".$host, $errno, $errstr, $timeout = 60);
					if( !$fp ) {
						$error = true;
						$vmLogger->debug( JText::_('VM_INTERNAL_ERROR').": $errstr ($errno)");
					}
					else {
						//send the server request
						fputs($fp, "POST $path HTTP/1.1\r\n");
						fputs($fp, "Host: $host\r\n");
						fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
						fputs($fp, "Content-length: ".strlen($xmlPost)."\r\n");
						fputs($fp, "Connection: close\r\n\r\n");
						fputs($fp, $xmlPost . "\r\n\r\n");

						$xmlResult = '';
						while(!feof($fp)) {
							$xmlResult .= fgets($fp, 4096);
						}
						if( stristr( $xmlResult, "Success" )) {
							/* XML Parsing */
							require_once( $mosConfig_absolute_path. '/includes/domit/xml_domit_lite_include.php' );
							$xmlDoc =& new DOMIT_Lite_Document();
							$xmlDoc->parseXML( $xmlResult, false, true );
							$error = false;
							
						}
						else {
							$vmLogger->err("Error processing the Request to USPS.com");
							$error = true;
						}
					}

				}
				if( $error ) {
					// comment out, if you don't want the Errors to be shown!!
					//$vmLogger->err( $html );
					// Switch to StandardShipping on Error !!!
					//require_once( ADMINPATH . 'plugins/shipping/standard_shipping.php' );
					//$shipping =& new standard_shipping();
					//$shipping->list_rates( $d );
					$vmLogger->debug("We are unable to ship USPS as the there was an error,<br> please select another shipping method.");
					return false;
				}
				// Domestic shipping - add how long it might take
				$ship_commit[0]="1 - 2 Days";
				$ship_commit[1]="1 - 2 Days";
				$ship_commit[2]="1 - 2 Days";
				$ship_commit[3]="1 - 3 Days";
				$ship_commit[4]="1 - 3 Days";
				$ship_commit[5]="1 - 3 Days";
				$ship_commit[6]="2 - 9 Days";
				$ship_commit[7]="2 - 9 Days";
				$ship_commit[8]="2 - 9 Days";
				$ship_commit[9]="2 - 9 Days";
				$ship_commit[10]="2 Days or More";
				
				// retrieve the service and postage items
				$i = 0;
				if ($order_weight > 15) {
					$count = 8;
					$usps_ship[6] = $usps_ship[7];
					$usps_ship[7] = $usps_ship[9];
					$usps_ship[8] = $usps_ship[10];	
					}			
				else if ($order_weight >= 0.86) {
					$count = 9;
					$usps_ship[6] = $usps_ship[7];
					$usps_ship[7] = $usps_ship[8];
					$usps_ship[8] = $usps_ship[9];
					$usps_ship[9] = $usps_ship[10];
				}
				else {
					$count = 10;

				}
				while ($i <= $count) {
				if( isset( $xmlDoc)) {
					$ship_service[$i] = $xmlDoc->getElementsByTagName( 'MailService' );
					$ship_service[$i] = $ship_service[$i]->item($i);
					$ship_service[$i] = $ship_service[$i]->getText();

					$ship_postage[$i] = $xmlDoc->getElementsByTagName( 'Rate' );
					$ship_postage[$i] = $ship_postage[$i]->item($i);
					$ship_postage[$i] = $ship_postage[$i]->getText();
					if (preg_match('/%$/',$this->params->get('USPS_HANDLINGFEE'))) {
					  $ship_postage[$i] = $ship_postage[$i] * (1+substr($this->params->get('USPS_HANDLINGFEE'),0,-1)/100);
					} else {
					  $ship_postage[$i] = $ship_postage[$i] + $this->params->get('USPS_HANDLINGFEE');
					}


				$i++;

				}
				}
				/******END OF DOMESTIC RATE******/
			}
			else	{
				/******START INTERNATIONAL RATE******/
				//the xml that will be posted to usps
				$xmlPost = 'API=IntlRate&XML=<IntlRateRequest USERID="'.$usps_username.'" PASSWORD="'.$usps_password.'">';
				$xmlPost .= '<Package ID="'.$usps_packageid.'">';
				$xmlPost .= "<Pounds>".$shipping_pounds_intl."</Pounds>";
				$xmlPost .= "<Ounces>".$shipping_ounces."</Ounces>";
				$xmlPost .= "<MailType>Package</MailType>";
				$xmlPost .= "<Country>".$dest_country_name."</Country>";
				$xmlPost .= "</Package></IntlRateRequest>";

				// echo htmlentities( $xmlPost );
				$host = $usps_server;
				//$host = "production.shippingapis.com";
				$path = $usps_path; //"/ups.app/xml/Rate";
				//$path = "/ShippingAPI.dll";
				$port = 80;
				$protocol = "http";

				//echo "<textarea>".$protocol."://".$host.$path."?API=Rate&XML=".$xmlPost."</textarea>";
				// Using cURL is Up-To-Date and easier!!
				if( function_exists( "curl_init" )) {
					$CR = curl_init();
					curl_setopt($CR, CURLOPT_URL, $protocol."://".$host.$path); //"?API=RateV2&XML=".$xmlPost);
					curl_setopt($CR, CURLOPT_POST, 1);
					curl_setopt($CR, CURLOPT_FAILONERROR, true);
					curl_setopt($CR, CURLOPT_POSTFIELDS, $xmlPost);
					curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);


					$xmlResult = curl_exec( $CR );
					//echo "<textarea>".$xmlResult."</textarea>";
					$error = curl_error( $CR );
					if( !empty( $error )) {
						$vmLogger->err( curl_error( $CR ) );
						$vmLogger->info(JText::_('VM_INTERNAL_ERROR')." USPS.com" );
						$error = true;
					}
					else {
						/* XML Parsing */
						require_once( $mosConfig_absolute_path. '/includes/domit/xml_domit_lite_include.php' );
						$xmlDoc = new DOMIT_Lite_Document();
						$xmlDoc->parseXML( $xmlResult, false, true );

						/* Let's check wether the response from USPS is Success or Failure ! */
						if( strstr( $xmlResult, "Error" ) ) {
							$error = true;
							$error_code = $xmlDoc->getElementsByTagName( "Number" );
							$error_code = $error_code->item(0);
							$error_code = $error_code->getText();
							$message = JText::_('VM_ERROR_CODE').": ".$error_code.". ";

							$error_desc = $xmlDoc->getElementsByTagName( "Description" );
							$error_desc = $error_desc->item(0);
							$error_desc = $error_desc->getText();
							$message .= JText::_('VM_ERROR_DESC').": ".$error_desc.".";
							$vmLogger->debug ($message );

						}

					}
					curl_close( $CR );

				}
				else {
					$protocol = "http";
					$fp = fsockopen($protocol."://".$host, $errno, $errstr, $timeout = 60);
					if( !$fp ) {
						$error = true;
						$vmLogger->debug( JText::_('VM_INTERNAL_ERROR').": $errstr ($errno)");
					}
					else {
						//send the server request
						fputs($fp, "POST $path HTTP/1.1\r\n");
						fputs($fp, "Host: $host\r\n");
						fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
						fputs($fp, "Content-length: ".strlen($xmlPost)."\r\n");
						fputs($fp, "Connection: close\r\n\r\n");
						fputs($fp, $xmlPost . "\r\n\r\n");

						$xmlResult = '';
						while(!feof($fp)) {
							$xmlResult .= fgets($fp, 4096);
						}
						if( stristr( $xmlResult, "Success" )) {
							/* XML Parsing */
							require_once( $mosConfig_absolute_path. '/includes/domit/xml_domit_lite_include.php' );
							$xmlDoc = new DOMIT_Lite_Document();
							$xmlDoc->parseXML( $xmlResult, false, true );
							$error = false;
						}
						else {
							$vmLogger->info("Error processing the Request to USPS.com");
							$error = true;
						}
					}

				}

				if( $error ) {

					$vmLogger->info( "We are unable to ship USPS as there was an error,<br> please select another shipping method.");
					return false;
				}
				// retrieve the service and postage items
				$i = 0;
				$numChildren = 0;
				$numChildren = $xmlDoc->documentElement->firstChild->childCount;
				$numChildren = ($numChildren - 7);  // this line removes the preceeding 6 lines of crap not needed plus 1 to make up for the $i starting at 0
				while ($i <= $numChildren) {
					if( isset( $xmlDoc)) {
						$ship_service[$i] = $xmlDoc->getElementsByTagName( "SvcDescription" );
						$ship_service[$i] = $ship_service[$i]->item($i);
						$ship_service[$i] = $ship_service[$i]->getText();
						
						$ship_weight[$i] = $xmlDoc->getElementsByTagName( "MaxWeight" );
						$ship_weight[$i] = $ship_weight[$i]->item($i);
						$ship_weight[$i] = $ship_weight[$i]->getText($i);
					}
					$i++;
				}
				// retrieve postage for countries that support all nine shipping methods and weights
				$ship_weight[8] = ($ship_weight[8]/16);
				if ( $order_weight <= $ship_weight[0] && $ship_weight[1] && $ship_weight[2] && $ship_weight[3] && $ship_weight[4] && $ship_weight[5] && $ship_weight [6] && $ship_weight[7] && $ship_weight[8] ) {
						$count = 8;
					}
				// retrieve postage for countries that support eight of the nine shipping methods and weights
				elseif ( $order_weight <= $ship_weight[0] && $ship_weight[1] && $ship_weight[2] && $ship_weight[3] && $ship_weight[4] && $ship_weight[5] && $ship_weight [6] && $ship_weight[7] ) {
						$count = 7;
						// $usps_intl[6] = $usps_intl[7];
					}
				// retrieve postage for countries that support seven of the nine shipping methods and weights
				elseif ( $order_weight <= $ship_weight[0] && $ship_weight[1] && $ship_weight[2] && $ship_weight[3] && $ship_weight[4] && $ship_weight[5] && $ship_weight [6] ) {
						$count = 6;
					}	
				// retrieve postage for countries that support six of the nine shipping methods and weights
				elseif ( $order_weight <= $ship_weight[0] && $ship_weight[1] && $ship_weight[2] && $ship_weight[3] && $ship_weight[4] && $ship_weight[5] ) {
						$count = 5;
					}		
				// retrieve postage for countries that support five of the nine shipping methods and weights
				elseif ( $order_weight <= $ship_weight[0] && $ship_weight[1] && $ship_weight[2] && $ship_weight[3] && $ship_weight[4] ) {
						$count = 4;
					}	
				// retrieve postage for countries that support four of the nine shipping methods and weights
				elseif ( $order_weight <= $ship_weight[0] && $ship_weight[1] && $ship_weight[2] && $ship_weight[3] ) {
						$count = 3;
					}		
				// retrieve postage for countries that support three of the nine shipping methods and weights 
				elseif ( $order_weight <= $ship_weight[0] && $ship_weight[1] && $ship_weight[2] ) {
						$count = 2;
					}		
				// retrieve postage for countries that support two of the nine shipping methods and weights 
				elseif ( $order_weight <= $ship_weight[0] && $ship_weight[1] ) {
						$count = 1;
					}
				// retrieve postage for countries that support one of the nine shipping methods and weights 
				elseif ( $order_weight <= $ship_weight[0] ) {
						$count = 0;
					}
				else { 
					$vmLogger->info("We are unable to ship USPS as the package weight exceeds what your country allows, please select another shipping method.");
				}
				$i = 0;
				while ($i <= $numChildren) {
				if( isset( $xmlDoc)) {
					$ship_service[$i] = $xmlDoc->getElementsByTagName( "SvcDescription" );
					$ship_service[$i] = $ship_service[$i]->item($i);
					$ship_service[$i] = $ship_service[$i]->getText();
					
					$ship_commit[$i] = $xmlDoc->getElementsByTagName( "SvcCommitments");
					$ship_commit[$i] = $ship_commit[$i]->item($i);
					$ship_commit[$i] = $ship_commit[$i]->getText();

					$ship_postage[$i] = $xmlDoc->getElementsByTagName( "Postage" );
					$ship_postage[$i] = $ship_postage[$i]->item($i);
					$ship_postage[$i] = $ship_postage[$i]->getText($i);
					$ship_postage[$i] = $ship_postage[$i] + USPS_INTLHANDLINGFEE;
				$i++;
				}
				/******END INTERNATIONAL RATE******/
			}
			}
			$i = 0;
			while ($i <= $count) {
				$returnArr = array();
				// USPS returns Charges in USD.
				$charge[$i] = $ship_postage[$i];
	
				$shipping_rate_id = urlencode($this->_name."|USPS|".$ship_service[$i]."|".$charge[$i]);	
				$_SESSION[$shipping_rate_id] = 1;

				$delivery_date = '';
				if ($this->params->get('USPS_SHOW_DELIVERY_QUOTE') == 1) {
					$delivery_date = $ship_commit[$i];
				}
				if( ($dest_country_name == "United States" && $usps_ship[$i] == "TRUE")
					|| ($dest_country_name != "United States" && $usps_intl[$i] == "TRUE")) {
					$returnArr[] = array('shipping_rate_id' => $shipping_rate_id,
													'carrier' => 'USPS',
													'rate_name' => $ship_service[$i],
													'rate' => $charge[$i],
													'delivery_date' => $delivery_date
												);
				}
				$i++;

			}
		}
		}
		return true;
	} //end function list_rates


	function get_shipping_rate( &$d ) {

		$shipping_rate_id = $d["shipping_rate_id"];
		$is_arr = explode("|", urldecode(urldecode($shipping_rate_id)) );
		$order_shipping = (float)$is_arr[3];

		return $order_shipping;

	} //end function get_shipping_rate


	function get_shippingtax_rate() {

		if( intval($this->params->get('USPS_TAX_CLASS'))== 0 )
		return( 0 );
		else {
			require_once( CLASSPATH. "ps_tax.php" );
			$tax_rate = ps_tax::get_taxrate_by_id( intval($this->params->get('USPS_TAX_CLASS')) );
			return $tax_rate;
		}
	}

}
?>
