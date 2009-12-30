<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: paymentMethod.class.php 1760 2009-05-03 22:58:57Z Aravot $
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

define('UNKNOWN', 0);
define('MASTERCARD', 1);
define('VISA', 2);
define('AMEX', 3);
define('DINNERS', 4);
define('DISCOVER', 5);
define('ENROUTE', 6);
define('JCB', 7);
define('BANKCARD', 8);
define('SOLO_MAESTRO', 9);
define('SWITCH_MAESTRO', 10);
define('SWITCH_', 11);
define('MAESTRO ', 12);
define('UK_ELECTRON', 13);
define('SWITCHCARD', 14);

define('CC_OK', 0);
define('CC_ECALL', 1);
define('CC_EARG', 2);
define('CC_ETYPE', 3);
define('CC_ENUMBER', 4);
define('CC_EFORMAT', 5);
define('CC_ECANTYPE', 6);

class vmPaymentMethod extends vmAbstractObject {

	// CreditCard Validation vars
	var $number = 0;
	var $type = UNKNOWN;
	var $errno = CC_OK;
	/** @var string The key which is used to identify this object (example: product_id) */
	var $_key = 'id';
	/** @var array An array holding the names of all required fields */
	var $_required_fields = array('name' );
	
	/** @var array An array holding the names of fields that are UNIQUE => means those must be checked onAdd and onUpdate for occurences of entities with the same value */
	var $_unique_fields = array();
	/** @var string The name of the databaser table for this entity */
	var $_table_name = '#__{vm}_payment_method';
	
	/**
	 * Validates the Input Parameters on Payment Add
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_add(&$d) {
		

		if (empty($d["name"])) {
			$GLOBALS['vmLogger']->err( JText::_('VM_PAYMENTMETHOD_ERR_NAME') );
			return False;
		}

		$d['is_creditcard'] = !empty( $d['creditcard']) ? '1' : '0';

		if (empty($d['element'])) {
			$d['element'] = "payment";
		}

		if (empty($d["published"])) {
			$d["published"] = "N";
		}
		if (empty($d["creditcard"])) {
			$d["accepted_creditcards"] = "";
		}
		else {
			$d["accepted_creditcards"] = "";
			foreach($d['creditcard'] as $num => $creditcard_id) {
				$d["accepted_creditcards"] .= $creditcard_id . ",";
			}
		}
		
		if ( !empty($d["element"]) ) {
			// Here we have a custom payment class
			$element = basename(vmGet($d,'element'));
			if( file_exists( ADMINPATH . "plugins/payment/".$element.".php" ) ) {
				// Include the class code and create an instance of this class
				include( ADMINPATH . "plugins/payment/".$element.".php" );
				$class = 'plgpayment'.$element;
				if( !class_exists($class)) {
					$GLOBALS['vmLogger']->err(JText::_('VM_PAYMENTMETHOD_CLASS_NOT_EXIST').' ('.$element.')');
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Validates the Input Parameters on Payment Update
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_update(&$d) {

		if( !$this->validate_add($d)) {
			return false;
		}

		if (empty($d["id"])) {
			$GLOBALS['vmLogger']->err( JText::_('VM_PAYMENTMETHOD_UPDATE_SELECT') );
			return False;
		}

		return True;
	}

	/**
	 * Validates the Input Parameters on Payment Update
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_delete(&$d) {

		if (empty($d["id"])) {
			$GLOBALS['vmLogger']->err( JText::_('VM_PAYMENTMETHOD_DELETE_SELECT') );
			return False;
		}

		return True;
	}
	
	/**
	 * Adds a new payment method
	 *
	 * @param array $d
	 * @return boolean
	 */
	function add(&$d) {
		global $hVendor;
		$db = new ps_DB;
		$vendor_id = $hVendor->getLoggedVendor();

		//Todo make it available for admins, but need a menu to select user
		$auth = $_SESSION['auth'];
		$user_id = $auth["user_id"];
		$vendor_id = $hVendor->getVendorIdByUserId($user_id);
		

		if (!$this->validate_add($d)) {
			return False;
		}
    
		if (!$d["shopper_group_id"]) {
			$q =  "SELECT shopper_group_id FROM #__{vm}_shopper_group WHERE ";
			$q .= "`default`='1' ";
			$q .= "AND vendor_id='$vendor_id'";
			$db->query($q);
			$db->next_record();
			$d["shopper_group_id"] = $db->f("shopper_group_id");
		}
		$params		= vmRequest::getVar( 'params', null, 'post', 'array' );

		// Build parameter INI string
		if (is_array($params))
		{
			$txt = array ();
			foreach ($params as $k => $v) {
				if( is_array($v)) {
					$v = implode(',', $v );
				}
				$txt[] = "$k=$v";
			}
			$params = implode("\n", $txt);
		}
		$fields = array( 'vendor_id' => $vendor_id, 
						'name' => vmGet($d, 'name' ), 
						'element' => vmGet($d, 'element' ),
						'shopper_group_id' => vmRequest::getInt('shopper_group_id'),
						'discount' => vmRequest::getFloat('discount'),
						'discount_is_percentage' => vmGet($d, 'discount_is_percentage'),
						'discount_max_amount' => (float)str_replace(',', '.', $d["discount_max_amount"]),						
						'discount_min_amount' => (float)str_replace(',', '.', $d["discount_min_amount"]),
						'type' => vmGet($d, 'type'), 
						'ordering' => vmRequest::getInt('ordering'), 
						'is_creditcard' => vmGet($d, 'is_creditcard'),
						'published' => vmGet($d, 'published'),
						'accepted_creditcards' => vmGet($d, 'accepted_creditcards'), 
						'extra_info' => vmGet( $_POST, 'extra_info', null, VMREQUEST_ALLOWRAW ),
						'params' => $params
				);
		$db->buildQuery( 'INSERT', '#__{vm}_payment_method', $fields );
		$db->query();
		
		$_REQUEST['id'] = $db->last_insert_id();
		
		return True;

	}

