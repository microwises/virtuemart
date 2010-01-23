<?php
/*
 * Created on 18.06.2009
 *
 * Copyright 2009 VirtueMart Team based on Virtuemart 2009/04
 * 
 */
 
 
 if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*	This class provides Functions to get simple answers for a user and/or vendor	
*
* @version $Id$
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2009 Virtuemart, since 2009 VirtueMart Dev Team - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/


class Vendor {
	
	
//	public $vendor_image,$vendor_country_2_code, $vendor_country_3_code, $vendor_image_url, $vendor_name, $vendor_state_name,
//		$vendor_address,$vendor_address_2, $vendor_url, $vendor_city,$vendor_country,$vendor_mail,$vendor_store_name,
//		$vendor_state, $vendor_zip, $vendor_phone, $vendor_currency, $vendor_store_desc, $vendor_freeshipping,
//		 $vendor_currency_display_style, $vendor_full_image, $vendor_accepted_currencies,
//        $vendor_address_format, $vendor_date_format;
       
     private $db;
     
     function __construct(){
		$this->db = JFactory::getDBO();
	}
	
	/**
	 * Proxy function
	 * @todo delete this function
	 */
	function getVendorIdByUserId($userId, $ownerOnly=true) 
	{				
		return self::getVendorId('user', $userId, $ownerOnly);
	}

	/**
	 * Find the user id given a vendor id
	 *
	 * @author Max Milbers
	 * @param int $vendor_id
	 * @return int $user_id
	 */
	function getUserIdByVendorId($vendorId) 
	{
		$db = JFactory::getDBO();
		if (empty($vendorId)) return;
		else {
			$query = 'SELECT `user_id` FROM `#__vm_auth_user_vendor` WHERE `vendor_id`="' . $vendorId . '" ';
			$db->setQuery($query);
			
			return $db->loadResult();
		}
	}
	


	/**
	* name: getLoggedVendor
	* Checks which $vendorId has the just logged in user.
	* @author Max Milbers
	* @param @param $ownerOnly returns only an id if the vendorOwner is logged in (dont get confused with storeowner) 
	* returns int $vendorId
	*/	
	function getLoggedVendor($ownerOnly = true){
		global $hVendor;
		$user = JFactory::getUser();
		$userId = $user->id;
		if(isset($userId)){
			$vendorId = Vendor::getVendorIdByUserId($userId,$ownerOnly);
			return $vendorId;
		}else{
			echo('$user_id empty, no logged User');
			$GLOBALS['vmLogger']->err('$user_id empty, no logged User');
			return 0;
		}
		
	}
	
	/**
	 * Function to determine if a user is a Vendor by looking at the field user_is_vendor
	 * in the table user_info 
	 *
	 * @author Max Milbers
	 * @param int $user_id
	 * @param int $vendor_id
	 * @return boolean
	 */
	function isVendor( $userId ) {
		if (empty($userId)) return;
		else {
			$db = JFactory::getDBO();
			$q = 'SELECT `user_is_vendor` FROM `#__vm_user_info` WHERE `user_id`='.$userId;
			$db->setQuery($q);
			return $db->loadResult();
		}
		
	}
	

	
	/**
	 * Retrieves the name of a vendor specified by $vendor_id
	 *
	 * @param int $vendor_id
	 * @param int $product_id
	 * @return String
	 */
	function get_name($vendor_id,$product_id="") {

		// Returns the vendor name corresponding to a vendor_id;
		$db = new ps_DB;

		if ($vendor_id) {
			$q = "SELECT `vendor_name` FROM `#__{vm}_vendor` WHERE `vendor_id` = '$vendor_id'";
		} elseif ($product_id) {
			$q  = "SELECT `vendor_name` FROM `#__{vm}_product`,`#__{vm}_vendor` ";
			$q .= "WHERE `product_id` = '$product_id' ";
			$q .= "AND `#__{vm}_product`.`vendor_id` = `#__{vm}_vendor`.`vendor_id` ";
		} else {
			$vendor_id = $this -> getLoggedVendor();
			$q = "SELECT `vendor_name` FROM `#__{vm}_vendor` WHERE `vendor_id` = '$vendor_id'";
		}

		$db->query($q);
		$db->next_record();
		return $db->f("vendor_name");
	}




