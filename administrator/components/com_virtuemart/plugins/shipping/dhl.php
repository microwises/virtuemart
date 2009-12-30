<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
 *
 * @version $Id: dhl.php 1760 2009-05-03 22:58:57Z Aravot $
 * @package VirtueMart
 * @subpackage shipping
 *
 */

/*
 * This is the Shipping class for DHL ShipIT and RateIT tools.
 */
class plgShippingDhl extends vmShippingPlugin {
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
	function plgShippingDhl( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}

	function get_shipping_rate_list(&$d) {
		global $vmLogger;
		global  $CURRENCY_DISPLAY, $mosConfig_absolute_path;

		/*
		 * Check the current day and time to determine if it is too late to
		 * ship today.  This will have impact on the Saturday delivery
		 * option and the ship date XML field.
		 */
		$cur_timestamp = time();
		$cur_day_of_week = date('D', $cur_timestamp);
		$cur_month = date('n', $cur_timestamp);
		$cur_day_of_month = date('j', $cur_timestamp);
		$cur_year = date('Y', $cur_timestamp);
		if ($cur_day_of_week == 'Sun') {
			/* we don't ship on Sunday */
			$shipping_delayed = true;
			$ship_timestamp = mktime(0, 0, 0, $cur_month,
			    $cur_day_of_month + 1, $cur_year);
			$ship_delay_msg =
			    JText::_('VM_SHIPPING_METHOD_DHL_NOT_ON_WEEKENDS') . " " .
			    JText::_('VM_SHIPPING_METHOD_DHL_WILL_GO_OUT') . ": " .
				date('M j, Y', $ship_timestamp);
			$ship_day = 'Mon';
			$ship_date = date('Y-m-d', $ship_timestamp);
		} else if ($cur_day_of_week == 'Sat') {
			/* we don't ship on Saturday */
			$shipping_delayed = true;
			$ship_timestamp = mktime(0, 0, 0, $cur_month,
			    $cur_day_of_month + 2, $cur_year);
			$ship_delay_msg =
			    JText::_('VM_SHIPPING_METHOD_DHL_NOT_ON_WEEKENDS') . " " .
			    JText::_('VM_SHIPPING_METHOD_DHL_WILL_GO_OUT') . ": " .
				date('M j, Y', $ship_timestamp);
			$ship_day = 'Mon';
			$ship_date = date('Y-m-d', $ship_timestamp);
		} else {
			/* check time */
			$shipping_delayed = true;
			$cur_time = date('Gi');
			if ($cur_time > intval(DHL_TOO_LATE)) {
				/* too late to go out today */
				if ($cur_day_of_week == 'Fri') {
					$ship_timestamp = mktime(0, 0, 0, $cur_month,
						$cur_day_of_month + 3, $cur_year);
					$ship_delay_msg =
						JText::_('VM_SHIPPING_METHOD_DHL_TOO_LATE_TO_SHIP')
					    . " " .
						JText::_('VM_SHIPPING_METHOD_DHL_WILL_GO_OUT')
					    . ": " .  date('M j, Y', $ship_timestamp);
					$ship_day = 'Mon';
					$ship_date = date('Y-m-d', $ship_timestamp);
				} else {
					$ship_timestamp = mktime(0, 0, 0, $cur_month,
						$cur_day_of_month + 1, $cur_year);
					$ship_delay_msg =
						JText::_('VM_SHIPPING_METHOD_DHL_TOO_LATE_TO_SHIP')
					    . " " .
						JText::_('VM_SHIPPING_METHOD_DHL_WILL_GO_OUT')
					    . ": " .  date('M j, Y', $ship_timestamp);
					$ship_day = date('D', $ship_timestamp);
					$ship_date = date('Y-m-d', $ship_timestamp);
				}
			} else {
				/* it's okay, we can ship today */
				$shipping_delayed = false;
				$ship_day = $cur_day_of_week;
				$ship_date = date('Y-m-d', $cur_timestamp);
			}
		}

		$db =& new ps_DB;

		$cart = $_SESSION['cart'];

		$q  = "SELECT * FROM #__users, #__{vm}_country " .
		    "WHERE user_info_id='" . $d["ship_to_info_id"] .
		    "' AND ( country=country_2_code OR " .
		    "country=country_3_code)";
		$db->query($q);
		if (!$db->next_record()) {
			$q  = "SELECT * FROM #__{vm}_user_info, " .
			    "#__{vm}_country " .
			    "WHERE user_info_id='" . $d["ship_to_info_id"] .
			    "' AND ( country=country_2_code OR " .
			    "country=country_3_code)";
			$db->query($q);
		}

		if ($d['weight'] == 0)
			return (true);

		$dhl_url = "https://eCommerce.airborne.com/";
		if (DHL_TEST_MODE == 'TRUE')
			$dhl_url .= "ApiLandingTest.asp";
		else
			$dhl_url .= "ApiLanding.asp";

		/* We haven't defined any shipping methods yet. */
		$methods = array();

		/* determine if we are domestic or international */
		$dest_country = $db->f("country_2_code");
		$dest_state = $db->f("state");

		$is_international = $this->is_international($dest_country,
		    $dest_state);
		if (!$is_international) {
			if ($this->params->get('DHL_EXPRESS_ENABLED') == 'TRUE') {
				$methods[] = array(
					'service_desc' =>
					    JText::_('VM_SHIPPING_METHOD_DHL_EXPRESS'),
					'service_code' => 'E',
					'special_service' => '',
					'package_type' => $this->params->get('DHL_DOMESTIC_PACKAGE'),
					'international' => false);
			}
			if ($this->params->get('DHL_NEXT_AFTERNOON_ENABLED') == 'TRUE') {
				$methods[] = array(
					'service_desc' =>
					    JText::_('VM_SHIPPING_METHOD_DHL_NEXT_AFTERNOON'),
					'service_code' => 'N',
					'special_service' => '',
					'package_type' => $this->params->get('DHL_DOMESTIC_PACKAGE'),
					'international' => false);
			}
			if ($this->params->get('DHL_SECOND_DAY_ENABLED') == 'TRUE') {
				$methods[] = array(
					'service_desc' =>
					    JText::_('VM_SHIPPING_METHOD_DHL_SECOND_DAY'),
					'service_code' => 'S',
					'special_service' => '',
					'package_type' => $this->params->get('DHL_DOMESTIC_PACKAGE'),
					'international' => false);
			}
			if ($this->params->get('DHL_GROUND_ENABLED') == 'TRUE') {
				$methods[] = array(
					'service_desc' =>
					    JText::_('VM_SHIPPING_METHOD_DHL_GROUND'),
					'service_code' => 'G',
					'special_service' => '',
					'package_type' => $this->params->get('DHL_DOMESTIC_PACKAGE'),
					'international' => false);
			}
			if ($this->params->get('DHL_1030_ENABLED') == 'TRUE') {
				$methods[] = array(
					'service_desc' =>
					    JText::_('VM_SHIPPING_METHOD_DHL_1030'),
					'service_code' => 'E',
					'special_service' => '1030',
					'package_type' => $this->params->get('DHL_DOMESTIC_PACKAGE'),
					'international' => false);
			}
			// Saturday delivery is only an option on Fridays
			if (DHL_SATURDAY_ENABLED == 'TRUE' && ($ship_day == 'Fri')) {
				$methods[] = array(
					'service_desc' =>
					    JText::_('VM_SHIPPING_METHOD_DHL_SATURDAY'),
					'service_code' => 'E',
					'special_service' => 'SAT',
					'package_type' => $this->params->get('DHL_DOMESTIC_PACKAGE'),
					'international' => false);
			}
			$shipping_key = $this->params->get('DHL_DOMESTIC_SHIPPING_KEY');

			if (DHL_DOMESTIC_PACKAGE != 'E')
				$order_weight = $d['weight'] + floatval($this->params->get('DHL_PACKAGE_WEIGHT'));

			$content_desc = '';
			$duty_value = 0;
		} else {
			if ($this->params->get('DHL_INTERNATIONAL_ENABLED') == 'TRUE') {
				$methods[] = array(
					'service_desc' =>
					    JText::_('VM_SHIPPING_METHOD_DHL_INTERNATIONAL'),
					'service_code' => 'IE',
					'special_service' => '',
					'package_type' => $this->params->get('DHL_INTERNATIONAL_PACKAGE'),
					'international' => true);
			}

			/*
			 * XXX
			 * We should really walk through the list of each product in
			 * the order and check for special "harmonizing descriptions"
			 * to build our $content_desc variables.
			 */
			$content_desc = $this->params->get('DHL_CONTENT_DESC');

			$duty_value = $this->calc_duty_value($d);
			$shipping_key = $this->params->get('DHL_INTERNATIONAL_SHIPPING_KEY');

			/* DHL country codes are non-standard, remap them */
			$dest_country = $this->remap_country_code($dest_country,
			    $dest_state);

			if ($this->params->get('DHL_INTERNATIONAL_PACKAGE') != 'E')
				$order_weight = $d['weight'] + floatval($this->params->get('DHL_PACKAGE_WEIGHT'));
		}
		/* if we're not on an exact integer pound, round */
		if (floatval(intval($order_weight)) != $order_weight) {
			/* round up */
			$order_weight = $order_weight + 0.51;
			$order_weight = round($order_weight, 0);
		}

		/* calculate insurance protection value */
		$insurance = $this->calc_insurance_value($d, $is_international);

		require_once($mosConfig_absolute_path .	'/includes/domit/xml_domit_lite_include.php');

		$html = '';
		if ($shipping_delayed) {
			$html .= '<span class="message"><strong>';
			$html .= $ship_delay_msg;
			$html .= '</strong></span><br />';
		}
		$returnArr = array();
		foreach ($methods as $method) {
			$xmlReq =& new DOMIT_Lite_Document();

			$xmlReq->setXMLDeclaration('<?xml version="1.0"?>' ); 
			$root =& $xmlReq->createElement('eCommerce'); 
			$root->setAttribute('action', 'Request'); 
			$root->setAttribute('version', '1.1'); 
			$xmlReq->setDocumentElement($root); 

			$requestor =& $xmlReq->createElement('Requestor'); 
			$id =& $xmlReq->createElement('ID');
			$id->setText($this->params->get('DHL_ID'));
			$requestor->appendChild($id);
			$password =& $xmlReq->createElement('Password');
			$password->setText($this->params->get('DHL_PASSWORD'));
			$requestor->appendChild($password);
			$root->appendChild($requestor);

			/* International Rate Estimate Request */
			if ($method['international'])
				$shipment =& $xmlReq->createElement('IntlShipment');
			else
				$shipment =& $xmlReq->createElement('Shipment');
			$shipment->setAttribute('action', 'RateEstimate'); 
			$shipment->setAttribute('version', '1.0'); 

			$creds =& $xmlReq->createElement('ShippingCredentials');
			$ship_key =& $xmlReq->createElement('ShippingKey');
			$ship_key->setText($shipping_key);
			$creds->appendChild($ship_key);
			$an =& $xmlReq->createElement('AccountNbr');
			$an->setText($this->params->get('DHL_ACCOUNT_NUMBER'));
			$creds->appendChild($an);
			$shipment->appendChild($creds);

			$detail =& $xmlReq->createElement('ShipmentDetail');
			$date =& $xmlReq->createElement('ShipDate');
			$date->setText($ship_date);
			$detail->appendChild($date);
			$service =& $xmlReq->createElement('Service');
			$code =& $xmlReq->createElement('Code');
			$code->setText($method['service_code']);
			$service->appendChild($code);
			$detail->appendChild($service);
			$stype =& $xmlReq->createElement('ShipmentType');
			$code =& $xmlReq->createElement('Code');
			$code->setText($method['package_type']);
			$stype->appendChild($code);

			if ($insurance > 0 && $this->params->get('DHL_ADDITIONAL_PROTECTION') != 'NR') {
				/* include additional value protection */
				$addl_prot =& $xmlReq->createElement('AdditionalProtection');
				$code =& $xmlReq->createElement('Code');
				$code->setText($this->params->get('DHL_ADDITIONAL_PROTECTION'));
				$addl_prot->appendChild($code);
				$value =& $xmlReq->createElement('Value');
				$value->setText(round($insurance, 0));
				$addl_prot->appendChild($value);
				$detail->appendChild($addl_prot);
			}

			$detail->appendChild($stype);

			if ($method['international']) {
				$desc =& $xmlReq->createElement('ContentDesc');
				/* CDATA description */
				$desc_text =& $xmlReq->createCDATASection($content_desc);
				$desc->appendChild($desc_text);
				$detail->appendChild($desc);
			}
			$weight =& $xmlReq->createElement('Weight');
			$weight->setText($order_weight);
			$detail->appendChild($weight);

			if ($method['special_service'] != '') {
				$sservices =& $xmlReq->createElement('SpecialServices');
				$service =& $xmlReq->createElement('SpecialService');
				$code =& $xmlReq->createElement('Code');
				$code->setText($method['special_service']);
				$service->appendChild($code);
				$sservices->appendChild($service);
				$detail->appendChild($sservices);
			}
			$shipment->appendChild($detail);
		
			if ($method['international']) {
				$dutiable =& $xmlReq->createElement('Dutiable');
				$dflag =& $xmlReq->createElement('DutiableFlag');
				if ($duty_value == 0) {
					$dflag->setText('N');
					$dutiable->appendChild($dflag);
				} else {
					$dflag->setText('Y');
					$dutiable->appendChild($dflag);

					$dval =& $xmlReq->createElement('CustomsValue');
					$dval->setText(round($duty_value, 0));
					$dutiable->appendChild($dval);
				}
				$shipment->appendChild($dutiable);
			}

			$billing =& $xmlReq->createElement('Billing');
			$party =& $xmlReq->createElement('Party');
			$code =& $xmlReq->createElement('Code');
			/* Always bill shipper */
			$code->setText('S');
			$party->appendChild($code);
			$billing->appendChild($party);
			if ($method['international']) {
				$duty_payer =& $xmlReq->createElement('DutyPaymentType');
				/* receiver pays duties */
				$duty_payer->setText('R');
				$billing->appendChild($duty_payer);
			}
			$shipment->appendChild($billing);

			$recv =& $xmlReq->createElement('Receiver');
			$addr =& $xmlReq->createElement('Address');
			
			// Handle address_1
			$address_1 = $db->f('address_1');
			if( strlen( $address_1 ) > 35 ) {
				$address_1 = substr( $address_1, 0, 35 );
				$vmLogger->debug( 'Address 1 too long. Shortened to 35 characters.' );
			}
			$street_addr =& $xmlReq->createCDATASection( $address_1 );

			$street =& $xmlReq->createElement('Street');
			$street->appendChild($street_addr);
			$addr->appendChild($street);

			// Handle address_2
			$address_2 = $db->f('address_2');
			if( strlen( $address_2 ) > 35 ) {
				$address_2 = substr( $address_2, 0, 35 );
				$vmLogger->debug( 'Address 2 too long. Shortened to 35 characters.' );
			}
			$street_addr2 =& $xmlReq->createCDATASection( $address_2 );

			$street2 = & $xmlReq->CreateElement( 'StreetLine2' );
			$street2->appendChild($street_addr2);
			$addr->appendChild($street2);

			$city =& $xmlReq->createElement('City');
			$city_name =& $xmlReq->createCDATASection($db->f('city'));
			$city->appendChild($city_name);
			$addr->appendChild($city);
			if ($db->f('state') != '') {
				$state =& $xmlReq->createElement('State');
				$state->setText($db->f('state'));
				$addr->appendChild($state);
			}
			$country =& $xmlReq->createElement('Country');
			$country->setText($dest_country);
			$addr->appendChild($country);
			if ($db->f('zip') != '') {
				$pc =& $xmlReq->createElement('PostalCode');
				$pc->setText($db->f('zip'));
				$addr->appendChild($pc);
			}
			$recv->appendChild($addr);
			$shipment->appendChild($recv);
			$root->appendChild($shipment);

//			$vmLogger->err($xmlReq->toNormalizedString());

			if (function_exists( "curl_init" )) {
				$CR = curl_init();
				curl_setopt($CR, CURLOPT_URL, $dhl_url);
				curl_setopt($CR, CURLOPT_POST, 1);
				curl_setopt($CR, CURLOPT_FAILONERROR, true);
				curl_setopt($CR, CURLOPT_POSTFIELDS, $xmlReq->toString());
				curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);

				$xmlResult = curl_exec($CR);
				$error = curl_error($CR);
				if (!empty($error)) {
					$vmLogger->err(curl_error($CR));
					$html = '<br/><span class="message">' .
						JText::_('VM_INTERNAL_ERROR') .
						 ' DHL</span>';
					return (false);
				}
				curl_close($CR);
			}

			// XML Parsing
			$xmlResp =& new DOMIT_Lite_Document();
			if (!$xmlResp->parseXML($xmlResult, false, true)) {
				$vmLogger->err(
				    JText::_('VM_SHIPPING_METHOD_DHL_INVALID_XML') .
				    $xmlResult);
			
				continue;
			}
//			$vmLogger->err($xmlResp->toNormalizedString());

			// Check for success or failure.
			$result_code_list =& $xmlResp->getElementsByPath('//Result/Code');
			$result_code =& $result_code_list->item(0);
			$result_desc_list =& $xmlResp->getElementsByPath('//Result/Desc');
			$result_desc =& $result_desc_list->item(0);
			if ($result_code == NULL) {
				$vmLogger->debug(
				    JText::_('VM_SHIPPING_METHOD_DHL_MISSING_RESULT') .
				    "\n" . $xmlResp->toNormalizedString());
				continue;
			}

			// '203' is the code for success (at least with domestic)
			if ($result_code->getText() != '203') {
				$vmLogger->debug(
					$method['service_desc'] . ': ' .
					$result_desc->getText() . ' [code ' .
					$result_code->getText() . ']' );

				// display an error line for each fault
				$fault_node_list =& $xmlResp->getElementsByPath(
				    '//Faults/Fault');
				
				for ($i = 0; $i < $fault_node_list->getLength(); $i++) {
					$fault_node =& $fault_node_list->item($i);
					$fault_code_node_list =& $fault_node->getElementsByTagName(
					    'Code');
					$fault_desc_node_list =& $fault_node->getElementsByTagName(
					    'Desc');
					$fault_code_node =& $fault_code_node_list->item(0);
					$fault_desc_node =& $fault_desc_node_list->item(0);

					$vmLogger->debug( $fault_desc_node->getText() . ' [code ' .
					    $fault_code_node->getText() . ']' );
				}

				continue;
			} else {
				$deliver_date_node_list =& $xmlResp->getElementsByPath(
				    '//ServiceLevelCommitment/Desc');
				$deliver_date_node =& $deliver_date_node_list->item(0);
				$deliver_date = $deliver_date_node->getText();

				$ship_rate_node_list =&  $xmlResp->getElementsByTagName(
					'TotalChargeEstimate');
				$ship_rate_node =& $ship_rate_node_list->item(0);
				$ship_rate = $ship_rate_node->getText();

				/*
				 * If DHL freaks out and gives us a $0.00 shipping
				 * rate, don't list the option.
				 */
				if ($ship_rate == 0.00)
					continue;

				$total_rate = $ship_rate + floatval($this->params->get('DHL_HANDLING_FEE'));
				

				/*
				 * Leave the shipping class field empty
				 * since it looks ugly.  The information we need to
				 * generate a shipping label for this rate will be
				 * stored one off the end.
				 */
				$id_string = $this->_name;
				$id_string .= "|DHL";
				$id_string .= "|" . $method['service_desc'];
				$id_string .= "|" . $total_rate;
				$id_string .= "|";
				$id_string .= "|" . $ship_date;
				$id_string .= ";" . $method['service_code'];
				$id_string .= ";" . $method['special_service'];
				$id_string .= ";" . $method['package_type'];
				if ($method['international'])
					$id_string .= ";T";
				else
					$id_string .= ";F";
				$id_string .= ";" . $this->params->get('DHL_ADDITIONAL_PROTECTION');
				$id_string .= ";" . $order_weight;
				$id_string .= ";" . $duty_value;
				$id_string .= ";" . $insurance;
				$id_string .= ";" . $content_desc;

				$shipping_rate_id = urlencode($id_string);

				$_SESSION[$shipping_rate_id] = 1;
				
				$returnArr[] = array('shipping_rate_id' => $shipping_rate_id,
													'carrier' => 'DHL',
													'rate_name' => $method['service_desc'],
													'delivery_date' => $deliver_date,
													'rate' => $total_rate
												);
			}
		}
		
