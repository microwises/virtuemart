<?php

if (!defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * Shipper plugin for standard shippers, like regular postal services
 *
 * @version $Id: standard.php 3220 2011-05-12 20:09:14Z Milbo $
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
class plgVmShipperWeight_countries extends vmShipperPlugin {

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
    function plgVmShipperWeight_countries(&$subject, $config) {
        $this->_selement = basename(__FILE__, '.php');
        $this->_createTable();
        parent::__construct($subject, $config);
    }

    /**
     * Create the table for this plugin if it does not yet exist.
     * @author Oscar van Eijk
     */
    protected function _createTable() {
        $scheme = DbScheme::get_instance();
        $scheme->create_scheme('#__vm_order_shipper_' . $this->_selement);
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
                'type' => 'text'
                , 'null' => false
            )
            , 'order_weight' => array(
                'type' => 'int'
                , 'length' => 11
                , 'null' => false
            )
        );
        $schemeIdx = array(
            'idx_order_shipping' => array(
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
     * Get the total weight for the order, based on which the proper shipping rate
     * can be selected.
     * @param object $cart Cart object
     * @return float Total weight for the order
     * @author Oscar van Eijk
     */
    protected function getOrderWeight(VirtueMartCart $cart) {
        $weight = 0;
        foreach ($cart->products as $prod) {
            $weight += ( $prod->product_weight * $prod->quantity);
        }
        return $weight;
    }

    /**
     * This event is fired during the checkout process. It allows the shopper to select
     * one of the available shippers.
     * It should display a radio button (name: shipper_id) to select the shipper. In the description,
     * the shipping cost can also be displayed, based on the total order weight and the shipto
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
        $orderWeight = $this->getOrderWeight($cart);

        foreach ($this->shippers as $shipper_id => $shipper_name) {
            if ($selectedShipper == $shipper_id) {
                $checked = '"checked"';
            } else {
                $checked = '';
            }

            $shipping_params = $this->getVmShipperParams($cart->vendorId, $shipper_id);
            $params = new JParameter($shipping_params);
            $countries_list = $params->get('countries');
            if (!is_array($countries_list)) {
                $countries[0] = $countries_list;
            } else {
                $countries = array();
                $countries = $countries_list;
            }
            $cond = "";
            if (!empty($orderWeight)) {
                $cond .= ' AND ((' . $orderWeight . " > " . $params->get('weight_start', 0) . " AND " . $orderWeight . " < " . $params->get('weight_end', 0) . ")";
                $cond .= ' OR  (' . $params->get('weight_start', 0) . " <= " . $orderWeight . '  ))';
            }
            if (!empty($address['zip'])) {
                $cond .= ' AND ((' . $address['zip'] . '> ' . $params->get('zip_start', 0) . ' AND ' . $address['zip'] . '< ' . $params->get('zip_end', 0) . ")";
                $cond .= ' OR  (' . $params->get('zip_start', 0) . ' <= ' . $address['zip'] . ' )) ';
            }

            if (in_array($address['virtuemart_country_id'], $countries) || count($countries)==0 ) {
                if ($cond) {
                    $cost = $params->get('rate_value', 0) + $params->get('package_fee', 0);

                    if (!class_exists('CurrencyDisplay'))
                        require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
                    $currency = CurrencyDisplay::getInstance();
                    $cost = $currency->convertCurrencyTo($params->get('currency'), $cost);
                    $shipping_rate_vat_id = $params->get('tax');
                    $shippingCost = $cost;
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

                    $shippingCostDisplay = $currency->priceDisplay($shippingCost);
                    $html .= '<input type="radio" name="shipper_id" id="shipper_id_' . $shipper_id . '" value="' . $shipper_id . '" ' . $checked . '>'
                            . '<label for="shipper_id_' . $shipper_id . '">' . $params->get('shipping_name') . " ($shippingCostDisplay)</label><br/>\n";
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
      public function plgVmOnShipperSelected($cart, $selectedShipper = 0 ) {
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

         $orderWeight = $this->getOrderWeight($cart);
        $address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

        $shipping_carrier_params = $this->getVmShipperParams($cart->vendorId, $selectedShipper);
        $params = new JParameter($shipping_carrier_params);

          $cost = $params->get('rate_value', 0) + $params->get('package_fee', 0);

        $shipping->shipping_currency_id = $params->get('currency');
        $shipping->shipping_name = $params->get('shipping_name');
        $shipping->shipping_rate_vat_id = $params->get('tax');
        $shipping->shipping_value = $cost;
        return true;

    }

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
/*
        $shp = '';
        $db = &JFactory::getDBO();
        $q = 'SELECT r.`shipping_rate_name` AS rate '
                . ',      c.`shipping_carrier_name` AS carrier '
                . 'FROM #__virtuemart_shippingrates r '
                . ',    #__virtuemart_shippingcarriers c '
                . 'WHERE r.`virtuemart_shippingrate_id` = ' . $this->getShippingRateIDForOrder($orderId) . ' '
                . 'AND   r.`shipping_rate_carrier_id` = c.`virtuemart_shippingcarrier_id` '
        ;
        $db->setQuery($q);
        $r = $db->loadAssoc();
        $shp .= $r['carrier'] . ' (' . $r['rate'] . ')';
        return $shp;
 * */
 
    }

    /**
     * Select the shipping rate ID, based on the selected shipper in combination with the
     * shipto address (country and zipcode) and the total order weight.
     * @param object $cart Cart object
     * @param int $shipperID Shipper ID, by default taken from the cart
     * @return int Shipping rate ID, -1 when no match is found. Only 1 selected ID will be returned;
     * if more ID's match, the cheapest will be selected.
     */
    protected function selectShippingRate(VirtueMartCart $cart, $selectedShipper = 0) {
        $orderWeight = $this->getOrderWeight($cart);
        if ($selectedShipper == 0) {
            $selectedShipper = $cart->shipper_id;
        }
        $address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

        $shipping_carrier_params = $this->getVmShipperParams($cart->vendorId, $selectedShipper);
        $params = new JParameter($shipping_carrier_params);

        $cost = $params->get('cost_first_product') +
                (($nbProducts > 1 ) ? ($nbProducts - 1) * $params->get('cost_next_products') : 0);

        /*
          $shipping['shipping_currency_id'] = $params->get('currency');
          $shipping['shipping_name'] = $params->get('name');
          $shipping['shipping_rate_vat_id'] = $params->get('tax');
          $shipping['shipping_value'] = $cost;
         */
        $shipping->shipping_currency_id = $params->get('currency');
        $shipping->shipping_name = $params->get('shipping_name');
        $shipping->shipping_rate_vat_id = $params->get('tax');
        $shipping->shipping_value = $cost;
        return true;
    }

}

// No closing tag