	/**
	 * Updates a Payment Entry
	 *
	 * @param array $d
	 * @return boolean
	 */
	function update(&$d) {
		
		
		global $vmLogger,$hVendor;
		
		$db = new ps_DB;

		$vendor_id = $hVendor->getLoggedVendor();
			
		if( !$perm->check( 'admin' )) {
			if($vendor_id!=$d['vendor_id']){
				$vmLogger->err( JText::_('VM_PAYMENTMETHOD_NOT_ALLOWED_TO_UPDATE ',false) );
				return false;
			}
		}else{
			if($vendor_id!=$d['vendor_id']){
				$vendor_id = $d['vendor_id'];
			}
		}

		if (!$this->validate_update($d)) {
			return False;
		}
		
		$params		= vmRequest::getVar( 'params', null, 'post', 'array' );

		// Build parameter INI string
		if (is_array($params))
		{
			$txt = array ();
			foreach ($params as $k => $v) {
				if( is_array($v)) {
					$v = implode(',', $v );
				}
				$txt[] = "$k=$v";
			}
			$params = implode("\n", $txt);
		}
		$fields = array( 'name' => vmGet($d, 'name' ), 
						'element' => vmGet($d, 'element' ),
						'shopper_group_id' => vmRequest::getInt('shopper_group_id'),
						'discount' => vmRequest::getFloat('discount'),
						'discount_is_percentage' => vmGet($d, 'discount_is_percentage'),
						'discount_max_amount' => (float)str_replace(',', '.', $d["discount_max_amount"]),						
						'discount_min_amount' => (float)str_replace(',', '.', $d["discount_min_amount"]),
						'type' => vmGet($d, 'type'), 
						'ordering' => vmRequest::getInt('ordering'), 
						'is_creditcard' => vmGet($d, 'is_creditcard'),
						'published' => vmGet($d, 'published'),
						'accepted_creditcards' => vmGet($d, 'accepted_creditcards'), 
						'extra_info' => vmGet( $_POST, 'extra_info', null, VMREQUEST_ALLOWRAW ),
						'params' => $params
				);
		$db->buildQuery( 'UPDATE', '#__{vm}_payment_method', $fields, 'WHERE id='.(int)$d["id"].' AND vendor_id='.$vendor_id );
		if( $db->query() === false ) {
			$vmLogger->err('Failed to update the Payment Method!');
			return false;
		}
		$vmLogger->info('The Payment Method has been updated.');
		return True;
		
	}

