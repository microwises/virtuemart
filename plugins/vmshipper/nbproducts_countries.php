<?php

if (!defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * Shipper plugin for nbproducts_countries shippers, like regular postal services
 *
 * @version $Id:  3220 2011-05-12 20:09:14Z Milbo $
 * @package VirtueMart
 * @subpackage Plugins - shippper
 * @copyright Copyright (C) 2004-2011 VirtueMart Team - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
class plgVmShipperNbProducts_countries extends vmShipperPlugin {

    /**
     * Constructor
     *
     * For php4 compatability we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @param object $subject The object to observe
     * @param array  $config  An array that holds the plugin configuration
     */
    function plgVmShipperNbProducts_countries(&$subject, $config) {
        $this->_selement = basename(__FILE__, '.php');
        $this->_createTable();
        parent::__construct($subject, $config);
        JPlugin::loadLanguage('plg_vmshipper_nbproducts_countries', JPATH_ADMINISTRATOR);
    }

    /**
     * Create the table for this plugin if it does not yet exist.
     * @author Oscar van Eijk
     */
    protected function _createTable() {

        $scheme = DbScheme::get_instance();
        $scheme->create_scheme('#__virtuemart_order_shipper_' . $this->_selement);
        $schemeCols = array(
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
            , 'shipper_id' => array(
                'type' => 'int'
                , 'length' => 11
                , 'null' => false
            )
            , 'shipper_name' => array(
                'type' => 'text'
                , 'length' => 11
                , 'null' => false
            )
            , 'nb_products' => array(
                'type' => 'int'
                , 'length' => 11
                , 'null' => false
            )
            , 'cost_first_product' => array(
                'type' => 'int'
                , 'length' => 11
                , 'null' => false
            )
            , 'cost_next_product' => array(
                'type' => 'int'
                , 'length' => 11
                , 'null' => false
            )
            , 'cost_limit' => array(
                'type' => 'int'
                , 'length' => 11
                , 'null' => false
            )
            , 'currency_id' => array(
                'type' => 'int'
                , 'length' => 11
                , 'null' => false
            )
            , 'tax_id' => array(
                'type' => 'int'
                , 'length' => 11
                , 'null' => false
            )
        );
        $schemeIdx = array(
            'idx_order_shipper' => array(
                'columns' => array('virtuemart_order_id')
                , 'primary' => false
                , 'unique' => false
                , 'type' => null
            )
        );
        $scheme->define_scheme($schemeCols);
        $scheme->define_index($schemeIdx);
        if (!$scheme->scheme(true)) {
            JError::raiseWarning(500, $scheme->get_db_error());
        }
        $scheme->reset();
    }

    /**
     * This event is fired during the checkout process. It allows the shopper to select
     * one of the available shippers.
     * It should display a radio button (name: shipper_id) to select the shipper. In the description,
     * the shipping cost can also be displayed, based on the total order weight and the shipto
     * country (this wil be calculated again during order confirmation)
     *
     * @param object $_cart the cart object
     * @param integer $_selected ID of the shipper currently selected
     * @return HTML code to display the form
     * @author Valérie Isaksen
     */
    function plgVmOnSelectShipper($cart, $selectedShipper = 0) {

        if (( $this->getShippers($cart->vendorId)) === false) {
            return false;
        }
      
        $html = '';
        $nbProducts = $this->_getNbProducts($cart);
        $address = (($cart->ST == 0) ? $cart->BT : $cart->ST);
         $countries=array();
        foreach ($this->shippers as $shipper_id => $shipper_name) {
            $cost = 0;
            $found = false;
            $shipping_params = $this->getVmShipperParams($cart->vendorId, $shipper_id);
            $params = new JParameter($shipping_params);
              $country_list = $params->get('countries');
            if (!empty($country_list)) {
                if (!is_array($country_list)) {
                    $countries[0] = $country_list;
                } else {
                    $countries = $country_list;
                }
            }
            if (in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
                $cost = $this->_getShippingCost($nbProducts, $params); // converted in vendor currency
                $html = $this->getShippingHtml($params->get('rate_name'), $shipper_id, $selectedShipper, $params->get('shipper_logo'), $cost, $params->get('tax_id'), $params->get('currency_id'));
            }
        }
        return $html;
    }

    /**
     * This event is fired after the shipping method has been selected. It can be used to store
     * additional shipper info in the cart.
     *
     * @param object $cart Cart object
     * @param integer $selected ID of the shipper selected
     * @return boolean True on succes, false on failures, null when this plugin was not selected.
     * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
     * @author Valérie Isaksen
     */
    public function plgVmOnShipperSelected($cart, $selectedShipper = 0) {
        if (!$this->selectedThisShipper($this->_selement, $selectedShipper)) {
            return null; // Another shipper was selected, do nothing
        } else {
            return true;
        }
    }

    /**
     * This event is fired after the shipping method has been selected. It can be used to store
     * additional shipper info in the cart.
     *
     * @param object $cart Cart object
     * @param integer $selected ID of the shipper selected
     * @return boolean True on succes, false on failures, null when this plugin was not selected.
     * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
     * @author Valérie Isaksen
     */
    public function plgVmOnShipperSelectedCalculatePrice($cart, $selectedShipper = 0, &$shipping) {
        if (!$this->selectedThisShipper($this->_selement, $selectedShipper)) {
            return null; // Another shipper was selected, do nothing
        }

        $nbProducts = $this->_getNbProducts($cart);
        $shipping_carrier_params = $this->getVmShipperParams($cart->vendorId, $selectedShipper);
        $params = new JParameter($shipping_carrier_params);

        $shipping->shipping_name = $this->getThisShipperName($selectedShipper);
        $shipping->shipping_currency_id = $params->get('currency_id');
        $shipping->shipping_rate_vat_id = $params->get('tax_id');
        $shipping->shipping_value = $this->_getShippingCostFromNbPRoducts($nbProducts, $params);
        return true;
    }

    /**
     * This event is fired after the order has been stored; it stores the shipping method-
     * specific data.
     *
     * @param int $orderNr The ordernumber being processed
     * @param object $orderData Data from the cart
     * @param array $priceData Price information for this order
     * @return mixed Null when this method was not selected, otherwise the new order status
     * @author Valerie Isaksen
     */
    function plgVmOnConfirmedOrderStoreShipperData($order_id, $cart, $priceData) {
        if (!($this->selectedThisShipper($this->_selement, $cart->virtuemart_shippingcarrier_id))) {
            return null;
        }
        $nbProducts = $this->_getNbProducts($cart);
        $shipping_carrier_params = $this->getVmShipperParams($cart->vendorId, $cart->virtuemart_shippingcarrier_id);
        $params = new JParameter($shipping_carrier_params);

        $values['virtuemart_order_id'] = $order_id;
        $values['shipper_id'] = $cart->virtuemart_shippingcarrier_id;
        $values['shipper_name'] = $this->getThisShipperName($cart->virtuemart_shippingcarrier_id);
        $values['nb_products'] = $nbProducts;
        $values['cost_first_product'] = $params->get('cost_first_product');
        $values['cost_next_products'] = $params->get('cost_next_products');
        $values['cost_limit'] = $params->get('cost_limit');
        $values['currency_id'] = $params->get('currency_id');
        $values['tax_id'] = $params->get('tax_id');
        $this->writeShipperData($values, '#__virtuemart_order_shipper_' . $this->_selement);
    }

    /**
     * This method is fired when showing the order details in the backend.
     * It displays the shipper-specific data.
     * NOTE, this plugin should NOT be used to display form fields, since it's called outside
     * a form! Use plgVmOnUpdateOrderBE() instead!
     *
     * @param integer $_orderId The order ID
     * @param integer $_vendorId Vendor ID
     * @param object $_shipInfo Object with the properties 'carrier' and 'name'
     * @return mixed Null for shippers that aren't active, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    public function plgVmOnShowOrderShipperBE($virtuemart_order_id, $vendorId, $ship_method_id) {
        if (!($this->selectedThisShipper($this->_selement, $ship_method_id))) {
            return null;
        }
        $db = JFactory::getDBO();
        $q = 'SELECT * FROM `#__virtuemart_order_shipper_' . $this->_selement . '` '
                . 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
        $db->setQuery($q);
        if (!($shipinfo = $db->loadObject())) {
            JError::raiseWarning(500, $q . " " . $db->getErrorMsg());
            return '';
        }
        if (!class_exists('CurrencyDisplay')

            )require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
        $currency = CurrencyDisplay::getInstance();  //Todo, set currency of shopper or user?
//		$_currency = VirtueMartModelVendor::getCurrencyDisplay($_vendorId);
        $html = '<table class="admintable">' . "\n"
                . '	<thead>' . "\n"
                . '		<tr>' . "\n"
                . '			<td class="key" style="text-align: center;" colspan="2">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_LBL') . '</td>' . "\n"
                . '		</tr>' . "\n"
                . '	</thead>' . "\n"
                . '	<tr>' . "\n"
                . '		<td class="key">' . JText::_('VMSHIPPER_NBPRODUCTS_COUNTRIES_NB_PRODUCTS') . ': </td>' . "\n"
                . '		<td>' . $shipInfo->nb_products . '</td>' . "\n"
                . '	</tr>' . "\n"
                . '	<tr>' . "\n"
                . '		<td class="key">' . JText::_('VMSHIPPER_NBPRODUCTS_COUNTRIES_COST_FIRST_PRODUCT') . ': </td>' . "\n"
                . '		<td>' . $shipInfo->cost_first_product . '</td>' . "\n"
                . '	</tr>' . "\n"
                . '	<tr>' . "\n"
                . '		<td class="key">' . JText::_('VMSHIPPER_NBPRODUCTS_COUNTRIES_COST_NEXT_PRODUCTS') . ': </td>' . "\n"
                . '		<td>' . $shipInfo->cost_next_products . '</td>' . "\n"
                . '	</tr>' . "\n"
                . '	<tr>' . "\n"
                . '		<td class="key">' . JText::_('VMSHIPPER_NBPRODUCTS_COUNTRIES_COST_LIMIT') . ': </td>' . "\n"
                . '		<td>' . $shipInfo->cost_limit . '</td>' . "\n"
                . '	</tr>' . "\n"
                . '	<tr>' . "\n"
                . '		<td class="key">' . JText::_('VMSHIPPER_NBPRODUCTS_COUNTRIES_TAX_ID') . ': </td>' . "\n"
                . '		<td>' . $shipInfo->tax . '</td>' . "\n"
                . '	</tr>' . "\n"
                . '	<tr>' . "\n"
                . '		<td class="key">' . JText::_('VMSHIPPER_NBPRODUCTS_COUNTRIES_CURRENCY_ID') . ': </td>' . "\n"
                . '		<td>' . $shipInfo->currency . '</td>' . "\n"
                . '	</tr>' . "\n"
                . '</table>' . "\n"
        ;
        return $html;
    }

    /**
     * Get the number of products for the order, based on which the proper shipping rate
     * can be selected.
     * @param object $cart Cart object
     * @return   Total nb of products for the order
     * @author Valerie Isaksen
     */
    function _getNbProducts($cart) {

        $nbProducts = 0;
        foreach ($cart->products as $product) {
            if ($product->product_weight) {
                $nbProducts += $product->quantity;
            }
        }
        return $nbProducts;
    }

    /*
     * Get Cost With tax, Currency Converted
     */

    function _getShippingCostWithTax($shippingCost, $shipping_rate_vat_id) {

        if (!class_exists('CurrencyDisplay'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');

        if (!empty($shipping_rate_vat_id)) {
            $taxrules = array();
            if (!class_exists('calculationHelper'))
                require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
            $calculator = calculationHelper::getInstance();
            $db = &JFactory::getDBO();
            $q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $shipping_rate_vat_id . '" ';
            $db->setQuery($q);
            $taxrules = $db->loadAssocList();
            if (count($taxrules) > 0) {
                $shippingCost = $calculator->roundDisplay($calculator->executeCalculation($taxrules, $cost));
            }
        }

        return $shippingCost;
    }

    /*
     * Get shipping cost from $nb products, $params
     */

    function _getShippingCost($nbProducts, $params) {
        $shippingCost = $params->get('cost_first_product') + ($nbProducts - 1) * $params->get('cost_next_products');
        if ($params->get('cost_limit', 0)) {
            $shippingCost = min($shippingCost, $params->get('cost_limit'));
        }
        return $shippingCost;
    }

}

// No closing tag




