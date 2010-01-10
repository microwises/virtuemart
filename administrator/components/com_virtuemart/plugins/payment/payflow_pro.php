<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* The PayFlow Pro class, containing the payment processing code
*  for transactions with payflowpro.verisign.com
*
* @version $Id: payflow_pro.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage payment
* @copyright Copyright (C) 2007-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
define ('PFP_CLIENT_CERTIFICATION_ID', 'bea46ef28cd8693d8b191d2d011b7fd1');

class plgPaymentPayflow_Pro extends vmPaymentPlugin {
	
	var $payment_code = "PFP";
	
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
	function plgPaymentPayflow_Pro( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}


	/**
	 * process transaction with Payflow Pro
	 * Authorizes the amount for the customer account
	 *
	 * @param string $order_number
	 * @param float $order_total
	 * @param array $d
	 * @return boolean
	 */
	function process_payment($order_number, $order_total, &$d) {

		global $vendor_mail, $vendor_currency, $vmLogger;
		$database = new ps_DB;
		$hVendor_id = $_SESSION["ps_vendor_id"];
		$auth = $_SESSION['auth'];
		$ps_checkout = new ps_checkout;

		// connector class
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'connection.php');

		// Get the Password securely from the database
		$transactionkey = $this->get_passkey();
		if( empty($transactionkey)) {
			$vmLogger->err( JText::_('VM_PAYMENT_ERROR',false).'. Technical Note: The required passwird is empty! The payment method settings must be reviewed.' );
			return false;
		}

		// Get user billing information
		$dbbt = new ps_DB;

		$qt = "SELECT * FROM #__{vm}_user_info WHERE user_id=".$auth["user_id"]." AND address_type='BT'";

		$dbbt->query($qt);
		$dbbt->next_record();
		$user_info_id = $dbbt->f("user_info_id");
		if( $user_info_id != $d["ship_to_info_id"]) {
			// Get user billing information
			$dbst =& new ps_DB;
			$qt = "SELECT * FROM #__{vm}_user_info WHERE user_info_id='".$d["ship_to_info_id"]."' AND address_type='ST'";
			$dbst->query($qt);
			$dbst->next_record();
		}
		else {
			$dbst = $dbbt;
		}
		
		$tempstr = $_SESSION['ccdata']['order_payment_number'] . $order_total . date('YmdGis');
		$request_id = md5($tempstr);		
	
		//Authnet vars to send
		$formdata = array (
		
		'PARTNER' =>$this->params->get('PFP_PARTNER'),
		'VENDOR' => $this->params->get('PFP_VENDOR'),
		'USER' => $this->params->get('PFP_USER'),
		'PWD' => $transactionkey,
		'TEST' => ($this->params->get('DEBUG') ? 'TRUE' : 'FALSE'),

		// Transaction Data
		'AMT' => $order_total, // amount
		'TRXTYPE' => 'A', // transaction type: Delayed Capture
		'TENDER' => 'C', // payment method (C = Credit Card)
		'CURRENCY' => $vendor_currency,
		
		// Customer Name and Billing Address
		'NAME' => strtoupper(substr($dbbt->f("first_name"), 0, 15).substr($dbbt->f("last_name"), 0, 15)),
		'STREET' => substr($dbbt->f("address_1"), 0, 30),
		'CITY' => substr($dbbt->f("city"), 0, 40),
		'STATE' => substr($dbbt->f("state"), 0, 40),
		'ZIP' => substr($dbbt->f("zip"), 0, 9 ),

		// Invoice Information
		'CUSTREF' => substr($order_number, 0, 12),

		// Account Data
		'ACCT' => $_SESSION['ccdata']['order_payment_number'],
		'CVV2' => $_SESSION['ccdata']['credit_card_code'],
		'EXPDATE' => ($_SESSION['ccdata']['order_payment_expire_month']) . substr($_SESSION['ccdata']['order_payment_expire_year'], 2 )

		);