	/**
	* Controller for Deleting Records.
	*/
	function delete(&$d) {

		if (!$this->validate_delete($d)) {
			return False;
		}
		$record_id = $d["id"];

		if( is_array( $record_id)) {
			foreach( $record_id as $record) {
				if( !$this->delete_record( $record, $d ))
				return false;
			}
			return true;
		}
		else {
			return $this->delete_record( $record_id, $d );
		}
	}
	/**
	* Deletes one Record.
	*/
	function delete_record( $record_id, &$d ) {

		global $db,$hVendor;
		
		//Gets the user_id of logged user and ref vendor_id
		$vendor_id = $hVendor->getLoggedVendor();


		$q = 'DELETE from #__{vm}_payment_method WHERE id='.(int)$record_id.' AND ';
		if( !$perm->check( 'admin' )) {
			if($vendor_id!=$d['vendor_id']){
				$vmLogger->err( JText::_('VM_PAYMENTMETHOD_NOT_ALLOWED_TO_DELETE ',false) );
			}else{
				$q .= " vendor_id = '$vendor_id'";	
			}
		}
		
		$db->query($q);

		return True;
	}
	/**
	 * Retrieves a Payment Plugin. You can specify the element name by $plugin
	 *
	 * @param string $plugin
	 * @return mixed
	 */
	function getPaymentPlugin($plugin=null) {
		return vmPluginHelper::getPlugin('payment', $plugin );
	}
	/**
	 * Allows to retrieve an object of the desired payment method, specified by $id 
	 *
	 * @param int $id
	 * @return vmpaymentplugin
	 */
	function &getPaymentPluginById( $id ) {
		return vmPluginHelper::getPluginById('payment', $id );
	}
	/**
	 * Import the vmpaymentplugin instance of the desired payment method, specified by $id 
	 *
	 * @param int $id
	 * @return vmpaymentplugin
	 */
	function &importPaymentPluginById( $id ) {
		return vmPluginHelper::importPluginById('payment', $id );
	}
	/**
	 * Prints a drop-down list with all available payment methods
	 *
	 * @param int $payment_method_id
	 */
	function list_method($payment_method_id) {
		
		global $hVendor;
		$vendor_id = $hVendor->getLoggedVendor();

		//Paymentmethods are not vendorrelated yet we use the store by Max Milbers
		$vendor_id = 1;
		
		$db = new ps_DB;

		require_once(CLASSPATH.'ps_shopper_group.php');
		$ps_shopper_group = new ps_shopper_group;


		$q =  "SELECT * FROM #__{vm}_shopper_group WHERE ";
		$q .= "`default`='1' ";
//		$q .= "AND vendor_id='$vendor_id'";
		$db->query($q);
		if (!$db->num_rows()) {
			$q =  "SELECT * from #__{vm}_shopper_group WHERE ";
//			$q .= "vendor_id='$vendor_id'";
			$db->query($q);
		}
		$db->next_record();
		$default_shopper_group_id = $db->f("shopper_group_id");


		$q = "SELECT * FROM #__{vm}_payment_method WHERE ";
//		$q .= "vendor_id='$hVendor_id' AND "; //paymentmethods are not vendorrelated yet by Max Milbers
		$q .= "shopper_group_id='$default_shopper_group_id' ";
		if ($ps_shopper_group->get_id() != $default_shopper_group_id)
		$q .= "OR shopper_group_id='".$ps_shopper_group->get_id()."' ";
		$q .= "ORDER BY ordering";
		$db->query($q);

		// Start drop down list
	
		$array[0] = JText::_('VM_SELECT');
		while ($db->next_record()) {
			$array[$db->f("id")] = $db->f("name");
		}
		ps_html::dropdown_display('payment_method_id', $payment_method_id, $array );

	}