	/**
	 * Prints a drop-down list of vendor names and their ids.
	 *
	 * @author unknown changed by Max Milbers
	 * 
	 * @param int $vendor_id
	 * @param boolean $withZero a List with Option to choose NON vendor
	 */
	function list_vendor($vendor_id='1',$withZero=false) {

		$db = new ps_DB;

		$q = 'SELECT `vendor_id`,`vendor_name` FROM `#__{vm}_vendor` ORDER BY `vendor_name`';
		$db->query($q);
		$db->next_record();

		// If only one vendor do not show list
		if ($db->num_rows() == 1 && !$withZero) {
			echo '<input type="hidden" name="vendor_id" value="'.$db->f("vendor_id").'" />';
			echo $db->f("vendor_name");
		}	
		elseif($db->num_rows() > 1) {
			$db->reset();
			$array = array();
			if($withZero){
				$array[0] = "-";
			}
			while ($db->next_record()) {
				$array[$db->f("vendor_id")] = $db->f("vendor_name");
			}
			echo ps_html::selectList('vendor_id', $vendor_id, $array );
		}
		else{
			if($withZero){
				$array[0] = "-";
			}
			echo ps_html::selectList('vendor_id', $vendor_id, $array );
		}
		unset($db);
	}

	/**
	 * Prints a drop-down list of vendor names and their ids.
	 * But not if the user is only a normal vendor. 
	 * This is used in product.product_form.php
	 * @author Max Milbers
	 * @param int $vendor_id the vendorID of the logged in user
	 */
	function list_ornot_vendor($vendor_id='1', $product_vendor_id) {

		$db = new ps_DB;
		global $perm;
		
		// If mainvendor or adminrights show whole list
		if($vendor_id==1 || $perm->check( 'admin' )){
			$q = 'SELECT `vendor_id`,`vendor_name` FROM `#__{vm}_vendor` ORDER BY `vendor_name` ';
			$db->query($q);
			$db->next_record();
			if ($db->num_rows() == 1) {
				echo '<input type="hidden" name="vendor_id" value="'.$db->f("vendor_id").'" />';
				echo $db->f("vendor_name");
			}
			elseif($db->num_rows() > 1) {
				$db->reset();
				$array = array();
				while ($db->next_record()) {
					$array[$db->f("vendor_id")] = $db->f("vendor_name");
				}
				echo ps_html::selectList('vendor_id', $product_vendor_id, $array );
			}
		}else{
			$db = ps_vendor::get_vendor_fields($product_vendor_id, array("vendor_id","vendor_name"),"");
			echo '<input type="hidden" name="vendor_id" value="'.$db->f("vendor_id").'" />';
			echo $db->f("vendor_name");
			
		}

	}


	/**************************************************************************
	** name: show_image()
	** created by: pablo
	** description:  Shows the image send in the $image field.
	**               $args are appended to the IMG tag.
	** parameters:
	** returns:
	***************************************************************************/
	function show_image($image, $args="") {

		$VendorId = $args;
		
		$url = IMAGEURL;
//		$path = ps_vendor::get_field($hVendor_id,"vendor_image_path");
		$db = ps_vendor::get_vendor_fields($VendorId, array("vendor_image_path"));
		$path = $this->db->loadResult();
//		$path =$db ->f('vendor_image_path');
		if (!empty($path))
		$url = str_replace( "shop_image/", $path, $url );

		$url .= "vendor/";
		$url .= $image;
		echo "<img src=\"".$url ."\" ". $args ." />\n";

		return True;
	}
	
		/**
	 * 
	 *  
	 * @author Max Milbers
	 * @param int $user_id
	 * returns joomla user email
	 */

	function get_juser_email_by_user_id(&$user_id){
		if(empty ($user_id))return;
//		$db =& JFactory::getDBO();
		$q = 'SELECT `email` FROM `#__users` WHERE `id`="'.$user_id.'" ';
		$this->db->setQuery($q);
		$this->db->query();
		return $this->db->loadResult();
	}
	
