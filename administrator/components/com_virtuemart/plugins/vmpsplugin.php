<?php

defined('_JEXEC') or die('Restricted access');
/**
 * abstract class for payment/shipment plugins
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Max Milbers
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: vmpaymentplugin.php 4601 2011-11-03 15:50:01Z alatak $
 */
if (!class_exists('vmPlugin'))
    require(JPATH_VM_PLUGINS . DS . 'vmplugin.php');

abstract class vmPSPlugin extends vmPlugin {

    function __construct(& $subject, $config) {

	parent::__construct($subject, $config);

	$this->_tablepkey = 'id'; //virtuemart_order_id';
	$this->_idName = 'virtuemart_' . $this->_psType . 'method_id';
	$this->_configTable = '#__virtuemart_' . $this->_psType . 'methods';
	$this->_configTableFieldName = $this->_psType . '_params';
	$this->_configTableFileName = $this->_psType . 'methods';
	$this->_configTableClassName = 'Table' . ucfirst($this->_psType) . 'methods'; //TablePaymentmethods
	// 		$this->_configTableIdName = $this->_psType.'_jplugin_id';
	$this->_loggable = true;

	$this->_tableChecked = true;
    }

    public function getVarsToPush() {
	$black_list = array('spacer');
	$data = array();
	if (JVM_VERSION === 2) {
	    $filename = JPATH_SITE . '/plugins/' . $this->_type . '/' . $this->_name . '/' . $this->_name . '.xml';
	} else {
	    $filename = JPATH_SITE . '/plugins/' . $this->_type . '/' . $this->_name . '.xml';
	}
	// Check of the xml file exists
	$filePath = JPath::clean($filename);
	if (is_file($filePath)) {
	    $xml = JFactory::getXMLParser('simple');
	    $result = $xml->loadFile($filename);
	    if ($result) {
		if ($params = $xml->document->params) {
		    foreach ($params as $param) {
			if ($param->_name = "params") {
			    if ($children = $param->_children) {
				foreach ($children as $child) {
				    if (isset($child->_attributes['name'])) {
					$data[$child->_attributes['name']] = array('', 'char');
					$result = true;
				    }
				}
			    }
			}
		    }
		}
	    }
	}

	return $data;
    }

    /**
     * check if it is the correct type
     * @param string $psType either payment or shipment
     * @return boolean
     */
    public function selectedThisType($psType) {
	if ($this->_psType <> $psType) {
	    return false;
	} else {
	    return true;
	}
    }

    /**
     * Create the table for this plugin if it does not yet exist.
     * This functions checks if the called plugin is active one.
     * When yes it is calling the standard method to create the tables
     * @author Valérie Isaksen
     *
     */
    protected function onStoreInstallPluginTable($jplugin_id) {
	if ($res = $this->selectedThisByJPluginId($jplugin_id)) {
	    parent::onStoreInstallPluginTable($this->_psType);
	}
	return $res;
    }

    /**
     * This event is fired after the payment method has been selected. It can be used to store
     * additional payment info in the cart.
     *
     * @author Max Milbers
     * @author Valérie isaksen
     *
     * @param VirtueMartCart $cart: the actual cart
     * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
     *
     */
    public function onSelectCheck(VirtueMartCart $cart) {
	$idName = $this->_idName; //vmdebug('OnSelectCheck',$idName);
	if (!$this->selectedThisByMethodId($cart->$idName)) {
	    return null; // Another method was selected, do nothing
	}
	return true; // this method was selected , and the data is valid by default
    }

    /**
     * displayListFE
     * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for exampel
     *
     * @param object $cart Cart object
     * @param integer $selected ID of the method selected
     * @return boolean True on succes, false on failures, null when this plugin was not selected.
     * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
     *
     * @author Valerie Isaksen
     * @author Max Milbers
     */
    public function displayListFE(VirtueMartCart $cart, $selected = 0, &$htmlIn) {

	if ($this->getPluginMethods($cart->vendorId) === 0) {
	    if (empty($this->_name)) {
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('COM_VIRTUEMART_CART_NO_' . strtoupper($this->_psType)));
		return false;
	    } else {
		return false;
	    }
	}

	$html = array();
	$method_name = $this->_psType . '_name';
	foreach ($this->methods as $method) {
	    if ($this->checkConditions($cart, $method, $cart->pricesUnformatted)) {
		$methodSalesPrice = $this->calculateSalesPrice($cart, $method, $cart->pricesUnformatted);
		$method->$method_name = $this->renderPluginName($method);
		$html [] = $this->getPluginHtml($method, $selected, $methodSalesPrice);
	    }
	}
	if (!empty($html)) {
	    $htmlIn[] = $html;
	    return true;
	}