		return $returnArr;
	}


	function get_shipping_rate(&$d) {

		$shipping_rate_id = $d["shipping_rate_id"];
		$is_arr = explode("|", urldecode(urldecode($shipping_rate_id)));
		$order_shipping = $is_arr[3];

		return ($order_shipping);
	}


	/*
	 * This function saves shipping information for later use in
	 * printing a label.  It should be saved when the check-out
	 * process if finalized.
	 */
	function save_rate_info(&$d) {

		$shipping_rate_id = $d['shipping_rate_id'];
		$is_arr = explode("|", urldecode(urldecode($shipping_rate_id)));

		/*
		 * 6th element is really an encoding of eight values separated
		 * by semi-colons
		 */
		$shipping_info = explode(";", $is_arr[5]);
		$ship_date = $shipping_info[0];
		$service_code = $shipping_info[1];
		$special_service = $shipping_info[2];
		$package_type = $shipping_info[3];
		if ($shipping_info[4] == 'T')
			$is_international = true;
		else
			$is_international = false;
		$addl_prot = $shipping_info[5];
		$order_weight = $shipping_info[6];
		$duty_value = $shipping_info[7];
		$insurance = $shipping_info[8];
		$content_desc = $shipping_info[9];

		/*
		 * stick these in a database somewhere
		 *  order_id, ship_date, service_code, special_service, package_type,
		 *  order_weight, is_international, additional_protection_type, 
		 *  additional_protection_value, duty_value, content_desc,
		 *  label_is_generated, label_image
		 */
		$q = "INSERT INTO #__{vm}_shipping_label ";
		$q .= "(order_id, shipper_class, ship_date, service_code, ";
		$q .= "special_service, package_type, order_weight, ";
		$q .= "is_international, additional_protection_type, ";
		$q .= "additional_protection_value, duty_value, content_desc, ";
		$q .= "label_is_generated) ";
		$q .= "VALUES (";
		$q .= "'" . $d['order_id'] . "', ";
		$q .= "'" . $this->_name. "', ";
		$q .= "'" . $ship_date . "', ";
		$q .= "'" . $service_code . "', ";
		$q .= "'" . $special_service . "', ";
		$q .= "'" . $package_type . "', ";
		$q .= "'" . $order_weight . "', ";
		$q .= "'" . $is_international . "', ";
		$q .= "'" . $addl_prot . "', ";
		$q .= "'" . $insurance . "', ";
		$q .= "'" . $duty_value . "', ";
		$q .= "'" . $content_desc . "', ";
		$q .= "'0'";
		$q .= ")";

		$db =& new ps_DB;
		$db->query($q);
		$db->next_record();
	}


	function generate_label($order_id) {
		global $vmLogger;
		global  $mosConfig_absolute_path;

		/* Retrieve label information from database */
		$dbl =& new ps_DB;
		$q = "SELECT order_id, ship_date, service_code, special_service, ";
		$q .= "package_type, order_weight, is_international, ";
		$q .= "additional_protection_type, additional_protection_value, ";
		$q .= "duty_value, content_desc, label_is_generated ";
		$q .= "FROM #__{vm}_shipping_label ";
		$q .= "WHERE order_id = '" . $order_id . "'";
		$dbl->query($q);
		if (!$dbl->next_record()) {
			$vmLogger->err("couldn't find label info for order #" .
			    $order_id);
			return (false);
		}

		if ($dbl->f('order_weight') == 0)
			return (false);

		/* If the label has already been generated, we're done */
		if ($dbl->f('label_is_generated'))
			return (true);

		/* get customer shipping information */
		$db =& new ps_DB;
		$q = "SELECT first_name,last_name,address_1,address_2,";
		$q .= "city,state,country,zip,phone_1,email,country_2_code ";
		$q .= "FROM #__{vm}_order_user_info, #__{vm}_country ";
		$q .= "WHERE order_id = '" . $order_id . "' ";
		$q .= "AND (country=country_2_code OR country=country_3_code)";
		$q .= "AND address_type='ST'";
		$db->query($q);
		if (!$db->next_record()) {
			/* If we can't find a ship-to address use bill-to */
			$q = "SELECT first_name,last_name,address_1,address_2,";
			$q .= "city,state,country,zip,phone_1,email,country_2_code ";
			$q .= "FROM #__{vm}_order_user_info, #__{vm}_country ";
			$q .= "WHERE order_id = '" . $order_id . "' ";
			$q .= "AND (country=country_2_code OR country=country_3_code)";
			$q .= "AND address_type='BT'";
			$db->query($q);
			if (!$db->next_record()) {
				$vmLogger->err("user info for order #" .
					$order_id . " not found");
				return (false);
			}
		}

		$dest_country = $db->f("country_2_code");
		$dest_state = $db->f("state");

		$dhl_url = "https://eCommerce.airborne.com/";
		if (DHL_TEST_MODE == 'TRUE')
			$dhl_url .= "ApiLandingTest.asp";
		else
			$dhl_url .= "ApiLanding.asp";

		if (!$dbl->f('is_international')) {
			$shipping_key = $this->params->get('DHL_DOMESTIC_SHIPPING_KEY');
		} else {
			/*
			 * XXX
			 * We should really walk through the list of each product in
			 * the order and check for special "harmonizing descriptions"
			 * to build our $content_desc variables.
			 */
			$content_desc = $this->params->get('DHL_CONTENT_DESC');

			$shipping_key = $this->params->get('DHL_INTERNATIONAL_SHIPPING_KEY');

			/* DHL country codes are non-standard, remap them */
			$dest_country = $this->remap_country_code($dest_country,
			    $dest_state);
		}

		/* Get our sending address information */
		// the vendor must be taken from the order
		$vendor_id = ps_order::get_vendor_id_by_order_id($order_id);
		$dbv = ps_vendor::get_vendor_details($db,$vendor_id);
		
		require_once($mosConfig_absolute_path .
			'/includes/domit/xml_domit_lite_include.php');

		$xmlReq =& new DOMIT_Lite_Document();

		$xmlReq->setXMLDeclaration('<?xml version="1.0"?>' ); 
		$root =& $xmlReq->createElement('eCommerce'); 
		$root->setAttribute('action', 'Request'); 
		$root->setAttribute('version', '1.1'); 
		$xmlReq->setDocumentElement($root); 

		$requestor =& $xmlReq->createElement('Requestor'); 
		$id =& $xmlReq->createElement('ID');
		$id->setText($this->params->get('DHL_ID'));
		$requestor->appendChild($id);
		$password =& $xmlReq->createElement('Password');
		$password->setText($this->params->get('DHL_PASSWORD'));
		$requestor->appendChild($password);
		$root->appendChild($requestor);

		/* International Rate Estimate Request */
		if ($dbl->f('is_international'))
			$shipment =& $xmlReq->createElement('IntlShipment');
		else
			$shipment =& $xmlReq->createElement('Shipment');
		$shipment->setAttribute('action', 'GenerateLabel'); 
		$shipment->setAttribute('version', '1.0'); 

		$creds =& $xmlReq->createElement('ShippingCredentials');
		$ship_key =& $xmlReq->createElement('ShippingKey');
		$ship_key->setText($shipping_key);
		$creds->appendChild($ship_key);
		$an =& $xmlReq->createElement('AccountNbr');
		$an->setText($this->params->get('DHL_ACCOUNT_NUMBER'));
		$creds->appendChild($an);
		$shipment->appendChild($creds);

		$detail =& $xmlReq->createElement('ShipmentDetail');
		$date =& $xmlReq->createElement('ShipDate');
		$date->setText(date($dbl->f('ship_date')));
		$detail->appendChild($date);
		$service =& $xmlReq->createElement('Service');
		$code =& $xmlReq->createElement('Code');
		$code->setText($dbl->f('service_code'));
		$service->appendChild($code);
		$detail->appendChild($service);
		$stype =& $xmlReq->createElement('ShipmentType');
		$code =& $xmlReq->createElement('Code');
		$code->setText($dbl->f('package_type'));
		$stype->appendChild($code);

		if ($dbl->f('additional_protection_value') > 0 &&
		    $dbl->f('additional_protection_type') != 'NR') {
			/* include additional value protection */
			$addl_prot =& $xmlReq->createElement('AdditionalProtection');
			$code =& $xmlReq->createElement('Code');
			$code->setText($dbl->f('additional_protection_type'));
			$addl_prot->appendChild($code);
			$value =& $xmlReq->createElement('Value');
			$value->setText(round($dbl->f('additional_protection_value'), 0));
			$addl_prot->appendChild($value);
			$detail->appendChild($addl_prot);
		}

		$detail->appendChild($stype);

		if ($dbl->f('is_international')) {
			$desc =& $xmlReq->createElement('ContentDesc');
			/* CDATA description */
			$desc_text =& $xmlReq->createCDATASection($dbl->f('content_desc'));
			$desc->appendChild($desc_text);
			$detail->appendChild($desc);
		}
		$weight =& $xmlReq->createElement('Weight');
		$weight->setText(round($dbl->f('order_weight'), 0));
		$detail->appendChild($weight);

		if ($dbl->f('special_service') != '') {
			$sservices =& $xmlReq->createElement('SpecialServices');
			$service =& $xmlReq->createElement('SpecialService');
			$code =& $xmlReq->createElement('Code');
			$code->setText($dbl->f('special_service'));
			$service->appendChild($code);
			$sservices->appendChild($service);
			$detail->appendChild($sservices);
		}
		$shipment->appendChild($detail);
	
		if ($dbl->f('is_international')) {
			$dutiable =& $xmlReq->createElement('Dutiable');
			$dflag =& $xmlReq->createElement('DutiableFlag');
			if ($dbl->f('duty_value') == 0) {
				$dflag->setText('N');
				$dutiable->appendChild($dflag);
			} else {
				$dflag->setText('Y');
				$dutiable->appendChild($dflag);

				$dval =& $xmlReq->createElement('CustomsValue');
				$dval->setText(round($dbl->f('duty_value'), 0));
				$dutiable->appendChild($dval);

				/*
				 * This should probably be a configuration item,
				 * but I'm harding coding it to 'N'.  The question
				 * being asked is: Is a Shipment Export Declaration (SED)
				 * required?
				 * N - If an Export Declaration is to be created only
				 *     when required by value or Self Filing details
				 *     in the form of XTN number is provided.
				 * Y - If an Export Declaration is to be created
				 *     even when not required by value.
				 * X - I will file SED information for this shipment
				 *     electronically. ?need to provide XTN number.
				 */
				$sed =& $xmlReq->createElement('IsSEDReqd');
				$sed->setText('N');
				$dutiable->appendChild($sed);
			}
			$shipment->appendChild($dutiable);
		}

		$billing =& $xmlReq->createElement('Billing');
		$party =& $xmlReq->createElement('Party');
		$code =& $xmlReq->createElement('Code');
		/* Always bill shipper */
		$code->setText('S');
		$party->appendChild($code);
		$billing->appendChild($party);
		if ($dbl->f('is_international')) {
			$duty_payer =& $xmlReq->createElement('DutyPaymentType');
			/* receiver pays duties */
			$duty_payer->setText('R');
			$billing->appendChild($duty_payer);
		}
		$shipment->appendChild($billing);

		$send =& $xmlReq->createElement('Sender');
		if ($dbl->f('is_international')) {
			/* International shipments require shipper address */
			$addr =& $xmlReq->createElement('Address');
			$name =& $xmlReq->createElement('CompanyName');
			$name_cdata =& $xmlReq->createCDATASection(
				$dbv->f('name'));
			$name->appendChild($name_cdata);
			$addr->appendChild($name);
			$street =& $xmlReq->createElement('Street');
			$street_addr =& $xmlReq->createCDATASection(
			    $dbv->f('address_1') . "\n" .
			    $dbv->f('address_2'));
			$street->appendChild($street_addr);
			$addr->appendChild($street);
			$city =& $xmlReq->createElement('City');
			$city_name =& $xmlReq->createCDATASection($dbv->f('city'));
			$city->appendChild($city_name);
			$addr->appendChild($city);
			$state =& $xmlReq->createElement('State');
			$state->setText($dbv->f('state'));
			$addr->appendChild($state);
			/*
			 * DHL's XML API currently only supports international
			 * shipping from the US.
			 */
			$country =& $xmlReq->createElement('Country');
			$country->setText('US');
			$addr->appendChild($country);
			$pc =& $xmlReq->createElement('PostalCode');
			$pc->setText($dbv->f('zip'));
			$addr->appendChild($pc);
			$send->appendChild($addr);		

			$email =& $xmlReq->createElement('Email');
			$email_cdata =& $xmlReq->createCDATASection(
			    $dbv->f('email'));
			$email->appendChild($email_cdata);
			$send->appendChild($email);
		}
		$sent_by =& $xmlReq->createElement('SentBy');
		$sent_by_cdata =& $xmlReq->createCDATASection(
		    $dbv->f('first_name') . " " . $dbv->f('last_name'));
		$sent_by->appendChild($sent_by_cdata);
		$send->appendChild($sent_by);
		$pn =& $xmlReq->createElement('PhoneNbr');
		$pn->setText($dbv->f('phone_1'));
		$send->appendChild($pn);
		$shipment->appendChild($send);

		$recv =& $xmlReq->createElement('Receiver');
		$addr =& $xmlReq->createElement('Address');
		$name =& $xmlReq->createElement('CompanyName');
		$name_cdata =& $xmlReq->createCDATASection(
			$db->f('first_name') . " " . $db->f('last_name'));
		$name->appendChild($name_cdata);
		$addr->appendChild($name);

		$street =& $xmlReq->createElement('Street');
		$street_addr =& $xmlReq->createCDATASection($db->f('address_1'));
		$street->appendChild($street_addr);
		$addr->appendChild($street);
		$street2 =& $xmlReq->createElement('StreetLine2');
		$street2_addr =& $xmlReq->createCDATASection($db->f('address_2'));
		$street2->appendChild($street2_addr);
		$addr->appendChild($street2);
		$city =& $xmlReq->createElement('City');
		$city_name =& $xmlReq->createCDATASection($db->f('city'));
		$city->appendChild($city_name);
		$addr->appendChild($city);
		if ($db->f('state') != '') {
			$state =& $xmlReq->createElement('State');
			$state->setText($db->f('state'));
			$addr->appendChild($state);
		}
		$country =& $xmlReq->createElement('Country');
		$country->setText($dest_country);
		$addr->appendChild($country);

		if ($db->f('zip') != '') {
			$pc =& $xmlReq->createElement('PostalCode');
			$pc->setText($db->f('zip'));
			$addr->appendChild($pc);
		}
		$recv->appendChild($addr);
		$attn =& $xmlReq->createElement('AttnTo');
		$attn_cdata =& $xmlReq->createCDATASection(
			$db->f('first_name') . " " . $db->f('last_name'));
		$attn->appendChild($attn_cdata);
		$recv->appendChild($attn);
		$pn =& $xmlReq->createElement('PhoneNbr');
		$pn->setText($db->f('phone_1'));
		$recv->appendChild($pn);
		
		$shipment->appendChild($recv);

		/* label image type */
		$inst =& $xmlReq->createElement('ShipmentProcessingInstructions');
		$label =& $xmlReq->createElement('Label');
		$itype =& $xmlReq->createElement('ImageType');
		/* GIF is the alternative */
		$itype->setText('PNG');
		$label->appendChild($itype);
		$inst->appendChild($label);
		$shipment->appendChild($inst);

		/* Have DHL notify the receiver */
		$notification =& $xmlReq->createElement('Notification');
		$notify =& $xmlReq->createElement('Notify');
		$email =& $xmlReq->createElement('EmailAddress');
		$email->setText($db->f('email'));
		$notify->appendChild($email);
		$notification->appendChild($notify);
		$shipment->appendChild($notification);

		$root->appendChild($shipment);

//		$vmLogger->err($xmlReq->toNormalizedString());

		if (function_exists( "curl_init" )) {
			$CR = curl_init();
			curl_setopt($CR, CURLOPT_URL, $dhl_url);
			curl_setopt($CR, CURLOPT_POST, 1);
			curl_setopt($CR, CURLOPT_FAILONERROR, true);
			curl_setopt($CR, CURLOPT_POSTFIELDS, $xmlReq->toString());
			curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);

			$xmlResult = curl_exec($CR);
			$error = curl_error($CR);
			if (!empty($error)) {
				$vmLogger->err(curl_error($CR));
				return (false);
			}
			curl_close($CR);
		}

		// XML Parsing
		$xmlResp =& new DOMIT_Lite_Document();
		if (!$xmlResp->parseXML($xmlResult, false, true)) {
			echo JText::_('VM_SHIPPING_METHOD_DHL_INVALID_XML') .
			    $xmlResult;
			return (false);
		}