	/**
	 * Returns all payment_methods with given selector in a Radiolist
	 *
	 * @param string $selector A String like "B" identifying a type of payment methods
	 * @param int $payment_method_id An ID to preselect
	 * @param boolean $horiz Separate Items with Spaces if true, else with <br />
	 * @return string
	 */
	function list_payment_radio($selector, $payment_method_id, $horiz) {
		global $CURRENCY_DISPLAY, $ps_checkout;
		//This is the id of the mainvendor because the payment mehthods are not vendorrelated yet
//		$hVendor_id = $_SESSION['ps_vendor_id'];
		$vendor_id = 1; 
		$auth = $_SESSION["auth"];
		$db = new ps_DB;
		if( !isset( $ps_checkout )) { $ps_checkout = new ps_checkout(); }
		
		require_once(CLASSPATH.'ps_shopper_group.php');
		$ps_shopper_group = new ps_shopper_group;

		$q =  "SELECT shopper_group_id from #__{vm}_shopper_group WHERE ";
		$q .= "`default`='1' ";
		$db->query($q);
		if (!$db->num_rows()) {
			$q =  "SELECT shopper_group_id from #__{vm}_shopper_group";
			$db->query($q);
		}
		$db->next_record();
		$default_shopper_group_id = $db->f("shopper_group_id");

		$q = "SELECT id,discount, discount_is_percentage, name from #__{vm}_payment_method WHERE ";
		$q .= "(type='$selector') AND ";
		$q .= "published='Y' AND ";
//		$q .= "vendor_id='$vendor_id' AND ";

		if ($auth["shopper_group_id"] == $default_shopper_group_id) {
			$q .= "shopper_group_id='$default_shopper_group_id' ";
		} else {
			$q .= "(shopper_group_id='$default_shopper_group_id' ";
			$q .= "OR shopper_group_id='".$auth["shopper_group_id"]."') ";
		}

		$q .= "ORDER BY ordering";
		$db->query($q);
		$has_result = false;
		// Start radio list
		while ($db->next_record()) {
			$has_result = true;
//			echo "<input type=\"radio\" name=\"payment_method_id\" id=\"".$db->f("name")."\" value=\"".$db->f("id")."\" ";
			echo "<input type=\"radio\" name=\"payment_method_id\" id=\"".$db->f("name")."\" value=\"".$db->f("id")."\" ";
			if( $selector == "' OR type='Y" ) {
				echo "onchange=\"javascript: changeCreditCardList();\" ";
			}
			if ((($db->f("id") == $payment_method_id) || $db->num_rows() < 2) && !@$GLOBALS['payment_selected']) {
				echo "checked=\"checked\" />\n";
				$GLOBALS['payment_selected'] = true;
			}
			else
			echo ">\n";
			$discount  = $ps_checkout->get_payment_discount( $db->f("id") );
			echo "<label for=\"".$db->f("name")."\">".$db->f("name");
			if ($discount > 0.00) {
				echo " (- ".$CURRENCY_DISPLAY->getFullValue(abs($discount)).") \n";
			}
			elseif ($discount < 0.00) {
				echo " (+ ".$CURRENCY_DISPLAY->getFullValue(abs($discount)).") \n";
			}
			echo "</label>";
			if ($horiz) {
				echo(" ");
			} else {
				echo("<br />");
			}
		}
		return $has_result;
	}

	/**
	 * Query the payment_method Table for the given ID
	 *
	 * @param int $payment_method_id
	 * @return ps_DB
	 */
	function payment_sql($payment_method_id) {
		$db = new ps_DB;
		$q = 'SELECT * FROM #__{vm}_payment_method WHERE id='.(int)$payment_method_id;
		$db->query($q);
		return $db;
	}

	/**
	 * Returns all CreditCards in a Radiolist
	 *
	 * @param int $payment_method_id
	 * @param boolean $horiz
	 */
	function list_cc($payment_method_id, $horiz) {
		$this->list_payment_radio("' OR type='Y",$payment_method_id, $horiz); //A bit strange :-)
	}

	/**
	 * Returns all Bank payment in a Radiolist
	 *
	 * @param int $payment_method_id
	 * @param boolean $horiz
	 */
	function list_bank($payment_method_id, $horiz) {
		$has_bank_methods = $this->list_payment_radio("B", $payment_method_id, $horiz); //A bit easier :-)
		if( $has_bank_methods ) {
			require_once( CLASSPATH . 'ps_user.php' );
			$dbu =& ps_user::getUserInfo( $_SESSION['auth']['user_id'], array( 'bank_account_holder','bank_iban','bank_account_nr','bank_sort_code','bank_name' ) ); 
			if( !$dbu->f('bank_account_holder') || !$dbu->f('bank_account_nr') || !$dbu->f('bank_sort_code')) {
				echo '<br />';
				require_once( CLASSPATH . 'ps_userfield.php');
				ps_userfield::listUserFields( ps_userfield::getUserfields( 'bank' ), array(), $dbu );
			}
		}
	}

