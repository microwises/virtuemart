<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2009 VirtueMart Dev Team - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/


class ps_vendor {
	var $_key = 'vendor_id';
	var $_table_name = '#__{vm}_vendor';
	
	/**
	 * Validates the Input Parameters onBeforeVendorAdd
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_add(&$d) {		
		return $this->validate_addUpdateVendor($d);
	}
	/**
	 * Validates the Input Parameters onBeforeVendorUpdate
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_update(&$d,&$db) {		
		return $this->validate_addUpdateVendor($d);
	}
	
		/**
	 * Validates the Input Parameters onBeforeVendorAddUpdate
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_addUpdateVendor(&$d) {
		global $vmLogger, $perm;
		
		require_once(CLASSPATH . 'imageTools.class.php' );
		if (!vmImageTools::validate_image($d,'vendor_thumb_image','vendor')) {
			return false;
		}
		if (!vmImageTools::validate_image($d,'vendor_full_image','vendor')) {
			return false;
		}

		// convert all "," in prices to decimal points.
		if (stristr($d['vendor_min_pov'],',')) {
			$d['vendor_min_pov'] = str_replace(',', '.', $d['vendor_min_pov']);
		}

		
		if (!$d['vendor_name']) {
			$d['vendor_name'] = vmRequest::getVar('vendor_nick');
		} 
		
		if (!$d['vendor_store_name']) {
			$d['vendor_store_name'] = vmRequest::getVar('vendor_nick');
		}
		
//		for ($x = 0; $x < sizeof($d); ++$x){
//			$vmLogger->info('key: '.key($d).'   value: '.current($d).'');
//			next($d);
//		}
		return True;

	}
	
		/**
	 * Adds a Vendor Record
	 * Only for Legacy, use direct addUpdateVendor($d)
	 * 
	 * @param array $d
	 * @return boolean
	 */
	function add(&$d) {		
		return $this -> addUpdateVendor($d);
	}
	
	/**
	 * Updates a Vendor (and the Store) Record
	 * Only for Legacy, use direct addUpdateVendor($d)
	 * 
	 * @param array $d
	 * @return boolean
	 */
	function update(&$d) {	
		return $this -> addUpdateVendor($d);
	}
	
	/**
	 * Add/Update a User, user information of shopper or vendor
	 * 
	 * @author Max Milbers
	 * @param array $d
	 * @return boolean
	 */
	function addUpdateVendor(&$d , $user_id) {
	
		global $vendor_currency,$vmLogger,$perm, $hVendor;
		$db = new ps_DB;
		
		if (!ps_vendor::validate_addUpdateVendor($d)) {
			return False;
		}
		
		if (!vmImageTools::process_images($d)) {
			return false;
		}
		
		$d['display_style'][1] = $hVendor -> checkCurrencySymbol( $d['display_style'][1] );		
		$d['display_style'] = implode("|", $d['display_style'] );
		
		if( empty( $d['vendor_accepted_currencies'] )) {
			$d['vendor_accepted_currencies'] = array( $vendor_currency );
		}

//		for ($x = 0; $x < sizeof($d); ++$x){
//			$vmLogger->info('key: '.key($d).'   value: '.current($d).'');
//			next($d);
//		}

		$vendor_id = $hVendor -> getVendorIdByUserId($user_id, false);
		
		$vmLogger->debug( 'addUpdateVendor vendor_id '.$vendor_id );
		$vendor_idnew = ps_vendor::setVendorInfo($d, $user_id);
		if(empty($vendor_idnew)){
			$vmLogger->err( 'setVendorInfo failed' );
			return false;
		}
		if($vendor_id===$vendor_idnew){
			if( $d['vendor_id'] == 1 ) {
				$GLOBALS['vmLogger']->info(JText::_('VM_STORE_UPDATED'));
			} else {
				$GLOBALS['vmLogger']->info(JText::_('VM_VENDOR_UPDATED'));
			}			
		}else{
			/* Insert default- shopper group */
			/* What is the sense behind it? Every shopper is related to one vendor,
			 * but what happens if one user is buying from different vendors? In which group is the user than?
			 * If every vendors has its own products and his own customers the shop could be realized with many 
			 * parallel installations. The trick with multivendor is that the customers dont have any extra effort 
			 * if they buy from different vendors.
			 * That a vendor has the possibilty to get a list of his customers makes a bit sense, but is very
			 * unimportant, very important is a list that the vendor can see all his products, orders, the money he should
			 * get by the shop and the commission he has to pay.   by Max Milbers
			 * 		 */
//			$q = "INSERT INTO #__{vm}_shopper_group (";
//			$q .= "`vendor_id`,";
//			$q .= "`shopper_group_name`,";
//			$q .= "`shopper_group_desc`,`default`) VALUES ('";
//			$q .= $d["vendor_id"] . "',";
//			$q .= "'-default-',";
//			$q .= "'Default shopper group for ".$d["vendor_name"]."','1')";
//			$db->query($q);
			$GLOBALS['vmLogger']->info(JText::_('VM_VENDOR_ADDED'));
		}

		unset($db);
		return True;	
	}

