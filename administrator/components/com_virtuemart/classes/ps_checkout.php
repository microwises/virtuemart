<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage classes
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

define("CHECK_OUT_GET_FINAL_BASKET", 1);
define("CHECK_OUT_GET_SHIPPING_ADDR", 2);
define("CHECK_OUT_GET_SHIPPING_METHOD", 3);
define("CHECK_OUT_GET_PAYMENT_METHOD", 4);
define("CHECK_OUT_GET_FINAL_CONFIRMATION", 99);

/**
 * The class contains the shop checkout code.  It is used to checkout
 * and order and collect payment information.
 *
 */
class ps_checkout {

	var $_subtotal = null;
	var $_shipping = null;
	var $_shipping_tax = null;
	var $_payment_discount = null;
	var $_coupon_discount = null;
	var $_order_total = null;
	/** @var string An md5 hash of print_r( $cart, true ) to check wether the checkout values have to be renewed */
	var $_cartHash;

	/**
	 * Initiate Shipping Modules
	 */
	function ps_checkout() {
		global $vendor_freeshipping, $vars;

		// Make a snapshot of the current checkout configuration
		$this->generate_cart_hash();

		/* Ok, need to decide if we have a free Shipping amount > 0,
		* and IF the cart total is more than that Free Shipping amount,
		* let's set Order Shipping = 0
		*/

		$this->_subtotal = $this->get_order_subtotal($vars);
		require_once(CLASSPATH.'shippingMethod.class.php');
		if( $vendor_freeshipping > 0 && $vars['order_subtotal_withtax'] >= $vendor_freeshipping) {
			vmPluginHelper::importPlugin('shipping', 'free_shipping');
		}
		elseif( !empty( $_REQUEST['shipping_rate_id'] )) {

			// Create a Shipping Object and assign it to the _SHIPPING attribute
			// We take the first Part of the Shipping Rate Id String
			// which holds the Class Name of the Shipping Module
			$rate_array = explode( "|", urldecode(JRequest::getVar("shipping_rate_id")) );
			$filename = basename( $rate_array[0] );
			if( $filename != '' && file_exists(ADMINPATH . "plugins/shipping/".$filename.".php")) {
				vmPluginHelper::importPlugin('shipping', $filename);
			}
		}
		$steps = ps_checkout::get_checkout_steps();
		if(empty($_REQUEST['ship_to_info_id']) && ps_checkout::noShipToNecessary()) {

			$db = new ps_DB();

			/* Select all the ship to information for this user id and
			* order by modification date; most recently changed to oldest
			*/
			$q  = "SELECT user_info_id from `#__{vm}_user_info` WHERE ";
			$q .= "user_id='" . $_SESSION['auth']["user_id"] . "' ";
			$q .= "AND address_type='BT'";
			$db->query($q);
			$db->next_record();

			$_REQUEST['ship_to_info_id'] = $db->f("user_info_id");
		}
	}
	/**
	 * Checks if Ship To can be skipped
	 *
	 * @return boolean
	 */
	function noShipToNecessary() {
		global $cart, $only_downloadable_products;
		if( NO_SHIPTO == '1') {
			return true;
		}
		if( !isset( $cart)) $cart = ps_cart::initCart();
		
		if( ENABLE_DOWNLOADS == '1') {
			$not_downloadable = false;
			require_once( CLASSPATH .'ps_product.php');
			for($i = 0; $i < $cart["idx"]; $i++) {
				
				if( !ps_product::is_downloadable($cart[$i]['product_id']) ) {					
					$not_downloadable = true;
					break;
				}
			}
			return !$not_downloadable;
		}
		return false;
	}
	function noShippingMethodNecessary() {
		global $cart, $only_downloadable_products;
		if( NO_SHIPPING == '1') {
			return true;
		}
		
		if( !isset( $cart)) $cart = ps_cart::initCart();
		
		if( ENABLE_DOWNLOADS == '1') {
			$not_downloadable = false;
			require_once( CLASSPATH .'ps_product.php');
			for($i = 0; $i < $cart["idx"]; $i++) {
				if( !ps_product::is_downloadable($cart[$i]['product_id']) ) {
					$not_downloadable = true;
					break;
				}
			}
			return !$not_downloadable;
		}
		return false;
	}
	function noShippingNecessary() {
		return $this->noShipToNecessary() && $this->noShippingMethodNecessary();
	}
	/**
	 * Retrieve an array with all order steps and their details
	 *
	 * @return array
	 */
	function get_checkout_steps() {
		global $VM_CHECKOUT_MODULES;
		$stepnames = array_keys( $VM_CHECKOUT_MODULES );
		$steps = array();
		$i = 0;
		$last_order = 0;
		foreach( $VM_CHECKOUT_MODULES as $step ) {
			// Get the stepname from the array key
			$stepname = current($stepnames);
//			$GLOBALS['vmLogger']->info('$stepname '.$stepname);
			next($stepnames);
			
			switch( $stepname ) {
				case 'CHECK_OUT_GET_SHIPPING_ADDR':
					if( ps_checkout::noShipToNecessary() ) $step['enabled'] = 0;
					break;
				case 'CHECK_OUT_GET_SHIPPING_METHOD':
					if( ps_checkout::noShippingMethodNecessary() ) $step['enabled'] = 0;
					break;
			}
			
			
			if( $step['enabled'] == 1 ) {
				$steps[$step['order']][] = $stepname;
			}
			
		}
		ksort( $steps );

		return $steps;
	}
	/**
	 * Retrieve the key name of the current checkout step
	 *
	 * @return string
	 */
	function get_current_stage() {
		$steps = ps_checkout::get_checkout_steps();
		$stage = key( $steps ); // $steps is sorted by key, so the first key is the first stage
		// First check the REQUEST parameters for other steps
		if( !empty( $_REQUEST['checkout_last_step'] ) && empty( $_POST['checkout_this_step'] )) {
			// Make sure we have an integer (max 4)
			$checkout_step = abs( min( $_REQUEST['checkout_last_step'], 4 ) );
			if( isset( $steps[$checkout_step] )) {
//				$GLOBALS['vmLogger']->info('ps_checkout get_current_stage �ber Request '.$checkout_step);
				return $checkout_step; // it's a valid step
			}
		}
		$checkout_step = (int)JRequest::getVar( 'checkout_stage' );
		
		if( isset( $steps[$checkout_step] )) {
//			$GLOBALS['vmLogger']->info('ps_checkout get_current_stage �ber vmGet '.$checkout_step.' '.$steps[$checkout_step]);
			return $checkout_step; // it's a valid step
		}
		// Else: we have no alternative steps given by REQUEST
		while ($step = current($steps)) {
			if( !empty($_POST['checkout_this_step']) )  {
				foreach( $step as $stepname ) {
					if( in_array( $stepname, $_POST['checkout_this_step'])) {
						next($steps);
						$key = key( $steps );
						if( empty( $key )) {
							// We are beyond the last index of the array and need to go "back" to the last index
							end( $steps );
						}
//						$GLOBALS['vmLogger']->info('ps_checkout get_current_stage WhileReturn  '.key( $steps ));
						return key($steps);
						
					}
				}
			}
			next($steps);
		}
//		$GLOBALS['vmLogger']->info('ps_checkout get_current_stage  '.$stage);
		return $stage;
	}
	/**
	 * Displays the "checkout bar" using the checkout bar template
	 *
	 * @param array $steps_to_do Array holding all steps the customer has to make
	 * @param array $step_msg Array containing the step messages
	 * @param int $step_count Number of steps to make
	 * @param int $highlighted_step The index of the recent step
	 */
	function show_checkout_bar() {

		global $sess, $ship_to_info_id, $shipping_rate_id;
		
		if (SHOW_CHECKOUT_BAR != '1' || defined('VM_CHECKOUT_BAR_LOADED')) {
			return;
		}
	    // Let's assemble the steps
	    $steps = ps_checkout::get_checkout_steps();
	    $step_count = sizeof( $steps );
	    $steps_tmp = $steps;
	    $i = 0;
	    foreach( $steps as $step ) {	    	
	    	foreach( $step as $step_name ) {
	    		switch ( $step_name ) {
	    			case 'CHECK_OUT_GET_SHIPPING_ADDR':
	    				$step_msg = JText::_('VM_ADD_SHIPTO_2');
	    				break;
	    			case 'CHECK_OUT_GET_SHIPPING_METHOD':
	    				$step_msg = JText::_('VM_ISSHIP_LIST_CARRIER_LBL');
	    				break;
	    			case 'CHECK_OUT_GET_PAYMENT_METHOD':
	    				$step_msg = JText::_('VM_ORDER_PRINT_PAYMENT_LBL');
	    				break;
	    			case 'CHECK_OUT_GET_FINAL_CONFIRMATION':
	    				$step_msg = JText::_('VM_CHECKOUT_CONF_PAYINFO_COMPORDER');
	    				break;
	    		}
	    		$steps_to_do[$i][] = array('step_name' => $step_name,
	    								'step_msg' => $step_msg,
	    								'step_order' => key($steps_tmp) );
			
	    	}
    		next( $steps_tmp );
	    	$i++;
	    }
	      
      	$highlighted_step = ps_checkout::get_current_stage(); 
    	
    	$theme = new $GLOBALS['VM_THEMECLASS']();
    	$theme->set_vars( array( 'step_count' => $step_count,
    							'steps_to_do' => $steps_to_do,
    							'steps' => $steps,
    							'highlighted_step' => $highlighted_step,
    							'ship_to_info_id' => JRequest::getVar( 'ship_to_info_id'),
    							'shipping_rate_id' => JRequest::getVar( 'shipping_rate_id')
    						) );
    						
		echo $theme->fetch( 'checkout/checkout_bar.tpl.php');
		define('VM_CHECKOUT_BAR_LOADED', 1 );
	}

	/**
	 * Called to validate the form values before the order is stored
	 * 
	 * @author gday
	 * @author soeren
	 * 
	 * @param array $d
	 * @return boolean
	 */
	function validate_form(&$d) {
		global  $vmLogger;

		$db = new ps_DB;

		$auth = $_SESSION['auth'];
		$cart = $_SESSION['cart'];

		if (!$cart["idx"]) {
			$q  = "SELECT order_id FROM #__{vm}_orders WHERE user_id='" . $auth["user_id"] . "' ";
			$q .= "ORDER BY cdate DESC";
			$db->query($q);
			$db->next_record();
			$d["order_id"] = $db->f("order_id");
			return False;
		}
		if( PSHOP_AGREE_TO_TOS_ONORDER == '1' ) {
			if( empty( $d["agreed"] )) {
				$vmLogger->warning( JText::_('VM_AGREE_TO_TOS',false) );
				return false;
			}
		}

		if ( !ps_checkout::noShippingMethodNecessary() ) {
			if ( !$this->validate_shipping_method($d) ) {
				return False;
			}
		}
		
		if ( !$this->validate_payment_method( $d, false )) {
			return false;
		}
		if( CHECK_STOCK == '1' ) {
			for($i = 0; $i < $cart["idx"]; $i++) {

				$quantity_in_stock = ps_product::get_field($cart[$i]["product_id"], 'product_in_stock');
				$product_name = ps_product::get_field($cart[$i]["product_id"], 'product_name');
				if( $cart[$i]["quantity"] > $quantity_in_stock ) {
					$vmLogger->err( 'The Quantity for the Product "'.$product_name.'" in your Cart ('.$cart[$i]["quantity"].') exceeds the Quantity in Stock ('.$quantity_in_stock.'). 
												We are very sorry for this Inconvenience, but you you need to lower the Quantity in Cart for this Product.');
					return false;
				}
			}
		}
		// calculate the unix timestamp for the specified expiration date
		// default the day to the 1st
		$expire_timestamp = @mktime(0,0,0,$_SESSION["ccdata"]["order_payment_expire_month"], 1,$_SESSION["ccdata"]["order_payment_expire_year"]);
		$_SESSION["ccdata"]["order_payment_expire"] = $expire_timestamp;

		return True;
	}

	/**
	 * Validates the variables prior to adding an order
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_add(&$d) {
		global $auth, $vmLogger;

		require_once(CLASSPATH.'paymentMethod.class.php');
		$vmPaymentMethod = new vmPaymentMethod;
		
		if( empty( $auth['user_id'] ) ) {
			$vmLogger->err('Sorry, but it is not possible to order without a User ID. 
										Please contact the Store Administrator if this Error occurs again.');
			return false;
		}
		if (!ps_checkout::noShipToNecessary()) {
			if (empty($d["ship_to_info_id"])) {
				$vmLogger->err( 'validate add'.JText::_('VM_CHECKOUT_ERR_NO_SHIPTO',false) );
				return False;
			}
		}
		/*
		if (!$d["payment_method_id"]) {
			$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_PAYM',false) );
			return False;
		}*/
		if ($vmPaymentMethod->is_creditcard(@$d["payment_method_id"])) {

			if (empty($_SESSION["ccdata"]["order_payment_number"])) {
				$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_CCNR',false) );
				return False;
			}

			if(!$vmPaymentMethod->validate_payment($d["payment_method_id"],
					$_SESSION["ccdata"]["order_payment_number"])) {
				$vmLogger->err( JText::_('VM_CHECKOUT_ERR_CCNUM_INV',false) );
				return False;
			}

			if(empty( $_SESSION["ccdata"]["order_payment_expire"])) {
				$vmLogger->err( JText::_('VM_CHECKOUT_ERR_CCDATE_INV',false) );
				return False;
			}
		}

		return True;
	}