	/**
	 * Returns all Payment methods which need no check
	 *
	 * @param int $payment_method_id
	 * @param boolean $horiz
	 */
	function list_nocheck($payment_method_id, $horiz) {
		$this->list_payment_radio("N",$payment_method_id, $horiz); //A bit easier :-)
	}

	/**
	 * Returns all Payment methods which a paypal - like
	 *
	 * @param int $payment_method_id
	 * @param boolean $horiz
	 */
	function list_paypalrelated($payment_method_id, $horiz) {
		$this->list_payment_radio("P",$payment_method_id, $horiz); //A bit easier :-)
	}

	/**
	* get_field public method
	* @return string
	*/
	function get_field($payment_method_id, $field_name) {

		$db = new ps_DB;
		
		$q = 'SELECT `'.$field_name.'` FROM `#__{vm}_payment_method` WHERE `id`='.(int)$payment_method_id;
		$db->query($q);
		$db->next_record();
		return $db->f($field_name);
	}
	
	/**
	 * returns true if the payment is credit card payment
	 *
	 * @param int $payment_id
	 * @return boolean
	 */
	function is_creditcard($payment_id) {

		$db = new ps_DB;
		$q = "SELECT is_creditcard,accepted_creditcards FROM #__{vm}_payment_method\n";
		$q .= 'WHERE id='.(int)$payment_id;
		$db->query($q);
		$db->next_record();
		$details = $db->f('accepted_creditcards');

		return $details != "";

	}

	/**
	 * Validates the Payment Method (Credit Card Number)
	 * Adapted From CreditCard Class
	 * Copyright (C) 2002 Daniel Fr�z Costa
	 *
	 * Documentation:
	 *
	 * Card Type                   Prefix           Length     Check digit
	 * - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 * MasterCard                  51-55            16         mod 10
	 * Visa                        4                13, 16     mod 10
	 * AMEX                        34, 37           15         mod 10
	 * Dinners Club/Carte Blanche  300-305, 36, 38  14         mod 10
	 * Discover                    6011             16         mod 10
	 * enRoute                     2014, 2149       15         any
	 * JCB                         3                16         mod 10
	 * JCB                         2131, 1800       15         mod 10
	 *
	 * More references:
	 * http://www.beachnet.com/~hstiles/cardtype.hthml.
	  *
	  * @param string $creditcard_code
	  * @param string $cardnum
	  * @return boolean
	 */
	function validate_payment($creditcard_code, $cardnum) {

		$this->number = $this->_strtonum($cardnum);
		/*
		if(!$this->detectType($this->number))
		{
		$this->errno = CC_ETYPE;
		$d['error'] = $this->errno;
		return false;
		}*/

		if(empty($this->number) || !$this->mod10($this->number))
		{
			$this->errno = CC_ENUMBER;
			$d['error'] = $this->errno;
			return false;
		}

		return true;
	}