	/**
	 * Inserts or Updates the vendor information
	 * Attention without Validation.
	 * Important use validate_addUpdateVendor.
	 * @author Max Milbers
	 * @param $d array like $keyValues = array('email' => $emailvalue, 'last_name' => $lastname);
	 * @param int $vendor_id
	 * @param $and An 'AND' condition like 'AND column = value'
	 * return $vendor_id
	 */
	function setVendorInfo(&$d, $user_id,$and=""){
		
		global $hVendor;

		$db = new ps_DB;
		
		$timestamp = time();	
		$vendor_currency_display_style="";
		if(empty($d['display_style'])){
			$vendor_currency_display_style = $d['vendor_currency_display_style'];
		}else{
			$vendor_currency_display_style = $d['display_style'];
		}
		//Split the array $d in two, because tha data is on two different tables
		$fields = array(
		
				'vendor_name' => $d['vendor_name'],
				'vendor_phone' => $d['vendor_phone'],
				'vendor_store_name' => $d['vendor_store_name'],
				'vendor_store_desc' => $d['vendor_store_desc'],
//				'vendor_category_id' => $d['vendor_name'],
				'vendor_thumb_image' => $d['vendor_thumb_image'],
				'vendor_full_image' => $d['vendor_full_image'],
				'vendor_currency' => $d['vendor_currency'],
				'cdate' => $d['cdate'],
				'mdate' => $timestamp,
//				'vendor_image_path' => $d['vendor_name'],
				'vendor_terms_of_service' => $d['vendor_terms_of_service'],
				'vendor_url' => $d['vendor_url'],
				'vendor_min_pov' => $d['vendor_min_pov'],
				'vendor_freeshipping' => $d['vendor_freeshipping'],
				'vendor_currency_display_style' => $vendor_currency_display_style, //hmm or $d['display_style']
				
//				'vendor_accepted_currencies' => implode( ',', vmRequest::getVar('vendor_accepted_currencies') ),
				'vendor_accepted_currencies' => implode( ',', $d['vendor_accepted_currencies'])
//				'vendor_address_format' => $d['vendor_name'],
//				'vendor_date_format' => $d['vendor_name'],

				);
		$requ = "";		
		//I think this should stay for Add and Update and deleted in the array init above
		//there are more variables to handle like this to prevent notices
		//This is not really a nice solution and will be redesigned
		if (!empty($d['vendor_category_id'])) {
			$requ = vmRequest::getInt('vendor_category_id');
			if(isset($requ)){
				$fields['vendor_category_id'] = vmRequest::getInt('vendor_category_id');
			}else{
				$fields['vendor_category_id'] = $d['vendor_category_id'];
			}	
		}
		if (!empty($d['vendor_image_path'])) {
//			$fields['vendor_image_path'] = vmRequest::getVar('vendor_image_path');
			$requ = vmRequest::getInt('vendor_image_path');
			if(isset($requ)){
				$fields['vendor_image_path'] = vmRequest::getInt('vendor_image_path');
			}else{
				$fields['vendor_image_path'] = $d['vendor_image_path'];
			}
		}
		if (!empty($d['vendor_address_format'])) {
//			$fields['vendor_address_format'] = vmRequest::getVar('vendor_address_format');
			$requ = vmRequest::getInt('vendor_address_format');
			if(isset($requ)){
				$fields['vendor_address_format'] = vmRequest::getInt('vendor_address_format');
			}else{
				$fields['vendor_address_format'] = $d['vendor_address_format'];
			}
		}
		if (!empty($d['vendor_date_format'])) {
//			$fields['vendor_date_format'] = vmRequest::getVar('vendor_date_format');
			$requ = vmRequest::getInt('vendor_date_format');
			if(isset($requ)){
				$fields['vendor_date_format'] = vmRequest::getInt('vendor_date_format');
			}else{
				$fields['vendor_date_format'] = $d['vendor_date_format'];
			}
		}

		//Setting fields empty is senseless people should use a dummy (-),... makes the life for devs a lot easier
		$fields = array_filter($fields);
		
		if(empty($hVendor)){
			require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'vendorhelper.php');
			$hVendor = new Vendor;
		}
		$vendor_id = $hVendor -> getVendorIdByUserId($user_id, false);
		if( empty( $vendor_id ) ) { // INSERT NEW USER/SHOPPER
			$action = 'INSERT';
			$whereAnd = "";
			$add = true;
			JError::raiseNotice('SOME_ERROR_CODE', 'setVendorInfo ADD');
			$fields['cdate'] = $timestamp; // add a creation date only if this is an INSERT
		}else{
			$action = 'UPDATE';
			$add = false;
			$whereAnd = 'WHERE `vendor_id`='.(int)$vendor_id . $and;
			
			JError::raiseNotice('SOME_ERROR_CODE', 'setVendorInfo UPDATE');
		}
		