	function getUserIdByOrderId( &$order_id){
		if(empty ($order_id))return;
		$q  = "SELECT `user_id` FROM `#__vm_orders` WHERE `order_id`='$order_id'";
//		$db->query( $q );
		$this->db->setQuery($q);
		
//		if($db->next_record()){
		if($this->db->query()){
//			$user_id = $db->f('user_id');
			return $this->db->loadResult();
		}else{
			$GLOBALS['vmLogger']->err('Error in DB $order_id '.$order_id.' dont have a user_id');
			return 0;
		}
	}
	
	/**
	 * 		state_id 	country_id 	state_name 	state_3_code 	state_2_code
	 		1 			223 		Alabama 	ALA 			AL
	 		
	 		
	 		country_id 	zone_id 	country_name 	country_3_code 	country_2_code
			1 			1 			Afghanistan 	AFG 			AF
	 */
	

	/**
	 * Checks a currency symbol wether it is a HTML entity.
	 * When not and $convertToEntity is true, it converts the symbol
	 * Seems not be used      ATTENTION
	 * @param string $symbol
	 */
	function checkCurrencySymbol( $symbol, $convertToEntity=true ) {
		
		$symbol = str_replace('&amp;', '&', $symbol );
		
		if( substr( $symbol, 0, 1) == '&' && substr( $symbol, strlen($symbol)-1, 1 ) == ';') {
			return $symbol;
		}
		else {
			if( $convertToEntity ) {
				$symbol = htmlentities( $symbol, ENT_QUOTES, 'utf-8' );
				
				if( substr( $symbol, 0, 1) == '&' && substr( $symbol, strlen($symbol)-1, 1 ) == ';') {
					return $symbol;
				}		
				// Sometimes htmlentities() doesn't return a valid HTML Entity
				switch( ord( $symbol ) ) {
					case 128:
					case 63:
						$symbol = '&euro;';
						break;
				}
						
			}
		}
		
		return $symbol;
	}

	/**
	* @param string The vendor_currency_display_code
	*   FORMAT: 
    1: id, 
    2: CurrencySymbol, 
    3: NumberOfDecimalsAfterDecimalSymbol,
    4: DecimalSymbol,
    5: Thousands separator
    6: Currency symbol position with Positive values :
									// 0 = '00Symb'
									// 1 = '00 Symb'
									// 2 = 'Symb00'
									// 3 = 'Symb 00'
    7: Currency symbol position with Negative values :
									// 0 = '(Symb00)'
									// 1 = '-Symb00'
									// 2 = 'Symb-00'
									// 3 = 'Symb00-'
									// 4 = '(00Symb)'
									// 5 = '-00Symb'
									// 6 = '00-Symb'
									// 7 = '00Symb-'
									// 8 = '-00 Symb'
									// 9 = '-Symb 00'
									// 10 = '00 Symb-'
									// 11 = 'Symb 00-'
									// 12 = 'Symb -00'
									// 13 = '00- Symb'
									// 14 = '(Symb 00)'
									// 15 = '(00 Symb)'
    	EXAMPLE: ||&euro;|2|,||1|8
	* @return string
	*/
	function get_currency_display_style( $style ) {
	
		$array = explode( "|", $style );
		$display = Array();
		$display["id"] = @$array[0];
		$display["symbol"] = @$array[1];
		$display["nbdecimal"] = @$array[2];
		$display["sdecimal"] = @$array[3];
		$display["thousands"] = @$array[4];
		$display["positive"] = @$array[5];
		$display["negative"] = @$array[6];
		return $display;
	}	
	