	function validate_shipto(&$d) {
		//TODO to be implemented
	}
	/**
	 * Called to validate the shipping_method
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_shipping_method(&$d) {
		global  $vm_mainframe, $vmLogger;
		
		if( empty($d['shipping_rate_id']) ) {
			$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_SHIP',false) );
			return false;
		}
		
		$result = $vm_mainframe->triggerEvent('validate', array( $d ));
		if( is_array($result) && $result[0] === false ) {
			$vmLogger->err( JText::_('VM_CHECKOUT_ERR_OTHER_SHIP',false) );
			return false;
		}
		
		return true;
	}

	/**
	 * Called to validate the payment_method
	 * If payment with CreditCard is used, than the Data must be in stored in the session
	 * This has be done to prevent sending the CreditCard Number back in hidden fields
	 * If the parameter $is_test is true the Number Visa Creditcard number 4111 1111 1111 1111
	 *
	 * @param array $d
	 * @param boolean $is_test
	 * @return boolean
	 */
	function validate_payment_method(&$d, $is_test) {
		global  $vmLogger, $order_total;

		$auth = $_SESSION['auth'];
		$cart = $_SESSION['cart'];
		
		// We don't need to validate a payment method when
		// the user has no order total he should pay
		if( empty( $_REQUEST['order_total'])) {
			
			if( isset( $d['order_total'])) {
				if( round( $d['order_total'], 2 ) <= 0.00 ) {
					return true;
				}
			}
			if( isset($order_total) && $order_total <= 0.00 ) {
				return true;
			}
		}
		
//		if (!isset($d["payment_method_id"]) || $d["payment_method_id"]==0 ) {
		if (empty($d["payment_method_id"])){
			$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_PAYM',false) );
			return false;
		}
		require_once(CLASSPATH.'paymentMethod.class.php');
		$vmPaymentMethod = new vmPaymentMethod;

		$dbp = new ps_DB; //DB Payment_method

		// Now Check if all needed Payment Information are entered
		// Bank Information is found in the User_Info
		$w  = "SELECT `type` FROM `#__{vm}_payment_method` WHERE ";
		$w .= "payment_method_id=" .  (int)$d["payment_method_id"];
		$dbp->query($w);
		$dbp->next_record();
		
		if (($dbp->f("type") == "Y") 
			|| ($dbp->f("type") == "")) {

			// Creditcard
			if (empty( $_SESSION['ccdata']['creditcard_code']) ) {
				$vmLogger->err( JText::_('VM_CHECKOUT_ERR_CCTYPE') );
				return false;
			}

			// $_SESSION['ccdata'] = $ccdata;
			// The Data should be in the session
			if (!isset($_SESSION['ccdata'])) { //Not? Then Error
				$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_CCDATA',false) );
				return False;
			}

			if (!$_SESSION['ccdata']['order_payment_number']) {
				$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_CCNR_FOUND',false) );
				return False;
			}

			// CREDIT CARD NUMBER CHECK
			// USING THE CREDIT CARD CLASS in ps_payment
			if(!$vmPaymentMethod->validate_payment( $_SESSION['ccdata']['creditcard_code'], $_SESSION['ccdata']['order_payment_number'])) {
				$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_CCDATE',false) );
				return False;
			}

			if (!$is_test) {
				$payment_number = ereg_replace(" |-", "", $_SESSION['ccdata']['order_payment_number']);
				if ($payment_number == "4111111111111111") {
					$vmLogger->warning( JText::_('VM_CHECKOUT_ERR_TEST',false) );
					return False;
				}
			}
			if(!empty($_SESSION['ccdata']['need_card_code']) && empty($_SESSION['ccdata']['credit_card_code'])) {
				$vmLogger->err( JText::_('VM_CUSTOMER_CVV2_ERROR',false) );
				return False;
			}
			if(!$_SESSION['ccdata']['order_payment_expire_month']) {
				$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_CCMON',false) );
				return False;
			}
			if(!$_SESSION['ccdata']['order_payment_expire_year']) {
				$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_CCYEAR',false) );
				return False;
			}
			$date = getdate( time() );
			if ($_SESSION['ccdata']['order_payment_expire_year'] < $date["year"] or
			($_SESSION['ccdata']['order_payment_expire_year'] == $date["year"] and
			$_SESSION['ccdata']['order_payment_expire_month'] < $date["mon"])) {
				$vmLogger->err( JText::_('VM_CHECKOUT_ERR_CCDATE_INV',false) );
				return False;
			}
			return True;
		}
		elseif ($dbp->f("type") == "B") {
			$_SESSION['ccdata']['creditcard_code'] = "";
			$_SESSION['ccdata']['order_payment_name']  = "";
			$_SESSION['ccdata']['order_payment_number']  = "";
			$_SESSION['ccdata']['order_payment_expire_month'] = "";
			$_SESSION['ccdata']['order_payment_expire_year'] = "";
			// Bank Account
			require_once( CLASSPATH . 'ps_user.php' );
			$dbu =& ps_user::getUserInfo( $auth["user_id"], array( 'bank_account_holder','bank_iban','bank_account_nr','bank_sort_code','bank_name' ) ); 

			if ( $dbu->f("bank_account_holder") == "" || $dbu->f("bank_account_nr") =="" ) {
				if( !empty($d['bank_account_holder']) && !empty($d['bank_account_nr'])) {
					// Insert the given data
					$fields = array('id' => $auth["user_id"],
							'bank_account_holder' => $d['bank_account_holder'],
							'bank_account_nr' => $d['bank_account_nr'],
							'bank_sort_code' => $d['bank_sort_code'],
							'bank_name' => $d['bank_name'],
							'bank_iban' => $d['bank_iban']
							);
					
					ps_user::update($fields);
					//The function below dont use a validations and is only for private use in ps_user
//					ps_user::setUserInfoWithEmail( $fields, $auth["user_id"] );

					$dbu =& ps_user::getUserInfo( $auth["user_id"], array( 'bank_account_holder','bank_iban','bank_account_nr','bank_sort_code','bank_name' ) ); 
				}
				else {
					$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_USER_DATA',false) );
					return False;
				}
			}
			if ($dbu->f("bank_account_holder") == ""){
				$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_BA_HOLDER_NAME',false) );
				return False;
			}
			if (($dbu->f("bank_iban") == "") and
			($dbu->f("bank_account_nr") =="")) {
				$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_IBAN',false) );
				return False;
			}
			if ($dbu->f("bank_iban") == "") {
				if ($dbu->f("bank_account_nr") == ""){
					$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_BA_NUM',false) );
					return False;
				}
				if ($dbu->f("bank_sort_code") == ""){
					$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_BANK_SORT',false) );
					return False;
				}
				if ($dbu->f("bank_name") == ""){
					$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_BANK_NAME',false) );
					return False;
				}
			}
		}
		else {
			$_SESSION['ccdata']['creditcard_code'] = '';
			$_SESSION['ccdata']['order_payment_name']  = "";
			$_SESSION['ccdata']['order_payment_number']  = "";
			$_SESSION['ccdata']['order_payment_expire_month'] = "";
			$_SESSION['ccdata']['order_payment_expire_year'] = "";
		}
		// Enter additional Payment check procedures here if neccessary

		return True;
	}

	/**
	 * Update order details
	 * CURRENTLY UNUSED
	 *
	 * @param array $d
	 * @return boolean
	 */
	function update(&$d) {
		global $vmLogger;
		
		$db = new ps_DB;
		$timestamp = time();


		if ($this->validate_update($d)) {
			return True;
		}
		else {
			$vmLogger->err( $this->error );
			return False;
		}
	}

	/**
	 * Control Function for the Checkout Process
	 * @author Ekkhard Domning
	 * @author soeren
	 * @param array $d
	 * @return boolean
	 */
	function process(&$d) {
		global $checkout_this_step, $sess, $vmLogger;
		$ccdata = array();

		if( empty($d["checkout_this_step"]) || !is_array(@$d["checkout_this_step"])) {
			$vmLogger->err( JText::_('VM_CHECKOUT_ERR_NO_VALID_STEP',false) );
			return false;
		}
		
		foreach($d["checkout_this_step"] as $checkout_this_step) {
		
			switch($checkout_this_step) {
				
				case 'CHECK_OUT_GET_FINAL_BASKET' :
					break;
	
				case 'CHECK_OUT_GET_SHIPPING_ADDR' :		
					// The User has choosen a Shipping address
					if (empty($d["ship_to_info_id"])) {
						$vmLogger->err('I am in  process '.JText::_('VM_CHECKOUT_ERR_NO_SHIPTO',false) );
						unset( $_POST['checkout_this_step']);
						return False;
					}
					break;
	
				case 'CHECK_OUT_GET_SHIPPING_METHOD':
					// The User has choosen a Shipping method
					if (!$this->validate_shipping_method($d)) {
						unset( $_POST['checkout_this_step']);
						return false;
					}
					break;
	
				case 'CHECK_OUT_GET_PAYMENT_METHOD':
					
					// The User has choosen a payment method
					$_SESSION['ccdata']['order_payment_name'] = @$d['order_payment_name'];
					// VISA, AMEX, DISCOVER....
					$_SESSION['ccdata']['creditcard_code'] = @$d['creditcard_code'];
					$_SESSION['ccdata']['order_payment_number'] = @$d['order_payment_number'];
					$_SESSION['ccdata']['order_payment_expire_month'] = @$d['order_payment_expire_month'];
					$_SESSION['ccdata']['order_payment_expire_year'] = @$d['order_payment_expire_year'];
					// 3-digit Security Code (CVV)
					$_SESSION['ccdata']['credit_card_code'] = @$d['credit_card_code'];
					$GLOBALS['vmLogger']->info('process '.$d["payment_method_id"]);
					if (!$this->validate_payment_method($d, false)) { //Change false to true to Let the user play with the VISA Testnumber
						unset( $_POST['checkout_this_step']);
						return false;
					}
					
					break;
	
				case 'CHECK_OUT_GET_FINAL_CONFIRMATION':
					
				
					// The User wants to order now, validate everything, if OK than Add immeditialtly
					return( $this->storeOrderInformationToDB( $d ) );
	
				default:
					$vmLogger->crit( "CheckOut step ($checkout_this_step) is undefined!" );
					return false;
	
			} // end switch
		}
		return true;
	} // end function process