		$db->buildQuery( $action, '#__{vm}_vendor', $fields, $whereAnd );
		if( $db->query() == false ) {
			JError::raiseError('SOME_ERROR_CODE','setVendorInfo '.$action.' set user_info failed for $vendor_id '.$vendor_id);
			return false;
		}else{
			if($add){
				// Get the assigned vendor_id //
				$_REQUEST['vendor_id'] = $vendor_id = $db->last_insert_id();
			}
//			$vmLogger->debug( ' setVendorInfo $user_id'. $user_id);
			require_once(CLASSPATH. 'ps_user.php');

			if (!empty($user_id)) {
				
				$auth_user_vendor = array('user_id' => $user_id, 'vendor_id' => $vendor_id);
				if(!$add){
					$whereAnd = 'WHERE `user_id`= "'.$user_id.'"';
				}
				$db->buildQuery( $action, '#__{vm}_auth_user_vendor', $auth_user_vendor, $whereAnd );
				if( $db->query() == false ) {
					JError::raiseError('SOME_ERROR_CODE', JText::_('Failed to associate the vendor to a user') );
				}

				$user_is_vendor = array('user_is_vendor' => 1);

				$whereAnd = 'WHERE `user_id`= "'.$user_id.'"';

				$db->buildQuery( 'UPDATE', '#__{vm}_user_info', $user_is_vendor, $whereAnd );
				if( $db->query() == false ) {
					JError::raiseError('SOME_ERROR_CODE', JText::_('Failed to set the user as vendor') );
				}
//				$GLOBALS['vmLogger']->debug('setVendorInfo vendor_id= "'.$vendor_id.'" user_id="'.$user_id.'"');		
								
			}else {
//				if(!$perm->check( 'admin' )){
					JError::raiseError('SOME_ERROR_CODE', 'No matching Virtuemart shopper found; This is not supposed to happen' );
					return false;
//				}		
			}
		}
		return $vendor_id;
	}
	
		/**
	 * Retrieves a DB object with the recordset of the specified vendor
	 * and the country it is assigned to    
	 * completly rewritten by Max Milbers
	 * @author Max Milbers
	 * @static 
	 * @param int $vendor_id
	 * @return ps_DB
	 */

	function get_vendor_details($vendor_id) {
		global $hVendor; 
		$db = new ps_DB();		
		$user_id = $hVendor -> getUserIdByVendorId($vendor_id);
		if (empty($user_id)) {
				$GLOBALS['vmLogger']->debug( 'Failure in Database no user_id for vendor_id '.$vendor_id.' found' );
				return $db;
		}else{
//			$GLOBALS['vmLogger']->debug( 'get_vendor_details user_id for vendor_id found' );
		}
		
		$q   = 'SELECT * FROM (`#__{vm}_vendor` `v`, `#__{vm}_user_info` `u`) 
				LEFT JOIN `#__users` `ju` ON (`ju`.`id` = `u`.`user_id`) 
				LEFT JOIN `#__{vm}_country` `c` ON (`u`.`country`=`c`.`country_id`) ' .
			   'LEFT JOIN `#__{vm}_state` `s` ON  (`s`.`country_id`=`c`.`country_id`) ' .
			   'WHERE `v`.`vendor_id`='.(int)$vendor_id.' AND `u`.`user_id`='.$user_id.' ';

//		$q   = 'SELECT * FROM (`#__{vm}_vendor` v, `#__{vm}_user_info` u) 
//				LEFT JOIN #__users ju ON (ju.id = u.user_id) 
//				LEFT JOIN #__{vm}_country c ON (u.country=c.country_2_code OR u.country=c.country_3_code) 	
//				LEFT JOIN #__{vm}_state s ON (u.state=s.state_2_code AND s.country_id=c.country_id) 
//				WHERE `v`.`vendor_id`='.(int)$vendor_id.' AND `u`.`user_id`='.$user_id.' ';

						
		$db->query($q);
		$db->next_record();

		return $db;
	}
	
		/**
	 * Retrieves a DB object with the recordset of the specified fields (as array)
	 * of vendor_id and ordered by lastparam 
	 * If no orderby is need just set "" 
	 * the country the vendor is assigned to    
	 * 
	 * @author Max Milbers
	 * @static 
	 * @param int $vendor_id
	 * @param array $fields  "" = Select *
	 * @param String $orderby to order by, just the columnname Without 'ORDER BY '
	 * @return ps_DB
	 */
	 
	function get_vendor_fields($vendor_id, $fields=array(), $oderby="") {
		global $hVendor;
//		return ps_vendor::get_vendor_details($vendor_id);
		$db = new ps_DB();
		$usertable= false;
		$user_id = $hVendor -> getUserIdByVendorId($vendor_id);
		if (empty($user_id)) {
				$GLOBALS['vmLogger']->err( 'Failure in Database no user_id for vendor_id '.$vendor_id.' found' );
				return;
		}else{
//			$GLOBALS['vmLogger']->debug( 'get_vendor_details user_id for vendor_id found' );
		}
		if( empty( $fields )) {
			$fieldstring = '*';
			$usertable = true;
		}
		else {
		//All fieldnames from the tables vm_vendor, vm_user_info, __users, vm_country and vm_state
			$allowedStrings = array('vendor_id', 'vendor_name', 'vendor_phone', 'vendor_store_name', 
			'vendor_store_desc', 'vendor_category_id', 'vendor_thumb_image', 'vendor_full_image', 'vendor_currency', 'cdate',
			'mdate', 'vendor_image_path', 'vendor_terms_of_service', 'vendor_url', 'vendor_min_pov', 'vendor_freeshipping', 
			'vendor_currency_display_style', 'vendor_accepted_currencies', 'vendor_address_format', 'vendor_date_format',
			'address_1','address_2','vendor_url','city','state', 'country', 'title', 'last_name', 'first_name', 'middle_name', 'phone_1',
			'phone_2', 'fax', 'email','zip',
			'state_id', 'country_id','state_name','state_3_code','state_2_code',
			'country_id','zone_id','country_name','country_3_code','country_2_code');
			
			
			foreach($fields as $field){
				if(!in_array($field, $allowedStrings)){
					$GLOBALS['vmLogger']->err( 'get_vendor_fields: field not known: '.$field );	
					return;
				}
			}
			//Probably faster in the foreach
			if(in_array('email',$fields)){
				$usertable = true;
			}
			//Probably faster in the foreach
			$countryFields = array('state_id', 'country_id','state_name','state_3_code','state_2_code',
			'country_id','zone_id','country_name','country_3_code','country_2_code');
			if(in_array($countryFields,$fields)){
				$countrytable = true;
			}
			else {
				$countrytable = false;
			}
			$fieldstring = '`'. implode( '`,`', $fields ) . '`';
			if(empty($fieldstring)){
				$GLOBALS['vmLogger']->err( 'get_vendor_fields implode returns empty String: '.$fields[0] );
				return;
			}	
		}    
		
		$q = 'SELECT '.$fieldstring.' FROM (#__vm_vendor v, #__vm_user_info u) ';
		if($usertable){
			$q .= 'LEFT JOIN #__users ju ON (ju.id = u.user_id) ';
		}
		if($countrytable){
			$q .= 'LEFT JOIN #__vm_country c ON (u.country=c.country_id) 
			LEFT JOIN #__vm_state s ON (s.country_id=c.country_id) ';
		}	
		$q .= 'WHERE v.vendor_id = '.(int)$vendor_id.' AND u.user_id = '.(int)$user_id.' ';
		if(!empty($orderby)){
			$q .= 'ORDER BY '.$orderby.' ';
		}				
		$db->query($q);
		
		if( ! $db->next_record() ) {
			print '<h1>Invalid query in get_vendor_fields <br />Query: '.$q.'<br />';
			print 'vendor_id: '.$vendor_id.' and user_id: '.$user_id.' <br />' ;
			print '$orderby: '.$orderby.' and $usertable: '.$usertable.'</h1>' ;
			return ;
		}else{
			return $db;
		}
	}
	
	/**
	 * Validates the Input Parameters onBeforeVendorDelete
	 *
	 * @param int $vendor_id
	 * @param array $d
	 * @return boolean
	 */
	function validate_delete( $vendor_id, &$d) {
		global $vmLogger;
		$db = new ps_DB;

//		if (!$d["vendor_id"]) {
		if (empty($vendor_id)) {
			$vmLogger->err( 'Please select a vendor to delete.' );
			return False;
		}

		$q = "SELECT vendor_id FROM #__{vm}_product where vendor_id='$vendor_id'";
		$db->query($q);
		if ($db->next_record()) {
			$vmLogger->err( 'This vendor still has products. Delete all products first.' );
			return False;
		}

		/* Get the image filenames from the database */
		$db = new ps_DB;
		$q  = "SELECT vendor_thumb_image,vendor_full_image " .
				"FROM #__{vm}_vendor WHERE vendor_id='$vendor_id'";
		$db->query($q);
		$db->next_record();
		
		require_once(CLASSPATH . 'imageTools.class.php' );
		/* Validate vendor_thumb_image */
		$d["vendor_thumb_image_curr"] = $db->f("vendor_thumb_image");
		$d["vendor_thumb_image_name"] = "none";
		if (!vmImageTools::validate_image($d,"vendor_thumb_image","vendor")) {
			return false;
		}

		/* Validate vendor_full_image */
		$d["vendor_full_image_curr"] = $db->f("vendor_full_image");
		$d["vendor_full_image_name"] = "none";
		if (!vmImageTools::validate_image($d,"vendor_full_image","vendor")) {
			return false;
		}
		unset($db);
		return True;
	}
	
	/**************************************************************************
	* name: delete()
	* created by: unknown changed by Max Milbers
	* description:
	* parameters:
	* returns:
	**************************************************************************/
	
	/**
	* Controller for Deleting Records.
	*/
	function delete(&$d) {
		global $vars;
		$record_id = $vars["vendor_id"];
//		global $vmLogger, $vars;

		//This was not working and there is no possibilty in the gui to delete more than one vendor at once
//		if( is_array( $record_id)) {
//			foreach( $record_id as $record) {
//				if( !$this->delete_vendor_record( $record, $d ))
//				return false;
//			}
//			return true;
//		}
//		else {
			return $this->delete_vendor_record( $record_id, $d );
//		}
	}
	/**
	* Deletes one Record.
	* @author unknown changed by Max Milbers
	*/
	function delete_vendor_record( $vendor_id, &$d ) {
		global $vmLogger;
		$db = new ps_DB();
		$vmLogger->info( "'delete_record $vendor_id '.$vendor_id" );
		if (!$this->validate_delete( $vendor_id, $d)) {
			$vmLogger->err( 'Deleting of the vendor couldnt be done' );
			return False;
		} 

		/* Delete Image files */
		if (!vmImageTools::process_images($d)) {
			$vmLogger->err( 'Deleting of the vendor couldnt be done' );
			return false;
		}

		$user_id = ps_vendor::getUserIdByVendorId($vendor_id);
		
		$user_update = 'UPDATE `#__{vm}_user_info` SET `user_is_vendor` = "0" WHERE `user_id`="'.$user_id.'"';
		$db->query($user_update);
		
		$q = 'DELETE FROM `#__{vm}_auth_user_vendor` where `vendor_id`="'.$vendor_id.'"';
		$db->query($q);
		
		$q = 'DELETE FROM `#__{vm}_vendor` where `vendor_id`="'.$vendor_id.'"';
		$db->query($q);
		unset($db);
		return True;
	}
	

