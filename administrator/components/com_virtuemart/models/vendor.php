<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author RolandD, RickG
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the model framework
jimport( 'joomla.application.component.model');

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
* Model for VirtueMart Vendors
*
* @package		VirtueMart
*/
class VirtueMartModelVendor extends VmModel {

	function __construct() {
		parent::__construct();
	}

    /**
	* name: getLoggedVendor
	* Checks which $vendorId has the just logged in user.
	* @author Max Milbers
	* @param @param $ownerOnly returns only an id if the vendorOwner is logged in (dont get confused with storeowner)
	* returns int $vendorId
	*/
	function getLoggedVendor($ownerOnly = true){
		$user = JFactory::getUser();
		$userId = $user->id;
		if(isset($userId)){
			$vendorId = self::getVendorId('user', $userId, $ownerOnly);
			return $vendorId;
		}else{
			JError::raiseNotice(1,'$virtuemart_user_id empty, no user logged in');
			return 0;
		}

	}

    /**
	* Retrieve the vendor details from the database.
	*
	* @author Max Milbers
	* @return object Vendor details
	*/
	function getVendor() {

        if (empty($this->_data)) {

	    	$this->_data = $this->getTable('vendors');
   			$this->_data->load($this->_id);

		    // Convert ; separated string into array
		    if ($this->_data->vendor_accepted_currencies) {
				$this->_data->vendor_accepted_currencies = explode(',', $this->_data->vendor_accepted_currencies);
		    }
		    else{
				$this->_data->vendor_accepted_currencies = array();
		    }

		    $xrefTable = $this->getTable('vendor_medias');
			$this->_data->virtuemart_media_id = $xrefTable->load($this->_id);

//          	if($this->_data->virtuemart_media_id){
//  				$this->_data->virtuemart_media_id = explode(',',$this->_data->virtuemart_media_id);
//  			}
//			if($withUserData){
//			    $query = "SELECT virtuemart_user_id FROM #__vm_auth_user_vendor ";
//			    $query .= "WHERE virtuemart_vendor_id = '". $this->_id ."'";
//			    $this->_db->setQuery($query);
//			    $userVendor = $this->_db->loadObject();
//
//			    // Get user_info table data
//			    $this->_data->userId = (isset($userVendor->virtuemart_user_id) ? $userVendor->virtuemart_user_id : 0);
//
//				$userInfoTable = $this->getTable('userinfos');
//	    		$userInfoTable->load((int)$this->_id);
//	    		$this->_data->userInfo = $userInfoTable;
//
//				$vendorJUser = JFactory::getUser($this->_id);
//		//	   	$user_table = $this->getTable('user');
//		//	    $user_table->load((int)$userId);
//			    $this->_data->jUser = $vendorJUser;

//
//			}

		}

//       	if (!$this->_data) {
//	    	$this->_data = new stdClass();
//	    	$this->_id = 0;
//		}

		return $this->_data;
	}

	/**
	* Retrieve a list of vendors
	* todo only names are needed here, maybe it should be enhanced (loading object list is slow)
	* todo add possibility to load without limit
	* @author RickG
	* @author Max Milbers
	* @return object List of vendors
	*/
	public function getVendors() {

		$query = 'SELECT * FROM `#__virtuemart_vendors` ';
		$query .= 'ORDER BY `#__virtuemart_vendors`.`virtuemart_vendor_id`';
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		return $this->_data;
	}

	/**
	 * Find the user id given a vendor id
	 *
	 * @author Max Milbers
	 * @param int $virtuemart_vendor_id
	 * @return int $virtuemart_user_id
	 */
	function getUserIdByVendorId($vendorId=0) {
		//this function is used static, needs its own db
		$db = JFactory::getDBO();
		if (empty($vendorId)) return;
		else {
			$query = 'SELECT `virtuemart_user_id` FROM `#__virtuemart_vmusers` WHERE `virtuemart_vendor_id`=' . $this->_db->Quote((int)$vendorId)  ;
			$db->setQuery($query);
			$result = $db->loadResult();
			return (isset($result) ? $result : 0);
		}
	}