	/**
	 * Prints the List of all shipping addresses of a user
	 *
	 * @param unknown_type $user_id
	 * @param unknown_type $name
	 * @param unknown_type $value
	 */
	function ship_to_addresses_radio($user_id, $name, $value) {
		echo ps_checkout::list_addresses( $user_id, $name, $value );
	}
	/**
	 * Creates a Radio List of all shipping addresses of a user
	 *
	 * @param int $user_id
	 * @param string $name
	 * @param string $value
	 */
	function list_addresses( $user_id, $name, $value ) {
		global $sess,$vmLogger;

		
		$db = new ps_DB;

		/* Select all the ship to information for this user id and
		* order by modification date; most recently changed to oldest
		*/
//		$q  = "SELECT 'user_info_id','address_type_name' from #__{vm}_user_info WHERE ";
//		$q .= "user_id=" . (int)$user_id . ' ';
//		$q .= "AND address_type='BT'";
//		$db->query($q);
//		$db->next_record();
//
//		$bt_user_info_id = $db->f("user_info_id","address_type_name");

		//This is unsure and must be tested by Max Milbers
//		$q  = "SELECT * FROM (#__{vm}_user_info u , #__users ju) ";
//		$q .= "INNER JOIN #__{vm}_country c ON (u.country=c.country_3_code) ";
//		$q .= "LEFT JOIN #__{vm}_state s ON (u.state=s.state_2_code AND s.country_id=c.country_id) ";	
//		$q .= "WHERE u.user_id =". (int)$user_id ." AND ju.id = ".(int)$user_id;
//		$q .= " AND address_type = 'ST' ";
//		$q .= " ORDER by address_type_name, mdate DESC";
//		$db->query($q);
//		$db->next_record();

		require_once(CLASSPATH. "ps_user.php");
//		$db = ps_user::get_user_details((int)$user_id ,array("*"),"address_type_name, mdate DESC","AND address_type = 'ST'", false);
		$db = ps_user::get_user_details((int)$user_id ,array("*"),"address_type_name, mdate DESC","", false);

		$theme = vmTemplate::getInstance();
		$theme->set_vars(array('db' => $db,
								'user_id' => $user_id,
								'name' => $name,
								'value' => $value,
								'bt_user_info_id' => $db->f("user_info_id"),
						 	)
						 );
		echo $theme->fetch( 'checkout/list_shipto_addresses.tpl.php');
	}

	/**
	 * Fetches the address information for the currently logged in user
	 *
	 * @param string $address_type Can be BT (Bill To) or ST (Shipto address)
	 */
	function display_address($address_type='BT') { 
		global $vmLogger;
		$auth = $_SESSION['auth'];
		$address_type = $address_type == 'BT' ? $address_type : 'ST';
		
		require_once(CLASSPATH. "ps_user.php");
		//by Max Milbers seems  to work
		$db = ps_user::get_user_details($auth['user_id'],array('*'),'','AND `u`.`address_type`= "'.$address_type.'"');
		
		$theme = new $GLOBALS['VM_THEMECLASS']();
		$theme->set('db', $db );
//		$theme->set_vars(array('db' => $db,
//						'user_id' => $user_id,
//						'name' => $name,
//						'value' => $value,
//						'bt_user_info_id' => $db->f("user_info_id"),
//				 	)
//				 );
		return $theme->fetch('checkout/customer_info.tpl.php');
		
	}
	/**
	 * Lists Shipping Methods of all published Shipping Modules
	 *
	 * @param string $ship_to_info_id
	 * @param string $shipping_method_id
	 */
	function list_shipping_methods( $ship_to_info_id=null, $shipping_method_id=null ) {
		global $vmLogger, $auth, $weight_total;
		
		if( empty( $ship_to_info_id )) {
		    // Get the Bill to user_info_id
		    $database = new ps_DB();
		    $database->setQuery( "SELECT user_info_id FROM #__{vm}_user_info WHERE user_id=".$auth['user_id']." AND address_type='BT'" );
		    $vars["ship_to_info_id"] = $_REQUEST['ship_to_info_id'] = $database->loadResult();
		} else {
			$vars['ship_to_info_id'] = $ship_to_info_id;
		}
		$vars['shipping_rate_id'] = $shipping_method_id;
		$vars["weight"] = $weight_total;
		$vars['zone_qty'] = vmRequest::getInt( 'zone_qty', 0 );
		$i = 0;

		$theme = vmTemplate::getInstance();
		$theme->set_vars(array('vars' => $vars));

		echo $theme->fetch( 'checkout/list_shipping_methods.tpl.php');
		
	}
	/**
	 * Lists the payment methods of all available payment modules
	 * @static 
	 * @param int $payment_method_id
	 */
	function list_payment_methods( $payment_method_id=0 ) {
		global $order_total, $sess, $VM_CHECKOUT_MODULES;
		
		//This is the id of the mainvendor because the payment mehthods are not vendorrelated yet
//		$hVendor_id = $_SESSION['ps_vendor_id'];
		$hVendor_id = 1; 
		$auth = $_SESSION['auth'];
		
		$ship_to_info_id = JRequest::getVar( 'ship_to_info_id' );
		$shipping_rate_id = JRequest::getVar( 'shipping_rate_id' );
		
        require_once(CLASSPATH . 'paymentMethod.class.php');
        $vmPaymentMethod = new vmPaymentMethod;
		require_once( CLASSPATH. 'ps_creditcard.php' );
	    $ps_creditcard = new ps_creditcard();

		// Do we have Credit Card Payments?
		$db_cc  = new ps_DB;
		$q = "SELECT * FROM #__{vm}_payment_method,#__{vm}_shopper_group WHERE ";
		$q .= "#__{vm}_payment_method.shopper_group_id=#__{vm}_shopper_group.shopper_group_id ";
		$q .= "AND (#__{vm}_payment_method.shopper_group_id='".$auth['shopper_group_id']."' ";
		$q .= "OR #__{vm}_shopper_group.default='1') ";
		$q .= "AND (type='' OR type='Y') ";
		$q .= "AND published='Y' ";
//		$q .= "AND #__{vm}_payment_method.vendor_id='$hVendor_id' ";
		$q .= " ORDER BY ordering";
		$db_cc->query($q);
//		$GLOBALS['vmLogger']->info('list_payment_methods '.$q);
		if ($db_cc->num_rows()) {
		    $cc_payments=true;
		}
		else {
		    $cc_payments=false;
		}
		$count = 0;
		$db_nocc  = new ps_DB;
		$q = "SELECT * from #__{vm}_payment_method,#__{vm}_shopper_group WHERE ";
		$q .= "#__{vm}_payment_method.shopper_group_id=#__{vm}_shopper_group.shopper_group_id ";
		$q .= "AND (#__{vm}_payment_method.shopper_group_id='".$auth['shopper_group_id']."' ";
		$q .= "OR #__{vm}_shopper_group.default='1') ";
		$q .= "AND (type='B' OR type='N' OR type='P') ";
		$q .= "AND published='Y' ";
//		$q .= "AND #__{vm}_payment_method.vendor_id='$vendor_id' ";
		$q .= " ORDER BY ordering";
		$db_nocc->query($q);
		if ($db_nocc->next_record()) {
		    $nocc_payments=true;
		    $first_payment_method_id = $db_nocc->f("id");
		    $count = $db_nocc->num_rows();
		    $db_nocc->reset();
		}
		else {
		    $nocc_payments=false;
		}
		
		//this is not really sensefull, because the paymentmethod is not saved automatically
		//AND how should the customer give his data for the transfer?
        // Redirect to the last step when there's only one payment method
//		if( $VM_CHECKOUT_MODULES['CHECK_OUT_GET_PAYMENT_METHOD']['order'] != $VM_CHECKOUT_MODULES['CHECK_OUT_GET_FINAL_CONFIRMATION']['order'] ) {
//			if ($count <= 1 && $cc_payments==false) {
//				vmRedirect($sess->url(SECUREURL.basename($_SERVER['PHP_SELF'])."?page=checkout.index&payment_method_id=$first_payment_method_id&ship_to_info_id=$ship_to_info_id&shipping_rate_id=".urlencode($shipping_rate_id)."&checkout_stage=".$VM_CHECKOUT_MODULES['CHECK_OUT_GET_FINAL_CONFIRMATION']['order'], false, false ),"");
//			}
//			elseif( isset($order_total) && $order_total <= 0.00 ) {
		if( isset($order_total) && $order_total <= 0.00 ) {
			// In case the order total is less than or equal zero, we don't need a payment method
			vmRedirect($sess->url(SECUREURL.basename($_SERVER['PHP_SELF'])."?page=checkout.index&ship_to_info_id=$ship_to_info_id&shipping_rate_id=".urlencode($shipping_rate_id)."&checkout_stage=".$VM_CHECKOUT_MODULES['CHECK_OUT_GET_FINAL_CONFIRMATION']['order'], false, false),"");
		}
//		}
		$theme = new $GLOBALS['VM_THEMECLASS']();
		$theme->set_vars(array('db_nocc' => $db_nocc,
								'db_cc' => $db_cc,
								'nocc_payments' => $nocc_payments,
								'payment_method_id' => $payment_method_id,
								'first_payment_method_id' => $first_payment_method_id,
								'count' => $count,
								'cc_payments' => $cc_payments,
								'ps_creditcard' => $ps_creditcard,
								'vmPaymentMethod' => $vmPaymentMethod
						 	)
						 );

		echo $theme->fetch( 'checkout/list_payment_methods.tpl.php');
		
	}

