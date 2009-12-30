<?php
/**
 * The ps_echeck class, containing the payment processing code
 *  for eCheck.net transactions with authorize.net 
 *
 * @version $Id: echeck.php 1760 2009-05-03 22:58:57Z Aravot $
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
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;

class plgPaymentEcheck extends vmPaymentPlugin {
	
	var $payment_code = "ECK" ;

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
	function plgPaymentEcheck(& $subject, $config) {
		parent::__construct($subject, $config);
	}
	/**************************************************************************
	 ** name: process_payment()
	 ** created by: jep
	 ** description: process transaction authorize.net
	 ** parameters: $order_number, the number of the order, we're processing here
	 **            $order_total, the total $ of the order
	 ** returns: 
	 ***************************************************************************/
	function process_payment( $order_number, $order_total, &$d ) {
		
		global $vendor_mail, $vendor_currency, $vmLogger ;
		$database = new ps_DB( ) ;
		
		//This is the id of the mainvendor because the payment mehthods are not vendorrelated yet
		//		$hVendor_id = $_SESSION['ps_vendor_id'];
		$hVendor_id = 1 ;
		$auth = $_SESSION['auth'] ;
		$ps_checkout = new ps_checkout( ) ;
		
		$passkey = $this->get_passkey();
		if( empty( $passkey ) ) {
			$vmLogger->err( JText::_( 'VM_PAYMENT_ERROR' ), false ) ;
			return false ;
		}
		
		// Get user billing information
		$dbbt = new ps_DB( ) ;
		$qt = "SELECT * FROM #__{vm}_user_info WHERE user_id='" . $auth["user_id"] . "' AND address_type='BT'" ;
		$dbbt->query( $qt ) ;
		$dbbt->next_record() ;
		$user_info_id = $dbbt->f( "user_info_id" ) ;
		if( $user_info_id != $d["ship_to_info_id"] ) {
			// Get user billing information
			$dbst = & new ps_DB( ) ;
			$qt = "SELECT * FROM #__{vm}_user_info WHERE user_info_id='" . $d["ship_to_info_id"] . "' AND address_type='ST'" ;
			$dbst->query( $qt ) ;
			$dbst->next_record() ;
		} else {
			$dbst = $dbbt ;
		}
		
		$host = "secure.authorize.net" ;
		$port = 443 ;
		$path = "/gateway/transact.dll" ;
		
		//Authnet vars to send
		$formdata = array( 'x_version' => '3.1' , 'x_login' => $this->params->get('ECK_LOGIN') , 'x_tran_key' => $passkey , 'x_test_request' => $this->params->get('DEBUG') , 

		'x_delim_data' => 'TRUE' , 'x_delim_char' => '|' , 'x_relay_response' => 'FALSE' , 

		'x_first_name' => substr( $dbbt->f( "first_name" ), 0, 50 ) , 'x_last_name' => substr( $dbbt->f( "last_name" ), 0, 50 ) , 'x_company' => substr( $dbbt->f( "company" ), 0, 50 ) , 'x_address' => substr( $dbbt->f( "address_1" ), 0, 60 ) , 'x_city' => substr( $dbbt->f( "city" ), 0, 40 ) , 'x_state' => substr( $dbbt->f( "state" ), 0, 40 ) , 'x_zip' => substr( $dbbt->f( "zip" ), 0, 20 ) , 'x_country' => substr( $dbbt->f( "country" ), 0, 60 ) , 'x_phone' => substr( $dbbt->f( "phone_1" ), 0, 25 ) , 'x_fax' => substr( $dbbt->f( "fax" ), 0, 25 ) , 

		'x_ship_to_first_name' => substr( $dbst->f( "first_name" ), 0, 50 ) , 'x_ship_to_last_name' => substr( $dbst->f( "last_name" ), 0, 50 ) , 'x_ship_to_company' => substr( $dbst->f( "company" ), 0, 50 ) , 'x_ship_to_address' => substr( $dbst->f( "address_1" ), 0, 60 ) , 'x_ship_to_city' => substr( $dbst->f( "city" ), 0, 40 ) , 'x_ship_to_state' => substr( $dbst->f( "state" ), 0, 40 ) , 'x_ship_to_zip' => substr( $dbst->f( "zip" ), 0, 20 ) , 'x_ship_to_country' => substr( $dbst->f( "country" ), 0, 60 ) , 

		'x_cust_id' => $auth['user_id'] , 'x_customer_ip' => $_SERVER["REMOTE_ADDR"] , 'x_customer_tax_id' => $dbbt->f( "tax_id" ) , 

		'x_email' => $dbbt->f( "email" ) , 'x_email_customer' => 'True' , 'x_merchant_email' => $vendor_mail , 

		'x_invoice_num' => substr( $order_number, 0, 20 ) , 'x_description' => '' , 

		'x_amount' => $order_total , 'x_currency_code' => $vendor_currency , 'x_method' => 'ECHECK' , 'x_type' => $this->params->get('ECK_TYPE') , 'x_echeck_type' => $this->params->get('ECK_ECHECK_TYPE') , 

		'x_recurring_billing' => $this->params->get('ECK_RECURRING') , 

		'x_bank_aba_code' => $dbbt->f( "bank_iban" ) , 'x_bank_acct_num' => $dbbt->f( "bank_account_nr" ) , 'x_bank_acct_type' => $dbbt->f( "bank_account_type" ) , 'x_bank_name' => $dbbt->f( "bank_name" ) , 'x_bank_acct_name' => $dbbt->f( "bank_account_holder" ) , 

		// Level 2 data
		'x_po_num' => substr( $order_number, 0, 20 ) , 'x_tax' => substr( $d['order_tax'], 0, 15 ) , 'x_tax_exempt' => "FALSE" , 'x_freight' => $d['order_shipping'] , 'x_duty' => 0 ) ;
		
		//build the post string
		$poststring = '' ;
		foreach( $formdata as $key => $val ) {
			$poststring .= urlencode( $key ) . "=" . urlencode( $val ) . "&" ;
		}
		// strip off trailing ampersand
		$poststring = substr( $poststring, 0, - 1 ) ;
		
		if( function_exists( "curl_init" ) ) {
			
			$CR = curl_init() ;
			curl_setopt( $CR, CURLOPT_URL, "https://" . $host . $path ) ;
			curl_setopt( $CR, CURLOPT_POST, 1 ) ;
			curl_setopt( $CR, CURLOPT_FAILONERROR, true ) ;
			curl_setopt( $CR, CURLOPT_POSTFIELDS, $poststring ) ;
			curl_setopt( $CR, CURLOPT_RETURNTRANSFER, 1 ) ;
			
			// No PEER certificate validation...as we don't have 
			// a certificate file for it to authenticate the host www.ups.com against!
			curl_setopt( $CR, CURLOPT_SSL_VERIFYPEER, 0 ) ;
			//curl_setopt($CR, CURLOPT_SSLCERT , "/usr/locale/xxxx/clientcertificate.pem");
			

			$result = curl_exec( $CR ) ;
			
			$error = curl_error( $CR ) ;
			if( ! empty( $error ) ) {
				$vmLogger->err( curl_error( $CR ) ) ;
				$html = "<br/><span class=\"message\">" . JText::_( 'VM_PAYMENT_INTERNAL_ERROR' ) . " authorize.net</span>" ;
				return false ;
			} else {
				//echo $result; exit();
			}
			curl_close( $CR ) ;
		} else {
			
			$fp = fsockopen( "ssl://" . $host, $port, $errno, $errstr, $timeout = 60 ) ;
			if( ! $fp ) {
				//error tell us
				$vmLogger->err( "$errstr ($errno)" ) ;
			} else {
				
				//send the server request
				fputs( $fp, "POST $path HTTP/1.1\r\n" ) ;
				fputs( $fp, "Host: $host\r\n" ) ;
				fputs( $fp, "Content-type: application/x-www-form-urlencoded\r\n" ) ;
				fputs( $fp, "Content-length: " . strlen( $poststring ) . "\r\n" ) ;
				fputs( $fp, "Connection: close\r\n\r\n" ) ;
				fputs( $fp, $poststring . "\r\n\r\n" ) ;
				
				//Get the response header from the server
				$str = '' ;
				while( ! feof( $fp ) && ! stristr( $str, 'content-length' ) ) {
					$str = fgets( $fp, 4096 ) ;
				}
				// If didnt get content-lenght, something is wrong, return false.
				if( ! stristr( $str, 'content-length' ) ) {
					return false ;
				
				}
				$data = "" ;
				while( ! feof( $fp ) ) {
					$data .= fgets( $fp, 1024 ) ;
				}
				$result = trim( $data ) ;
				/*
                 // Get length of data to be received.
                 $length = trim(substr($str,strpos($str,'content-length') + 15));
                 // Get buffer (blank data before real data)
                 fgets($fp, 4096);
                 // Get real data
                 $data = fgets($fp, $length);
                 fclose($fp);*/
			
			}
		}
		$response = explode( "|", $result ) ;
		
		// Approved - Success!
		if( $response[0] == '1' ) {
			$d["order_payment_log"] = JText::_( 'VM_PAYMENT_TRANSACTION_SUCCESS' ) . ": " ;
			$d["order_payment_log"] .= $response[3] ;
			return True ;
		} // Payment Declined
		elseif( $response[0] == '2' ) {
			$vmLogger->err( $response[3] ) ;
			$d["order_payment_log"] = $response[3] ;
			return False ;
		} // Transaction Error
		elseif( $response[0] == '3' ) {
			$vmLogger->err( $response[3] ) ;
			$d["order_payment_log"] = $response[3] ;
			return False ;
		}
	}

}
?>