//		$vmLogger->err($xmlResp->toNormalizedString());

		// Check for success or failure.
		$result_code_list =& $xmlResp->getElementsByPath('//Result/Code');
		$result_code =& $result_code_list->item(0);
		$result_desc_list =& $xmlResp->getElementsByPath('//Result/Desc');
		$result_desc =& $result_desc_list->item(0);
		if ($result_code == NULL) {
			$vmLogger->debug(
				JText::_('VM_SHIPPING_METHOD_DHL_MISSING_RESULT') .
				"\n" . $xmlResp->toNormalizedString());
			return (false);
		}

		/* '100' is the code for success with a generated label. */
		if ($result_code->getText() != '100') {
			$err_msg = '<br /><span class="message">' .
				$result_desc->getText() . ' [code ' .
				$result_code->getText() . ']' .
				'</span>';

			// display an error line for each fault
			$fault_node_list =& $xmlResp->getElementsByPath(
				'//Faults/Fault');
			if ($fault_node_list->getLength() > 0)
				$err_msg .= '<ul>';
			for ($i = 0; $i < $fault_node_list->getLength(); $i++) {
				$fault_node =& $fault_node_list->item($i);
				$fault_code_node_list =& $fault_node->getElementsByTagName(
					'Code');
				$fault_desc_node_list =& $fault_node->getElementsByTagName(
					'Desc');
				$fault_code_node =& $fault_code_node_list->item(0);
				$fault_desc_node =& $fault_desc_node_list->item(0);

				$err_msg .= '<li>' . $fault_desc_node->getText() . ' [code ' .
					$fault_code_node->getText() . ']</li>';
			}
			if ($fault_node_list->getLength() > 0)
				$err_msg .= '</ul>';
			echo $err_msg;
			return (false);
		}

		$label_image_node_list =& $xmlResp->getElementsByPath('//Label/Image');
		$label_image_node =& $label_image_node_list->item(0);
		$label_image = $label_image_node->getText();

		$airbill_number_node_list =& $xmlResp->getElementsByPath(
		    '//ShipmentDetail/AirbillNbr');
		$airbill_number_node =& $airbill_number_node_list->item(0);
		$airbill_number = $airbill_number_node->getText();

		/*
		 * insert label image into database and mark that the label has
		 * been generated.
		 */
		$q = "UPDATE #__{vm}_shipping_label ";
		$q .= "SET ";
		$q .= "label_is_generated='1', ";
		$q .= "tracking_number='" . $airbill_number . "', ";
		$q .= "label_image='" . $label_image . "' ";
		$q .= "WHERE order_id = '" . $order_id . "'";

		$dbnl =& new ps_DB;
		$dbnl->query($q);
		$dbnl->next_record();

		return (true);
	}

	function get_label_dimensions($order_id) {
		global $vmLogger;

		/* Retrieve label information from database */
		$dbl =& new ps_DB;
		$q = "SELECT is_international ";
		$q .= "FROM #__{vm}_shipping_label ";
		$q .= "WHERE order_id = '" . $order_id . "'";
		$dbl->query($q);
		if (!$dbl->next_record()) {
			$vmLogger->err("couldn't find label info for order #" .
			    $order_id);
			return;
		}

		if ($dbl->f('is_international'))
			$dim = '669x559';
		else 
			$dim = '669x724';
		return ($dim);
	}

	function get_label_image_type($order_id) {

		return ('image/png');
	}

	function get_label_image($order_id) {
		global $vmLogger;

		/* Retrieve label information from database */
		$dbl =& new ps_DB;
		$q = "SELECT label_is_generated, label_image ";
		$q .= "FROM #__{vm}_shipping_label ";
		$q .= "WHERE order_id = '" . $order_id . "'";
		$dbl->query($q);
		if (!$dbl->next_record()) {
			$vmLogger->err("couldn't find label info for order #" .
			    $order_id);
			return;
		}

		if (!$dbl->f('label_is_generated')) {
			$vmLogger->err("label has not been generated");
			return;
		}

		$label_image = base64_decode($dbl->f('label_image'));
		return ($label_image);
	}


	function void_label($order_id) {
		global $vmLogger;
		global  $mosConfig_absolute_path;

		/* Retrieve waybill information from database */
		$dbl =& new ps_DB;
		$q = "SELECT tracking_number, label_is_generated, is_international ";
		$q .= "FROM #__{vm}_shipping_label ";
		$q .= "WHERE order_id = '" . $order_id . "'";
		$dbl->query($q);
		if (!$dbl->next_record() || !$dbl->f("label_is_generated"))
			return ("couldn't find label info for order #" .  $order_id);

		$dhl_url = "https://eCommerce.airborne.com/";
		if ($this->params->get('DEBUG'))
			$dhl_url .= "ApiLandingTest.asp";
		else
			$dhl_url .= "ApiLanding.asp";

		if (!$dbl->f('is_international'))
			$shipping_key = $this->params->get('DHL_DOMESTIC_SHIPPING_KEY');
		else
			$shipping_key = $this->params->get('DHL_INTERNATIONAL_SHIPPING_KEY');

		require_once($mosConfig_absolute_path .
			'/includes/domit/xml_domit_lite_include.php');

		$xmlReq =& new DOMIT_Lite_Document();

		$xmlReq->setXMLDeclaration('<?xml version="1.0"?>' ); 
		$root =& $xmlReq->createElement('eCommerce'); 
		$root->setAttribute('action', 'Request'); 
		$root->setAttribute('version', '1.1'); 
		$xmlReq->setDocumentElement($root); 

		$requestor =& $xmlReq->createElement('Requestor'); 
		$id =& $xmlReq->createElement('ID');
		$id->setText($this->params->get('DHL_ID'));
		$requestor->appendChild($id);
		$password =& $xmlReq->createElement('Password');
		$password->setText($this->params->get('DHL_PASSWORD'));
		$requestor->appendChild($password);
		$root->appendChild($requestor);

		/* International Rate Estimate Request */
		if ($dbl->f('is_international'))
			$shipment =& $xmlReq->createElement('IntlShipment');
		else
			$shipment =& $xmlReq->createElement('Shipment');
		$shipment->setAttribute('action', 'Void'); 
		$shipment->setAttribute('version', '1.0'); 

		$creds =& $xmlReq->createElement('ShippingCredentials');
		$ship_key =& $xmlReq->createElement('ShippingKey');
		$ship_key->setText($shipping_key);
		$creds->appendChild($ship_key);
		$an =& $xmlReq->createElement('AccountNbr');
		$an->setText($this->params->get('DHL_ACCOUNT_NUMBER'));
		$creds->appendChild($an);
		$shipment->appendChild($creds);

		$detail =& $xmlReq->createElement('ShipmentDetail');
		$airbill =& $xmlReq->createElement('AirbillNbr');
		$airbill->setText($dbl->f('tracking_number'));
		$detail->appendChild($airbill);
		$shipment->appendChild($detail);
		$root->appendChild($shipment);

//		$vmLogger->err($xmlReq->toNormalizedString());

		if (function_exists( "curl_init" )) {
			$CR = curl_init();
			curl_setopt($CR, CURLOPT_URL, $dhl_url);
			curl_setopt($CR, CURLOPT_POST, 1);
			curl_setopt($CR, CURLOPT_FAILONERROR, true);
			curl_setopt($CR, CURLOPT_POSTFIELDS, $xmlReq->toString());
			curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);

			$xmlResult = curl_exec($CR);
			$error = curl_error($CR);
			if (!empty($error)) {
				$vmLogger->err(curl_error($CR));
				$emsg = '<br/><span class="message">' .
					JText::_('VM_INTERNAL_ERROR') .
					 ' DHL</span>';
				return ($emsg);
			}
			curl_close($CR);
		}

		// XML Parsing
		$xmlResp =& new DOMIT_Lite_Document();
		if (!$xmlResp->parseXML($xmlResult, false, true)) {
			$emsg = '<br /><span class="message">' .
				JText::_('VM_SHIPPING_METHOD_DHL_INVALID_XML') .
				'</span>';
			return ($emsg);
		}
