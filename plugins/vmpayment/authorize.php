<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* The ps_authorize class, containing the payment processing code
*  for transactions with authorize.net 
*
* @version $Id$
* @package VirtueMart
* @subpackage payment
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

class plgVmPaymentAuthorize extends vmPaymentPlugin {
	var $_pelement;
	var $_pcode = 'AN';

	private $_cc_name = '';
	private $_cc_number = '';
	private $_cc_code = '';
	private $_cc_expire_month = '';
	private $_cc_expire_year = '';

	var $selectedCC = '0';
	var $paym_creditcards = '0';
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgVmPaymentAuthorize(& $subject, $config) {
		$this->_pelement = basename(__FILE__, '.php');
		$this->_createTable();
		parent::__construct($subject, $config);
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * @author Oscar van Eijk
	 */
	protected function _createTable()
	{
		$_db = JFactory::getDBO();
		$_q = 'CREATE TABLE IF NOT EXISTS `#__vm_order_payment_' . $this->_pelement . '` ('
			. ' `id` INT(11) NOT NULL AUTO_INCREMENT'
			. ',`order_id` INT(11) NOT NULL'
			. ',`payment_method_id` INT(11) NOT NULL'
			. ',`order_payment_status` INT(11) NOT NULL DEFAULT 1'
			. ',`order_payment_number` BLOB'
			. ',`order_payment_expire` INT(11) DEFAULT NULL'
			. ',`order_payment_name` VARCHAR(255) DEFAULT NULL'
			. ',`order_payment_log` TEXT'
			. ",`order_payment_trans_id` TEXT NOT NULL DEFAULT ''"
			. ',PRIMARY KEY (`id`)'
			. ',KEY `idx_order_payment_' . $this->_pelement . '_order_id` (`order_id`)'
			. ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Data for the " . $this->_pelement . " payment plugin.'";
	$_db->setQuery($_q);
	if (!$_db->query()) {
			JError::raiseWarning(500, $_db->getErrorMsg());
		}
	}

	/**
	 * This shows the plugin for choosing in the payment list of the checkout process.
	 * 
	 * @author Max Milbers
	 */
	function plgVmOnShowList($cart,$checkedPaymId=0){
		
		if(!$this -> setVmParams($cart['vendor_id'])) return ;
		
		if($checkedPaymId==$this->paymentMethod->paym_id) $checked = '"checked"'; else $checked = '';
		
		$html = '<fieldset>';
		$html .= '<input type="radio" name="paym_id" value="'.$this->paymentMethod->paym_id.'" '.$checked.'>'.$this->paymentMethod->paym_name.' ';

	
		if($this->paymentMethod->paym_creditcards){
			$html .= ($this->paymentModel->renderCreditCardRadioList($this->selectedCC,$this->paymentMethod->paym_creditcards));
		}else {
			$html .= '<br />';
		}
		if(!empty($this->cart['cc_name'])) $this->_cc_name = $this->cart['cc_name'];
		if(!empty($this->cart['cc_number'])) $this->_cc_number = $this->cart['cc_number'];
		if(!empty($this->cart['cc_code'])) $this->_cc_code = $this->cart['cc_code'];
		if(!empty($this->cart['cc_expire_month'])) $this->_cc_expire_month = $this->cart['cc_expire_month'];
		if(!empty($this->cart['cart_cc_expire_year'])) $this->_cc_expire_year = $this->cart['cart_cc_expire_year'];

		$html .= '<fieldset>
				<table border="0" cellspacing="0" cellpadding="2" width="100%">
			    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="order_payment_name">'.JText::_('VM_CHECKOUT_PAYINFO_NAMECARD') .':</label>
		        </td>
		        <td>
		        <input type="text" class="inputbox" id="cart_cc_name" name="cart_cc_name" value="'. $this->_cc_name .'" autocomplete="off" />
		        </td>
		    </tr>
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="order_payment_number">'. JText::_('VM_CHECKOUT_PAYINFO_CCNUM') .':</label>
		        </td>
		        <td>
		        <input type="text" class="inputbox" id="cart_cc_number" name="cart_cc_number" value="'.$this->_cc_number.'" autocomplete="off" />
		        </td>
		    </tr>
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="credit_card_code">'. JText::_('VM_CHECKOUT_PAYINFO_CVV2')  .': </label>
		        </td>		        		
		        <td>
		            <input type="text" class="inputbox" id="cart_cc_code" name="cart_cc_code" value="'. $this->_cc_code.'" autocomplete="off" />   
		        </td>
		    </tr>
		    <tr>
		        <td nowrap width="10%" align="right">'. JText::_('VM_CHECKOUT_PAYINFO_EXDATE').':</td>
		        <td> '.$this->_cc_expire_month;
		        $html .= shopfunctions::listMonths('cart_cc_expire_month', $this->_cc_expire_month );
		        $html .= "/";
		        $html .= $this->_cc_expire_year;
		        $html .= shopfunctions::listYears('cart_cc_expire_year', $this->_cc_expire_year);
		        $html .= '</td>  </tr>  	</table>';
    	
		$html .= ' </fieldset> </fieldset>';
		return $html;
		
	}

	/**
	 * This is for checking the input data of the payment method within the checkout
	 * 
	 * @author Max Milbers
	 */
	function plgVmOnCheckoutCheckPaymentData($cart){
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'models'.DS.'paymentmethod.php');
		if(VirtueMartModelPaymentmethod::hasCreditCard($cart['paym_id'])){
			if(empty($cart['creditcard_id']) ||
				empty($cart['cc_name']) ||
				empty($cart['cc_number']) ||
				empty($cart['cc_code']) ||
				empty($cart['cc_expire_month']) ||
				empty($cart['cc_expire_year'])){
					$cart['inCheckOut'] = true;
					$confirmDone=false;
					$this->editpayment();
					return;
			}
//			$this->setpayment(false);	//For what was this case? internal notice Max
		}
	}
	