	/**
	 * Bind the post data to the vendor table and save it
     * This function DOES NOT safe information which is in the vmusers or vm_user_info table
     * It only stores the stuff into the vendor table
     * @author RickG
     * @author Max Milbers
     * @return boolean True is the save was successful, false otherwise.
	 */
    function store($data){

	$table = $this->getTable('vendors');

	// Store multiple selectlist entries as a ; separated string
	if (key_exists('vendor_accepted_currencies', $data) && is_array($data['vendor_accepted_currencies'])) {
	    $data['vendor_accepted_currencies'] = implode(',', $data['vendor_accepted_currencies']);
	}

	// Bind the form fields to the vendor table
	if (!$table->bind($data)) {
	    $this->setError($table->getError());
	    $this->setError($table->getDBO()->getErrorMsg());
	    return false;
	}

	// Make sure the vendor record is valid
	if (!$table->check()) {
	    $this->setError($table->getDBO()->getErrorMsg());
	    return false;
	}

	// Save the vendor to the database
	if (!$table->store()) {
	    $this->setError($table->getDBO()->getErrorMsg());
	    return false;
	}

	//set vendormodel id to the lastinserted one
	$dbv = $table->getDBO();
	if(empty($this->_id)) $this->_id = $dbv->insertid();

	/* Process the images */

	if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
	$xrefTable = $this->getTable('vendor_medias');
	$mediaModel = new VirtueMartModelMedia();
	$mediaModel->storeMedia($data,$xrefTable,'vendor');

	return $this->_id;

	}

	/**
	 * Get the vendor specific currency
	 *
	 * @author Oscar van Eijk
	 * @param $_vendorId Vendor ID
	 * @return string Currency code
	 */
	function getVendorCurrency ($_vendorId)
	{
		$db = JFactory::getDBO();

		$q = 'SELECT *  '
			. 'FROM `#__virtuemart_currencies` AS c'
			. ',    `#__virtuemart_vendors` AS v '
			. 'WHERE v.virtuemart_vendor_id = '.$_vendorId . ' '
			. 'AND   v.vendor_currency = c.virtuemart_currency_id';
		$db->setQuery($q);
		$r = $db->loadObject();
		return $r;
	}

	/**
	 * Retrieve a lost of vendor objects
	 *
	 * @author Oscar van Eijk
	 * @return Array with all Vendor objects
	 */
	function getVendorCategories()
	{
		$_q = 'SELECT * FROM `#__vm_vendor_category`';
		$this->_db->setQuery($_q);
		return $this->_db->loadObjectList();
	}

	function getUserIdByOrderId( &$virtuemart_order_id){
		if(empty ($virtuemart_order_id))return;
		$q  = "SELECT `virtuemart_user_id` FROM `#__virtuemart_orders` WHERE `virtuemart_order_id`='$virtuemart_order_id'";
//		$db->query( $q );
		$this->_db->setQuery($q);

//		if($db->next_record()){
		if($this->_db->query()){
//			$virtuemart_user_id = $db->f('virtuemart_user_id');
			return $this->_db->loadResult();
		}else{
			JError::raiseNotice(1,'Error in DB $virtuemart_order_id '.$virtuemart_order_id.' dont have a virtuemart_user_id');
			return 0;
		}
	}

	/**
	 * 		virtuemart_state_id 	virtuemart_country_id 	state_name 	state_3_code 	state_2_code
	 		1 			223 		Alabama 	ALA 			AL


	 		virtuemart_country_id 	virtuemart_worldzone_id 	country_name 	country_3_code 	country_2_code
			1 			1 			Afghanistan 	AFG 			AF
	 */


	/**
	 * ATM Unused !
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
		if(empty($value)) return 0;
		//static call used, so we need our own db instance
		$db = JFactory::getDBO();
		switch ($type) {
			case 'order':
				$q = 'SELECT virtuemart_vendor_id FROM #__virtuemart_order_items WHERE virtuemart_order_id='.$value;
				break;
			case 'user':
				if ($ownerOnly) {
					$q = 'SELECT `virtuemart_vendor_id`
						FROM `#__virtuemart_vmusers` `au`
						LEFT JOIN `#__virtuemart_userinfos` `u`
						ON (au.virtuemart_user_id = u.virtuemart_user_id)
						WHERE `u`.`virtuemart_user_id`=' .$value;
				}
				else {
					$q  = 'SELECT `virtuemart_vendor_id` FROM `#__virtuemart_vmusers` WHERE `virtuemart_user_id`= "' .$value.'" ';
				}
				break;
			case 'product':
				$q = 'SELECT virtuemart_vendor_id FROM #__virtuemart_products WHERE virtuemart_product_id='.$value;
				break;
		}
		$db->setQuery($q);
		$virtuemart_vendor_id = $db->loadResult();
		if ($virtuemart_vendor_id) return $virtuemart_vendor_id;
		else {
			return 0;
//			if($type!='user'){
//				return 0;
//			} else {
//				JError::raiseNotice(1, 'No virtuemart_vendor_id found for '.$value.' on '.$type.' check.');
//				return 0;
//			}
		}
	}

	/**
	 * This function gives back the storename for the given vendor.
	 * This function is just for improving speed. When you need the whole vendor table, use getVendor
	 *
	 * @author Max Milbers
	 */
	public function getVendorName($virtuemart_vendor_id=1){
		$query = 'SELECT `vendor_store_name` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id` = "'.$virtuemart_vendor_id.'" ';
		$this->_db->setQuery($query);
		if($this->_db->query()) return $this->_db->loadResult(); else return '';
	}

