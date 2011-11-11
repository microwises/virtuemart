<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage
 * @author RolandD
 * @author Oscar van Eijk
 * @author Max Milbers
 * @author Patrick Kohl
 * @author Valerie Isaksen
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
 * Model for VirtueMart Orders
 * WHY $this->db is never used in the model ?
 * @package VirtueMart
 * @author RolandD
 */
class VirtueMartModelOrders extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('orders');
		$this->addvalidOrderingFieldName(array('o.order_name','pm.payment_method' ) );

		//Delete the field so that and push it to the begin of the array so that it is used as default value
		$key = array_search('o.modified_on',$this->_validOrderingFieldName);
		unset($this->_validOrderingFieldName[$key]);
		array_unshift($this->_validOrderingFieldName,'o.modified_on');

	}

	/**
	 * This function gets the orderId, for anonymous users
	 * @author Max Milbers
	 */
	public function getOrderIdByOrderPass($orderNumber,$orderPass){

		$db = JFactory::getDBO();
		$q = 'SELECT `virtuemart_order_id` FROM `#__virtuemart_orders` WHERE `order_pass`="'.$db->getEscaped($orderPass).'" AND `order_number`="'.$db->getEscaped($orderNumber).'"';
		$db->setQuery($q);
		$orderId = $db->loadResult();

		return $orderId;

	}
	/**
	 * This function gets the orderId, for payment response
	 * author Valerie Isaksen
	 */
	public function getOrderIdByOrderNumber($orderNumber){

		$db = JFactory::getDBO();
		$q = 'SELECT `virtuemart_order_id` FROM `#__virtuemart_orders` WHERE `order_number`="'.$db->getEscaped($orderNumber).'"';
		$db->setQuery($q);
		$orderId = $db->loadResult();
		return $orderId;

	}
	/**
	 * This function seems completly broken, JRequests are not allowed in the model, sql not escaped
	 * This function gets the secured order Number, to send with paiement
	 *
	 */
	public function getOrderNumber($virtuemart_order_id){

		$db = JFactory::getDBO();
		$q = 'SELECT `order_number` FROM `#__virtuemart_orders` WHERE virtuemart_order_id="'.(int)$virtuemart_order_id.'"  ';
		$db->setQuery($q);
		$OrderNumber = $db->loadResult();
		return $OrderNumber;

	}

	/**
	 * Was also broken, actually used?
	 *
	 * get next/previous order id
	 *
	 */

	public function GetOrderId($direction ='DESC', $order_id) {

		if ($direction == 'ASC') {
			$arrow ='>';
		} else {
			$arrow ='<';
		}

		$db = JFactory::getDBO();
		$q = 'SELECT `virtuemart_order_id` FROM `#__virtuemart_orders` WHERE `virtuemart_order_id`'.$arrow.(int)$order_id;
		$q.= ' ORDER BY `virtuemart_order_id` '.$direction ;
		$db->setQuery($q);

		if ($oderId = $db->loadResult()) {
			return $oderId ;
		}
		return 0 ;
	}


	/**
	 * Load a single order
	 */
	public function getOrder($virtuemart_order_id){

		//sanitize id
		$virtuemart_order_id = (int)$virtuemart_order_id;
		$db = JFactory::getDBO();
		$order = array();

		// Get the order details
		$q = "SELECT  u.*,o.*,
				IF(isempty(coupon_code), '-', coupon_code) AS coupon_code,
				s.order_status_name
			FROM #__virtuemart_orders o
			LEFT JOIN #__virtuemart_orderstates s
			ON s.order_status_code = o.order_status
			LEFT JOIN #__virtuemart_order_userinfos u
			ON u.virtuemart_order_id = o.virtuemart_order_id
			WHERE o.virtuemart_order_id=".$virtuemart_order_id;
		$db->setQuery($q);
		$order['details'] = $db->loadObjectList('address_type');

		// Get the order history
		$q = "SELECT *
			FROM #__virtuemart_order_histories
			WHERE virtuemart_order_id=".$virtuemart_order_id."
			ORDER BY virtuemart_order_history_id ASC";
		$db->setQuery($q);
		$order['history'] = $db->loadObjectList();

		// Get the order items
		$q = "SELECT virtuemart_order_item_id, product_quantity, order_item_name,
				order_item_sku, i.virtuemart_product_id, product_item_price,
				product_final_price, product_attribute, order_status,
				intnotes
			FROM #__virtuemart_order_items i
			LEFT JOIN #__virtuemart_products p
			ON p.virtuemart_product_id = i.virtuemart_product_id
			WHERE virtuemart_order_id=".$virtuemart_order_id;
		$db->setQuery($q);
		$order['items'] = $db->loadObjectList();

		vmdebug('getOrder my order',$order);
		return $order;
	}

	/**
	 * Select the products to list on the product list page
	 * @param $uid integer Optional user ID to get the orders of a single user
	 * @param $_ignorePagination boolean If true, ignore the Joomla pagination (for embedded use, default false)
	 */
	public function getOrdersList($uid = 0, $noLimit = false)
	{

		$this->_noLimit = $noLimit;
		$selecct = " o.*, CONCAT_WS(' ',u.first_name,u.middle_name,u.last_name) AS order_name "
		.',pm.payment_name AS payment_method ';
		$from = $this->getOrdersListQuery();
		/*		$_filter = array();
		 if ($uid > 0) {
		$_filter[] = ('u.virtuemart_user_id = ' . (int)$uid);
		}*/

		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		if(!Permissions::getInstance()->check('admin')){
			$myuser		=& JFactory::getUser();
			$whereString= 'WHERE u.virtuemart_user_id = ' . (int)$myuser->id.' AND o.virtuemart_vendor_id = "1" ';
		} else {
			if(empty($uid)){
				$whereString= 'WHERE o.virtuemart_vendor_id = "1" ';
			} else {
				$whereString= 'WHERE u.virtuemart_user_id = ' . (int)$uid.' AND o.virtuemart_vendor_id = "1" ';
			}
		}


		if ($search = JRequest::getWord('search', false)){

			$search = '"%' . $this->_db->getEscaped( $search, true ) . '%"' ;

			if(empty($whereString)){
				$whereString = ' WHERE ( u.first_name LIKE '.$search.' OR u.middle_name LIKE '.$search.' OR u.last_name LIKE '.$search.' OR `order_number` LIKE '.$search.')';
			} else {
				$whereString .= ' AND ( u.first_name LIKE '.$search.' OR u.middle_name LIKE '.$search.' OR u.last_name LIKE '.$search.' OR `order_number` LIKE '.$search.')';
			}


		}
/*		$query .= $this->_getOrdering('virtuemart_order_id', 'DESC');
		if ($_ignorePagination) {
			$this->_data = $this->_getList($query);
		} else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		// set total for pagination
		if(count($this->_data) >0){
			$this->_total = $this->_getListCount($query);
		}*/

		if ( JRequest::getCmd('view') == 'orders') {
			$ordering = $this->_getOrdering();
		} else {
			$ordering = ' order by o.modified_on DESC';
		}

		$this->_data = $this->exeSortSearchListQuery(0,$selecct,$from,$whereString,'',$ordering);


		return $this->_data ;
	}

	/**
	 * List of tables to include for the product query
	 * @author RolandD
	 */
	private function getOrdersListQuery()
	{
		return ' FROM #__virtuemart_orders as o
			LEFT JOIN #__virtuemart_order_userinfos as u
			ON u.virtuemart_order_id = o.virtuemart_order_id AND u.address_type="BT"
			LEFT JOIN #__virtuemart_paymentmethods as pm
			ON o.virtuemart_paymentmethod_id = pm.virtuemart_paymentmethod_id ';
	}


	/**
	 * Update an order item status
	 * @author Max Milbers
	 */
	public function updateSingleItem($virtuemart_order_item_id, $order_status, $comment,$virtuemart_order_id)
	{

		/*		$item = JRequest::getInt('virtuemart_order_item_id', '');
		 $table->load($item);
		$table->order_status = JRequest::getWord('order_status_'.$item, '');
		$table->product_quantity = JRequest::getVar('product_quantity_'.$item, '');
		$table->product_item_price = JRequest::getVar('product_item_price_'.$item, '');
		$table->product_final_price = JRequest::getVar('product_final_price_'.$item, '');*/

		vmdebug('updateSingleItem',$virtuemart_order_item_id,$order_status);

		// Update order item status
		if(empty($virtuemart_order_item_id)){

// 			if (!empty($update_lines[$virtuemart_order_id])) {
				$q = 'SELECT virtuemart_order_item_id
						FROM #__virtuemart_order_items
						WHERE virtuemart_order_id="'.(int)$virtuemart_order_id.'"';
				$db = JFactory::getDBO();
				$db->setQuery($q);
				$virtuemart_order_item_ids = $db->loadResultArray();
// 				if ($order_items) {
// 					foreach ($order_items as $key => $order_item) {
// 						$this->updateSingleItem($order_item->virtuemart_order_item_id, $new_status);
// 					}
// 				}
// 			}
		}else {
			if(!is_array($virtuemart_order_item_id)) $virtuemart_order_item_ids = array($virtuemart_order_item_id);
		}


		/* Send a download ID */
		//if (VmConfig::get('enable_downloads') == '1') $this->mailDownloadId($virtuemart_order_id);

		/* Check if the customer needs to be informed */
		//if (!empty($notify[$virtuemart_order_id])) $this->notifyCustomer($order, $comments,$includeComments);
		//$updated++;

		foreach($virtuemart_order_item_ids as $id){
			$table = $this->getTable('order_items');
			$table->load($id);
			$oldOrderStatus = $table->order_status;

			$data->order_status = $order_status;
			//$data->comment = $comment;

			$data = $table->bindChecknStore($data,true);
		/* Update the order item history */
			//$this->_updateOrderItemHist($id, $order_status, $customer_notified, $comment);
			$errors = $table->getErrors();
			foreach($errors as $error){
				$this->setError( get_class( $this ).'::store '.$error);
			}

			// 		$this->handleStockAfterStatusChanged($order_status,array($product),$table->order_status);
			$this->handleStockAfterStatusChangedPerProduct($order_status, $oldOrderStatus, $table->virtuemart_product_id,$table->product_quantity);

		}

	}



	/**
	 * Strange name is just temporarly
	 *
	 * @param unknown_type $order_id
	 * @param unknown_type $order_status
         * @author Max Milbers
	 */
	public function updateOrderStatus($orders=0, $order_id =0,$order_status=0){

		//General change of orderstatus
		if(empty($orders)){
			//$order_id = array();
			$orders = JRequest::getVar('orders',  array());
			$oldStatus = JRequest::getVar('current_order_status', array());
			// Get the list of updated orders in post
			foreach ($orders as $key => $_order) {
				if ( $order['order_status'] !== $oldStatus[$key]) $orders[$key] = $_order;
			}
			//$order_ids = array_diff_assoc(JRequest::getVar('order_status', array()), JRequest::getVar('current_order_status', array()));

			/* Get the list of orders to notify */
			// TODO as getInt ???


			/* See where the lines should be updated too */
			// TODO as getInt ???
			//$update_lines = JRequest::getVar('update_lines', array());

			/* Get the list of comments */

		}

		// TODO This is not the most logical place for these plugins (or better; the method updateStatus() must be renamed....)
		if(!class_exists('vmShipmentPlugin')) require(JPATH_VM_PLUGINS.DS.'vmshipmentplugin.php');
		if(!class_exists('vmPaymentPlugin')) require(JPATH_VM_PLUGINS.DS.'vmpaymentplugin.php');
		JPluginHelper::importPlugin('vmshipment');
		$_dispatcher = JDispatcher::getInstance();
		$_returnValues = $_dispatcher->trigger('plgVmOnSaveOrderShipmentBE',array(JRequest::get('post')));
		foreach ($_returnValues as $_retVal) {
			if ($_retVal === false) {
				// Stop as soon as the first active plugin returned a failure status
				return;
			}
		}

		if(!is_array($orders)){
			$orders = array($orders);
		}

		/* Process the orders to update */
		$updated = 0;
		$error = 0;
		if ($orders) {
			$notify = JRequest::getVar('customer_notified', array()); // ???
			$comments = JRequest::getVar('comments', array()); // ???
			foreach ($orders as $virtuemart_order_id => $order) {
				if  ($order_id >0) $virtuemart_order_id= $order_id;
				/* Get customer notification */
				//$customer_notified = (@$notify[$virtuemart_order_id] == 1) ? 1 : 0;
				/* Get the comments */
				//$comment = (array_key_exists($virtuemart_order_id, $comments)) ? $comments[$virtuemart_order_id] : '';

				/* Update the order */
				$data = $this->getTable('orders');
				$data->load($virtuemart_order_id);
				$order_status_code = $data->order_status;
				$data->bind($order);
				// Order updates can be ignored if we're updating only lines
				//$order->order_status = $new_status;

				/* When the order is set to "shipped", we can capture the payment */
				if( ($order_status_code == "P" || $order_status_code == "C") && $order['order_status'] == "S") {
					JPluginHelper::importPlugin('vmpayment');
					$_dispatcher = JDispatcher::getInstance();
					$_returnValues = $_dispatcher->trigger('plgVmOnUpdateOrder',array(
					$virtuemart_order_id
					)
					);
					foreach ($_returnValues as $_returnValue) {
						if ($_returnValue === true) {
							break; // Plugin was successfull
						} elseif ($_returnValue === false) {
							return false; // Plugin failed
						}
						// Ignore null status and look for the next returnValue
					}
				}

				/**
				 * If an order gets cancelled, fire a plugin event, perhaps
				 * some authorization needs to be voided
				 */
				if ($order['order_status'] == "X") {
					JPluginHelper::importPlugin('vmpayment');
					$_dispatcher = JDispatcher::getInstance();
					$_dispatcher->trigger('plgVmOnCancelPayment',array(
					$virtuemart_order_id
					,$order_status_code
					,$order['order_status']
					)
					);
				}

				if ($data->store()) {
					$q = 'SELECT virtuemart_order_item_id
										FROM #__virtuemart_order_items
										WHERE virtuemart_order_id="'.$virtuemart_order_id.'"';
					$db = JFactory::getDBO();
					$db->setQuery($q);
					$order_items = $db->loadObjectList();
					if ($order_items) {
						foreach ($order_items as $order_item) {
						$this->updateSingleItem($order_item->virtuemart_order_item_id, $order['order_status'], $order['comments'] , $virtuemart_order_id);
						}
					}
					/* Update the order history */
					$this->_updateOrderHist($virtuemart_order_id, $order['order_status'], $order['customer_notified'], $order['comments']);

					// Send a download ID */
					//if (VmConfig::get('enable_downloads') == '1') $this->mailDownloadId($virtuemart_order_id);

					// Check if the customer needs to be informed */
					if ($order['customer_notified']) {
						$order['virtuemart_order_id'] =$virtuemart_order_id ;
						$this->notifyCustomer($order,  $order['comments'],  $order['customer_notified']);
					}

					JPluginHelper::importPlugin('vmcoupon');
					$dispatcher = JDispatcher::getInstance();
					$returnValues = $dispatcher->trigger('plgVmCouponUpdateOrderStatus', array($data, $order_status_code));
					if(!empty($returnValues)){
						foreach ($returnValues as $returnValue) {
							if ($returnValue !== null  ) {
								//Take a look on this seyi, I am not sure about that, but it should work at least simular note by Max
								//$couponData = $returnValue;
								return $returnValue;
							}
						}
					}

				} else {
					$error++;
				}
			}
		}

	}

	/**
	 * Update an order status and send e-mail if needed
	 * @author RolandD
	 * @author Oscar van Eijk
	 * @deprecated
	 */
	public function updateStatus( $orders=null,$virtuemart_order_id =0){
		$this -> updateOrderStatus($orders,$virtuemart_order_id);
		return;
	}

	/**
	 * Get the information from the cart and create an order from it
	 *
	 * @author Oscar van Eijk
	 * @param object $_cart The cart data
	 * @return mixed The new ordernumber, false on errors
	 */
	public function createOrderFromCart($cart)
	{
		if ($cart === null) {
			vmError('createOrderFromCart() called without a cart - that\'s a programming bug','Can\'t create order, sorry.');
			return false;
		}

		$usr = JFactory::getUser();
		$prices = $cart->getCartPrices();
		if (($orderID = $this->_createOrder($cart, $usr, $prices)) == 0) {
			vmError('Couldn\'t create order','Couldn\'t create order');
			return false;
		}
		if (!$this->_createOrderLines($orderID, $cart)) {
			vmError('Couldn\'t create order items','Couldn\'t create order items');
			return false;
		}
		$this->_updateOrderHist($orderID);
		if (!$this->_writeUserInfo($orderID, $usr, $cart)) {
			vmError('Couldn\'t create order history','Couldn\'t create order history');
			return false;
		}
		$this->_handlePayment($orderID, $cart, $prices);
		$this->_handleShipment($orderID, $cart, $prices);

		return $orderID;
	}

	/**
	 * Write the order header record
	 *
	 * @author Oscar van Eijk
	 * @param object $_cart The cart data
	 * @param object $_usr User object
	 * @param array $_prices Price data
	 * @return integer The new ordernumber
	 */
	private function _createOrder($_cart, $_usr, $_prices)
	{
		//		TODO We need tablefields for the new values:
		//		Shipment:
		//		$_prices['shipmentValue']		w/out tax
		//		$_prices['shipmentTax']			Tax
		//		$_prices['salesPriceShipment']	Total
		//
		//		Payment:
		//		$_prices['paymentValue']		w/out tax
		//		$_prices['paymentTax']			Tax
		//		$_prices['paymentDiscount']		Discount
		//		$_prices['salesPricePayment']	Total

		$_orderData = new stdClass();

		$_orderData->virtuemart_order_id = null;
		$_orderData->virtuemart_user_id = $_usr->get('id');
		$_orderData->virtuemart_vendor_id = $_cart->vendorId;
		$_orderData->order_number = $this->generateOrderNumber($_usr->get('id'),8);
		$_orderData->order_pass = 'p_'.$this->generateOrderNumber($_orderData->order_number, 6);
		//Note as long we do not have an extra table only storing addresses, the virtuemart_userinfo_id is not needed.
		//The virtuemart_userinfo_id is just the id of a stored address and is only necessary in the user maintance view or for choosing addresses.
		//the saved order should be an snapshot with plain data written in it.
		//		$_orderData->virtuemart_userinfo_id = 'TODO'; // $_cart['BT']['virtuemart_userinfo_id']; // TODO; Add it in the cart... but where is this used? Obsolete?
		$_orderData->order_total = $_prices['billTotal'];
		$_orderData->order_subtotal = $_prices['priceWithoutTax'];
		$_orderData->order_tax = $_prices['taxAmount'];
		$_orderData->order_tax_details = null; // TODO What's this?? Which data needs to be serialized?  I dont know also
		$_orderData->order_shipment = $_prices['shipmentValue'];
		$_orderData->order_shipment_tax = $_prices['shipmentTax'];
		$_orderData->order_payment = $_prices['paymentValue'];
		$_orderData->order_payment_tax = $_prices['paymentTax'];
		if (!empty($_cart->couponCode)) {
			$_orderData->coupon_code = $_cart->couponCode;
			$_orderData->coupon_discount = $_prices['salesPriceCoupon'];
		}
		$_orderData->order_discount = $_prices['discountAmount'];

		$_orderData->order_status = 'P';
// 		if (isset($_cart->virtuemart_currency_id)) {
		if (isset($_cart->pricesCurrency)) {
			$_orderData->user_currency_id = $_cart->pricesCurrency;
			$currency = CurrencyDisplay::getInstance();
			if(!empty($currency->exchangeRateShopper)){
				$_orderData->user_currency_rate = $currency->exchangeRateShopper;
			} else {
				$_orderData->user_currency_rate = 1.0;
			}

		}
		$_orderData->virtuemart_paymentmethod_id = $_cart->virtuemart_paymentmethod_id;
		$_orderData->virtuemart_shipmentmethod_id = $_cart->virtuemart_shipmentmethod_id;

		$_filter = JFilterInput::getInstance (array('br', 'i', 'em', 'b', 'strong'), array(), 0, 0, 1);
		$_orderData->customer_note = $_filter->clean($_cart->customer_comment);
		$_orderData->ip_address = $_SERVER['REMOTE_ADDR'];

		$orderTable =  $this->getTable('orders');
		$orderTable -> bindChecknStore($_orderData);
		$errors = $orderTable->getErrors();
		foreach($errors as $error){
			$this->setError($error);
		}

		$db = JFactory::getDBO();
		$_orderID = $db->insertid();

		if (!empty($_cart->couponCode)) {
			//set the virtuemart_order_id in the Request for 3rd party coupon components (by Seyi and Max)
			JRequest::setVar ( 'virtuemart_order_id', $_orderData->virtuemart_order_id );
			// If a gift coupon was used, remove it now
			CouponHelper::RemoveCoupon($_cart->couponCode);
		}

		return $_orderID;
	}

	/**
	 * Write the BillTo record, and if set, the ShipTo record
	 *
	 * @author Oscar van Eijk
	 * @param integer $_id Order ID
	 * @param object $_usr User object
	 * @param object $_cart Cart object
	 * @return boolean True on success
	 */
	private function _writeUserInfo($_id, &$_usr, $_cart)
	{
		$_userInfoData = array();

		if(!class_exists('VirtueMartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php');

		//if(!class_exists('shopFunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');

		$_userFieldsModel = new VirtueMartModelUserfields();
		$_userFieldsBT = $_userFieldsModel->getUserFields('account'
		, array('delimiters'=>true, 'captcha'=>true)
		, array('username', 'password', 'password2', 'user_is_vendor')
		);


		foreach ($_userFieldsBT as $_fld) {
			$_name = $_fld->name;
			if(!empty( $_cart->BT[$_name])){

				$_userInfoData[$_name] = $_cart->BT[$_name];
			}
		}

		$_userInfoData['virtuemart_order_id'] = $_id;
		$_userInfoData['virtuemart_user_id'] = $_usr->get('id');
		$_userInfoData['address_type'] = 'BT';

		$order_userinfosTable = $this->getTable('order_userinfos');
		if (!$order_userinfosTable->bindChecknStore($_userInfoData)){
			vmError($order_userinfosTable->getError());
			return false;
		}

		if ($_cart->ST) {
			$_userInfoData = array();
// 			$_userInfoData['virtuemart_order_userinfo_id'] = null; // Reset key to make sure it doesn't get overwritten by ST
			$_userFieldsST = $_userFieldsModel->getUserFields('shipment'
			, array('delimiters'=>true, 'captcha'=>true)
			, array('username', 'password', 'password2', 'user_is_vendor')
			);
			foreach ($_userFieldsST as $_fld) {
				$_name = $_fld->name;
				if(!empty( $_cart->ST[$_name])){
					$_userInfoData[$_name] = $_cart->ST[$_name];
				}
			}

			$_userInfoData['virtuemart_order_id'] = $_id;
			$_userInfoData['virtuemart_user_id'] = $_usr->get('id');
			$_userInfoData['address_type'] = 'ST';
			$order_userinfosTable = $this->getTable('order_userinfos');
			if (!$order_userinfosTable->bindChecknStore($_userInfoData)){
				vmError($order_userinfosTable->getError());
				return false;
			}
		}
		return true;
	}

	/**
	 * Handle the selected payment method. If triggered to do so, this method will also
	 * take care of the stock updates.
	 *
	 * @author Oscar van Eijk
	 * @param int $_orderID Order ID
	 * @param object $_cart Cart object
	 * @param array $_prices Price data
	 */
	private function _handlePayment($orderID, $cart, $prices)
	{

		$order_number = $this->getOrderNumber($orderID);

		JPluginHelper::importPlugin('vmpayment');
		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmOnConfirmedOrderStoreData',array(
		$order_number
		,$cart
		,$prices
		));

		foreach ($returnValues as $returnValue) {
			if ($returnValue !== null) {
			    if ($returnValue ) {
				// i have some infos to display ??
				$this->handleStockAfterStatusChanged('P',$cart->products,'N');
				break; // This was the active plugin, so there's nothing left to do here.
			    }
			}
			// Returnvalue 'null' must be ignored; it's an inactive plugin so look for the next one

		}
	}

	function handleStockAfterStatusChanged($newState,$products,$oldState = 'P'){
// after cart
		foreach ($products as $prod) {
			$this->handleStockAfterStatusChangedPerProduct($newState,$oldState,$prod->virtuemart_product_id,$prod->quantity);
		}
	}



	function handleStockAfterStatusChangedPerProduct($newState, $oldState,$productId, $quantity) {

		if($newState == $oldState) return;
		$StatutWhiteList = array('P','C','X','R','S','N');
		if(!in_array($oldState,$StatutWhiteList) or !in_array($newState,$StatutWhiteList)) {
			vmError('The workflow for '.$newState.' or  '.$oldState.' is unknown, take a look on model/orders function handleStockAfterStatusChanged','Cant process workflow, contact the shopowner status '.$newState);
			return ;
			}
		//vmdebug( 'updatestock qt :' , $quantity.' id :'.$productId);
		// P 	Pending
		// C 	Confirmed
		// X 	Cancelled
		// R 	Refunded
		// S 	Shipped
		// N 	New or comming from cart
		//  TO have no product setted as ordered when added to cart simply delete 'P' FROM array Reserved
		// don't set same values in the 2 arrays !!!
		// stockOut is in normal case shipped product

		// the status decreasing real stock ?
		$stockOut = array('S');
		$isOut = in_array($newState, $stockOut);
		$wasOut= in_array($oldState, $stockOut);
		// Stock change ?
		if ($isOut && !$wasOut)     $product_in_stock = '-';
		else if ($wasOut && !$isOut ) $product_in_stock = '+';
		else $product_in_stock = '=';

		// the status increasing reserved stock(virtual Stock = product_in_stock - product_ordered)
		$Reserved =  array('P','C');
		$isReserved = in_array($newState, $Reserved);
		$wasReserved = in_array($oldState, $Reserved);
		// reserved stock must be change(all ordered product)
		if ($isReserved && !$wasReserved )     $product_ordered = '+';
		else if (!$isReserved && $wasReserved ) $product_ordered = '-';
		else $product_ordered = '=';


		if(!class_exists('VirtueMartModelProduct')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'product.php');
		$productModel = new VirtueMartModelProduct();

		$productModel->updateStock ($productId, $quantity,$product_in_stock,$product_ordered);

	}

	/**
	 * Handle the selected shipment method. If triggered to do so, this method will also
	 * take care of the stock updates.
	 *
	 * @author Valérie Isaksen
	 * @param int $orderID Order ID
	 * @param object $_cart Cart object
	 * @param array $prices Price data
	 */
	private function _handleShipment($orderID, $cart, $prices)
	{
		JPluginHelper::importPlugin('vmshipment');
		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmOnConfirmedOrderStoreData',array(
		$orderID
		,$cart
		,$prices
		));


	}
	/**
	 * Create the ordered item records
	 *
	 * @author Oscar van Eijk
	 * @author Kohl Patrick
	 * @param integer $_id integer Order ID
	 * @param object $_cart array The cart data
	 * @return boolean True on success
	 */
	private function _createOrderLines($_id, $_cart)
	{
		$_orderItems = $this->getTable('order_items');
		//		$_lineCount = 0;
		foreach ($_cart->products as $priceKey=>$_prod) {
			if (!is_int($priceKey)) {
				if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
				$calculator = calculationHelper::getInstance();
				$variantmods = $calculator->parseModifier($priceKey);
				$row=0 ;
				$product_id = (int)$priceKey;
				$_prod->product_attribute = '';
				$product_attribute = array();
				foreach($variantmods as $variant=>$selected){
					if ($selected) {
						if(!class_exists('VirtueMartModelCustomfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'customfields.php');
						$productCustom = VirtueMartModelCustomfields::getProductCustomFieldCart ($product_id,$selected );
						if ($productCustom->field_type == "E") {

							if(!class_exists('vmCustomPlugin')) require(JPATH_VM_PLUGINS.DS.'vmcustomplugin.php');
							//$html ='<input type="hidden" value="'.$field->custom_value.'" name="customPrice['.$row.']['.$field->virtuemart_custom_id.']">';
							$product_attribute[$selected] = vmCustomPlugin::displayInCartPlugin( $_prod,$productCustom, $row,'Order');
							// foreach ($product->userfield as $pKey => $puser) {
								// $this->data->products[$i]['customfieldsCart'] .= '<br/ > <b>'.$product->customfieldsCart[$row]->custom_title.' : </b>'.$puser.' '.$product->customfieldsCart[$row]->custom_field_desc;
							// }
						} else {

							$product_attribute[$selected] = ' <span>'.$productCustom->custom_title.' : </span>'.$productCustom->custom_value;
						}
					}
					$row++;
				}
				//if (isset($_prod->userfield )) $_prod->product_attribute .= '<br/ > <b>'.$_prod->userfield.' : </b>';
				$_orderItems->product_attribute = json_encode($product_attribute);
				//print_r($product_attribute);
			} else {
			    $_orderItems->product_attribute = null ;
			}
			// TODO: add fields for the following data:
			//    * [double] basePrice = 38.48
			//    * [double] basePriceVariant = 38.48
			//    * [double] basePriceWithTax = 42.04
			//    * [double] discountedPriceWithoutTax = 36.48
			//    * [double] priceBeforeTax = 36.48
			//    * [double] salesPrice = 39.85
			//    * [double] salesPriceTemp = 39.85
			//    * [double] taxAmount = 3.37
			//    * [double] salesPriceWithDiscount = 0
			//    * [double] discountAmount = 2.19
			//    * [double] priceWithoutTax = 36.48
			//    * [double] variantModification = 0
			$_orderItems->virtuemart_order_item_id = null;
			$_orderItems->virtuemart_order_id = $_id;
			$_orderItems->virtuemart_userinfo_id = 'TODO'; //$_cart['BT']['virtuemart_userinfo_id']; // TODO; Add it in the cart... but where is this used? Obsolete?
			$_orderItems->virtuemart_vendor_id = $_prod->virtuemart_vendor_id;
			$_orderItems->virtuemart_product_id = $_prod->virtuemart_product_id;
			$_orderItems->order_item_sku = $_prod->product_sku;
			$_orderItems->order_item_name = $_prod->product_name; //TODO Patrick
			$_orderItems->product_quantity = $_prod->quantity;
			$_orderItems->product_item_price = $_cart->pricesUnformatted[$priceKey]['basePrice'];
			$_orderItems->product_final_price = $_cart->pricesUnformatted[$priceKey]['salesPrice'];
			//			$_orderItems->order_item_currency = $_prices[$_lineCount]['']; // TODO Currency
			$_orderItems->order_status = 'P';


			if (!$_orderItems->check()) {
				$this->setError($this->getError());
				return false;
			}

			// Save the record to the database
			if (!$_orderItems->store()) {
				$this->setError($this->getError());
				return false;
			}
			$this->handleStockAfterStatusChangedPerProduct( $_orderItems->order_status,'N',$_orderItems->virtuemart_product_id,$_orderItems->product_quantity);

		}
		//jExit();
		return true;
	}

	/**
	 * Update the order history
	 *
	 * @author Oscar van Eijk
	 * @param $_id Order ID
	 * @param $_status New order status (default: P)
	 * @param $_notified 1 (default) if the customer was notified, 0 otherwise
	 * @param $_comment (Customer) comment, default empty
	 */
	private function _updateOrderHist($_id, $_status = 'P', $_notified = 1, $_comment = '')
	{
		$_orderHist = $this->getTable('order_histories');
		$_orderHist->virtuemart_order_id = $_id;
		$_orderHist->order_status_code = $_status;
		//$_orderHist->date_added = date('Y-m-d G:i:s', time());
		$_orderHist->customer_notified = $_notified;
		$_orderHist->comments = $_comment;
		$_orderHist->store();
	}	/**
	 * Update the order item history
	 *
	 * @author Oscar van Eijk,kohl patrick
	 * @param $_id Order ID
	 * @param $_status New order status (default: P)
	 * @param $_notified 1 (default) if the customer was notified, 0 otherwise
	 * @param $_comment (Customer) comment, default empty
	 */
	private function _updateOrderItemHist($_id, $status = 'P', $notified = 1, $comment = '')
	{
		$_orderHist = $this->getTable('order_item_histories');
		$_orderHist->virtuemart_order_item_id = $_id;
		$_orderHist->order_status_code = $status;
		$_orderHist->customer_notified = $notified;
		$_orderHist->comments = $comment;
		$_orderHist->store();
	}

	/**
	 * Generate a unique ordernumber. This is done in a similar way as VM1.1.x, although
	 * the reason for this is unclear to me :-S
	 *
	 * @author Oscar van Eijk
	 * @param integer $uid The user ID. Defaults to 0 for guests
	 * @return string A unique ordernumber
	 */
	private function generateOrderNumber($uid = 0,$length=10)
	{
		return substr( md5( session_id().(string)time().(string)$uid )
		,0
		,$length
		);
	}


	/**
	 * E-mails the Download-ID to the customer
	 * or removes the Download-ID from the product_downloads table
	 *
	 * @author ?, Christopher Roussel
	 * @return boolean
	 */
	function mailDownloadId ($virtuemart_order_id) {
		if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		$mainframe = JFactory::getApplication();
		$vars = array('orderID' => $virtuemart_order_id);

		//TODO, mail download  this url is old
		//$vars['url'] = VmConfig::get('url')."index.php?option=com_virtuemart&page=shop.downloads&Itemid=".$sess->getShopItemid();

		$db = JFactory::getDBO();
		$db->setQuery('SELECT order_status FROM #__virtuemart_orders WHERE virtuemart_order_id='.$virtuemart_order_id);
		$order_status = $db->loadResult();

		if ($order_status == VmConfig::get('enable_download_status')) {
			$q = 'SELECT * '; //virtuemart_order_id,virtuemart_user_id,download_id,file_name
			$q .= 'FROM #__virtuemart_product_downloads
				WHERE virtuemart_order_id = "'.$virtuemart_order_id.'"';
			$db->setQuery($q);
			$downloads = $db->loadObjectList();
			if ($downloads) {
				$q = "SELECT CONCAT_WS(' ',first_name, middle_name , last_name) AS full_name, email
					FROM #__virtuemart_userinfos
					LEFT JOIN #__users ju
					ON (ju.id = u.virtuemart_user_id)
					WHERE virtuemart_user_id = '".$downloads[0]->virtuemart_user_id."'
					AND address_type='BT'
					LIMIT 1";
				$db->setQuery($q);
				$user = $db->loadObject();
				$vars['downloads'] = $downloads;
				$vars['user'] = $user;
				$vars['layoutName'] = 'download';

				if (shopFunctionsF::renderMail('orders', $user->email, $vars)) {
					$string = 'COM_VIRTUEMART_DOWNLOADS_SEND_MSG';
				}
				else {
					$string = 'COM_VIRTUEMART_DOWNLOADS_ERR_SEND';
				}
				$mainframe->enqueueMessage(JText::_($string,false). " ". $user->full_name. ' '.$user->email);
			}
		}
		else if ($order_status == VmConfig::get('disable_download_status')) {
			$q = "DELETE FROM #__virtuemart_product_downloads WHERE virtuemart_order_id=".$virtuemart_order_id;
			$db->setQuery($q);
			$db->query();
		}

		return true;
	}

	/**
	 * Notifies the customer that the Order Status has been changed
	 *
	 * @author RolandD, Christopher Roussel
	 * @todo: Fix URL when we have front-end done
	 */
	function notifyCustomer($order, $comments,$includeComments ) {
		if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		$mainframe = JFactory::getApplication();
		$vars = array('order' => $order, 'comments' => $comments, 'includeComments' => $includeComments);
		//$vars['includeComments'] = JRequest::getVar('customer_notified', array());

		//$url = VmConfig::get('secureurl')."index.php?option=com_virtuemart&page=account.order_details&virtuemart_order_id=".$order->virtuemart_order_id.'&Itemid='.$sess->getShopItemid();
		$vars['url'] = 'url';
		$vars['doVendor']=false;
		$db = JFactory::getDBO();
		$q = "SELECT CONCAT_WS(' ',first_name, middle_name , last_name) AS full_name, email, order_status_name
			FROM #__virtuemart_order_userinfos
			LEFT JOIN #__virtuemart_orders
			ON #__virtuemart_orders.virtuemart_user_id = #__virtuemart_order_userinfos.virtuemart_user_id
			LEFT JOIN #__virtuemart_orderstates
			ON #__virtuemart_orderstates.order_status_code = #__virtuemart_orders.order_status
			WHERE #__virtuemart_orders.virtuemart_order_id = '".$order['virtuemart_order_id']."'
			AND #__virtuemart_orders.virtuemart_order_id = #__virtuemart_order_userinfos.virtuemart_order_id";
		$db->setQuery($q);
		$db->query();
		$user = $db->loadObject();
		$vars['user'] = $user;

		/* Send the email */
		if (shopFunctionsF::renderMail('orders', $user->email, $vars)) {
			$string = 'COM_VIRTUEMART_DOWNLOADS_SEND_MSG';
		}
		else {
			$string = 'COM_VIRTUEMART_DOWNLOADS_ERR_SEND';
		}
		$mainframe->enqueueMessage( JText::_($string,false).' '.$user->full_name. ', '.$user->email);
	}


	/**
	 * Retrieve the details for an order line item.
	 *
	 * @author RickG
	 * @param string $orderId Order id number
	 * @param string $orderLineId Order line item number
	 * @return object Object containing the order item details.
	 */
	function getOrderLineDetails($orderId, $orderLineId) {
		$table = $this->getTable('order_items');
		if ($table->load((int)$orderLineId)) {
			return $table;
		}
		else {
			$table->reset();
			$table->virtuemart_order_id = $orderId;
			return $table;
		}
	}


	/**
	 * Save an order line item.
	 *
	 * @author RickG
	 * @return boolean True of remove was successful, false otherwise
	 */
	function saveOrderLineItem($data) {
		$table = $this->getTable('order_items');

		//Done in the table already
		/*
		$curDate = JFactory::getDate();
		$data['modified_on'] = $curDate->toMySql();*/

		if(!class_exists('vmShipmentPlugin')) require(JPATH_VM_PLUGINS.DS.'vmshipmentplugin.php');
		JPluginHelper::importPlugin('vmshipment');
		$_dispatcher = JDispatcher::getInstance();
		$_returnValues = $_dispatcher->trigger('plgVmOnUpdateOrderLine',array($data));
		foreach ($_returnValues as $_retVal) {
			if ($_retVal === false) {
				// Stop as soon as the first active plugin returned a failure status
				return;
			}
		}

		return $table->bindChecknStore($data);

		//		return true;
	}

	/**
	 * Remove an order
	 *
	 * @author Oscar van Eijk
	 * @return boolean True of remove was successful, false otherwise
	 */
	function remove($cids) {
vmdebug('remove', $cids);
		foreach($cids as $_id) {
			$this->removeOrderItems ($_id);
		}

		parent::remove($cids);
	}
	/*
	 *remove product from order item table
	*@var $virtuemart_order_id Order to clear
	*/
	function removeOrderItems ($virtuemart_order_id){
		$q ='DELETE from `#__virtuemart_order_items` WHERE `virtuemart_order_id` = ' .(int) $virtuemart_order_id;
		$this->_db->setQuery($q);

		if ($this->_db->query() === false) {
			$this->setError($this->_db->getError());
			return false;
		}
		return true;
	}
	/**
	 * Remove an order line item.
	 *
	 * @author RickG
	 * @param string $orderLineId Order line item number
	 * @return boolean True of remove was successful, false otherwise
	 */
	function removeOrderLineItem($orderLineId) {

		$table = $this->getTable('order_items');

		if ($table->delete($orderLineId)) {
			return true;
		}
		else {
			$this->setError($table->getError());
			return false;
		}
	}


	/**
	 *  Create a list of products for JSON return
	 *
	 * TODO sanitize variables Very unsecure
	 * identical with function in orders?
	 * disabled to unsecure written
	 */
	/*	public function getProductListJson() {
		$db = JFactory::getDBO();
	$filter = JRequest::getVar('q', false);
	$q = "SELECT virtuemart_product_id AS id, CONCAT(product_name, '::', product_sku) AS value
	FROM #__virtuemart_products";
	if ($filter) $q .= " WHERE product_name LIKE '%".$filter."%'";
	$db->setQuery($q);
	return $db->loadObjectList();
	}*/
}


// No closing tag