	/**
	 * This is the main function which stores the order information in the database
	 * 
	 * @author gday, soeren, many others!
	 * @param array $d The REQUEST/$vars array
	 * @return boolean
	 */
	function storeOrderInformationToDB( &$d ) {
//	function add( &$d ) {
		global $order_tax_details, $vm_mainframe, $auth, $my, $mosConfig_offset,
		$vmLogger, $vmInputFilter, $discount_factor, $mosConfig_mailfrom, $mosConfig_fromname;

		$cart = $_SESSION['cart'];
		$vendor_id = $cart['cart_vendor_id'];

		require_once(CLASSPATH. 'paymentMethod.class.php' );
		$vmPaymentMethod = new vmPaymentMethod;
		require_once(CLASSPATH. 'ps_product.php' );
		$ps_product= new ps_product;
		require_once(CLASSPATH.'ps_cart.php');
		$ps_cart = new ps_cart;

		$db = new ps_DB;

		/* Set the order number */
		$order_number = $this->get_order_number();

		$totals = $this->calc_order_totals( $d );
		extract( $totals );
		
		$timestamp = time() + ($mosConfig_offset*60*60);
		
		if (!$this->validate_form($d)) {
			return false;
		}

		if (!$this->validate_add($d)) {
			return false;
		}

		// make sure Total doesn't become negative
		if( $order_total < 0 ) $order_total = 0;

		$order_total = round( $order_total, 2);


		$vmLogger->debug( '-- Checkout Debug--	
			Subtotal: '.$order_subtotal.'
			Taxable: '.$order_taxable.'
			Payment Discount: '.$payment_discount.'
			Coupon Discount: '.$coupon_discount.'
			Shipping: '.$order_shipping.'
			Shipping Tax : '.$order_shipping_tax.'
			Tax : '.$order_tax.'
			------------------------
			Order Total: '.$order_total.'
			----------------------------' 
		);

		vmPaymentMethod::importPaymentPluginById($d["payment_method_id"]);
	    
		$process_payment_result = $vm_mainframe->triggerEvent('process_payment', array($order_number,$order_total, $d) );
		
		
		if (is_array($process_payment_result) && @$process_payment_result[0] === false ) {
			$vmLogger->err( JText::_('VM_PAYMENT_ERROR',false));
			$_SESSION['last_page'] = "checkout.index";
			$_REQUEST["checkout_next_step"] = CHECK_OUT_GET_PAYMENT_METHOD;
			return False;
		}
		else {
			$d["order_payment_log"] = JText::_('VM_CHECKOUT_MSG_LOG');
		}

		// Remove the Coupon, because it is a Gift Coupon and now is used!!
		if( @$_SESSION['coupon_type'] == "gift" ) {
			$d['coupon_id'] = $_SESSION['coupon_id'];
			include_once( CLASSPATH.'ps_coupon.php' );
			ps_coupon::remove_coupon_code( $d );
		}
		
		// Get the IP Address
		if (!empty($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		else {
			$ip = 'unknown';
		}
		// Collect all fields and values to store them!
		$fields = array(
			'user_id' => $auth["user_id"], 
			'vendor_id' => $vendor_id, 
			'order_number' => $order_number, 
			'user_info_id' =>  $d["ship_to_info_id"], 
			'ship_method_id' => @urldecode($d["shipping_rate_id"]),
			'order_total' => $order_total, 
			'order_subtotal' => $order_subtotal, 
			'order_tax' => $order_tax, 
			'order_tax_details' => serialize($order_tax_details), 
			'order_shipping' => $order_shipping,
			'order_shipping_tax' => $order_shipping_tax, 
			'order_discount' => $payment_discount, 
			'coupon_discount' => $coupon_discount,
			'coupon_code' => @$_SESSION['coupon_code'],
			'order_currency' => $GLOBALS['product_currency'], 
			'order_status' => 'P', 
			'cdate' => $timestamp,
			'mdate' => $timestamp,
			'customer_note' => htmlspecialchars(strip_tags($d['customer_note']), ENT_QUOTES ),
			'ip_address' => $ip
			);

		// Insert the main order information
		$db->buildQuery( 'INSERT', '#__{vm}_orders', $fields );
		$result = $db->query();

		$d["order_id"] = $order_id = $db->last_insert_id();
		if( $result === false || empty( $order_id )) {
			$vmLogger->crit( 'Adding the Order into the Database failed! User ID: '.$auth["user_id"] );
			return false;
		}
		
		$vm_mainframe->triggerEvent('onAfterOrderAdd',array($order_id, $d));

	    // Insert the initial Order History.	    
		$mysqlDatetime = date("Y-m-d G:i:s", $timestamp);
		
		$fields = array(
					'order_id' => $order_id,
					'order_status_code' => 'P',
					'date_added' => $mysqlDatetime,
					'customer_notified' => 1,
					'comments' => ''
				  );
		$db->buildQuery( 'INSERT', '#__{vm}_order_history', $fields );
		$db->query();

		/**
	    * Insert the Order payment info 
	    */
		$payment_number = ereg_replace(" |-", "", @$_SESSION['ccdata']['order_payment_number']);

		$d["order_payment_code"] = @$_SESSION['ccdata']['credit_card_code'];

		// Payment number is encrypted using mySQL encryption functions.
		$fields = array(
					'order_id' => $order_id, 
					'payment_method_id' => $d["payment_method_id"], 
					'order_payment_log' => @$d["order_payment_log"], 
					'order_payment_trans_id' => $vmInputFilter->safeSQL( @$d["order_payment_trans_id"] )
				  );
		if( !empty( $payment_number ) && VM_STORE_CREDITCARD_DATA == '1' ) {
			// Store Credit Card Information only if the Store Owner has decided to do so
			$fields['order_payment_code'] = $d["order_payment_code"];
			$fields['order_payment_expire'] = @$_SESSION["ccdata"]["order_payment_expire"];
			$fields['order_payment_name'] = @$_SESSION["ccdata"]["order_payment_name"];
			$fields['order_payment_number'] = VM_ENCRYPT_FUNCTION."( '$payment_number','" . ENCODE_KEY . "')";
			$specialfield = array('order_payment_number');
		} else {
			$specialfield = array();
		}
		$db->buildQuery( 'INSERT', '#__{vm}_order_payment', $fields, '', $specialfield );
		$db->query();

		/**
		* Insert the User Billto & Shipto Info
		* This here, the complete thing up to "Insert all Products from Cart" is redundant data shit
		* But for legal reasons the order must be saved completly.
		*/
		// First: get all the fields from the user field list to copy them from user_info into the order_user_info
		$fields = array();
		require_once( CLASSPATH . 'ps_userfield.php' );
		$userfields = ps_userfield::getUserFields('', false, '', true, true );
		foreach ( $userfields as $field ) {
			$fields[] = $field->name;
		}
		
		//Solution
		$fields['address_type'] =  'address_type';

//		$fieldstr = '`'. implode( '`,`', $fields ) . '`,`address_type`';
		require_once( CLASSPATH . 'ps_user.php' );
		$dbU = ps_user::get_user_details($auth['user_id'],$fields);
		
		$userInfo = array();
		//Probably there exists a nicer function to do that	
		foreach ( $fields as $field ) {
			$userInfo[$field] = $dbU -> f($field);
		}
		$userInfo['order_id'] =  $d['order_id'];
		$userInfo['user_id'] =  $auth['user_id'];
		
		$db->buildQuery( 'INSERT', '#__{vm}_order_user_info', $userInfo);
		if( $db->query() === false ) {
			$GLOBALS['vmLogger']->err('setShopper BT adress failed for user_id '.$auth['user_id']);
			return false;
		}
		//Here it would be good to have the choosen index
//		$selectedST=2;
		if( $db->next_record() ) {
			$db->buildQuery( 'INSERT', '#__{vm}_order_user_info', $userInfo);
			if( $db->query() === false ) {
				$GLOBALS['vmLogger']->err('setShopper ST adress failed for user_id '.$auth['user_id']);
				return false;
			}
		}
//		$keyValues = $dbU->loadResultArray( $selectedST );
//		if(!empty($keyValues)){
//			$db->buildQuery( 'INSERT', '#__{vm}_order_user_info', $keyValues);
//		}

		//The problem with the old construction is the email adress which is stored in the joomla user table
//		unset($fields['email']);
//		$fieldstr = '`'. implode( '`,`', $fields );
//		// Save current Bill To Address
//		$q = "INSERT INTO `#__{vm}_order_user_info` 
//			(`order_info_id`,`order_id`,`user_id`,address_type, ".$fieldstr.") ";
//		$q .= "SELECT NULL, '$order_id', '".$auth['user_id']."', address_type, ".$fieldstr." FROM #__{vm}_user_info WHERE user_id='".$auth['user_id']."' AND address_type='BT'";
//		$db->query( $q );
//
//		// Save current Ship to Address if applicable
//		$q = "INSERT INTO `#__{vm}_order_user_info` 
//			(`order_info_id`,`order_id`,`user_id`,address_type, ".$fieldstr.") ";
//		$q .= "SELECT NULL, '$order_id', '".$auth['user_id']."', address_type, ".$fieldstr." FROM #__{vm}_user_info WHERE user_id='".$auth['user_id']."' AND user_info_id='".$d['ship_to_info_id']."' AND address_type='ST'";
//		$db->query( $q );


		/**
    	* Insert all Products from the Cart into order line items; 
    	* one row per product in the cart 
    	*/
		$dboi = new ps_DB;

		for($i = 0; $i < $cart["idx"]; $i++) {

			$r = "SELECT product_id,product_in_stock,product_sales,product_parent_id,product_sku,product_name ";
			$r .= "FROM #__{vm}_product WHERE product_id='".$cart[$i]["product_id"]."'";
			$dboi->query($r);
			$dboi->next_record();

			$product_price_arr = $ps_product->get_adjusted_attribute_price($cart[$i]["product_id"], $cart[$i]["description"]);
			$product_price = $GLOBALS['CURRENCY']->convert( $product_price_arr["product_price"], $product_price_arr["product_currency"] );

			if( empty( $_SESSION['product_sess'][$cart[$i]["product_id"]]['tax_rate'] )) {
				$my_taxrate = $ps_product->get_product_taxrate($cart[$i]["product_id"] );
			}
			else {
				$my_taxrate = $_SESSION['product_sess'][$cart[$i]["product_id"]]['tax_rate'];
			}
			// Attribute handling
			$product_parent_id = $dboi->f('product_parent_id');
			$description = '';
			if( $product_parent_id > 0 ) {
				
				$db_atts = $ps_product->attribute_sql( $dboi->f('product_id'), $product_parent_id );
				while( $db_atts->next_record()) {
					$description .=	$db_atts->f('attribute_name').': '.$db_atts->f('attribute_value').'; ';
				}
			}
			
			$description .= $ps_product->getDescriptionWithTax($_SESSION['cart'][$i]["description"], $dboi->f('product_id'));
			
			$product_final_price = round( ($product_price *($my_taxrate+1)), 2 );
			
			$fields = array('order_id' => $order_id, 
									'user_info_id' => $d["ship_to_info_id"],
									'vendor_id' => $vendor_id, 
									'product_id' => $cart[$i]["product_id"], 
									'order_item_sku' => $dboi->f("product_sku"), 
									'order_item_name' => $dboi->f("product_name"), 
									'product_quantity' => $cart[$i]["quantity"], 
									'product_item_price' => $product_price, 
									'product_final_price' => $product_final_price, 		
									'order_item_currency' => $GLOBALS['product_currency'],
									'order_status' => 'P', 
									'product_attribute' => $description, 
									'cdate' => $timestamp, 
									'mdate' => $timestamp
						);
			$db->buildQuery( 'INSERT', '#__{vm}_order_item', $fields );
			$db->query();

			// Update Stock Level and Product Sales, decrease - no matter if in stock or not!
			$q = "UPDATE #__{vm}_product ";
			$q .= "SET product_in_stock = product_in_stock - ".(int)$cart[$i]["quantity"];
			$q .= " WHERE product_id = '" . $cart[$i]["product_id"]. "'";
			$db->query($q);

			$q = "UPDATE #__{vm}_product ";
			$q .= "SET product_sales= product_sales + ".(int)$cart[$i]["quantity"];
			$q .= " WHERE product_id='".$cart[$i]["product_id"]."'";
			$db->query($q);

      // Sends a notification email to the administrator indicating that the minimum number of stock has been reached
			$q = "SELECT product_sku, product_name, product_in_stock, low_stock_notification ";
      $q .= "FROM #__{vm}_product WHERE product_id = '" . $cart[$i]["product_id"]. "'";
      $db->query($q);
        if ($db->f("low_stock_notification")>$db->f("product_in_stock")) {
        
        // The product information
        $product_name = $db->f("product_name");
        $product_sku = $db->f("product_sku");
        $product_in_stock = $db->f("product_in_stock");
        
        // The email subject
        $subject = sprintf( JText::_( 'VM_LOW_STOCK_NOTIFICATION_EMAIL_SUBJECT' ), $db->f("product_name"));
        
        // The email body
        $msg = str_replace( '{product_name}', $product_name, JText::_( 'VM_LOW_STOCK_NOTIFICATION_EMAIL_MESSAGE' ) );
        $msg = str_replace( '{product_sku}', $product_sku, $msg );
        $msg = str_replace( '{product_in_stock}', $product_in_stock, $msg );
        $msg = vmHtmlEntityDecode( $msg );
        
        // Send the email
        vmMail($mosConfig_mailfrom, $mosConfig_fromname, $mosConfig_mailfrom, $subject, $msg, "" );
        }
		}

		######## BEGIN DOWNLOAD MOD ###############
		if( ENABLE_DOWNLOADS == "1" ) {
			require_once( CLASSPATH.'ps_order.php');
			for($i = 0; $i < $cart["idx"]; $i++) {
				
				$params = array('product_id' => $cart[$i]["product_id"], 'order_id' => $order_id, 'user_id' => $auth["user_id"] );
				ps_order::insert_downloads_for_product( $params );
				
				if( @VM_DOWNLOADABLE_PRODUCTS_KEEP_STOCKLEVEL == '1' ) {
					// Update the product stock level back to where it was.
					$q = "UPDATE #__{vm}_product ";
					$q .= "SET product_in_stock = product_in_stock + ".(int)$cart[$i]["quantity"];
					$q .= " WHERE product_id = '" .(int)$cart[$i]["product_id"]. "'";
					$db->query($q);
				}
			}
		}
		################## END DOWNLOAD MOD ###########

		// Export the order_id so the checkout complete page can get it
		$d["order_id"] = $order_id;

		/*
		 * Let the shipping module know which shipping method
		 * was selected.  This way it can save any information
		 * it might need later to print a shipping label.
		 */
		$vm_mainframe->triggerEvent('save_rate_info', array($d));

		// Now as everything else has been done, we can update
		// the Order Status if the Payment Method is
		// "Use Payment Processor", because:
		// Payment Processors return false on any error
		// Only completed payments return true!
		$update_order = false;
		
		$vmPaymentMethod = new vmPaymentMethod();
		$payment_plg = $vmPaymentMethod->get($d['payment_method_id']);
		if( is_object($payment_plg) ) {
			if( $payment_plg->f('type') == "Y" ) {
				$params = new vmParameters($payment_plg->f('params'),ADMINPATH.'plugins/payment/'.$payment_plg->f('element').'.xml', 'payment');
				if( $params->get('PAYMENT_VERIFIED_STATUS')) {
	              	$d['order_status'] = $params->get('PAYMENT_VERIFIED_STATUS');
	              	$update_order = true;
	            }
			}
        } elseif( $order_total == 0.00 ) {
        	// If the Order Total is zero, we can confirm the order to automatically enable the download
        	$d['order_status'] = ENABLE_DOWNLOAD_STATUS;
        	$update_order = true;
        }
		if ( $update_order ) {
			require_once(CLASSPATH."ps_order.php");
			$ps_order =& new ps_order();
			$ps_order->order_status_update($d);
		}

		// Send the e-mail confirmation messages
		$this->email_receipt($order_id);

		// Reset the cart (=empty it)
		$ps_cart->reset();
        $_SESSION['savedcart']['idx']=0;
        $ps_cart->saveCart();

		// Unset the payment_method variables
		$d["payment_method_id"] = "";
		$d["order_payment_number"] = "";
		$d["order_payment_expire"] = "";
		$d["order_payment_name"] = "";
		$d["credit_card_code"] = "";
		// Clear the sensitive Session data
		$_SESSION['ccdata']['order_payment_name']  = "";
		$_SESSION['ccdata']['order_payment_number']  = "";
		$_SESSION['ccdata']['order_payment_expire_month'] = "";
		$_SESSION['ccdata']['order_payment_expire_year'] = "";
		$_SESSION['ccdata']['credit_card_code'] = "";
		$_SESSION['coupon_discount'] = "";
		$_SESSION['coupon_id'] = "";
		$_SESSION['coupon_redeemed'] = false;
		
		$_POST["payment_method_id"] = "";
		$_POST["order_payment_number"] = "";
		$_POST["order_payment_expire"] = "";
		$_POST["order_payment_name"] = "";
		
		/*
		if( empty($my->id) && !empty( $auth['user_id'])) {
			require_once(CLASSPATH.'ps_user.php');
			ps_user::logout();
		}
		*/

		return True;
	}

	/**
	 * Create an order number using the session id, session
	 * name, and the current unix timestamp.
	 *
	 * @return string
	 */
	function get_order_number() {
		global $auth;

		/* Generated a unique order number */

		$str = session_id();
		$str .= (string)time();

		$order_number = $auth['user_id'] .'_'. md5($str);

		return substr($order_number, 0, 32);
	}
	/**
         * Stores the md5 hash of the recent cart in the var _cartHash
         *
         */
	function generate_cart_hash() {
		$this->_cartHash = $this->get_new_cart_hash();
	}
	
	function get_order_total( &$d ) {
		global $discount_factor;
		$totals = $this->calc_order_totals($d);
		return $totals['order_total'];
	}
	
	/**
	 * Calculates the current order totals and fills an array with all the values
	 *
	 * @param array $d
	 * @return array
	 */
	function calc_order_totals( &$d ) {
		global $discount_factor, $mosConfig_offset;
		
		$totals = array();
		
		/* sets _subtotal */
		$totals['order_subtotal'] = $tmp_subtotal = $this->calc_order_subtotal($d);
		
		$totals['order_taxable'] = $this->calc_order_taxable($d);
		
		if( !empty($d['payment_method_id'])) {
			$totals['payment_discount'] = $d['payment_discount'] = $this->get_payment_discount($d['payment_method_id'], $totals['order_subtotal']);
		} else {
			$totals['payment_discount'] = $d['payment_discount'] = 0.00;
		}

		/* DISCOUNT HANDLING */
		if( !empty($_SESSION['coupon_discount']) ) {
			$totals['coupon_discount'] = floatval($_SESSION['coupon_discount']);
		}
		else {
			$totals['coupon_discount'] = 0.00;
		}

		// make sure Total doesn't become negative
		if( $tmp_subtotal < 0 ) $totals['order_subtotal'] = $tmp_subtotal = 0;
		if( $totals['order_taxable'] < 0 ) $totals['order_taxable'] = 0;

		// from now on we have $order_tax_details
		$d['order_tax'] = $totals['order_tax'] = round( $this->calc_order_tax($totals['order_taxable'], $d), 2 );
		
		
		// Get the Shipping Total
		$d['order_shipping'] = $totals['order_shipping'] = round( $this->calc_order_shipping( $d ), 2 );

		/* sets _shipping_tax
		* btw: This is WEIRD! To get an exactly rounded value we have to convert
		* the amount to a String and call "round" with the string. */
		$d['order_shipping_tax'] = $totals['order_shipping_tax'] = round( strval($this->calc_order_shipping_tax($d)), 2 );

		$d['order_total'] = $totals['order_total'] = 	$tmp_subtotal 
											+ $totals['order_tax']
											+ $totals['order_shipping']
											+ $totals['order_shipping_tax']
											- $totals['coupon_discount']
											- $totals['payment_discount'];
		
		$totals['order_tax'] *= $discount_factor;
		
		return $totals;
	}
	/**
         * Generates the md5 hash of the recent cart / checkout constellation
         *
         * @return unknown
         */
	function get_new_cart_hash() {

		return md5( print_r( $_SESSION['cart'], true)
		. JRequest::getVar('shipping_rate_id')
		. JRequest::getVar('payment_method_id')
		);

	}

	/**
         * Returns the recent subtotal
         *
         * @param array $d
         * @return float The current order subtotal
         */
	function get_order_subtotal( &$d ) {

		if( $this->_subtotal === null ) {
			$this->_subtotal = $this->calc_order_subtotal( $d );
		}
		else {
			if( $this->_cartHash != $this->get_new_cart_hash() ) {
				// Need to re-calculate the subtotal
				$this->_subtotal = $this->calc_order_subtotal( $d );
			}
		}
		return $this->_subtotal;
	}

	/**************************************************************************
	** name: calc_order_subtotal()
	** created by: gday
	** description:  Calculate the order subtotal for the current order.
	**               Does not include tax or shipping charges.
	** parameters: $d
	** returns: sub total for this order
	***************************************************************************/
	function calc_order_subtotal( &$d ) {
		global $order_tax_details;
		
		$order_tax_details = array();
		$d['order_subtotal_withtax'] = 0;
		$d['payment_discount'] = 0;
		$auth = $_SESSION['auth'];
		$cart = $_SESSION['cart'];
		$order_subtotal = 0;

		require_once(CLASSPATH.'ps_product.php');
		$ps_product= new ps_product;

		for($i = 0; $i < $cart["idx"]; $i++) {
			$my_taxrate = $ps_product->get_product_taxrate($cart[$i]["product_id"] );
			$price = $ps_product->get_adjusted_attribute_price($cart[$i]["product_id"], $cart[$i]["description"]);
			$product_price = $product_price_tmp = $GLOBALS['CURRENCY']->convert( $price["product_price"], @$price["product_currency"] );
			
			if( $auth["show_price_including_tax"] == 1 ) {
				$product_price = round( ($product_price *($my_taxrate+1)), 2 );
				$product_price *= $cart[$i]["quantity"];
				
				$d['order_subtotal_withtax'] += $product_price;
				$product_price = $product_price /($my_taxrate+1);
				$order_subtotal += $product_price;
				
			}
			else {
				$order_subtotal += $product_price * $cart[$i]["quantity"];
				
				$product_price = round( ($product_price *($my_taxrate+1)), 2 );
				$product_price *= $cart[$i]["quantity"];
				$d['order_subtotal_withtax'] += $product_price;
				$product_price = $product_price /($my_taxrate+1);
			}
			if( MULTIPLE_TAXRATES_ENABLE ) {
				// Calculate the amounts for each tax rate
				if( !isset( $order_tax_details[$my_taxrate] )) {
					$order_tax_details[$my_taxrate] = 0;
				}
				$order_tax_details[$my_taxrate] += $product_price_tmp*$my_taxrate*$cart[$i]["quantity"];
			}
		}

		return($order_subtotal);
	}


	/**
	 * Calculates the taxable order subtotal for the order.
	 * If an item has no weight, it is non taxable.
	 * @author Chris Coleman
	 * @param array $d
	 * @return float Subtotal
	 */
	function calc_order_taxable($d) {
		$auth = $_SESSION['auth'];
		$cart = $_SESSION['cart'];

		$subtotal = 0.0;
		
		require_once(CLASSPATH.'ps_product.php');
		$ps_product= new ps_product;
		require_once(CLASSPATH.'shippingMethod.class.php');

		$db = new ps_DB;

		for($i = 0; $i < $cart["idx"]; $i++) {
			$price = $ps_product->get_adjusted_attribute_price($cart[$i]["product_id"], $cart[$i]["description"]);
			$product_price = $GLOBALS['CURRENCY']->convert( $price["product_price"], $price['product_currency'] );
			$item_weight = vmShippingMethod::get_weight($cart[$i]["product_id"]) * $cart[$i]['quantity'];

			if ($item_weight != 0 or TAX_VIRTUAL=='1') {
				$subtotal += $product_price * $cart[$i]["quantity"];
			}
		}
		return($subtotal);
	}
	
	/**
	 * Calculate the tax charges for the current order.
	 * You can switch the way, taxes are calculated:
	 * either based on the VENDOR address,
	 * or based on the ship-to address.
	 * ! Creates the global $order_tax_details
	 *
	 * @param float $order_taxable
	 * @param array $d
	 * @return float
	 */
	function calc_order_tax($order_taxable, $d) {
		global $vm_mainframe, $order_tax_details, $discount_factor;
		$auth = $_SESSION['auth'];
		
		$cart = $_SESSION['cart'];
		$hVendor_id = $cart['cart_vendor_id'];
		
		$db = new ps_DB;
		$ship_to_info_id = JRequest::getVar( 'ship_to_info_id');
		
		
		require_once(CLASSPATH.'ps_tax.php');
		$ps_tax = new ps_tax;
		
		$discount_factor = 1;
		
		// Shipping address based TAX
		if ( !ps_checkout::tax_based_on_vendor_address () ) {
			$q = "SELECT state, country FROM #__{vm}_user_info ";
			$q .= "WHERE user_info_id='".$ship_to_info_id. "'";
			$db->query($q);
			$db->next_record();
			$state = $db->f("state");
			$country = $db->f("country");
			$q = "SELECT * FROM #__{vm}_tax_rate WHERE tax_country='$country' ";
			if( !empty($state)) {
				$q .= "AND (tax_state='$state' OR tax_state=' $state ')";
			}
			$db->query($q);
			if ($db->next_record()) {
				$rate = $order_taxable * floatval( $db->f("tax_rate") );
				if (empty($rate)) {
					$order_tax = 0.0;
				}
				else {
					$order_tax = $rate;
				}
			}
			else {
				$order_tax = 0.0;
			}
			$order_tax_details[$db->f('tax_rate')] = $order_tax;
		}
		// Store Owner Address based TAX
		else {

				// Calculate the Tax with a tax rate for every product
				$cart = $_SESSION['cart'];
				$order_tax = 0.0;
				$total = 0.0;
				if( (!empty( $_SESSION['coupon_discount'] ) || !empty( $d['payment_discount'] ))
					&& PAYMENT_DISCOUNT_BEFORE == '1' ) {
					// We need to recalculate the tax details when the discounts are applied
					// BEFORE taxes - because they affect the product subtotals then
					$order_tax_details = array();
				}
				require_once(CLASSPATH.'ps_product.php');
				$ps_product= new ps_product;
				require_once(CLASSPATH.'shippingMethod.class.php');

				for($i = 0; $i < $cart["idx"]; $i++) {
					$item_weight = vmShippingMethod::get_weight($cart[$i]["product_id"]) * $cart[$i]['quantity'];

					if ($item_weight !=0 or TAX_VIRTUAL) {
						$price = $ps_product->get_adjusted_attribute_price($cart[$i]["product_id"], $cart[$i]["description"]);
						$price['product_price'] = $GLOBALS['CURRENCY']->convert( $price['product_price'], $price['product_currency']);
						$tax_rate = $ps_product->get_product_taxrate($cart[$i]["product_id"]);
						
						if( (!empty( $_SESSION['coupon_discount'] ) || !empty( $d['payment_discount'] ))
							&& PAYMENT_DISCOUNT_BEFORE == '1' ) {
							$use_coupon_discount= @$_SESSION['coupon_discount'];
							if( !empty( $_SESSION['coupon_discount'] )) {
								if( $auth["show_price_including_tax"] == 1 ) {
									$use_coupon_discount = $_SESSION['coupon_discount'] / ($tax_rate+1);
								}
							}
							$factor = (100 * ($use_coupon_discount + @$d['payment_discount'])) / $this->_subtotal;
							$price["product_price"] = $price["product_price"] - ($factor * $price["product_price"] / 100);
							@$order_tax_details[$tax_rate] += $price["product_price"] * $tax_rate * $cart[$i]["quantity"];
						}
						
						$order_tax += $price["product_price"] * $tax_rate * $cart[$i]["quantity"];
						$total += $price["product_price"] * $cart[$i]["quantity"];
					}
				}

				if( (!empty( $_SESSION['coupon_discount'] ) || !empty( $d['payment_discount'] ))
					&& PAYMENT_DISCOUNT_BEFORE != '1' ) {
						
					// Here we need to re-calculate the Discount
					// because we assume the Discount is "including Tax"
					$discounted_total = @$d['order_subtotal_withtax'] - @$_SESSION['coupon_discount'] - @$d['payment_discount'];
					
					if( $discounted_total != @$d['order_subtotal_withtax'] && @$d['order_subtotal_withtax'] > 0.00) {
						$discount_factor = $discounted_total / $d['order_subtotal_withtax'];
						
						foreach( $order_tax_details as $rate => $value ) {
							$order_tax_details[$rate] = $value * $discount_factor;
						}
					}
					
				}
				$result = $vm_mainframe->triggerEvent('get_shippingtax_rate');
				
				$taxrate = is_array($result) ? @$result[0] : '';
				if( $taxrate ) {
					$result = $vm_mainframe->triggerEvent('get_shipping_rate', array($d));
					$rate = is_array($result) ? $result[0] : '';
					if( $auth["show_price_including_tax"] == 1 ) {
						@$order_tax_details[$taxrate] += $rate - ($rate / ($taxrate+1));
					}
					else {
						@$order_tax_details[$taxrate] += $rate * $taxrate;
					}
				}

		}
		return( round( $order_tax, 2 ) );
	}
  
	/**************************************************************************
	** name: calc_order_shipping()
	** created by: soeren
	** description:  Get the Shipping costs WITHOUT TAX
	** parameters: $d,
	** returns: a decimal number, excluding taxes
	***************************************************************************/
	function calc_order_shipping( &$d ) {
		global $vm_mainframe;
		$auth = $_SESSION['auth'];

		$result = $vm_mainframe->triggerEvent('get_shipping_rate', array( $d ));		
		$shipping_total = is_array($result) ? $result[0] : 0.00;
		
		$result = $vm_mainframe->triggerEvent('get_shippingtax_rate');
		$shipping_taxrate = is_array($result) ? $result[0] : 0.00;
		// When the Shipping rate is shown including Tax
		// we have to extract the Tax from the Shipping Total
		// before returning the value
		if( $auth["show_price_including_tax"] == 1 ) {
			$d['shipping_tax'] = $shipping_total - ($shipping_total / ($shipping_taxrate+1));
			$d['shipping_total'] = $shipping_total - $d['shipping_tax'];
		}
		else {
			$d['shipping_tax'] = $shipping_total * $shipping_taxrate;
			$d['shipping_total'] = $shipping_total;
		}
		$d['shipping_tax'] = $GLOBALS['CURRENCY']->convert( $d['shipping_tax'] );
		$d['shipping_total'] = $GLOBALS['CURRENCY']->convert( $d['shipping_total'] );
		
		return $d['shipping_total'];
	}




	/**************************************************************************
	** name: calc_order_shipping_tax()
	** created by: Soeren
	** description:  Calculate the tax for the shipping of the current order
	** Assumes that the function calc_order_shipping has been called before
	** parameters: $d
	** returns: Tax for the shipping of this order
	***************************************************************************/
	function calc_order_shipping_tax($d) {

		return $d['shipping_tax'];

	}

	/**************************************************************************
	** name: get_vendor_currency()
	** created by: gday
	** description:  Get the currency type used by the $vendor_id
	** parameters: $vendor_id - vendor id to return currency type
	** returns: Currency type for this vendor
	***************************************************************************/
	function get_vendor_currency($vendor_id) {
	
		//by Max Milbers
		$db = ps_vendor::get_vendor_fields($vendor_id,array("vendor_currency"),"");
		$currency = $db->f("vendor_currency");

		return($currency);
	}


	/**************************************************************************
	** name: get_payment_discount()
	** created by: soeren
	** description:  Get the discount for the selected payment
	** parameters: $payment_method_id
	** returns: Discount as a decimal if found
	**          0 if nothing is found
	***************************************************************************/
	function get_payment_discount( $payment_method_id, $subtotal = '' ) {
		global $vm_mainframe;
		if( empty( $payment_method_id )) {
			return 0;
		}
		$db = new ps_DB();
		//MOD ei
		// There is a special payment method, which fee is depend on subtotal
		// it is a type of cash on delivery
		// comment soeren: Payment methods can implement their own method
		// how to calculate the discount: the function "get_payment_rate"
		// should return a float value from the payment class
		require_once(CLASSPATH.'paymentMethod.class.php');
		if( vmPaymentMethod::importPaymentPluginById($payment_method_id) ) {
			$result = $vm_mainframe->triggerEvent('get_payment_rate', array($subtotal));
			if( !empty($result) && $result[0] !== false ) {
				return (double)$result[0];
			}
		}

		// If a payment method has no special way of calculating a discount,
		// let's do this on our own from the payment_method_discount settings
		$q = 'SELECT `discount`,`discount_is_percentage`,`discount_max_amount`, `discount_min_amount`
                                FROM `#__{vm}_payment_method` WHERE payment_method_id='.$payment_method_id;
		$db->query($q);$db->next_record();

		$discount = $db->f('discount');
		$is_percent = $db->f('discount_is_percentage');

		if( !$is_percent ) {
			// Standard method: absolute amount
			if (!empty($discount)) {
				return(floatval( $GLOBALS['CURRENCY']->convert($discount)));
			}
			else {
				return(0);
			}
		}
		else {

			if( $subtotal === '') {
				$subtotal = $this->get_order_subtotal( $vars );
			}

			// New: percentage of the subtotal, limited by minimum and maximum
			$max = $db->f('discount_max_amount');
			$min = $db->f('discount_min_amount');
			$value = (float) ($discount/100) * $subtotal;

			if( abs($value) > $max && $max > 0 ) {
				$value = -$max;
			}
			elseif( abs($value) < $min && $min > 0 ) {
				$value = -$min;
			}
			return $value;
		}

	}

	
	/**
    * Create a receipt for the current order and email it to
    * the customer and the vendor.
    * @author gday
    * @author soeren
    * @param int $order_id
    * @return boolean True on success, false on failure
    */
	function email_receipt($order_id) {
		global $sess, $ps_product, $CURRENCY_DISPLAY, $vmLogger,
		$mosConfig_fromname, $hVendor;

		//by Max Milbers takes the vendor of the cart
		$cart = $_SESSION['cart'];

		$vendor_id = $cart['cart_vendor_id'];
		$auth = $_SESSION["auth"];

		require_once( CLASSPATH.'ps_order_status.php');
		require_once( CLASSPATH.'ps_userfield.php');
		require_once(CLASSPATH.'ps_product.php');
		$ps_product = new ps_product;

		// Connect to database and gather appropriate order information
		$db = new ps_DB;
		$q  = "SELECT * FROM #__{vm}_orders WHERE order_id='$order_id'";
		$db->query($q);
		$db->next_record();
		$user_id = $db->f("user_id");
		$customer_note = $db->f("customer_note");
		$order_status = ps_order_status::getOrderStatusName($db->f("order_status") );

		$dbbt = new ps_DB;
		$dbst = new ps_DB;

		//Changed by Max Milbers merging #__{vm}_user_info.user_email to #__users.email
//		$qt = "SELECT * FROM #__{vm}_user_info WHERE user_id='".$user_id."' AND address_type='BT' ";
//		$qt = "SELECT * FROM #__{vm}_user_info u ";
//		$qt .= "LEFT JOIN #__users ju ON (ju.id = u.user_id) ";
//		$qt .= "WHERE user_id='".$user_id."' AND address_type='BT' ";
//		$dbbt->query($qt);
//		$dbbt->next_record();
		require_once( CLASSPATH . 'ps_user.php' );
		$dbbt = ps_user::get_user_details($user_id,"","","AND u.address_type='BT' ");
		
//		$dbst = ps_user::get_user_details($db->f("user_info_id"),"","");
		
		$qt = "SELECT * FROM #__{vm}_user_info WHERE user_info_id='". $db->f("user_info_id") . "'";
		$dbst->query($qt);
		$dbst->next_record();
//		$dbst = ps_user::get_user_details($db->f("user_info_id"));

		$dbv = $hVendor->get_vendor_details($vendor_id);
		if(empty($dbv)){
			$GLOBALS['vmLogger']->err( "Sending Confirmation email: Failure in Database no user_id for vendor_id ".$vendor_id." found" );
			return false;
		}
		$dboi = new ps_DB;
		$q_oi = "SELECT * FROM #__{vm}_product, #__{vm}_order_item, #__{vm}_orders ";
		$q_oi .= "WHERE #__{vm}_product.product_id=#__{vm}_order_item.product_id ";
		$q_oi .= "AND #__{vm}_order_item.order_id='$order_id' ";
		$q_oi .= "AND #__{vm}_orders.order_id=#__{vm}_order_item.order_id";
		$dboi->query($q_oi);

		$db_payment = new ps_DB;
		$q  = "SELECT op.payment_method_id, pm.name FROM #__{vm}_order_payment as op, #__{vm}_payment_method as pm
              WHERE order_id='$order_id' AND op.payment_method_id=pm.payment_method_id";
		$db_payment->query($q);
		$db_payment->next_record();

		if ($auth["show_price_including_tax"] == 1) {

			$order_shipping = $db->f("order_shipping");
			$order_shipping += $db->f("order_shipping_tax");
			$order_shipping_tax = 0;
			$order_tax = $db->f("order_tax") + $db->f("order_shipping_tax");
		}
		else {

			$order_shipping = $db->f("order_shipping");
			$order_shipping_tax = $db->f("order_shipping_tax");
			$order_tax = $db->f("order_tax");
		}
		$order_total = $db->f("order_total");
		$order_discount = $db->f("order_discount");
		$coupon_discount = $db->f("coupon_discount");

		// Email Addresses for shopper and vendor
		// **************************************
		$shopper_email = $dbbt->f("email");
		$shopper_name = $dbbt->f("first_name")." ".$dbbt->f("last_name");

		$from_email = $dbv->f("email");
		$shopper_subject = $dbv->f("vendor_name") . " ".JText::_('VM_ORDER_PRINT_PO_LBL',false)." - " . $db->f("order_id");
		$vendor_subject = $dbv->f("vendor_name") . " ".JText::_('VM_ORDER_PRINT_PO_LBL',false)." - " . $db->f("order_id");

		$GLOBALS['vmLogger']->debug('$vendor_subject '.$vendor_subject);
		$GLOBALS['vmLogger']->debug('$from_email '.$from_email);
		$shopper_order_link = $sess->url( SECUREURL ."index.php?page=account.order_details&order_id=$order_id", true, false );
		$vendor_order_link = $sess->url( SECUREURL ."index2.php?page=order.order_print&order_id=$order_id&pshop_mode=admin", true, false );

		/**
		 * Prepare the payment information, including Credit Card information when not empty
		 */
		$payment_info_details = $db_payment->f("name");
		if( !empty( $_SESSION['ccdata']['order_payment_name'] )
			&& !empty($_SESSION['ccdata']['order_payment_number'])) {
	  		$payment_info_details .= '<br />'.JText::_('VM_CHECKOUT_CONF_PAYINFO_NAMECARD',false).': '.$_SESSION['ccdata']['order_payment_name'].'<br />';
	  		$payment_info_details .= JText::_('VM_CHECKOUT_CONF_PAYINFO_CCNUM',false).': '.$this->asterisk_pad($_SESSION['ccdata']['order_payment_number'], 4 ).'<br />';
	  		$payment_info_details .= JText::_('VM_CHECKOUT_CONF_PAYINFO_EXDATE',false).': '.$_SESSION['ccdata']['order_payment_expire_month'].' / '.$_SESSION['ccdata']['order_payment_expire_year'].'<br />';
	  		if( !empty($_SESSION['ccdata']['credit_card_code'])) {
	  			$payment_info_details .= 'CVV code: '.$_SESSION['ccdata']['credit_card_code'].'<br />';
	  		}
		}
		// Convert HTML into Text
		$payment_info_details_text = str_replace( '<br />', "\n", $payment_info_details );
		
		// Get the Shipping Details
		$shipping_arr = explode("|", urldecode(JRequest::getVar("shipping_rate_id")) );
		
		// Headers and Footers
		// ******************************
		// Shopper Header
		$shopper_header = JText::_('VM_CHECKOUT_EMAIL_SHOPPER_HEADER1',false)."\n";
		
		$legal_info_title = '';
		$legal_info_html = '';
		// Get the legal information about the returns/order cancellation policy
		if( @VM_ONCHECKOUT_SHOW_LEGALINFO == '1' ) {
			$article = intval(@VM_ONCHECKOUT_LEGALINFO_LINK);
			if( $article > 0 ) {
				$db_legal = new ps_DB();
				// Get the content article, which contains the Legal Info
				$db_legal->query( 'SELECT id, title, introtext FROM #__content WHERE id='.$article );
				$db_legal->next_record();
				if( $db_legal->f('introtext') ) {
					$legal_info_title = $db_legal->f('title');
					$legal_info_text = strip_tags( str_replace( '<br />', "\n", $db_legal->f('introtext') ));
					$legal_info_html = $db_legal->f('introtext');
				}
			}
		}
		//Shopper Footer
		$shopper_footer = "\n\n".JText::_('VM_CHECKOUT_EMAIL_SHOPPER_HEADER2',false)."\n";
		if( VM_REGISTRATION_TYPE != 'NO_REGISTRATION' ) {
			$shopper_footer .= "\n\n".JText::_('VM_CHECKOUT_EMAIL_SHOPPER_HEADER5',false)."\n";
			$shopper_footer .= $shopper_order_link;
		}
		$shopper_footer .= "\n\n".JText::_('VM_CHECKOUT_EMAIL_SHOPPER_HEADER3',false)."\n";
		$shopper_footer .= "Email: " . $from_email;
		// New in version 1.0.5
		if( @VM_ONCHECKOUT_SHOW_LEGALINFO == '1' && !empty( $legal_info_title )) {
			$shopper_footer .= "\n\n____________________________________________\n";
			$shopper_footer .= $legal_info_title."\n";
			$shopper_footer .= $legal_info_text."\n";
		}
		
		// Vendor Header
		$vendor_header = JText::_('VM_CHECKOUT_EMAIL_SHOPPER_HEADER4',false)."\n";

		// Vendor Footer
		$vendor_footer = "\n\n".JText::_('VM_CHECKOUT_EMAIL_SHOPPER_HEADER5',false)."\n";
		$vendor_footer .= $vendor_order_link;

		$vendor_email = $from_email;

		/////////////////////////////////////
		// set up text mail
		//

		// Main Email Message Purchase Order
		// *********************************
		$shopper_message  = "\n".JText::_('VM_ORDER_PRINT_PO_LBL',false)."\n";
		$shopper_message .= "------------------------------------------------------------------------\n";
		$shopper_message .= JText::_('VM_ORDER_PRINT_PO_NUMBER',false).": " . $db->f("order_id") . "\n";
		$shopper_message .= JText::_('VM_ORDER_PRINT_PO_DATE',false).":   ";
		$shopper_message .= strftime( JText::_('DATE_FORMAT_LC'), $db->f("cdate") ) . "\n";
		$shopper_message .= JText::_('VM_ORDER_PRINT_PO_STATUS',false).": ";
				
		$shopper_message .= $order_status."\n\n";
				
		// BillTo Fields		
		$registrationfields = ps_userfield::getUserFields('registration', false, '', false, true );
		foreach( $registrationfields as $field ) {
			if( $field->name == 'email') $field->name = 'email';
			if( $field->name == 'delimiter_sendregistration' || $field->type == 'captcha') continue;
			
			if( $field->type == 'delimiter') {
				$shopper_message .= (JText::_($field->title) != '' ? JText::_($field->title) : $field->title)."\n";
				$shopper_message .= "--------------------\n\n";
			} else {
				$shopper_message .= (JText::_($field->title) != '' ? JText::_($field->title) : $field->title).':    ';
				$shopper_message .= $dbst->f($field->name) . "\n";
			}
		}
		
		// Shipping Fields
		$shopper_message .= "\n\n";
		$shopper_message .= JText::_('VM_ORDER_PRINT_SHIP_TO_LBL')."\n";
		$shopper_message .= "-------\n\n";
		
		$shippingfields = ps_userfield::getUserFields('shipping', false, '', false, true );
		foreach( $shippingfields as $field ) {			
			
			if( $field->type == 'delimiter') {
				$shopper_message .= (JText::_($field->title) != '' ? JText::_($field->title) : $field->title)."\n";
				$shopper_message .= "--------------------\n\n";
			} else {
				$shopper_message .= (JText::_($field->title) != '' ? JText::_($field->title) : $field->title).':    ';
				$shopper_message .= $dbst->f($field->name) . "\n";
			}
		}
		
		$shopper_message .= "\n\n";

		$shopper_message .= JText::_('VM_ORDER_PRINT_ITEMS_LBL',false)."\n";
		$shopper_message .= "-----------";
		$sub_total = 0.00;
		while($dboi->next_record()) {
			$shopper_message .= "\n\n";
			$shopper_message .= JText::_('VM_PRODUCT',false)."  = ";
			if ($dboi->f("product_parent_id")) {
				$shopper_message .= $dboi->f("order_item_name") . "\n";
				$shopper_message .= "SERVICE  = ";
			}
			$shopper_message .= $dboi->f("product_name") . "; ".$dboi->f("product_attribute") ."\n";
			$shopper_message .= JText::_('VM_ORDER_PRINT_QUANTITY',false)." = ";
			$shopper_message .= $dboi->f("product_quantity") . "\n";
			$shopper_message .= JText::_('VM_ORDER_PRINT_SKU',false)."      = ";
			$shopper_message .= $dboi->f("order_item_sku") . "\n";

			$shopper_message .= JText::_('VM_ORDER_PRINT_PRICE',false)."    = ";
			if ($auth["show_price_including_tax"] == 1) {
				$sub_total += ($dboi->f("product_quantity") * $dboi->f("product_final_price"));
				$shopper_message .= $CURRENCY_DISPLAY->getFullValue($dboi->f("product_final_price"), '', $db->f('order_currency'));
			} else {
				$sub_total += ($dboi->f("product_quantity") * $dboi->f("product_final_price"));
				$shopper_message .= $CURRENCY_DISPLAY->getFullValue($dboi->f("product_item_price"), '', $db->f('order_currency'));
			}
		}

		$shopper_message .= "\n\n";

		$shopper_message .= JText::_('VM_ORDER_PRINT_SUBTOTAL',false)." = ";
		$shopper_message .= $CURRENCY_DISPLAY->getFullValue($sub_total, '', $db->f('order_currency'))."\n";

		if ( PAYMENT_DISCOUNT_BEFORE == '1') {
			if( !empty($order_discount)) {
				if ($order_discount > 0) {
					$shopper_message .= JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT',false)." = ";
					$shopper_message .= "- ".$CURRENCY_DISPLAY->getFullValue(abs($order_discount), '', $db->f('order_currency')) . "\n";
				} else {
					$shopper_message .= JText::_('VM_FEE',false)." = ";
					$shopper_message .= "+ ".$CURRENCY_DISPLAY->getFullValue(abs($order_discount), '', $db->f('order_currency')) . "\n";
				}
			}
			if( !empty($coupon_discount)) {
				/* following 2 lines added by Erich for coupon hack */
				$shopper_message .= JText::_('VM_COUPON_DISCOUNT',false) . ": ";
				$shopper_message .= $CURRENCY_DISPLAY->getFullValue($coupon_discount, '', $db->f('order_currency')) . "\n";
			}
		}

		if ($auth["show_price_including_tax"] != 1) {
			$shopper_message .= JText::_('VM_ORDER_PRINT_TOTAL_TAX',false)."      = ";
			$shopper_message .= $CURRENCY_DISPLAY->getFullValue($order_tax, '', $db->f('order_currency')) . "\n";
		}
		$shopper_message .= JText::_('VM_ORDER_PRINT_SHIPPING',false)." = ";
		$shopper_message .= $CURRENCY_DISPLAY->getFullValue($order_shipping, '', $db->f('order_currency')) . "\n";
		if( !empty($order_shipping_tax)) {
			$shopper_message .= JText::_('VM_ORDER_PRINT_SHIPPING_TAX',false)."   = ";
			$shopper_message .= $CURRENCY_DISPLAY->getFullValue($order_shipping_tax, '', $db->f('order_currency'));
		}
		$shopper_message .= "\n\n";
		if ( PAYMENT_DISCOUNT_BEFORE != '1') {
			if( !empty($order_discount)) {
				if ($order_discount > 0) {
					$shopper_message .= JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT',false)." = ";
					$shopper_message .= "- ".$CURRENCY_DISPLAY->getFullValue(abs($order_discount), '', $db->f('order_currency')) . "\n";
				} else {
					$shopper_message .= JText::_('VM_FEE',false)." = ";
					$shopper_message .= "+ ".$CURRENCY_DISPLAY->getFullValue(abs($order_discount), '', $db->f('order_currency')) . "\n";
				}
			}
			if( !empty($coupon_discount)) {
				/* following 2 lines added by Erich for coupon hack */
				$shopper_message .= JText::_('VM_COUPON_DISCOUNT',false) . ": ";
				$shopper_message .= $CURRENCY_DISPLAY->getFullValue($coupon_discount, '', $db->f('order_currency')) . "\n";
			}
		}
		$shopper_message .= JText::_('VM_ORDER_PRINT_TOTAL',false)."    = ";
		$shopper_message .= $CURRENCY_DISPLAY->getFullValue($order_total, '', $db->f('order_currency'));

		if ($auth["show_price_including_tax"] == 1) {
			$shopper_message .= "\n---------------";
			$shopper_message .= "\n";
			$shopper_message .= JText::_('VM_ORDER_PRINT_TOTAL_TAX',false)."      = ";
			$shopper_message .= $CURRENCY_DISPLAY->getFullValue($order_tax, '', $db->f('order_currency')) . "\n";
		}
		if( $db->f('order_tax_details') ) {
			$shopper_message .= str_replace( '<br />', "\n", ps_checkout::show_tax_details( $db->f('order_tax_details'), $db->f('order_currency') ));
		}
		// Payment Details
		$shopper_message .= "\n\n------------------------------------------------------------------------\n";
		$shopper_message .= $payment_info_details_text;
		
		// Shipping Details
		if( !empty($shipping_arr[1]) && !empty($shipping_arr[2]) ) {
			$shopper_message .= "\n\n------------------------------------------------------------------------\n";
			$shopper_message .= JText::_('VM_ORDER_PRINT_SHIPPING_LBL',false).":\n";
			$shopper_message .= $shipping_arr[1]." (".$shipping_arr[2].")";
		}
		// Customer Note
		$shopper_message .= "\n\n------------------------------------------------------------------------\n";
		$shopper_message .= "\n".JText::_('VM_ORDER_PRINT_CUSTOMER_NOTE',false)."\n";
		$shopper_message .= "---------------";
		$shopper_message .= "\n";
		if( !empty( $customer_note )) {
			$shopper_message .= $customer_note."\n";
		}
		else {
			$shopper_message .= " ./. \n";
		}
		$shopper_message .= "------------------------------------------------------------------------\n";
		
		// Decode things like &euro; => €
		$shopper_message = vmHtmlEntityDecode( $shopper_message );
		
		// End of Purchase Order
		// *********************

		//
		//END: set up text mail
		/////////////////////////////////////
		// Send text email
		//
		if (ORDER_MAIL_HTML == '0') {

			$msg = $shopper_header . $shopper_message . $shopper_footer;

			// Mail receipt to the shopper
			vmMail( $from_email, $mosConfig_fromname, $shopper_email, $shopper_subject, $msg, "" );

			$msg = $vendor_header . $shopper_message . $vendor_footer;

			// Mail receipt to the vendor
			vmMail($from_email, $mosConfig_fromname, $vendor_email, $vendor_subject,	$msg, "" );

		}

		////////////////////////////
		// set up the HTML email
		//
		elseif (ORDER_MAIL_HTML == '1') {

			$dboi->query($q_oi);

			// Create Template Object 
			$template = vmTemplate::getInstance();
			
			if ($order_discount > 0) {
				$order_discount_lbl = JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
				$order_discount_plusminus = '-';
			} else {
				$order_discount_lbl = JText::_('VM_FEE');
				$order_discount_plusminus = '+';
			}
			if ($coupon_discount > 0) {
				$coupon_discount_lbl = JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
				$coupon_discount_plusminus = '-';
			} else {
				$coupon_discount_lbl = JText::_('VM_FEE');
				$coupon_discount_plusminus = '+';
			}

			if( !empty($shipping_arr[1]) && !empty($shipping_arr[2]) ) {
				$shipping_info_details = stripslashes($shipping_arr[1])." (".stripslashes($shipping_arr[2]).")";
			}
			else {
				$shipping_info_details = ' ./. ';
			}
			// These are a lot of vars to import for the email confirmation
			$template->set_vars(array(
						'is_email_to_shopper' => true,
						'db' => $db,
						'dboi' => $dboi,
						'dbbt' => $dbbt,
						'dbst' => $dbst,
						'ps_product' => $ps_product,
						'shippingfields' => $shippingfields,
						'registrationfields' => $registrationfields,
						'order_id' => $order_id,
						'order_discount' => $order_discount,
						'order_discount_lbl' => $order_discount_lbl,
						'order_discount_plusminus' => $order_discount_plusminus,			
						'coupon_discount' => $coupon_discount,
						'coupon_discount_lbl' => $coupon_discount_lbl,
						'coupon_discount_plusminus' => $coupon_discount_plusminus,
						'order_date' => vmFormatDate($db->f("cdate"), JText::_('DATE_FORMAT_LC') ),
						'order_status' => $order_status,
						'legal_info_title' => $legal_info_title,
						'legal_info_html' => $legal_info_html,
						'order_link' => $shopper_order_link,

						'payment_info_lbl' => JText::_('VM_ORDER_PRINT_PAYINFO_LBL'),
						'payment_info_details' => $payment_info_details,
						'shipping_info_lbl' => JText::_('VM_ORDER_PRINT_SHIPPING_LBL'),
						'shipping_info_details' => $shipping_info_details,

						'from_email' => $from_email,
						'customer_note' => nl2br($customer_note),
						'order_header_msg' => $shopper_header,

						'order_subtotal' => $CURRENCY_DISPLAY->getFullValue($sub_total, '', $db->f('order_currency')),
						'order_shipping' => $CURRENCY_DISPLAY->getFullValue($order_shipping, '', $db->f('order_currency')),
						'order_tax' => $CURRENCY_DISPLAY->getFullValue($order_tax, '', $db->f('order_currency')). ps_checkout::show_tax_details( $db->f('order_tax_details'), $db->f('order_currency') ),
						'order_total' => $CURRENCY_DISPLAY->getFullValue($order_total, '', $db->f('order_currency')),
			
						));
			$shopper_html = $template->fetch('order_emails/confirmation_email.tpl.php');
			
			// Reset the list of order items for use in the vendor email
			$dboi->reset();
			
			// Override some vars for the vendor email, so we can use the same template
			$template->set_vars(array(
														'order_header_msg' => $vendor_header,
														'order_link' => $vendor_order_link,
														'is_email_to_shopper' => false
											));
											
			$vendor_html = $template->fetch('order_emails/confirmation_email.tpl.php');


			/*
			* Add the text, html and embedded images.
			* The name of the image should match exactly
			* (case-sensitive) to the name in the html.
			*/
			$shopper_mail_Body = $shopper_html;
			$shopper_mail_AltBody = $shopper_header . $shopper_message . $shopper_footer;

			$vendor_mail_Body = $vendor_html;
			$vendor_mail_AltBody = $vendor_header . $shopper_message . $vendor_footer;

			$imagefile = pathinfo($dbv->f("vendor_full_image"));
			$extension = $imagefile['extension'] == "jpg" ? "jpeg" : "jpeg";

			$EmbeddedImages[] = array(	'path' => IMAGEPATH."vendor/".$dbv->f("vendor_full_image"),
								'name' => "vendor_image", 
								'filename' => $dbv->f("vendor_full_image"),
								'encoding' => "base64",
								'mimetype' => "image/".$extension );

			
			$shopper_mail = vmMail( $from_email, $mosConfig_fromname, $shopper_email, $shopper_subject, $shopper_mail_Body, $shopper_mail_AltBody, true, null, null, $EmbeddedImages);

			$vendor_mail = vmMail( $shopper_email, $shopper_name, $vendor_email, $vendor_subject, $vendor_mail_Body, $vendor_mail_AltBody, true, null, null, $EmbeddedImages);

			if ( !$shopper_mail || !$vendor_mail ) {
				
				$vmLogger->debug( 'Something went wrong while sending the order confirmation email to '.$from_email.' and '.$shopper_email );
				return false;
			}
			//
			// END: set up and send the HTML email
			////////////////////////////////////////
		}

		return true;

	} // end of function email_receipt()



	/**
	 * Return $str with all but $display_length at the end as asterisks.
	 * @author gday
	 *
	 * @param string $str The string to mask
	 * @param int $display_length The length at the end of the string that is NOT masked
	 * @param boolean $reversed When true, masks the end. Masks from the beginning at default
	 * @return string The string masked by asteriks
	 */
	function asterisk_pad($str, $display_length, $reversed = false) {

		$total_length = strlen($str);

		if($total_length > $display_length) {
			if( !$reversed) {
				for($i = 0; $i < $total_length - $display_length; $i++) {
					$str[$i] = "*";
				}
			}
			else {
				for($i = $total_length-1; $i >= $total_length - $display_length; $i--) {
					$str[$i] = "*";
				}
			}
		}

		return($str);
	}

	/**
	 * Displays the order_tax_details array when it contains
	 * more than one 
	 * @param mixed $details
	 * @return string
	 */
	function show_tax_details( $details, $currency = ''  ) {
		global $discount_factor, $CURRENCY_DISPLAY;
		
		if( !isset( $discount_factor) || !empty($_REQUEST['discount_factor'])) {
			$discount_factor = 1;
		}
		$auth = $_SESSION['auth'];
		if( !is_array( $details )) {
			$details = @unserialize( $details );
			if( !is_array($details)) {
				return false;
			}
		}
		$html = '';
		if( sizeof( $details) > 1 ) {
			$html .= '<br />'.JText::_('VM_TAXDETAILS_LABEL').':<br />';
			
			foreach ($details as $rate => $value ) {
				if( !$auth['show_price_including_tax']) {
					$value /= $discount_factor;
				}
				$rate = str_replace( '-', $CURRENCY_DISPLAY->decimal, $rate )*100;
				$html .= $CURRENCY_DISPLAY->getFullValue( $value, 5, $currency ).' ('.$rate.'% '.JText::_('VM_CART_TAX').')<br />';
			}
		}
		return $html;
	}
	
	/*
	* @abstract This function is very useful to round totals with definite decimals.
	*
	* @param float   $value
	* @param integer $dec
	* @return float
	*/
	function approx( $value, $dec = 2 ) {
		$value += 0.0;
		$unit  = floor( $value * pow( 10, $dec + 1 ) ) / 10;
		$round = round( $unit );
		return $round / pow( 10, $dec );
	}



	/**
	 * If the customer is in the EU then tax should be charged according to the
	 *  vendor's address, and this function will return true.
	 */
	function tax_based_on_vendor_address () {
		global $__tax_based_on_vendor_address;
		global $vmLogger;
	
		if (!isset ($__tax_based_on_vendor_address)) {
			$__tax_based_on_vendor_address = ps_checkout::_tax_based_on_vendor_address ();
//			if ($__tax_based_on_vendor_address)
//				$vmLogger->debug ('calculating tax based on vendor address');
//			else
//				$vmLogger->debug ('calculating tax based on shipping address');
		}
		return $__tax_based_on_vendor_address;
	}
	
	function _tax_based_on_vendor_address () {
		global $auth;
		global $vmLogger;
	
		switch (TAX_MODE) {
		case '0':
			return false;
	
		case '1':
			return true;
	
		case '17749':
			if (! array_key_exists ('country', $auth)) {
				$vmLogger->debug ('shopper\'s country is not known; defaulting to vendor-based tax');
				return true;
			}
	
			$vmLogger->debug ('shopper is in ' . $auth['country']);
			return ps_checkout::country_in_eu_common_vat_zone ($auth['country']);
	
		default:
			$vmLogger->warning ('unknown TAX_MODE "' . TAX_MODE . '"');
			return true;
		}
	}
	
	function country_in_eu_common_vat_zone ($country) {
		$eu_countries = array ('AUT', 'BGR', 'BEL', 'CYP', 'CZE', 'DEU', 'DNK', 'ESP', 'EST', 
								'FIN', 'FRA', 'FXX', 'GBR', 'GRC', 'HUN', 'IRL', 'ITA', 'LVA', 'LTU', 
								'LUX', 'MLT', 'NLD', 'POL', 'PRT', 'ROM', 'SVK', 'SVN', 'SWE');
		return in_array ($country, $eu_countries);
	}
}
?>