	/**
	 * This function gives back the email for the given vendor.
	 * This function is just for improving speed. When you need the whole vendor data, use getUser in the usermodell
	 *
	 * @author Max Milbers
	 */

 	public function getVendorEmail($virtuemart_vendor_id){
 		$virtuemart_user_id = self::getUserIdByVendorId($virtuemart_vendor_id);
 		if(!empty($virtuemart_user_id)){
  			$query = 'SELECT `email` FROM `#__users` WHERE `id` = "'.$virtuemart_user_id.'" ';
			$this->_db->setQuery($query);
			if($this->_db->query()) return $this->_db->loadResult(); else return '';
 		}
		return '';
 	}

	/**
	 * ATTENTION this function ist atm NOT USED
	 * Create a formatted vendor address
	 * mosttime $virtuemart_vendor_id is set to 1;
	 * Returns the formatted Store Address
	 * @author someone, completly rewritten by Max Milbers, RolandD
	 * @param integer $virtuemart_vendor_id
	 * @return String
	 */
	function formatted_store_address($virtuemart_vendor_id) {

		echo 'Developer notice, you used an old legacy function, you may do it, but correct it first <br />';
		echo 'But be aware that the class VmStore is obsolete!';die;
		if(empty($virtuemart_vendor_id)){
			JError::raiseWarning(1,'formatted_store_address no virtuemart_vendor_id given' );
			return;
		}
		else {
			//Todo this query is broken due the changes with the tables
			$this->_db = JFactory::getDBO();
			$q = "SELECT vendor_store_name AS storename, address_1, address_2, email, fax,
				s.state_2_code AS state, s.state_name AS statename, city, zip,
				c.country_name AS country, vendor_phone, vendor_url AS url, phone_1 as phone
				FROM #__virtuemart_vendors v
				LEFT JOIN #__virtuemart_vmuser_shoppergroups x
				ON x.virtuemart_vendor_id = v.virtuemart_vendor_id
				LEFT JOIN #__virtuemart_userinfos u
				ON u.virtuemart_user_id = x.virtuemart_user_id
				LEFT JOIN #__users j
				ON j.id = u.virtuemart_user_id
				LEFT JOIN #__virtuemart_countries c ON c.virtuemart_country_id = u.virtuemart_country_id
				LEFT JOIN #__virtuemart_states s ON s.virtuemart_state_id = u.virtuemart_state_id
				WHERE v.virtuemart_vendor_id = ".$virtuemart_vendor_id."
				AND address_type = 'BT'";
			$this->_db->setQuery($q);
			$vendor = $this->_db->loadObject();

//			$vendor_address_format = VmStore::get('vendor_address_format');
			$vendor_address_format = '';
			$store_address = str_ireplace('{storename}', $vendor->storename, $vendor_address_format);
			$store_address = str_ireplace('{address_1}', $vendor->address_1, $store_address);
			$store_address = str_ireplace('{address_2}', $vendor->address_2, $store_address);
			$store_address = str_ireplace('{state}', $vendor->state, $store_address);
			$store_address = str_ireplace('{statename}', $vendor->statename, $store_address);
			$store_address = str_ireplace('{city}', $vendor->city, $store_address);
			$store_address = str_ireplace('{zip}', $vendor->zip, $store_address);
			$store_address = str_ireplace('{country}', $vendor->country, $store_address);
			$store_address = str_ireplace('{phone}', $vendor->phone, $store_address);
			$store_address = str_ireplace('{email}', $vendor->email, $store_address);
			$store_address = str_ireplace('{fax}', $vendor->fax, $store_address);
			$store_address = str_ireplace('{url}', $vendor->url, $store_address);

			return nl2br($store_address);
		}
	}

