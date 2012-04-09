<?php

/**
 * @version $Id: standard.php,v 1.4 2005/05/27 19:33:57 ei
 *
 * a special type of 'cash on delivey':
 * @author Valérie Isaksen
 * @version $Id: authorize.php 5122 2011-12-18 22:24:49Z alatak $
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
defined('_JEXEC') or die('Restricted access');


if (!class_exists('Creditcard')) {
    require_once(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'creditcard.php');
}
if (!class_exists('vmPSPlugin'))
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');

class plgVmpaymentAuthorizenet extends vmPSPlugin {

    // instance of class
    public static $_this = false;
    private $_cc_name = '';
    private $_cc_type = '';
    private $_cc_number = '';
    private $_cc_cvv = '';
    private $_cc_expire_month = '';
    private $_cc_expire_year = '';
    private $_cc_valid = false;
    private $_errormessage = array();
    protected $_authorizenet_params = array(
	"version" => "3.1",
	"delim_char" => ",",
	"delim_data" => "TRUE",
	"relay_response" => "FALSE",
	"encap_char" => "|",
    );
    public $approved;
    public $declined;
    public $error;
    public $held;

    const APPROVED = 1;
    const DECLINED = 2;
    const ERROR = 3;
    const HELD = 4;

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
    // instance of class
    function __construct(& $subject, $config) {

	parent::__construct($subject, $config);

	$this->_loggable = true;
	$this->_tablepkey = 'id';
	$this->_tableId = 'id';
	$this->tableFields = array_keys($this->getTableSQLFields());
	$varsToPush = array(
	    'login_id' => array('', 'int'),
	    'transaction_key' => array(0, 'int'),
	    'secure_post' => array('', 'int'),
	    'sandbox' => array('', 'int'),
	    'sandbox_login_id' => array('', 'int'),
	    'sandbox_transaction_key' => array('', 'int'),
	    'creditcards' => array('', 'int'),
	    'payment_logos' => array('', 'char'),
	    'cvv_images' => array('', 'char'),
	    'debug' => array(0, 'int'),
	    'payment_approved_status' => array('C', 'char'),
	    'payment_declined_status' => array('X', 'char'),
	    'payment_held_status' => array('P', 'char'),
	    'countries' => array(0, 'char'),
	    'min_amount' => array(0, 'int'),
	    'max_amount' => array(0, 'int'),
	    'cost_per_transaction' => array(0, 'int'),
	    'cost_percent_total' => array(0, 'char'),
	    'tax_id' => array(0, 'int')
	);

	$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);
    }

    protected function getVmPluginCreateTableSQL() {
	return $this->createTableSQL('Payment AuthorizeNet Table');
    }

    function getTableSQLFields() {
	$SQLfields = array(
	    'id' => 'int(1) UNSIGNED NOT NULL AUTO_INCREMENT',
	    'virtuemart_order_id' => 'int(1) UNSIGNED',
	    'order_number' => ' char(64)',
	    'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
	    'payment_name' => 'varchar(5000)',
	    'return_context' => 'char(255)',
	    'cost_per_transaction' => 'decimal(10,2)',
	    'cost_percent_total' => 'char(10)',
	    'tax_id' => 'smallint(1)',
	    'authorizenet_response_authorization_code' => 'char(10)',
	    'authorizenet_response_transaction_id' => 'mediumint(1) UNSIGNED',
	    'authorizenet_response_authorization_code' => 'char(6)',
	    'authorizenet_response_response_code' => 'char(128)',
	    'authorizenet_response_response_subcode' => 'char(13)',
	    'authorizenet_response_response_reason_code' => 'decimal(10,2)',
	    'authorizenet_response_response_reason_text' => 'text',
	    'authorizenet_response_transaction_type' => 'char(50)',
	    'authorizenet_response_account_number' => 'char(4)',
	    'authorizenet_response_card_type' => 'char(128)',
	    'authorizenet_response_card_code_response' => 'char(5)',
	    'authorizenet_response_cavv_response' => 'char(1)',
	    'authorizeresponse_raw' => 'text'
	);
	return $SQLfields;
    }

    /**
     * This shows the plugin for choosing in the payment list of the checkout process.
     *
     * @author Valerie Cartan Isaksen
     */
    function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {
	JHTML::_('behavior.tooltip');

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

	JHTML::script('vmcreditcard.js', 'components/com_virtuemart/assets/js/', false);
	JFactory::getLanguage()->load('com_virtuemart');
	vmJsApi::jCreditCard();
	$htmla = '';
	$html = array();
	foreach ($this->methods as $method) {
	    if ($this->checkConditions($cart, $method, $cart->pricesUnformatted)) {
		$methodSalesPrice = $this->calculateSalesPrice($cart, $method, $cart->pricesUnformatted);
		$method->$method_name = $this->renderPluginName($method);
		$html = $this->getPluginHtml($method, $selected, $methodSalesPrice);
		if ($selected == $method->virtuemart_paymentmethod_id) {
		    if (!empty($authorizeSession->cc_type))
			$this->_cc_type = $authorizeSession->cc_type;
		    if (!empty($authorizeSession->cc_number))
			$this->_cc_number = $authorizeSession->cc_number;
		    if (!empty($authorizeSession->cc_cvv))
			$this->_cc_cvv = $authorizeSession->cc_cvv;
		    if (!empty($authorizeSession->cc_expire_month))
			$this->_cc_expire_month = $authorizeSession->cc_expire_month;
		    if (!empty($authorizeSession->cart_cc_expire_year))
			$this->_cc_expire_year = $authorizeSession->_cc_expire_year;
		} else {
		    $this->_cc_type = '';
		    $this->_cc_number = '';
		    $this->_cc_cvv = '';
		    $this->_cc_expire_month = '';
		    $this->_cc_expire_year = '';
		}
		$creditCards = $method->creditcards;

		$creditCardList = '';
		if ($creditCards) {
		    $creditCardList = ($this->_renderCreditCardList($creditCards, $this->_cc_type, $method->virtuemart_paymentmethod_id, false));
		}
		$sandbox_msg = "";
		if ($method->sandbox) {
		    $sandbox_msg .= '<br />' . JText::_('VMPAYMENT_AUTHORIZENET_SANDBOX_TEST_NUMBERS');
		}

		$cvv_images = $this->_displayCVVImages($method);
		$html .= '<br /><span class="vmpayment_cardinfo">' . JText::_('VMPAYMENT_AUTHORIZENET_COMPLETE_FORM') . $sandbox_msg . '
		    <table border="0" cellspacing="0" cellpadding="2" width="100%">
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="creditcardtype">' . JText::_('VMPAYMENT_AUTHORIZENET_CCTYPE') . '</label>
		        </td>
		        <td>' . $creditCardList .
			'</td>
		    </tr>

		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="cc_type">' . JText::_('VMPAYMENT_AUTHORIZENET_CCNUM') . '</label>
		        </td>
		        <td>
		        <input type="text" class="inputbox" id="cc_number_' . $method->virtuemart_paymentmethod_id . '" name="cc_number_' . $method->virtuemart_paymentmethod_id . '" value="' . $this->_cc_number . '"    autocomplete="off"   onchange="ccError=razCCerror(' . $method->virtuemart_paymentmethod_id . ');
	CheckCreditCardNumber(this . value, ' . $method->virtuemart_paymentmethod_id . ');
	if (!ccError) {
	    this.value=\'\';}" />
		        <div id="cc_cardnumber_errormsg_' . $method->virtuemart_paymentmethod_id . '"></div>
		    </td>
		    </tr>
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="cc_cvv">' . JText::_('VMPAYMENT_AUTHORIZENET_CVV2') . '</label>
		        </td>
		        <td>
		            <input type="text" class="inputbox" id="cc_cvv_' . $method->virtuemart_paymentmethod_id . '" name="cc_cvv_' . $method->virtuemart_paymentmethod_id . '" maxlength="4" size="5" value="' . $this->_cc_cvv . '" autocomplete="off" />

			<span class="hasTip" title="' . JText::_('VMPAYMENT_AUTHORIZENET_WHATISCVV') . '::' . JText::sprintf("VMPAYMENT_AUTHORIZENET_WHATISCVV_TOOLTIP", $cvv_images) . ' ">' .
			JText::_('VMPAYMENT_AUTHORIZENET_WHATISCVV') . '
			</span></td>
		    </tr>
		    <tr>
		        <td nowrap width="10%" align="right">' . JText::_('VMPAYMENT_AUTHORIZENET_EXDATE') . '</td>
		        <td> ';
		$html .= shopfunctions::listMonths('cc_expire_month_' . $method->virtuemart_paymentmethod_id, $this->_cc_expire_month);
		$html .= " / ";

		$html .= shopfunctions::listYears('cc_expire_year_' . $method->virtuemart_paymentmethod_id, $this->_cc_expire_year, null, null, "onchange=\"var month = document.getElementById('cc_expire_month_'.$method->virtuemart_paymentmethod_id); if(!CreditCardisExpiryDate(month.value,this.value, '.$method->virtuemart_paymentmethod_id.')){this.value='';month.value='';}\" ");
		$html .='<div id="cc_expiredate_errormsg_' . $method->virtuemart_paymentmethod_id . '"></div>';
		$html .= '</td>  </tr>  	</table></span>';


		$htmla[] = $html;
	    }
	}
	$htmlIn[] = $htmla;

	return true;
    }

    /**
     * Check if the payment conditions are fulfilled for this payment method
     * @author: Valerie Isaksen
     *
     * @param $cart_prices: cart prices
     * @param $payment
     * @return true: if the conditions are fulfilled, false otherwise
     *
     */
    protected function checkConditions($cart, $method, $cart_prices) {

	$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

	$amount = $cart_prices['salesPrice'];
	$amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
		OR
		($method->min_amount <= $amount AND ($method->max_amount == 0) ));
	if (!$amount_cond) {
	    return false;
	}
	$countries = array();
	if (!empty($method->countries)) {
	    if (!is_array($method->countries)) {
		$countries[0] = $method->countries;
	    } else {
		$countries = $method->countries;
	    }
	}

	// probably did not gave his BT:ST address
	if (!is_array($address)) {
	    $address = array();
	    $address['virtuemart_country_id'] = 0;
	}

	if (!isset($address['virtuemart_country_id']))
	    $address['virtuemart_country_id'] = 0;
	if (count($countries) == 0 || in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
	    return true;
	}

	return false;
    }

    function getCosts(VirtueMartCart $cart, $method, $cart_prices) {
	if (preg_match('/%$/', $method->cost_percent_total)) {
	    $cost_percent_total = substr($method->cost_percent_total, 0, -1);
	} else {
	    $cost_percent_total = $method->cost_percent_total;
	}
	return ($method->cost_per_transaction + ($cart_prices['salesPrice'] * $cost_percent_total * 0.01));
    }

    function _setAuthorizeNetIntoSession() {
	$session = JFactory::getSession();
	$sessionAuthorizeNet = new stdClass();
	// card information
	$sessionAuthorizeNet->cc_type = $this->_cc_type;
	$sessionAuthorizeNet->cc_number = $this->_cc_number;
	$sessionAuthorizeNet->cc_cvv = $this->_cc_cvv;
	$sessionAuthorizeNet->cc_expire_month = $this->_cc_expire_month;
	$sessionAuthorizeNet->cc_expire_year = $this->_cc_expire_year;
	$sessionAuthorizeNet->cc_valid = $this->_cc_valid;
	$session->set('authorizenet', serialize($sessionAuthorizeNet), 'vm');
    }

    function _getAuthorizeNetIntoSession() {
	$session = JFactory::getSession();
	$authorizenetSession = $session->get('authorizenet', 0, 'vm');

	if (!empty($authorizenetSession)) {
	    $authorizenetData = unserialize($authorizenetSession);
	    $this->_cc_type = $authorizenetData->cc_type;
	    $this->_cc_number = $authorizenetData->cc_number;
	    $this->_cc_cvv = $authorizenetData->cc_cvv;
	    $this->_cc_expire_month = $authorizenetData->cc_expire_month;
	    $this->_cc_expire_year = $authorizenetData->cc_expire_year;
	    $this->_cc_valid = $authorizenetData->cc_valid;
	}
    }

    /**
     * This is for checking the input data of the payment method within the checkout
     *
     * @author Valerie Cartan Isaksen
     */
    function plgVmOnCheckoutCheckDataPayment(VirtueMartCart $cart) {

	if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
	    return null; // Another method was selected, do nothing
	}
	$this->_getAuthorizeNetIntoSession();
	return $this->_validate_creditcard_data(true);
    }

    /**
     * Create the table for this plugin if it does not yet exist.
     * This functions checks if the called plugin is active one.
     * When yes it is calling the standard method to create the tables
     * @author Valérie Isaksen
     *
     */
    function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {
	return parent::onStoreInstallPluginTable($jplugin_id);
    }

    /**
     * This is for adding the input data of the payment method to the cart, after selecting
     *
     * @author Valerie Isaksen
     *
     * @param VirtueMartCart $cart
     * @return null if payment not selected; true if card infos are correct; string containing the errors id cc is not valid
     */
    function plgVmOnSelectCheckPayment(VirtueMartCart $cart) {


	if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
	    return null; // Another method was selected, do nothing
	}

	//$cart->creditcard_id = JRequest::getVar('creditcard', '0');
	$this->_cc_type = JRequest::getVar('cc_type_' . $cart->virtuemart_paymentmethod_id, '');
	$this->_cc_name = JRequest::getVar('cc_name_' . $cart->virtuemart_paymentmethod_id, '');
	$this->_cc_number = str_replace(" ", "", JRequest::getVar('cc_number_' . $cart->virtuemart_paymentmethod_id, ''));
	$this->_cc_cvv = JRequest::getVar('cc_cvv_' . $cart->virtuemart_paymentmethod_id, '');
	$this->_cc_expire_month = JRequest::getVar('cc_expire_month_' . $cart->virtuemart_paymentmethod_id, '');
	$this->_cc_expire_year = JRequest::getVar('cc_expire_year_' . $cart->virtuemart_paymentmethod_id, '');

	if (!$this->_validate_creditcard_data(true)) {
	    return false; // returns string containing errors
	}
	$this->_setAuthorizeNetIntoSession();
	return true;
    }

    public function plgVmOnSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$payment_name) {

	if (!($method = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}

	$this->_getAuthorizeNetIntoSession();
	$cart_prices['payment_tax_id'] = 0;
	$cart_prices['payment_value'] = 0;

	if (!$this->checkConditions($cart, $method, $cart_prices)) {
	    return false;
	}
	$payment_name = $this->renderPluginName($method);

	$this->setCartPrices($cart, $cart_prices, $method);

	return true;
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
	$this->_getAuthorizeNetIntoSession();
	$extrainfo = $this->getExtraPluginNameInfo();
	$pluginName = $return . '<span class="' . $this->_type . '_name">' . $plugin->$plugin_name . '</span>' . $description;
	$pluginName.= $extrainfo;
	return $pluginName;
    }

    /**
     * Display stored payment data for an order
     * @see components/com_virtuemart/helpers/vmPaymentPlugin::plgVmOnShowOrderPaymentBE()
     */
    function plgVmOnShowOrderBEPayment($virtuemart_order_id, $virtuemart_payment_id) {
	if (!$this->selectedThisByMethodId($virtuemart_payment_id)) {
	    return null; // Another method was selected, do nothing
	}
	if (!($paymentTable = $this->getDataByOrderId($virtuemart_order_id))) {
	    return null;
	}
	$html = '<table class="adminlist">' . "\n";
	$html .= $this->getHtmlHeaderBE();
	$html .= $this->getHtmlRowBE('AUTHORIZENET_PAYMENT_NAME', $paymentTable->payment_name);
	$html .= $this->getHtmlRowBE('AUTHORIZENET_COST_PER_TRANSACTION', $paymentTable->cost_per_transaction);
	$html .= $this->getHtmlRowBE('AUTHORIZENET_COST_PERCENT_TOTAL', $paymentTable->cost_percent_total);
	$code = "authorizenet_response_";
	foreach ($paymentTable as $key => $value) {
	    if (substr($key, 0, strlen($code)) == $code) {
		$html .= $this->getHtmlRowBE($key, $value);
	    }
	}
	$html .= '</table>' . "\n";
	return $html;
    }

    /**
     * Reimplementation of vmPaymentPlugin::plgVmOnConfirmedOrderStorePaymentData()
     *
     * @author Valerie Isaken

      function plgVmOnConfirmedOrderStoreDataPayment(  $virtuemart_order_id, VirtueMartCart $cart, $prices) {
      return null;
      }
     */

    /**
     * Reimplementation of vmPaymentPlugin::plgVmOnConfirmedOrder()
     *
     * @link http://www.authorize.net/support/AIM_guide.pdf
     * Credit Cards Test Numbers
     * Visa Test Account           4007000000027
     * Amex Test Account           370000000000002
     * Master Card Test Account    6011000000000012
     * Discover Test Account       5424000000000015
     * @author Valerie Isaksen
     */
    function plgVmConfirmedOrder($cart, $order) {

	if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}
	$usrBT = $order['details']['BT'];
	$usrST = ((isset($order['details']['ST'])) ? $order['details']['ST'] : $order['details']['BT']);
	$session = JFactory::getSession();
	$return_context = $session->getId();
	$transaction_key = $this->get_passkey();
	if ($transaction_key === false) {
	    return false;
	}
	// Set up data
	$formdata = array();
	$formdata = array_merge($this->_setHeader(), $formdata);
	$formdata = array_merge($this->_setResponseConfiguration(), $formdata);
	$formdata = array_merge($this->_setBillingInformation($usrBT), $formdata);
	$formdata = array_merge($this->_setShippingInformation($usrST), $formdata);
	$formdata = array_merge($this->_setTransactionData($order['details']['BT']), $formdata);
	$formdata = array_merge($this->_setMerchantData($method), $formdata);
	// prepare the array to post
	$poststring = '';
	foreach ($formdata AS $key => $val) {
	    $poststring .= urlencode($key) . "=" . urlencode($val) . "&";
	}
	$poststring = rtrim($poststring, "& ");

	// Prepare data that should be stored in the database
	$dbValues['order_number'] = $order['details']['BT']->order_number;
	$dbValues['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
	$dbValues['payment_method_id'] = $order['details']['BT']->virtuemart_paymentmethod_id;
	$dbValues['return_context'] = $return_context;
	$dbValues['payment_name'] = parent::renderPluginName($method);
	$dbValues['cost_per_transaction'] = $method->cost_per_transaction;
	$dbValues['cost_percent_total'] = $method->cost_percent_total;
	$this->storePSPluginInternalData($dbValues);

	// send a request
	$response = $this->_sendRequest($this->_getPostUrl($method), $poststring);

	$this->logInfo($response);

	$authnet_values = array(); // to check the values???
	// evaluate the response
	$html = $this->_handleResponse($response, $authnet_values, $order, $dbValues['payment_name']);
	if ($this->error) {
	    $new_status = $method->payment_declined_status;
	    $this->_handlePaymentCancel($order['details']['BT']->virtuemart_order_id, $html);
	    return; // will not process the order
	} else if ($this->approved) {
	    $this->_clearAuthorizeNetSession();
	    $new_status = $method->payment_approved_status;
	} else if ($this->declined) {
	    JRequest::setVar('html', $html);
	    $new_status = $method->payment_declined_status;
	    $this->_handlePaymentCancel($order['details']['BT']->virtuemart_order_id, $html);
	    return;
	} else if ($this->held) {
	    $this->_clearAuthorizeNetSession();
	    $new_status = $method->payment_held_status;
	}
	$modelOrder = VmModel::getModel('orders');
	$order['order_status'] = $new_status;
	$order['customer_notified'] = 1;
	$order['comments'] = '';
	$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order, true);

	//We delete the old stuff
	$cart->emptyCart();
	JRequest::setVar('html', $html);
    }

    function _handlePaymentCancel($virtuemart_order_id, $html) {
	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
	$modelOrder = VmModel::getModel('orders');
	$modelOrder->remove(array('virtuemart_order_id' => $virtuemart_order_id));
	// error while processing the payment
	$mainframe = JFactory::getApplication();
	$mainframe->enqueueMessage($html);
	$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&task=editpayment'), JText::_('COM_VIRTUEMART_CART_ORDERDONE_DATA_NOT_VALID'));
    }

    function plgVmGetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

	if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}

	if (!class_exists('VirtueMartModelVendor'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
	$vendorId = 1; //VirtueMartModelVendor::getLoggedVendor();
	$db = JFactory::getDBO();

	$q = 'SELECT   `virtuemart_currency_id` FROM `#__virtuemart_currencies` WHERE `currency_code_3`= "USD"';
	$db->setQuery($q);
	$paymentCurrencyId = $db->loadResult();
    }

    function _clearAuthorizeNetSession() {
	$session = JFactory::getSession();
	$session->clear('authorizenet', 'vm');
    }

    /**
     * renderPluginName
     * Get the name of the payment method
     *
     * @author Valerie Isaksen
     * @param  $payment
     * @return string Payment method name
     */
    function getExtraPluginNameInfo() {
	$creditCardInfos = '';
	if ($this->_validate_creditcard_data(false)) {
	    $cc_number = "**** **** **** " . substr($this->_cc_number, -4);
	    $creditCardInfos .= '<br /><span class="vmpayment_cardinfo">' . JText::_('VMPAYMENT_AUTHORIZENET_CCTYPE') . $this->_cc_type . '<br />';
	    $creditCardInfos .=JText::_('VMPAYMENT_AUTHORIZENET_CCNUM') . $cc_number . '<br />';
	    $creditCardInfos .= JText::_('VMPAYMENT_AUTHORIZENET_CVV2') . '****' . '<br />';
	    $creditCardInfos .= JText::_('VMPAYMENT_AUTHORIZENET_EXDATE') . $this->_cc_expire_month . '/' . $this->_cc_expire_year;
	    $creditCardInfos .="</span>";
	}
	return $creditCardInfos;
    }

    /**
     * Creates a Drop Down list of available Creditcards
     *
     * @author Valerie Isaksen
     */
    function _renderCreditCardList($creditCards, $selected_cc_type, $paymentmethod_id, $multiple = false, $attrs = '') {

	$idA = $id = 'cc_type_' . $paymentmethod_id;
	//$options[] = JHTML::_('select.option', '', JText::_('VMPAYMENT_AUTHORIZENET_SELECT_CC_TYPE'), 'creditcard_type', $name);
	if (!is_array($creditCards)) {
	    $creditCards = (array) $creditCards;
	}
	foreach ($creditCards as $creditCard) {
	    $options[] = JHTML::_('select.option', $creditCard, JText::_('VMPAYMENT_AUTHORIZENET_' . strtoupper($creditCard)));
	}
	if ($multiple) {
	    $attrs = 'multiple="multiple"';
	    $idA .= '[]';
	}
	return JHTML::_('select.genericlist', $options, $idA, $attrs, 'value', 'text', $selected_cc_type);
    }

    /*
     * validate_creditcard_data
     * @author Valerie isaksen
     */

    function _validate_creditcard_data($enqueueMessage = true) {

	$html = '';
	$this->_cc_valid = true;

	if (!Creditcard::validate_credit_card_number($this->_cc_type, $this->_cc_number)) {
	    $this->_errormessage[] = 'VMPAYMENT_AUTHORIZENET_CARD_NUMBER_INVALID';
	    $this->_cc_valid = false;
	}

	if (!Creditcard::validate_credit_card_cvv($this->_cc_type, $this->_cc_cvv)) {
	    $this->_errormessage[] = 'VMPAYMENT_AUTHORIZENET_CARD_CVV_INVALID';
	    $this->_cc_valid = false;
	}
	if (!Creditcard::validate_credit_card_date($this->_cc_type, $this->_cc_expire_month, $this->_cc_expire_year)) {
	    $this->_errormessage[] = 'VMPAYMENT_AUTHORIZENET_CARD_CVV_INVALID';
	    $this->_cc_valid = false;
	}
	if (!$this->_cc_valid) {
	    //$html.= "<ul>";
	    foreach ($this->_errormessage as $msg) {
		//$html .= "<li>" . Jtext::_($msg) . "</li>";
		$html .= Jtext::_($msg) . "<br/>";
	    }
	    //$html.= "</ul>";
	}
	if (!$this->_cc_valid && $enqueueMessage) {
	    $app = JFactory::getApplication();
	    $app->enqueueMessage($html);
	}

	return $this->_cc_valid;
    }

    function _getLoginId($method) {
	return $method->sandbox ? $method->sandbox_login_id : $method->login_id;
    }

    function _getTransactionKey($method) {
	return $method->get('sandbox') ? $method->sandbox_transaction_key : $method->transaction_key;
    }

    /**
     * Gets the gateway Authorize.net URL
     *
     * @return string
     * @access protected
     */
    function _getPostUrl($method) {
	if ($method->sandbox) {
	    if (isset($method->sandbox_hostname)) {
		return $method->sandbox_hostname;
	    } else {
		return 'https://test.authorize.net/gateway/transact.dll';
	    }
	} else {
	    if (isset($method->hostname)) {
		return $method->hostname;
	    } else {
		return 'https://secure.authorize.net/gateway/transact.dll';
	    }
	}
    }

    function _recurringPayment($method) {

	return ''; //$params->get('recurring_payment', '0');
    }

    /**
     * _getFormattedDate
     *
     *
     */
    function _getFormattedDate($month, $year) {

	return sprintf('%02d-%04d', $month, $year);
    }

    function _setHeader() {
	return $this->_authorizenet_params;
    }

    function _setMerchantData($method) {
	return array(
	    'x_login' => $this->_getLoginId($method),
	    'x_tran_key' => $this->_getTransactionKey($method),
	    'x_relay_response' => 'FALSE'
	);
    }

    function _setResponseConfiguration() {
	return array(
	    'x_delim_data' => 'TRUE',
	    'x_delim_char' => '|',
	    'x_relay_response' => 'FALSE'
	);
    }

    function _getfield($string, $length) {
	return substr($string, 0, $length);
    }

    function _setBillingInformation($usrBT) {
	// Customer Name and Billing Address
	return array(
	    'x_first_name' => isset($usrBT->first_name) ? $this->_getField($usrBT->first_name, 50) : '',
	    'x_last_name' => isset($usrBT->last_name) ? $this->_getField($usrBT->last_name, 50) : '',
	    'x_company' => isset($usrBT->company) ? $this->_getField($usrBT->company, 50) : '',
	    'x_address' => isset($usrBT->address_1) ? $this->_getField($usrBT->address_1, 60) : '',
	    'x_city' => isset($usrBT->city) ? $this->_getField($usrBT->city, 40) : '',
	    'x_zip' => isset($usrBT->zip) ? $this->_getField($usrBT->zip, 40) : '',
	    'x_state' => isset($usrBT->virtuemart_state_id) ? $this->_getField(ShopFunctions::getStateByID($usrBT->virtuemart_state_id), 20) : '',
	    'x_country' => isset($usrBT->virtuemart_country_id) ? $this->_getField(ShopFunctions::getCountryByID($usrBT->virtuemart_country_id), 60) : '',
	    'x_phone' => isset($usrBT->phone_1) ? $this->_getField($usrBT->phone_1, 25) : '',
	    'x_fax' => isset($usrBT->fax) ? $this->_getField($usrBT->fax, 25) : ''
	);
    }

    function _setShippingInformation($usrST) {
	// Customer Name and Billing Address
	return array(
	    'x_ship_to_first_name' => isset($usrST->first_name) ? $this->_getField($usrST->first_name, 50) : '',
	    'x_ship_to_last_name' => isset($usrST->first_name) ? $this->_getField($usrST->last_name, 50) : '',
	    // 'x_ship_to_company' => substr($usrST->company, 0, 50),
	    'x_ship_to_address' => isset($usrST->first_name) ? $this->_getField($usrST->address_1, 60) : '',
	    'x_ship_to_city' => isset($usrST->city) ? $this->_getField($usrST->city, 40) : '',
	    'x_ship_to_zip' => isset($usrST->zip) ? $this->_getField($usrST->zip, 40) : '',
	    'x_ship_to_state' => isset($usrST->virtuemart_state_id) ? $this->_getField(ShopFunctions::getStateByID($usrST->virtuemart_state_id), 20) : '',
	    'x_ship_to_country' => isset($usrST->virtuemart_country_id) ? $this->_getField(ShopFunctions::getCountryByID($usrST->virtuemart_country_id), 60) : '',
	);
    }

    function _setTransactionData($orderDetails) {


	return array(
	    'x_amount' => $orderDetails->order_total,
	    'x_invoice_num' => $orderDetails->order_number,
	    'x_method' => 'CC',
	    'x_type' => 'AUTH_CAPTURE',
	    'x_recurring_billing' => 0, //$this->_recurringPayment($params),
	    'x_card_num' => $this->_cc_number,
	    'x_card_code' => $this->_cc_cvv,
	    'x_exp_date' => $this->_getFormattedDate($this->_cc_expire_month, $this->_cc_expire_year)
	);
    }

    /**
     * _sendRequest
     * Posts the request to AuthorizeNet & returns response using curl
     *
     * @author Valerie Isaksen
     * @param string $url
     * @param string $content
     *
     */
    function _sendRequest($post_url, $post_string) {
	$this->logInfo("_sendRequest" . "\n\n", 'message');
	$curl_request = curl_init($post_url);
	curl_setopt($curl_request, CURLOPT_POSTFIELDS, $post_string);
	curl_setopt($curl_request, CURLOPT_HEADER, 0);
	curl_setopt($curl_request, CURLOPT_TIMEOUT, 45);
	curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);

	curl_setopt($curl_request, CURLOPT_POST, 1);
	if (preg_match('/xml/', $post_url)) {
	    curl_setopt($curl_request, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
	}

	$response = curl_exec($curl_request);

	if ($curl_error = curl_error($curl_request)) {
	    $this->logInfo("----CURL ERROR----\n" . $curl_error . "\n\n", 'message');
	}

	curl_close($curl_request);

	return $response;
    }

    /**
     * Proceeds the simple payment
     *
     * @param string $resp
     * @param array $submitted_values
     * @return object Message object
     *
     */
    function _handleResponse($response, $submitted_values, $order, $payment_name) {

	$delimiter = $this->_authorizenet_params['delim_char'];
	$encap_char = $this->_authorizenet_params['encap_char'];

	if ($response) {
	    // Split Array
	    if ($encap_char) {
		//$response_array = explode($encap_char . $delimiter . $encap_char, substr($response, 1, -1));
		$response_array = explode($encap_char, $response);
	    } else {
		$response_array = explode($delimiter, $response);
	    }

	    /**
	     * If AuthorizeNet doesn't return a delimited response.
	     */
	    if (count($response_array) < 10) {
		$this->approved = false;
		$this->error = true;
		$error_message = JText::_('VMPAYMENT_AUTHORIZENET_UNKNOWN') . $response;
		// send email to vendor
		$this->sendEmailToVendorAndAdmins(JText::_('VMPAYMENT_AUTHORIZENET_ERROR_EMAIL_SUBJECT'), $error_message);
		return $error_message;
	    }

	    $authorizeNetResponse['response_code'] = $response_array[0];
	    $this->approved = ($authorizeNetResponse['response_code'] == self::APPROVED);
	    $this->declined = ($authorizeNetResponse['response_code'] == self::DECLINED);
	    $this->error = ($authorizeNetResponse['response_code'] == self::ERROR);
	    $this->held = ($authorizeNetResponse['response_code'] == self::HELD);
	    $authorizeNetResponse['response_subcode'] = $response_array[1];
	    $authorizeNetResponse['response_reason_code'] = $response_array[2];
	    $authorizeNetResponse['response_reason_text'] = $response_array[3];
	    $authorizeNetResponse['authorization_code'] = $response_array[4];
	    $authorizeNetResponse['avs_response'] = $response_array[5]; //Address Verification Service
	    $authorizeNetResponse['transaction_id'] = $response_array[6];
	    $authorizeNetResponse['invoice_number'] = $response_array[7];
	    $authorizeNetResponse['description'] = $response_array[8];
	    if ($this->approved) {
		$authorizeNetResponse['amount'] = $response_array[9];
		$authorizeNetResponse['method'] = $response_array[10];
		$authorizeNetResponse['transaction_type'] = $response_array[11];
		$authorizeNetResponse['customer_id'] = $response_array[12];
		$authorizeNetResponse['first_name'] = $response_array[13];
		$authorizeNetResponse['last_name'] = $response_array[14];
		$authorizeNetResponse['company'] = $response_array[15];
		$authorizeNetResponse['address'] = $response_array[16];
		$authorizeNetResponse['city'] = $response_array[17];
		$authorizeNetResponse['state'] = $response_array[18];
		$authorizeNetResponse['zip_code'] = $response_array[19];
		$authorizeNetResponse['country'] = $response_array[20];
		$authorizeNetResponse['phone'] = $response_array[21];
		$authorizeNetResponse['fax'] = $response_array[22];
		$authorizeNetResponse['email_address'] = $response_array[23];
		$authorizeNetResponse['ship_to_first_name'] = $response_array[24];
		$authorizeNetResponse['ship_to_last_name'] = $response_array[25];
		$authorizeNetResponse['ship_to_company'] = $response_array[26];
		$authorizeNetResponse['ship_to_address'] = $response_array[27];
		$authorizeNetResponse['ship_to_city'] = $response_array[28];
		$authorizeNetResponse['ship_to_state'] = $response_array[29];
		$authorizeNetResponse['ship_to_zip_code'] = $response_array[30];
		$authorizeNetResponse['ship_to_country'] = $response_array[31];
		$authorizeNetResponse['tax'] = $response_array[32];
		$authorizeNetResponse['duty'] = $response_array[33];
		$authorizeNetResponse['freight'] = $response_array[34];
		$authorizeNetResponse['tax_exempt'] = $response_array[35];
		$authorizeNetResponse['purchase_order_number'] = $response_array[36];
		$authorizeNetResponse['md5_hash'] = $response_array[37];
		$authorizeNetResponse['card_code_response'] = $response_array[38];
		$authorizeNetResponse['cavv_response'] = $response_array[39]; //// cardholder_authentication_verification_response
		$authorizeNetResponse['account_number'] = $response_array[50];
		$authorizeNetResponse['card_type'] = $response_array[51];
		$authorizeNetResponse['split_tender_id'] = $response_array[52];
		$authorizeNetResponse['requested_amount'] = $response_array[53];
		$authorizeNetResponse['balance_on_card'] = $response_array[54];
	    }

	    /*
	     * check the amount is the same as the amount sent
	     */
	    /* SUBCODE?? */
	    $this->approved = ($authorizeNetResponse['response_code'] == self::APPROVED);
	    $this->declined = ($authorizeNetResponse['response_code'] == self::DECLINED);
	    $this->error = ($authorizeNetResponse['response_code'] == self::ERROR);
	    $this->held = ($authorizeNetResponse['response_code'] == self::HELD);

	    // Set custom fields: not used yet: could put the return context
	    /*
	      if ($count = count($custom_fields)) {
	      $custom_fields_response = array_slice($response_array, -$count, $count);
	      $i = 0;
	      foreach ($custom_fields as $key => $value) {
	      $this->$key = $custom_fields_response[$i];
	      $i++;
	      }
	      }
	     */

	    $virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($authorizeNetResponse['invoice_number']);
	    if (!$virtuemart_order_id) {
		$this->approved = false;
		$this->error = true;
		$this->logInfo(JText::sprintf('VMPAYMENT_AUTHORIZENET_NO_ORDER_NUMBER', $authorizeNetResponse['invoice_number']), 'ERROR');
		$this->sendEmailToVendorAndAdmins(JText::sprintf('VMPAYMENT_AUTHORIZENET_NO_ORDER_NUMBER', $authorizeNetResponse['invoice_number']), JText::sprintf('VMPAYMENT_AUTHORIZENET_ERROR_WHILE_PROCESSING_PAYMENT', $authorizeNetResponse['invoice_number']));
		$html = Jtext::sprintf('VMPAYMENT_AUTHORIZENET_ERROR', $authorizeNetResponse['response_reason_text'], $authorizeNetResponse['response_code']) . "<br />";
		$this->logInfo($html, 'PAYMENT DECLINED');
		return $html;
	    }
	    if ($this->error or $this->declined) {
		// Prepare data that should be stored in the database
		$dbValues['authorizenet_response_response_code'] = $authorizeNetResponse['response_code'];
		$dbValues['authorizenet_response_response_subcode'] = $authorizeNetResponse['response_subcode'];
		$dbValues['authorizenet_response_response_reason_code'] = $authorizeNetResponse['response_reason_code'];
		$dbValues['authorizenet_response_response_reason_text'] = $authorizeNetResponse['response_reason_text'];
		//$this->storePSPluginInternalData($dbValues, 'id', true);
		$html = Jtext::sprintf('VMPAYMENT_AUTHORIZENET_ERROR', $authorizeNetResponse['response_reason_text'], $authorizeNetResponse['response_code']) . "<br />";
		$this->logInfo($html, 'PAYMENT DECLINED');
		return $html;
	    }
	} else {
	    $this->approved = false;
	    $this->error = true;
	    $this->logInfo(JText::_('VMPAYMENT_AUTHORIZENET_CONNECTING_ERROR'), 'ERROR');
	    $this->sendEmailToVendorAndAdmins(JText::_('VMPAYMENT_AUTHORIZENET_ERROR_EMAIL_SUBJECT'), JText::_('VMPAYMENT_AUTHORIZENET_CONNECTING_ERROR'));
	    return JText::_('VMPAYMENT_AUTHORIZENET_CONNECTING_ERROR');
	}
// Prep
// get all know columns of the table
	$db = JFactory::getDBO();
	$query = 'SHOW COLUMNS FROM `' . $this->_tablename . '` ';
	$db->setQuery($query);
	$columns = $db->loadResultArray(0);

	foreach ($authorizeNetResponse as $key => $value) {
	    $table_key = 'authorizenet_response_' . $key;
	    if (in_array($table_key, $columns)) {
		$response_fields[$table_key] = $value;
	    }
	}
	$response_fields['virtuemart_order_id'] = $virtuemart_order_id;
	$response_fields['invoice_number'] = $authorizeNetResponse['invoice_number'];
	$response_fields['authorizeresponse_raw'] = $response;

	$this->storePSPluginInternalData($response_fields, 'virtuemart_order_id', true);

	$currencyModel = VmModel::getModel('Currency');
	$currency = $currencyModel->getCurrency($order['details']['BT']->user_currency_id);

	$html = '<table class="adminlist">' . "\n";
	$html .= $this->getHtmlRow('AUTHORIZENET_PAYMENT_NAME', $payment_name);
	$html .= $this->getHtmlRow('AUTHORIZENET_ORDER_NUMBER', $authorizeNetResponse['invoice_number']);
	$html .= $this->getHtmlRow('AUTHORIZENET_AMOUNT', $authorizeNetResponse['amount'] . ' ' . $currency->currency_name);
	//$html .= $this->getHtmlRow('AUTHORIZENET_RESPONSE_AUTHORIZATION_CODE', $authorizeNetResponse['authorization_code']);
	$html .= $this->getHtmlRow('AUTHORIZENET_RESPONSE_TRANSACTION_ID', $authorizeNetResponse['transaction_id']);
	$html .= '</table>' . "\n";
	$this->logInfo(JText::_('VMPAYMENT_AUTHORIZENET_ORDER_NUMBER') . " " . $authorizeNetResponse['invoice_number'] . ' payment approved', 'message');
	return $html;
    }

    /**
     * displays the CVV images of for CVV tooltip plugin
     *
     * @author Valerie Isaksen
     * @param array $logo_list
     * @return html with logos
     */
    public function _displayCVVImages($method) {
	$cvv_images = $method->cvv_images;
	$img = '';
	if ($cvv_images) {
	    $img = $this->displayLogos($cvv_images);
	    $img = str_replace('"', "'", $img);
	}
	return $img;
    }

    /**
     * We must reimplement this triggers for joomla 1.7
     */

    /**
     * plgVmOnCheckAutomaticSelectedPayment
     * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
     * The plugin must check first if it is the correct type
     * @author Valerie Isaksen
     * @param VirtueMartCart cart: the cart object
     * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
     *
     */
    function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array(),   &$paymentCounter) {
	return parent::onCheckAutomaticSelected($cart, $cart_prices,   $paymentCounter);
    }

    /**
     * This method is fired when showing the order details in the frontend.
     * It displays the method-specific data.
     *
     * @param integer $order_id The order ID
     * @return mixed Null for methods that aren't active, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    protected function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {
	$this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
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
    function plgVmOnShowOrderPrintPayment($order_number, $method_id) {
	return parent::onShowOrderPrint($order_number, $method_id);
    }

    /**
     * Save updated order data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.


      public function plgVmOnUpdateOrderPayment(  $_formData) {
      return null;
      }
     */
    /**
     * Save updated orderline data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.


      public function plgVmOnUpdateOrderLine(  $_formData) {
      return null;
      }
     */
    /**
     * plgVmOnEditOrderLineBE
     * This method is fired when editing the order line details in the backend.
     * It can be used to add line specific package codes
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise


      public function plgVmOnEditOrderLineBE(  $_orderId, $_lineId) {
      return null;
      }
     */

    /**
     * This method is fired when showing the order details in the frontend, for every orderline.
     * It can be used to display line specific package codes, e.g. with a link to external tracking and
     * tracing systems
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise

      public function plgVmOnShowOrderLineFE(  $_orderId, $_lineId) {
      return null;
      }
     */
    function plgVmDeclarePluginParamsPayment($name, $id, &$data) {
	return $this->declarePluginParams('payment', $name, $id, $data);
    }

    function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {
	return $this->setOnTablePluginParams($name, $id, $table);
    }

}

// No closing tag
