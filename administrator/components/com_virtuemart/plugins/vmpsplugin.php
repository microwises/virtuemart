<?php

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
	$this->psType = substr($this->_type, 2);
	$this->_createTable();
    }
  
    /*
     * plgVmOnCheckAutomaticSelectedPlugin
     * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
     * @author Valerie Isaksen
     * @param VirtueMartCart cart: the cart object
     * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
     *
     *
     */

    function plgVmOnCheckAutomaticSelectedPlugin(VirtueMartCart $cart, array $cart_prices) {

	$nbPlugin = 0;
	$virtuemart_pluginmethod_id = 0;
	$function = 'getSelectable' . ucFirst($this->psType);
	$nbPlugin = $this->$function($cart, $cart_prices, $virtuemart_pluginmethod_id);
	if ($nbPlugin == null)
	    return null;
	return ($nbPlugin == 1) ? $virtuemart_pluginmethod_id : 0;
    }

    function getDebug() {
	return $this->_debug;
    }

    function setDebug($params) {
	return $this->_debug = $params->get('debug');
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

    /**
     * Overwrites the standard function in vmplugin. Extendst the input data by virtuemart_order_id
     * Calls the parent to execute the write operation
     *
     * @author Max Milbers
     * @param array $_values
     * @param string $_table
     */
    protected function storePluginInternalData($_values) {
	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
	if (!isset($_values['virtuemart_order_id'])) {
	    $_values['virtuemart_order_id'] = VirtueMartModelOrders::getOrderIdByOrderNumber($_values['order_number']);
	}
	parent::storePluginInternalData($_values);
    }

    /**
     * Something went wrong, Send notification to all administrators
     * @param string subject of the mail
     * @param string message
     */
    protected function sendEmailToVendorAndAdmins($subject, $message) {
	// recipient is vendor and admin
	$vendorId = 1;
	if (!class_exists('VirtueMartModelVendor'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
	$vendorModel = new VirtueMartModelVendor();
	$vendorEmail = $vendorModel->getVendorEmail($vendorId);
	$vendorName = $vendorModel->getVendorName($vendorId);
	JUtility::sendMail($vendorEmail, $vendorName, $vendorEmail, $subject, $message);
	if (VmConfig::isJ15()) {
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
	    $url = JURI::root() . 'images/stories/virtuemart/' . $this->psType . '/';
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

    protected function getPluginName($plugin) {
	$return = '';
	$plugin_params = $this->psType . '_params';
	$plugin_name = $this->psType . '_name';
	$params = new JParameter($plugin->$plugin_params);
	$logo = $params->get($this->psType . '_logos');
	$description = $params->get($this->psType . '_description', '');
	if (!empty($logo)) {
	    $return = $this->displayLogos($logo) . ' ';
	}
	if (!empty($description)) {
	    $description = '<span class="' . $this->_type . '_description">' . $description . '</span>';
	}
	return $return . '<span class="' . $this->_type . '_name">' . $plugin->$plugin_name . '</span>' . $description;
    }

    protected function getPluginHtml($plugin, $selectedPlugin, $pluginSalesPrice) {
	$pluginmethod_id = 'virtuemart_' . $this->psType . 'method_id';
	$pluginName = $this->psType . '_name';
	if ($selectedPlugin == $plugin->$pluginmethod_id) {
	    $checked = 'checked';
	} else {
	    $checked = '';
	}

	if (!class_exists('CurrencyDisplay'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
	$currency = CurrencyDisplay::getInstance();
	/*
	  $html = '<input type="radio" name="virtuemart_paymentmethod_id" id="payment_id_' . $payment->virtuemart_paymentmethod_id . '" value="' . $payment->virtuemart_paymentmethod_id . '" ' . $checked . '>'
	  . '<label for="payment_id_' . $payment->virtuemart_paymentmethod_id . '">' .'<span class="vmpayment">'. $payment->payment_name . '<span class="vmpayment_cost">(' . $paymentCostDisplay . ")</span></span></label>\n";
	  $html .="\n";
	 */


	$costDisplay = $currency->priceDisplay($pluginSalesPrice);
	$html = '<input type="radio" name="' . $pluginmethod_id . '" id="' . $this->psType . '_id"  " value="' . $plugin->$pluginmethod_id . '" ' . $checked . '>'
		. '<label for="' . $this->psType . '_id_' . $plugin->$pluginmethod_id . '">' . '<span class="' . $this->_type . '">' . $plugin->$pluginName . '<span class="' . $this->_type . '_cost"> (' . $costDisplay . ")</span></span></label>\n";
	return $html;
    }

    /*
     *
     */

    protected function getHtmlHeaderBE() {
	$class = "class='key'";
	$html = ' 	<thead>' . "\n"
		. '		<tr>' . "\n"
		. '			<td ' . $class . ' style="text-align: center;" colspan="2">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_' . $this->psType . '_LBL') . '</td>' . "\n"
		. '		</tr>' . "\n"
		. '	</thead>' . "\n";

	return $html;
    }

    /*
     *
     */

    protected function getHtmlRow($key, $value, $class='') {
	$lang = & JFactory::getLanguage();
	$key_text = '';
	$complete_key = strtoupper($this->_type . '_' . $key);
	// vmdebug('getHtmlRow',$key,$complete_key);
	if ($lang->hasKey($complete_key)) {
	    $key_text = JText::_($complete_key);
	}
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

    /**
     * @author Valerie Isaksen
     * @param int $plugin_id The plugin method ID

     * @return plugin table
     */
    final protected function getPluginMethod($plugin_id) {
	$db = JFactory::getDBO();

// 		$q = 'SELECT * FROM #__virtuemart_shipmentmethods WHERE `virtuemart_shipmentmethod_id`="' . $shipment_id . '" AND `shipment_element` = "'.$this->_name.'"';
	$q = 'SELECT * FROM #__virtuemart_' . $this->psType . 'methods WHERE `virtuemart_' . $this->psType . 'method_id`="' . $plugin_id . '" ';

	$db->setQuery($q);
	return $db->loadObject();
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

	$q = $select . ' FROM   #__virtuemart_' . $this->psType . 'methods AS v ';

	$q.= ' LEFT JOIN ' . $extPlgTable . ' as j ON j.`' . $extField1 . '` =  v.`' . $this->psType . '_jplugin_id` ';
	$q.= ' LEFT OUTER JOIN #__virtuemart_' . $this->psType . 'method_shoppergroups AS s ON v.`virtuemart_' . $this->psType . 'method_id` = s.`virtuemart_' . $this->psType . 'method_id` ';
	$q.= ' WHERE v.`published` = "1" AND j.`' . $extField2 . '` = "' . $this->_name . '"
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

	return $results;
    }

    /*
     * which plugin is selected
     * @param int $pluginmethod_id
     * return $plugin if found
     * return null otherwise
     *
     * @author Valérie Isaksen
     */

    function pluginSelected($pluginmethod_id) {
	$plugins = $this->psType . 's';
	$virtuemart_pluginmethod_id = 'virtuemart_' . $this->psType . 'method_id';
	foreach ($this->$plugins as $plugin) {
	    if ($plugin->$virtuemart_pluginmethod_id == $pluginmethod_id) {
		return $plugin;
	    }
	}
	return null;
    }

    /**
     * Get Plugin Data for a go given plugin ID
     * @author Valérie Isaksen
     * @param int $virtuemart_shipmentmethod_id The Shipment ID
     * @return  Shipment data
     */
    final protected function getThisPluginData($pluginmethod_id) {
	$virtuemart_pluginmethod_id = 'virtuemart_' . $this->psType . 'method_id';
	$db = JFactory::getDBO();
	$q = 'SELECT * '
		. 'FROM #__virtuemart_' . $this->psType . 'methods '
		. "WHERE `" . $virtuemart_pluginmethod_id . "` ='" . $pluginmethod_id . "' ";
	$db->setQuery($q);
	return $db->loadObject();
    }

    /**
     *
     */
    function getOrderPluginNamebyOrderId($virtuemart_order_id) {

	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
	$db->setQuery($q);
	if (!($order_plugin = $db->loadObject())) {
	    return null;
	}
	$plugin_name = $this->psType . '_name';
	return $order_plugin->$plugin_name;
    }

    function getOrderPluginName($order_number, $pluginmethod_id) {

	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `order_number` = "' . $order_number . '"  AND `' . $this->pstype . '_id` =' . $pluginmethod_id;
	$db->setQuery($q);
	if (!($order = $db->loadObject())) {
	    return null;
	}
	JFactory::getLanguage()->load('com_virtuemart');
	$plugin_name = $this->psType . '_name';
	return $order_plugin->$plugin_name;
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

    function setCartPrices(&$cart_prices, $value, $tax_id) {
	$psType = ucfirst($this->psType);
	$cart_prices[$this->psType . 'Value'] = $value;

	$taxrules = array();
	if (!empty($payment_tax_id)) {
	    $db = JFactory::getDBO();
	    $q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $tax_id . '" ';
	    $db->setQuery($q);
	    $taxrules = $db->loadAssocList();
	}
	if (!class_exists('calculationHelper'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
	$calculator = calculationHelper::getInstance();
	if (count($taxrules) > 0) {
	    $cart_prices['salesPrice' . $psType] = $calculator->roundDisplay($calculator->executeCalculation($taxrules, $cart_prices[$this->psType . 'Value']));
	    $cart_prices[$this->psType . 'Tax'] = $calculator->roundDisplay($cart_prices['salesPrice' . $psType]) - $cart_prices[$this->psType . 'Value'];
	} else {
	    $cart_prices['salesPrice' . $psType] = $value;
	    $cart_prices[$this->psType . 'Tax'] = 0;
	}
    }

    /*
     * calculateSalesPriceShipment
     * @param $shipment_value
     * @param $tax_id: tax id
     * @return $salesPriceShipment
     */

    protected function calculateSalesPrice($value, $tax_id) {

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
	    $salesPrice = $calculator->roundDisplay($calculator->executeCalculation($taxrules, $value));
	} else {
	    $salesPrice = $value;
	}

	return $salesPrice;
    }

    /**
     * validateVendor
     * Check if this plugin has methods for the current vendor.
     * @author Oscar van Eijk
     * @param integer $_vendorId The vendor ID taken from the cart.
     * @return True when a shipment_id was found for this vendor, false otherwise
     *
     * @deprecated ????
     */
    protected function validateVendor($_vendorId) {

	if (!$_vendorId) {
	    $_vendorId = 1;
	}

	$_db = JFactory::getDBO();

	if (VmConfig::isJ15()) {
	    $_q = 'SELECT 1 '
		    . 'FROM   #__virtuemart_' . $this->psType . 'methods v '
		    . ',      #__plugins             j '
		    . 'WHERE j.`element` = "' . $this->_name . '" '
		    . 'AND   v.`' . $this->psType . '_jplugin_id` = j.`id` '
		    . 'AND   v.`virtuemart_vendor_id` = "' . $_vendorId . '" '
		    . 'AND   v.`published` = 1 '
	    ;
	} else {
	    $_q = 'SELECT 1 '
		    . 'FROM   #__virtuemart_' . $this->psType . 'methods AS v '
		    . ',      #__extensions   AS     j '
		    . 'WHERE j.`folder` = "' . $this->_type . '" '
		    . 'AND j.`element` = "' . $this->_name . '" '
		    . 'AND   v.`' . $this->psType . '_jplugin_id` = j.`extension_id` '
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

}