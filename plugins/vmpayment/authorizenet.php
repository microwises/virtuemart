<?php

if (!defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * Payment processing plugin for transactions with authorize.net AIM
 *
 * @package VirtueMart
 * @subpackage Plugins - payment
 * @author Valerie Isaksen
* @copyright	(C)2011  Alatak.net software and iStraxx company. All rights reserved.
 *
 * http://www.alatak.net
 */
if (!class_exists('Creditcard')) {
    require_once(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'creditcard.php');
}

class plgVmPaymentAuthorizenet extends vmPaymentPlugin {

    var $_pelement;
    var $_tablename;
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
    private $_cc_paymentmethod_id = '';
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
    function plgVmPaymentAuthorizenet(& $subject, $config) {
	$this->_pelement = basename(__FILE__, '.php');
	$this->_tablename = '#__virtuemart_order_payment_' . $this->_pelement;
	$this->_createTable();
	parent::__construct($subject, $config);
    }

    /**
     * Create the table for this plugin if it does not yet exist.
     * @author Oscar van Eijk
     */
    protected function _createTable() {
	$_scheme = DbScheme::get_instance();
	$_scheme->create_scheme($this->_tablename);
	$_schemeCols = array(
	    'id' => array(
		'type' => 'int'
		, 'length' => 11
		, 'auto_inc' => true
		, 'null' => false
	    )
	    , 'virtuemart_order_id' => array(
		'type' => 'int'
		, 'length' => 11
		, 'null' => false
	    )
	    , 'order_number' => array(
		'type' => 'varchar'
		, 'length' => 32
		, 'null' => false
	    )
	    , 'payment_name' => array(
		'type' => 'varchar'
		, 'length' => 255
		, 'null' => true
		, 'default' => 'NULL'
	    )
	    , 'payment_method_id' => array(
		'type' => 'text'
		, 'null' => false
	    )
	    , 'return_context' => array(
		'type' => 'varchar'
		, 'length' => 255
		, 'null' => false
	    )
	    , 'authorize_order_payment_expire' => array(
		'type' => 'int'
		, 'length' => 11
		, 'null' => true
	    )
	    , 'authorize_order_payment_log' => array(
		'type' => 'text'
		, 'null' => true
	    )
	    , 'authorizenet_response_authorization_code' => array(
		'type' => 'text'
		, 'default' => ''
		, 'null' => false
	    )
	    , 'authorizenet_response_transaction_id' => array(
		'type' => 'text'
		, 'default' => ''
		, 'null' => false
	    )
	    , 'authorizenet_response_response_code' => array(
		'type' => 'int'
		, 'default' => '11'
		, 'null' => false
	    )
	    , 'authorizenet_response_response_subcode' => array(
		'type' => 'int'
		, 'default' => '11'
		, 'null' => false
	    )
	    , 'authorizenet_response_response_reason_code' => array(
		'type' => 'int'
		, 'default' => '11'
		, 'null' => false
	    )
	    , 'authorizenet_response_response_reason_text' => array(
		'type' => 'text'
		, 'null' => false
	    )
	    , 'authorizenet_response_transaction_type' => array(
		'type' => 'text'
		, 'null' => false
	    )
	    , 'authorizenet_response_account_number' => array(
		'type' => 'text'
		, 'null' => false
	    )
	    , 'authorizenet_response_card_type' => array(
		'type' => 'text'
		, 'null' => false
	    )
	    , 'authorizenet_response_card_code_response' => array(
		'type' => 'char'
		, 'default' => '1'
		, 'null' => false
	    )
	    , 'authorizenet_response_cavv_response' => array(
		'type' => 'char'
		, 'default' => '1'
		, 'null' => false
	    )
	    , 'authorizeresponse_raw' => array(
		'type' => 'text'
		, 'null' => false
	    )
	);
	$_schemeIdx = array(
	    'idx_order_payment' => array(
		'columns' => array('virtuemart_order_id')
		, 'primary' => false
		, 'unique' => false
		, 'type' => null
	    )
	);
	$_scheme->define_scheme($_schemeCols);
	$_scheme->define_index($_schemeIdx);
	if (!$_scheme->scheme(true)) {
	    JError::raiseWarning(500, $_scheme->get_db_error());
	}
	$_scheme->reset();
    }

    /**
     * This shows the plugin for choosing in the payment list of the checkout process.
     *
     * @author Max Milbers
     * @author Valerie Cartan Isaksen
     */
    function plgVmOnSelectPayment(VirtueMartCart $cart, $selectedPayment=0) {
	JHTML::_('behavior.tooltip');
	if ($this->getPaymentMethods($cart->vendorId) === false) {
	    if (empty($this->_name)) {
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('COM_VIRTUEMART_CART_NO_PAYMENT'));
		return;
	    } else {
		//return JText::sprintf('COM_VIRTUEMART_PAYMENT_NOT_VALID_FOR_THIS_VENDOR', $this->_name , $cart->vendorId );
		return;
	    }
	}

	JHTML::script('vmcreditcard.js', 'components/com_virtuemart/assets/js/', false);
	JFactory::getLanguage()->load('com_virtuemart');
	vmJsApi::jCreditCard();
	$htmla = '';
	$html = array();
	foreach ($this->payments as $payment) {
	    if ($this->checkPaymentConditions($cart->pricesUnformatted, $payment)) {
		$params = new JParameter($payment->payment_params);
		$paymentSalesPrice = $this->calculateSalesPricePayment($this->getPaymentValue($params, $cart), $this->getPaymentTaxId($params, $cart));
		$this->_cc_paymentmethod_id = $cart->virtuemart_paymentmethod_id;
		$payment->payment_name = $this->getPaymentName($payment);
		$html = $this->getPaymentHtml($payment, $selectedPayment, $paymentSalesPrice);
		if ($selectedPayment == $payment->virtuemart_paymentmethod_id) {
		    if (!empty($cart->cc_type))
			$this->_cc_type = $cart->cc_type;
		    if (!empty($cart->cc_number))
			$this->_cc_number = $cart->cc_number;
		    if (!empty($cart->cc_cvv))
			$this->_cc_cvv = $cart->cc_cvv;
		    if (!empty($cart->cc_expire_month))
			$this->_cc_expire_month = $cart->cc_expire_month;
		    if (!empty($cart->cart_cc_expire_year))
			$this->_cc_expire_year = $cart->_cc_expire_year;
		} else {
		    $this->_cc_type = '';
		    $this->_cc_number = '';
		    $this->_cc_cvv = '';
		    $this->_cc_expire_month = '';
		    $this->_cc_expire_year = '';
		}
		$creditCards = $params->get('creditcards');

		$creditCardList = '';
		if ($creditCards) {
		    $creditCardList = ($this->_renderCreditCardList($creditCards, $this->_cc_type, $payment->virtuemart_paymentmethod_id, false));
		}
		$sandbox_msg = "";
		if ($params->get('sandbox', 0)) {
		    $sandbox_msg .= '<br />' . JText::_('VMPYAMENT_AUTHORIZENET_SANDBOX_TEST_NUMBERS');
		}

		$cvv_images = $this->_displayCVVImages($params);
		$html .= '<br /><span class="vmpayment_cardinfo">' . JText::_('VMPYAMENT_AUTHORIZENET_COMPLETE_FORM') . $sandbox_msg . '
		    <table border="0" cellspacing="0" cellpadding="2" width="100%">
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="creditcardtype">' . JText::_('VMPYAMENT_AUTHORIZENET_CCTYPE') . '</label>
		        </td>
		        <td>' . $creditCardList .
			'</td>
		    </tr>

		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="cc_type">' . JText::_('VMPYAMENT_AUTHORIZENET_CCNUM') . '</label>
		        </td>
		        <td>
		        <input type="text" class="inputbox" id="cc_number_' . $payment->virtuemart_paymentmethod_id . '" name="cc_number_' . $payment->virtuemart_paymentmethod_id . '" value="' . $this->_cc_number . '"    autocomplete="off"   onchange="ccError=razCCerror(' . $payment->virtuemart_paymentmethod_id . ');
	CheckCreditCardNumber(this . value, ' . $payment->virtuemart_paymentmethod_id . ');
	if (!ccError) {
	    this.value=\'\';}" />
		        <div id="cc_cardnumber_errormsg_' . $payment->virtuemart_paymentmethod_id . '"></div>
		    </td>
		    </tr>
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="cc_cvv">' . JText::_('VMPYAMENT_AUTHORIZENET_CVV2') . '</label>
		        </td>
		        <td>
		            <input type="text" class="inputbox" id="cc_cvv_' . $payment->virtuemart_paymentmethod_id . '" name="cc_cvv_' . $payment->virtuemart_paymentmethod_id . '" maxlength="4" size="5" value="' . $this->_cc_cvv . '" autocomplete="off" />

			<span class="hasTip" title="' . JText::_('VMPYAMENT_AUTHORIZENET_WHATISCVV') . '::' . JText::sprintf("VMPYAMENT_AUTHORIZENET_WHATISCVV_TOOLTIP", $cvv_images) . ' ">' .
			JText::_('VMPYAMENT_AUTHORIZENET_WHATISCVV') . '
			</span></td>
		    </tr>
		    <tr>
		        <td nowrap width="10%" align="right">' . JText::_('VMPYAMENT_AUTHORIZENET_EXDATE') . '</td>
		        <td> ';
		$html .= shopfunctions::listMonths('cc_expire_month_' . $payment->virtuemart_paymentmethod_id, $this->_cc_expire_month);
		$html .= " / ";

		$html .= shopfunctions::listYears('cc_expire_year_' . $payment->virtuemart_paymentmethod_id, $this->_cc_expire_year, null, null, "onchange=\"var month = document.getElementById('cc_expire_month_'.$payment->virtuemart_paymentmethod_id); if(!CreditCardisExpiryDate(month.value,this.value, '.$payment->virtuemart_paymentmethod_id.')){this.value='';month.value='';}\" ");
		$html .='<div id="cc_expiredate_errormsg_' . $payment->virtuemart_paymentmethod_id . '"></div>';
		$html .= '</td>  </tr>  	</table></span>';


		$htmla[] = $html;
	    }
	}


	return $htmla;
    }

    /**
     * This is for checking the input data of the payment method within the checkout
     *
     * @author Max Milbers
     * @author Valerie Cartan Isaksen
     */
    function plgVmOnCheckoutCheckPaymentData(VirtueMartCart $cart) {
	if (!($payment = $this->getPaymentMethod($cart->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	$params = new JParameter($payment->payment_params);

	$this->_cc_type = $cart->cc_type;
	$this->_cc_number = $cart->cc_number;
	$this->_cc_cvv = $cart->cc_cvv;
	$this->_cc_expire_month = $cart->cc_expire_month;
	$this->_cc_expire_year = $cart->cc_expire_year;

	return $this->_validate_creditcard_data(true);
    }

    /**
     * This is for adding the input data of the payment method to the cart, after selecting
     *
     * @author Max Milbers
     * @author Oscar van Eijk
     * @author Valerie Isaksen
     *
     * @param VirtueMartCart $cart
     * @return null if payment not selected; true if card infos are correct; string containing the errors id cc is not valid
     */
    function plgVmOnPaymentSelectCheck(VirtueMartCart $cart) {
	if (!($payment = $this->getPaymentMethod($cart->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	$params = new JParameter($payment->payment_params);


	//$cart->creditcard_id = JRequest::getVar('creditcard', '0');
	$this->_cc_type = JRequest::getVar('cc_type_' . $cart->virtuemart_paymentmethod_id, '');
	$this->_cc_name = JRequest::getVar('cc_name_' . $cart->virtuemart_paymentmethod_id, '');

	$this->_cc_number = JRequest::getVar('cc_number_' . $cart->virtuemart_paymentmethod_id, '');
	$this->_cc_cvv = JRequest::getVar('cc_cvv_' . $cart->virtuemart_paymentmethod_id, '');
	$this->_cc_expire_month = JRequest::getVar('cc_expire_month_' . $cart->virtuemart_paymentmethod_id, '');
	$this->_cc_expire_year = JRequest::getVar('cc_expire_year_' . $cart->virtuemart_paymentmethod_id, '');


	//if ($params->get('check_card_code', 0)) {
	if (!$this->_validate_creditcard_data(true)) {
	    return false; // returns string containing errors
	}
	//}

	$cart->cc_type = $this->_cc_type;
	$cart->cc_number = $this->_cc_number;
	$cart->cc_cvv = $this->_cc_cvv;
	$cart->cc_expire_month = $this->_cc_expire_month;
	$cart->cc_expire_year = $this->_cc_expire_year;
	return true;
    }

    public function plgVmOnPaymentSelectedCalculatePrice(VirtueMartCart $cart, array &$cart_prices, $payment_name) {
	if (!$this->selectedThisPayment($this->_pelement, $cart->virtuemart_paymentmethod_id)) {
	    return null; // Another payment was selected, do nothing
	}

	if (!($payment = $this->getThisPaymentData($cart->virtuemart_paymentmethod_id) )) {
	    return null;
	}

	$this->_cc_type = $cart->cc_type;
	$this->_cc_number = $cart->cc_number;
	$this->_cc_cvv = $cart->cc_cvv;
	$this->_cc_expire_month = $cart->cc_expire_month;
	$this->_cc_expire_year = $cart->cc_expire_year;
	$this->_cc_valid = true;
	$this->_cc_paymentmethod_id = $cart->virtuemart_paymentmethod_id;
	$payment_name = '';
	$cart_prices['payment_tax_id'] = 0;
	$cart_prices['payment_value'] = 0;

	if (!$this->checkPaymentConditions($cart_prices, $payment)) {
	    return false;
	}
	$params = new JParameter($payment->payment_params);
	$payment_name = $this->getPaymentName($payment);
	$payment_value = $this->getPaymentValue($params, $cart_prices);
	$payment_tax_id = $this->getPaymentTaxId($params);

	$this->setCartPrices($cart_prices, $payment_value, $payment_tax_id);

	return true;
    }

    /**
     * Display stored payment data for an order
     * @see components/com_virtuemart/helpers/vmPaymentPlugin::plgVmOnShowOrderPaymentBE()
     */
    function plgVmOnShowOrderPaymentBE($virtuemart_order_id, $virtuemart_payment_id) {
	if (!$this->selectedThisPayment($this->_pelement, $virtuemart_payment_id)) {
	    return null; // Another method was selected, do nothing
	}
	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
	$db->setQuery($q);
	if (!($paymentTable = $db->loadObject())) {
	    JError::raiseWarning(500, $db->getErrorMsg());
	    return '';
	}
	$html = '<table class="admintable">' . "\n";
	$html .=$this->getHtmlHeaderBE();
	$html .= $this->getHtmlRowBE('AUTHORIZENET_PAYMENT_NAME', $paymentTable->payment_name);
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
     */
    function plgVmOnConfirmedOrderStorePaymentData($virtuemart_order_id, VirtueMartCart $cart, $prices) {
	return null;
    }

    /**
     * Reimplementation of vmPaymentPlugin::plgVmOnConfirmedOrderGetPaymentForm()
     *
     * @link http://www.authorize.net/support/AIM_guide.pdf
     * Credit Cards Test Numbers
     * Visa Test Account           4007000000027
     * Amex Test Account           370000000000002
     * Master Card Test Account    6011000000000012
     * Discover Test Account       5424000000000015
     * @author Valerie Isaken
     */
    function plgVmOnConfirmedOrderGetPaymentForm($order_number, $cart, $return_context, &$html, &$new_status) {
	if (!($payment = $this->getPaymentMethod($cart->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	$params = new JParameter($payment->payment_params);
	$this->setDebug($params);
	$usrBT = $cart->BT;
	$usrST = (($cart->ST === 0) ? $cart->BT : $cart->ST);

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
	$formdata = array_merge($this->_setTransactionData($cart, $order_number), $formdata);
	$formdata = array_merge($this->_setMerchantData($params), $formdata);
	// prepare the array to post
	$poststring = '';
	foreach ($formdata AS $key => $val) {
	    $poststring .= urlencode($key) . "=" . urlencode($val) . "&";
	}
	$poststring = rtrim($poststring, "& ");

	// Prepare data that should be stored in the database
	$dbValues['order_number'] = $order_number;
	$dbValues['virtuemart_order_id'] = $cart->virtuemart_order_id;
	$dbValues['payment_method_id'] = $cart->virtuemart_paymentmethod_id;
	$dbValues['return_context'] = $return_context;
	$dbValues['payment_name'] = parent::getPaymentName($payment); //$this->getThisPaymentName($cart->virtuemart_paymentmethod_id);
	$this->writeData($dbValues, $this->_tablename);

	// send a request
	$response = $this->_sendRequest($this->_getPostUrl($params), $poststring);

	$this->logInfo($response);

	$authnet_values = array(); // to check the values???
	// evaluate the response
	$html = $this->_handleResponse($response, $authnet_values, $dbValues['payment_name']);
	if ($this->error) {
	    return false; // will not empty the cart
	} else if ($this->approved) {
	    $new_status = $params->get('payment_approved_status');
	    return true;
	} else if ($this->declined) {
	    $new_status = $params->get('payment_declined_status');
	    return true;
	} else if ($this->held) {
	    $new_status = $params->get('payment_held_status');
	    return true;
	}
    }

    /**
     * getPaymentName
     * Get the name of the payment method
     *
     * @author Valerie Isaksen
     * @param  $payment
     * @return string Payment method name
     */
    function getPaymentName($payment) {
	$paymentName = $returnlogo = '';
	$params = new JParameter($payment->payment_params);
	$paymentLogo = $params->get('payment_logos');
	$paymentDescription = $params->get('payment_description', '');
	if (!empty($paymentLogo)) {
	    $returnlogo = $this->displayLogos($paymentLogo) . ' ';
	}
	$paymentName = $returnlogo . $payment->payment_name;

	if (!empty($paymentDescription)) {
	    $paymentDescription = '<span class="vmpayment_description">' . $paymentDescription . '</span>';
	}
	$paymentName = $returnlogo . '<span class="vmpayment_name">' . $payment->payment_name . '</span>' . $paymentDescription;

	if (($this->_cc_paymentmethod_id == $payment->virtuemart_paymentmethod_id) && $this->_validate_creditcard_data(false)) {
	    $cc_number = "**** **** **** " . substr($this->_cc_number, -4);
	    $paymentName .= '<br /><span class="vmpayment_cardinfo">' . JText::_('VMPYAMENT_AUTHORIZENET_CCTYPE') . $this->_cc_type . '<br />';
	    $paymentName .=JText::_('VMPYAMENT_AUTHORIZENET_CCNUM') . $cc_number . '<br />';
	    $paymentName .= JText::_('VMPYAMENT_AUTHORIZENET_CVV2') . '****' . '<br />';
	    $paymentName .= JText::_('VMPYAMENT_AUTHORIZENET_EXDATE') . $this->_cc_expire_month . '/' . $this->_cc_expire_year;
	    $paymentName .="</span>";
	}
	return $paymentName;
    }

    /**
     * Creates a Drop Down list of available Creditcards
     *
     * @author Valerie Isaksen
     */
    function _renderCreditCardList($creditCards, $selected_cc_type, $paymentmethod_id, $multiple = false, $attrs='') {

	$idA = $id = 'cc_type_' . $paymentmethod_id;
	//$options[] = JHTML::_('select.option', '', JText::_('VMPAYMENT_AUTHORIZENET_SELECT_CC_TYPE'), 'creditcard_type', $name);
	if (!is_array($creditCards)) {
	    $creditCards=(array) $creditCards;
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

    function _validate_creditcard_data($enqueueMessage=true) {

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
	    $app = & JFactory::getApplication();
	    $app->enqueueMessage($html);
	}

	return $this->_cc_valid;
    }

    function _getLoginId($params) {
	return $params->get('sandbox') ? $params->get('sandbox_login_id') : $params->get('login_id');
    }

    function _getTransactionKey($params) {
	return $params->get('sandbox') ? $params->get('sandbox_transaction_key') : $params->get('transaction_key');
    }

    /**
     * Gets the gateway Authorize.net URL
     *
     * @return string
     * @access protected
     */
    function _getPostUrl($params) {

	return $params->get('sandbox') ? 'https://test.authorize.net/gateway/transact.dll' : 'https://secure.authorize.net/gateway/transact.dll';
    }

    function _recurringPayment($params) {

	return $params->get('recurring_payment', '0');
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

    function _setMerchantData($params) {
	return array(
	    'x_login' => $this->_getLoginId($params),
	    'x_tran_key' => $this->_getTransactionKey($params),
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
	    'x_first_name' => isset($usrBT['first_name']) ? $this->_getField($usrBT['first_name'], 50) : '',
	    'x_last_name' => isset($usrBT['last_name']) ? $this->_getField($usrBT['last_name'], 50) : '',
	    'x_company' => isset($usrBT['company']) ? $this->_getField($usrBT['company'], 50) : '',
	    'x_address' => isset($usrBT['address_1']) ? $this->_getField($usrBT['address_1'], 60) : '',
	    'x_city' => isset($usrBT['city']) ? $this->_getField($usrBT['city'], 40) : '',
	    'x_state' => isset($usrBT['state']) ? $this->_getField($usrBT['state'], 40) : '',
	    'x_zip' => isset($usrBT['virtuemart_state_id']) ? $this->_getField(ShopFunctions::getStateByID($usrBT['virtuemart_state_id']), 20) : '',
	    'x_country' => isset($usrBT['virtuemart_country_id']) ? $this->_getField(ShopFunctions::getCountryByID($usrBT['virtuemart_country_id']), 60) : '',
	    'x_phone' => isset($usrBT['phone_1']) ? $this->_getField($usrBT['phone_1'], 25) : '',
	    'x_fax' => isset($usrBT['fax']) ? $this->_getField($usrBT['fax'], 25) : ''
	);
    }

    function _setShippingInformation($usrST) {
	// Customer Name and Billing Address
	return array(
	    'x_ship_to_first_name' => isset($usrST['first_name']) ? $this->_getField($usrST['first_name'], 50) : '',
	    'x_ship_to_last_name' => isset($usrST['first_name']) ? $this->_getField($usrST['last_name'], 50) : '',
	    // 'x_ship_to_company' => substr($usrST['company'], 0, 50),
	    'x_ship_to_address' => isset($usrST['first_name']) ? $this->_getField($usrST['address_1'], 60) : '',
	    'x_ship_to_city' => isset($usrST['city']) ? $this->_getField($usrST['city'], 40) : '',
	    'x_ship_to_zip' => isset($usrST['zip']) ? $this->_getField($usrST['zip'], 40) : '',
	    'x_ship_to_state' => isset($usrST['virtuemart_state_id']) ? $this->_getField(ShopFunctions::getStateByID($usrST['virtuemart_state_id']), 20) : '',
	    'x_ship_to_country' => isset($usrST['virtuemart_country_id']) ? $this->_getField(ShopFunctions::getCountryByID($usrST['virtuemart_country_id']), 60) : '',
	);
    }

    function _setTransactionData($cart, $order_number) {
	if (!class_exists('VirtueMartModelCurrency'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'currency.php');
	$currencyModel = new VirtueMartModelCurrency();
	$currency = $currencyModel->getCurrency($cart->pricesCurrency);

	return array(
	    'x_amount' => $cart->pricesUnformatted['billTotal'],
	    'x_invoice_num' => $order_number,
	    'x_method' => 'CC',
	    'x_type' => 'AUTH_CAPTURE',
	    'x_recurring_billing' => 0, //$this->_recurringPayment($params),
	    'x_card_num' => $cart->cc_number,
	    'x_card_code' => $cart->cc_cvv,
	    'x_exp_date' => $this->_getFormattedDate($cart->cc_expire_month, $cart->cc_expire_year)
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
    function _handleResponse($response, $submitted_values, $payment_name) {

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
	    $authorizeNetResponse['response_subcode'] = $response_array[1];
	    $authorizeNetResponse['response_reason_code'] = $response_array[2];
	    $authorizeNetResponse['response_reason_text'] = $response_array[3];
	    $authorizeNetResponse['authorization_code'] = $response_array[4];
	    $authorizeNetResponse['avs_response'] = $response_array[5]; //Address Verification Service
	    $authorizeNetResponse['transaction_id'] = $response_array[6];
	    $authorizeNetResponse['invoice_number'] = $response_array[7];
	    $authorizeNetResponse['description'] = $response_array[8];
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
		return JText::sprintf('VMPAYMENT_AUTHORIZENET_ERROR_WHILE_PROCESSING_PAYMENT', $authorizeNetResponse['invoice_number']);
	    }
	    if ($this->error or $this->declined) {
		// Prepare data that should be stored in the database
		$dbValues['authorizenet_response_response_code'] = $authorizeNetResponse['response_code'];
		$dbValues['authorizenet_response_response_subcode'] = $authorizeNetResponse['response_subcode'];
		$dbValues['authorizenet_response_response_reason_code'] = $authorizeNetResponse['response_reason_code'];
		$dbValues['authorizenet_response_response_reason_text'] = $authorizeNetResponse['response_reason_text'];

		$this->updateData($dbValues, $this->_tablename, 'virtuemart_order_id', $virtuemart_order_id);
		$html = Jtext::sprintf('VMPAYMENT_AUTHORIZENET_ERROR', $authorizeNetResponse['response_reason_text'], $authorizeNetResponse['response_code']) . "<br />";
		$this->logInfo($html, 'PAYMENT DECLINED');
		return $html; // the transaction has been submitted, we don't want to delete the order
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

	$response_fields['authorizeresponse_raw'] = $response;

	$this->updateData($response_fields, $this->_tablename, 'virtuemart_order_id', $virtuemart_order_id);

	$html = '<table>' . "\n";
	$html .= $this->getHtmlRow('', $payment_name);

	$html .= $this->getHtmlRow('AUTHORIZENET_ORDER_NUMBER', $authorizeNetResponse['invoice_number']);
	$html .= $this->getHtmlRow('AUTHORIZENET_AMOUNT', $authorizeNetResponse['amount']);
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
    public function _displayCVVImages($params) {
	$cvv_images = $params->get('cvv_images', '');
	$img = '';
	if ($cvv_images) {
	    $img = $this->displayLogos($cvv_images);
	    $img = str_replace('"', "'", $img);
	}
	return $img;
    }

}

// No closing tag
