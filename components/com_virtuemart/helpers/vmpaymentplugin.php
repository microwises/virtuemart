<?php

/**
 * abstract class for payment plugins
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Max Milbers
 * @author Oscar van Eijk
 * @author Valérie Cartan Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */
// Load the helper functions that are needed by all plugins
if (!class_exists('ShopFunctions'))
require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
if (!class_exists('DbScheme'))
require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'dbscheme.php');

// Get the plugin library
jimport('joomla.plugin.plugin');

abstract class vmPaymentPlugin extends JPlugin {

	private $_virtuemart_paymentmethod_id = 0;
	private $_payment_name = '';

	/** var Must be overriden in every plugin file by adding this code to the constructor: $this->_pelement = basename(__FILE, '.php'); */
	var $_pelement = '';
	var $_tablename = '';

	/**
	 * @var array List with all carriers the have been implemented with the plugin in the format
	 * id => name
	 */
	protected $payments;

	/**
	 * Constructor
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function __construct(& $subject, $config) {
		$lang = JFactory::getLanguage();
		$filename = 'plg_vmpayment_' . $this->_pelement;
		$lang->load($filename, JPATH_ADMINISTRATOR);
		if (!class_exists('JParameter'))
		require(JPATH_VM_LIBRARIES . DS . 'joomla' . DS . 'html' . DS . 'parameter.php' );
		parent::__construct($subject, $config);
	}

	/**
	 * Method to create te plugin specific table; must be reimplemented.
	 * @example
	 * 	$_scheme = DbScheme::get_instance();
	 * 	$_scheme->create_scheme('#__vm_order_payment_'.$this->_pelement);
	 * 	$_schemeCols = array(
	 * 		 'id' => array (
	 * 				 'type' => 'int'
	 * 				,'length' => 11
	 * 				,'auto_inc' => true
	 * 				,'null' => false
	 * 		)
	 * 		,'virtuemart_order_id' => array (
	 * 				 'type' => 'int'
	 * 				,'length' => 11
	 * 				,'null' => false
	 * 		)
	 * 		,'order_number' => array (
	 * 				 'type' => 'varchar'
	 * 				,'length' => 32
	 * 				,'null' => false
	 * 		)
	 * 		,'payment_method_id' => array (
	 * 				 'type' => 'text'
	 * 				,'null' => false
	 * 		)
	 * 	);
	 * 	$_schemeIdx = array(
	 * 		 'idx_order_payment' => array(
	 * 				 'columns' => array ('virtuemart_order_id')
	 * 				,'primary' => false
	 * 				,'unique' => false
	 * 				,'type' => null
	 * 		)
	 * 	);
	 * 	$_scheme->define_scheme($_schemeCols);
	 * 	$_scheme->define_index($_schemeIdx);
	 * 	if (!$_scheme->scheme()) {
	 * 		JError::raiseWarning(500, $_scheme->get_db_error());
	 * 	}
	 * 	$_scheme->reset();
	 * @author Oscar van Eijk
	 */
	abstract protected function _createTable();