//		$vmLogger->err($xmlResp->toNormalizedString());

		// Check for success or failure.
		$result_code_list =& $xmlResp->getElementsByPath('//Result/Code');
		$result_code =& $result_code_list->item(0);
		$result_desc_list =& $xmlResp->getElementsByPath('//Result/Desc');
		$result_desc =& $result_desc_list->item(0);
		if ($result_code == NULL) {
			$emsg = JText::_('VM_ERROR_DESC') . ': ' .
				JText::_('VM_SHIPPING_METHOD_DHL_MISSING_RESULT');
			$vmLogger->debug(
				JText::_('VM_SHIPPING_METHOD_DHL_MISSING_RESULT') .
				"\n" . $xmlResp->toNormalizedString());
			return ($emsg);
		}

		/* '101' is the code for success for voiding a label */
		if ($result_code->getText() != '101') {
			$emsg = '<br /><span class="message">' .
				$result_desc->getText() . ' [code ' .
				$result_code->getText() . ']' .
				'</span>';

			// display an error line for each fault
			$fault_node_list =& $xmlResp->getElementsByPath(
				'//Faults/Fault');
			if ($fault_node_list->getLength() > 0)
				$emsg .= '<ul>';
			for ($i = 0; $i < $fault_node_list->getLength(); $i++) {
				$fault_node =& $fault_node_list->item($i);
				$fault_code_node_list =& $fault_node->getElementsByTagName(
					'Code');
				$fault_desc_node_list =& $fault_node->getElementsByTagName(
					'Desc');
				$fault_code_node =& $fault_code_node_list->item(0);
				$fault_desc_node =& $fault_desc_node_list->item(0);

				$emsg .= '<li>' . $fault_desc_node->getText() . ' [code ' .
					$fault_code_node->getText() . ']</li>';
			}
			if ($fault_node_list->getLength() > 0)
				$emsg .= '</ul>';
			return ($emsg);
		}

		/* Remove label from shipping item.  */
		$q = "UPDATE #__{vm}_shipping_label ";
		$q .= "SET ";
		$q .= "label_is_generated='0', ";
		$q .= "tracking_number=NULL, ";
		$q .= "label_image=NULL, ";
		$q .= "have_signature='0', ";
		$q .= "signature_image=NULL ";
		$q .= "WHERE order_id = '" . $order_id . "'";

		$dbnl =& new ps_DB;
		$dbnl->query($q);
		$dbnl->next_record();

		return ('');
	}


	function track($order_id) {
		global $vmLogger, $sess;
		global  $mosConfig_absolute_path;

		/* Retrieve waybill information from database */
		$dbl =& new ps_DB;
		$q = "SELECT tracking_number, label_is_generated, is_international ";
		$q .= "FROM #__{vm}_shipping_label ";
		$q .= "WHERE order_id = '" . $order_id . "'";
		$dbl->query($q);
		if (!$dbl->next_record() || !$dbl->f("label_is_generated"))
			return ("couldn't find label info for order #" .  $order_id);

		$tracking_number = $dbl->f('tracking_number');

		$dhl_url = "https://eCommerce.airborne.com/";
		if ($this->params->get('DEBUG'))
			$dhl_url .= "ApiLandingTest.asp";
		else
			$dhl_url .= "ApiLanding.asp";

		require_once($mosConfig_absolute_path .
			'/includes/domit/xml_domit_lite_include.php');

		$xmlReq =& new DOMIT_Lite_Document();

		$xmlReq->setXMLDeclaration('<?xml version="1.0"?>' ); 
		$root =& $xmlReq->createElement('eCommerce'); 
		$root->setAttribute('action', 'Request'); 
		$root->setAttribute('version', '1.1'); 
		$xmlReq->setDocumentElement($root); 

		$requestor =& $xmlReq->createElement('Requestor'); 
		$id =& $xmlReq->createElement('ID');
		$id->setText($this->params->get('DHL_ID'));
		$requestor->appendChild($id);
		$password =& $xmlReq->createElement('Password');
		$password->setText($this->params->get('DHL_PASSWORD'));
		$requestor->appendChild($password);
		$root->appendChild($requestor);

		/* Tracking Request */
		$track =& $xmlReq->createElement('Track');
		$track->setAttribute('action', 'Get'); 
		$track->setAttribute('version', '1.0'); 

		$shipment =& $xmlReq->createElement('Shipment');
		$nbr =& $xmlReq->createElement('TrackingNbr');
		$nbr->setText($tracking_number);
		$shipment->appendChild($nbr);
		$track->appendChild($shipment);
		$root->appendChild($track);

//		$vmLogger->err($xmlReq->toNormalizedString());

		if (function_exists( "curl_init" )) {
			$CR = curl_init();
			curl_setopt($CR, CURLOPT_URL, $dhl_url);
			curl_setopt($CR, CURLOPT_POST, 1);
			curl_setopt($CR, CURLOPT_FAILONERROR, true);
			curl_setopt($CR, CURLOPT_POSTFIELDS, $xmlReq->toString());
			curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);

			$xmlResult = curl_exec($CR);
			$error = curl_error($CR);
			curl_close($CR);
			if (!empty($error)) {
				$vmLogger->err(curl_error($CR));
				$emsg = '<br/><span class="message">' .
					JText::_('VM_INTERNAL_ERROR') .
					 ' DHL</span>';
				curl_close($CR);
				return ($emsg);
			}
		}

		// XML Parsing
		$xmlResp =& new DOMIT_Lite_Document();
		if (!$xmlResp->parseXML($xmlResult, false, true)) {
			$emsg = '<br /><span class="message">' .
				JText::_('VM_SHIPPING_METHOD_DHL_INVALID_XML') .
				'</span>';
			return ($emsg);
		}
