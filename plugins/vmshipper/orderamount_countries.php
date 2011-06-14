<?php

if (!defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * Shipper plugin for standard shippers, like regular postal services
 *
 * @version $Id: orderamount_countries.php 3220 2011-05-12 20:09:14Z Milbo $
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
 * http://virtuemart.org
 */
class plgVmShipperOrderAmount_countries extends vmShipperPlugin {

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
    function plgVmShipperOrderAmount_countries(&$subject, $config) {
        $this->_selement = basename(__FILE__, '.php');
        $this->_createTable();
        parent::__construct($subject, $config);
        JPlugin::loadLanguage('plg_vmshipper_orderamount_countries', JPATH_ADMINISTRATOR);
    }

    /**
     * Create the table for this plugin if it does not yet exist.
     * @author Oscar van Eijk
     */
    protected function _createTable() {
        $scheme = DbScheme::get_instance();
        $scheme->create_scheme('#__virtuemart_order_shipping_' . $this->_selement);
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
                , 'null' => false
            )
            , 'order_amount' => array(
                'type' => 'int'
                , 'length' => 11
                , 'null' => false
            )
            , 'shipper_cost' => array(
                'type' => 'text'
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
     * Get the total amount for the order, based on which the proper shipping rate
     * can be selected.
     * @param object $cart Cart object
     * @return float Total amount for the order
     * @author Valerie isakesn
     */
    protected function getOrderAmount(VirtueMartCart $cart) {
// THIS IS WRONG
         $orderAmount=0;
        foreach ($cart->products as $product) {
            $orderAmount += ( $product->product_price * $product->quantity);
        }
        return $orderAmount;
    }

    /**
     * This event is fired during the checkout process. It allows the shopper to select
     * one of the available shippers.
     * It should display a radio button (name: shipper_id) to select the shipper. In the description,
     * the shipping cost can also be displayed, based on the total order amount and the shipto
     * country (this wil be calculated again during order confirmation)
     *
     * @param object $cart the cart object
     * @param integer $selected ID of the shipper currently selected
     * @return HTML code to display the form
     * @author Valérie Isaksen
     */
    public function plgVmOnSelectShipper(VirtueMartCart $cart, $selectedShipper = 0) {
        if ($this->getShippers($cart->vendorId) === false) {
            if (empty($this->_name)) {
                $app = JFactory::getApplication();
                $app->enqueueMessage(JText::_('COM_VIRTUEMART_CART_NO_CARRIER'));
                return;
            } else {
                //return JText::sprintf('COM_VIRTUEMART_SHIPPER_NOT_VALID_FOR_THIS_VENDOR', $this->_name , $cart->vendorId );
                return;
            }
        }
        $address = (($cart->ST == 0) ? $cart->BT : $cart->ST);
        $orderAmount = $this->getOrderAmount($cart);
        $html = "";
        $countries = array();
        foreach ($this->shippers as $shipper_id => $shipper_name) {
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

            $cond = ' ((' . $orderAmount . " > " . $params->get('orderamount_start', 0) . " AND " . $orderAmount . " < " . $params->get('orderamount_end', 0) . ")";
            $cond .= ' OR  (' . $params->get('orderamount_start', 0) . " <= " . $orderAmount . '  ))';

              if (in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
                if ($cond) {
                    $cost = $params->get('shipping_value');
                    $html = $this->getShippingHtml($shipper_name, $shipper_id, $selectedShipper, $params->get('shipper_logo'), $cost, $params->get('tax_id'), $params->get('currency_id'));
                }
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
/*    public function plgVmOnShipperSelectedCalculatePrice($cart, $shipping) {
 /*       if (!$this->selectedThisShipper($this->_selement, $selectedShipper)) {
            return null; // Another shipper was selected, do nothing
        }

        $address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

        $shipping_carrier_params = $this->getVmShipperParams($cart->vendorId, $selectedShipper);
        $params = new JParameter($shipping_carrier_params);

        $shipping->shipping_currency_id = $params->get('currency_id');
        $shipping->shipping_name = $this->getThisShipperName($selectedShipper);
        $shipping->shipping_rate_vat_id = $params->get('tax_id');
        $shipping->shipping_value =  $params->get('shipping_value');
        return true;
       	
    } 
    */

    /**
     * This method is fired when showing the order details in the frontend.
     * It displays the shipper-specific data.
     *
     * @param integer $orderId The order ID
     * @return mixed Null for shippers that aren't active, text (HTML) otherwise
     * @author Valérie Isaksen
     */
    public function plgVmOnShowOrderShipperFE($orderId) {
        if (!($this->selectedThisShipper($this->_selement, $this->getShipperIDForOrder($orderId)))) {
            return null;
        }
    }

    /**
     * Select the shipping rate ID, based on the selected shipper in combination with the
     * shipto address (country and zipcode) and the total order amount.
     * @param object $cart Cart object
     * @param int $shipperID Shipper ID, by default taken from the cart
     * @return int Shipping rate ID, -1 when no match is found. Only 1 selected ID will be returned;
     * if more ID's match, the cheapest will be selected.
     */
    protected function selectShippingRate(VirtueMartCart $cart, $selectedShipper = 0) {
 
        if ($selectedShipper == 0) {
            $selectedShipper = $cart->virtuemart_shippingcarrier_id;
        }
        $address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

        $shipping_carrier_params = $this->getVmShipperParams($cart->vendorId, $selectedShipper);
        $params = new JParameter($shipping_carrier_params);

        $shipping->shipping_name = $params->get('shipping_name');
        $shipping->shipping_rate_vat_id = $params->get('tax_id');
        $shipping->shipping_value =  $params->get('shipping_value');
        return true;
    }

    /**
     * This event is fired after the order has been stored; it gets the shipping method-
     * specific data.
     *
     * @param int $order_id The order_id being processed
     * @param object $cart  the cart
     * @param array $priceData Price information for this order
     * @return mixed Null when this method was not selected, otherwise true
     * @author Valerie Isaksen
     */
    function plgVmOnConfirmedOrderStoreShipperData($order_id, $cart, $priceData) {

        if (!($this->selectedThisShipper($this->_selement, $cart->virtuemart_shippingcarrier_id))) {
            return null;
        }
        $values['virtuemart_order_id'] = $order_id;
        $values['shipper_id'] = $cart->virtuemart_shippingcarrier_id;
        $values['shipper_name'] = $this->getThisShipperName($cart->virtuemart_shippingcarrier_id);
        $values['shipper_cost'] = $params->get('shipper_cost');
        $values['tax_id'] = $params->get('tax_id');

        $this->writeShipperData($values, '#__virtuemart_order_shipper_' . $this->_selement);
        return true;
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
                . '		<td class="key">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_CARRIER_LBL') . ': </td>' . "\n"
                . '		<td align="left">' . $shipInfo->shipper_name . '</td>' . "\n"
                . '	</tr>' . "\n"
                . '	<tr>' . "\n"
                . '		<td class="key">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_MODE_LBL') . ': </td>' . "\n"
                . '		<td>' . $shipInfo->shipper_cost . '</td>' . "\n"
                . '	</tr>' . "\n"
                . '	<tr>' . "\n"
                . '		<td class="key">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_MODE_LBL') . ': </td>' . "\n"
                . '		<td>' . $shipInfo->tax . '</td>' . "\n"
                . '	</tr>' . "\n"
                . '	<tr>' . "\n"
                . '		<td class="key">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_MODE_LBL') . ': </td>' . "\n"
                . '		<td>' . $shipInfo->currency . '</td>' . "\n"
                . '	</tr>' . "\n"
                . '</table>' . "\n"
        ;
        return $html;
    }
 

}

// No closing tag