	/**
	 * This functions gets the used and configured payment method
	 * pelement of this class determines the used jplugin.
	 * The right payment method is determined by the vendor and the jplugin id.
	 *
	 * This function sets the used payment plugin as variable of this class
	 * @author Max Milbers
	 *
	 */
	protected function setVmPaymentParams($vendorId=0, $jplugin_id=0) {
		/*
	  if (!$vendorId)
		$vendorId = 1;
		$db = JFactory::getDBO();
		if (!$jplugin_id) {
		if (VmConfig::isJ15()) {
		$q = 'SELECT `id` FROM #__plugins WHERE `element` = "' . $this->_pelement . '"';
		} else {
		$q = 'SELECT `extension_id` FROM #__extensions  WHERE `element` = "' . $this->_pelement . '"';
		}
		$db->setQuery($q);
		$this->_jplugin_id = $db->loadResult();
		if (!$this->_jplugin_id) {
		$mainframe = &JFactory::getApplication();
		$mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_NO_PAYMENT_PLUGIN'));
		return false;
		}
		} else {
		$this->_jplugin_id = $jplugin_id;
		}

		$q = 'SELECT `virtuemart_paymentmethod_id`,`payment_name` FROM #__virtuemart_paymentmethods WHERE `payment_jplugin_id` = "' . $this->_jplugin_id . '" AND `virtuemart_vendor_id` = "' . $vendorId . '" AND `published`="1" ';
		$db->setQuery($q);
		$result = $db->loadAssoc();

		if (!empty($result)) {
		if (!class_exists('VirtueMartModelPaymentmethod'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'paymentmethod.php');

		if (!class_exists('vmParameters'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'parameterparser.php');
		$this->paymentModel = new VirtueMartModelPaymentmethod();
		$this->paymentModel->setId($result['virtuemart_paymentmethod_id']);
		$this->paymentMethod = $this->paymentModel->getPaym();
		$this->params->_raw = $this->paymentMethod->payment_params; // valerie

		return true;
		} else {
		//			$mainframe = &JFactory::getApplication();
		//			$mainframe->enqueueMessage( 'The Paymentmethod '.$this->_payment_name.' with element '.$this->_pelement.' didnt found used and published payment plugin by vendor','error' );
		return false;
		}
	 * *
	 *
	 */
	}

	/**
	 * plgVmOnSelectPayment
	 * This event is fired during the checkout process. It allows the shopper to select
	 * one of the available payment methods.
	 * It should display a radio button (name: virtuemart_paymentmethod_id) to select the payment method. Other
	 * information (like credit card info) might be selected as well.
	 *
	 * @param object $cart The cart object
	 * @param integer $checkedPaymId ID of an already selected payment method ID, if any
	 * @author Max Milbers
	 */
	public function plgVmOnSelectPayment(VirtueMartCart $cart, $selectedPayment=0) {

		if ($this->getPaymentMethods($cart->vendorId) === false) {
			if (empty($this->_name)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('COM_VIRTUEMART_CART_NO_PAYMENT'));
				return;
			} else {
				return;
			}
		}
		$html = array();
		foreach ($this->payments as $payment) {
			if ($this->checkPaymentConditions($cart->pricesUnformatted, $payment)) {
				//vmdebug('plgVmOnSelectPayment', $payment->payment_name, $payment->payment_params);
				$params = new JParameter($payment->payment_params);
				$paymentSalesPrice = $this->calculateSalesPricePayment($this->getPaymentValue($params, $cart), $this->getPaymentTaxId($params, $cart));
				$payment->payment_name = $this->getPaymentName($payment);
				$html [] = $this->getPaymentHtml($payment, $selectedPayment, $paymentSalesPrice);
			}
		}

