<?php

define( '_VALID_MOS', 1 );
define( '_JEXEC', 1 );

/**
 * Virtuemart User SOA Connector
 *
 * Virtuemart User SOA Connector (Provide functions GetUsers, Authentification ...)
 *
 * @package    com_vm_soa
 * @subpackage modules
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  2010 Mickael Cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    $Id:$
 */

 /** loading framework **/
include_once('VM_Commons.php');

/**
 * Class User
 *
 * Class "User" with attribute : id, name, code,
 * 
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class User {
	
		public $user_id="";
		public $email="";
		public $username="";
		public $password="";
		public $userinfo_id="";
		public $address_type="";
		public $address_type_name="";
		public $name="";
		public $company="";
		public $title="";
		public $last_name="";
		public $first_name="";
		public $middle_name="";
		public $phone_1="";
		public $phone_2="";
		public $fax="";
		public $address_1="";
		public $address_2="";
		public $city="";
		public $virtuemart_state_id="";
		public $virtuemart_country_id="";
		public $zip="";
		public $extra_field_1="";
		public $extra_field_2="";
		public $extra_field_3="";
		public $extra_field_4="";
		public $extra_field_5="";
		public $created_on="";
		public $modified_on="";
		public $user_is_vendor="";
		public $customer_number="";
		public $perms="";
		public $virtuemart_paymentmethod_id="";
		public $virtuemart_shippingcarrier_id="";
		public $agreed="";
		public $shoppergroup_id="";
		
		
		
		function __construct($user_id,$email,$username,$password,$userinfo_id,$address_type,$address_type_name,$name,$company,$title,$last_name,
							$first_name,$middle_name,$phone_1,$phone_2,$fax,$address_1,$address_2,$city,$virtuemart_state_id,$virtuemart_country_id,$zip,
							$extra_field_1,$extra_field_2,$extra_field_3,$extra_field_4,$extra_field_5,$created_on,$modified_on
							,$user_is_vendor,$customer_number,$perms,$virtuemart_paymentmethod_id,$virtuemart_shippingcarrier_id,$agreed,$shoppergroup_id){
		
			$this->user_id					=$user_id;
			$this->email					=$email;
			$this->username					=$username;
			$this->password					=$password;
			$this->userinfo_id				=$userinfo_id;
			$this->address_type				=$address_type;
			$this->address_type_name		=$address_type_name;
			$this->name						=$name;
			$this->company					=$company;
			$this->title					=$title;
			$this->last_name				=$last_name;
			$this->first_name				=$first_name;
			$this->middle_name				=$middle_name;
			$this->phone_1					=$phone_1;
			$this->phone_2					=$phone_2;
			$this->fax						=$fax;
			$this->address_1				=$address_1;
			$this->address_2				=$address_2;
			$this->city						=$city;
			$this->virtuemart_state_id		=$virtuemart_state_id;
			$this->virtuemart_country_id	=$virtuemart_country_id;
			$this->zip						=$zip;
			$this->extra_field_1			=$extra_field_1;
			$this->extra_field_2			=$extra_field_2;
			$this->extra_field_3			=$extra_field_3;
			$this->extra_field_4			=$extra_field_4;
			$this->extra_field_5			=$extra_field_5;
			$this->created_on				=$created_on;
			$this->modified_on				=$modified_on;
			$this->user_is_vendor			=$user_is_vendor;
			$this->customer_number			=$customer_number;
			$this->perms					=$perms;
			$this->virtuemart_paymentmethod_id		=$virtuemart_paymentmethod_id;
			$this->virtuemart_shippingcarrier_id 	=$virtuemart_shippingcarrier_id;
			$this->agreed					=$agreed;
			$this->shoppergroup_id			=$shoppergroup_id;
			
			
			
		}
	
	}
	
	/**
	 * Class Country
	 *
	 * Class "Country" with attribute : country_id, zone_id, country_name,country_3_code, country_2_code
	 * 
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class Country {
	
		public $country_id="";
		public $virtuemart_worldzone_id="";
		public $country_name="";		
		public $country_3_code="";	
		public $country_2_code="";
		public $published="";
		
		
		
		function __construct($country_id, $virtuemart_worldzone_id,$country_name,$country_3_code,$country_2_code,$published){
		
			$this->country_id				=$country_id;
			$this->virtuemart_worldzone_id	=$virtuemart_worldzone_id;
			$this->country_name				=$country_name;		
			$this->country_3_code			=$country_3_code;	
			$this->country_2_code			=$country_2_code;
			$this->published				=$published;				
			
		}
	}
  
  	/**
	 * Class AuthGroup (Permsgroup in VM2)
	 *
	 * Class "AuthGroup" with attribute : group_id ...
	 * 
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class AuthGroup {
	
		public $group_id="";
		public $vendor_id="";
		public $group_name="";		
		public $group_level="";	
		public $ordering="";	
		public $shared="";	
		public $published="";	
		
		function __construct($group_id, $vendor_id,$group_name, $group_level, $ordering, $shared, $published){
		
			$this->group_id		=$group_id;
			$this->vendor_id	=$vendor_id;
			$this->group_name	=$group_name;
			$this->group_level	=$group_level;
			$this->ordering		=$ordering;
			$this->shared		=$shared;
			$this->published	=$published;			
		}
	}
  
  	/**
	 * Class State
	 *
	 * Class "State" with attribute : state_id ...
	 * 
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class State {
	
		public $state_id="";
		public $virtuemart_vendor_id="";
		public $virtuemart_country_id="";		
		public $virtuemart_worldzone_id="";	
		public $state_name="";
		public $state_3_code="";
		public $state_2_code="";
		public $published="";
		
		
		function __construct($state_id, $virtuemart_vendor_id,$virtuemart_country_id,$virtuemart_worldzone_id,$state_name,$state_3_code,$state_2_code,$published){
		
			$this->state_id					=$state_id;
			$this->virtuemart_vendor_id		=$virtuemart_vendor_id;
			$this->virtuemart_country_id	=$virtuemart_country_id;		
			$this->virtuemart_worldzone_id	=$virtuemart_worldzone_id;	
			$this->state_name				=$state_name;	
			$this->state_3_code				=$state_3_code;
			$this->state_2_code				=$state_2_code;
			$this->published				=$published;
			
		}
	}
  
    /**
	 * Class ShopperGroup
	 *
	 * Class "ShopperGroup" with attribute :  shopper_group_id ...
	 * 
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class ShopperGroup {
	
		public $shopper_group_id="";
		public $vendor_id="";
		public $shopper_group_name="";		
		public $shopper_group_desc="";	
		public $custom_price_display="";
		public $price_display="";
		public $default="";
		public $ordering="";
		public $shared="";
		public $published="";
		
		function __construct($shopper_group_id, $vendor_id,$shopper_group_name,$shopper_group_desc,$custom_price_display,$price_display,$default,$ordering,$shared,$published){
		
			$this->shopper_group_id		=$shopper_group_id;
			$this->vendor_id			=$vendor_id;
			$this->shopper_group_name	=$shopper_group_name;		
			$this->shopper_group_desc	=$shopper_group_desc;	
			$this->custom_price_display	=$custom_price_display;	
			$this->price_display		=$price_display;	
			$this->default				=$default;
			$this->ordering				=$ordering;
			$this->shared				=$shared;
			$this->published			=$published;			
		}
	}
  
 /**
 * Class User
 *
 * Class "Vendor" with attribute : vendor_id...
 * 
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class Vendor {
	
		public $vendor_id="";
		public $vendor_name="";
		public $vendor_phone="";	
		public $vendor_store_name="";	
		public $vendor_store_desc="";	
		public $vendor_currency="";
		public $vendor_image_path="";	
		public $vendor_terms_of_service="";	
		public $vendor_url="";	
		public $vendor_min_pov="";	
		public $vendor_freeshipping="";
		public $vendor_accepted_currencies="";	
		public $vendor_address_format="";
		public $vendor_date_format ="";
		public $config="";	
		
		
		function __construct($vendor_id, $vendor_name,$vendor_phone,$vendor_store_name,$vendor_store_desc,$vendor_currency,$vendor_image_path,$vendor_terms_of_service,$vendor_url,$vendor_min_pov,$vendor_freeshipping,
		$vendor_accepted_currencies,$vendor_address_format,$vendor_date_format,$config){
		
			$this->vendor_id					=$vendor_id;
			$this->vendor_name					=$vendor_name;
			$this->vendor_phone					=$vendor_phone;		
			$this->vendor_store_name			=$vendor_store_name;	
			$this->vendor_store_desc			=$vendor_store_desc;
			$this->vendor_currency				=$vendor_currency;
			$this->vendor_image_path			=$vendor_image_path;
			$this->vendor_terms_of_service		=$vendor_terms_of_service;
			$this->vendor_url					=$vendor_url;
			$this->vendor_min_pov				=$vendor_min_pov;
			$this->vendor_freeshipping			=$vendor_freeshipping;
			$this->vendor_accepted_currencies	=$vendor_accepted_currencies;
			$this->vendor_address_format		=$vendor_address_format;
			$this->vendor_date_format			=$vendor_date_format;
			$this->config						=$config;
			
		}
	
	} 
	
	  	/**
	 * Class VendorCategory
	 *
	 * Class "VendorCategory" with attribute : vendor_category_id ...
	 * 
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class VendorCategory {
	
		public $vendor_category_id="";
		public $vendor_category_name="";
		public $vendor_category_desc="";		
		
		function __construct($vendor_category_id, $vendor_category_name,$vendor_category_desc){
		
			$this->vendor_category_id	=$vendor_category_id;
			$this->vendor_category_name	=$vendor_category_name;
			$this->vendor_category_desc	=$vendor_category_desc;		
		}
	}
	 /**
	 * Class Manufacturer
	 *
	 * Class "Manufacturer" with attribute :  manufacturer_id ...
	 * 
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class Manufacturer {
	
		public $manufacturer_id="";
		public $mf_name="";
		public $slug="";
		public $mf_email="";		
		public $mf_desc="";	
		public $mf_category_id="";
		public $mf_url="";
		public $hits="";
		public $published="";
		
		function __construct($manufacturer_id, $mf_name,$slug,$mf_email,$mf_desc,$mf_category_id,$mf_url,$hits,$published){
		
			$this->manufacturer_id		=$manufacturer_id;
			$this->mf_name				=$mf_name;
			$this->slug					=$slug;
			$this->mf_email				=$mf_email;		
			$this->mf_desc				=$mf_desc;	
			$this->mf_category_id		=$mf_category_id;	
			$this->mf_url				=$mf_url;	
			$this->hits					=$hits;	
			$this->published			=$published;	
		}
	}
	
		 /**
	 * Class ManufacturerCat
	 *
	 * Class "ManufacturerCat" with attribute :  mf_category_id ...
	 * 
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class ManufacturerCat {
	
		public $mf_category_id="";
		public $mf_category_name="";
		public $mf_category_desc="";
		public $published="";

		

		
		function __construct($mf_category_id, $mf_category_name,$mf_category_desc,$published){
		
			$this->mf_category_id=$mf_category_id;
			$this->mf_category_name=$mf_category_name;
			$this->mf_category_desc=$mf_category_desc;	
			$this->published=$published;				
			
		}
	}
	
	/**
 * Class AvalaibleImage
 *
 * Class "AvalaibleImage" with attribute : id, name, code, 
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class AvalaibleImage {
		public $image_name="";
		public $image_url="";
		public $realpath="";

		//constructeur
		/**
		 * Enter description here...
		 *
		 * @param String $image_name
		 * @param String $image_url
		 */
		function __construct($image_name, $image_url, $realpath) {
			$this->image_name = $image_name;
			$this->image_url = $image_url;	
			$this->realpath = $realpath;				
		}
	}	
	
	
	 /**
	 * Class Session
	 *
	 * Class "Session" with attribute :  username ...
	 * 
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class Session {
	
		public $username="";
		public $time="";
		public $session_id="";		
		public $guest="";	
		public $userid="";
		public $usertype="";
		public $gid="";
		public $client_id="";
		public $data="";
		
		function __construct($username, $time,$session_id,$guest,$userid,$usertype,$gid,$client_id,$data){
		
			$this->username=$username;
			$this->time=$time;
			$this->session_id=$session_id;		
			$this->guest=$guest;	
			$this->userid=$userid;	
			$this->usertype=$usertype;	
			$this->gid=$gid;	
			$this->client_id=$client_id;	
			$this->data=$data;	
			
		}
	}
	
	 /**
	 * Class WaitingList
	 *
	 * Class "WaitingList" with attribute :  username ...
	 * 
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class WaitingList {
	
		public $waiting_list_id="";
		public $product_id="";
		public $user_id="";		
		public $notify_email="";	
		public $notified="";	
		public $notify_date="";
		
		function __construct($waiting_list_id, $product_id,$user_id,$notify_email,$notified,$notify_date){
		
			$this->waiting_list_id=$waiting_list_id;
			$this->product_id=$product_id;
			$this->user_id=$user_id;		
			$this->notify_email=$notify_email;	
			$this->notified=$notified;	
			$this->notify_date=$notify_date;	
		
		}
	}
	
	
/**
 * Class CommonReturn
 *
 * Class "CommonReturn" with attribute : returnCode, message, code, 
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class CommonReturn {
		public $returnCode="";
		public $message="";
		public $returnData="";

		//constructeur
		/**
		 * Enter description here...
		 *
		 * @param String $returnCode
		 * @param String $message
		 */
		function __construct($returnCode, $message, $returnData) {
			$this->returnCode = $returnCode;
			$this->message = $message;	
			$this->returnData = $returnData;				
		}
	}	
	
	/**
    * This function GetSessions return all sessions 
	* (expose as WS)
    * @param 
    * @return array of Users
	*/
	function GetSessions($params) {
	
		include('../vm_soa_conf.php');
			
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_getall']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			
			$db = JFactory::getDBO();	
				
			$query  = "SELECT * FROM #__session WHERE 1 ";
			
			$limite_start = $params->limite_start;
			$limite_end = $params->limite_end;
			if (empty($params->limite_start)){
				$limite_start = "0";
			}
			if (empty($params->limite_end)){
				$limite_end = "100";
			}
			
			if ($params->guest != ""){
				$query .= " AND guest = '$params->guest' " ;
			}
			if ($params->usertype != ""){
				$query .= " AND usertype = '$params->usertype' " ;
			}
			if ($params->gid != ""){
				$query .= " AND gid = '$params->gid' " ;//not in VM2 
			}
			if ($params->client_id != ""){
				$query .= " AND client_id = '$params->client_id' " ;
			}
			$query .= " ORDER BY time DESC  ";
			$query .= " LIMIT $limite_start,$limite_end ";
			
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row){
				$data="";
				if ($params->with_data == "Y"){
					$data = $row->data;
				}
				$Session = new Session($row->username,$row->time,$row->session_id, $row->guest, 
							$row->userid,$row->usertype, 'Not in VM2', $row->client_id, $data  );
				$arraySession[]= $Session;
			
			}
			return $arraySession;
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		

	}
	
	/**
    * This function GetWaitingList return waiting list 
	* (expose as WS)
    * @param 
    * @return array of Users
	*/
	function GetWaitingList($params) {
	
		include('../vm_soa_conf.php');
			
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_getall']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			
			$db = JFactory::getDBO();	
				
			$query  = "SELECT * FROM `#__virtuemart_waitingusers` WHERE 1 ";
			
			$limite_start = $params->limite_start;
			$limite_end = $params->limite_end;
			if (empty($params->limite_start)){
				$limite_start = "0";
			}
			if (empty($params->limite_end)){
				$limite_end = "100";
			}
			  			
			if ($params->waiting_list_id != ""){
				$query .= "AND virtuemart_waitinguser_id = $params->waiting_list_id";
			}
			if ($params->product_id != ""){
				$query .= " AND virtuemart_product_id = '$params->product_id' " ;
			}
			if ($params->user_id != ""){
				$query .= " AND virtuemart_user_id = '$params->user_id' " ;
			}
			if ($params->notify_email != ""){
				$query .= " AND notify_email = '$params->notify_email' " ;
			}
			if ($params->notified != ""){
				$query .= " AND notified = '$params->notified' " ;
			}
			if ($params->notify_date != ""){
				$query .= " AND notify_date > '$params->notify_date' " ;
			}
			
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row){
				$WaitingList = new WaitingList($row->virtuemart_waitinguser_id,$row->virtuemart_product_id,$row->virtuemart_user_id, $row->notify_email, 
							$row->notified, $row->notify_date  );
				$arrayWaitingList[]= $WaitingList;
			}
			$errMsg=  $db->getErrorMsg();	
			if ($errMsg==null){
				return $arrayWaitingList;
			} else {
				return new SoapFault("GetWaitingListFault", "SQL Error \n ".$errMsg);
			}
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		

	}
	
	
	/**
    * This function Notify Waiting List users
	* (expose as WS)
    * @param 
    * @return array of Users
	*/
	function NotifyWaitingList($params) {
	
		include('../vm_soa_conf.php');
			
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_getall']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			$mosConfig_lang = $params->loginInfo->lang;
			$zw_waiting_list = new zw_waiting_list();
			
			if (is_array($params->ids->id)){
			
				$count = count($params->ids->id);
				for ($i = 0; $i < $count; $i++) {
					$prod_id = $params->ids->id[$i];
					$ret = $zw_waiting_list->notify_list($prod_id);
				}
			}else {
				
				$prod_id = $params->ids->id;
				$ret = $zw_waiting_list->notify_list($prod_id);
			}
			
			if ($ret==True){
				return "NotifyWaitingList OK";
			} else {
				return new SoapFault("NotifyWaitingListFault", "Error while notify waiting list");
			}
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		

	}
	
	
	/**
    * This function GetUsers return all users 
	* (NOT expose as WS)
    * @param 
    * @return array of Users
	*/
	function GetUsersGeneric($params) {
	
			$limite_start = isset($params->limite_start) ? $params->limite_start : 0;
			$limite_end = isset($params->limite_end) ? $params->limite_end : 50;
			

			$db = JFactory::getDBO();	
				
			$query   = "SELECT * FROM `#__users` JU ";
			$query  .= "JOIN `#__virtuemart_vmusers` VU on JU.id = VU.virtuemart_user_id ";
			$query  .= "JOIN `#__virtuemart_userinfos` UI on JU.id = UI.virtuemart_user_id ";
			$query  .= "JOIN `#__virtuemart_vmuser_shoppergroups` SG on VU.virtuemart_user_id = SG.virtuemart_user_id WHERE 1 ";
			
			if (!empty($params->searchtype)){
				if ($params->searchtype=="email"){
					$query  .= "AND JU.email  like '%$params->email%' ";
				} else if ($params->searchtype=="user_id"){ //request for user_ids
					$query  .= "AND JU.id = '".$params->user_ids->user_id."' ";
				} else if ($params->searchtype=="username"){ //request for USERNAME
					$query  .= "AND JU.username like '%$params->username%' ";
				} else {
					$query  .= "AND (JU.username like '%$params->username%' OR JU.id = $params->user_id OR JU.username like '%$params->username%' ) )";
				}
			}
			$query  .= "LIMIT $limite_start,$limite_end ";
						
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			$errMsg=  $db->getErrorMsg();
		
			//return new SoapFault("JoomlaServerAuthFault", "err : ".$query."\n".$params->user_ids->user_id);
			
			foreach ($rows as $row){
			
				$User = new User($row->virtuemart_user_id,
									$row->email,
									$row->username,
									"*******",
									$row->virtuemart_userinfo_id,
									$row->address_type,
									$row->address_type_name,
									$row->name,
									$row->company,
									$row->title,
									$row->last_name,
									$row->first_name,
									$row->middle_name,
									$row->phone_1,
									$row->phone_2,
									$row->fax,
									$row->address_1,
									$row->address_2,
									$row->city,
									$row->virtuemart_state_id,
									$row->virtuemart_country_id,
									$row->zip ,
									$row->extra_field_1,
									$row->extra_field_2,
									$row->extra_field_3,
									$row->extra_field_4,
									$row->extra_field_5,
									$row->created_on,
									$row->modified_on,
									$row->user_is_vendor,
									$row->customer_number,
									$row->perms,
									$row->virtuemart_paymentmethod_id,
									$row->virtuemart_shippingcarrier_id,
									$row->agreed,
									$row->virtuemart_shoppergroup_id		
									
									);
				$arrayUser[]= $User;
			}
			return $arrayUser;
	
	
	}
	
	
	
  	/**
    * This function GetUsers return all users 
	* (expose as WS)
    * @param 
    * @return array of Users
	*/
	function GetUsers($params) {
	
		include('../vm_soa_conf.php');
			
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_getall']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			
			/*if (!class_exists( 'VirtueMartModelProduct' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\user.php');
			$VirtueMartModelUser = new VirtueMartModelUser;
			
			$users = $VirtueMartModelUser->getUserList();*/ //NOT ENOUGHT DATA -> I make my query
			
		
			return GetUsersGeneric($params);
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		

	}
	
  	/**
    * This function GetUsers return all users 
	* (expose as WS)
    * @param 
    * @return array of Users
	*/
	function AddUser($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_adduser']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
			setToken();
			
			if (!class_exists( 'VirtueMartModelUser' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\user.php');
			$modelUser = new VirtueMartModelUser;
			
			$_POST['email'] = $params->User->email;
			$_POST['name'] = $params->User->name;
			$_POST['username'] = $params->User->username;
			$_POST['password'] = $params->User->password;
			$_POST['password2'] = $params->User->password;
			
			$data['email'] 					= $params->User->email;
			$data['username'] 				= $params->User->username;
			$data['password'] 				= $params->User->password;
			$data['userinfo_id'] 			= $params->User->userinfo_id;
			$data['address_type'] 			= isset($params->User->address_type) ? $params->User->address_type : "BT";
			$data['address_type_name'] 		= $params->User->address_type_name;
			$data['name'] 					= $params->User->name;
			$data['company'] 				= $params->User->company;
			$data['title'] 					= $params->User->title;
			$data['last_name'] 				= $params->User->last_name;
			$data['first_name'] 			= $params->User->first_name;
			$data['middle_name'] 			= $params->User->middle_name;
			$data['phone_1'] 				= $params->User->phone_1;
			$data['phone_2'] 				= $params->User->phone_2;
			$data['fax'] 					= $params->User->fax;
			$data['address_1'] 				= $params->User->address_1;
			$data['address_2'] 				= $params->User->address_2;
			$data['city'] 					= $params->User->city;
			$data['virtuemart_state_id'] 	= $params->User->virtuemart_state_id;
			$data['virtuemart_country_id'] 	= $params->User->virtuemart_country_id;
			$data['zip'] 					= $params->User->zip;
			$data['extra_field_1'] 			= $params->User->extra_field_1;
			$data['extra_field_2'] 			= $params->User->extra_field_2;
			$data['extra_field_3'] 			= $params->User->extra_field_3;
			$data['extra_field_4'] 			= $params->User->extra_field_4;
			$data['extra_field_5'] 			= $params->User->extra_field_5;
			$data['extra_field_1'] 			= $params->User->extra_field_1;
			$data['user_is_vendor'] 		= $params->User->user_is_vendor;
			$data['customer_number'] 		= $params->User->customer_number;
			$data['perms'] 					= isset($params->User->perms) ? $params->User->perms : "shopper";
			$data['virtuemart_paymentmethod_id'] = $params->User->virtuemart_paymentmethod_id;
			$data['virtuemart_shippingcarrier_id'] = $params->User->virtuemart_shippingcarrier_id;
			$data['agreed'] 				= $params->User->agreed;
			$data['virtuemart_shoppergroup_id'] = $params->User->shoppergroup_id; //unused ???
		
			$res = $modelUser->store($data);
			
			if ($res  == false){
				return new SoapFault("JoomlaAddUserFault", getWSMsg('User '.$data['username'], ADDKO));
			} else {
				$commonReturn = new CommonReturn(OK,getWSMsg('User '.$data['username'], ADD),$res['newId']);
				return $commonReturn;
			}
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Update User
	* (expose as WS)
    * @param 
    * @return result
	*/
	function UpdateUser($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_upuser']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			
			setToken();
			
			if (!class_exists( 'VirtueMartModelUser' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\user.php');
			$modelUser = new VirtueMartModelUser;
			
			
			//return new SoapFault("UpdateUserFault", $user->id);
			
			
			$_POST['email'] = $params->User->email;
			$_POST['name'] = $params->User->name;
			$_POST['username'] = $params->User->username;
			$_POST['password'] = $params->User->password;
			$_POST['password2'] = $params->User->password;
			
			
			$user_id = $params->User->user_id;
			$user = JFactory::getUser($user_id);
			$modelUser->setId($user_id);
			$modelUser->setUserId($user_id);
			//
			//we cannot modify username/email
			$data['virtuemart_user_id'] 	= $params->User->user_id;
			//$data['email'] 					= $params->User->email;
			//$data['username'] 				= $params->User->username;
			$data['password'] 				= $params->User->password;
			$data['userinfo_id'] 			= $params->User->userinfo_id;
			$data['address_type'] 			= isset($params->User->address_type) ? $params->User->address_type : "BT";
			$data['address_type_name'] 		= $params->User->address_type_name;
			$data['name'] 					= $params->User->name;
			$data['company'] 				= $params->User->company;
			$data['title'] 					= $params->User->title;
			$data['last_name'] 				= $params->User->last_name;
			$data['first_name'] 			= $params->User->first_name;
			$data['middle_name'] 			= $params->User->middle_name;
			$data['phone_1'] 				= $params->User->phone_1;
			$data['phone_2'] 				= $params->User->phone_2;
			$data['fax'] 					= $params->User->fax;
			$data['address_1'] 				= $params->User->address_1;
			$data['address_2'] 				= $params->User->address_2;
			$data['city'] 					= $params->User->city;
			$data['virtuemart_state_id'] 	= $params->User->virtuemart_state_id;
			$data['virtuemart_country_id'] 	= $params->User->virtuemart_country_id;
			$data['zip'] 					= $params->User->zip;
			$data['extra_field_1'] 			= $params->User->extra_field_1;
			$data['extra_field_2'] 			= $params->User->extra_field_2;
			$data['extra_field_3'] 			= $params->User->extra_field_3;
			$data['extra_field_4'] 			= $params->User->extra_field_4;
			$data['extra_field_5'] 			= $params->User->extra_field_5;
			$data['extra_field_1'] 			= $params->User->extra_field_1;
			$data['user_is_vendor'] 		= $params->User->user_is_vendor;
			$data['customer_number'] 		= $params->User->customer_number;
			$data['perms'] 					= isset($params->User->perms) ? $params->User->perms : "shopper";
			$data['virtuemart_paymentmethod_id'] = $params->User->virtuemart_paymentmethod_id;
			$data['virtuemart_shippingcarrier_id'] = $params->User->virtuemart_shippingcarrier_id;
			$data['agreed'] 				= $params->User->agreed;
			$data['virtuemart_shoppergroup_id'] = $params->User->shoppergroup_id;
			
			$result = $modelUser->store($data);
			if (is_array($result)){
				$res= true;
			}
			//return new SoapFault("UpdateUserFault", $res);
			if ($res  == false){
				return new SoapFault("UpdateUserFault",  getWSMsg('User '.$data['username'], UPKO));
			} else {
				$commonReturn = new CommonReturn(OK, getWSMsg('User '.$data['username'], UP),$params->User->user_id);
				return $commonReturn;
				
			}

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}

  	/**
    * This function DeleteUser delete a user
	* (expose as WS)
    * @param 
    * @return result
	*/
	function DeleteUser($params) {
	
		include('../vm_soa_conf.php');
			
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_deluser']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			
			// doesn't work yet in RC2
			if (!class_exists( 'VirtueMartModelUser' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\user.php');
			$modelUser = new VirtueMartModelUser;
			
			$cid[] = $params->user_id;
			$res = $modelUser->remove($cid);
			
			if ($res){
				$commonReturn = new CommonReturn(OK,getWSMsg('User '.$params->user_id, DEL),$params->user_id);
				return $commonReturn;
				
			} else {
				return new SoapFault("JoomlaDeleteUserFault", getWSMsg('User '.$params->user_id, DELKO));
			}

		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
  	/**
    * This function sendMail 
	* (expose as WS)
    * @param 
    * @return result
	*/
	function SendMail($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_sendmail']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
				
				if (is_array($params->EmailParams->images->Image)){
				
					$count = count($params->EmailParams->images->Image);
					for ($i = 0; $i < $count; $i++) {
						$mailImag['path']= $params->EmailParams->images->Image->path[$i];
						$mailImag['name']= $params->EmailParams->images->Image->name[$i];
						$mailImag['filename']= $params->EmailParams->images->Image->filename[$i];
						$mailImag['encoding']= $params->EmailParams->images->Image->encoding[$i];
						$mailImag['mimetype']= $params->EmailParams->images->Image->mimetype[$i];
						
						$mailImage[]=$mailImag;
					}
				
				} else {
					$mailImage['path']= $params->EmailParams->images->Image->path;
					$mailImage['name']= $params->EmailParams->images->Image->name;
					$mailImage['filename']= $params->EmailParams->images->Image->filename;
					$mailImage['encoding']= $params->EmailParams->images->Image->encoding;
					$mailImage['mimetype']= $params->EmailParams->images->Image->mimetype;
				
				}
				$ret = JUtility::sendMail($params->EmailParams->from_mail,
											$params->EmailParams->from_name,
											$params->EmailParams->recipient,
											$params->EmailParams->subject,
											$params->EmailParams->body,
											$params->EmailParams->mode,
											$params->EmailParams->cc,
											$params->EmailParams->bcc,
											$mailImage/*$params->EmailParams->attachment*/,
											$params->EmailParams->replyto,
											null
											);
											//$params->EmailParams->altbody ?
				//$ret = vmMail( $params->EmailParams->from_mail, $params->EmailParams->from_name, $params->EmailParams->recipient, $params->EmailParams->subject, $params->EmailParams->body, $params->EmailParams->altbody,$params->EmailParams->mode,$params->EmailParams->cc,$params->EmailParams->bcc,$mailImage,$params->EmailParams->attachment,$params->EmailParams->replyto);
				if (ret){
					$commonReturn = new CommonReturn(OK,"Email send to ".$params->EmailParams->recipient,$params->EmailParams->recipient);
					return $commonReturn;
				}else{
					return new SoapFault("JoomlaSendMailFault", "Email can not be send to".$params->EmailParams->recipient);
				}

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}	

	/**
    * This function to research an user (By email or by username)
	* (expose as WS)
    * @param 
    * @return result
	*/
	function GetUserFromEmailOrUsername($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_search']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			
			//problem by id
			return GetUsersGeneric($params);
			

		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}	
	
	
	/**
    * This function to Get Additional User Info
	* (expose as WS)
    * @param 
    * @return result
	*/
	function GetAdditionalUserInfo($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_search']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
			$db = JFactory::getDBO();	
				
			
			$query   = "SELECT * FROM `#__virtuemart_userinfos` ui ";
			$query  .= "JOIN #__users u ON ui.virtuemart_user_id=u.id WHERE 1 ";
			
			if (!empty($params->user_id)){
				$query .= " AND ui.virtuemart_user_id = '$params->user_id'";
			}
			if (!empty($params->login)){
				$query .= " AND u.username  = '$params->login'";
			}
			if (!empty($params->email)){
				$query .= " AND u.email = '$params->email'";
			}
			
			$query .= " LIMIT 0,100 ";
			
						
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row){
			
				$User = new User($row->virtuemart_user_id,
									$row->email,
									$row->username,
									"*******",
									$row->virtuemart_userinfo_id,
									$row->address_type,
									$row->address_type_name,
									$row->name,
									$row->company,
									$row->title,
									$row->last_name,
									$row->first_name,
									$row->middle_name,
									$row->phone_1,
									$row->phone_2,
									$row->fax,
									$row->address_1,
									$row->address_2,
									$row->city,
									$row->virtuemart_state_id,
									$row->virtuemart_country_id,
									$row->zip ,
									$row->extra_field_1,
									$row->extra_field_2,
									$row->extra_field_3,
									$row->extra_field_4,
									$row->extra_field_5,
									$row->created_on,
									$row->modified_on,
									$row->user_is_vendor,
									$row->customer_number,
									$row->perms,
									$row->virtuemart_paymentmethod_id,
									$row->virtuemart_shippingcarrier_id,
									$row->agreed,
									$row->virtuemart_shoppergroup_id		
									
									);
				$arrayUser[]= $User;
				
			
			}
			
			
			$errMsg=  $db->getErrorMsg();	
			if ($errMsg==null){
				return $arrayUser;
			}else {
				return new SoapFault("GetAdditionalUserInfoFault", "Error in GetAdditionalUserInfo ",$errMsg);
			}
			

		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}	
	
	

	/**
    * This function  GetUserInfo From OrderID
	* (expose as WS)
    * @param 
    * @return result
	*/
	function GetUserInfoFromOrderID($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_search']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
			/*if (!class_exists( 'TableOrder_userinfos' )) require (JPATH_VM_ADMINISTRATOR.DS.'tables\order_userinfos.php');
			$db = JFactory::getDBO();
			$tableOrder_userinfos = new TableOrder_userinfos($db);*/
			
			$db = JFactory::getDBO();	
				
			$query   = "SELECT * FROM `#__virtuemart_order_userinfos` ui WHERE ui.virtuemart_order_id = '$params->order_id' ";
			
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row){
			
				$User = new User($row->virtuemart_user_id,
									$row->email,
									$row->username,
									"*******",
									$row->virtuemart_userinfo_id,
									$row->address_type,
									$row->address_type_name,
									$row->name,
									$row->company,
									$row->title,
									$row->last_name,
									$row->first_name,
									$row->middle_name,
									$row->phone_1,
									$row->phone_2,
									$row->fax,
									$row->address_1,
									$row->address_2,
									$row->city,
									$row->virtuemart_state_id,
									$row->virtuemart_country_id,
									$row->zip ,
									$row->extra_field_1,
									$row->extra_field_2,
									$row->extra_field_3,
									$row->extra_field_4,
									$row->extra_field_5,
									$row->created_on,
									$row->modified_on,
									$row->user_is_vendor,
									$row->customer_number,
									$row->perms,
									$row->virtuemart_paymentmethod_id,
									$row->virtuemart_shippingcarrier_id,
									$row->agreed			
									);
				
				$arrayUser[]= $User;
			}
			return $arrayUser;


		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}	


	
  	/**
    * This function GetAllCountryCode return all country code 
	* (expose as WS)
    * @param 
    * @return array of Country
	*/
	function GetAllCountryCode($params2) {
	
		include('../vm_soa_conf.php');
			
		/* Authenticate*/
		$result = onAdminAuthenticate($params2->login, $params2->password);
		if ($conf['auth_users_getcountry']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
								
			if (!class_exists( 'VirtueMartModelCountry' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\country.php');
			$modelCountry = new VirtueMartModelCountry;
			
			$rows = $modelCountry->getCountries(false,true);
			
			foreach ($rows as $row){
				$Country = new Country($row->virtuemart_country_id,
										$row->virtuemart_worldzone_id,
										$row->country_name,
										$row->country_3_code,
										$row->country_2_code,
										$row->published);
				$arrayCountry[]= $Country;
			}
			return $arrayCountry;
			

		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params2->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params2->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params2->login);
		}
	}
	
	/**
    * This function get Get AuthGroup
	* (expose as WS)
    * @param string
    * @return result
    */
	function GetAuthGroup($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->login, $params->password);
		if ($conf['auth_users_getauthgrp']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'VirtueMartModelUsergroups' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\usergroups.php');
			$modelUsergroups = new VirtueMartModelUsergroups;
			
			$userGroups = $modelUsergroups->getUsergroups(false,true);
			
			foreach ($userGroups as $row){
				$AuthGroup = new AuthGroup($row->virtuemart_permgroup_id,
											$row->virtuemart_vendor_id,
											$row->group_name,
											$row->group_level,
											$row->ordering,
											$row->shared,
											$row->published
											);
				$arrayAuthGroup[]=$AuthGroup;
			}
			return $arrayAuthGroup;
			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
	}
	

	
	/**
    * This function Add AuthGroup (permsgroup in VM2)
	* (expose as WS)
    * @param string
    * @return result
    */
	function AddAuthGroup($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_addautgrp']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
					
			if (!class_exists( 'VirtueMartModelUsergroups' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\usergroups.php');
			$modelUsergroups = new VirtueMartModelUsergroups;
			
			//$data['virtuemart_manufacturercategories_id'] = $params->ManufacturerCat->mf_category_id;
			$data['group_name'] = $params->AuthGroup->group_name;
			$data['group_level'] = $params->AuthGroup->group_level;
			$data['published'] = isset($params->AuthGroup->published) ? $params->AuthGroup->published : 1;
			
			$res = $modelUsergroups->store($data);
			$errMsg = $modelUsergroups->getError();
			
			
			if ($res != false){
				$commonReturn = new CommonReturn(OK,getWSMsg("AuthGroup",ADD)." : ".$params->AuthGroup->group_name,$res);
				return $commonReturn;
				
			}else {
				return new SoapFault("AddAuthGroupFault", getWSMsg("AuthGroup",ADDKO)." : ".$params->AuthGroup->group_name." ".$errMsg );
			}
			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
		/**
    * This function Delete AuthGroup
	* (expose as WS)
    * @param string
    * @return result
    */
	function DeleteAuthGroup($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_delauthgrp']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'TableUsergroups' )) require (JPATH_VM_ADMINISTRATOR.DS.'tables\usergroups.php');
			$db = JFactory::getDBO();
			$tableUsergroups = new TableUsergroups($db);
			
			$tableUsergroups->virtuemart_permgroup_id = $params->group_id;
			$res = $tableUsergroups->delete();
			
		
			
			if ($res){
				$commonReturn = new CommonReturn(OK,getWSMsg('authGroup ',DEL)." : ".$params->group_id,$params->group_id);
				return $commonReturn;
				
			}else {
				return new SoapFault("DeleteAuthGroupFault", getWSMsg('authGroup ',DELKO)." : ".$params->group_id);
			}
			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
		/**
    * This function GetAllStates
	* (expose as WS)
    * @param string
    * @return result
    */
	function GetAllStates($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_getstate']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
			
			$db = JFactory::getDBO();	
				
			if (!empty($params->country_id)){
				$query  = "SELECT * FROM `#__virtuemart_states` WHERE virtuemart_country_id = '".$params->country_id."'";
			} else {
				$query  = "SELECT * FROM `#__virtuemart_states` WHERE 1 ";
			}
						
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row){
				$State = new State($row->virtuemart_state_id,
									$row->virtuemart_vendor_id,
									$row->virtuemart_country_id,
									$row->virtuemart_worldzone_id,
									$row->state_name,
									$row->state_3_code,
									$row->state_2_code,
									$row->published
									);
				$arrayState[]=$State;
			}
			return $arrayState;
			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Add States
	* (expose as WS)
    * @param string
    * @return result
    */
	function AddStates($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_addstate']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
		$cpnIdsStr = "";
		$allOk=true;
		
		/*if (!class_exists( 'TableStates' )) require (JPATH_VM_ADMINISTRATOR.DS.'tables\states.php');
		$db = JFactory::getDBO();
		$tableStates = new TableStates($db);*/
		
		if (!class_exists( 'VirtueMartModelState' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\state.php');
		$modelState = new VirtueMartModelState;
			
		if (is_array($params->States->State)){
			
				$count = count($params->States->State);
				for ($i = 0; $i < $count; $i++) {
								
					$data['virtuemart_vendor_id'] = isset($params->States->State[$i]->virtuemart_vendor_id) ? $params->States->State[$i]->virtuemart_vendor_id : 1;
					$data['virtuemart_country_id'] = $params->States->State[$i]->virtuemart_country_id;
					$data['virtuemart_worldzone_id'] = $params->States->State[$i]->virtuemart_worldzone_id;
					$data['state_name'] = $params->States->State[$i]->state_name;
					$data['state_3_code'] = $params->States->State[$i]->state_3_code;
					$data['state_2_code'] = $params->States->State[$i]->state_2_code;
					$data['published'] = isset($params->States->State[$i]->virtuemart_vendor_id) ? $params->States->State[$i]->virtuemart_vendor_id : 1;
					
					$res = $modelState->store($data);
					$errMsg = $modelState->getError();
					
					//$result = $tableStates->store();	
					
					if ($res != false){
						$cpnIdsStr .= $res." ";
					}else{
						$allOk=false;
					}
				}
			
			} else {
				
				$data['virtuemart_vendor_id'] = isset($params->States->State->virtuemart_vendor_id) ? $params->States->State->virtuemart_vendor_id : 1;
				$data['virtuemart_country_id'] = $params->States->State->virtuemart_country_id;
				$data['virtuemart_worldzone_id'] = $params->States->State->virtuemart_worldzone_id;
				$data['state_name'] = $params->States->State->state_name;
				$data['state_3_code'] = $params->States->State->state_3_code;
				$data['state_2_code'] = $params->States->State->state_2_code;
				$data['published'] = isset($params->States->State->virtuemart_vendor_id) ? $params->States->State->virtuemart_vendor_id : 1;
					
				$res = $modelState->store($data);
				$errMsg = $modelState->getError();	
				
				if ($res != false){
					
					$commonReturn = new CommonReturn(OK,getWSMsg('State', ADD)." : ".$params->States->State->state_name,$res);
					return $commonReturn;
					
				}else {
					return new SoapFault("AddStatesFault", getWSMsg('State', ADDKO)." : ".$params->States->State->state_name."\n".$errMsg);
				}
			}

			if ($allOk){
				$commonReturn = new CommonReturn(OK,getWSMsg('State', ALLOK)." : ".$cpnIdsStr,$cpnIdsStr);
				return $commonReturn;
			
			} else {
				return new SoapFault("DeleteStatesFault", getWSMsg('State', NOTALLOK)." : ".$cpnIdsStr."\n".$errMsg);
			}	
			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
		/**
    * This function Delete States
	* (expose as WS)
    * @param string
    * @return result
    */
	function DeleteStates($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_delstate']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
					
			
			$allOk=true;
			$cpnIdsStr="";
			
			/*if (!class_exists( 'TableStates' )) require (JPATH_VM_ADMINISTRATOR.DS.'tables\states.php');
			$db = JFactory::getDBO();
			$tableStates = new TableStates($db);*/
			
			if (!class_exists( 'VirtueMartModelState' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\state.php');
			$modelState = new VirtueMartModelState;
			
			
						
			if (is_array($params->ids->id)){
			
				$count = count($params->ids->id);
				for ($i = 0; $i < $count; $i++) {
					$state_id = $params->ids->id[$i];
					$data['virtuemart_state_id'] = $state_id;
					$result = $modelState->remove($data);
					if ($result != false){
						$cpnIdsStr .= $state_id." ";
					}else{
						$allOk=false;
					}
				}
			} else {
				$state_id = $params->ids->id;
				$data['virtuemart_state_id'] = $state_id;
				$result = $modelState->remove($data);
				
				if ($result != false){
					$commonReturn = new CommonReturn(OK,getWSMsg('State', DEL)." : ".$state_id,$state_id);
					return $commonReturn;
					
				}else {
					return new SoapFault("DeleteStatesFault", getWSMsg('State', DELKO)." : ".$state_id);
				}
			}
			if ($allOk){
				$commonReturn = new CommonReturn(OK,getWSMsg('State', ALLOK)." : ".$cpnIdsStr,$cpnIdsStr);
				return $commonReturn;
				
			} else {
				return new SoapFault("DeleteStatesFault", getWSMsg('State', NOTALLOK)." , only states id : ".$cpnIdsStr);
			}			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function get shopperGroup
	* (expose as WS)
    * @param string
    * @return result
    */
	function GetShopperGroup($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->login, $params->password);
		if ($conf['auth_users_getshopgrp']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			/*$db = JFactory::getDBO();	
			$query  = "SELECT * FROM `#__virtuemart_shoppergroups` WHERE 1 ";
			$db->setQuery($query);
			$rows = $db->loadObjectList();*/
			
			if (!class_exists( 'VirtueMartModelShopperGroup' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\shoppergroup.php');
			$modelShopperGroup = new VirtueMartModelShopperGroup;
			
			$rows = $modelShopperGroup->getShopperGroups();
			
			foreach ($rows as $row){
			
				$price_display = unserialize($row->price_display);
				
				$ShopperGroup = new ShopperGroup($row->virtuemart_shoppergroup_id,
													$row->virtuemart_vendor_id,
													$row->shopper_group_name,
													$row->shopper_group_desc,
													$row->custom_price_display,
													/*$row->price_display*/$price_display,
													$row->default,
													$row->ordering,
													$row->shared,
													$row->published);
				$arrayShopperGroup[]=$ShopperGroup;
			}
			return $arrayShopperGroup;
					
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
	}
	
	/**
    * This function Add ShopperGroup
	* (expose as WS)
    * @param string
    * @return result
    */
	function AddShopperGroup($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_addshopgrp']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			$cpnIdsStr = "";
			$allOk=true;
			
			if (!class_exists( 'VirtueMartModelShopperGroup' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\shoppergroup.php');
			$modelShopperGroup = new VirtueMartModelShopperGroup;
			
			if (is_array($params->shoppergroups->shoppergroup)){
				
				$count = count($params->shoppergroups->shoppergroup);
				for ($i = 0; $i < $count; $i++) {
				
					//price_display transform
					//price_display format must be like
					//{"basePrice":1,"basePriceText":1,"basePriceRounding":2,"variantModification":1,"variantModificationText":1,"variantModificationRounding":2,"basePriceVariant":0,"basePriceVariantText":1,"basePriceVariantRounding":2,"basePriceWithTax":0,"basePriceWithTaxText":1,"basePriceWithTaxRounding":2,"discountedPriceWithoutTax":0,"discountedPriceWithoutTaxText":1,"discountedPriceWithoutTaxRounding":2,"salesPriceWithDiscount":1,"salesPriceWithDiscountText":1,"salesPriceWithDiscountRounding":2,"salesPrice":0,"salesPriceText":1,"salesPriceRounding":2,"priceWithoutTax":0,"priceWithoutTaxText":1,"priceWithoutTaxRounding":2,"discountAmount":0,"discountAmountText":1,"discountAmountRounding":2,"taxAmount":0,"taxAmountText":1,"taxAmountRounding":2}
		
					$price_d = $params->shoppergroups->shoppergroup[$i]->price_display;
					//clean
					$price_d = str_replace("{", "", $price_d);
					$price_d = str_replace("}", "", $price_d);
					$price_d = str_replace('"', "", $price_d);
					$price_d_arr  =  explode(",", $price_d);
					
					foreach ($price_d_arr as $param){
						$parval=  explode(":", $param);
						$data[$parval[0]]= $parval[1];
						//ex make $data['basePrice'] = '0';
					}
					
					$data["virtuemart_vendor_id"] = $params->shoppergroups->shoppergroup[$i]->vendor_id;
					$data['shopper_group_name'] = $params->shoppergroups->shoppergroup[$i]->shopper_group_name;
					$data['shopper_group_desc'] = $params->shoppergroups->shoppergroup[$i]->shopper_group_desc;
					$data['custom_price_display'] = $params->shoppergroups->shoppergroup[$i]->custom_price_display;
					//$data['price_display'] = $params->shoppergroups->shoppergroup[$i]->price_display;
					$data['default']= $params->shoppergroups->shoppergroup[$i]->default;
					$data['ordering']= $params->shoppergroups->shoppergroup[$i]->ordering;
					$data['shared']= $params->shoppergroups->shoppergroup[$i]->shared;
					$data['published']= $params->shoppergroups->shoppergroup[$i]->published;
					
					$result = $modelShopperGroup->store($data);
					
					if ($result){
						$cpnIdsStr .= $params->shoppergroups->shoppergroup[$i]->shopper_group_name." ";
					}else{
						$allOk=false;
					}
				}
			
			} else {
				$price_d = $params->shoppergroups->shoppergroup->price_display;
				$price_d = str_replace("{", "", $price_d);
				$price_d = str_replace("}", "", $price_d);
				$price_d = str_replace('"', "", $price_d);
				$price_d_arr  =  explode(",", $price_d);
				
				foreach ($price_d_arr as $param){
					$parval=  explode(":", $param);
					$data[$parval[0]]= $parval[1];
					
				}
				$data["virtuemart_vendor_id"] = $params->shoppergroups->shoppergroup->vendor_id;
				$data['shopper_group_name'] = $params->shoppergroups->shoppergroup->shopper_group_name;
				$data['shopper_group_desc'] = $params->shoppergroups->shoppergroup->shopper_group_desc;
				$data['custom_price_display'] = $params->shoppergroups->shoppergroup->custom_price_display;
				//$data['price_display'] = $params->shoppergroups->shoppergroup[$i]->price_display;
				$data['default']= $params->shoppergroups->shoppergroup->default;
				$data['ordering']= $params->shoppergroups->shoppergroup->ordering;
				$data['shared']= $params->shoppergroups->shoppergroup->shared;
				$data['published']= $params->shoppergroups->shoppergroup->published;
				
				$result = $modelShopperGroup->store($data);
				
				if ($result){
					$commonReturn = new CommonReturn(OK,getWSMsg('ShopperGroup', ADD)." : ".$data['shopper_group_name'],$data['shopper_group_name']);
					return $commonReturn;
					
				}else {
					return new SoapFault("AddShopperGroupFault", getWSMsg('ShopperGroup', ADDKO)." :".$data['shopper_group_name'] );
				}
			}

			if ($allOk){
				$commonReturn = new CommonReturn(OK,getWSMsg('ShopperGroup', ALLOK)." : ".$cpnIdsStr,$cpnIdsStr);
				return $commonReturn;
			
			} else {
				return new SoapFault("ShopperGroupFault", getWSMsg('ShopperGroup', NOTALLOK).", only ShopperGroup  : ".$cpnIdsStr);
			}		
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Update ShopperGroup
	* (expose as WS)
    * @param string
    * @return result
    */
	function UpdateShopperGroup($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_upshopgrp']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
			
			setToken();
			
			$cpnIdsStr = "";
			$allOk=true;
			
			if (!class_exists( 'VirtueMartModelShopperGroup' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\shoppergroup.php');
			$modelShopperGroup = new VirtueMartModelShopperGroup;
			
			if (is_array($params->shoppergroups->shoppergroup)){
				
				$count = count($params->shoppergroups->shoppergroup);
				for ($i = 0; $i < $count; $i++) {
					
					$price_d = $params->shoppergroups->shoppergroup[$i]->price_display;
					$price_d = str_replace("{", "", $price_d);
					$price_d = str_replace("}", "", $price_d);
					$price_d = str_replace('"', "", $price_d);
					$price_d_arr  =  explode(",", $price_d);
					
					foreach ($price_d_arr as $param){
						$parval=  explode(":", $param);
						$data[$parval[0]]= $parval[1];
						
					}
					$data["virtuemart_shoppergroup_id"] = $params->shoppergroups->shoppergroup[$i]->shopper_group_id;
					$data["virtuemart_vendor_id"] = $params->shoppergroups->shoppergroup[$i]->vendor_id;
					$data['shopper_group_name'] = $params->shoppergroups->shoppergroup[$i]->shopper_group_name;
					$data['shopper_group_desc'] = $params->shoppergroups->shoppergroup[$i]->shopper_group_desc;
					$data['custom_price_display'] = $params->shoppergroups->shoppergroup[$i]->custom_price_display;
					//$data['price_display'] = $params->shoppergroups->shoppergroup[$i]->price_display;
					$data['default']= $params->shoppergroups->shoppergroup[$i]->default;
					$data['ordering']= $params->shoppergroups->shoppergroup[$i]->ordering;
					$data['shared']= $params->shoppergroups->shoppergroup[$i]->shared;
					$data['published']= $params->shoppergroups->shoppergroup[$i]->published;
					
					$result = $modelShopperGroup->store($data);
					
					if ($result){
						$cpnIdsStr .= $params->shoppergroups->shoppergroup[$i]->shopper_group_name." ";
					}else{
						$allOk=false;
					}
				}
			} else {
				$price_d = $params->shoppergroups->shoppergroup->price_display;
				$price_d = str_replace("{", "", $price_d);
				$price_d = str_replace("}", "", $price_d);
				$price_d = str_replace('"', "", $price_d);
				$price_d_arr  =  explode(",", $price_d);
				
				foreach ($price_d_arr as $param){
					$parval=  explode(":", $param);
					$data[$parval[0]]= $parval[1];
					
				}
				$data["virtuemart_shoppergroup_id"] = $params->shoppergroups->shoppergroup->shopper_group_id;
				$data["virtuemart_vendor_id"] = $params->shoppergroups->shoppergroup->vendor_id;
				$data['shopper_group_name'] = $params->shoppergroups->shoppergroup->shopper_group_name;
				$data['shopper_group_desc'] = $params->shoppergroups->shoppergroup->shopper_group_desc;
				$data['custom_price_display'] = $params->shoppergroups->shoppergroup->custom_price_display;
				//$data['price_display'] = $params->shoppergroups->shoppergroup[$i]->price_display;
				$data['default']= $params->shoppergroups->shoppergroup->default;
				$data['ordering']= $params->shoppergroups->shoppergroup->ordering;
				$data['shared']= $params->shoppergroups->shoppergroup->shared;
				$data['published']= $params->shoppergroups->shoppergroup->published;
				
				$result = $modelShopperGroup->store($data);
				
				if ($result!=false){
					$commonReturn = new CommonReturn(OK,getWSMsg('ShopperGroup', UP)." : ".$data['shopper_group_name'],$data['shopper_group_name']);
					return $commonReturn;
					
				}else {
					return new SoapFault("UpdateShopperGroupFault", getWSMsg('ShopperGroup', UPKO)." :".$data['shopper_group_name'] );
				}
			}

			if ($allOk){
				$commonReturn = new CommonReturn(OK,getWSMsg('ShopperGroup', ALLOK)." : ".$cpnIdsStr,$cpnIdsStr);
				return $commonReturn;
			
			} else {
				return new SoapFault("ShopperGroupFault", getWSMsg('ShopperGroup', NOTALLOK).", only ShopperGroup  : ".$cpnIdsStr);
			}	
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Delete ShopperGroup
	* (expose as WS)
    * @param string
    * @return result
    */
	function DeleteShopperGroup($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_delshopgroup']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
			
			
			// TODO gestion code retour
			$allOk=true;
			$cpnIdsStr="";
			
			if (!class_exists( 'VirtueMartModelShopperGroup' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\shoppergroup.php');
			$modelShopperGroup = new VirtueMartModelShopperGroup;
						
			if (is_array($params->ids->id)){
			
				$count = count($params->ids->id);
				for ($i = 0; $i < $count; $i++) {
					$id  = $params->ids->id[$i];
					$ids[] = $id;
					$result =$modelShopperGroup->remove($ids);
					if ($result){
						$cpnIdsStr .= $params->ids->id[$i]." ";
					}else{
						$allOk=false;
					}
				}
			} else {
				
				$id = $params->ids->id;
				$ids[] = $id;
				$cpnIdsStr .= $params->ids->id." ";
				$result =$modelShopperGroup->remove($ids);
				if ($result != false){
					$commonReturn = new CommonReturn(OK,getWSMsg('ShopperGroup', DEL)." : ".$cpnIdsStr,$cpnIdsStr);
					return $commonReturn;
				}else {
					return new SoapFault("DeleteShopperGroupFault", getWSMsg('ShopperGroup', DELKO)." : ".$cpnIdsStr);
				}
			}
			if ($allOk){
				$commonReturn = new CommonReturn(OK,getWSMsg('ShopperGroup', ALLOK)." : ".$cpnIdsStr,$cpnIdsStr);
				return $commonReturn;
			} else {
				return new SoapFault("DeleteShopperGroupFault", getWSMsg('ShopperGroup', NOTALLOK).", only ShopperGroup id : ".$cpnIdsStr);
			}			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	
	/**
    * This function  Get All Vendor
	* (expose as WS)
    * @param string
    * @return result
    */
	function GetAllVendor($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->login, $params->password);
		if ($conf['auth_users_getvendor']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
						
			if (!class_exists( 'VirtueMartModelVendor' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\vendor.php');
			$modelVendor = new VirtueMartModelVendor;
			
			$rows = $modelVendor->getVendors();
			
			foreach ($rows as $row){
				$Vendor = new Vendor($row->virtuemart_vendor_id,
										$row->vendor_name,
										$row->vendor_phone,
										$row->vendor_store_name,
										$row->vendor_store_desc,
										$row->vendor_currency,
										$row->vendor_image_path,
										$row->vendor_terms_of_service,
										$row->vendor_url,
										$row->vendor_min_pov,
										$row->vendor_freeshipping,
										$row->vendor_accepted_currencies,
										$row->vendor_address_format,
										$row->vendor_date_format,
										$row->config
										);
				$arrayVendor[]=$Vendor;
			}
			return $arrayVendor;
			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
	}
	
	/**
    * This function  Add Vendor
	* (expose as WS)
    * @param string
    * @return result
    */
	function AddVendor($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_addvendor']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
			
			setToken();	
			
			if (!class_exists( 'VirtueMartModelVendor' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\vendor.php');
			$modelVendor = new VirtueMartModelVendor;
			
			$data['vendor_name'] 				= $params->Vendor->vendor_name;
			$data['vendor_phone'] 				= $params->Vendor->vendor_phone;
			$data['vendor_store_name'] 			= $params->Vendor->vendor_store_name;
			$data['vendor_store_desc'] 			= $params->Vendor->vendor_store_desc;
			$data['vendor_currency'] 			= $params->Vendor->vendor_currency;
			$data['vendor_image_path'] 			= $params->Vendor->vendor_image_path;
			$data['vendor_terms_of_service'] 	= $params->Vendor->vendor_terms_of_service;
			$data['vendor_url'] 				= $params->Vendor->vendor_url;
			$data['vendor_min_pov'] 			= $params->Vendor->vendor_min_pov;
			$data['vendor_freeshipping'] 		= $params->Vendor->vendor_freeshipping;
			$data['vendor_address_format'] 		= $params->Vendor->vendor_address_format;
			$data['vendor_date_format'] 		= $params->Vendor->vendor_date_format;
			$data['config'] 					= $params->Vendor->config;
			
			$arr_cur = explode(',', $params->Vendor->vendor_accepted_currencies);
			$data['vendor_accepted_currencies'] = $arr_cur;
			
			$ret = $modelVendor->store($data);

			if ($ret){
				$commonReturn = new CommonReturn(OK,getWSMsg('Vendor', ADD)." : ".$data['vendor_name'],$ret);
				return $commonReturn;
			} else {
				return new SoapFault("AddVendorFault", getWSMsg('Vendor', ADDKO)." : ".$data['vendor_name']);
			}		
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Update Vendor
	* (expose as WS)
    * @param string
    * @return result
    */
	function UpdateVendor($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_upvendor']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			setToken();	
			
			if (!class_exists( 'VirtueMartModelVendor' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\vendor.php');
			$modelVendor = new VirtueMartModelVendor;
			
			$data['virtuemart_vendor_id'] 		= $params->Vendor->vendor_id;
			$data['vendor_name'] 				= $params->Vendor->vendor_name;
			$data['vendor_phone'] 				= $params->Vendor->vendor_phone;
			$data['vendor_store_name'] 			= $params->Vendor->vendor_store_name;
			$data['vendor_store_desc'] 			= $params->Vendor->vendor_store_desc;
			$data['vendor_currency'] 			= $params->Vendor->vendor_currency;
			$data['vendor_image_path'] 			= $params->Vendor->vendor_image_path;
			$data['vendor_terms_of_service'] 	= $params->Vendor->vendor_terms_of_service;
			$data['vendor_url'] 				= $params->Vendor->vendor_url;
			$data['vendor_min_pov'] 			= $params->Vendor->vendor_min_pov;
			$data['vendor_freeshipping'] 		= $params->Vendor->vendor_freeshipping;
			$data['vendor_address_format'] 		= $params->Vendor->vendor_address_format;
			$data['vendor_date_format'] 		= $params->Vendor->vendor_date_format;
			$data['config'] 					= $params->Vendor->config;
			
			$arr_cur = explode(',', $params->Vendor->vendor_accepted_currencies);
			$data['vendor_accepted_currencies'] = $arr_cur;
			
			$ret = $modelVendor->store($data);
			

			if ($ret){
				$commonReturn = new CommonReturn(OK,getWSMsg('Vendor', UP)." : ".$data['vendor_name'],$ret);
				return $commonReturn;
			} else {
				return new SoapFault("UpdateVendorFault", getWSMsg('Vendor', UPKO)." : ".$data['vendor_name']);
			}		
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function  Delete Vendor
	* (expose as WS)
    * @param string
    * @return result
    */
	function DeleteVendor($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_delvendor']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
			
			setToken();	
			
			$allOk=true;
			$cpnIdsStr="";
						
			if (!class_exists( 'VirtueMartModelVendor' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\vendor.php');
			$modelVendor = new VirtueMartModelVendor;
			
			if (is_array($params->ids->id)){
			
				$count = count($params->ids->id);
				for ($i = 0; $i < $count; $i++) {
					
									
					$data['virtuemart_vendor_id'] = $params->ids->id[$i];
					$result = $modelVendor->remove($data);
					
					if ($result != false){
						$cpnIdsStr .= $params->ids->id[$i]." ";
					}else{
						$allOk=false;
					}
				}
			} else {
				$cpnIdsStr .= $params->ids->id." ";
				$data['virtuemart_vendor_id'] = $params->ids->id;
				$result = $modelVendor->remove($data);
				
				if ($result != false){
					$commonReturn = new CommonReturn(OK,getWSMsg('Vendor', DEL)." : ".$params->ids->id,$params->ids->id);
					return $commonReturn;
				}else {
					return new SoapFault("DeleteVendorault", getWSMsg('Vendor', DELKO)."  : ".$params->ids->id);
				}
			}
			if ($allOk){
				$commonReturn = new CommonReturn(OK, getWSMsg('Vendor', ALLOK)." : ".$cpnIdsStr,$cpnIdsStr);
				return $commonReturn;
			} else {
				return new SoapFault("DeleteVendorFault", getWSMsg('Vendor', NOTALLOK).", only Vendor id : ".$cpnIdsStr);
			}			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function  Get All Vendor Category
	* (expose as WS)
    * @param string
    * @return result
    */
	function GetAllVendorCategory($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->login, $params->password);
		if ($conf['auth_users_getvendorcat']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			return new SoapFault("JoomlaServerAuthFault", "NOT IN VM2");
			$db = new ps_DB;

			$list  = "SELECT * FROM #__{vm}_vendor_category WHERE 1";
			$db->query($list);
			
			while ($db->next_record()) {
			
				$VendorCategory = new VendorCategory($db->f("vendor_category_id"),$db->f("vendor_category_name"),$db->f("vendor_category_desc"));
				$arrayVendorCategory[]=$VendorCategory;
			
			}
			return $arrayVendorCategory;
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
	}
	
	/**
    * This function  Add VendorCategory
	* (expose as WS)
    * @param string
    * @return result
    */
	function AddVendorCategory($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_addvendorcat']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			return new SoapFault("JoomlaServerAuthFault", "NOT IN VM2");
			$d = array(	'vendor_category_id' =>  "0", /* vendor_category_id not used but needed*/
						'vendor_category_name' =>  $params->VendorCategory->vendor_category_name,
						'vendor_category_desc' =>  $params->VendorCategory->vendor_category_desc
						);
			
			$ps_vendor_category = new ps_vendor_category;
			$ret = $ps_vendor_category->add($d);

			if ($ret){
				return "VendorCategory successfully added : ".$_REQUEST['vendor_category_id'];
			} else {
				return new SoapFault("AddVendorCategoryFault", "Cannot add Vendor Category  : ".$d['vendor_category_name']);
			}		
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Update VendorCategory
	* (expose as WS)
    * @param string
    * @return result
    */
	function UpdateVendorCategory($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_upvendorcat']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
			return new SoapFault("JoomlaServerAuthFault", "NOT IN VM2");
			$d = array(	'vendor_category_id' =>  $params->VendorCategory->vendor_category_id,
						'vendor_category_name' =>  $params->VendorCategory->vendor_category_name,
						'vendor_category_desc' =>  $params->VendorCategory->vendor_category_desc
						);
			
			$ps_vendor_category = new ps_vendor_category;
			$ret = $ps_vendor_category->update($d);

			if ($ret){
				return "VendorCategory successfully updated : ".$d['vendor_category_name'];
			} else {
				return new SoapFault("UpdateVendorCategoryFault", "Cannot update Vendor Category  : ".$d['vendor_category_name']);
			}		
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function  Delete VendorCategory
	* (expose as WS)
    * @param string
    * @return result
    */
	function DeleteVendorCategory($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_delvendorcat']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
			return new SoapFault("JoomlaServerAuthFault", "NOT IN VM2");
			$ps_vendor_category = new ps_vendor_category;
			$allOk=true;
			$cpnIdsStr="";
						
			if (is_array($params->ids->id)){
			
				$count = count($params->ids->id);
				for ($i = 0; $i < $count; $i++) {
					$d['vendor_category_id']  = $params->ids->id[$i];
					$result = $ps_vendor_category->delete($d);
					if ($result){
						$cpnIdsStr .= $params->ids->id[$i]." ";
					}else{
						$allOk=false;
					}
				}
			} else {
				$d['vendor_category_id'] = $params->ids->id;
				$cpnIdsStr .= $params->ids->id." ";
				$result = $ps_vendor_category->delete($d);
				if ($result){
					return "Vendor deleted sucessfully : ".$d['vendor_category_id']  ;
				}else {
					return new SoapFault("DeleteVendorault", "Cannot delete Vendor  : ".$d['vendor_category_id']);
				}
			}
			if ($allOk){
				return "All Vendors Categories successfully deleted : ".$cpnIdsStr;
			} else {
				return new SoapFault("DeleteVendorCategoryFault", "Not all Vendor Category deleted, only Vendor Category id : ".$cpnIdsStr);
			}			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function   Get All Manufacturer
	* (expose as WS)
    * @param string
    * @return result
    */
	function GetAllManufacturer($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->login, $params->password);
		if ($conf['auth_users_getmanufacturer']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'VirtueMartModelManufacturer' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\manufacturer.php');
			$modelManufacturer = new VirtueMartModelManufacturer;
			
			$rows = $modelManufacturer->getManufacturers();
						
			foreach ($rows as $row){
				$Manufacturer = new Manufacturer($row->virtuemart_manufacturer_id,
													$row->mf_name,
													$row->slug,
													$row->mf_email,
													$row->mf_desc,
													$row->virtuemart_manufacturercategories_id ,
													$row->mf_url,
													$row->hits,
													$row->published);
				$arrayManufacturer[]=$Manufacturer;
			}
			return $arrayManufacturer;
		
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
	}
	
	/**
    * This function  Add Manufacturer
	* (expose as WS)
    * @param string
    * @return result
    */
	function AddManufacturer($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_addmanufacturer']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			setToken();
			
			if (!class_exists( 'VirtueMartModelManufacturer' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\manufacturer.php');
			$modelManufacturer = new VirtueMartModelManufacturer;
			
			$data['mf_name']= $params->Manufacturer->mf_name;
			$data['slug']= $params->Manufacturer->slug;
			$data['mf_email']= $params->Manufacturer->mf_email;
			$data['mf_desc']= $params->Manufacturer->mf_desc;
			$data['mf_category_id']= $params->Manufacturer->mf_category_id;
			$data['hits']= $params->Manufacturer->hits;
			$data['published']= isset($params->Manufacturer->published) ? $params->Manufacturer->published : 1;
						
			$res = $modelManufacturer->store($data);
			
			if ($res){
				$commonReturn = new CommonReturn(OK,getWSMsg('Manufacturer', ADD)." : ".$params->Manufacturer->mf_name,$params->Manufacturer->mf_name);
				return $commonReturn;
			} else {
				return new SoapFault("AddManufacturerFault", getWSMsg('Manufacturer', ADDKO)."  : ".$params->Manufacturer->mf_name);
			}		
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Update Manufacturer
	* (expose as WS)
    * @param string
    * @return result
    */
	function UpdateManufacturer($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_upmanufacturer']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
			
			setToken();
			
			if (!class_exists( 'VirtueMartModelManufacturer' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\manufacturer.php');
			$modelManufacturer = new VirtueMartModelManufacturer;
			
			$data['virtuemart_manufacturer_id']= $params->Manufacturer->manufacturer_id;
			$data['mf_name']= $params->Manufacturer->mf_name;
			$data['slug']= $params->Manufacturer->slug;
			$data['mf_email']= $params->Manufacturer->mf_email;
			$data['mf_desc']= $params->Manufacturer->mf_desc;
			$data['mf_category_id']= $params->Manufacturer->mf_category_id;
			$data['hits']= $params->Manufacturer->hits;
			$data['published']= isset($params->Manufacturer->published) ? $params->Manufacturer->published : 1;
						
			$res = $modelManufacturer->store($data);

			if ($res){
				$commonReturn = new CommonReturn(OK,getWSMsg('Manufacturer', UP)." : ".$data['mf_name'],$data['mf_name']);
				return $commonReturn;
			} else {
				return new SoapFault("ManufacturerFault", getWSMsg('Manufacturer', UPKO)." : ".$d['mf_name']);
			}		
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function  Delete Manufacturer
	* (expose as WS)
    * @param string
    * @return result
    */
	function DeleteManufacturer($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_delmanufacturer']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
					
			if (!class_exists( 'TableManufacturers' )) require (JPATH_VM_ADMINISTRATOR.DS.'tables\manufacturers.php');
			$db = JFactory::getDBO();
			$tableManufacturers = new TableManufacturers($db);
			
			
			
			$allOk=true;
			$cpnIdsStr="";
						
			if (is_array($params->ids->id)){
			
				$count = count($params->ids->id);
				for ($i = 0; $i < $count; $i++) {
					$tableManufacturers->virtuemart_manufacturer_id  = $params->ids->id[$i];
					$result = $tableManufacturers->delete();
					if ($result){
						$cpnIdsStr .= $params->ids->id[$i]." ";
					}else{
						$allOk=false;
					}
				}
			} else {

				$cpnIdsStr .= $params->ids->id." ";
				$tableManufacturers->virtuemart_manufacturer_id  = $params->ids->id;
				$result = $tableManufacturers->delete();
				if ($result){
					$commonReturn = new CommonReturn(OK,getWSMsg('Manufacturer', DEL)." : ".$tableManufacturers->virtuemart_manufacturer_id,$tableManufacturers->virtuemart_manufacturer_id);
					return $commonReturn;
				}else {
					return new SoapFault("DeleteManufacturerFault", getWSMsg('Manufacturer', DELKO)." : ".$tableManufacturers->virtuemart_manufacturer_id);
				}
			}
			if ($allOk){
				$commonReturn = new CommonReturn(OK,"All Manufacturer successfully deleted : ".$cpnIdsStr,$cpnIdsStr);
				return $commonReturn;
				//return "All Manufacturer successfully deleted : ".$cpnIdsStr;
			} else {
				return new SoapFault("DeleteManufacturerFault", "Not all Manufacturer deleted, only Manufacturer id : ".$cpnIdsStr);
			}			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function   Get All Manufacturer cat
	* (expose as WS)
    * @param string
    * @return result
    */
	function GetAllManufacturerCat($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->login, $params->password);
		if ($conf['auth_users_getmanufacturercat']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
			
			
			if (!class_exists( 'VirtuemartModelManufacturercategories' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\manufacturercategories.php');
			$modelManufacturercategories = new VirtuemartModelManufacturercategories;
			
			$rows = $modelManufacturercategories->getManufacturerCategories();
				
			/*query  = "SELECT * FROM `#__virtuemart_manufacturercategories` WHERE 1 ";	
			$db->setQuery($query);
			$rows = $db->loadObjectList();*/
			
			foreach ($rows as $row){
				$ManufacturerCat = new ManufacturerCat($row->virtuemart_manufacturercategories_id,
														$row->mf_category_name,
														$row->mf_category_desc,
														$row->published);
				$arrayManufacturerCat[]=$ManufacturerCat;
			}
			return $arrayManufacturerCat;
			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
	}
	
	/**
    * This function  Add Manufacturer cat
	* (expose as WS)
    * @param string
    * @return result
    */
	function AddManufacturerCat($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_addmanufacturercat']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			/*if (!class_exists( 'TableManufacturercategories' )) require (JPATH_VM_ADMINISTRATOR.DS.'tables\manufacturercategories.php');
			$db = JFactory::getDBO();
			$tableManufacturercategories = new TableManufacturercategories($db);
			
			$tableManufacturercategories->mf_category_name = $params->ManufacturerCat->mf_category_name;
			$tableManufacturercategories->mf_category_desc = $params->ManufacturerCat->mf_category_desc;
			$tableManufacturercategories->published 	   = isset($params->ManufacturerCat->published) ? $params->ManufacturerCat->published : 1;
			
			$res = $tableManufacturercategories->store();*/
			
			if (!class_exists( 'VirtuemartModelManufacturercategories' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\manufacturercategories.php');
			$modelManufacturercategories = new VirtuemartModelManufacturercategories;
			
			$data['mf_category_name'] = $params->ManufacturerCat->mf_category_name;
			$data['mf_category_desc'] = $params->ManufacturerCat->mf_category_desc;
			$data['published'] = isset($params->ManufacturerCat->published) ? $params->ManufacturerCat->published : 1;
			
			$res = $modelManufacturercategories->store($data);

			if ($res != false){
				$commonReturn = new CommonReturn(OK,getWSMsg('Manufacturer Categorie', ADD)." : ".$params->ManufacturerCat->mf_category_name,$res);
				return $commonReturn;
			} else {
				return new SoapFault("AddManufacturerCatFault", getWSMsg('Manufacturer Categorie', ADDKO)." : ".$d['mf_category_name']);
			}		
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Update ManufacturerCat
	* (expose as WS)
    * @param string
    * @return result
    */
	function UpdateManufacturerCat($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_upmanufacturercat']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			/*if (!class_exists( 'TableManufacturercategories' )) require (JPATH_VM_ADMINISTRATOR.DS.'tables\manufacturercategories.php');
			$db = JFactory::getDBO();
			$tableManufacturercategories = new TableManufacturercategories($db);
			
			$tableManufacturercategories->virtuemart_manufacturercategories_id 	 = $params->ManufacturerCat->mf_category_id;
			$tableManufacturercategories->mf_category_name = $params->ManufacturerCat->mf_category_name;
			$tableManufacturercategories->mf_category_desc = $params->ManufacturerCat->mf_category_desc;
			$tableManufacturercategories->published 	   = isset($params->ManufacturerCat->published) ? $params->ManufacturerCat->published : 1;
			
			$res = $tableManufacturercategories->store();*/
			
			if (!class_exists( 'VirtuemartModelManufacturercategories' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\manufacturercategories.php');
			$modelManufacturercategories = new VirtuemartModelManufacturercategories;
			
			$data['virtuemart_manufacturercategories_id'] = $params->ManufacturerCat->mf_category_id;
			$data['mf_category_name'] = $params->ManufacturerCat->mf_category_name;
			$data['mf_category_desc'] = $params->ManufacturerCat->mf_category_desc;
			$data['published'] = isset($params->ManufacturerCat->published) ? $params->ManufacturerCat->published : 1;
			
			$res = $modelManufacturercategories->store($data);
		
			if ($res != false){
				$commonReturn = new CommonReturn(OK, getWSMsg('Manufacturer Categorie', UP)." : ".$params->ManufacturerCat->mf_category_name,$res);
				return $commonReturn;
			} else {
				return new SoapFault("UpdateManufacturerCatFault",getWSMsg('Manufacturer Categorie', UPKO)." : ".$data['mf_category_name']);
			}		
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function  Delete ManufacturerCat
	* (expose as WS)
    * @param string
    * @return result
    */
	function DeleteManufacturerCat($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_users_delmanufacturercat']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
					
			$allOk=true;
			$cpnIdsStr="";
			
			if (!class_exists( 'VirtuemartModelManufacturercategories' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\manufacturercategories.php');
			$modelManufacturercategories = new VirtuemartModelManufacturercategories;
			
			
						
			if (is_array($params->ids->id)){
			
				$count = count($params->ids->id);
				for ($i = 0; $i < $count; $i++) {
					$cat_id  = $params->ids->id[$i];
					$result = $modelManufacturercategories->removeManufacturerCategories($cat_id);
					if ($result){
						$cpnIdsStr .= $params->ids->id[$i]." ";
					}else{
						$allOk=false;
					}
				}
			} else {
				$cat_id = $params->ids->id;
				$cpnIdsStr .= $params->ids->id." ";
				$result = $modelManufacturercategories->removeManufacturerCategories($cat_id);
				if ($result){
					$commonReturn = new CommonReturn(OK,getWSMsg('Manufacturer Categorie', DEL)." : ".$cat_id ,$cat_id );
					return $commonReturn;
					
				}else {
					return new SoapFault("DeleteManufacturerCatFault", getWSMsg('Manufacturer Categorie', DELKO)."  : ".$d['mf_category_id']);
				}
			}
			if ($allOk){
				$commonReturn = new CommonReturn(OK,getWSMsg('Manufacturer Categorie', ALLOK)." : ".$cpnIdsStr ,$cpnIdsStr );
				return $commonReturn;
			} else {
				return new SoapFault("DeleteManufacturerCatFault", getWSMsg('Manufacturer Categorie', NOTALLOK)." , only Manufacturer id : ".$cpnIdsStr);
			}			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function get Get Available vendor Images on server (dir components/com_virtuemart/shop_image/vendor)
	* (expose as WS)
    * @param string
    * @return array of products
   */
	function GetAvailableVendorImages($params) {
	
		include('../vm_soa_conf.php');
		
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->login, $params->password);
		if ($conf['auth_users_getvendorimg']=="off"){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			$mosConfig_absolute_path= realpath( dirname(__FILE__).'/../../../..' );
			// Load the joomla main cfg
			if( file_exists(dirname(__FILE__).'/configuration.php' )) {
				require_once( $mosConfig_absolute_path.'/configuration.php' );
				
			} else {
				require_once( $mosConfig_absolute_path.'/configuration.php');
			}
		
			global $mosConfig_live_site;
			$URL_BASE;
			if( $mosConfig_live_site[strlen( $mosConfig_live_site)-1] == '/' ) {
				$URL_BASE = $mosConfig_live_site;
			}
			else {
				$URL_BASE = $mosConfig_live_site.'/';
			}
			
			$INSTALLURL = '';
			if (empty($conf['BASESITE']) && empty($conf['URL'])){
				$INSTALLURL = $URL_BASE;
			} else if (!empty($conf['BASESITE'])){
				$INSTALLURL = 'http://'.$conf['URL'].'/'.$conf['BASESITE'].'/';
			} else {
				$INSTALLURL = 'http://'.$conf['URL'].'/';
			}
			
			$dir = realpath( dirname(__FILE__).'/../../../../components/com_virtuemart/assets/images/vendors' );
			$dirname = $dir;
			//$dir = "/tmp/php5";
			// Ouvre un dossier bien connu, et liste tous les fichiers
			if (is_dir($dir)) {
				if ($dh = opendir($dir)) {
					while (($file = readdir($dh)) !== false) {
						//echo "fichier : $file : type : " . filetype($dir . $file) . "\n";
						if ($file =="." || $file ==".." || $file =="index.html"){
							
						} else {
							$AvalaibleImage = new AvalaibleImage($file,$INSTALLURL.'components/com_virtuemart/assets/images/vendors/'.$file,$dirname);
							$AvalaibleImageArray[] = $AvalaibleImage;
						}
					}
					closedir($dh);
				}
			}
					
			return $AvalaibleImageArray;

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}		
	}
	
		/**
    * This function Get Versions
	* (expose as WS)
    * @param string
    * @return 
   */
	function GetVersions($params) {
	
		include('../vm_soa_conf.php');
		/*$mosConfig_absolute_path= realpath( dirname(__FILE__).'/../../../..' );
		include($mosConfig_absolute_path.'/administrator/components/com_virtuemart/version.php');*/
		
		if (!class_exists( 'vmVersion' )) require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'version.php');
		//$VMVersion = vmVersion::RELEASE;
		
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->login, $params->password);
		if ($conf['auth_users_getversion']=="off"){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			$_VERSION = new JVersion();
			$VMVERSION = new vmVersion();
			//global $database;
			$db = JFactory::getDBO();
			
			$version['SOA_For_Virtuemart_Version'] = "2.0.0-Beta1";//$conf['version'];
			$version['Joomla_Version'] = $_VERSION->RELEASE;
			$version['Virtuemart_Version'] = vmVersion::$RELEASE;
			$version['Database_Version'] = $db->getVersion();//$database->getVersion();
			$version['Author'] = $conf['author'];
			$version['PHP_Version'] = phpversion();
			$version['URL'] = "http://www.virtuemart-datamanager.com";
					
			return $version;

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}		
	}
	
	
	/**
    *  function Authentification
	* (expose as WS)
    * @param login/pass
    * @return order details
   */
	function Authentification($params) {
		
		$result = onAdminAuthenticate($params->login, $params->password);
		
		if ($result == "true"){
			$token  = JUtility::getToken();
			$commonReturn = new CommonReturn(OK,"Autification OK for ".$params->login,$token);
			return $commonReturn;
			
			return ; 
		}else if ($result== "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}

	}
	
	
	
	
	
	/* SOAP SETTINGS */
	if ($vmConfig->get('soap_ws_user_on')==1){

		/* SOAP SETTINGS */
		$cache = "0";
		if ($conf['users_cache'] == "on")$cache = "1";
		ini_set("soap.wsdl_cache_enabled", $cache); // wsdl cache settings
		
		if ($conf['soap_version'] == "SOAP_1_1"){
			$options = array('soap_version' => SOAP_1_1);
		}else {
			$options = array('soap_version' => SOAP_1_2);
		}
		
		/** SOAP SERVER **/
		if (empty($conf['BASESITE']) && empty($conf['URL'])){
			$server = new SoapServer(JURI::root(false).'/VM_UsersWSDL.php');
		}else if (!empty($conf['BASESITE'])){
			$server = new SoapServer('http://'.$conf['URL'].'/'.$conf['BASESITE'].'/administrator/components/com_virtuemart/services/VM_UsersWSDL.php');
		}else {
			$server = new SoapServer('http://'.$conf['URL'].'/administrator/components/com_virtuemart/services/VM_UsersWSDL.php');
		}
				
		/* Add Functions */
		$server->addFunction("GetUsers");
		$server->addFunction("Authentification");
		$server->addFunction("AddUser");
		$server->addFunction("DeleteUser");
		$server->addFunction("SendMail");
		$server->addFunction("GetUserFromEmailOrUsername");
		$server->addFunction("GetAllCountryCode");
		$server->addFunction("GetAuthGroup");
		$server->addFunction("AddAuthGroup");
		$server->addFunction("DeleteAuthGroup");	
		$server->addFunction("GetAllStates");
		$server->addFunction("AddStates");
		$server->addFunction("DeleteStates");		
		$server->addFunction("GetShopperGroup");
		$server->addFunction("AddShopperGroup");		
		$server->addFunction("UpdateShopperGroup");
		$server->addFunction("DeleteShopperGroup");
		$server->addFunction("UpdateUser");
		$server->addFunction("AddVendor");
		$server->addFunction("GetAllVendor");
		$server->addFunction("UpdateVendor");
		$server->addFunction("DeleteVendor");
		$server->addFunction("GetAllVendorCategory");
		$server->addFunction("AddVendorCategory");
		$server->addFunction("UpdateVendorCategory");
		$server->addFunction("DeleteVendorCategory");	
		$server->addFunction("GetAllManufacturer");
		$server->addFunction("AddManufacturer");
		$server->addFunction("UpdateManufacturer");
		$server->addFunction("DeleteManufacturer");
		$server->addFunction("GetAllManufacturerCat");
		$server->addFunction("AddManufacturerCat");
		$server->addFunction("UpdateManufacturerCat");
		$server->addFunction("DeleteManufacturerCat");
		$server->addFunction("GetAvailableVendorImages");
		$server->addFunction("GetVersions");
		$server->addFunction("GetAdditionalUserInfo");
		$server->addFunction("GetSessions");
		$server->addFunction("GetWaitingList");
		$server->addFunction("NotifyWaitingList");
		$server->addFunction("GetUserInfoFromOrderID");
		
		$server->handle();
		
	}else{
		echo "This Web Service (Users) is disabled";
	}
?> 