	/**
	 * 
	 * MUST-TODO , functions calls need to be rewritten !!!
	 * mosttime $vendor_id is set to 1;
	 * Returns the formatted Store Address
	 *	@author someone, completly rewritten by Max Milbers
	 * @param boolean $use_html
	 * @return String
	 */
	function formatted_store_address( $use_html=false, $vendor_id ) {
		
		if(empty($vendor_id)){
			$GLOBALS['vmLogger']->err( 'formatted_store_address no vendor_id given' );
			return;
		}
		
		$db = ps_vendor::get_vendor_details($vendor_id);
		
		$address_details['name'] = $db->f("vendor_store_name");;
		$address_details['address_1'] = $db->f("address_1");
		$address_details['address_2'] = $db->f("address_2");
		$address_details['state'] = $db->f("state");
		$address_details['state_name'] = $db->f("state_name");
		$address_details['city'] = $db->f("city");
		$address_details['zip'] = $db->f("zip");
		$address_details['country'] = $db->f("country");
		$address_details['phone'] = $db->f("vendor_phone");
		$address_details['email'] = $db->f("email");
		$address_details['fax'] = $db->f("fax");
		$address_details['url'] = $db->f("url");
		
		return vmFormatAddress( $address_details, $use_html, true);
	}
	
	/**
	 * Print the name of vendor $vend_id
	 *
	 * @param int $vend_id
	 */
	 //ATM unused
//	function show_vendorname($vend_id) {
//		echo $this->getVendorName( $vend_id );
//	}

		/**
	 * Return the name of vendor $id
	 *
	 * @param int $id
	 * @return string
	 */
	 //ATM unused
//	function getVendorName( $id ) {
//		if(empty ($id)) return ;
//		$db =& JFactory::getDBO();
//		$q = 'SELECT vendor_name FROM #__{vm}_vendor WHERE vendor_id=`'.(int)$id.'`';
//		$db->setQuery($q);
//		$db->query($q);
//		return $db->loadResult();
//
//	}
	


		/**
	* name: get_user_id_by_nickname
	* 
	* @author Max Milbers
	* @param String $nickname
	* returns int $user_id
	*/
	//Atm unused
//	function get_user_id_by_nickname(&$nickname){
//		if(empty ($nickname))return;
//		$db = new ps_DB();
//		$q  = 'SELECT `id` FROM `#__users` WHERE `username` = "'.$nickname.'"';
//		$db->query($q);
//		$userid = $db->f('id');
//		return $userid;
//	}
	
	//Atm unused
//	function get_UserEmail_by_order_id(&$order_id){
//		if(empty ($order_id))return;
//		$db = new ps_DB();
//		$q  = 'SELECT `user_id` FROM `#__{vm}_order_user_info` WHERE `order_id`="'.$order_id.'"';
//		$db->query( $q );
//		$db->next_record();
//		$user_id = $db->f('user_id');
//		$email = Vendor::get_juser_email_by_user_id($user_id);
//		return $email;
//	}
	
	/**
	* Gets the vendorId by user Id mapped by table auth_user_vendor or by the order item 
	* Assigned users cannot change storeinformations
	* ownerOnly = false should be used for users who are assigned to a vendor
	* for administrative jobs like execution of orders or managing products
	* Changing of vendorinformation should ONLY be possible by the Mainvendor who is in charge
	* @author by Max Milbers
	* @author RolandD
	* @param string $type Where the vendor ID should be taken from
	* @param mixed $value Whatever value the vendor ID should be filtered on
	* @return int Vendor ID
	*/
	public function getVendorId($type, $value, $ownerOnly=true){
		$db = JFactory::getDBO();
		switch ($type) {
			case 'order':
				$q = 'SELECT vendor_id FROM #__vm_order_item WHERE order_id='.$value;
				break;
			case 'user':
				if ($ownerOnly) {
					$q = 'SELECT `vendor_id`, `user_is_vendor` 
						FROM `#__vm_auth_user_vendor` `au` 
						LEFT JOIN `#__vm_user_info` `u` 
						ON (au.user_id = u.user_id) 
						WHERE `u`.`user_id`=' .$value;
				}
				else {
					$q  = 'SELECT `vendor_id` 
						FROM `#__vm_auth_user_vendor` 
						WHERE `user_id`=' .$value;
				}						
				break;
			case 'product':
				$q = 'SELECT vendor_id FROM #__vm_product WHERE product_id='.$value;
				break;
		}
		$db->setQuery($q);
		$vendor_id = $db->loadResult();
		if ($vendor_id) return $vendor_id;
		else {
			JError::raiseNotice(1, 'No vendor_id found for '.$value.' on '.$type.' check.');
			return 0;
		}
	}
	
