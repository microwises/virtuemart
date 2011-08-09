<?php

define( '_VALID_MOS', 1 );
define( '_JEXEC', 1 );

/**
 * Virtuemart Order SOA Connector
 *
 * Virtuemart Order SOA Connector (Provide functions getOrdersFromStatus, getOrderStatus, getOrder, getAllOrders)
 *
 * @package    com_vm_soa
 * @subpackage component
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  2011 Mickael Cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    $Id:$
 */

 /** loading framework **/
include_once('VM_Commons.php');

/**
 * Class OrderStatus
 *
 * Class "OrderStatus" with attribute : id, name, code,
 * 
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class OrderStatus {
	
		public $order_status_id="";
		public $order_status_code="";		
		public $order_status_name="";	
		public $order_status_description="";
		public $ordering="";		
		public $vendor_id="";
		public $published="";
		
		
		function __construct($order_status_id,$order_status_code,$order_status_name,$order_status_description,$ordering,$vendor_id,$published){
		
			$this->order_status_id			=$order_status_id;
			$this->order_status_code		=$order_status_code;		
			$this->order_status_name		=$order_status_name;	
			$this->order_status_description	=$order_status_description;
			$this->ordering					=$ordering;		
			$this->vendor_id				=$vendor_id;
			$this->published				=$published;			
		
		}
	
	}
	
/**
 * Class Order
 *
 * Class "Order" with attribute : id, user_id, vendor_id,  order_number, user_info_id , order_total order_subtotal
 * order_tax, order_tax_details order_shipping, coupon_discount order_currency ...)
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class Order {
		public $id="";
		public $user_id="";		
		public $vendor_id="";		
		public $order_number="";
		public $order_pass="";
		public $user_info_id="";
		public $order_total="";
		public $order_subtotal="";
		public $order_tax="";
		public $order_tax_details="";
		public $order_shipping="";
		public $order_shipping_tax="";
		public $coupon_discount="";
		public $coupon_code="";
		public $order_discount="";
		public $order_currency="";
		public $order_status="";
		public $user_currency_id="";
		public $user_currency_rate="";
		public $payment_method_id="";
		public $ship_method_id="";
		public $customer_note="";
		public $ip_address="";
		public $created_on="";
		public $modified_on="";
																					
		//constructeur
		function __construct($id,$user_id,$vendor_id,$order_number,$order_pass,$user_info_id,$order_total,$order_subtotal,$order_tax,$order_tax_details,$order_shipping,$order_shipping_tax,
		$coupon_discount,$coupon_code,$order_discount,$order_currency,$order_status,$user_currency_id,$user_currency_rate,$payment_method_id,$ship_method_id,$customer_note,$ip_address,$created_on,$modified_on) {

			$this->id					=$id;
			$this->user_id				=$user_id;	
			$this->vendor_id			=$vendor_id;			
			$this->order_number			=$order_number;
			$this->order_pass			=$order_pass;
			$this->user_info_id			=$user_info_id;
			$this->order_total			=$order_total;
			$this->order_subtotal		=$order_subtotal;
			$this->order_tax			=$order_tax;
			$this->order_tax_details	=$order_tax_details;
			$this->order_shipping		=$order_shipping;
			$this->order_shipping_tax	=$order_shipping_tax;
			$this->coupon_discount		=$coupon_discount;
			$this->coupon_code			=$coupon_code;
			$this->order_discount		=$order_discount;
			$this->order_currency		=$order_currency;
			$this->order_status			=$order_status;
			$this->user_currency_id		=$user_currency_id;
			$this->user_currency_rate	=$user_currency_rate;
			$this->payment_method_id	=$payment_method_id;
			$this->ship_method_id		=$ship_method_id;
			$this->customer_note		=$customer_note;
			$this->ip_address			=$ip_address;
			$this->created_on			=$created_on;
			$this->modified_on			=$modified_on;
		}
	}
	
  /**
 * Class ShippingRate
 *
 * Class "ShippingRate" with attribute : shipping_rate_id ...,
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class ShippingRate { //NOT IN VM2
		public $shipping_rate_id="";
		public $shipping_rate_name="";		
		public $shipping_rate_carrier_id="";		
		public $shipping_rate_country="";
		public $shipping_rate_zip_start="";
		public $shipping_rate_zip_end="";
		public $shipping_rate_weight_start="";
		public $shipping_rate_weight_end="";
		public $shipping_rate_value="";
		public $shipping_rate_package_fee="";
		public $shipping_rate_currency_id="";
		public $shipping_rate_vat_id="";
		public $shipping_rate_list_order="";
													
		//constructeur
		function __construct($shipping_rate_id,$shipping_rate_name,$shipping_rate_carrier_id,$shipping_rate_country,$shipping_rate_zip_start,
		$shipping_rate_zip_end,$shipping_rate_weight_start,$shipping_rate_weight_end,$shipping_rate_value,$shipping_rate_package_fee,$shipping_rate_currency_id,
		$shipping_rate_vat_id,$shipping_rate_list_order) {

			$this->shipping_rate_id=$shipping_rate_id;
			$this->shipping_rate_name=$shipping_rate_name;	
			$this->shipping_rate_carrier_id=$shipping_rate_carrier_id;			
			$this->shipping_rate_country=$shipping_rate_country;
			$this->shipping_rate_zip_start=$shipping_rate_zip_start;
			$this->shipping_rate_zip_end=$shipping_rate_zip_end;
			$this->shipping_rate_weight_start=$shipping_rate_weight_start;
			$this->shipping_rate_weight_end=$shipping_rate_weight_end;
			$this->shipping_rate_value=$shipping_rate_value;
			$this->shipping_rate_package_fee=$shipping_rate_package_fee;
			$this->shipping_rate_currency_id=$shipping_rate_currency_id;
			$this->shipping_rate_vat_id=$shipping_rate_vat_id;
			$this->shipping_rate_list_order=$shipping_rate_list_order;
			
		}
	}
	
  	/**
	 * Class Coupon
	 *
	 * Class "Coupon" with attribute : coupon_id ...
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class Coupon {
	
		public $coupon_id="";
		public $coupon_code="";
		public $percent_or_total="";		
		public $coupon_type="";	
		public $coupon_value="";
		public $coupon_start_date="";	
		public $coupon_expiry_date="";	
		public $coupon_value_valid="";	
		public $published="";	
		
		function __construct($coupon_id, $coupon_code,$percent_or_total,$coupon_type,$coupon_value,$coupon_start_date,$coupon_expiry_date,$coupon_value_valid,$published){
		
			$this->coupon_id=$coupon_id;
			$this->coupon_code=$coupon_code;
			$this->percent_or_total=$percent_or_total;		
			$this->coupon_type=$coupon_type;	
			$this->coupon_value=$coupon_value;	
			$this->coupon_start_date=$coupon_start_date;	
			$this->coupon_expiry_date=$coupon_expiry_date;	
			$this->coupon_value_valid=$coupon_value_valid;	
			$this->published=$published;	
			
		}
	
	}
	
/**
 * Class ShippingCarrier
 *
 * Class "ShippingCarrier" with attribute : shipping_carrier_id ...
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class ShippingCarrier {
	
		public $shipping_carrier_id="";
		public $virtuemart_vendor_id="";		
		public $shipping_carrier_jplugin_id="";	
		public $shipping_carrier_name="";	
		public $shipping_carrier_desc="";	
		public $shipping_carrier_element="";	
		public $shipping_carrier_params="";	
		public $shipping_carrier_value="";	
		public $shipping_carrier_package_fee="";	
		public $shipping_carrier_vat_id="";	
		public $ordering="";	
		public $shared="";	
		public $published="";	
		
		function __construct($shipping_carrier_id,$virtuemart_vendor_id,$shipping_carrier_jplugin_id,$shipping_carrier_name,$shipping_carrier_desc
							,$shipping_carrier_element,$shipping_carrier_params,$shipping_carrier_value,$shipping_carrier_package_fee
							,$shipping_carrier_vat_id,$ordering,$shared,$published){
		
			$this->shipping_carrier_id=$shipping_carrier_id;
			$this->virtuemart_vendor_id=$virtuemart_vendor_id;		
			$this->shipping_carrier_jplugin_id=$shipping_carrier_jplugin_id;
			$this->shipping_carrier_name=$shipping_carrier_name;
			$this->shipping_carrier_desc=$shipping_carrier_desc;
			$this->shipping_carrier_element=$shipping_carrier_element;
			$this->shipping_carrier_params=$shipping_carrier_params;
			$this->shipping_carrier_value=$shipping_carrier_value;
			$this->shipping_carrier_package_fee=$shipping_carrier_package_fee;
			$this->shipping_carrier_vat_id=$shipping_carrier_vat_id;
			$this->ordering=$ordering;
			$this->shared=$shared;
			$this->published=$published;
			
		}
	}	
	
	  /**
 * Class PaymentMethod
 *
 * Class "PaymentMethod" with attribute : payment_method_id ...,
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class PaymentMethod {
		public $payment_method_id="";
		public $virtuemart_vendor_id="";		
		public $payment_jplugin_id="";		
		public $payment_name="";
		public $payment_element="";
		public $discount="";
		public $discount_is_percentage="";
		public $discount_max_amount="";
		public $discount_min_amount="";
		public $payment_params="";
		public $shared="";
		public $ordering="";
		public $published="";
		public $payment_enabled="";
		public $accepted_creditcards="";
		public $payment_extrainfo="";
		
		//constructeur
		function __construct($payment_method_id,$virtuemart_vendor_id,$payment_jplugin_id,$payment_name,$payment_element,
		$discount,$discount_is_percentage,$discount_max_amount,$discount_min_amount,$payment_params,$shared,$ordering,$published) {

			$this->payment_method_id=$payment_method_id;
			$this->virtuemart_vendor_id=$virtuemart_vendor_id;	
			$this->payment_jplugin_id=$payment_jplugin_id	;			
			$this->payment_name=$payment_name;
			$this->payment_element=$payment_element;
			$this->discount=$discount;
			$this->discount_is_percentage=$discount_is_percentage;
			$this->discount_max_amount=$discount_max_amount;
			$this->discount_min_amount=$discount_min_amount;
			$this->payment_params=$payment_params;
			$this->shared=$shared;
			$this->ordering=$ordering;
			$this->published=$published;
			
			
		}
	}
	
/**
 * Class Creditcard
 *
 * Class "Creditcard" with attribute : creditcard_id ...
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class Creditcard {
	
		public $creditcard_id="";
		public $vendor_id="";		
		public $creditcard_name="";	
		public $creditcard_code="";	
		public $shared="";	
		public $published="";	
		
		function __construct($creditcard_id,$vendor_id,$creditcard_name,$creditcard_code,$shared,$published){
		
			$this->creditcard_id	=$creditcard_id;
			$this->vendor_id		=$vendor_id;		
			$this->creditcard_name	=$creditcard_name;	
			$this->creditcard_code	=$creditcard_code;
			$this->shared			=$shared;
			$this->published		=$published;
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
    * This function getOrderStatus return all status avalaible
	* (expose as WS)
    * @param 
    * @return array of Status
    */
	function getOrderStatus($params) {
		
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->login, $params->password);
		if ($conf['auth_order_getstatus']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'VirtueMartModelOrderstatus' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\orderstatus.php');
			$VirtueMartModelOrderstatus = new VirtueMartModelOrderstatus;
			
			$listStatus = $VirtueMartModelOrderstatus->getOrderStatusList();
			
			foreach ($listStatus as $status)
			{
				$OrderStatus = new OrderStatus($status->virtuemart_orderstate_id,
									$status->order_status_code,
									$status->order_status_name,
									$status->order_status_description,
									$status->ordering,
									$status->virtuemart_vendor_id,
									$status->published);
				$arrayStatus[]= $OrderStatus;
			}
			return $arrayStatus;
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
	}
	
	
	/**
    * This is generic function to get order details 
	* (NOT expose as WS)
    * @param Object
    * @return order details
    */
	function getOrderGeneric($params) {
			
			if (empty($params->limite_start)){
				$params->limite_start="0";
			}
			if (empty($params->limite_end)){
				$params->limite_end="100";
			}
			
			$db = JFactory::getDBO();	
			$query  = "SELECT * FROM `#__virtuemart_orders` WHERE 1 ";
			
			if (!empty($params->status)){
				$query .= " AND order_status = '$params->status' ";
			}
			if (!empty($params->order_id)){
				$query .= " AND virtuemart_order_id = '$params->order_id' ";
			}
			if (!empty($params->order_number)){
				$query .= " AND order_number = '$params->order_number' ";
			}
			
			//format date en entree : '2011-07-25 00:00:00' ou '2011-07-18'
			if (!empty($params->date_start)){
				$query .= " AND created_on BETWEEN '$params->date_start' AND '$params->date_end' ";
			}
			
			$query .= " ORDER BY virtuemart_order_id desc ";
			$query .= " LIMIT $params->limite_start, $params->limite_end "; 
			
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			//return new SoapFault("TEST", "TEST VM_ID: ".$query."\n".$row->created_on."\n".$date_end);
			foreach ($rows as $row){
				
				$Order = new Order($row->virtuemart_order_id,
									$row->virtuemart_user_id,
									$row->virtuemart_vendor_id,
									$row->order_number,
									$row->order_pass,
									$row->virtuemart_userinfo_id ,
									$row->order_total,
									$row->order_subtotal,
									$row->order_tax,
									$row->order_tax_details,
									$row->order_shipping,
									$row->order_shipping_tax,
									$row->coupon_discount,
									$row->coupon_code,
									$row->order_discount,
									$row->order_currency,
									$row->order_status,
									$row->user_currency_id,
									$row->user_currency_rate,
									$row->payment_method_id,
									$row->ship_method_id,
									$row->customer_note,
									$row->ip_address,
									$row->created_on, 
									$row->modified_on 
									);
				$orderArray[]=$Order;
			
			}
			return $orderArray;
	}
	
	
	/**
    * This function get order details from order id
	* (expose as WS)
    * @param string The id of the order
    * @return order details
    */
	function getOrder($params) {
	
		include('../vm_soa_conf.php');
		
		$order_id=$params->order_id;
			
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_getorder']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			
			$ord = getOrderGeneric($params);
			return $ord[0];
			/*if (!class_exists( 'VirtueMartModelOrders' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\orders.php');
			$VirtueMartModelOrders = new VirtueMartModelOrders;
			
			$orderInfo = $VirtueMartModelOrders->getOrder($order_id);
			$orderInfo['details'];
			$orderInfo['history'];
			$orderInfo['items'];
			
			//TODO : A terminer 
			$Order = new Order($orderInfo['details']->order_id,$orderInfo['details']->user_id,$orderInfo['details']->vendor_id, $orderInfo['details']->order_number, $orderInfo['details']->user_info_id, $orderInfo['details']->order_total, $orderInfo['details']->order_subtotal,
				$orderInfo['details']->order_tax, $orderInfo['details']->order_tax_details, $orderInfo['details']->order_shipping, $orderInfo['details']->order_shipping_tax, $orderInfo['details']->coupon_discount, $orderInfo['details']->coupon_code, $orderInfo['details']->order_discount, $orderInfo['details']->order_currency,
				$orderInfo['details']->order_status, $orderInfo['details']->cdate, $orderInfo['details']->mdate, $orderInfo['details']->ship_method_id, $orderInfo['details']->customer_note, $orderInfo['details']->ip_address);
			
			return $Order;*/
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function get all orders with specified status P, C, R etc...
	* (expose as WS)
    * @param string params (limiteStart, LimitEnd, Status)
    * @return array of orders
    */
	function getOrdersFromStatus($params) {
	
		include('../vm_soa_conf.php');
		/* Authenticate*/
		
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_getfromstatus']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
			if (empty($params->limite_start)){
				$params->limite_start="0";
			}
			if (empty($params->limite_end)){
				$params->limite_end="100";
			}
			
			return getOrderGeneric($params);
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function get all Orders
	* (expose as WS)
    * @param string params (limiteStart, LimitEnd)
    * @return array of Categories
    */
	function getAllOrders($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_getall']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
		
			if (empty($params->limite_start)){
				$params->limite_start="0";
			}
			if (empty($params->limite_end)){
				$params->limite_end="100";
			}
			
			return getOrderGeneric($params);
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}

	/**
    * This function UpdateOrderStatus
	* (expose as WS)
    * @param string params (user, pass, orderid, status, comment)
    * @return string result
    */
	function UpdateOrderStatus($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_updatestatus']=="off"){
			$result = "true";
		}
		
		$mosConfig_absolute_path= realpath( dirname(__FILE__).'/../../../..' );
		
		//Auth OK
		if ($result == "true"){
		
			//////////////////////TODO ///////////////////////
			//model order
			if (!class_exists( 'VirtueMartModelOrders' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\orders.php');
			$modelOrders = new VirtueMartModelOrders;
			
			$order_id = $params->UpdateOrderStatusParams->UpdateOrderStatusParam->order_id;
			$order_status = $params->UpdateOrderStatusParams->UpdateOrderStatusParam->status;
			
			$_REQUEST['order_status'] = $order_status;
			$_REQUEST['notify_customer']= $params->UpdateOrderStatusParams->UpdateOrderStatusParam->notify;
			$_REQUEST['order_comment'] = $params->UpdateOrderStatusParams->UpdateOrderStatusParam->comment;
			
			//$modelOrders->updateOrderStatus($order_id,$order_status); //statu change but no stok
			$res= $modelOrders->updateStatus();//better workflow but not working
			
			
			return new SoapFault("UpdateOrderStatusFault", $res);
			
			
			
			
			
			$cpnIdsStr = "";
			$allOk=true;
			
			if (is_array($params->UpdateOrderStatusParams->UpdateOrderStatusParam)){
				
				$count = count($params->UpdateOrderStatusParams->UpdateOrderStatusParam);
				for ($i = 0; $i < $count; $i++) {
				
					$_SESSION['ps_vendor_id'] = "1";
					$ps_order= new ps_order;
					
					$d['order_id'] = $params->UpdateOrderStatusParams->UpdateOrderStatusParam[$i]->order_id;
					//$d['current_order_status'] = "P";
					$d['order_status'] =  $params->UpdateOrderStatusParams->UpdateOrderStatusParam[$i]->status;
					$d['notify_customer'] = $params->UpdateOrderStatusParams->UpdateOrderStatusParam[$i]->notify;
					$_REQUEST['notify_customer'] = $params->UpdateOrderStatusParams->UpdateOrderStatusParam[$i]->notify;
					if (!empty($params->UpdateOrderStatusParams->UpdateOrderStatusParam[$i]->comment)){
						$d['include_comment'] = "Y";
						$_REQUEST['include_comment'] = "Y";
						
						$d['order_comment'] = $params->UpdateOrderStatusParams->UpdateOrderStatusParam[$i]->comment;
						$_REQUEST['order_comment'] = $params->UpdateOrderStatusParams->UpdateOrderStatusParam[$i]->comment;
						//$ps_order->notify_customer($d);
					} else {
						$d['include_comment'] = "N";
						$_REQUEST['include_comment'] = "N";
					}
					
					// change status of order
					$result = $ps_order->order_status_update($d);
				
					if ($result){
						$cpnIdsStr .= $params->UpdateOrderStatusParams->UpdateOrderStatusParam[$i]->order_id." ";
					}else{
						$allOk=false;
					}
				}
			
			} else {
				$order_id = $params->UpdateOrderStatusParams->UpdateOrderStatusParam->order_id;
				$order_status = $params->UpdateOrderStatusParams->UpdateOrderStatusParam->status;
				
				$_REQUEST['order_status'][] = $order_status;
				$_REQUEST['notify_customer'][] = $params->UpdateOrderStatusParams->UpdateOrderStatusParam->notify;
				$_REQUEST['order_comment'][] = $params->UpdateOrderStatusParams->UpdateOrderStatusParam->comment;
				
				//$modelOrders->updateOrderStatus($order_id,$order_status); //statu change but no stok
				$res= $modelOrders->updateStatus();//better workflow but not working
			
				if (!empty( $params->UpdateOrderStatusParams->UpdateOrderStatusParam->comment)){
					
					$d['include_comment'] = "Y";
					$_REQUEST['include_comment'] = "Y";
					
					$d['order_comment'] = $params->UpdateOrderStatusParams->UpdateOrderStatusParam->comment;
					$_REQUEST['order_comment'] = $params->UpdateOrderStatusParams->UpdateOrderStatusParam->comment;
					//$ps_order->notify_customer($d);
				} else {
					$d['include_comment'] = "N";
					$_REQUEST['include_comment'] = "N";
				}
				
				// change status of order
				$result = $ps_order->order_status_update($d);
				
				if ($result){
					$commonReturn = new CommonReturn(OK,"Order Status updated sucessfully : \n".$d['order_id'],$d['order_id']);
					return $commonReturn;
					
				}else {
					return new SoapFault("UpdateOrderStatusFault", "Cannot update OrderStatus  :".$d['order_id']);
				}
			}

			if ($allOk){
				$commonReturn = new CommonReturn(OK,"All Order Status updated sucessfully : ".$cpnIdsStr,$cpnIdsStr);
				return $commonReturn;
				return "All Order Status updated sucessfully : ".$cpnIdsStr;
			} else {
				return new SoapFault("DeleteStatesFault", "Not all Order Status updated, only orderid  : ".$cpnIdsStr);
			}	
			
		
		
		
		
			
			
			$strResult ="OrderId  ".$params->order_id." status updated to :".$params->status;
			
			return $strResult;
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}

	/**
    * This function DeleteOrder
	* (expose as WS)
    * @param string params (user, pass, orderid)
    * @return result
    */
	function DeleteOrder($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_deleteorder']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			
			//Remove is ok but return false (bug in vm)
			
			//model order
			if (!class_exists( 'VirtueMartModelOrders' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\orders.php');
			$modelOrders = new VirtueMartModelOrders;
			
			$ids[] = $params->order_id;
			$ids['virtuemart_order_id '] = $params->order_id;
			$res = $modelOrders->remove($ids);
			
			if ($res != false){
				$commonReturn = new CommonReturn(OK,getWSMsg("Order ". $params->order_id,DEL)."",$params->order_id);
				return $commonReturn;
			} else {
				return new SoapFault("DeleteOrderFault",getWSMsg("Order ". $params->order_id,DELKO)."",$params->order_id,$params->order_id);
			}
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	 /**
    * This function get user_info_id (copy ps_chekout.php)
	* (not expose as WS)
    * @param 
    * @return array of Status
    */
	function getUserInfoId($user_id){
		
		///////////////////TODO///////////////////
		$db = JFactory::getDBO();	
		$query  = "SELECT user_info_id from `#__vm_user_info` WHERE ";
		$query .= "user_id='" . $user_id . "' ";
		$query .= "AND address_type='BT'";
		$db->setQuery($query);
		
		$rows = $db->loadObjectList();
		foreach ($rows as $row){
			$user_info_id=$row->user_info_id;
		}
		return $user_info_id;
		/*$db = new ps_DB();

		/* Select all the ship to information for this user id and
		* order by modification date; most recently changed to oldest
		*/
		/*$q  = "SELECT user_info_id from `#__{vm}_user_info` WHERE ";
		$q .= "user_id='" . $user_id . "' ";
		$q .= "AND address_type='BT'";
		$db->query($q);
		$db->next_record();
		return $db->f("user_info_id");*/
	}
	
	 /**
    * This function create an order
	* (expose as WS)
    * @param 
    * @return array of Status
    */
	function CreateOrder($params) {
		
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_createorder']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			//TODO /correct user/currency/coupon
			//load user order
			$user_id = $params->user_id;
			$usr = JFactory::getUser($user_id);
			
			//import plugin payment
			if (!class_exists( 'vmpaymentplugin.php' )) require (JPATH_VM_SITE.DS.'helpers\vmpaymentplugin.php');
			JPluginHelper::importPlugin('vmpayment');
		
			if (!class_exists( 'calculationHelper' )) require (JPATH_VM_ADMINISTRATOR.DS.'helpers\calculationh.php');
			
			//model order
			if (!class_exists( 'VirtueMartModelOrders' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\orders.php');
			$modelOrders = new VirtueMartModelOrders;
			
			//cart
			if (!class_exists( 'VirtueMartCart' )) require (JPATH_VM_SITE.DS.'helpers\cart.php');
			$virtueMartCart = VirtueMartCart::getCart();
			
			$virtueMartCart->setPaymentMethod($params->payment_method_id);
			$virtueMartCart->setCouponCode($params->coupon_code);
			$virtueMartCart->setShipper($params->shipping_method_id);
			$virtueMartCart->customer_comment = $params->customer_note;
			$virtueMartCart->virtuemart_currency_id  = $params->virtuemart_currency_id;
			
			
			if (is_array($params->products->product)){
				$count = count($params->products->product);
				$_SESSION['cart']["idx"]=$count;
				for ($i = 0; $i < $count; $i++) {				
					$prod_id = $params->products->product[$i]->product_id;
					$virtuemart_product_ids->virtuemart_product_id = $prod_id;
					$_REQUEST['quantity']['virtuemart_product_id'] = $params->products->product[$i]->quantity;  
					//add products to cart
					$rescart = $virtueMartCart->add($virtuemart_product_ids);
				}
			}else{
				$prod_id = $params->products->product->product_id;
				$virtuemart_product_ids->virtuemart_product_id = $prod_id;
				$_REQUEST['quantity']['virtuemart_product_id'] = $params->products->product->quantity;  
				//add products to cart
				$rescart = $virtueMartCart->add($virtuemart_product_ids);
			}

			//create order
			$ret = $modelOrders->createOrderFromCart($virtueMartCart);
			
			if ($ret != false){
				$returnCode = "OK";
				$message = "Order sucessfully created for user_id : ".$params->user_id;
				$outputParam=$ret;
				$commonReturn = new CommonReturn($returnCode,$message,$outputParam);
				return $commonReturn;
			} else{
				return new SoapFault("CreateOrderFault", "Cannot create order for user_id : ".$params->user_id);
			}
			
			return new SoapFault("CreateOrderFault",$ret,$res);
		
					
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function get all coupon code
	* (expose as WS)
    * @param string
    * @return Coupon details
    */
	function GetAllCouponCode($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_getcoupon']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			$db = JFactory::getDBO();	
			
			$query  = "SELECT * FROM #__virtuemart_coupons WHERE 1 ";
			
			if (!empty($params->coupon->coupon_id)){
				$query  .= "AND virtuemart_coupon_id = '".$params->coupon->coupon_id."' ";
			}
			if (!empty($params->coupon->coupon_code)){
				$query  .= "AND coupon_code = '".$params->coupon->coupon_code."' ";
			}
			if (!empty($params->coupon->percent_or_total)){
				$query  .= "AND percent_or_total = '".$params->coupon->percent_or_total."' ";
			}
			if (!empty($params->coupon->coupon_type)){
				$query  .= "AND coupon_type = '".$params->coupon->coupon_type."' ";
			}
			if (!empty($params->coupon->coupon_value)){
				$query  .= "AND coupon_value = '".$params->coupon->coupon_value."' ";
			}
			if (!empty($params->coupon->coupon_start_date)){
				$query  .= "AND coupon_start_date > '".$params->coupon->coupon_start_date."' ";
			}
			if (!empty($params->coupon->coupon_expiry_date)){
				$query  .= "AND coupon_expiry_date < '".$params->coupon->coupon_expiry_date."' ";
			}
			if (!empty($params->coupon->coupon_value_valid)){
				$query  .= "AND coupon_value_valid = '$params->coupon->coupon_value_valid' ";
			}
			if (!empty($params->coupon->published)){
				$query  .= "AND published = '$params->coupon->published' ";
			}
			
			
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			
			foreach ($rows as $row){
				$Coupon = new Coupon($row->virtuemart_coupon_id ,
									$row->coupon_code,
									$row->percent_or_total, 
									$row->coupon_type, 
									$row->coupon_value,
									$row->coupon_start_date, 
									$row->coupon_expiry_date, 
									$row->coupon_value_valid, 
									$row->published								
									);
				$arrayCoupon[]=$Coupon;
			}
			return $arrayCoupon;
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
	}
	
	/**
    * This function add coupon code
	* (expose as WS)
    * @param string
    * @return result
    */
	function AddCouponCode($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_addcoupon']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'VirtueMartModelCoupon' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\coupon.php');
			$modelCoupon = new VirtueMartModelCoupon;
			
			$couponCodesStr="";
			$allOk=true;
			
			//chek if there is one or more coupon and add
			if (is_array($params->coupons->coupon)){
				
				$count = count($params->coupons->coupon);

				for ($i = 0; $i < $count; $i++) {
					$_POST['coupon_code']= $params->coupons->coupon[$i]->coupon_code;
					$_POST['coupon_value']= $params->coupons->coupon[$i]->coupon_value;
					$_POST['percent_or_total']= $params->coupons->coupon[$i]->percent_or_total;
					$_POST['coupon_type']= $params->coupons->coupon[$i]->coupon_type;
					
					$_POST['coupon_start_date']= $params->coupons->coupon[$i]->coupon_start_date;
					$_POST['coupon_expiry_date']= $params->coupons->coupon[$i]->coupon_expiry_date;
					$_POST['coupon_value_valid']= $params->coupons->coupon[$i]->coupon_value_valid;
					$_POST['published']= $params->coupons->coupon[$i]->published;
					//add coupon
					$ret = $modelCoupon->store();
					if ($ret != false){
						$couponCodesStr .= $_POST['coupon_code']." ";
					} else {
						$allOk=false;
					}
				}
				
			} else {
				
				$_POST['coupon_code']= $params->coupons->coupon->coupon_code;
				$_POST['coupon_value']= $params->coupons->coupon->coupon_value;
				$_POST['percent_or_total']= $params->coupons->coupon->percent_or_total;
				$_POST['coupon_type']= $params->coupons->coupon->coupon_type;
				
				$_POST['coupon_start_date']= $params->coupons->coupon->coupon_start_date;
				$_POST['coupon_expiry_date']= $params->coupons->coupon->coupon_expiry_date;
				$_POST['coupon_value_valid']= $params->coupons->coupon->coupon_value_valid;
				$_POST['published']= $params->coupons->coupon->published;
				//add coupon
				$ret = $modelCoupon->store();
				
				//there is bug : $ret allways false even coupon is stored
				if ($ret == false){
					return new SoapFault("AddCouponFault", getWSMsg("Coupon",ADDKO)." : ".$_POST['coupon_code'],$modelCoupon->getError());
					
				} else {
					return new CommonReturn(OK,getWSMsg("Coupon",ADD)." : ".$_POST['coupon_code']." id : ".$ret['virtuemart_coupon_id'],$ret['virtuemart_coupon_id']);
					
				}
			}
			
			if ($allOk){
				return new CommonReturn(OK,getWSMsg("Coupon",ALLOK)." :",$couponCodesStr);
			} else {
				return new SoapFault("AddCouponsFault", getWSMsg("Coupon",NOTALLOK)." : ".$couponCodesStr);
			}
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function delete coupon code
	* (expose as WS)
    * @param string
    * @return Coupon details
    */
	function DeleteCouponCode($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_delcoupon']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			/*$db = JFactory::getDBO();	
			$query  = "DELETE FROM #__virtuemart_coupons WHERE virtuemart_coupon_id IN ( ";*/
		
			if (!class_exists( 'VirtueMartModelCoupon' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\coupon.php');
			$modelCoupon = new VirtueMartModelCoupon;
			
			$cpnIdsStr="";
			
			if (is_array($params->ids->id)){
			
				$count = count($params->ids->id);
				for ($i = 0; $i < $count; $i++) {
					//$d['coupon_id'][$i] = $params->ids->id[$i];
					/*if ($i == $count -1){
						$cpnIdsStr .= $params->ids->id[$i]."  ";
					}else {
						$cpnIdsStr .= $params->ids->id[$i]." , ";
					}*/
					$cpnIdsStr .= $params->ids->id[$i]."  ";
					$data['virtuemart_coupon_id'] = $params->ids->id[$i];
					$ret = $modelCoupon->remove($data);
					
				}
			
			} else {
				//$d['coupon_id'] = $params->ids->id;
				$cpnIdsStr .= $params->ids->id." ";
				$data['virtuemart_coupon_id'] = $params->ids->id;
				$ret = $modelCoupon->remove($data);
			}
			/*$query .= $cpnIdsStr." )";
			$db->setQuery($query);
			$rows = $db->loadAssocList();
			
			$errMsg=  $db->getErrorMsg();*/
			
			
			
			if ($ret){
				$commonReturn = new CommonReturn(OK,getWSMsg("Coupon",DEL)." : ".$cpnIdsStr,$cpnIdsStr);
				return $commonReturn;
			}else {
				return new SoapFault("JoomlaServerAuthFault", getWSMsg("Coupon",DELKO)." : ".$cpnIdsStr);
			}
	
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}

	/**
    * This function get shipping rate
	* (expose as WS)
    * @param string
    * @return shipping rate
    */
	function GetAllShippingRate($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->login, $params->password);
		if ($conf['auth_order_getshiprate']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
			
			return new SoapFault("GetAllShippingRateFault", "Not available in VM2");
			$db = JFactory::getDBO();	
			
			//NOT IN VM2 (check shipper plugin with table name __virtuemart_order_shipper_)
			//$query  = "SELECT * FROM #__virtuemart_shippingcarriers WHERE 1";
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row){
				$ShippingRate = new ShippingRate($row->shipping_rate_id,
												$row->shipping_rate_name,
												$row->shipping_rate_carrier_id,
												$row->shipping_rate_country,
												$row->shipping_rate_zip_start, 
												$row->shipping_rate_zip_end,
												$row->shipping_rate_weight_start,
												$row->shipping_rate_weight_end,
												$row->shipping_rate_value,
												$row->shipping_rate_package_fee,
												$row->shipping_rate_currency_id,
												$row->shipping_rate_vat_id, $row->shipping_rate_list_order);
				$arrayShippingRate[]=$ShippingRate;
			}
			return $arrayShippingRate;
			///////
			/*$db = new ps_DB;

			$list  = "SELECT * FROM #__{vm}_shipping_rate WHERE 1";
			$db->query($list);
			
			while ($db->next_record()) {
			
				$ShippingRate = new ShippingRate($db->f("shipping_rate_id"),$db->f("shipping_rate_name"),$db->f("shipping_rate_carrier_id"), $db->f("shipping_rate_country"),
				$db->f("shipping_rate_zip_start"), $db->f("shipping_rate_zip_end"), $db->f("shipping_rate_weight_start"), $db->f("shipping_rate_weight_end"), $db->f("shipping_rate_value"),
				$db->f("shipping_rate_package_fee"), $db->f("shipping_rate_currency_id"), $db->f("shipping_rate_vat_id"), $db->f("shipping_rate_list_order"));
				$arrayShippingRate[]=$ShippingRate;
			
			}
			return $arrayShippingRate;*/
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
	}
	
	/**
    * This function add shipping rate
	* (expose as WS)
    * @param string
    * @return shipping rate
    */
	function AddShippingRate($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_addshiprate']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
			return new SoapFault("GetAllShippingRateFault", "Not available in VM2");
			$vm_ps_shipping = new vm_ps_shipping;
			$allOk=true;
			$ratesStr;
			//chek if there is one or more shippnig carriers and add
			if (is_array($params->shipping_rates->shippingrate)){
			
				$count = count($params->shipping_rates->shippingrate);

				for ($i = 0; $i < $count; $i++) {
					//$d['shipping_rate_id']= $params->shipping_rates->shipping_rate[$i]->shipping_rate_id;
					$d['shipping_rate_name']= $params->shipping_rates->shippingrate[$i]->shipping_rate_name;
					$d['shipping_rate_carrier_id']= $params->shipping_rates->shippingrate[$i]->shipping_rate_carrier_id;
					$d['shipping_rate_country']= explode(";",$params->shipping_rates->shippingrate[$i]->shipping_rate_country);
					$d['shipping_rate_zip_start']= $params->shipping_rates->shippingrate[$i]->shipping_rate_zip_start;
					$d['shipping_rate_zip_end']= $params->shipping_rates->shippingrate[$i]->shipping_rate_zip_end;
					$d['shipping_rate_weight_start']= $params->shipping_rates->shippingrate[$i]->shipping_rate_weight_start;
					$d['shipping_rate_weight_end']= $params->shipping_rates->shippingrate[$i]->shipping_rate_weight_end;
					$d['shipping_rate_value']= $params->shipping_rates->shippingrate[$i]->shipping_rate_value;
					$d['shipping_rate_package_fee']= $params->shipping_rates->shippingrate[$i]->shipping_rate_package_fee;
					$d['shipping_rate_currency_id']= $params->shipping_rates->shippingrate[$i]->shipping_rate_currency_id;
					$d['shipping_rate_vat_id']= $params->shipping_rates->shippingrate[$i]->shipping_rate_vat_id;
					$d['shipping_rate_list_order']= $params->shipping_rates->shippingrate[$i]->shipping_rate_list_order;
					/*
					$db = new ps_DB;
					$db->buildQuery("INSERT","#__{vm}_shipping_rate",$d);
					$result = $db->query();
					$errMsg=  $db->getErrorMsg();	*/
					$result = $vm_ps_shipping->rate_add($d);

					if ($result){
						$ratesStr .= $params->shipping_rates->shippingrate[$i]->shipping_rate_name." ";
					} else {
						$allOk=false;
					}
				} 
			}else {
					//$d['shipping_rate_id']= $params->shipping_rates->shipping_rate->shipping_rate_id;
					$d['shipping_rate_name']= $params->shipping_rates->shippingrate->shipping_rate_name;
					$d['shipping_rate_carrier_id']= $params->shipping_rates->shippingrate->shipping_rate_carrier_id;
					$d['shipping_rate_country']= explode(";",$params->shipping_rates->shippingrate->shipping_rate_country);
					$d['shipping_rate_zip_start']= $params->shipping_rates->shippingrate->shipping_rate_zip_start;
					$d['shipping_rate_zip_end']= $params->shipping_rates->shippingrate->shipping_rate_zip_end;
					$d['shipping_rate_weight_start']= $params->shipping_rates->shippingrate->shipping_rate_weight_start;
					$d['shipping_rate_weight_end']= $params->shipping_rates->shippingrate->shipping_rate_weight_end;
					$d['shipping_rate_value']= $params->shipping_rates->shippingrate->shipping_rate_value;
					$d['shipping_rate_package_fee']= $params->shipping_rates->shippingrate->shipping_rate_package_fee;
					$d['shipping_rate_currency_id']= $params->shipping_rates->shippingrate->shipping_rate_currency_id;
					$d['shipping_rate_vat_id']= $params->shipping_rates->shippingrate->shipping_rate_vat_id;
					$d['shipping_rate_list_order']= $params->shipping_rates->shippingrate->shipping_rate_list_order;
					
					//add
					/*
					$db = new ps_DB;
					$db->buildQuery("INSERT","#__{vm}_shipping_rate",$d);
					$result = $db->query();
					$errMsg=  $db->getErrorMsg();*/
					$result = $vm_ps_shipping->rate_add($d);
				if ($result){
					return "Shipping Rate successfully added : ".$d['shipping_rate_name'];
				} else {
					return new SoapFault("AddShippingRateFault", "Cannot add rate : ".$d['shipping_rate_name']);
				}
			}
			
			if ($allOk){
				return "All Shipping Rates successfully added : ".$ratesStr;
			} else {
				return new SoapFault("AddShippingRatesFault", "Not all ShiipingRates added, only rates code : ".$ratesStr);
			}
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Update ShippingRate
	* (expose as WS)
    * @param string
    * @return shipping rate
    */
	function UpdateShippingRate($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_upshiprate']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
			return new SoapFault("GetAllShippingRateFault", "Not available in VM2");
			$vm_ps_shipping = new vm_ps_shipping;
			$allOk=true;
			$ratesStr;
			//chek if there is one or more shippnig carriers and add
			if (is_array($params->shipping_rates->shippingrate)){
			
				$count = count($params->shipping_rates->shippingrate);

				for ($i = 0; $i < $count; $i++) {
					$_REQUEST['shipping_rate_id'] =  $params->shipping_rates->shippingrate[$i]->shipping_rate_id;
					$d['shipping_rate_id']= $params->shipping_rates->shipping_rate[$i]->shipping_rate_id;
					$d['shipping_rate_name']= $params->shipping_rates->shippingrate[$i]->shipping_rate_name;
					$d['shipping_rate_carrier_id']= $params->shipping_rates->shippingrate[$i]->shipping_rate_carrier_id;
					$d['shipping_rate_country']= explode(";",$params->shipping_rates->shippingrate[$i]->shipping_rate_country);
					$d['shipping_rate_zip_start']= $params->shipping_rates->shippingrate[$i]->shipping_rate_zip_start;
					$d['shipping_rate_zip_end']= $params->shipping_rates->shippingrate[$i]->shipping_rate_zip_end;
					$d['shipping_rate_weight_start']= $params->shipping_rates->shippingrate[$i]->shipping_rate_weight_start;
					$d['shipping_rate_weight_end']= $params->shipping_rates->shippingrate[$i]->shipping_rate_weight_end;
					$d['shipping_rate_value']= $params->shipping_rates->shippingrate[$i]->shipping_rate_value;
					$d['shipping_rate_package_fee']= $params->shipping_rates->shippingrate[$i]->shipping_rate_package_fee;
					$d['shipping_rate_currency_id']= $params->shipping_rates->shippingrate[$i]->shipping_rate_currency_id;
					$d['shipping_rate_vat_id']= $params->shipping_rates->shippingrate[$i]->shipping_rate_vat_id;
					$d['shipping_rate_list_order']= $params->shipping_rates->shippingrate[$i]->shipping_rate_list_order;
					/*
					$db = new ps_DB;
					$db->buildQuery("INSERT","#__{vm}_shipping_rate",$d);
					$result = $db->query();
					$errMsg=  $db->getErrorMsg();*/	
					$result = $vm_ps_shipping->rate_update($d);

					if ($result){
						$ratesStr .= $params->shipping_rates->shippingrate[$i]->shipping_rate_name." ";
					} else {
						$allOk=false;
					}
				} 
			}else {
					$d['shipping_rate_id']= $params->shipping_rates->shippingrate->shipping_rate_id;
					$d['shipping_rate_name']= $params->shipping_rates->shippingrate->shipping_rate_name;
					$d['shipping_rate_carrier_id']= $params->shipping_rates->shippingrate->shipping_rate_carrier_id;
					$d['shipping_rate_country']= explode(";",$params->shipping_rates->shippingrate->shipping_rate_country);
					$d['shipping_rate_zip_start']= $params->shipping_rates->shippingrate->shipping_rate_zip_start;
					$d['shipping_rate_zip_end']= $params->shipping_rates->shippingrate->shipping_rate_zip_end;
					$d['shipping_rate_weight_start']= $params->shipping_rates->shippingrate->shipping_rate_weight_start;
					$d['shipping_rate_weight_end']= $params->shipping_rates->shippingrate->shipping_rate_weight_end;
					$d['shipping_rate_value']= $params->shipping_rates->shippingrate->shipping_rate_value;
					$d['shipping_rate_package_fee']= $params->shipping_rates->shippingrate->shipping_rate_package_fee;
					$d['shipping_rate_currency_id']= $params->shipping_rates->shippingrate->shipping_rate_currency_id;
					$d['shipping_rate_vat_id']= $params->shipping_rates->shippingrate->shipping_rate_vat_id;
					$d['shipping_rate_list_order']= $params->shipping_rates->shippingrate->shipping_rate_list_order;
					
					//add
					/*$db = new ps_DB;
					$db->buildQuery("INSERT","#__{vm}_shipping_rate",$d);
					$result = $db->query();
					$errMsg=  $db->getErrorMsg();*/
					$result = $vm_ps_shipping->rate_update($d);
				if ($result){
					return "Shipping Rate successfully updated : ".$d['shipping_rate_id'];
				} else {
					return new SoapFault("UpdateShippingRateFault", "Cannot update rate : ".$d['shipping_rate_id']);
				}
			}
			
			if ($allOk){
				return "All Shipping Rates successfully updated : ".$ratesStr;
			} else {
				return new SoapFault("UpdateShippingRateFault", "Not all ShiipingRates updated , only rates code : ".$ratesStr);
			}
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Delete Shipping Rate
	* (expose as WS)
    * @param string
    * @return Coupon details
    */
	function DeleteShippingRate($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_delshiprate']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
			return new SoapFault("GetAllShippingRateFault", "Not available in VM2");
			$vm_ps_shipping = new vm_ps_shipping;
			//$db = new ps_DB;
			$allOk=true;
			$cpnIdsStr="";
			
			if (is_array($params->ids->id)){
			
				$count = count($params->ids->id);
				for ($i = 0; $i < $count; $i++) {
					$d['shipping_rate_id'][$i] = $params->ids->id[$i];
					
					/*$q = 'DELETE FROM #__{vm}_shipping_rate WHERE shipping_rate_id  = '.(int)$d['rate_id'][$i];
					$result = $db->query($q);*/
					$result = $vm_ps_shipping->rate_delete($d);
					if ($result){
						$cpnIdsStr .= $params->ids->id[$i]." ";
					}else{
						$allOk=false;
					}
				}
			} else {
				$d['shipping_rate_id'] = $params->ids->id;
				$cpnIdsStr .= $params->ids->id." ";
				/*
				$q = 'DELETE FROM #__{vm}_shipping_rate WHERE shipping_rate_id  = '.(int)$d['rate_id'];
				$result = $db->query($q);*/
				$result = $vm_ps_shipping->rate_delete($d);
				if ($result){
					return "Shipping rate successfully deleted : ".$cpnIdsStr;			
				} else{
					return new SoapFault("DeleteShippingRateFault", "Cannot delete shipping rate : ".$d['rate_id']);
				}
			}
			if ($allOk){
				return "Shipping Rates successfully deleted : ".$cpnIdsStr;
			} else {
				return new SoapFault("DeleteShippingRatesFault", "Not all ShiipingRates deleted, only rates id : ".$cpnIdsStr);
			}

		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function get GetAllShippingCarrier
	* (expose as WS)
    * @param string
    * @return shipping carrier
    */
	function GetAllShippingCarrier($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->login, $params->password);
		if ($conf['auth_order_getshipcarrier']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			$db = JFactory::getDBO();	
			$query  = "SELECT * FROM #__virtuemart_shippingcarriers WHERE 1";
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row){
				
				$ShippingCarrier = new ShippingCarrier($row->virtuemart_shippingcarrier_id,
														$row->virtuemart_vendor_id,
														$row->shipping_carrier_jplugin_id,
														$row->shipping_carrier_name,
														$row->shipping_carrier_desc,
														$row->shipping_carrier_element,
														$row->shipping_carrier_params,
														$row->shipping_carrier_value,
														$row->shipping_carrier_package_fee,
														$row->shipping_carrier_vat_id,
														$row->ordering,
														$row->shared,
														$row->published);
				$arrayShippingCarrier[]=$ShippingCarrier;
			}
			return $arrayShippingCarrier;
			
			////////////
			/*$db = new ps_DB;

			$list  = "SELECT * FROM #__{vm}_shipping_carrier WHERE 1";
			$db->query($list);
			
			while ($db->next_record()) {
			
				$ShippingCarrier = new ShippingCarrier($db->f("shipping_carrier_id"),$db->f("shipping_carrier_name"),$db->f("shipping_carrier_list_order"));
				$arrayShippingCarrier[]=$ShippingCarrier;
			
			}
			return $arrayShippingCarrier;*/
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
	}
	
		/**
    * This function get AddShippingCarrier
	* (expose as WS)
    * @param string
    * @return result
    */
	function AddShippingCarrier($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_addshipcarrier']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			
			$allOk=true;
			$carriersCodesStr;
			
			if (!class_exists( 'VirtueMartModelShippingCarrier' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\shippingcarrier.php');
			$modelShippingCarrier = new VirtueMartModelShippingCarrier;
			
			//todo make sur all is ok // dont work now for array of carrier
			$i = 0;
			
			debugInfile($params->shipping_carriers);
			foreach ($params->shipping_carriers as $shipping_carrier){
				$i++;
				//return new SoapFault("AddCarrierFault",count($params->shipping_carriers));
				//$carriersCodesStr .= $shipping_carrier[0]->shipping_carrier_name." ";
				
				$data['virtuemart_vendor_id'] 			= $shipping_carrier->virtuemart_vendor_id;
				$data['shipping_carrier_jplugin_id'] 	= $shipping_carrier->shipping_carrier_jplugin_id;
				$data['shipping_carrier_name'] 			= $shipping_carrier->shipping_carrier_name;
				$data['shipping_carrier_desc'] 			= $shipping_carrier->shipping_carrier_desc;
				$data['shipping_carrier_element'] 		= $shipping_carrier->shipping_carrier_element;
				$data['shipping_carrier_params'] 		= $shipping_carrier->shipping_carrier_params;
				$data['shipping_carrier_value'] 		= $shipping_carrier->shipping_carrier_value;
				$data['shipping_carrier_package_fee'] 	= $shipping_carrier->shipping_carrier_package_fee;
				$data['shipping_carrier_vat_id'] 		= $shipping_carrier->shipping_carrier_vat_id;
				$data['ordering'] 						= $shipping_carrier->ordering;
				$data['shared'] 						= $shipping_carrier->shared;
				$data['published'] 						= $shipping_carrier->published;
			
			//	$carr_id = $modelShippingCarrier->store($data);
			}
			return new SoapFault("AddCarrierFault",$i);
			
			
			if ($carr_id == false ){
				return new SoapFault("AddCarrierFault", getWSMsg("ShippingCarrier",ADDKO)." : ".$modelShippingCarrier->getError().'\n');
			}else{
				$commonReturn = new CommonReturn(OK,getWSMsg("ShippingCarrier",ADD)." : ".$carriersCodesStr,$carr_id);
				return $commonReturn;
			}
			
			//chek if there is one or more shippnig carriers and add
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function  Update ShippingCarrier
	* (expose as WS)
    * @param string
    * @return result
    */
	function UpdateShippingCarrier($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_upshipcarrier']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			
			$allOk=true;
			$carriersCodesStr;
			if (!class_exists( 'VirtueMartModelShippingCarrier' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\shippingcarrier.php');
			$modelShippingCarrier = new VirtueMartModelShippingCarrier;
			
			//todo make sur all is ok // dont work now for array of carrier
			foreach ($params->shipping_carriers as $shipping_carrier){
				
				$carriersCodesStr .= $shipping_carrier->shipping_carrier_name." ";
				
				$data['virtuemart_shippingcarrier_id'] 	= $shipping_carrier->shipping_carrier_id;
				$data['virtuemart_vendor_id'] 			= $shipping_carrier->virtuemart_vendor_id;
				$data['shipping_carrier_jplugin_id'] 	= $shipping_carrier->shipping_carrier_jplugin_id;
				$data['shipping_carrier_name'] 			= $shipping_carrier->shipping_carrier_name;
				$data['shipping_carrier_desc'] 			= $shipping_carrier->shipping_carrier_desc;
				$data['shipping_carrier_element'] 		= $shipping_carrier->shipping_carrier_element;
				$data['shipping_carrier_params'] 		= $shipping_carrier->shipping_carrier_params;
				$data['shipping_carrier_value'] 		= $shipping_carrier->shipping_carrier_value;
				$data['shipping_carrier_package_fee'] 	= $shipping_carrier->shipping_carrier_package_fee;
				$data['shipping_carrier_vat_id'] 		= $shipping_carrier->shipping_carrier_vat_id;
				$data['ordering'] 						= $shipping_carrier->ordering;
				$data['shared'] 						= $shipping_carrier->shared;
				$data['published'] 						= $shipping_carrier->published;
			
				$carr_id = $modelShippingCarrier->store($data);
			}
			
			
			
			if ($carr_id == false ){
				return new SoapFault("UpdateShippingCarrierFault", "Not all carriers updated. Error : ".$modelShippingCarrier->getError().'\n');
			}else{
				$commonReturn = new CommonReturn(OK,"All Carriers successfully updated : ".$carriersCodesStr,$carriersCodesStr);
				return $commonReturn;
			}
			
		
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function DeleteShippingCarrier
	* (expose as WS)
    * @param string
    * @return result
    */
	function DeleteShippingCarrier($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_delshipcarrier']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			
			$db = new ps_DB;
			$allOk=true;
			$cpnIdsStr="";
			
			if (is_array($params->ids->id)){
			
				$count = count($params->ids->id);
				for ($i = 0; $i < $count; $i++) {
					$d['id'][$i] = $params->ids->id[$i];
					
					$q = 'DELETE FROM #__{vm}_shipping_carrier WHERE shipping_carrier_id  = '.(int)$d['id'][$i];
					$result = $db->query($q);
					if ($result){
						$cpnIdsStr .= $params->ids->id[$i]." ";
					}else{
						$allOk=false;
					}
				}
			} else {
				$d['id'] = $params->ids->id;
				$cpnIdsStr .= $params->ids->id." ";
				$q = 'DELETE FROM #__{vm}_shipping_carrier WHERE shipping_carrier_id  = '.(int)$d['id'];
				$result = $db->query($q);
				if ($result){
					return "ShippingCarrier successfully deleted : ".$cpnIdsStr;			
				} else{
					return new SoapFault("DeleteShippingCarrierFault", "Cannot delete shipping carrier : ".$d['id']);
				}
			}
			if ($allOk){
				return "All Shipping Carrier successfully deleted : ".$cpnIdsStr;
			} else {
				return new SoapFault("DeleteShippingCarrierFault", "Not all Shipping Carriers deleted, only carrier id : ".$cpnIdsStr);
			}

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Get All Payment Method
	* (expose as WS)
    * @param string
    * @return shipping rate
    */
	function GetAllPaymentMethod($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_getpayment']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		

			if ($params->payment_enabled == "Y" || $params->payment_enabled == "N"){
				// payment_enabled NOT IN VM2
				//$query  = "SELECT * FROM #__vvirtuemart_paymentmethods WHERE payment_enabled ='".$params->payment_enabled."'";
				$query  = "SELECT * FROM #__virtuemart_paymentmethods WHERE published = '$params->payment_enabled' ";
			} else {
				$query  = "SELECT * FROM #__virtuemart_paymentmethods WHERE 1";
			}
			if (!empty($params->payment_method_id)){
				$query  .= "AND payment_method_id = '$params->payment_method_id' ";
			}
			
			$db = JFactory::getDBO();	
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row){
				$PaymentMethod = new PaymentMethod($row->virtuemart_paymentmethod_id ,
												$row->virtuemart_vendor_id ,
												$row->payment_jplugin_id ,
												$row->payment_name,
												$row->payment_element 	,
												$row->discount ,
												$row->discount_is_percentage,
												$row->discount_max_amount ,
												$row->discount_min_amount ,
												$row->payment_params,
												$row->shared ,
												$row->ordering ,
												$row->published
													);
				$arrayPaymentMethod[]=$PaymentMethod;
			}
			return $arrayPaymentMethod;
			
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	
	/**
    * This function Get All Payment Method
	* (expose as WS)
    * @param string
    * @return shipping rate
    */
	function GetOrderPaymentInfo($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_getpayment']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			$order_id = $params->order_id;
			
			$db = JFactory::getDBO();	
			$query  = "SELECT payment_method_id FROM #__virtuemart_orders WHERE 1 ";
			
			if (!empty($params->order_id)){
				$query  .= "AND virtuemart_order_id = '$order_id' ";
			}
			if (!empty($params->order_number)){
				$query  .= "AND order_number = '$params->order_number' ";
			}
			
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row){
				$params->payment_method_id = $row->payment_method_id;
			}
			$pm = GetAllPaymentMethod($params);
			return $pm[0];
			
			/*$list  = "SELECT * FROM #__{vm}_payment_method pm join #__{vm}_order_payment op on pm.payment_method_id= op.payment_method_id  ";
			$list  .= "WHERE order_id = '$order_id' ";
			$db = new ps_DB;
			
			$db->query($list);*/
			
			/*while ($db->next_record()) {
			
				$PaymentMethod = new PaymentMethod($db->f("payment_method_id"),$db->f("vendor_id"),$db->f("payment_method_name"), $db->f("payment_class"),
				$db->f("shopper_group_id"), $db->f("payment_method_discount"), $db->f("payment_method_discount_is_percent"), $db->f("payment_method_discount_max_amount"), $db->f("payment_method_discount_min_amount"),
				$db->f("list_order"), $db->f("payment_method_code"), $db->f("enable_processor"), $db->f("is_creditcard"), $db->f("payment_enabled"), $db->f("accepted_creditcards"), $db->f("payment_extrainfo"));
				
			
			}
			return $PaymentMethod;*/
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Add PaymentMethod
	* (expose as WS)
    * @param string
    * @return result
    */
	function AddPaymentMethod($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_addpayment']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'VirtueMartModelPaymentmethod' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\paymentmethod.php');
			$modelPaymentmethod = new VirtueMartModelPaymentmethod;
			 
			$data['virtuemart_vendor_id'] = isset($params->payment_method->vendor_id) ? $params->payment_method->vendor_id : 1;
			$data['payment_jplugin_id'] = $params->payment_method->payment_jplugin_id;
			$data['payment_name'] = $params->payment_method->payment_name;
			$data['payment_element'] = $params->payment_method->payment_element;
			$data['discount'] = $params->payment_method->discount;
			$data['discount_is_percentage'] = $params->payment_method->discount_is_percentage;
			$data['discount_max_amount'] = $params->payment_method->discount_max_amount;
			$data['discount_min_amount'] = $params->payment_method->discount_min_amount;
			$data['payment_params'] = $params->payment_method->payment_params;
			$data['shared'] = $params->payment_method->shared;
			$data['ordering'] = $params->payment_method->ordering;
			$data['published'] = $params->payment_method->published;
			
			$paym_id = $modelPaymentmethod->store($data);
			
			if ($paym_id == false ){
				return new SoapFault("AddPaymentMethodFault",  getWSMsg("PaymentMethod",ADDKO)." : ".$modelPaymentmethod->getError().'\n');
			}else{
				$commonReturn = new CommonReturn(OK, getWSMsg("PaymentMethod",ADD).", ID : ".$paym_id,$paym_id);
				return $commonReturn;
			}

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Add UpdatePaymentMethod
	* (expose as WS)
    * @param string
    * @return result
    */
	function UpdatePaymentMethod($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_updatepayment']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'VirtueMartModelPaymentmethod' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\paymentmethod.php');
			$modelPaymentmethod = new VirtueMartModelPaymentmethod;
			 
			$data['virtuemart_paymentmethod_id'] = $params->payment_method->payment_method_id;
			$data['virtuemart_vendor_id'] = isset($params->payment_method->vendor_id) ? $params->payment_method->vendor_id : 1;
			$data['payment_jplugin_id'] = $params->payment_method->payment_jplugin_id;
			$data['payment_name'] = $params->payment_method->payment_name;
			$data['payment_element'] = $params->payment_method->payment_element;
			$data['discount'] = $params->payment_method->discount;
			$data['discount_is_percentage'] = $params->payment_method->discount_is_percentage;
			$data['discount_max_amount'] = $params->payment_method->discount_max_amount;
			$data['discount_min_amount'] = $params->payment_method->discount_min_amount;
			$data['payment_params'] = $params->payment_method->payment_params;
			$data['shared'] = $params->payment_method->shared;
			$data['ordering'] = $params->payment_method->ordering;
			$data['published'] = $params->payment_method->published;
			
			$paym_id = $modelPaymentmethod->store($data);
			
			if ($paym_id == false ){
				return new SoapFault("UpdatePaymentMethodFault", getWSMsg("PaymentMethod",UPKO)." : ".$modelPaymentmethod->getError().'\n');
			}else{
				$commonReturn = new CommonReturn(OK, getWSMsg("PaymentMethod",UP).", ID : ".$paym_id,$paym_id);
				return $commonReturn;
			}

		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Add DeletePaymentMethod
	* (expose as WS)
    * @param string
    * @return result
    */
	function DeletePaymentMethod($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_delapyment']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'TablePaymentmethods' )) require (JPATH_VM_ADMINISTRATOR.DS.'tables\paymentmethods.php');
			$db = JFactory::getDBO();
			$tablePaymentmethods = new TablePaymentmethods($db);
			
			//TODO make sure all paymethod deleted
			$cpnIdsStr="";
			
			if (is_array($params->ids->id)){
			
				$count = count($params->ids->id);
				for ($i = 0; $i < $count; $i++) {
					
					$cpnIdsStr .= $params->ids->id[$i]." ";
					$tablePaymentmethods->virtuemart_paymentmethod_id = $params->ids->id[$i];
					$res = $tablePaymentmethods->delete();
				}
			
			} else {
				
				$cpnIdsStr .= $params->ids->id." ";
				$tablePaymentmethods->virtuemart_paymentmethod_id = $params->ids->id;
				$res = $tablePaymentmethods->delete();
				
			}
			
			if ($res != false){
				$commonReturn = new CommonReturn(OK,"Payment Method successfully deleted, ID : ".$cpnIdsStr,$cpnIdsStr);
				return $commonReturn;
			}else {
				return new SoapFault("DeletePaymentMethodFault", "Cannot delete paymentMethod: ".$cpnIdsStr);
			}

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Get Order between date
	* (expose as WS)
    * @param string 
    * @return array of orders
    */
	function GetOrderFromDate($params) {
	
		include('../vm_soa_conf.php');
		/* Authenticate*/
		
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_getorderfromdate']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			
			return getOrderGeneric($params);
			/*
			//MARCHE PAS A DEBUG
			global $mosConfig_offset;
			// format : 2010-01-30
			$date_start_Y = substr($params->date_start, 0, 4);
			$date_start_M = substr($params->date_start, 5, 2);
			$date_start_D = substr($params->date_start, 8, 2);
			$date_start = gmmktime(0, 0, 0, (int)$date_start_M, (int)$date_start_D, (int)$date_start_Y);
			 
			$date_end_Y = substr($params->date_end, 0, 4);
			$date_end_M = substr($params->date_end, 5, 2);
			$date_end_D = substr($params->date_end, 8, 2);
			$date_end = gmmktime(23, 59, 59, (int)$date_end_M, (int)$date_end_D,(int)$date_end_Y);
			
			$db = JFactory::getDBO();	
			$query  = "SELECT * FROM #__vm_orders WHERE ";
			if (!empty($params->order_status)){
				$query .= "order_status = '$params->order_status' AND ";
				$query .= "cdate BETWEEN '$date_start' AND '$date_end' ";
				
			}else {
				$query .= "cdate BETWEEN '$date_start' AND '$date_end' ";
			
			}
			$query .= " ORDER BY cdate ASC"; 
			
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row){
				$Order = new Order($row->order_id,$row->user_id, $row->vendor_id, $row->order_number, $row->user_info_id, $row->order_total, $row->order_subtotal,
				$row->order_tax, $row->order_tax_details, $row->order_shipping, $row->order_shipping_tax, $row->coupon_discount, $row->coupon_code, $row->order_discount, $row->order_currency,
				$row->order_status, $row->cdate, $row->mdate , $row->ship_method_id, $row->customer_note, $row->ip_address);
				$orderArray[]=$Order;
			}
			return $orderArray;*/
			
			
			
			////////////////////////////////////////
			/*$db = new ps_DB;
			
			// format : 2010-01-30
			 $date_start_Y = substr($params->date_start, 0, 4);
			 $date_start_M = substr($params->date_start, 5, 2);
			 $date_start_D = substr($params->date_start, 8, 2);
			 $date_start = gmmktime(0, 0, 0, (int)$date_start_M, (int)$date_start_D, (int)$date_start_Y);
			 
			 $date_end_Y = substr($params->date_end, 0, 4);
			 $date_end_M = substr($params->date_end, 5, 2);
			 $date_end_D = substr($params->date_end, 8, 2);
			 $date_end = gmmktime(23, 59, 59, (int)$date_end_M, (int)$date_end_D,(int)$date_end_Y);

			$list  = "SELECT * FROM #__{vm}_orders WHERE ";
			if (!empty($params->order_status)){
				$list .= "order_status = '$params->order_status' AND ";
				$list .= "cdate BETWEEN '$date_start' AND '$date_end' ";
				
			}else {
				$list .= "cdate BETWEEN '$date_start' AND '$date_end' ";
			
			}
			$list .= $q . " ORDER BY cdate ASC"; 
			
			
			$db = new ps_DB;
			$db->query($list);
			
			while ($db->next_record()) {
			
				$Order = new Order($db->f("order_id"),$db->f("user_id"), $db->f("vendor_id"), $db->f("order_number"), $db->f("user_info_id"), $db->f("order_total"), $db->f("order_subtotal"),
				$db->f("order_tax"), $db->f("order_tax_details"), $db->f("order_shipping"), $db->f("order_shipping_tax"), $db->f("coupon_discount"), $db->f("coupon_code"), $db->f("order_discount"), $db->f("order_currency"),
				$db->f("order_status"), $db->f("cdate"), $db->f("mdate") , $db->f("ship_method_id"), $db->f("customer_note"), $db->f("ip_address"));
				$orderArray[]=$Order;
			
			}
			return $orderArray;*/
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
		/**
    * This function get GetAllCreditCard
	* (expose as WS)
    * @param string
    * @return AllCreditCard
    */
	function GetAllCreditCard($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->login, $params->password);
		if ($conf['auth_order_getcreditcard']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			/*$db = JFactory::getDBO();	
			$query  = "SELECT * FROM #__virtuemart_creditcards WHERE 1";
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();*/
			
			if (!class_exists( 'VirtueMartModelCreditcard' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\creditcard.php');
			$modelCreditcard = new VirtueMartModelCreditcard;
			
			$rows = $modelCreditcard->getCreditCards(0);
			
			foreach ($rows as $row){
				$Creditcard = new Creditcard($row->virtuemart_creditcard_id,
										$row->virtuemart_vendor_id,
										$row->creditcard_name,
										$row->creditcard_code,
										$row->shared,
										$row->published
										);
				$arrayCreditcard[]=$Creditcard;
			}
			return $arrayCreditcard;
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
	}
	
	/**
    * This function  Add CreditCard
	* (expose as WS)
    * @param string
    * @return result
    */
	function AddCreditCard($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_addcreditcard']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			/*if (!class_exists( 'VirtueMartModelCreditcard' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\creditcard.php');
			$modelCreditcard = new VirtueMartModelCreditcard;*/
			
			if (!class_exists( 'VirtueMartModelCreditcard' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\creditcard.php');
			$modelCreditcard = new VirtueMartModelCreditcard;
			
			$data['creditcard_name'] = $params->Creditcard->creditcard_name;
			$data['creditcard_code'] = $params->Creditcard->creditcard_code;
			$data['virtuemart_vendor_id'] = isset($params->Creditcard->vendor_id) ? $params->Creditcard->vendor_id : 1;
			$data['shared'] = isset($params->Creditcard->shared) ? $params->Creditcard->shared : 1;
			$data['published'] = isset($params->Creditcard->published) ? $params->Creditcard->published : 1;
			
			$res = $modelCreditcard->store($data);
			
			//todo check before ?
			//$res = $cardTable->store();
			
			
			if ($res != false){
				$commonReturn = new CommonReturn(OK,getWSMsg("Creditcard",ADD).", ID : ".$res,$res);
				return $commonReturn;
			}else {
				return new SoapFault("AddCreditCardFault", getWSMsg("Creditcard",ADDKO)." : ".$_POST['creditcard_name']);
			}

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function  Update CreditCard
	* (expose as WS)
    * @param string
    * @return result
    */
	function UpdateCreditCard($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_upcreditcard']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'VirtueMartModelCreditcard' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\creditcard.php');
			$modelCreditcard = new VirtueMartModelCreditcard;
			
			$data['virtuemart_creditcard_id'] = $params->Creditcard->creditcard_id;
			$data['creditcard_name'] = $params->Creditcard->creditcard_name;
			$data['creditcard_code'] = $params->Creditcard->creditcard_code;
			$data['virtuemart_vendor_id'] = isset($params->Creditcard->vendor_id) ? $params->Creditcard->vendor_id : 1;
			$data['shared'] = isset($params->Creditcard->shared) ? $params->Creditcard->shared : 1;
			$data['published'] = isset($params->Creditcard->published) ? $params->Creditcard->published : 1;
			
			$res = $modelCreditcard->store($data);
			
			if ($res != false){
				$commonReturn = new CommonReturn(OK,getWSMsg("Creditcard",UP).", ID : ".$res,$res);
				return $commonReturn;
			}else {
				return new SoapFault("AddCreditCardFault", getWSMsg("Creditcard",UPKO)." : ".$params->Creditcard->creditcard_name);
			}
			
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
		/**
    * This function Delete creditCard
	* (expose as WS)
    * @param string
    * @return result
    */
	function DeleteCreditCard($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_delcreditcard']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'VirtueMartModelCreditcard' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\creditcard.php');
			$modelCreditcard = new VirtueMartModelCreditcard;
			
			$data['virtuemart_creditcard_id'] = $params->creditcard_id;
			
			$res = $modelCreditcard->remove($data);
			
			if ($res != false){
				$commonReturn = new CommonReturn(OK,getWSMsg("Creditcard",DEL).", ID : ".$params->creditcard_id,$params->creditcard_id);
				return $commonReturn;
			}else {
				return new SoapFault("DeleteCreditCardFault", getWSMsg("Creditcard",DELKO)." : ".$params->creditcard_id);
			}
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
/**
    * This function Add OrderStatus Code
	* (expose as WS)
    * @param string
    * @return result
    */
	function AddOrderStatusCode($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_addstatus']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'VirtueMartModelOrderstatus' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\orderstatus.php');
			$modelOrderstatus = new VirtueMartModelOrderstatus;
			
			
			$modelOrderstatus = new VirtueMartModelOrderstatus;
			$data['virtuemart_vendor_id'] = isset($params->OrderStatus->vendor_id) ? $params->OrderStatus->vendor_id : 1;
			$data['order_status_code'] = $params->OrderStatus->order_status_code;
			$data['order_status_name'] = $params->OrderStatus->order_status_name;
			$data['order_status_description'] = $params->OrderStatus->order_status_description;
			$data['ordering'] = $params->OrderStatus->ordering;
			$data['published'] = isset($params->OrderStatus->published) ? $params->OrderStatus->published : 1;
			
			$res = $modelOrderstatus->store($data);
			$errMsg = $modelOrderstatus->getError();
			
			if ($res != false){
				$commonReturn = new CommonReturn(OK,getWSMsg("OrderStatusCode " .$data['order_status_name'],ADD),$res);
				return $commonReturn;
			}else {
				return new SoapFault("AddOrderStatusCodeFault", getWSMsg("OrderStatusCode " .$data['order_status_name'],ADDKO).' : '.$errMsg);
			}
		

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Add Update OrderStatus Code
	* (expose as WS)
    * @param string
    * @return result
    */
	function UpdateOrderStatusCode($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_upstatus']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'VirtueMartModelOrderstatus' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\orderstatus.php');
			$modelOrderstatus = new VirtueMartModelOrderstatus;
			
			
			$modelOrderstatus = new VirtueMartModelOrderstatus;
			$data['virtuemart_orderstate_id'] = $params->OrderStatus->order_status_id;
			$data['virtuemart_vendor_id'] = isset($params->OrderStatus->vendor_id) ? $params->OrderStatus->vendor_id : 1;
			$data['order_status_code'] = $params->OrderStatus->order_status_code;
			$data['order_status_name'] = $params->OrderStatus->order_status_name;
			$data['order_status_description'] = $params->OrderStatus->order_status_description;
			$data['ordering'] = $params->OrderStatus->ordering;
			$data['published'] = isset($params->OrderStatus->published) ? $params->OrderStatus->published : 1;
			
			$res = $modelOrderstatus->store($data);
			$errMsg = $modelOrderstatus->getError();
			
			if ($res != false){
				$commonReturn = new CommonReturn(OK,getWSMsg("OrderStatusCode " .$data['order_status_name'],UP),$res);
				return $commonReturn;
			}else {
				return new SoapFault("UpdateOrderStatusCodeFault", getWSMsg("OrderStatusCode " .$data['order_status_name'],UPKO).' : '.$errMsg);
			}
			
			
		
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function Delete OrderStatus Code
	* (expose as WS)
    * @param string
    * @return result
    */
	function DeleteOrderStatusCode($params) {
	
		include('../vm_soa_conf.php');
				
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		if ($conf['auth_order_delstatus']=="off"){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'VirtueMartModelOrderstatus' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\orderstatus.php');
			$modelOrderstatus = new VirtueMartModelOrderstatus;
			
			$data['virtuemart_orderstate_id'] = $params->order_status_id;
			$res = $modelOrderstatus->remove($data);
			$errMsg = $modelOrderstatus->getError();
			
			if ($res != false){
				$commonReturn = new CommonReturn(OK,getWSMsg("OrderStatusCode " .$params->order_status_id,DEL),$params->order_status_id);
				return $commonReturn;
			}else {
				return new SoapFault("DeleteOrderStatusCodeFault", getWSMsg("OrderStatusCode " .$params->order_status_id,DELKO).' : '.$errMsg);
			}
			
		
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}	
	

	
	
	
	/* SOAP SETTINGS */
	
	if ($vmConfig->get('soap_ws_order_on')==1){
			
		$cache = "0";
		if ($conf['order_cache'] == "on")$cache = "1";
		ini_set("soap.wsdl_cache_enabled", $cache); // wsdl cache settings
		
		if ($conf['soap_version'] == "SOAP_1_1"){
			$options = array('soap_version' => SOAP_1_1);
		}else {
			$options = array('soap_version' => SOAP_1_2);
		}

		/** SOAP SERVER **/
		if (empty($conf['BASESITE']) && empty($conf['URL'])){
			$server = new SoapServer(JURI::root(false).'/VM_OrderWSDL.php');
		}else if (!empty($conf['BASESITE'])){
			$server = new SoapServer('http://'.$conf['URL'].'/'.$conf['BASESITE'].'/administrator/components/com_virtuemart/services/VM_OrderWSDL.php');
		}else {
			$server = new SoapServer('http://'.$conf['URL'].'/administrator/components/com_virtuemart/services/VM_OrderWSDL.php');
		}
		
		/* Add Functions */
		$server->addFunction("getOrdersFromStatus");
		$server->addFunction("getOrder");
		$server->addFunction("getOrderStatus");
		$server->addFunction("getAllOrders");
		$server->addFunction("UpdateOrderStatus");
		$server->addFunction("DeleteOrder");
		$server->addFunction("CreateOrder");
		$server->addFunction("GetAllCouponCode");
		$server->addFunction("AddCouponCode");
		$server->addFunction("DeleteCouponCode");	
		$server->addFunction("GetAllShippingRate");	
		$server->addFunction("GetAllShippingCarrier");	
		$server->addFunction("AddShippingCarrier");	
		$server->addFunction("AddShippingRate");	
		$server->addFunction("DeleteShippingCarrier");
		$server->addFunction("DeleteShippingRate");	
		$server->addFunction("GetAllPaymentMethod");	
		$server->addFunction("AddPaymentMethod");	
		$server->addFunction("DeletePaymentMethod");	
		$server->addFunction("UpdatePaymentMethod");	
		$server->addFunction("GetOrderFromDate");	
		$server->addFunction("GetAllCreditCard");
		$server->addFunction("AddCreditCard");
		$server->addFunction("UpdateCreditCard");
		$server->addFunction("DeleteCreditCard");
		$server->addFunction("DeleteOrderStatusCode");
		$server->addFunction("UpdateOrderStatusCode");
		$server->addFunction("AddOrderStatusCode");
		$server->addFunction("UpdateShippingCarrier");
		$server->addFunction("UpdateShippingRate");
		$server->addFunction("GetOrderPaymentInfo");
		
		$server->handle();
		
	}else{
		echo "This Web Service (Order) is desactived";
	}
?> 