		return $html;
	}

	/**
	 * plgVmOnPaymentSelectCheck
	 * This event is fired after the payment method has been selected. It can be used to store
	 * additional payment info in the cart.
	 *
	 * @author Max Milbers
	 * @author Valérie isaksen
	 */
	public function plgVmOnPaymentSelectCheck($cart) {
		return null;
	}

	/**
	 * plgVmOnCheckoutCheckPaymentData
	 * This event is fired during the checkout process. It can be used to validate the
	 * payment data as entered by the user.
	 *
	 * @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.
	 * @author Max Milbers
	 */
	public function plgVmOnCheckoutCheckPaymentData() {
		return null;
	}

	/**
	 * plgVmOnPaymentResponseReceived
	 * This event is fired when the payment method returns to the shop after
	 * Typically,  the events may also represent authorizations, Fraud Management Filter actions and other actions,
	 * such as refunds, disputes, and chargebacks.
	 *  the payment itseff should send in the URL the parameters needed
	 * NOTE for Plugin developers:
	 *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
	 *
	 * @param int $pelement code to the constructor
	 * @param int $virtuemart_paymentmethod_id
	 * @param $return_context: it was given and sent in the payment form. The notification should return it back.  Used to know which cart should be emptied
	 * @param int $virtuemart_order_id : payment notification order id
	 * @param char $new_status : new_status for this order id.
	 * * @return mixed Null when this method was not selected, otherwise the true or false
	 *
	 * @author Valerie Isaksen
	 *
	 */
	function plgVmOnPaymentResponseReceived(  &$virtuemart_order_id, &$html) {
		return null;
		return false;
	}

	/**
	 * plgVmOnPaymentUserCancel
	 * This event is fired when  the user return to the shop without doing the transaction.
	 *
	 * NOTE for Plugin developers:
	 *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL.
	 * The order previously created is deleted.. the acrt is not emptied, so the user can change the payment, and re-order.
	 * The payment itself should decide which parameter is necessary
	 * @param int $pelement code to the constructor
	 * @param int $virtuemart_paymentmethod_id
	 * @param int $virtuemart_order_id : payment notification order id
	 * * @return mixed Null when this method was not selected, otherwise the true or false
	 *
	 * @author Valerie Isaksen
	 *
	 */
	function plgVmOnPaymentUserCancel(  &$virtuemart_order_id) {
		if ($this->_pelement != $pelement) {
			return null;
		}
		return false;
	}

	/**
	 * plgVmOnPaymentNotification
	 * This event is fired when the payment method notifies you when an event occurs that affects a transaction.
	 * Typically,  the events may also represent authorizations, Fraud Management Filter actions and other actions,
	 * such as refunds, disputes, and chargebacks.
	 *
	 * NOTE for Plugin developers:
	 *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
	 *
	 * @param int $pelement
	 * @param int $virtuemart_paymentmethod_id
	 * @param $return_context: it was given and sent in the payment form. The notification should return it back.
	 * Used to know which cart should be emptied, in case it is still in the session.
	 * @param int $virtuemart_order_id : payment  order id
	 * @param char $new_status : new_status for this order id.
	 * * @return mixed Null when this method was not selected, otherwise the true or false
	 *
	 * @author Valerie Isaksen
	 *
	 */
	function plgVmOnPaymentNotification(  &$return_context, &$virtuemart_order_id, &$new_status) {
		if ($this->_pelement != $pelement) {
			return null;
		}
		return false;
	}

	/**
	 * plgVmOnConfirmedOrderStorePaymentData
	 * This event is fired after the payment has been processed; it stores the payment method-
	 * specific data.
	 * All plugins *must* reimplement this method.
	 * NOTE for Plugin developers:
	 *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
	 *  If this plugin IS executed, it MUST return the order status code that the order should get. This triggers the stock updates if required
	 *
	 * @param int $_orderNr The ordernumber being processed
	 * @param object $_orderData Data from the cart
	 * @param array $_priceData Price information for this order
	 * @return mixed Null when this method was not selected, otherwise the new order status
	 * @author Max Milbers
	 * @author Oscar van Eijk
	 */
	abstract function plgVmOnConfirmedOrderStorePaymentData($_orderNr, $_orderData, $_priceData);

	/**
	 * plgVmOnConfirmedOrderGetPaymentForm
	 * This event is fired after the order has been created
	 * All plugins *must* reimplement this method.
	 * NOTE for Plugin developers:
	 *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
	 * returns 1 if the Cart should be deleted, and order sent
	 * $order_number: the actual order number
	 * $orderDate
	 * $return_context: contains the session id. Should be sent to the form. And the payment will sent it back.
	 *                  Will be used to empty the cart if necessary, and semnd the order email.
	 * $html: the payment form to display. But in some case, the bank can be called directly.
	 * @author Valérie Isaksen
	 */
	abstract function plgVmOnConfirmedOrderGetPaymentForm($order_number, $orderData, $return_context, &$html);

	/**
	 * plgVmOnShowOrderPaymentFE
	 * This method is fired when showing the order details in the frontend.
	 * It displays the the payment method-specific data.
	 * All plugins *must* reimplement this method.
	 *
	 * @param integer $virtuemart_order_id The order ID
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 * @author Max Milbers
	 * @author Oscar van Eijk
	 */
	function plgVmOnShowOrderPaymentFE($virtuemart_order_id) {
		if ($this->_tablename) {
			$db = JFactory::getDBO();
			$q = 'SELECT * FROM `' . $this->_tablename . '` '
			. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
			$db->setQuery($q);
			if (!($paymentinfo = $db->loadObject())) {
				return '';
			}
		} else {
			return null;
		}

		if (!$this->selectedThisPayment($this->_pelement, $paymentinfo->payment_method_id)) {
			return null; // Another method was selected, do nothing
		}
		return $this->getThisPaymentName($paymentinfo->payment_method_id);
	}

	/**
	 * plgVmOnShowOrderPaymentBE
	 * This method is fired when showing the order details in the backend.
	 * It displays the the payment method-specific data.
	 * All plugins *must* reimplement this method.
	 *
	 * @param integer $_virtuemart_order_id The order ID
	 * @param integer $_paymethod_id Payment method used for this order
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 * @author Max Milbers
	 * @author Oscar van Eijk
	 */
	abstract function plgVmOnShowOrderPaymentBE($_virtuemart_order_id, $_paymethod_id);

	/**
	 * This event is fired each time the status of an order is changed to Cancelled.
	 * It can be used to refund payments, void authorization etc.
	 * Return values are ignored.
	 *
	 * Note for plugin developers: you are not required to reimplement this method, but if you
	 * do so, it MUST start with this code:
	 *
	 * 	$_paymethodID = $this->getPaymentMethodForOrder($_orderID);
	 * 	if (!$this->selectedThisMethod($this->_pelement, $_paymethodID)) {
	 * 		return;
	 * 	}
	 *
	 * @author Oscar van Eijk
	 * @param int $_orderID
	 * @param char $_oldStat Previous order status
	 * @param char $_newStat New order status
	 */
	/*
	 function plgVmOnCancelPayment($_orderID, $_oldStat, $_newStat) {
	return;
	}
	*/

	/**
	 * plgVmOnShipOrderPayment
	 * This event is fired when the status of an order is changed to Shipped.
	 * It can be used to confirm or capture payments
	 *
	 * Note for plugin developers: you are not required to reimplement this method, but if you
	 * do so, it MUST start with this code:
	 *
	 * 	$_paymethodID = $this->getPaymentMethodForOrder($_orderID);
	 * 	if (!$this->selectedThisMethod($this->_pelement, $_paymethodID)) {
	 * 		return null;
	 * 	}
	 *
	 * @author Oscar van Eijk
	 * @param int $_orderID Order ID
	 * @return mixed True on success, False on failure, Null if this plugin was not activated
	 */
	public function plgVmOnShipOrderPayment($_orderID) {
		return null;
	}

	/**
	 * getPaymentMethodForOrder
	 * Get the order payment ID for a given order number
	 * @access protected
	 * @author Oscar van Eijk
	 * @param int $_id The order ID
	 * @return int The payment method ID, or -1 when not found
	 */
	protected function getPaymentMethodForOrder($_id) {
		$_db = JFactory::getDBO();
		$_q = 'SELECT `payment_method_id` FROM #__virtuemart_orders WHERE virtuemart_order_id = ' . (int) $_id;
		$_db->setQuery($_q);
		if (!($_r = $_db->loadAssoc())) {
			return -1;
		}
		return $_r['payment_method_id'];
	}

	/**
	 * get_passkey
	 * Retrieve the payment method-specific encryption key
	 *
	 * @author Oscar van Eijk
	 * @return mixed
	 */
	function get_passkey() {
		$_db = JFactory::getDBO();
		$_q = 'SELECT ' . VM_DECRYPT_FUNCTION . "(secret_key, '" . ENCODE_KEY . "') as passkey "
		. 'FROM #__virtuemart_paymentmethods '
		. "WHERE virtuemart_paymentmethod_id='" . (int) $this->_virtuemart_paymentmethod_id . "'";
		$_db->setQuery($_q);
		$_r = $_db->loadAssoc(); // TODO Error check
		return $_r['passkey'];
	}

	/**
	 * selectedThisPayment
	 * This method checks if the selected payment method matches the current plugin
	 * @param string $_pelement Element name, taken from the plugin filename
	 * @param int $_pid The payment method ID
	 * @author Oscar van Eijk
	 * @return True if the calling plugin has the given payment ID
	 */
	final protected function selectedThisPayment($pelement, $pid) {
		$db = JFactory::getDBO();

		if (VmConfig::isJ15()) {
			$q = 'SELECT count(*) AS c
            		FROM #__virtuemart_paymentmethods AS vm , #__plugins AS j
            		WHERE vm.virtuemart_paymentmethod_id="' . (int) $pid . '"
            		AND   vm.payment_jplugin_id = j.id
					AND   j.element = "' . $db->getEscaped($pelement) . '"';
		} else {
			$q = 'SELECT count(*) AS c
            		FROM #__virtuemart_paymentmethods AS vm
            		, #__extensions AS j
            		WHERE vm.virtuemart_paymentmethod_id="' . (int) $pid . '"
            		AND   vm.payment_jplugin_id = j.extension_id
            		AND   j.element = "' . $db->getEscaped($pelement) . '"';
		}

		$db->setQuery($q);
		return $db->loadResult(); // TODO Error check
	}

	/**
	 * getPaymentMethods
	 * Fill the array with all carriers found with this plugin for the current vendor
	 * @return True when carrier(s) was (were) found for this vendor, false otherwise
	 * @author Oscar van Eijk
	 */
	protected function getPaymentMethods($vendorId) {

		if (!class_exists('VirtueMartModelUser'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'user.php');

		$usermodel = new VirtueMartModelUser();
		$user = $usermodel->getUser();
		$user->shopper_groups = (array) $user->shopper_groups;

		if (VmConfig::isJ15()) {
			$extPlgTable = '#__plugins';
			$extField1 = 'id';
			$extField2 = 'element';
		} else {
			$extPlgTable = '#__extensions';
			$extField1 = 'extension_id';
			$extField2 = 'element';
		}

		$db = JFactory::getDBO();

		$select = 'SELECT v.*,j.*,s.virtuemart_shoppergroup_id ';

		$q = $select . ' FROM   #__virtuemart_paymentmethods AS v ';

		$q.= ' LEFT JOIN ' . $extPlgTable . ' as j ON j.`' . $extField1 . '` =  v.`payment_jplugin_id` ';
		$q.= ' LEFT OUTER JOIN #__virtuemart_paymentmethod_shoppergroups AS s ON v.`virtuemart_paymentmethod_id` = s.`virtuemart_paymentmethod_id` ';
		$q.= ' WHERE v.`published` = "1" AND j.`' . $extField2 . '` = "' . $this->_pelement . '"
    						AND  (v.`virtuemart_vendor_id` = "' . $vendorId . '" OR   v.`virtuemart_vendor_id` = "0")
    						AND  (';

		foreach ($user->shopper_groups as $groups) {
			$q .= 's.`virtuemart_shoppergroup_id`= "' . (int) $groups . '" OR';
		}
		$q .= ' ISNULL(s.`virtuemart_shoppergroup_id`) ) ORDER BY v.`ordering`';

		$db->setQuery($q);
		if (!$results = $db->loadObjectList()) {
			return false;
		}
		$this->payments = $results;
		return true;
	}

	/**
	 * Get the name of the payment method
	 * @param int $_pid The payment method ID
	 * @author Oscar van Eijk
	 * @author Valérie Isaken
	 * @return string Payment method name
	 */
	public function plgVmGetThisPaymentName(TablePaymentmethods $payment) {
		if (!$this->selectedThisPayment($this->_pelement, $payment->virtuemart_paymentmethod_id)) {
			return null; // Another paymen was selected, do nothing
		}
		return $payment->payment_name;
	}

	/**
	 * This functions gets the used and configured payment method
	 * pelement of this class determines the used jplugin.
	 * The right payment method is determined by the vendor and the jplugin id.
	 *
	 * This function sets the used payment plugin as variable of this class
	 * @author Max Milbers
	 *
	 */
	protected function getVmPaymentParams($vendorId=0, $payment_id=0) {

		if (!$vendorId)
		$vendorId = 1;
		$db = JFactory::getDBO();

		$q = 'SELECT `payment_params` FROM #__virtuemart_paymentmethods
        		WHERE `virtuemart_paymentmethod_id`="' . $payment_id . '" ';
		$db->setQuery($q);
		return $db->loadResult();
	}

	/**
	 * This method writes all payment plugin specific data to the plugin's table
	 *
	 * @param array $_values Indexed array in the format 'column_name' => 'value'
	 * @param string $_table Table name
	 * @author Oscar van Eijk
	 */
	protected function writePaymentData($_values, $_table) {
		if (count($_values) == 0) {
			JError::raiseWarning(500, 'writePaymentData got no data to save to ' . $_table);
			return;
		}
		if (!class_exists('VirtueMartModelOrders'))
		require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
		if (!isset($_values['virtuemart_order_id'])) {
			$_values['virtuemart_order_id'] = VirtueMartModelOrders::getOrderIdByOrderNumber($_values['order_number']);
		}
		$_cols = array();
		$_vals = array();
		foreach ($_values as $_col => $_val) {
			$_cols[] = "`$_col`";
			$_vals[] = "'$_val'";
		}
		$_db = JFactory::getDBO();
		$_q = 'INSERT INTO `' . $_table . '` ('
		. implode(',', $_cols)
		. ') VALUES ('
		. implode(',', $_vals)
		. ')';
		$_db->setQuery($_q);
		if (!$_db->query()) {
			JError::raiseWarning(500, $_db->getErrorMsg());
		}
	}

	/**
	 * This method updates all payment plugin specific data to the plugin's table
	 *
	 * @param array $_values Indexed array in the format 'column_name' => 'value'
	 * @param string $_table Table name
	 * @author Valerie Isaksen
	 *
	 */
	protected function updatePaymentData($values, $table, $where_key, $where_value) {
		if (count($values) == 0) {
			JError::raiseWarning(500, 'updatePaymentData got no data to update to ' . $table);
			return;
		}
		$cols = array();
		$vals = array();
		foreach ($values as $col => $val) {
			$fields[] = "`$col`" . "=" . "'$val'";
		}
		$db = JFactory::getDBO();
		$q = 'UPDATE `' . $table . '` SET ';
		foreach ($values as $key => $value) {
			$q .= $db->getEscaped($key) . '="' . $value . '",';
		}
		$q = substr($q, 0, strlen($q) - 1);
		$q .= ' WHERE `' . $where_key . '` =' . $where_value;


		$db->setQuery($q);
		if (!$db->query()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}
	}

	protected function getPaymentHtml($payment, $selectedPayment, $paymentSalesPrice) {

		if ($selectedPayment == $payment->virtuemart_paymentmethod_id) {
			$checked = 'checked';
		} else {
			$checked = '';
		}

		if (!class_exists('CurrencyDisplay'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		$currency = CurrencyDisplay::getInstance();

		$paymentCostDisplay = $currency->priceDisplay($paymentSalesPrice);
		$html = '<input type="radio" name="virtuemart_paymentmethod_id" id="payment_id_' . $payment->virtuemart_paymentmethod_id . '" value="' . $payment->virtuemart_paymentmethod_id . '" ' . $checked . '>'
		. '<label for="payment_id_' . $payment->virtuemart_paymentmethod_id . '">' . $payment->payment_name . " (" . $paymentCostDisplay . ")</label>\n";
		$html .="\n";
		return $html;
	}

	/**
	 * Get the name of the payment method
	 * @param int $_pid The payment method ID
	 * @author Oscar van Eijk
	 * @return string Payment method name
	 */
	function getThisPaymentName($payment_id) {

		$db = JFactory::getDBO();

		$q = 'SELECT `payment_name` FROM #__virtuemart_paymentmethods WHERE `virtuemart_paymentmethod_id`="' . (int) $payment_id . '"';
		$db->setQuery($q);
		return $db->loadResult(); // TODO Error check
	}

	/**
	 * Get Shipper Data for a go given Payment ID
	 * @param int $virtuemart_payment_id The Payment ID
	 * @author Valérie Isaksen
	 * @return  Shipper data
	 */
	final protected function getThisPaymentData($virtuemart_payment_id) {
		$db = JFactory::getDBO();
		$q = 'SELECT * '
		. 'FROM #__virtuemart_paymentmethods '
		. "WHERE virtuemart_paymentmethod_id ='" . $virtuemart_payment_id . "' ";
		$db->setQuery($q);
		$result = $db->loadObject(); // TODO Error check
		return $result;
	}

	/**
	 * Get Shipper Data for a go given Payment ID
	 * @param int $virtuemart_payment_id The Payment ID
	 * @author Valérie Isaksen
	 * @return  Shipper data
	 */
	final protected function getPaymentDataByOrderId($virtuemart_order_id  ) {
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;

		$db->setQuery($q);
		$payment = $db->loadObject();

		return $payment;
	}

	function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices) {

		$nbPayment = 0;
		$virtuemart_paymentmethod_id = 0;
		$nbPayment = $this->getSelectablePayment($cart, $cart_prices, $virtuemart_paymentmethod_id);
		if ($nbPayment == null)
		return null;
		return ($nbPayment == 1) ? $virtuemart_paymentmethod_id : 0;
	}

	public function plgVmOnPaymentSelectedCalculatePrice(VirtueMartCart $cart, array &$cart_prices, $payment_name) {
		if (!$this->selectedThisPayment($this->_pelement, $cart->virtuemart_paymentmethod_id)) {
			return null; // Another payment was selected, do nothing
		}
		if (!is_array($this->payments))
		return null;
		foreach ($this->payments as $payment) {
			if ($payment->virtuemart_paymentmethod_id == $cart->virtuemart_paymentmethod_id) {
				break;
			}
		}

		$payment_name = '';
		$cart_prices['payment_tax_id'] = 0;
		$cart_prices['payment_value'] = 0;

		if (!$this->CheckPaymentIsValid($cart, $cart_prices))
		return false;
		$params = new JParameter($payment->payment_params);
		$payment_name = $this->getPaymentName($payment);
		$payment_value = $this->getPaymentValue($params, $cart_prices);
		$payment_tax_id = $this->getPaymentTaxId($params);

		$this->setCartPrices($cart_prices, $payment_value, $payment_tax_id);

		return true;
	}

	/*
	 * This method returns the number of payment methods valid
	*/

	function getSelectablePayment(VirtueMartCart $cart, $cart_prices, &$virtuemart_paymentmethod_id) {
		$nbPayments = 0;
		if ($this->getPaymentMethods($cart->vendorId) === false) {
			return false;
		}

		foreach ($this->payments as $payment) {
			if ($this->checkPaymentConditions($cart_prices, $payment)) {
				$nbPayments++;
				$virtuemart_paymentmethod_id = (int) $payment->virtuemart_paymentmethod_id;
			}
		}

		return $nbPayments;
	}

	function checkPaymentConditions($cart_prices, $payment) {

		$params = new JParameter($payment->payment_params);
		$amount = $cart_prices['salesPrice'];
		$amount_cond = ($amount >= $params->get('min_amount', 0) AND $amount <= $params->get('max_amount', 0)
		OR
		($params->get('min_amount', 0) <= $amount AND ($params->get('max_amount', '') == '') ));

		return $amount_cond;
	}

	function getPaymentValue($params) {
		return 0;
	}

	function getPaymentTaxId($params) {
		return -1;
	}

	function getPaymentCost($params, $cart) {
		return 0;
	}

	protected function calculateSalesPricePayment($payment_value, $tax_id) {

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

		$payment_value = $currency->convertCurrencyTo($vendor_currency->virtuemart_currency_id, $payment_value);

		$taxrules = array();
		if (!empty($tax_id)) {
			$q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $tax_id . '" ';
			$db->setQuery($q);
			$taxrules = $db->loadAssocList();
		}

		if (count($taxrules) > 0) {
			$salesPricePayment = $calculator->roundDisplay($calculator->executeCalculation($taxrules, $payment_value));
		} else {
			$salesPricePayment = $payment_value;
		}

		return $salesPricePayment;
	}

// 	/**
// 	 * Get the name of the payment method
// 	 * @param TablePaymentmethods $payment
// 	 * @return string Payment method name
// 	 * @author Valerie Isaksen
// 	 */
// 	function getPaymentName($payment) {

// 		$params = new JParameter($payment->payment_params);
// 		return $payment->payment_name;
// 	}

	/**
	* Get the name of the payment method
	*
	* @author Valerie Isaksen
	* @param TablePaymentmethods $payment
	* @return string Payment method name
	*/
	function getPaymentName($payment) {

		$return = '';
		$params = new JParameter($payment->payment_params);
		if(!empty($params->get('payment_logos'))){
			$return = $this->displayLogos(array($params->get('payment_logos')=> $payment->payment_name)).' ';
		}

		return $return . $payment->payment_name;
	}

	function checkPaymentIsValid(VirtueMartCart $cart, array $cart_prices) {
		$payment = $this->getThisPaymentData($cart->virtuemart_paymentmethod_id);
		return $this->checkPaymentConditions($cart_prices, $payment);
	}

	/*
	 * setCartPrices
	*
	* @author Valérie Isaksen
	*/

	function setCartPrices(&$cart_prices, $payment_value, $payment_tax_id) {

		$cart_prices['paymentValue'] = $payment_value;

		$taxrules = array();
		if (!empty($payment_tax_id)) {
			$db = JFactory::getDBO();
			$q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $payment_tax_id . '" ';
			$db->setQuery($q);
			$taxrules = $db->loadAssocList();
		}
		if (!class_exists('calculationHelper'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
		$calculator = calculationHelper::getInstance();
		if (count($taxrules) > 0) {
			$cart_prices['salesPricePayment'] = $calculator->roundDisplay($calculator->executeCalculation($taxrules, $cart_prices['paymentValue']));
			$cart_prices['paymentTax'] = $calculator->roundDisplay($cart_prices['salesPricePayment']) - $cart_prices['paymentValue'];
		} else {
			$cart_prices['salesPricePayment'] = $payment_value;
			$cart_prices['paymentTax'] = 0;
		}
	}

	/**
	 * displays the logos of a payment plugin
	 *
	 * @author Valerie Isaksen
	 * @author Max Milbers
	 * @param array $logo_list
	 * return html with logos
	 */
	public function displayLogos($logo_list) {

		$img = "";

		if (!(empty($logo_list))) {
			$url = JURI::root() . 'images/stories/virtuemart/payment/';
			if(!is_array($logo_list)) $logo_list = (array) $logo_list;
			foreach($logo_list as $shipper_logo => $alt_text){
				$img .= '<img align="middle" src="' . $url . $shipper_logo . '"  alt="' . $alt_text . '" > ';
			}
		}
		return $img;
	}

}