//	/**************************************************************************
//	** name: get_field   DANGEROUS function need to rewritten IF you get a failure use get_vendor_fields()
//	** created by: pablo
//	** description:
//	** parameters:
//	** returns:
//	***************************************************************************/
//	function get_field($vendor_id, $field_name) {
//		$db = new ps_DB;
//
//		$q = "SELECT $field_name FROM #__{vm}_vendor WHERE vendor_id='$vendor_id'";
//		$db->query($q);
//		if ($db->next_record()) {
//			return $db->f($field_name);
//		}
//		else {
//			return False;
//		}
//	}



//	/**
//	* @param string The vendor_currency_display_code
//	*   FORMAT: 
//    1: id, 
//    2: CurrencySymbol, 
//    3: NumberOfDecimalsAfterDecimalSymbol,
//    4: DecimalSymbol,
//    5: Thousands separator
//    6: Currency symbol position with Positive values :
//									// 0 = '00Symb'
//									// 1 = '00 Symb'
//									// 2 = 'Symb00'
//									// 3 = 'Symb 00'
//    7: Currency symbol position with Negative values :
//									// 0 = '(Symb00)'
//									// 1 = '-Symb00'
//									// 2 = 'Symb-00'
//									// 3 = 'Symb00-'
//									// 4 = '(00Symb)'
//									// 5 = '-00Symb'
//									// 6 = '00-Symb'
//									// 7 = '00Symb-'
//									// 8 = '-00 Symb'
//									// 9 = '-Symb 00'
//									// 10 = '00 Symb-'
//									// 11 = 'Symb 00-'
//									// 12 = 'Symb -00'
//									// 13 = '00- Symb'
//									// 14 = '(Symb 00)'
//									// 15 = '(00 Symb)'
//    	EXAMPLE: ||&euro;|2|,||1|8
//	* @return string
//	*/
//	function get_currency_display_style( $style ) {
//	
//		$array = explode( "|", $style );
//		$display = Array();
//		$display["id"] = @$array[0];
//		$display["symbol"] = @$array[1];
//		$display["nbdecimal"] = @$array[2];
//		$display["sdecimal"] = @$array[3];
//		$display["thousands"] = @$array[4];
//		$display["positive"] = @$array[5];
//		$display["negative"] = @$array[6];
//		return $display;
//	}
//	/**
//	 * 
//	 * MUST-TO , functions calls need to be rewritten !!!
//	 * mosttime $vendor_id is set to 1;
//	 * Returns the formatted Store Address
//	 *	@author someone, completly rewritten by Max Milbers
//	 * @param boolean $use_html
//	 * @return String
//	 */
//	function formatted_store_address( $use_html=false, $vendor_id ) {
//		
//		if(empty($vendor_id)){
//			$GLOBALS['vmLogger']->err( 'formatted_store_address no vendor_id given' );
//			return;
//		}
//		
//		$db = ps_vendor::get_vendor_details($vendor_id);
//		
//		$address_details['name'] = $db->f("vendor_store_name");;
//		$address_details['address_1'] = $db->f("address_1");
//		$address_details['address_2'] = $db->f("address_2");
//		$address_details['state'] = $db->f("state");
//		$address_details['state_name'] = $db->f("state_name");
//		$address_details['city'] = $db->f("city");
//		$address_details['zip'] = $db->f("zip");
//		$address_details['country'] = $db->f("country");
//		$address_details['phone'] = $db->f("vendor_phone");
//		$address_details['email'] = $db->f("email");
//		$address_details['fax'] = $db->f("fax");
//		$address_details['url'] = $db->f("url");
//		
//		return vmFormatAddress( $address_details, $use_html, true);
//	}
}
?>