	/**
	 * detectType method: returns card type in number format
	 *
	 * @param string $cardnum
	 * @return boolean
	 */
	function detectType($cardnum = 0){
		if($cardnum)
		$this->number = $this->_strtonum($cardnum);
		if(!$this->number) {
			$this->errno = CC_ECALL;
			return UNKNOWN;
		}

		if(preg_match("/^5[1-5]\d{14}$/", $this->number)) {
			$this->type = MASTERCARD;
		}
		elseif(preg_match("/^4(\d{12}|\d{15})$/", $this->number)) {
			$this->type = VISA;
		}
		else if(preg_match("/^3[47]\d{13}$/", $this->number)) {
			$this->type = AMEX;
		}
		else if(preg_match("/^[300-305]\d{11}$/", $this->number) || preg_match("/^3[68]\d{12}$/", $this->number)) {
			$this->type = DINNERS;
		}
		elseif (ereg ('^6334[5-9].{11}$', $this->number) || ereg ('^6767[0-9].{11}$', $this->number)) {
			$this->type = SOLO_MAESTRO;
		}
		elseif (ereg ('^564182[0-9].{9}$', $this->number) || ereg ('^6333[0-4].{11}$', $this->number) || ereg ('^6759[0-9].{11}$', $this->number)) {
			$this->type= SWITCH_MAESTRO;
		}
		elseif (ereg ('^49030[2-9].{10}$', $this->number) || ereg ('^49033[5-9].{10}$', $this->number) || ereg ('^49110[1-2].{10}$', $this->number) || ereg ('^49117[4-9].{10}$', $this->number) || ereg ('^49118[0-2].{10}$', $this->number) || ereg ('^4936[0-9].{11}$', $this->number)) {
			$this->type = SWITCH_;
		}
		//failing earlier 6xxx xxxx xxxx xxxx checks then its a Maestro card
		elseif (ereg ('^6[0-9].{14}$', $this->number) || ereg ('^5[0,6-8].{14}$', $this->number)) {
			$this->type = MAESTRO;
		}
		elseif (ereg ('^450875[0-9].{9}$', $this->number)
					|| ereg ('^48440[6-8].{10}$', $this->number)
					|| ereg ('^48441[1-9].{10}$', $this->number)
					|| ereg ('^4844[2-4].{11}$', $this->number)
					|| ereg ('^48445[0-5].{10}$', $this->number)
					|| ereg ('^4917[3-5].{11}$', $this->number)
					|| ereg ('^491880[0-9].{9}$', $this->number)) {
			$this->type= UK_ELECTRON;
		}
		//DB 18-07-05
		else if(preg_match("/^6\d{15,21}$/", $this->number)) {
			$this->type = SWITCHCARD;
		}
		else if(preg_match("/^6011\d{12}$/", $this->number)) {
			$this->type = DISCOVER;
		}
		else if(preg_match("/^5610\d{12}$/", $this->number)) {
			$this->type = BANKCARD;
		}
		else if(preg_match("/^2(014|149)\d{11}$/", $this->number)) {
			$this->type = ENROUTE;
		}
		else if(preg_match("/^3\d{15}$/", $this->number) ||  preg_match("/^(2131|1800)\d{11}$/", $this->number)) {
			$this->type = JCB;
		}

		if(!$this->type) {
			$this->errno = CC_ECANTYPE;
			return UNKNOWN;
		}
		return $this->type;
	}

	/*
	* detectTypeString
	*   return string of card type
	*/
	function detectTypeString($cardnum = 0) {
		if(!$cardnum) {
			if(!$this->type)
			$this->errno = CC_EARG;
		}
		else {
			$this->type = $this->detectType($cardnum);
		}

		if(!$this->type) {
			$this->errno = CC_ETYPE;
			return NULL;
		}

		switch($this->type) {
			case MASTERCARD:
				return "MASTERCARD";
			case VISA:
				return "VISA";
			case AMEX:
				return "AMEX";
			case DINNERS:
				return "DINNERS";
			case DISCOVER:
				return "DISCOVER";
			case ENROUTE:
				return "ENROUTE";
			case JCB:
				return "JCB";
			default:
				$this->errno = CC_ECANTYPE;
				return NULL;
		}
	}

	/*
	* getCardNumber
	*   returns card number, only digits
	*/
	function getCardNumber(){
		if(!$this->number){
			$this->errno = CC_ECALL;
			return 0;
		}
		return $this->number;
	}

	/*
	* errno method
	*   return error number
	*/
	function errno(){
		return $this->errno;
	}

	/*
	* mod10 method - Luhn check digit algorithm
	*   return 0 if true and !0 if false
	*/
	function mod10( $card_number ){

		$digit_array = array ();
		$cnt = 0;

		//Reverse the card number
		$card_temp = strrev ( $card_number );

		//Multiple every other number by 2 then ( even placement )
		//Add the digits and place in an array
		for ( $i = 1; $i <= strlen ( $card_temp ) - 1; $i = $i + 2 ) {
			//multiply every other digit by 2
			$t = substr ( $card_temp, $i, 1 );
			$t = $t * 2;
			//if there are more than one digit in the
			//result of multipling by two ex: 7 * 2 = 14
			//then add the two digits together ex: 1 + 4 = 5
			if ( strlen ( $t ) > 1 ) {
				//add the digits together
				$tmp = 0;
				//loop through the digits that resulted of
				//the multiplication by two above and add them
				//together
				for ( $s = 0; $s < strlen ( $t ); $s++ ) {
					$tmp = substr ( $t, $s, 1 ) + $tmp;
				}
			}
			else{  // result of (* 2) is only one digit long
				$tmp = $t;
			}
			//place the result in an array for later
			//adding to the odd digits in the credit card number
			$digit_array [ $cnt++ ] = $tmp;
		}
		$tmp = 0;

		//Add the numbers not doubled earlier ( odd placement )
		for ( $i = 0; $i <= strlen ( $card_temp ); $i = $i + 2 ) {
			$tmp = substr ( $card_temp, $i, 1 ) + $tmp;
		}

		//Add the earlier doubled and digit-added numbers to the result
		$result = $tmp + array_sum ( $digit_array );

		//Check to make sure that the remainder
		//of dividing by 10 is 0 by using the modulas
		//operator
		return ( $result % 10 == 0 );

	}