		//build the post string
		$poststring = '';
		foreach($formdata AS $key => $val){
			$poststring .= urlencode($key) . "=" . urlencode($val) . "&";
		}
		// strip off trailing ampersand
		$poststring = substr($poststring, 0, -1);
		
		if($this->params->get('DEBUG')) {
			$host = 'pilot-payflowpro.verisign.com';
		} else  {
			$host = 'payflowpro.verisign.com';
		}
		
		$headers[] = "X-VPS-Timeout: 30";
		$headers[] = "X-VPS-VIT-OS-Name: ".PHP_OS;  // Name of your OS
		$headers[] = "X-VPS-VIT-OS-Version: ".PHP_OS;  // OS Version
		$headers[] = "X-VPS-VIT-Client-Type: PHP/cURL";  // What you are using
		$headers[] = "X-VPS-VIT-Client-Version: 0.01";  // For your info
		$headers[] = "X-VPS-VIT-Client-Architecture: x86";  // For your info
		$headers[] = "X-VPS-VIT-Client-Certification-Id: ".$this->params->get('PFP_CLIENT_CERTIFICATION_ID'); // get this from Todd @ payflowintegrator@paypal.com
		$headers[] = "X-VPS-VIT-Integration-Product: ".phpversion()."::cURL";  // For your info, would populate with application name
		$headers[] = "X-VPS-VIT-Integration-Version: 0.01"; // Application version
		$headers[] = "X-VPS-Request-ID: " . $request_id;
	
		$result = VmConnector::handleCommunication( "https://$host:443/transaction/", $poststring, $headers );
		
		if( !$result ) {
			$vmLogger->err('The transaction could not be completed.' );
			return false;
		}
		
		$result = strstr($result, 'RESULT');
		
		$valArray = explode('&', $result);
		foreach($valArray as $val) {
			$valArray2 = explode('=', $val);
			$pfpro[$valArray2[0]] = $valArray2[1];
		}
		
		$vmLogger->debug('Beginning to analyse the response from '.$host);
		
		$RESULT_CODE = vmGet( $pfpro, 'RESULT' );
		$TRANSACTION_ID = vmGet( $pfpro, 'PNREF' );
		$RESPMSG = vmGet( $pfpro, 'RESPMSG', '' );
		$CVV2MATCH = vmGet( $pfpro, 'CVV2MATCH', '' );
		
		$success = false;
		
		switch($RESULT_CODE) {
			
			case '0':
				// Approved - Success!
				$success = true;
				$d["order_payment_log"] = JText::_('VM_PAYMENT_TRANSACTION_SUCCESS').": ";
				$d["order_payment_log"] .= $RESPMSG;
				$vmLogger->debug( $d['order_payment_log']);
				break;
				
			default:
				$d["order_payment_log"] = $this->getResponseMsg( $RESULT_CODE );
				if( !empty( $d["order_payment_log"] )) {
					$vmLogger->err( $d["order_payment_log"] );
				} else {
					$vmLogger->err( 'An unknown Error occured while processing your Payment Request.');
				}
				break;
			
		}		
		
		// Catch Transaction ID
		$d["order_payment_trans_id"] = $TRANSACTION_ID;