	/**
	 * Since a category dont need always an image, we can attach them to the category with this function.
	 * The parameter takes a single category or arrays of categories, look at FE/views/virtuemart/view.html.php
	 * for an exampel using it
	 *
	 * @author Max Milbers
	 * @param object $categories
	 */
	public function addImagesToVendor($vendor=0){

		if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
		if(empty($this->mediaModel))$this->mediaModel = new VirtueMartModelMedia();

		$this->mediaModel->attachImages($vendor,'vendor','image');

	}

}
//pure php no closing tag
//	/**
//	* Retrieves a DB object with the recordset of the specified fields (as array)
//	* of virtuemart_vendor_id and ordered by lastparam
//	* If no orderby is need just set ""
//	* the country the vendor is assigned to
//	*
//	* @author Max Milbers
//	* @author RolandD
//	* @static
//	* @param int $virtuemart_vendor_id
//	* @param array $fields  "" = Select *
//	* @param String $orderby to order by, just the columnname Without 'ORDER BY '
//	* @return ps_DB
//	*/
//
//	public function getVendorFields($virtuemart_vendor_id, $fields=array(), $orderby="") {
//
//		JError::raiseNotice(1,'Attention you use the obsolete function getVendorFields');
//		//used static
//		$db = JFactory::getDBO();
//		$usertable= false;
//		$virtuemart_user_id = self::getUserIdByVendorId($virtuemart_vendor_id);
//		if (empty($virtuemart_user_id)) {
//				//JError::raiseNotice(1, 'Failure in Database no virtuemart_user_id for virtuemart_vendor_id '.$virtuemart_vendor_id.' found' );
//				return;
//		}
//		else{
//			// JError::raiseNotice(1, 'get_vendor_details virtuemart_user_id for virtuemart_vendor_id found' );
//		}
//		if (empty($fields)) {
//			$fieldstring = '*';
//			$usertable = true;
//		}
//		else {
//			$showtables = array();
//			$showtables[] = 'vm_vendor';
//			$showtables[] = 'vm_user_info';
//			$showtables[] = 'users';
//			$showtables[] = 'vm_country';
//			$showtables[] = 'vm_state';
//			$allowedStrings = array();
//			$countryFields = array();
//			foreach ($showtables as $key => $table) {
//				$q = "SHOW COLUMNS FROM ".$db->nameQuote('#__'.$table);
//				$db->setQuery($q);
//				$dbfields = $db->loadObjectList();
//				if (count($dbfields) > 0) {
//					foreach ($dbfields as $key => $dbfield) {
//						$allowedStrings[] = $dbfield->Field;
//						if ($table == 'vm_country') {
//							$countryFields[] = $dbfield->Field;
//						}
//					}
//				}
//			}
//
//			/* Validate the fields */
//			foreach($fields as $field){
//					if(!in_array($field, $allowedStrings)){
//						echo $field;
//						//JError::raiseNotice(1, 'get_vendor_fields: field not known: '.$field );
//						return;
//					}
//					else {
//						switch ($field) {
//							case 'email':
//								$usertable = true;
//									break;
//						}
//					}
//				}
//				/* Check if we need to include the country table */
//				if(in_array($countryFields,$fields)) $countrytable = true;
//				else $countrytable = false;
//
//				/* Check the fields string */
//				$fieldstring = '`'. implode( '`,`', $fields ) . '`';
//				if(empty($fieldstring)) {
//					JError::raiseNotice(1, 'get_vendor_fields implode returns empty String: '.$fields[0] );
//					return;
//				}
//			}
//
//		$q = 'SELECT '.$fieldstring.' FROM (#__virtuemart_vendors v, #__virtuemart_userinfos u) ';
//		if($usertable) $q .= 'LEFT JOIN #__users ju ON (ju.id = u.virtuemart_user_id) ';
//		if($countrytable) {
//			$q .= 'LEFT JOIN #__virtuemart_countries c ON (u.country=c.virtuemart_country_id)
//				LEFT JOIN #__virtuemart_states s ON (s.virtuemart_country_id=c.virtuemart_country_id) ';
//		}
//		$q .= 'WHERE v.virtuemart_vendor_id = '.(int)$virtuemart_vendor_id.' AND u.virtuemart_user_id = '.(int)$virtuemart_user_id.' ';
//
//		if (!empty($orderby)) $q .= 'ORDER BY '.$orderby.' ';
//
//		$db->setQuery($q);
//		$vendor_fields = $db->loadObject();
//		if (!$vendor_fields) {
//			print '<h1>Invalid query in get_vendor_fields <br />Query: '.$q.'<br />';
//			print 'virtuemart_vendor_id: '.$virtuemart_vendor_id.' and virtuemart_user_id: '.$virtuemart_user_id.' <br />' ;
//			print '$orderby: '.$orderby.' and $usertable: '.$usertable.'</h1>' ;
//			return ;
//		}
//		else return $vendor_fields;
//	}

//}