	/**
	* Retrieves a DB object with the recordset of the specified fields (as array)
	* of vendor_id and ordered by lastparam 
	* If no orderby is need just set "" 
	* the country the vendor is assigned to    
	* 
	* @author Max Milbers
	* @author RolandD
	* @static 
	* @param int $vendor_id
	* @param array $fields  "" = Select *
	* @param String $orderby to order by, just the columnname Without 'ORDER BY '
	* @return ps_DB
	*/
	 
	public function getVendorFields($vendor_id, $fields=array(), $orderby="") {
		$db = JFactory::getDBO();
		$usertable= false;
		$user_id = self::getUserIdByVendorId($vendor_id);
		if (empty($user_id)) {
				//$GLOBALS['vmLogger']->err( 'Failure in Database no user_id for vendor_id '.$vendor_id.' found' );
				return;
		}
		else{
			// $GLOBALS['vmLogger']->debug( 'get_vendor_details user_id for vendor_id found' );
		}
		if (empty($fields)) {
			$fieldstring = '*';
			$usertable = true;
		}
		else {
			$showtables = array();
			$showtables[] = 'vm_vendor';
			$showtables[] = 'vm_user_info';
			$showtables[] = 'users';
			$showtables[] = 'vm_country';
			$showtables[] = 'vm_state';
			$allowedStrings = array();
			$countryFields = array();
			foreach ($showtables as $key => $table) {
				$q = "SHOW COLUMNS FROM ".$db->nameQuote('#__'.$table);
				$db->setQuery($q);
				$dbfields = $db->loadObjectList();
				if (count($dbfields) > 0) {
					foreach ($dbfields as $key => $dbfield) {
						$allowedStrings[] = $dbfield->Field;
						if ($table == 'vm_country') {
							$countryFields[] = $dbfield->Field;
						}
					}
				}
			} 
			
			/* Validate the fields */
			foreach($fields as $field){
					if(!in_array($field, $allowedStrings)){
						echo $field;
						//$GLOBALS['vmLogger']->err( 'get_vendor_fields: field not known: '.$field );	
						return;
					}
					else {
						switch ($field) {
							case 'email':
								$usertable = true;
									break;
						}
					}
				}
				/* Check if we need to include the country table */
				if(in_array($countryFields,$fields)) $countrytable = true;
				else $countrytable = false;
				
				/* Check the fields string */
				$fieldstring = '`'. implode( '`,`', $fields ) . '`';
				if(empty($fieldstring)) {
					$GLOBALS['vmLogger']->err( 'get_vendor_fields implode returns empty String: '.$fields[0] );
					return;
				}
			}
		
		$q = 'SELECT '.$fieldstring.' FROM (#__vm_vendor v, #__vm_user_info u) ';
		if($usertable) $q .= 'LEFT JOIN #__users ju ON (ju.id = u.user_id) ';
		if($countrytable) {
			$q .= 'LEFT JOIN #__vm_country c ON (u.country=c.country_id) 
				LEFT JOIN #__vm_state s ON (s.country_id=c.country_id) ';
		}
		$q .= 'WHERE v.vendor_id = '.(int)$vendor_id.' AND u.user_id = '.(int)$user_id.' ';
		
		if (!empty($orderby)) $q .= 'ORDER BY '.$orderby.' ';
		
		$db->setQuery($q);
		$vendor_fields = $db->loadObject();
		if (!$vendor_fields) {
			print '<h1>Invalid query in get_vendor_fields <br />Query: '.$q.'<br />';
			print 'vendor_id: '.$vendor_id.' and user_id: '.$user_id.' <br />' ;
			print '$orderby: '.$orderby.' and $usertable: '.$usertable.'</h1>' ;
			return ;
		}
		else return $vendor_fields;
	}
}
?>
