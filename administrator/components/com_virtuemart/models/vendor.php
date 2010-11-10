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


/**
* Model for VirtueMart Vendors
*
* @package		VirtueMart
*/
class VirtueMartModelVendor extends JModel {

    /**
    * Vendor Id
    *
    * @var $_id;
    */
    private $_id;

    /**
    * Vendor detail record
    *
    * @var object;
    */
    private $_data;


    /**
    * Constructor for the Vendor model.
    */
    function __construct()
    {
        parent::__construct();

        $cid = JRequest::getVar('cid', false, 'DEFAULT', 'array');
        if ($cid) {
            $id = $cid[0];
        }
        else {
            $id = JRequest::getInt('id', 1);
        }

        $this->setId($id);
    }


    /**
    * Resets the Vendor ID and data
    */
    function setId($id=1){
        $this->_id = $id;
        $this->_data = null;
    }

    function getId(){
    	return $this->_id;
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
			JError::raiseNotice(1,'$user_id empty, no user logged in');
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
		
        if (!$this->_data) {

	    	$this->_data = $this->getTable('vendor');
   			$this->_data->load((int)$this->_id);
   			
		    // Convert ; separated string into array
		    if ($this->_data->vendor_accepted_currencies) {
				$this->_data->vendor_accepted_currencies = explode(',', $this->_data->vendor_accepted_currencies);
		    }
		    else{
				$this->_data->vendor_accepted_currencies = array();
		    }

//			if($withUserData){
//			    $query = "SELECT user_id FROM #__vm_auth_user_vendor ";
//			    $query .= "WHERE vendor_id = '". $this->_id ."'";
//			    $this->_db->setQuery($query);
//			    $userVendor = $this->_db->loadObject();
//		
//			    // Get user_info table data
//			    $this->_data->userId = (isset($userVendor->user_id) ? $userVendor->user_id : 0);
//			    
//				$userInfoTable = $this->getTable('user_info');
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
		
       	if (!$this->_data) {
	    	$this->_data = new stdClass();
	    	$this->_id = 0;
		}

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
		
		$query = 'SELECT * FROM `#__vm_vendor` ';
		$query .= 'ORDER BY `#__vm_vendor`.`vendor_id`';
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		return $this->_data;
	}

	/**
	 * Find the user id given a vendor id
	 *
	 * @author Max Milbers
	 * @param int $vendor_id
	 * @return int $user_id
	 */
	function getUserIdByVendorId($vendorId=0) {
		//this function is used static, needs its own db
		$db = JFactory::getDBO();
		if (empty($vendorId)) return;
		else {
			$query = 'SELECT `user_id` FROM `#__vm_users` WHERE `vendor_id`="' . $this->_db->Quote((int)$vendorId) . '" ';
			$db->setQuery($query);
			$result = $db->loadResult(); 
			return (isset($result) ? $result : 0);
		}
	}

	
	/**
	 * Bind the post data to the vendor table and save it
     * This function DOES NOT safe information which is in the vm_users or vm_user_info table
     * It only stores the stuff into the vendor table
     * @author RickG
     * @author Max Milbers
     * @return boolean True is the save was successful, false otherwise.
	 */
    function store($data){
   
	$table = $this->getTable('vendor');

	require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'image.php');

	//uploading images and creating thumbnails
	$fullImage = JRequest::getVar('vendor_full_image', array(), 'files');	
	if(!empty($fullImage['name'])){
		$filename = $fullImage['name'];
	} else {
		$filename = $data['vendor_full_image_current'];
	}

	$thumbImage = JRequest::getVar('vendor_thumb_image', array(), 'files');
	if(!empty($thumbImage['name'])){
		$filenamethumb = $thumbImage['name'];
	} else {
		$filenamethumb = $data['vendor_thumb_image_current'];
	}
			
	$image = VmImage::getVendorImage($filename,$filenamethumb);
	if(!empty($image)){
		$data = $image->saveImage($data,$fullImage,false);
		$data = $image->saveImage($data,$thumbImage,true);
	}
		
	// Store multiple selectlist entries as a ; separated string
	if (key_exists('vendor_accepted_currencies', $data) && is_array($data['vendor_accepted_currencies'])) {
	    $data['vendor_accepted_currencies'] = implode(',', $data['vendor_accepted_currencies']);
	}
	// Store multiple selectlist entries as a | separated string
	if (key_exists('vendor_currency_display_style', $data) && is_array($data['vendor_currency_display_style'])) {
	    $data['vendor_currency_display_style'] = implode('|', $data['vendor_currency_display_style']);
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
	
	return true;

	}

	/**
	 * Get the vendor specific currency
	 * 
	 * @author Oscar van Eijk
	 * @param $_vendorId Vendor ID
	 * @return string Currency code
	 */
	function getVendorCurrencyCode ($_vendorId)
	{
		$_db = JFactory::getDBO();
		$_q = 'SELECT c.currency_code AS cc '
			. 'FROM `#__vm_currency` AS c'
			. ',    `#__vm_vendor` AS v '
			. 'WHERE v.vendor_id = '.$_vendorId . ' '
			. 'AND   v.vendor_currency = c.currency_id';
		$_db->setQuery($_q);
		$_r = $_db->loadObject();
		return $_r->cc;
	}

	/**
	 * 
	 *  
	 * @author Max Milbers
	 * @param int $user_id
	 * returns joomla user email
	 */

//	function get_juser_email_by_user_id(&$user_id){
//		if(empty ($user_id))return;
////		$db =& JFactory::getDBO();
//		$q = 'SELECT `email` FROM `#__users` WHERE `id`="'.$user_id.'" ';
//		$this->_db->setQuery($q);
//		$this->_db->query();
//		return $this->_db->loadResult();
//	}
	

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

	function getUserIdByOrderId( &$order_id){
		if(empty ($order_id))return;
		$q  = "SELECT `user_id` FROM `#__vm_orders` WHERE `order_id`='$order_id'";
//		$db->query( $q );
		$this->_db->setQuery($q);
		
//		if($db->next_record()){
		if($this->_db->query()){
//			$user_id = $db->f('user_id');
			return $this->_db->loadResult();
		}else{
			JError::raiseNotice(1,'Error in DB $order_id '.$order_id.' dont have a user_id');
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
	 * 
	 * Gives back the formate of the vendor, gets $style if none is set, with the vendorId.
	 * When no param is set, you get the format of the mainvendor
	 * 
	 * @author unknown
	 * @author Max Milbers
	 * @param int 		$vendorId Id of hte vendor
	 * @param string 	$style The vendor_currency_display_code
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
	public function getCurrencyDisplay($vendorId=1, $style=0){
		
		if(empty($style)){
			$db = JFactory::getDBO();
			$q = 'SELECT `vendor_currency_display_style` FROM `#__vm_vendor` WHERE `vendor_id`="'.$vendorId.'"';
			$db->setQuery($q);
			if($style = $db->loadResult()){
				
			} else {
				JError::raiseWarning('1', JText::_('VM_CONF_WARN_NO_CURRENCY_DEFINED'));
				return 0;	
			}	
		}
		$array = explode( "|", $style );
		$_currencyDisplayStyle = Array();
		$_currencyDisplayStyle['id'] = !empty($array[0]) ? $array[0] : 0;
		$_currencyDisplayStyle['symbol'] = !empty($array[1]) ? $array[1] : '';
		$_currencyDisplayStyle['nbdecimal'] = !empty($array[2]) ? $array[2] : '';
		$_currencyDisplayStyle['sdecimal'] = !empty($array[3]) ? $array[3] : '';
		$_currencyDisplayStyle['thousands'] = !empty($array[4]) ? $array[4] : '';
		$_currencyDisplayStyle['positive'] = !empty($array[5]) ? $array[5] : '';
		$_currencyDisplayStyle['negative'] = !empty($array[6]) ? $array[6] : '';

		if (!empty($_currencyDisplayStyle)) {
			$currency = new CurrencyDisplay($_currencyDisplayStyle['id'], $_currencyDisplayStyle['symbol']
				, $_currencyDisplayStyle['nbdecimal'], $_currencyDisplayStyle['sdecimal']
				, $_currencyDisplayStyle['thousands'], $_currencyDisplayStyle['positive']
				, $_currencyDisplayStyle['negative']
			);
		} else {
				$currency = new CurrencyDisplay();
		}
		return $currency;
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
				$q = 'SELECT vendor_id FROM #__vm_order_item WHERE order_id='.$value;
				break;
			case 'user':
				if ($ownerOnly) {
					$q = 'SELECT `vendor_id`
						FROM `#__vm_users` `au` 
						LEFT JOIN `#__vm_user_info` `u` 
						ON (au.user_id = u.user_id) 
						WHERE `u`.`user_id`=' .$value;
				}
				else {
					$q  = 'SELECT `vendor_id` FROM `#__vm_users` WHERE `user_id`= "' .$value.'" ';
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
	 * This function gives back the storename for the given vendor.
	 * This function is just for improving speed. When you need the whole vendor table, use getVendor
	 * 
	 * @author Max Milbers
	 */
	public function getVendorName($vendor_id=1){
		$query = 'SELECT `vendor_store_name` FROM `#__vm_vendor` WHERE `vendor_id` = "'.$vendor_id.'" ';
		$this->_db->setQuery($query);
		if($this->_db->query()) return $this->_db->loadResult(); else return '';
	}

	/**
	 * This function gives back the email for the given vendor.
	 * This function is just for improving speed. When you need the whole vendor data, use getUser in the usermodell
	 * 
	 * @author Max Milbers
	 */
	 
 	public function getVendorEmail($vendor_id){
 		$user_id = self::getUserIdByVendorId($vendor_id);
 		if(!empty($user_id)){
  			$query = 'SELECT `email` FROM `#__users` WHERE `vendor_id` = "'.$user_id.'" ';
			$this->_db->setQuery($query);
			if($this->_db->query()) return $this->_db->loadResult(); else return '';			
 		}
		return '';
 	}

	/**
	 * ATTENTION this function ist atm NOT USED
	 * Create a formatted vendor address
	 * mosttime $vendor_id is set to 1;
	 * Returns the formatted Store Address
	 * @author someone, completly rewritten by Max Milbers, RolandD
	 * @param integer $vendor_id
	 * @return String
	 */
	function formatted_store_address($vendor_id) {
		
		echo 'Developer notice, you used an old legacy function, you may do it, but correct it first <br />';
		echo 'But be aware that the class VmStore is obsolete!';die;
		if(empty($vendor_id)){
			JError::raiseWarning(1,'formatted_store_address no vendor_id given' );
			return;
		}
		else {
			//Todo this query is broken due the changes with the tables
			$this->_db = JFactory::getDBO();
			$q = "SELECT vendor_store_name AS storename, address_1, address_2, email, fax,
				s.state_2_code AS state, s.state_name AS statename, city, zip, 
				c.country_name AS country, vendor_phone, vendor_url AS url, phone_1 as phone
				FROM #__vm_vendor v
				LEFT JOIN #__vm_user_shopper_group_xref x
				ON x.vendor_id = v.vendor_id
				LEFT JOIN #__vm_user_info u
				ON u.user_id = x.user_id
				LEFT JOIN #__users j
				ON j.id = u.user_id
				LEFT JOIN #__vm_country c ON c.country_id = u.country_id
				LEFT JOIN #__vm_state s ON s.state_id = u.state_id
				WHERE v.vendor_id = ".$vendor_id."
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
	
//	/**
//	* Retrieves a DB object with the recordset of the specified fields (as array)
//	* of vendor_id and ordered by lastparam 
//	* If no orderby is need just set "" 
//	* the country the vendor is assigned to    
//	* 
//	* @author Max Milbers
//	* @author RolandD
//	* @static 
//	* @param int $vendor_id
//	* @param array $fields  "" = Select *
//	* @param String $orderby to order by, just the columnname Without 'ORDER BY '
//	* @return ps_DB
//	*/
//	 
//	public function getVendorFields($vendor_id, $fields=array(), $orderby="") {
//		
//		JError::raiseNotice(1,'Attention you use the obsolete function getVendorFields');
//		//used static
//		$db = JFactory::getDBO();
//		$usertable= false;
//		$user_id = self::getUserIdByVendorId($vendor_id);
//		if (empty($user_id)) {
//				//JError::raiseNotice(1, 'Failure in Database no user_id for vendor_id '.$vendor_id.' found' );
//				return;
//		}
//		else{
//			// JError::raiseNotice(1, 'get_vendor_details user_id for vendor_id found' );
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
//		$q = 'SELECT '.$fieldstring.' FROM (#__vm_vendor v, #__vm_user_info u) ';
//		if($usertable) $q .= 'LEFT JOIN #__users ju ON (ju.id = u.user_id) ';
//		if($countrytable) {
//			$q .= 'LEFT JOIN #__vm_country c ON (u.country=c.country_id) 
//				LEFT JOIN #__vm_state s ON (s.country_id=c.country_id) ';
//		}
//		$q .= 'WHERE v.vendor_id = '.(int)$vendor_id.' AND u.user_id = '.(int)$user_id.' ';
//		
//		if (!empty($orderby)) $q .= 'ORDER BY '.$orderby.' ';
//		
//		$db->setQuery($q);
//		$vendor_fields = $db->loadObject();
//		if (!$vendor_fields) {
//			print '<h1>Invalid query in get_vendor_fields <br />Query: '.$q.'<br />';
//			print 'vendor_id: '.$vendor_id.' and user_id: '.$user_id.' <br />' ;
//			print '$orderby: '.$orderby.' and $usertable: '.$usertable.'</h1>' ;
//			return ;
//		}
//		else return $vendor_fields;
//	}
	
}

//pure php no closing tag