	/*
	* resetCard method
	*   clear only cards information
	*/
	function resetCard() {
		$this->number = 0;
		$this->type = 0;
	}

	/*
	* strError method
	*   return string error
	*/
	function strError() {
		switch($this->errno) {
			case CC_ECALL:
				return "Invalid call for this method";
			case CC_ETYPE:
				return "Invalid card type";
			case CC_ENUMBER:
				return "Invalid card number";
			case CC_EFORMAT:
				return "Invalid format";
			case CC_ECANTYPE:
				return "Cannot detect the type of your card";
			case CC_OK:
				return "Success";
		}
	}

	/*
	* _strtonum private method
	*   return formated string - only digits
	*/
	function _strtonum($string) {
		$nstr = "";
		for($i=0; $i< strlen($string); $i++) {
			if(!is_numeric($string{$i}))
			continue;
			$nstr = "$nstr".$string{$i};
		}
		return $nstr;
	}
	/**
	 * Lists all available payment classes in the payment directory
	 *
	 * @param string $name
	 * @param string $preselected
	 * @return string
	 */
	function list_available_classes( $name, $preselected='payment' ) {
		
		$files = vmReadDirectory( ADMINPATH . "plugins/payment/", ".php$", true, true);
		$array = array();
        foreach ($files as $file) { 
            $file_info = pathinfo($file);
            $filename = $file_info['basename'];
            if( stristr($filename, '.cfg')) { continue; }
            $array[basename($filename, '.php' )] = basename($filename, '.php' );
        }
        return ps_html::selectList( $name, $preselected, $array );
	}
}


/**
*
* The vmPayment class, containing the default payment processing code
* for payment methods that have no own class
* @abstract 
*/
class vmPaymentPlugin extends vmPlugin {

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
	function vmPaymentPlugin(& $subject, $config) {
		parent::__construct($subject, $config);
	}

	/**
	 * Processes the actual payment (transaction)
	 *
	 * @param string $order_number
	 * @param double $order_total
	 * @param array $d
	 * @return boolean
	 */
   function process_payment($order_number, $order_total, &$d) {
        return true;
    }
    /**
     * Captures the payment after the order has been paid and the goods are shipped
     *
     * @param string $order_number
     * @param double $order_total
     * @param array $d
     */
    function capture_payment($order_number, $order_total, &$d) {
    	
    }
    /**
     * Should display some HTML, example: a Form to redirect the customer to the payment gateway
     *
     * @param ps_DB $db
     * @param stdClass $user
     * @param ps_DB $dbbt
     */
    function showPaymentForm( &$db, $user, $dbbt ) {
    	if( !empty($this->_id )) {
    		$db = new ps_DB();
    		$db->query('SELECT extra_info FROM #__{vm}_payment_method WHERE id='.(int)$this->_id);
    		if( $db->next_record() && $db->f('extra_info')) {
    			@eval('?>'.$db->f('extra_info').'<?php');
    		}
    	}
    }
    /**
     * Retrieves the secret transaction key
     *
     * @return mixed
     */
	function get_passkey() {
		$auth = $_SESSION['auth'];
		$db = new ps_DB();
		// Get the Transaction Key securely from the database
		$db->query( "SELECT ".VM_DECRYPT_FUNCTION."(secret_key,'".ENCODE_KEY."') as passkey FROM #__{vm}_payment_method WHERE element='".$this->_name."' AND shopper_group_id='".$auth['shopper_group_id']."'" );
		$db->next_record();
		if( !$db->f('passkey')) {
			$vmLogger->err( JText::_('VM_PAYMENT_ERROR',false).'. Technical Note: The required transaction key is empty! The payment method settings must be reviewed.' );
			return false;
		}
		return $db->f('passkey');
	}
}
?>