//		$vmLogger->err($xmlResp->toNormalizedString());

		/* Check for success or failure */
		$fault_node_list =& $xmlResp->getElementsByPath('//Track/Fault');
		if ($fault_node_list->getLength() > 0) {
			/* Error in Track request */
			$emsg = '';
			$emsg .= '<ul>';
			for ($i = 0; $i < $fault_node_list->getLength(); $i++) {
				$fault_node =& $fault_node_list->item($i);
				$fault_code_node_list =& $fault_node->getElementsByTagName(
					'Code');
				$fault_desc_node_list =& $fault_node->getElementsByTagName(
					'Desc');
				$fault_code_node =& $fault_code_node_list->item(0);
				$fault_desc_node =& $fault_desc_node_list->item(0);

				$emsg .= '<li>' . $fault_desc_node->getText() . ' [code ' .
					$fault_code_node->getText() . ']</li>';
			}
			$emsg .= '</ul>';
			return ($emsg);
		}

		/*
		 * No faults in Trace request, now check for faults
		 * in specifing tracking shipment.
		 */
		$fault_node_list =& $xmlResp->getElementsByPath(
		    '//Track/Shipment/Fault');
		if ($fault_node_list->getLength() > 0) {
			/* Error in Track request */
			$emsg = '';
			$emsg .= '<ul>';
			for ($i = 0; $i < $fault_node_list->getLength(); $i++) {
				$fault_node =& $fault_node_list->item($i);
				$fault_code_node_list =& $fault_node->getElementsByTagName(
					'Code');
				$fault_desc_node_list =& $fault_node->getElementsByTagName(
					'Desc');
				$fault_code_node =& $fault_code_node_list->item(0);
				$fault_desc_node =& $fault_desc_node_list->item(0);

				$emsg .= '<li>' . $fault_desc_node->getText() . ' [code ' .
					$fault_code_node->getText() . ']</li>';
			}
			$emsg .= '</ul>';
			return ($emsg);
		}

		/* No faults in shipment, get the tracking data result */
		$res = array();
		$result_node_list =& $xmlResp->getElementsByPath(
		    '//Track/Shipment/Result');
		if ($result_node_list->getLength() > 0) {
			$result_node =& $result_node_list->item(0);
	
			$node_list =& $result_node->getElementsByTagName('Code');
			$node =& $node_list->item(0);
			$res['code'] = $node->getText();

			$node_list =& $result_node->getElementsByTagName('Desc');
			$node =& $node_list->item(0);
			$res['desc'] = $node->getText();

			/*
			 * a non-zero code means there's some weird condition
			 * and we shouldn't bother with the rest of this.
			 */
			if ($res['code'] != 0) {
				$emsg = '<strong>' . $res['desc'] . ' [code ';
				$emsg .= $res['code'] . ']';
				$emsg .= '</strong>';
				return ($emsg);
			}
		}


		/* get shipment information */
		$shipment = array();
		$shipment_node_list =& $xmlResp->getElementsByPath(
		    '//Track/Shipment');
		if ($shipment_node_list->getLength() > 0) {
			$shipment_node =& $shipment_node_list->item(0);

			$node_list =& $shipment_node->getElementsByPath('ShipmentType/Desc');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$shipment['type'] = $node->getText();
			}

			$node_list =& $shipment_node->getElementsByPath('Service/Desc');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$shipment['service'] = $node->getText();
			}

			$node_list =& $shipment_node->getElementsByPath(
				'SpecialServices/SpecialService/Desc');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$shipment['special_service'] = $node->getText();
			}
		}

		/* sender information */
		$sender = array();
		$sender_node_list =& $xmlResp->getElementsByPath(
		    '//Track/Shipment/Sender');
		if ($sender_node_list->getLength() > 0) {
			$sender_node =& $sender_node_list->item(0);

			$node_list =& $sender_node->getElementsByTagName('CompanyName');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$sender['company'] = $node->getText();
			}

			$node_list =& $sender_node->getElementsByTagName('City');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$sender['city'] = $node->getText();
			}

			$node_list =& $sender_node->getElementsByTagName('State');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$sender['state'] = $node->getText();
			}

			$node_list =& $sender_node->getElementsByTagName('PostalCode');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$sender['zip'] = $node->getText();
			}

			$node_list =& $sender_node->getElementsByTagName('Country');
			$node =& $node_list->item(0);
			$sender['country'] = $node->getText();
		}

		/* receiver information */
		$receiver = array();
		$receiver_node_list =& $xmlResp->getElementsByPath(
		    '//Track/Shipment/Receiver');
		if ($receiver_node_list->getLength() > 0) {
			$receiver_node =& $receiver_node_list->item(0);

			$node_list =& $receiver_node->getElementsByTagName('CompanyName');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$receiver['company'] = $node->getText();
			}

			$node_list =& $receiver_node->getElementsByTagName('City');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$receiver['city'] = $node->getText();
			}

			$node_list =& $receiver_node->getElementsByTagName('State');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$receiver['state'] = $node->getText();
			}

			$node_list =& $receiver_node->getElementsByTagName('PostalCode');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$receiver['zip'] = $node->getText();
			}

			$node_list =& $receiver_node->getElementsByTagName('Country');
			$node =& $node_list->item(0);
			$receiver['country'] = $node->getText();
		}

		/* pick-up information */
		$pickup = array();
		$pickup_node_list =& $xmlResp->getElementsByPath(
		    '//Track/Shipment/Pickup');
		if ($pickup_node_list->getLength() > 0) {
			$pickup_node =& $pickup_node_list->item(0);

			$node_list =& $pickup_node->getElementsByTagName('Date');
			$node =& $node_list->item(0);
			$pickup['date'] = $node->getText();

			$node_list =& $pickup_node->getElementsByTagName('Time');
			$node =& $node_list->item(0);
			$pickup['time'] = $node->getText();

			$node_list =& $pickup_node->getElementsByPath('Location/Desc');
			$node =& $node_list->item(0);
			$pickup['location'] = $node->getText();

			$node_list =& $pickup_node->getElementsByTagName(
			    'EstDeliveryDate');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$pickup['est_delivery'] = $node->getText();
			}
		}

		/* delivery information */
		$delivery = array();
		$delivery_node_list =& $xmlResp->getElementsByPath(
		    '//Track/Shipment/Delivery');
		if ($delivery_node_list->getLength() > 0) {
			$delivery_node =& $delivery_node_list->item(0);

			$node_list =& $delivery_node->getElementsByTagName('Date');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$delivery['date'] = $node->getText();
			}

			$node_list =& $delivery_node->getElementsByTagName('Time');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$delivery['time'] = $node->getText();
			}

			$node_list =& $delivery_node->getElementsByTagName('Signatory');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$delivery['signatory'] = $node->getText();
			}

			$node_list =& $delivery_node->getElementsByPath('Location/Desc');
			if ($node_list->getLength() > 0) {
				$node =& $node_list->item(0);
				$delivery['location'] = $node->getText();
			}
		}

		/* tracking information */
		$has_been_delivered = false;
		$delivery_date = '';
		$tracking = array();
		$tracking_node_list =& $xmlResp->getElementsByPath(
		    '//Track/Shipment/TrackingHistory');
		if ($tracking_node_list->getLength() > 0) {

			$tracking_node =& $tracking_node_list->item(0);
			$status_node_list =& $tracking_node->getElementsByTagName(
			    'Status');
			for ($i = 0; $i < $status_node_list->getLength(); $i++) {
				$status = array();
				$status_node =& $status_node_list->item($i);

				$status['seq_number'] = $status_node->getAttribute(
				    'sequence_nbr');
				if ($status_node->hasAttribute('final_delivery'))
					$has_been_delivered = true;

				$node_list =& $status_node->getElementsByTagName(
				    'StatusDesc');
				if ($node_list->getLength() > 0) {
					$node =& $node_list->item(0);
					$status['desc'] = $node->getText();
				}

				$node_list =& $status_node->getElementsByTagName(
				    'Comment');
				if ($node_list->getLength() > 0) {
					$node =& $node_list->item(0);
					$status['comment'] = $node->getText();
				}

				$node_list =& $status_node->getElementsByTagName('Date');
				if ($node_list->getLength() > 0) {
					$node =& $node_list->item(0);
					$status['date'] = $node->getText();
					if ($status_node->hasAttribute('final_delivery'))
						$delivery_date = $status['date'];
				}

				$node_list =& $status_node->getElementsByTagName('Time');
				if ($node_list->getLength() > 0) {
					$node =& $node_list->item(0);
					$status['time'] = $node->getText();
				}

				$node_list =& $status_node->getElementsByTagName(
				    'StatusLocation');
				if ($node_list->getLength() > 0) {
					$node =& $node_list->item(0);
					$status['status_location'] = $node->getText();
				}

				$node_list =& $status_node->getElementsByPath(
				    'Location/City');
				if ($node_list->getLength() > 0) {
					$node =& $node_list->item(0);
					$status['city'] = $node->getText();
				}

				$node_list =& $status_node->getElementsByPath(
				    'Location/State');
				if ($node_list->getLength() > 0) {
					$node =& $node_list->item(0);
					$status['state'] = $node->getText();
				}

				$node_list =& $status_node->getElementsByPath(
				    'Location/Country');
				if ($node_list->getLength() > 0) {
					$node =& $node_list->item(0);
					$status['country'] = $node->getText();
				}

				$tracking[$status['seq_number']] = $status;
			}
		}

		/* If the shipment is delivered, get signature */
		$have_signature = false;
		if ($has_been_delivered)
			$have_signature = $this->get_signature($order_id, $delivery_date);

		/*
		 * Some might argue that this HTML code shouldn't be here.  And
		 * maybe it shouldn't, but here's my rational.
		 *
		 * Other shipping methods might implement tracking and that would
		 * seem to suggest that common HTML code in a separate file would
		 * save a lot of redundant HTML code.  However, until we see
		 * more implementation, we don't know what type of data other
		 * shipping modules will want to display for their tracking
		 * implementation.
		 *
		 * I think it is likely other implementations will have very
		 * different data to display and thus common HTML won't help.
		 * I think the HTML code to display tracking should
		 * be customized for each shipping module that implements tracking.
		 * Thus, the shipping->track() method returns customized HTML code
		 * to display it's tracking data.
		 */
		$msg = '<p><strong>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_HISTORY');
		$msg .= '</strong><br />';
		/* Now we've got all our data - format it into HTML */
		if (count($tracking) == 0) {
			$msg .= '<strong>';
			$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_NO_DATA');
			$msg .= '</strong>';
		} else {
			/* sort array by steps in reverse order */
			krsort($tracking);

			$msg .= '<table border="0" cellspacing="0" cellpadding="2" ';
			$msg .= 'width="100%">';
			$msg .= "\n";

			foreach ($tracking as $snum => $step) {
				$msg .= '<tr>';
				$msg .= "\n";
				$msg .= '<td>' . $snum . '</td>';
				$msg .= "\n";
				$msg .= '<td>';
				if (array_key_exists('date', $step))
					$msg .= $step['date'];
				$msg .= '</td>';
				$msg .= "\n";
				$msg .= '<td>';
				if (array_key_exists('time', $step))
					$msg .= $step['time'];
				$msg .= '</td>';
				$msg .= "\n";
				$msg .= '<td>';
				if (array_key_exists('status_location', $step))
					$msg .= $step['status_location'];
				$msg .= '</td>';
				$msg .= "\n";
				$msg .= '<td>';
				if (array_key_exists('desc', $step))
					$msg .= $step['desc'];
				$msg .= '</td>';
				$msg .= "\n";
				$msg .= '</tr>';
				$msg .= "\n";
				if (array_key_exists('comment', $step)) {
					$msg .= '<tr>';
					$msg .= '<td>' . $snum . '</td>';
					$msg .= '<td colspan="6">' . $step['comment'] . '</td>';
					$msg .= '</tr>';
					$msg .= "\n";
				}
			}

			$msg .= '</table>';
		}

		if ($have_signature) {
			/* If we have a signaure, display it */
			$image_url = $sess->url($_SERVER['PHP_SELF'] .
			    "?page=order.label_signature&order_id=" .
			    $order_id . "&no_menu=1&no_html=1");
			$image_url = stristr($image_url, "index2.php") ?
			    str_replace("index2.php", "index3.php", $image_url) :
			    str_replace("index.php", "index2.php", $image_url);

			$dim = $this->get_signature_dimensions($order_id);
			$dim_arr = explode("x", $dim);
			$dim_x = $dim_arr[0];
			$dim_y = $dim_arr[1];

			$msg .= '<br />';
			$msg .= "\n";
			$msg .= '<object data="' . $image_url . '", ';
			$msg .= 'type="' . $this->get_signature_image_type($order_id) . '", ';
			$msg .= 'height="' . $dim_y . '", width="' . $dim_x . '" ';
			$msg .= "></object>\n";

			$msg .= "\n";

			$msg .= '<p>';
			$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_SIGNATURE_LEGEND');
			$msg .= '<ul>';
			$msg .= "\n";
			$msg .= '<li>';
			$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_LEGEND_LD');
			$msg .= '</li>';
			$msg .= "\n";
			$msg .= '<li>';
			$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_LEGEND_FD');
			$msg .= '</li>';
			$msg .= "\n";
			$msg .= '<li>';
			$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_LEGEND_SD');
			$msg .= '</li>';
			$msg .= "\n";
			$msg .= '<li>';
			$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_LEGEND_BD');
			$msg .= '</li>';
			$msg .= "\n";
			$msg .= '<li>';
			$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_LEGEND_GAR');
			$msg .= '</li>';
			$msg .= "\n";
			$msg .= '<li>';
			$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_LEGEND_LOF');
			$msg .= '</li>';
			$msg .= "\n";
			$msg .= '<li>';
			$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_LEGEND_LPN');
			$msg .= '</li>';
			$msg .= "\n";
			$msg .= '</ul>';

			$msg .= '<br />';
		}

		$msg .= '<hr />';

		/* shipment */
		$msg .= '<strong>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_NUMBER') . ': ';
		$msg .= '</strong>';
		$msg .= ' ';
		$msg .= $tracking_number;
		$msg .= '<br />';
		$msg .= "\n";

		$msg .= '<strong>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_PACKAGE') . ': ';
		$msg .= '</strong>';
		$msg .= ' ';
		if (array_key_exists('type', $shipment))
			$msg .= $shipment['type'];
		$msg .= '<br />';
		$msg .= "\n";

		$msg .= '<strong>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_SERVICE') . ': ';
		$msg .= '</strong>';
		$msg .= ' ';
		if (array_key_exists('service', $shipment))
			$msg .= $shipment['service'];
		if (array_key_exists('special_service', $shipment))
			$msg .= ': ' . $shipment['special_service'];
		$msg .= '<br />';
		$msg .= "\n";

		$msg .= '<hr />';

		/* Sender / Receiver */
		$msg .= '<table width="100%" align="center" border="0" cellspacing="0"';
		$msg .= ' cellpadding="2">';
		$msg .= "\n";

		$msg .= '<tr>';
		$msg .= '<td><strong>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_SENDER');
		$msg .= '</strong></td>';
		$msg .= '<td><strong>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_RECEIVER');
		$msg .= '</strong></td>';
		$msg .= '</tr>';

		$msg .= '<tr>';
		$msg .= '<td>';
		if (array_key_exists('company', $sender))
			$msg .= $sender['company'];
		$msg .= '</td>';
		$msg .= '<td>';
		if (array_key_exists('company', $receiver))
			$msg .= $receiver['company'];
		$msg .= '</td>';
		$msg .= '</tr>';
		$msg .= "\n";

		$msg .= '<tr>';
		$msg .= '<td>';
		if (array_key_exists('city', $sender)) {
			$msg .= $sender['city'];
			if (array_key_exists('state', $sender))
				$msg .= ', ';
		}
		if (array_key_exists('state', $sender))
			$msg .= $sender['state'];
		$msg .= '</td>';
		$msg .= '<td>';
		if (array_key_exists('city', $receiver)) {
			$msg .= $receiver['city'];
			if (array_key_exists('state', $receiver))
				$msg .= ', ';
		}
		if (array_key_exists('state', $receiver))
			$msg .= $receiver['state'];
		$msg .= '</td>';
		$msg .= '</tr>';
		$msg .= "\n";

		$msg .= '<tr>';
		$msg .= '<td>';
		if (array_key_exists('zip', $sender))
			$msg .= $sender['zip'];
		$msg .= '</td>';
		$msg .= '<td>';
		if (array_key_exists('zip', $receiver))
			$msg .= $receiver['zip'];
		$msg .= '</td>';
		$msg .= '</tr>';
		$msg .= "\n";

		$msg .= '<tr>';
		$msg .= '<td>';
		if (array_key_exists('country', $sender))
			$msg .= $sender['country'];
		$msg .= '</td>';
		$msg .= '<td>';
		if (array_key_exists('country', $receiver))
			$msg .= $receiver['country'];
		$msg .= '</td>';
		$msg .= '</tr>';
		$msg .= "\n";

		$msg .= '</table>';
		$msg .= "\n";

		$msg .= '<hr />';

		$msg .= '<table width="100%" align="center" border="0" cellspacing="0"';
		$msg .= ' cellpadding="2">';
		$msg .= "\n";

		$msg .= '<tr>';
		$msg .= '<td>';
		$msg .= '<strong>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_PICKUP');
		$msg .= '</strong>';
		$msg .= '</td>';

		$msg .= '<td>';
		$msg .= '<strong>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_DELIVERY');
		$msg .= '</strong>';
		$msg .= '</td>';
		$msg .= '</tr>';
		$msg .= "\n";


		$msg .= '<tr>';
		$msg .= '<td>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_DATE') . ': ';
		if (array_key_exists('date', $pickup))
			$msg .= $pickup['date'];
		$msg .= '</td>';
		$msg .= '<td>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_DATE') . ': ';
		if (array_key_exists('date', $delivery))
			$msg .= $delivery['date'];
		$msg .= '</td>';
		$msg .= '</tr>';
		$msg .= "\n";


		$msg .= '<tr>';
		$msg .= '<td>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_TIME') . ': ';
		if (array_key_exists('time', $pickup))
			$msg .= $pickup['time'];
		$msg .= '</td>';
		$msg .= '<td>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_TIME') . ': ';
		if (array_key_exists('time', $delivery))
			$msg .= $delivery['time'];
		$msg .= '</td>';
		$msg .= '</tr>';
		$msg .= "\n";


		$msg .= '<tr>';
		$msg .= '<td>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_LOCATION') . ': ';
		if (array_key_exists('location', $pickup))
			$msg .= $pickup['location'];
		$msg .= '</td>';
		$msg .= '<td>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_LOCATION') . ': ';
		if (array_key_exists('location', $delivery))
			$msg .= $delivery['location'];
		$msg .= '</td>';
		$msg .= '</tr>';
		$msg .= "\n";


		$msg .= '<tr>';
		$msg .= '<td>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_EST_DEL') . ': ';
		if (array_key_exists('est_delivery', $pickup))
			$msg .= $pickup['est_delivery'];
		$msg .= '</td>';
		$msg .= '<td>';
		$msg .= JText::_('VM_SHIPPING_METHOD_DHL_TRACKING_SIGNATORY') . ': ';
		if (array_key_exists('signatory', $delivery))
			$msg .= $delivery['signatory'];
		$msg .= '</td>';
		$msg .= '</tr>';
		$msg .= "\n";
		$msg .= '</table>';
		$msg .= "\n";

		return ($msg);
	}


	function get_signature($order_id, $delivery_date) {
		global $vmLogger;
		global  $mosConfig_absolute_path;

		/* Retrieve waybill information from database */
		$dbl =& new ps_DB;
		$q = "SELECT tracking_number, label_is_generated, is_international, ";
		$q .= "have_signature ";
		$q .= "FROM #__{vm}_shipping_label ";
		$q .= "WHERE order_id = '" . $order_id . "'";
		$dbl->query($q);
		if (!$dbl->next_record() || !$dbl->f("label_is_generated"))
			return ("couldn't find label info for order #" .  $order_id);

		if ($dbl->f('have_signature'))
			return (true);

		$tracking_number = $dbl->f('tracking_number');

		$dhl_url = "https://eCommerce.airborne.com/";
		if ($this->params->get('DEBUG'))
			$dhl_url .= "ApiLandingTest.asp";
		else
			$dhl_url .= "ApiLanding.asp";

		require_once($mosConfig_absolute_path .
			'/includes/domit/xml_domit_lite_include.php');

		$xmlReq =& new DOMIT_Lite_Document();

		$xmlReq->setXMLDeclaration('<?xml version="1.0"?>' ); 
		$root =& $xmlReq->createElement('eCommerce'); 
		$root->setAttribute('action', 'Request'); 
		$root->setAttribute('version', '1.1'); 
		$xmlReq->setDocumentElement($root); 

		$requestor =& $xmlReq->createElement('Requestor'); 
		$id =& $xmlReq->createElement('ID');
		$id->setText($this->params->get('DHL_ID'));
		$requestor->appendChild($id);
		$password =& $xmlReq->createElement('Password');
		$password->setText($this->params->get('DHL_PASSWORD'));
		$requestor->appendChild($password);
		$root->appendChild($requestor);

		/* Signature Request */
		$signature =& $xmlReq->createElement('Signature');
		$signature->setAttribute('action', 'Get'); 
		$signature->setAttribute('version', '1.0'); 

		$shipment =& $xmlReq->createElement('Shipment');
		$nbr =& $xmlReq->createElement('TrackingNbr');
		$nbr->setText($tracking_number);
		$shipment->appendChild($nbr);

		$delivery =& $xmlReq->createElement('Delivery');
		$date =& $xmlReq->createElement('Date');
		$start =& $xmlReq->createElement('Start');
		$start->setText($delivery_date);
		$date->appendChild($start);
		$end =& $xmlReq->createElement('End');
		$end->setText($delivery_date);
		$date->appendChild($end);
		$delivery->appendChild($date);
		$shipment->appendChild($delivery);

		$signature->appendChild($shipment);

		$root->appendChild($signature);

//		$vmLogger->err($xmlReq->toNormalizedString());

		if (function_exists( "curl_init" )) {
			$CR = curl_init();
			curl_setopt($CR, CURLOPT_URL, $dhl_url);
			curl_setopt($CR, CURLOPT_POST, 1);
			curl_setopt($CR, CURLOPT_FAILONERROR, true);
			curl_setopt($CR, CURLOPT_POSTFIELDS, $xmlReq->toString());
			curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);

			$xmlResult = curl_exec($CR);
			$error = curl_error($CR);
			curl_close($CR);
			if (!empty($error))
				return (false);
		}

		// XML Parsing
		$xmlResp =& new DOMIT_Lite_Document();
		if (!$xmlResp->parseXML($xmlResult, false, true))
			return (false);