	/**
	 * This is for adding the input data of the payment method to the cart, after selecting
	 * 
	 * @author Max Milbers
	 */
	function plgVmOnPaymentSelectCheck(&$cart){
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'models'.DS.'paymentmethod.php');
		if(VirtueMartModelPaymentmethod::hasCreditCard($cart['paym_id'])){
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'models'.DS.'creditcard.php');
			$cart['creditcard_id']= JRequest::getVar('creditcard', '0');
			$cart['cc_name']= JRequest::getVar('cart_cc_name', '');
			$cart['cc_number']= JRequest::getVar('cart_cc_number', '');
			$cart['cc_code']= JRequest::getVar('cart_cc_code', '');
			$cart['cc_expire_month']= JRequest::getVar('cart_cc_expire_month', '');
			$cart['cc_expire_year']= JRequest::getVar('cart_cc_expire_year', '');
			if(!empty($cart['creditcard_id'])){
				$cc_ = VirtueMartModelCreditcard::getCreditCard($cart['creditcard_id']);
				$cc_type = $cc_->creditcard_code;
				return VirtueMartModelCreditcard::validate_creditcard_data($cc_type,$cart['cc_number']);
			}
		}
		return false;
	}

	/**
	 * Reimplementation of vmPaymentPlugin::plgVmOnCheckoutCheckPaymentData()
	 *
	 * @param int $_orderNr
	 * @param array $_orderData
	 * @param array $_priceData
	 * @author Oscar van Eijk
	 */
	function plgVmOnConfirmedOrderStorePaymentData($_orderNr, $_orderData, $_priceData)
	{
		if (!$this->selectedThisMethod($this->_pelement, $_orderData['paym_id'])) {
			return; // Another method was selected, do nothing
		}
		$this->_paym_id = $_orderData['paym_id'];
		$_transKey = $this->get_passkey();
		if( $_transKey === false ) return;

		// Load the required helpers
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'connection.php');
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'vendorhelper.php');

		$_usr =& JFactory::getUser();

		$_usrBT = $_orderData['BT'];
		$_usrST = (($_orderData['ST'] === null) ? $_orderData['BT'] : $_orderData['ST']);

		$database = JFactory::getDBO();
		
		$_vendorID = $_orderData['vendor_id']; 
		$_vendorCurrency = Vendor::getVendorCurrencyCode($_vendorID);

		// Option to send email to merchant from gateway
		if ($this->params->get('AN_EMAIL_MERCHANT') == '0') {
				$vendor_mail = "";
 		}
		if ($this->params->get('AN_EMAIL_CUSTOMER') == '1') {
			$email_customer = "TRUE";
		} else {
			$email_customer = "FALSE";
 		}
 		$_testReq = $this->params->get('DEBUG') == 1 ? 'YES' : 'NO';

 		//Authnet vars to send
		$_data = array (
			'x_version' => '3.1',
			'x_login' => $this->params->get('AN_LOGIN'),
			'x_tran_key' => $_transKey,
			'x_test_request' => $_testReq,

			// Gateway Response Configuration
			'x_delim_data' => 'TRUE',
			'x_delim_char' => '|',
			'x_relay_response' => 'FALSE',

			// Customer Name and Billing Address
			'x_first_name' => substr($_usrBT['first_name'], 0, 50),
			'x_last_name' => substr($_usrBT['last_name'], 0, 50),
			'x_company' => substr($_usrBT['company'], 0, 50),
			'x_address' => substr($_usrBT['address_1'], 0, 60),
			'x_city' => substr($_usrBT['city'], 0, 40),
			'x_state' => substr(ShopFunctions::getStateByID($_usrBT['state_id']), 0, 40),
			'x_zip' => substr($_usrBT['zip'], 0, 20),
			'x_country' => substr(ShopFunctions::getCountryByID($_usrBT['country_id']), 0, 60),
			'x_phone' => substr($_usrBT['phone_1'], 0, 25),
			'x_fax' => substr($_usrBT['fax'], 0, 25),

			// Customer Shipping Address
			'x_first_name' => substr($_usrST['first_name'], 0, 50),
			'x_last_name' => substr($_usrST['last_name'], 0, 50),
			'x_company' => substr($_usrST['company'], 0, 50),
			'x_address' => substr($_usrST['address_1'], 0, 60),
			'x_city' => substr($_usrST['city'], 0, 40),
			'x_state' => substr(ShopFunctions::getStateByID($_usrST['state_id']), 0, 40),
			'x_zip' => substr($_usrST['zip'], 0, 20),
			'x_country' => substr(ShopFunctions::getCountryByID($_usrST['country_id']), 0, 60),

			// Additional Customer Data
			'x_cust_id' => $_usr->get('id'),
			'x_customer_ip' => $_SERVER["REMOTE_ADDR"],
			'x_customer_tax_id' => $_cart['tax_id'], // TODO, where can I get this???

			// Email Settings
			'x_email' => $_usrBT['email'],
			'x_email_customer' => $email_customer,
			'x_merchant_email' => $vendor_mail,

			// Invoice Information
			'x_invoice_num' => substr($_orderNr, 0, 20),
			'x_description' => JText::_('VM_ORDER_PRINT_PO_LBL'),

			// Transaction Data
			'x_amount' => $_priceData['salesPrice'],
			'x_currency_code' => $_vendorCurrency,
			'x_method' => 'CC',
			'x_type' => AN_TYPE,
			'x_recurring_billing' => AN_RECURRING,

			'x_card_num' => $_orderData['cc_number'],
			'x_card_code' => $_orderData['cc_code'],
			'x_exp_date' => ($_orderData['cc_expire_month']) . ($_orderData['cc_expire_year']),

			// Level 2 data
			'x_po_num' => substr($_orderNr, 0, 20),
			'x_tax' => substr($d['order_tax'], 0, 15),
			'x_tax_exempt' => "FALSE",
			'x_freight' => $d['order_shipping'],
			'x_duty' => 0
		);

		$_qstring = '';
		foreach($_data AS $_k => $_v){
			$_qstring .= (empty($_qstring) ? '' : '&')
					. urlencode($_k) . '=' . urlencode($_v);
		}

		// Prepare data that should be stored in the database
		$_dbValues['order_id'] = $_orderNr;
		$_dbValues['payment_method_id'] = $this->_paym_id;
		if (VmConfig::get('store_creditcard_data')) {
			$_dbValues['order_payment_number'] = $_orderData['cc_number'];
			$_dbValues['order_payment_expire'] = ($_orderData['cc_expire_month']) . ($_orderData['cc_expire_year']);
			$_dbValues['order_payment_name'] = $_orderData['cc_name'];
		}
		
		$_host = 'secure.authorize.net';
		$_port = 443;
		$_uri = 'gateway/transact.dll';
		$_result = VmConnector::handleCommunication( "https://$_host:$_port/$_uri", $_qstring );
		
		if(!$_result) {
			JError::raiseError(500, JText::_('The transaction could not be completed.'));
			$_dbValues['order_payment_status'] = -1;
		} else {
			$_response = explode("|", $_result);
			$_response[0] = str_replace( '"', '', $_response[0] ); // Strip quotes

			$_dbValues['order_payment_status'] = $_response[0];

			if ($_response[0] == '1') { // Succeeded
				$_dbValues['order_payment_log'] = JText::_('VM_PAYMENT_TRANSACTION_SUCCESS').': '
					. $_response[3]; // Transaction log
				$_dbValues['order_payment_trans_id'] = $_response[6]; // Transaction ID
			} else { // 2 (Declined) or 3 (Transaction error)
				if ($this->params->get('AN_SHOW_ERROR_CODE') == '1') {
					$_log = $_response[0] . '-'
						. $_response[1] . '-'
						. $_response[2] . '-'
						. $_response[5] . '-'
						. $_response[38] . '-'
						. $_response[39] . '-'
						. $_response[3];
				} else {
					$_log = $_response[3];
				}
				JError::raiseWarning(500, $_log);
				$_dbValues['order_payment_log'] = $_log; // Transaction log
				$_dbValues['order_payment_trans_id'] = $_response[6]; // Transaction ID
			}
			$_dbValues['order_payment_log'] = $_response[3]; // Transaction log
			$_dbValues['order_payment_trans_id'] = $_response[6]; // Transaction ID
		}
		$this->writePaymentData($_dbValues, '#__vm_order_payment_' . $this->_pelement);
	}

	/**************************************************************************
	** name: capture_payment()
	** created by: Soeren
	** description: Process a previous transaction with authorize.net, Capture the Payment
	** parameters: $order_number, the number of the order, we're processing here
	** returns:
	***************************************************************************/
	function capture_payment( &$d ) {

		global $vendor_mail, $vendor_currency, $vmLogger;
		//$database = new ps_DB();
		$database = JFactory::getDBO();

		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'connection.php');
		
		/*CERTIFICATION
		Visa Test Account           4007000000027
		Amex Test Account           370000000000002
		Master Card Test Account    6011000000000012
		Discover Test Account       5424000000000015

		$host = "certification.authorize.net";
		$port = 443;
		$path = "/gateway/transact.dll";
		*/
		if( empty($d['order_number'])) {
			$vmLogger->err("Error: No Order Number provided.");
			return false;
		}

		$transaction_key = $this->get_passkey();
		if( $transaction_key === false ) return false;
		
		// $db = new ps_DB;
		$db = JFactory::getDBO();
		$q = "SELECT * FROM #__{vm}_orders, #__{vm}_order_payment WHERE ";
		$q .= "order_number='".$d['order_number']."' ";
		$q .= "AND #__{vm}_orders.order_id=#__{vm}_order_payment.order_id";
		$db->query( $q );
		if( !$db->next_record() ) {
			$vmLogger->err("Error: Order not found.");
			return false;
		}
		$expire_date = date( "my", $db->f("order_payment_expire") );

		// DECODE Account Number
		//$dbaccount = new ps_DB;
		$dbaccount = JFactory::getDBO();
		$q = "SELECT ".VM_DECRYPT_FUNCTION."(order_payment_number,'".ENCODE_KEY."')
          AS account_number from #__{vm}_order_payment WHERE order_id='".$db->f("order_id")."'";
		$dbaccount->query($q);
		$dbaccount->next_record();

		// Get user billing information
		//$dbbt = new ps_DB;
		$dbaccount = JFactory::getDBO();
		$qt = "SELECT * FROM #__{vm}_user_info WHERE user_id='".$db->f("user_id")."'";
		$dbbt->query($qt);
		$dbbt->next_record();
		$user_info_id = $dbbt->f("user_info_id");
		if( $user_info_id != $db->f("user_info_id")) {
			// Get user's alternative shipping information
			// $dbst =& new ps_DB;
			$dbst = JFactory::getDBO();
			$qt = "SELECT * FROM #__{vm}_user_info WHERE user_info_id='".$db->f("user_info_id")."' AND address_type='ST'";
			$dbst->query($qt);
			$dbst->next_record();
		}
		else {
			$dbst = $dbbt;
		}

		//Authnet vars to send
		$formdata = array (
		'x_version' => '3.1',
		'x_login' => $this->params->get('AN_LOGIN'),
		'x_tran_key' => $transaction_key,
		'x_test_request' => ($this->params->get('DEBUG') == 1 ? 'YES' : 'NO'),

		// Gateway Response Configuration
		'x_delim_data' => 'TRUE',
		'x_delim_char' => '|',
		'x_relay_response' => 'FALSE',

		// Customer Name and Billing Address
		'x_first_name' => substr($dbbt->f("first_name"), 0, 50),
		'x_last_name' => substr($dbbt->f("last_name"), 0, 50),
		'x_company' => substr($dbbt->f("company"), 0, 50),
		'x_address' => substr($dbbt->f("address_1"), 0, 60),
		'x_city' => substr($dbbt->f("city"), 0, 40),
		'x_state' => substr($dbbt->f("state"), 0, 40),
		'x_zip' => substr($dbbt->f("zip"), 0, 20),
		'x_country' => substr($dbbt->f("country"), 0, 60),
		'x_phone' => substr($dbbt->f("phone_1"), 0, 25),
		'x_fax' => substr($dbbt->f("fax"), 0, 25),

		// Customer Shipping Address
		'x_ship_to_first_name' => substr($dbst->f("first_name"), 0, 50),
		'x_ship_to_last_name' => substr($dbst->f("last_name"), 0, 50),
		'x_ship_to_company' => substr($dbst->f("company"), 0, 50),
		'x_ship_to_address' => substr($dbst->f("address_1"), 0, 60),
		'x_ship_to_city' => substr($dbst->f("city"), 0, 40),
		'x_ship_to_state' => substr($dbst->f("state"), 0, 40),
		'x_ship_to_zip' => substr($dbst->f("zip"), 0, 20),
		'x_ship_to_country' => substr($dbst->f("country"), 0, 60),

		// Additional Customer Data
		'x_cust_id' => $db->f('user_id'),
		'x_customer_ip' => $dbbt->f("ip_address"),
		'x_customer_tax_id' => $dbbt->f("tax_id"),

		// Email Settings
		'x_email' => $dbbt->f("email"),
		'x_email_customer' => 'False',
		'x_merchant_email' => $vendor_mail,

		// Invoice Information
		'x_invoice_num' => substr($d['order_number'], 0, 20),
		'x_description' => '',

		// Transaction Data
		'x_amount' => $db->f("order_total"),
		'x_currency_code' => $vendor_currency,
		'x_method' => 'CC',
		'x_type' => 'PRIOR_AUTH_CAPTURE',
		'x_recurring_billing' => ($this->params->get('AN_RECURRING')=='1'?'YES':'NO'),

		'x_card_num' => $dbaccount->f("account_number"),
		'x_card_code' => $db->f('order_payment_code'),
		'x_exp_date' => $expire_date,
		'x_trans_id' => $db->f("order_payment_trans_id"),

		// Level 2 data
		'x_po_num' => substr($d['order_number'], 0, 20),
		'x_tax' => substr($db->f('order_tax'), 0, 15),
		'x_tax_exempt' => "FALSE",
		'x_freight' => $db->f('order_shipping'),
		'x_duty' => 0

		);

		//build the post string
		$poststring = '';
		foreach($formdata AS $key => $val){
			$poststring .= urlencode($key) . "=" . urlencode($val) . "&";
		}
		// strip off trailing ampersand
		$poststring = substr($poststring, 0, -1);
		
		$host = 'secure.authorize.net';
		
		$result = VmConnector::handleCommunication( "https://$host:443/gateway/transact.dll", $poststring );
		
		if( !$result ) {
			$vmLogger->err('We\'re sorry, but an error has occured when we tried to communicate with the authorize.net server. Please try again later, thank you.' );
			return false;
		}
		
		$response = explode("|", $result);

		// Approved - Success!
		if ($response[0] == '1') {
			$d["order_payment_log"] = JText::_('VM_PAYMENT_TRANSACTION_SUCCESS').": ";
			$d["order_payment_log"] .= $response[3];
			// Catch Transaction ID
			$d["order_payment_trans_id"] = $response[6];

			$q = "UPDATE #__{vm}_order_payment SET ";
			$q .="order_payment_log='".$d["order_payment_log"]."',";
			$q .="order_payment_trans_id='".$d["order_payment_trans_id"]."' ";
			$q .="WHERE order_id='".$db->f("order_id")."' ";
			$db->query( $q );

			return True;
		}
		// Payment Declined
		elseif ($response[0] == '2') {
			$vmLogger->err($response[3]);
			$d["order_payment_log"] = $response[3];
			// Catch Transaction ID
			$d["order_payment_trans_id"] = $response[6];
			return False;
		}
		// Transaction Error
		elseif ($response[0] == '3') {
			$vmLogger->err($response[3]);
			$d["order_payment_log"] = $response[3];
			// Catch Transaction ID
			$d["order_payment_trans_id"] = $response[6];
			return False;
		}
	}

}
// No closing tag