		return $success;

	}

	/**
	 * Process a previous transaction with Payflow Pro and Capture the Payment
	 *
	 * @param array $d
	 * @return boolean
	 */
	function capture_payment( &$d ) {

		global $vendor_mail, $vendor_currency, $vmLogger;
		$database = new ps_DB();

		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'connection.php');
		
		if( empty($d['order_number'])) {
			$vmLogger->err("Error: No Order Number provided.");
			return false;
		}

		// Get the Account Password securely from the database
		$transactionkey = $this->get_passkey();
		if( empty($transactionkey)) {
			$vmLogger->err(JText::_('VM_PAYMENT_ERROR'),false);
			return false;
		}
		$db = new ps_DB;
		$q = "SELECT * FROM #__{vm}_orders, #__{vm}_order_payment WHERE ";
		$q .= "order_number='".$d['order_number']."' ";
		$q .= "AND #__{vm}_orders.order_id=#__{vm}_order_payment.order_id";
		$db->query( $q );
		if( !$db->next_record() ) {
			$vmLogger->err("Error: Order not found.");
			return false;
		}
		$expire_date = date( "my", $db->f("order_payment_expire") );
		$cvv2_code = $db->f("order_payment_code");

		// DECODE Account Number
		$dbaccount = new ps_DB;
		$q = "SELECT ".VM_DECRYPT_FUNCTION."(order_payment_number,'".ENCODE_KEY."')
          AS account_number from #__{vm}_order_payment WHERE order_id='".$db->f("order_id")."'";
		$dbaccount->query($q);
		$dbaccount->next_record();

		// Get user billing information
		$dbbt = new ps_DB;
		$qt = "SELECT * FROM #__{vm}_user_info WHERE user_id='".$db->f("user_id")."'";
		$dbbt->query($qt);
		$dbbt->next_record();
		$user_info_id = $dbbt->f("user_info_id");
		if( $user_info_id != $db->f("user_info_id")) {
			// Get user's alternative shipping information
			$dbst =& new ps_DB;
			$qt = "SELECT * FROM #__{vm}_user_info WHERE user_info_id='".$db->f("user_info_id")."' AND address_type='ST'";
			$dbst->query($qt);
			$dbst->next_record();
		}
		else {
			$dbst = $dbbt;
		}

		$tempstr = $dbaccount->f('account_number') . $db->f('order_total') . date('YmdGis');
		$request_id = md5($tempstr);		
	
		//Authnet vars to send
		$formdata = array (
		
		'PARTNER' => $this->params->get('PFP_PARTNER'),
		'VENDOR' => $this->params->get('PFP_VENDOR'),
		'USER' => $this->params->get('PFP_USER'),
		'PWD' => $transactionkey,
		'TEST' => ($this->params->get('DEBUG') ? 'TRUE' : 'FALSE'),

		// Transaction Data
		'AMT' => $db->f('order_total'), // amount
		'TRXTYPE' => 'D', // transaction type: Delayed Capture
		'TENDER' => 'C', // payment method (C = Credit Card)
		'CURRENCY' => $vendor_currency,
		
		// Customer Name and Billing Address
		'NAME' => strtoupper(substr($dbbt->f("first_name"), 0, 15).substr($dbbt->f("last_name"), 0, 15)),
		'STREET' => substr($dbbt->f("address_1"), 0, 30),
		'CITY' => substr($dbbt->f("city"), 0, 40),
		'STATE' => substr($dbbt->f("state"), 0, 40),
		'ZIP' => substr($dbbt->f("zip"), 0, 9 ),

		// Invoice Information
		'CUSTREF' => substr($db->f('order_number'), 0, 12),

		// Account Data
		'ORIGID' => $db->f('order_payment_trans_id'),
		'ACCT' => $dbaccount->f('account_number'),
		'CVV2' => $cvv2_code,
		'EXPDATE' => $expire_date

		);

		//build the post string
		$poststring = '';
		foreach($formdata AS $key => $val){
			$poststring .= urlencode($key) . "=" . urlencode($val) . "&";
		}
		// strip off trailing ampersand
		$poststring = substr($poststring, 0, -1);
		
		if($this->params->get('DEBUG')) {
			$host = 'pilot-payflowpro.verisign.com';
		} else  {
			$host = 'payflowpro.verisign.com';
		}
		
		$headers[] = "X-VPS-Timeout: 30";
		$headers[] = "X-VPS-VIT-OS-Name: ".PHP_OS;  // Name of your OS
		$headers[] = "X-VPS-VIT-OS-Version: ".PHP_OS;  // OS Version
		$headers[] = "X-VPS-VIT-Client-Type: PHP/cURL";  // What you are using
		$headers[] = "X-VPS-VIT-Client-Version: 0.01";  // For your info
		$headers[] = "X-VPS-VIT-Client-Architecture: x86";  // For your info
		$headers[] = "X-VPS-VIT-Client-Certification-Id: ".$this->params->get('PFP_CLIENT_CERTIFICATION_ID'); // get this from Todd @ payflowintegrator@paypal.com
		$headers[] = "X-VPS-VIT-Integration-Product: ".phpversion()."::cURL";  // For your info, would populate with application name
		$headers[] = "X-VPS-VIT-Integration-Version: 0.01"; // Application version
		$headers[] = "X-VPS-Request-ID: " . $request_id;
	
		$result = VmConnector::handleCommunication( "https://$host:443/transaction", $poststring, $headers );
		
		if( !$result ) {
			$vmLogger->err('The transaction could not be completed.' );
			return false;
		}
		
		$result = strstr($result, 'RESULT');
		
		$valArray = explode('&', $result);
		foreach($valArray as $val) {
			$valArray2 = explode('=', $val);
			$pfpro[$valArray2[0]] = $valArray2[1];
		}
		
		$vmLogger->debug('Beginning to analyse the response from '.$host);
		
		$RESULT_CODE = vmGet( $pfpro, 'RESULT' );
		$TRANSACTION_ID = vmGet( $pfpro, 'PNREF' );
		$RESPMSG = vmGet( $pfpro, 'RESPMSG', '' );
		$CVV2MATCH = vmGet( $pfpro, 'CVV2MATCH', '' );
		
		$success = false;
		
		switch($RESULT_CODE) {
			
			case '0':
				// Approved - Success!
				$success = true;
				$d["order_payment_log"] = JText::_('VM_PAYMENT_TRANSACTION_SUCCESS').": ";
				$d["order_payment_log"] .= $RESPMSG;
				
				$q = "UPDATE #__{vm}_order_payment SET ";
				$q .="order_payment_log='".$d["order_payment_log"]."',";
				$q .="order_payment_trans_id='".$TRANSACTION_ID."' ";
				$q .="WHERE order_id='".$db->f("order_id")."' ";
				$db->query( $q );
				$vmLogger->debug( $d['order_payment_log']);
				break;
				
			default:
				$d["order_payment_log"] = payflow_pro::getResponseMsg( $RESULT_CODE );
				if( !empty( $d["order_payment_log"] )) {
					$vmLogger->err( $d["order_payment_log"] );
				} else {
					$vmLogger->err( 'An unknown Error occured while capturing the Payment.');
				}
				break;
			
		}

		return $success;
	}
	
	/**
	 * Voids a previous transaction with Payflow Pro
	 *
	 * @param array $d
	 * @return boolean
	 */
	function void_authorization( &$d ) {

		global $vendor_mail, $vendor_currency, $vmLogger;
		$database = new ps_DB();

		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'connection.php');
		
		if( empty($d['order_number'])) {
			$vmLogger->err("Error: No Order Number provided.");
			return false;
		}

		// Get the Account Password securely from the database
		$transactionkey = $this->get_passkey();
		if( empty($transactionkey)) {
			$vmLogger->err(JText::_('VM_PAYMENT_ERROR'),false);
			return false;
		}
		$db = new ps_DB;
		$q = "SELECT * FROM #__{vm}_orders, #__{vm}_order_payment WHERE ";
		$q .= "order_number='".$d['order_number']."' ";
		$q .= "AND #__{vm}_orders.order_id=#__{vm}_order_payment.order_id";
		$db->query( $q );
		if( !$db->next_record() ) {
			$vmLogger->err("Error: Order not found.");
			return false;
		}
		$expire_date = date( "my", $db->f("order_payment_expire") );
		$cvv2_code = $db->f("order_payment_code");

		// DECODE Account Number
		$dbaccount = new ps_DB;
		$q = "SELECT ".VM_DECRYPT_FUNCTION."(order_payment_number,'".ENCODE_KEY."')
          AS account_number from #__{vm}_order_payment WHERE order_id='".$db->f("order_id")."'";
		$dbaccount->query($q);
		$dbaccount->next_record();

		// Get user billing information
		$dbbt = new ps_DB;
		$qt = "SELECT * FROM #__{vm}_user_info WHERE user_id='".$db->f("user_id")."'";
		$dbbt->query($qt);
		$dbbt->next_record();
		$user_info_id = $dbbt->f("user_info_id");
		if( $user_info_id != $db->f("user_info_id")) {
			// Get user's alternative shipping information
			$dbst =& new ps_DB;
			$qt = "SELECT * FROM #__{vm}_user_info WHERE user_info_id='".$db->f("user_info_id")."' AND address_type='ST'";
			$dbst->query($qt);
			$dbst->next_record();
		}
		else {
			$dbst = $dbbt;
		}

		$tempstr = $dbaccount->f('account_number') . $db->f('order_total') . date('YmdGis');
		$request_id = md5($tempstr);		
	
		//Authnet vars to send
		$formdata = array (
		
		'PARTNER' => $this->params->get('PFP_PARTNER'),
		'VENDOR' => $this->params->get('PFP_VENDOR'),
		'USER' => $this->params->get('PFP_USER'),
		'PWD' => $transactionkey,
		'TEST' => ($this->params->get('DEBUG') ? 'TRUE' : 'FALSE'),

		// Transaction Data
		'AMT' => $db->f('order_total'), // amount
		'TRXTYPE' => 'V', // transaction type: Void
		'TENDER' => 'C', // payment method (C = Credit Card)
		'CURRENCY' => $vendor_currency,
		
		// Customer Name and Billing Address
		'NAME' => strtoupper(substr($dbbt->f("first_name"), 0, 15).substr($dbbt->f("last_name"), 0, 15)),
		'STREET' => substr($dbbt->f("address_1"), 0, 30),
		'CITY' => substr($dbbt->f("city"), 0, 40),
		'STATE' => substr($dbbt->f("state"), 0, 40),
		'ZIP' => substr($dbbt->f("zip"), 0, 9 ),

		// Invoice Information
		'CUSTREF' => substr($db->f('order_number'), 0, 12),

		// Account Data
		'ORIGID' => $db->f('order_payment_trans_id'),
		'ACCT' => $dbaccount->f('account_number'),
		'CVV2' => $cvv2_code,
		'EXPDATE' => $expire_date

		);

		//build the post string
		$poststring = '';
		foreach($formdata AS $key => $val){
			$poststring .= urlencode($key) . "=" . urlencode($val) . "&";
		}
		// strip off trailing ampersand
		$poststring = substr($poststring, 0, -1);
		
		if($this->params->get('DEBUG')) {
			$host = 'pilot-payflowpro.verisign.com';
		} else  {
			$host = 'payflowpro.verisign.com';
		}
		
		$headers[] = "X-VPS-Timeout: 30";
		$headers[] = "X-VPS-VIT-OS-Name: ".PHP_OS;  // Name of your OS
		$headers[] = "X-VPS-VIT-OS-Version: ".PHP_OS;  // OS Version
		$headers[] = "X-VPS-VIT-Client-Type: PHP/cURL";  // What you are using
		$headers[] = "X-VPS-VIT-Client-Version: 0.01";  // For your info
		$headers[] = "X-VPS-VIT-Client-Architecture: x86";  // For your info
		$headers[] = "X-VPS-VIT-Client-Certification-Id: ".$this->params->get('PFP_CLIENT_CERTIFICATION_ID'); // get this from Todd @ payflowintegrator@paypal.com
		$headers[] = "X-VPS-VIT-Integration-Product: ".phpversion()."::cURL";  // For your info, would populate with application name
		$headers[] = "X-VPS-VIT-Integration-Version: 0.01"; // Application version
		$headers[] = "X-VPS-Request-ID: " . $request_id;
			
		$result = VmConnector::handleCommunication( "https://$host:443/transaction", $poststring, $headers );
		
		if( !$result ) {
			$vmLogger->err('The transaction could not be completed.' );
			return false;
		}
		
		$result = strstr($result, 'RESULT');
		
		$valArray = explode('&', $result);
		foreach($valArray as $val) {
			$valArray2 = explode('=', $val);
			$pfpro[$valArray2[0]] = $valArray2[1];
		}
		
		$vmLogger->debug('Beginning to analyse the response from '.$host);
		
		$RESULT_CODE = vmGet( $pfpro, 'RESULT' );
		$TRANSACTION_ID = vmGet( $pfpro, 'PNREF' );
		$RESPMSG = vmGet( $pfpro, 'RESPMSG', '' );
		$CVV2MATCH = vmGet( $pfpro, 'CVV2MATCH', '' );
		
		$success = false;
		
		switch($RESULT_CODE) {
			
			case '0':
				// Approved - Success!
				$success = true;
				$d["order_payment_log"] = JText::_('VM_PAYMENT_TRANSACTION_SUCCESS').": ";
				$d["order_payment_log"] .= $RESPMSG;
				
				$q = "UPDATE #__{vm}_order_payment SET ";
				$q .="order_payment_log='".$d["order_payment_log"]."',";
				$q .="order_payment_trans_id='".$TRANSACTION_ID."' ";
				$q .="WHERE order_id='".$db->f("order_id")."' ";
				$db->query( $q );
				$vmLogger->debug( $d['order_payment_log']);
				break;
				
			default:
				$d["order_payment_log"] = $this->getResponseMsg( $RESULT_CODE );
				if( !empty( $d["order_payment_log"] )) {
					$vmLogger->err( $d["order_payment_log"] );
				} else {
					$vmLogger->err( 'An unknown Error occured while voiding the transaction.');
				}
				break;
			
		}

		return $success;
	}
	
	/**
	 * Returns the error / mesage for the response code returned by Payflow Pro
	 *
	 * @param int $response_code
	 * @return string
	 */
	function getResponseMsg( $response_code ) {
		switch ( $response_code ) {
			case '1':
				return 'User authentication failed. Error is caused by one or more of the following: a) Login information is incorrect. Verify that USER, VENDOR, PARTNER, and PASSWORD have been entered correctly, 
				b)	Invalid Processor information entered. Contact merchant bank to verify.
				c) Allowed IP Address" security feature implemented. The transaction is coming from an unknown IP address. See PayPal Manager online help for details on how to use Manager to update the allowed IP addresses.
				c) You are using a test (not active) account to submit a transaction to the live PayPal servers. Change the URL from test-payflow.paypal.com to payflow.paypal.com.';
			case '2':
				return "Invalid tender type. Your merchant bank account does not support the following credit card type that was submitted.";
			case '3':
				return 'Invalid transaction type. Transaction type is not appropriate for this transaction. For example, you cannot credit an authorization-only transaction.';
			case '4':
				return 'Invalid amount format. Use the format: “#####.##” Do not include currency symbols or commas.';
			case '5': 
				return 'Invalid merchant information. Processor does not recognize your merchant account information. Contact your bank account acquirer to resolve this problem.';
			case '6':
				return 'Invalid or unsupported currency code';
			case '7':
				return 'Field format error. Invalid information entered. See RESPMSG.';
			case '8':
				return 'Not a transaction server';
			case '9':
				return 'Too many parameters or invalid stream';
			case '10':
				return 'Too many line items';
			case '11':
				return 'Client time-out waiting for response';
			case '12':
				return 'Declined. Check the credit card number, expiration date, and transaction information to make sure they were entered correctly. If this does not resolve the problem, have the customer call their card issuing bank to resolve.';
			case '13':
				return 'Referral. Transaction cannot be approved electronically but can be approved with a verbal authorization. Contact your merchant bank to obtain an authorization and submit a manual Voice Authorization transaction.';
			case '14':
				return 'Invalid Client Certification ID. Check the HTTP header. If the tag, X-VPS-VIT-CLIENT-CERTIFICATION-ID, is missing, RESULT code 14 is returned.';
			case '19':
				return 'Original transaction ID not found. The transaction ID you entered for this transaction is not valid. See RESPMSG.';
			case '20':
				return 'Cannot find the customer reference number';
			case '22':
				return 'Invalid ABA number';
			case '23':
				return 'Invalid account number. Check credit card number and re-submit.';
			case '24':
				return 'Invalid expiration date. Check and re-submit.';
			case '25':
				return 'Invalid Host Mapping. You are trying to process a tender type such as Discover Card, but you are not set up with your merchant bank to accept this card type.';
			case '26':
				return 'Invalid vendor account';
			case '27':
				return 'Insufficient partner permissions';
			case '28':
				return 'Insufficient user permissions';
			case '29':
				return 'Invalid XML document. This could be caused by an unrecognized XML tag or a bad XML format that cannot be parsed by the system.';
			case '50':
				return 'Insufficient funds available in account';
			case '51':
				return 'Exceeds per transaction limit';
			case '99':
				return 'General error. See RESPMSG.';
			case '100':
				return 'Transaction type not supported by host';
			case '101':
				return 'Time-out value too small';
			case '102':
				return 'Processor not available';
			case '103':
				return 'Error reading response from host';
			case '104':
				return 'Timeout waiting for processor response. Try your transaction again.';
			case '105':
				return 'Credit error. Make sure you have not already credited this transaction, or that this transaction ID is for a creditable transaction. (For example, you cannot credit an authorization.)';
			case '106':
				return 'Host not available';
			case '107':
				return 'Duplicate suppression time-out';
			case '108':
				return 'Void error. See RESPMSG. Make sure the transaction ID entered has not already been voided. If not, then look at the Transaction Detail screen for this transaction to see if it has settled. (The Batch field is set to a number greater than zero if the transaction has been settled). If the transaction has already settled, your only recourse is a reversal (credit a payment or submit a payment for a credit).';
			case '109':
				return 'Time-out waiting for host response';
			case '111':
				return 'Capture error. Either an attempt to capture a transaction that is not an authorization transaction type, or an attempt to capture an authorization transaction that has already been captured.';
			case '112':
				return 'Failed AVS check. Address and ZIP code do not match. An authorization may still exist on the cardholder’s account.';
			case '113':
				return 'Merchant sale total will exceed the sales cap with current transaction. ACH transactions only.';
			case '114':
				return 'Card Security Code (CSC) Mismatch. An authorization may still exist on the cardholder’s account.';
			case '115':
				return 'System busy, try again later';
			case '116':
				return 'VPS Internal error. Failed to lock terminal number';
			case '117':
				return 'Failed merchant rule check. One or more of the following three failures occurred:
An attempt was made to submit a transaction that failed to meet the security settings specified on the PayPal Manager Security Settings page. If the transaction exceeded the Maximum Amount security setting, then no values are returned for AVS or CSC.
AVS validation failed. The AVS return value should appear in the RESPMSG.
CSC validation failed. The CSC return value should appear in the RESPMSG.';
			case '118':
				return 'Invalid keywords found in string fields';
			case '122':
				return 'Merchant sale total will exceed the credit cap with current transaction. ACH transactions only.';
			case '125':
				return 'Fraud Protection Services Filter — Declined by filters';
			case '126':
				return 'Fraud Protection Services Filter — Flagged for review by filters';
				break;
		}
	}
}