//		$vmLogger->err($xmlResp->toNormalizedString());

		// Check for success or failure.
		$result_code_list =& $xmlResp->getElementsByPath('//Result/Code');
		$result_code =& $result_code_list->item(0);
		$result_desc_list =& $xmlResp->getElementsByPath('//Result/Desc');
		$result_desc =& $result_desc_list->item(0);
		if ($result_code == NULL) {
			$vmLogger->debug(
				JText::_('VM_SHIPPING_METHOD_DHL_MISSING_RESULT') .
				"\n" . $xmlResp->toNormalizedString());
			return (false);
		}

		/* 0 is the code for success with viewing a signature. */
		if ($result_code->getText() != 0) {
			$err_msg = '<br /><span class="message">' .
				$result_desc->getText() . ' [code ' .
				$result_code->getText() . ']' .
				'</span>';

			// display an error line for each fault
			$fault_node_list =& $xmlResp->getElementsByPath(
				'//Faults/Fault');
			if ($fault_node_list->getLength() > 0)
				$err_msg .= '<ul>';
			for ($i = 0; $i < $fault_node_list->getLength(); $i++) {
				$fault_node =& $fault_node_list->item($i);
				$fault_code_node_list =& $fault_node->getElementsByTagName(
					'Code');
				$fault_desc_node_list =& $fault_node->getElementsByTagName(
					'Desc');
				$fault_code_node =& $fault_code_node_list->item(0);
				$fault_desc_node =& $fault_desc_node_list->item(0);

				$err_msg .= '<li>' . $fault_desc_node->getText() . ' [code ' .
					$fault_code_node->getText() . ']</li>';
			}
			if ($fault_node_list->getLength() > 0)
				$err_msg .= '</ul>';
//			echo $err_msg;
			return (false);
		}

		$signature_image_node_list =& $xmlResp->getElementsByPath(
		    '//Signature/Image');
		$signature_image_node =& $signature_image_node_list->item(0);
		if ($signature_image_node == NULL)
			return (false);
		$signature_image = $signature_image_node->getText();


		/*
		 * insert signature image into database and mark that the signature
		 * has been retrieved.
		 */
		$q = "UPDATE #__{vm}_shipping_label ";
		$q .= "SET ";
		$q .= "have_signature='1', ";
		$q .= "signature_image='" . $signature_image . "' ";
		$q .= "WHERE order_id = '" . $order_id . "'";

		$dbnl =& new ps_DB;
		$dbnl->query($q);
		$dbnl->next_record();


		return (true);
	}

	function get_signature_dimensions($order_id) {

		return ('256x95');
	}

	function get_signature_image_type($order_id) {

		return ('image/jpeg');
	}

	function get_signature_image($order_id) {
		global $vmLogger;

		/* Retrieve signature information from database */
		$dbl =& new ps_DB;
		$q = "SELECT have_signature, signature_image ";
		$q .= "FROM #__{vm}_shipping_label ";
		$q .= "WHERE order_id = '" . $order_id . "'";
		$dbl->query($q);
		if (!$dbl->next_record()) {
			$vmLogger->err("couldn't find signature for order #" .
			    $order_id);
			return;
		}

		if (!$dbl->f('have_signature')) {
			$vmLogger->err("signature has not been generated");
			return;
		}

		$signature_image = base64_decode($dbl->f('signature_image'));
		return ($signature_image);
	}


	function get_shippingtax_rate() {

		if (intval($this->params->get('DHL_TAX_CLASS')) == 0)
			return (0);
		else {
			require_once(CLASSPATH . "ps_tax.php");
			$tax_rate = ps_tax::get_taxrate_by_id(intval($this->params->get('DHL_TAX_CLASS')));
			return ($tax_rate);
		}
	}


	function calc_duty_value(&$d) {

		$cart = $_SESSION['cart'];

		$total_duty_value = 0;
		for ($i = 0; $i < $cart['idx']; $i++) {
			$item_value = $this->get_duty_value(
			    $cart[$i]['product_id']) * $cart[$i]['quantity'];
			$total_duty_value += $item_value;
		}
		$total_duty_value = round($total_duty_value, 2);

		return ($total_duty_value);
	}

	/*
	 * Get's a product's price using the shopper group "DUTY"
	 * It would be handy if this was a function in ps_product.php,
	 * but I don't really want to go mucking up someone else's work.
	 */
	function get_duty_value($pid) {

		$db = new ps_DB;

		if ( $this->params->get('DHL_DUTY_SHOPPER_GROUP')<5 ) {
			// no valid group was specific for duty prices, use normal price
			$ps_product = new ps_product;
			$p_array = $ps_product->get_price($pid);
			$duty_value = $p_array['product_price'];
		} else {
			$sgid = (int)$this->params->get('DHL_DUTY_SHOPPER_GROUP');

			$q = "SELECT product_price FROM #__{vm}_product_price ";
			$q .= "WHERE product_id='" . $pid . "' ";
			$q .= "AND shopper_group_id='" . $sgid . "'";
			$db->query($q);
			if ($db->next_record()) {
				$duty_value = $db->f("product_price");
			} else {
				/* use the default product price */
				$ps_product = new ps_product;
				$p_array = $ps_product->get_price($pid);
				$duty_value = $p_array['product_price'];
			}
		}
		return ($duty_value);
	}

	function calc_insurance_value(&$d, $is_international) {

		$cart = $_SESSION['cart'];

		$total_insurance_value = 0;
		for ($i = 0; $i < $cart['idx']; $i++) {
			$item_value = $this->get_insurance_value(
			    $cart[$i]['product_id']) * $cart[$i]['quantity'];
			$total_insurance_value += $item_value;
		}

		return ($this->adjust_insurance($total_insurance_value, $d['weight'],
		    $is_international));
	}

	function adjust_insurance($ivalue, $weight, $is_international) {

		/*
		 * Compare insurance value against nominal insurance value.
		 * If we are less than the default coverage, return an
		 * insurance value of 0.
		 */

		if (!$is_international) {
			$default_insurance_value = floatval(
			    $this->params->get('DHL_INSURANCE_RATE_DOMESTIC_FLAT'));
		} else {
			$default_insurance_value = $weight *
			    floatval($this->params->get('DHL_INSURANCE_RATE_INTERNATIONAL'));
		}
		$default_insurance_value = round($default_insurance_value, 2);
		if ($default_insurance_value > $ivalue)
			$ivalue = 0;

		return ($ivalue);
	}

	/*
	 * Get's a value to be used for insurance purposes.  We look for
	 * a special group price of "INSURANCE" (or whatever was configured)
	 * and use it.  If there is no special INSURANCE price, we use the
	 * normal price.
	 */
	function get_insurance_value($pid) {

		$db = new ps_DB;

		if ( $this->params->get('DHL_INSURANCE_SHOPPER_GROUP')<5 ) {
			/* no group was specific for insurnace value, use normal price */
			$ps_product = new ps_product;
			$p_array = $ps_product->get_price($pid);
			$duty_value = $p_array['product_price'];
		} else {
			$sgid = (int)$this->params->get('DHL_INSURANCE_SHOPPER_GROUP');

			$q = "SELECT product_price FROM #__{vm}_product_price ";
			$q .= "WHERE product_id='" . $pid . "' ";
			$q .= "AND shopper_group_id='" . $sgid . "'";
			$db->query($q);
			if ($db->next_record()) {
				$duty_value = $db->f("product_price");
			} else {
				/* use the default product price */
				$ps_product = new ps_product;
				$p_array = $ps_product->get_price($pid);
				$duty_value = $p_array['product_price'];
			}
		}
		return ($duty_value);
	}


	function is_international($country, $state) {

		$states = array("AK", "AR", "AZ", "CA", "CO", "CT",
			"DC", "DE", "FL", "GA", "HI", "IA", "ID", "IL",
			"IN", "KS", "KY", "LA", "MA", "MD", "ME", "MI",
			"MN", "MO", "MS", "MT", "NC", "ND", "NE", "NH",
			"NJ", "NM", "NV", "NY", "OH", "OK", "OR", "PA",
			"RI", "SC", "SD", "TN", "TX", "UT", "VT", "VA",
			"WA", "WI", "WV", "WY");

		$is_intl = ($country != "US" || !in_array($state, $states));
		return ($is_intl);
	}

	function remap_country_code($country, $state) {

		/*
		 * DHL doesn't follow ISO 6133
		 * http://www.iso.org/iso/en/prods-services/iso3166ma/02iso-3166-code-lists/list-en1.html
		 * This function remaps the two character country codes to something
		 * DHL will accept.
		 *
		 * We need to check states too in some cases.
		 */
		if ($country == "GB") {
			$uk_states = array(
				'EN' => 'UK-ENGLAND',
				'NI' => 'UK-NORTHERN IRELAND',
				'SD' => 'UK-SCOTLAND',
				'WS' => 'UK-WALES'
			);
			if (!array_key_exists($state, $uk_states)) {
				$vmLogger->err('Unknown UK state:' . $state);
				$dhl_country_code = 'UK';
			} else {
				$dhl_country_code = $uk_states[$state];
			}
		} else if ($country == 'IL') {
			$il_states = array(
				'GZ' => 'IL-GAZA STRIP',
				'WB' => 'IL-WEST BANK',
				'OT' => 'IL'
			);
			if (!array_key_exists($state, $il_states)) {
				$vmLogger->err('Unknown IL state:' . $state);
				$dhl_country_code = 'IL';
			} else {
				$dhl_country_code = $il_states[$state];
			}
		} else if ($country == 'AN') {
			$an_states = array(
				'SM' => 'AN-ST. MAARTEN',
				'BN' => 'AN-BONAIRE',
				'CR' => 'AN-CURACAO'
			);
			if (!array_key_exists($state, $an_states)) {
				$vmLogger->err('Unknown AN state:' . $state);
				$dhl_country_code = 'AN';
			} else {
				$dhl_country_code = $an_states[$state];
			}
		} else {
			/*
			 * These are special countries not in the ISO 3361 list,
			 * but used by DHL.
			 */
			$special_countries = array(
				'XE' => 'EAST TIMOR',
				'XJ' => 'JERSEY',
				'XB' => 'ST. BARTHELEMY',
				'XU' => 'ST. EUSTATIUS',
				'XC' => 'CANARY ISLANDS'
			);
			if (!array_key_exists($country, $special_countries)) {
				$dhl_country_code = $country;
			} else {
				$dhl_country_code = $special_countries[$country];
			}
		}

		return ($dhl_country_code);
	}
}
?>