	return false;
    }

    /*
     * onSelectedCalculatePrice
     * Calculate the price (value, tax_id) of the selected method
     * It is called by the calculator
     * This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
     * @author Valerie Isaksen
     * @cart: VirtueMartCart the current cart
     * @cart_prices: array the new cart prices
     * @return null if the method was not selected, false if the shiiping rate is not valid any more, true otherwise
     *
     *
     */

    public function onSelectedCalculatePrice(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {
	$id = $this->_idName;
	if (!($method = $this->selectedThisByMethodId($cart->$id))) {
	    return null; // Another method was selected, do nothing
	}

	if (!($method = $this->getVmPluginMethod($cart->$id) )) {
	    return null;
	}

	$cart_prices_name = '';
	$cart_prices[$this->_psType . '_tax_id'] = 0;
	$cart_prices['cost'] = 0;

	if (!$this->checkConditions($cart, $method, $cart_prices)) {
	    return false;
	}
	$paramsName = $this->_psType . '_params';
	$cart_prices_name = $this->renderPluginName($method);
	$this->setCartPrices($cart, $cart_prices, $method);

	return true;
    }

    /**
     * onCheckAutomaticSelected
     * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
     * The plugin must check first if it is the correct type
     * @author Valerie Isaksen
     * @param VirtueMartCart cart: the cart object
     * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
     *
     */
    function onCheckAutomaticSelected(VirtueMartCart $cart, array $cart_prices = array()) {

	$nbPlugin = 0;
	$virtuemart_pluginmethod_id = 0;

	$nbMethod = $this->getSelectable($cart, $virtuemart_pluginmethod_id, $cart_prices);
	if ($nbMethod == null) {
	    return null;
	} else {
	    return ($nbMethod == 1) ? $virtuemart_pluginmethod_id : 0;
	}
    }

    /**
     * This method is fired when showing the order details in the frontend.
     * It displays the method-specific data.
     *
     * @param integer $order_id The order ID
     * @return mixed Null for methods that aren't active, text (HTML) otherwise
     * @author Max Milbers
     * @author Valerie Isaksen
     */
    protected function onShowOrderFE($virtuemart_order_id, $virtuemart_method_id, &$method_info) {
	if (!($this->selectedThisByMethodId($virtuemart_method_id))) {
	    return null;
	}
	$method_info = $this->getOrderMethodNamebyOrderId($virtuemart_order_id);
    }

    /**
     *
     * @author Valerie Isaksen
     * @author Max Milbers
     * @param int $virtuemart_order_id
     * @return string pluginName from the plugin table
     */
    private function getOrderMethodNamebyOrderId($virtuemart_order_id) {

	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
	$db->setQuery($q);
	if (!($pluginInfo = $db->loadObject())) {
	    vmWarn('Attention, ' . $this->_tablename . ' has not any entry for the order ' . $db->getErrorMsg());
	    return null;
	}
	$idName = $this->_psType . '_name';

	return $pluginInfo->$idName;
    }

    /**
     *
     * @author Valerie Isaksen
     * @author Max Milbers
     * @param int $virtuemart_order_id
     * @return string pluginName from the plugin table
     */
    private function getOrderPluginNamebyOrderId($virtuemart_order_id) {

	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
	$db->setQuery($q);
	if (!($pluginInfo = $db->loadObject())) {
	    vmWarn(500, $q . " getOrderPluginNamebyOrderId " . $db->getErrorMsg());
	    return null;
	}
	$idName = $this->_idName;

	return $pluginInfo->$idName;
    }

    /**
     * check if it is the correct element
     * @param string $element either standard or paypal
     * @return boolean
     */
    public function selectedThisElement($element) {
	if ($this->_name <> $element) {
	    return false;
	} else {
	    return true;
	}
    }

    /**
     * This method is fired when showing the order details in the backend.
     * It displays the the payment method-specific data.
     * All plugins *must* reimplement this method.
     *
     * @param integer $_virtuemart_order_id The order ID
     * @param integer $_paymethod_id Payment method used for this order
     * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
     * @author Max Milbers
     * @author Valerie Isaksen
     */
    function onShowOrderBE($_virtuemart_order_id, $_method_id) {
	return null;
    }

    /**
     * This method is fired when showing when priting an Order
     * It displays the the payment method-specific data.
     *
     * @param integer $_virtuemart_order_id The order ID
     * @param integer $method_id  method used for this order
     * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    function onShowOrderPrint($order_number, $method_id) {
	if (!$this->selectedThisByMethodId($method_id)) {
	    return null; // Another method was selected, do nothing
	}
	if (!($order_name = $this->getOrderPluginName($order_number, $method_id))) {
	    return null;
	}

	JFactory::getLanguage()->load('com_virtuemart');
	$html = '<table class="admintable">' . "\n"
		. '	<thead>' . "\n"
		. '		<tr>' . "\n"
		. '			<td class="key" style="text-align: center;" colspan="2">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_' . $this->_type . '_LBL') . '</td>' . "\n"
		. '		</tr>' . "\n"
		. '	</thead>' . "\n"
		. '	<tr>' . "\n"
		. '		<td class="key">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_' . $this->_type . '_LBL') . ': </td>' . "\n"
		. '		<td align="left">' . $order_name . '</td>' . "\n"
		. '	</tr>' . "\n";

	$html .= '</table>' . "\n";
	return $html;
    }

    private function getOrderPluginName($order_number, $pluginmethod_id) {

	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `' . $this->_tablename . '` WHERE `order_number` = "' . $order_number . '"
		AND `' . $this->_idName . '` =' . $pluginmethod_id;
	$db->setQuery($q);
	if (!($order = $db->loadObject())) {
	    return null;
	}

	$plugin_name = $this->_psType . '_name';
	return $order_plugin->$plugin_name;
    }

    /**
     * Save updated order data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.
     * @author Oscar van Eijk
     */
    public function onUpdateOrder($formData) {
	return null;
    }

    /**
     * Save updated orderline data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.
     * @author Oscar van Eijk
     */
    public function onUpdateOrderLine($formData) {
	return null;
    }

    /**
     * OnEditOrderLineBE
     * This method is fired when editing the order line details in the backend.
     * It can be used to add line specific package codes
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk
     */
    public function onEditOrderLineBE($orderId, $lineId) {
	return null;
    }

    /**
     * This method is fired when showing the order details in the frontend, for every orderline.
     * It can be used to display line specific package codes, e.g. with a link to external tracking and
     * tracing systems
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk
     */
    public function onShowOrderLineFE($orderId, $lineId) {
	return null;
    }

    /**
     * This event is fired when the  method notifies you when an event occurs that affects the order.
     * Typically,  the events  represents for payment authorizations, Fraud Management Filter actions and other actions,
     * such as refunds, disputes, and chargebacks.
     *
     * NOTE for Plugin developers:
     *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
     *
     * @param $return_context: it was given and sent in the payment form. The notification should return it back.
     * Used to know which cart should be emptied, in case it is still in the session.
     * @param int $virtuemart_order_id : payment  order id
     * @param char $new_status : new_status for this order id.
     * @return mixed Null when this method was not selected, otherwise the true or false
     *
     * @author Valerie Isaksen
     *
     */
    public function onNotification() {
	return null;
    }

    /**
     * OnResponseReceived
     * This event is fired when the  method returns to the shop after the transaction
     *
     *  the method itself should send in the URL the parameters needed
     * NOTE for Plugin developers:
     *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
     *
     * @param int $virtuemart_order_id : should return the virtuemart_order_id
     * @param text $html: the html to display
     * @return mixed Null when this method was not selected, otherwise the true or false
     *
     * @author Valerie Isaksen
     *
     */
    function onResponseReceived(&$virtuemart_order_id, &$html) {
	return null;
    }

    function getDebug() {
	return $this->_debug;
    }

    function setDebug($params) {
	return $this->_debug = $params->get('debug');
    }

    /**
     * Get Plugin Data for a go given plugin ID
     * @author Valérie Isaksen
     * @param int $pluginmethod_id The method ID
     * @return  method data
     */
    final protected function getPluginMethod($method_id) {

	if (!$this->selectedThisByMethodId($method_id))
	    return false;
	return $this->getVmPluginMethod($method_id);

	/* 		$db = JFactory::getDBO();

	  // 		$q = 'SELECT * FROM #__virtuemart_shipmentmethods WHERE `virtuemart_shipmentmethod_id`="' . $shipment_id . '" AND `shipment_element` = "'.$this->_name.'"';
	  $q = 'SELECT * FROM
	  `#__virtuemart_' . $this->_psType . 'methods_'.VMLANG.'` as l
	  JOIN `#__virtuemart_' . $this->_psType . 'methods` AS v   USING (`virtuemart_' . $this->_psType . 'method_id`)
	  WHERE `' . $this->_idName . '`="' . $plugin_id . '" AND `' . $this->_psType . '_element`="' . $this->_name . '" ';

	  $db->setQuery($q);
	  return $db->loadObject(); */
    }

    /**
     * Fill the array with all plugins found with this plugin for the current vendor
     * @return True when plugins(s) was (were) found for this vendor, false otherwise
     * @author Oscar van Eijk
     * @author max Milbers
     * @author valerie Isaksen
     */
    protected function getPluginMethods($vendorId) {

	if (!class_exists('VirtueMartModelUser'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'user.php');

	$usermodel = VmModel::getModel('user');
	$user = $usermodel->getUser();
	$user->shopper_groups = (array) $user->shopper_groups;

	$db = JFactory::getDBO();

	$select = 'SELECT l.*, v.*, ';

	if (JVM_VERSION === 1) {
	    $extPlgTable = '#__plugins';
	    $extField1 = 'id';
	    $extField2 = 'element';

	    $select .= 'j.`' . $extField1 . '`, j.`name`, j.`element`, j.`folder`, j.`client_id`, j.`access`,
				j.`params`,  j.`checked_out`, j.`checked_out_time`,  s.virtuemart_shoppergroup_id ';
	} else {
	    $extPlgTable = '#__extensions';
	    $extField1 = 'extension_id';
	    $extField2 = 'element';

	    $select .= 'j.`' . $extField1 . '`,j.`name`, j.`type`, j.`element`, j.`folder`, j.`client_id`, j.`enabled`, j.`access`, j.`protected`, j.`manifest_cache`,
				j.`params`, j.`custom_data`, j.`system_data`, j.`checked_out`, j.`checked_out_time`, j.`state`,  s.virtuemart_shoppergroup_id ';
	}

	$q = $select . ' FROM   `#__virtuemart_' . $this->_psType . 'methods_' . VMLANG . '` as l ';
	$q.= ' JOIN `#__virtuemart_' . $this->_psType . 'methods` AS v   USING (`virtuemart_' . $this->_psType . 'method_id`) ';
	$q.= ' LEFT JOIN `' . $extPlgTable . '` as j ON j.`' . $extField1 . '` =  v.`' . $this->_psType . '_jplugin_id` ';
	$q.= ' LEFT OUTER JOIN `#__virtuemart_' . $this->_psType . 'method_shoppergroups` AS s ON v.`virtuemart_' . $this->_psType . 'method_id` = s.`virtuemart_' . $this->_psType . 'method_id` ';
	$q.= ' WHERE v.`published` = "1" AND j.`' . $extField2 . '` = "' . $this->_name . '"
	    						AND  (v.`virtuemart_vendor_id` = "' . $vendorId . '" OR   v.`virtuemart_vendor_id` = "0")
	    						AND  (';

	foreach ($user->shopper_groups as $groups) {
	    $q .= 's.`virtuemart_shoppergroup_id`= "' . (int) $groups . '" OR';
	}
	$q .= ' (s.`virtuemart_shoppergroup_id`) IS NULL ) ORDER BY v.`ordering`';

	$db->setQuery($q);


	$this->methods = $db->loadObjectList();

	$err = $db->getErrorMsg();
	if (!empty($err)) {
	    vmError('Error reading getPluginMethods ' . $err);
	}
	if ($this->methods) {
	    foreach ($this->methods as $method) {
		VmTable::bindParameterable($method, $this->_xParams, $this->_varsToPushParam);
	    }
	}
	// 		vmdebug('getPluginMethods',$this->methods);
	return count($this->methods);
    }

    /**
     * Get Method Data for a given Payment ID
     * @author Valérie Isaksen
     * @param int $virtuemart_payment_id The Payment ID

     * @return  Payment data
     */
    final protected function getDataByOrderId($virtuemart_order_id) {
	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;

	$db->setQuery($q);
	$methodData = $db->loadObject();

	return $methodData;
    }

    /**
     * Get the total weight for the order, based on which the proper shipping rate
     * can be selected.
     * @param object $cart Cart object
     * @return float Total weight for the order
     * @author Oscar van Eijk
     */
    protected function getOrderWeight(VirtueMartCart $cart, $to_weight_unit) {
	$weight = 0;
	foreach ($cart->products as $product) {
	    $weight += ( ShopFunctions::convertWeigthUnit($product->product_weight, $product->product_weight_uom, $to_weight_unit) * $product->quantity);
	}
	return $weight;
    }

    /**
     * getThisName
     * Get the name of the method
     * @param int $id The method ID
     * @author Valérie Isaksen
     * @return string Shipment name
     */
    final protected function getThisName($virtuemart_method_id) {
	$db = JFactory::getDBO();
	$q = 'SELECT `' . $this->_psType . '_name` '
		. 'FROM #__virtuemart_' . $this->_psType . 'methods '
		. 'WHERE ' . $this->_idName . ' = "' . $virtuemart_method_id . '" ';
	$db->setQuery($q);
	return $db->loadResult(); // TODO Error check
    }

    /**
     * This functions gets the used and configured  method
     * pelement of this class determines the used jplugin.
     * The right  method is determined by the vendor and the jplugin id.
     *
     * This function sets the used  plugin as variable of this class
     * @author Max Milbers
     *
     */
    /* 	protected function getVmParams($vendorId=0, $method_id=0) {

      if (!$vendorId)
      $vendorId = 1;
      $db = JFactory::getDBO();

      $q = 'SELECT   `' . $this->_psType . '_params` FROM #__virtuemart_' . $this->_psType . 'methods WHERE `' . $this->_idName . '` = "' . $method_id . '" AND `virtuemart_vendor_id` = "' . $vendorId . '" AND `published`="1" ';
      $db->setQuery($q);
      return $db->loadResult();
      }
     */

    /**
     * Extends the standard function in vmplugin. Extendst the input data by virtuemart_order_id
     * Calls the parent to execute the write operation
     *
     * @author Max Milbers
     * @param array $_values
     * @param string $_table
     */
    protected function storePSPluginInternalData($values, $primaryKey = 0, $preload = false) {
	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
	if (!isset($values['virtuemart_order_id'])) {
	    $values['virtuemart_order_id'] = VirtueMartModelOrders::getOrderIdByOrderNumber($values['order_number']);
	}
	$this->storePluginInternalData($values, $primaryKey, 0, $preload);
    }

    /**
     * Something went wrong, Send notification to all administrators
     * @param string subject of the mail
     * @param string message
     */
    protected function sendEmailToVendorAndAdmins($subject, $message) {
	// recipient is vendor and admin
	$vendorId = 1;
	$vendorModel = VmModel::getModel('vendor');
	$vendorEmail = $vendorModel->getVendorEmail($vendorId);
	$vendorName = $vendorModel->getVendorName($vendorId);
	JUtility::sendMail($vendorEmail, $vendorName, $vendorEmail, $subject, $message);
	if (JVM_VERSION === 1) {
	    //get all super administrator
	    $query = 'SELECT name, email, sendEmail' .
		    ' FROM #__users' .
		    ' WHERE LOWER( usertype ) = "super administrator"';
	} else {
	    $query = 'SELECT name, email, sendEmail' .
		    ' FROM #__users' .
		    ' WHERE sendEmail=1';
	}
	$db = JFactory::getDBO();
	$db->setQuery($query);
	$rows = $db->loadObjectList();

	$subject = html_entity_decode($subject, ENT_QUOTES);

	// get superadministrators id
	foreach ($rows as $row) {
	    if ($row->sendEmail) {
		$message = html_entity_decode($message, ENT_QUOTES);
		JUtility::sendMail($vendorEmail, $vendorName, $row->email, $subject, $message);
	    }
	}
    }

    /**
     * displays the logos of a VirtueMart plugin
     *
     * @author Valerie Isaksen
     * @author Max Milbers
     * @param array $logo_list
     * @return html with logos
     */
    protected function displayLogos($logo_list) {

	$img = "";

	if (!(empty($logo_list))) {
	    $url = JURI::root() . 'images/stories/virtuemart/' . $this->_psType . '/';
	    if (!is_array($logo_list))
		$logo_list = (array) $logo_list;
	    foreach ($logo_list as $logo) {
		$alt_text = substr($logo, 0, strpos($logo, '.'));
		$img .= '<img align="middle" src="' . $url . $logo . '"  alt="' . $alt_text . '" /> ';
	    }
	}
	return $img;
    }

    /*
     * @param $plugin plugin
     */

    protected function renderPluginName($plugin) {
	$return = '';
	$plugin_name = $this->_psType . '_name';
	$plugin_desc = $this->_psType . '_desc';
	$description = '';
	// 		$params = new JParameter($plugin->$plugin_params);
	// 		$logo = $params->get($this->_psType . '_logos');
	$logosFieldName = $this->_psType . '_logos';
	$logos = $plugin->$logosFieldName;
	if (!empty($logos)) {
	    $return = $this->displayLogos($logos) . ' ';
	}
	if (!empty($plugin->$plugin_desc)) {
	    $description = '<span class="' . $this->_type . '_description">' . $plugin->$plugin_desc . '</span>';
	}
	$pluginName = $return . '<span class="' . $this->_type . '_name">' . $plugin->$plugin_name . '</span>' . $description;
	return $pluginName;
    }

    protected function getPluginHtml($plugin, $selectedPlugin, $pluginSalesPrice) {
	$pluginmethod_id = $this->_idName;
	$pluginName = $this->_psType . '_name';
	if ($selectedPlugin == $plugin->$pluginmethod_id) {
	    $checked = 'checked="checked"';
	} else {
	    $checked = '';
	}

	if (!class_exists('CurrencyDisplay'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
	$currency = CurrencyDisplay::getInstance();
	$costDisplay = "";
	if ($pluginSalesPrice) {
	    $costDisplay = $currency->priceDisplay($pluginSalesPrice);
	    $costDisplay = '<span class="' . $this->_type . '_cost"> (' . JText::_('COM_VIRTUEMART_PLUGIN_COST_DISPLAY') . $costDisplay . ")</span>";
	}

	$html = '<input type="radio" name="' . $pluginmethod_id . '" id="' . $this->_psType . '_id_' . $plugin->$pluginmethod_id . '"   value="' . $plugin->$pluginmethod_id . '" ' . $checked . ">\n"
		. '<label for="' . $this->_psType . '_id_' . $plugin->$pluginmethod_id . '">' . '<span class="' . $this->_type . '">' . $plugin->$pluginName . $costDisplay . "</span></label>\n";


	return $html;
    }

    /*
     *
     */

    protected function getHtmlHeaderBE() {
	$class = "class='key'";
	$html = ' 	<thead>' . "\n"
		. '		<tr>' . "\n"
		. '			<th ' . $class . ' style="text-align: center;" colspan="2">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_' . $this->_psType . '_LBL') . '</th>' . "\n"
		. '		</tr>' . "\n"
		. '	</thead>' . "\n";

	return $html;
    }

    /*
     *
     */

    protected function getHtmlRow($key, $value, $class = '') {
	$lang = JFactory::getLanguage();
	$key_text = '';
	$complete_key = strtoupper($this->_type . '_' . $key);
	// vmdebug('getHtmlRow',$key,$complete_key);
	//if ($lang->hasKey($complete_key)) {
	$key_text = JText::_($complete_key);
	//}
	$more_key = $complete_key . '_' . $value;
	if ($lang->hasKey($more_key)) {
	    $value .=" (" . JText::_($more_key) . ")";
	}
	$html = "<tr>\n<td " . $class . ">" . $key_text . "</td>\n <td align='left'>" . $value . "</td>\n</tr>\n";
	return $html;
    }

    protected function getHtmlRowBE($key, $value) {
	return $this->getHtmlRow($key, $value, "class='key'");
    }

    /*
     * getSelectable
     * This method returns the number of valid methods
     * @param VirtueMartCart cart: the cart object
     * @param $method_id eg $virtuemart_shipmentmethod_id
     *
     */

    function getSelectable(VirtueMartCart $cart, &$method_id, $cart_prices) {
	$nbMethod = 0;

	if ($this->getPluginMethods($cart->vendorId) === 0) {
	    return false;
	}

	foreach ($this->methods as $method) {
	    if ($nb = (int) $this->checkConditions($cart, $method, $cart_prices)) {
		$nbMethod = $nbMethod + $nb;
		$idName = $this->_idName;
		$method_id = $method->$idName;
	    }
	}
	return $nbMethod;
    }

    /**
     *
     * Enter description here ...
     * @author Valerie Isaksen
     * @author Max Milbers
     * @param VirtueMartCart $cart
     * @param int $method
     * @param array $cart_prices
     */
    protected function checkConditions($cart, $method, $cart_prices) {
	return false;
    }

    function getCosts(VirtueMartCart $cart, $method, $cart_prices) {
	return 0;
    }

    function getPaymentCurrency(&$method, $getCurrency = false) {
	if (!isset($method->payment_currency) or empty($method->payment_currency) or !$method->payment_currency or $getCurrency) {
	    // 	    if (!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
	    $vendorId = 1; //VirtueMartModelVendor::getLoggedVendor();
	    $db = JFactory::getDBO();

	    $q = 'SELECT   `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id`=' . $vendorId;
	    $db->setQuery($q);
	    $method->payment_currency = $db->loadResult();
	}
    }

    /*
     * displayTaxRule
     * @param int $tax_id
     * @return string $html:
     */

    function displayTaxRule($tax_id) {
	$html = '';
	$db = JFactory::getDBO();
	if (!empty($tax_id)) {
	    $q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $tax_id . '" ';
	    $db->setQuery($q);
	    $taxrule = $db->loadObject();

	    $html = $taxrule->calc_name . '(' . $taxrule->calc_kind . ':' . $taxrule->calc_value_mathop . $taxrule->calc_value . ')';
	}
	return $html;
    }

    /*
     * update the plugin cart_prices
     *
     * @author Valérie Isaksen
     *
     * @param $cart_prices: $cart_prices['salesPricePayment'] and $cart_prices['paymentTax'] updated. Displayed in the cart.
     * @param $value :   fee
     * @param $tax_id :  tax id
     */

    function setCartPrices(VirtueMartCart $cart, &$cart_prices, $method) {

	$value = round($this->getCosts($cart, $method, $cart_prices), 2);

	$_psType = ucfirst($this->_psType);
	$cart_prices[$this->_psType . 'Value'] = $value;

	$taxrules = array();
	if (!empty($method->tax_id)) {
	    $db = JFactory::getDBO();
	    $q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $method->tax_id . '" ';
	    $db->setQuery($q);
	    $taxrules = $db->loadAssocList();
	}
	if (!class_exists('calculationHelper'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
	$calculator = calculationHelper::getInstance();
	if (count($taxrules) > 0) {
	    $cart_prices['salesPrice' . $_psType] = $calculator->roundInternal($calculator->executeCalculation($taxrules, $cart_prices[$this->_psType . 'Value']));
	    $cart_prices[$this->_psType . 'Tax'] = $calculator->roundInternal($cart_prices['salesPrice' . $_psType]) - $cart_prices[$this->_psType . 'Value'];
	} else {
	    $cart_prices['salesPrice' . $_psType] = $value;
	    $cart_prices[$this->_psType . 'Tax'] = 0;
	}
    }

    /*
     * calculateSalesPrice
     * @param $value
     * @param $tax_id: tax id
     * @return $salesPrice
     */

    protected function calculateSalesPrice($cart, $method, $cart_prices) {

	$value = $this->getCosts($cart, $method, $cart_prices);

	$tax_id = @$method->tax_id;

	if (!class_exists('calculationHelper'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
	if (!class_exists('CurrencyDisplay'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');

	if (!class_exists('VirtueMartModelVendor'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
	$vendor_id = 1;
	$vendor_currency = VirtueMartModelVendor::getVendorCurrency($vendor_id);

	$db = JFactory::getDBO();
	$calculator = calculationHelper::getInstance();
	$currency = CurrencyDisplay::getInstance();

	$value = $currency->convertCurrencyTo($vendor_currency->virtuemart_currency_id, $value);

	$taxrules = array();
	if (!empty($tax_id)) {
	    $q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $tax_id . '" ';
	    $db->setQuery($q);
	    $taxrules = $db->loadAssocList();
	}

	if (count($taxrules) > 0) {
	    $salesPrice = $calculator->roundInternal($calculator->executeCalculation($taxrules, $value));
	} else {
	    $salesPrice = $value;
	}

	return $salesPrice;
    }

    /**
     * logPaymentInfo
     * to help debugging Payment notification for example
     */
    protected function logInfo($text, $type = 'message') {

	if ($this->_debug) {
	    $file = JPATH_ROOT . "/logs/" . $this->_name . ".log";
	    $date = JFactory::getDate();

	    $fp = fopen($file, 'a');
	    fwrite($fp, "\n\n" . $date->toFormat('%Y-%m-%d %H:%M:%S'));
	    fwrite($fp, "\n" . $type . ': ' . $text);
	    fclose($fp);
	}
    }

    public function processConfirmedOrderPaymentResponse($returnValue, $cart, $order, $html, $payment_name, $new_status = '') {

	if ($returnValue == 1) {
	    //We delete the old stuff
	    // send the email only if payment has been accepted
	    // update status

	    $modelOrder = VmModel::getModel('orders');
	    $order['order_status'] = $new_status;
	    $order['customer_notified'] = 1;
	    $order['comments'] = '';
	    $modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order, true);

	    $order['paymentName'] = $payment_name;
// 					if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
// 					shopFunctionsF::sentOrderConfirmedEmail($order);
	    //We delete the old stuff
	    $cart->emptyCart();
	    JRequest::setVar('html', $html);
	    // payment echos form, but cart should not be emptied, data is valid
	} elseif ($returnValue == 2) {
	    $cart->_confirmDone = false;
	    $cart->_dataValidated = false;
	    $cart->setCartIntoSession();
	    JRequest::setVar('html', $html);
	} elseif ($returnValue == 0) {
	    // error while processing the payment
	    $mainframe = JFactory::getApplication();
	    $mainframe->enqueueMessage($html);
	    $mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'), JText::_('COM_VIRTUEMART_CART_ORDERDONE_DATA_NOT_VALID'));
	}
    }

    function emptyCart($session_id) {
	if (!class_exists('VirtueMartCart'))
	    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
	$this->logInfo('Notification: emptyCart ' . $session_id, 'message');
	if ($session_id != null) {
	    $session = JFactory::getSession();
	    $session->close();

	    // Recover session in wich the payment is done
	    session_id($session_id);
	    session_start();
	}

	$cart = VirtueMartCart::getCart();
	$cart->emptyCart();
	return true;
    }

    /**
     * get_passkey
     * Retrieve the payment method-specific encryption key
     *
     * @author Oscar van Eijk
     * @author Valerie Isaksen
     * @return mixed
     * @deprecated
     */
    function get_passkey() {
	return true;
	$_db = JFactory::getDBO();
	$_q = 'SELECT ' . VM_DECRYPT_FUNCTION . "(secret_key, '" . ENCODE_KEY . "') as passkey "
		. 'FROM #__virtuemart_paymentmethods '
		. "WHERE virtuemart_paymentmethod_id='" . (int) $this->_virtuemart_paymentmethod_id . "'";
	$_db->setQuery($_q);
	$_r = $_db->loadAssoc(); // TODO Error check
	return $_r['passkey'];
    }

    /**
     * validateVendor
     * Check if this plugin has methods for the current vendor.
     * @author Oscar van Eijk
     * @param integer $_vendorId The vendor ID taken from the cart.
     * @return True when a  id was found for this vendor, false otherwise
     *
     * @deprecated ????
     */
    protected function validateVendor($_vendorId) {

	if (!$_vendorId) {
	    $_vendorId = 1;
	}

	$_db = JFactory::getDBO();

	if (JVM_VERSION === 1) {
	    $_q = 'SELECT 1 '
		    . 'FROM   #__virtuemart_' . $this->_psType . 'methods v '
		    . ',      #__plugins             j '
		    . 'WHERE j.`element` = "' . $this->_name . '" '
		    . 'AND   v.`' . $this->_psType . '_jplugin_id` = j.`id` '
		    . 'AND   v.`virtuemart_vendor_id` = "' . $_vendorId . '" '
		    . 'AND   v.`published` = 1 '
	    ;
	} else {
	    $_q = 'SELECT 1 '
		    . 'FROM   #__virtuemart_' . $this->_psType . 'methods AS v '
		    . ',      #__extensions   AS     j '
		    . 'WHERE j.`folder` = "' . $this->_type . '" '
		    . 'AND j.`element` = "' . $this->_name . '" '
		    . 'AND   v.`' . $this->_psType . '_jplugin_id` = j.`extension_id` '
		    . 'AND   v.`virtuemart_vendor_id` = "' . $_vendorId . '" '
		    . 'AND   v.`published` = 1 '
	    ;
	}

	$_db->setQuery($_q);
	$_r = $_db->loadAssoc();

	if ($_r) {
	    return true;
	} else {
	    return false;
	}
    }

    function handlePaymentUserCancel($virtuemart_order_id) {

	if ($virtuemart_order_id) {
	    // set the order to cancel , to handle the stock correctly
	    if (!class_exists('VirtueMartModelOrders'))
		require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

	    $modelOrder = VmModel::getModel('orders');
	    $order['order_status'] = 'X';
	    $order['virtuemart_order_id'] = $virtuemart_order_id;
	    $order['customer_notified'] = 0;
	    $order['comments'] = '';
	    $modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, true);
	    $modelOrder->remove(array('virtuemart_order_id' => $virtuemart_order_id));
	}
    }

}
