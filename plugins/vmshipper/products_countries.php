<?php

if (!defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * Shipper plugin for products_countries shippers, like regular postal services
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
class plgVmShipperProducts_countries extends vmShipperPlugin {

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
    function plgVmShipperProducts_countries(&$subject, $config) {
        $this->_selement = basename(__FILE__, '.php');
        $this->_createTable();
        parent::__construct($subject, $config);
    }

    /**
     * Create the table for this plugin if it does not yet exist.
     * @author Oscar van Eijk
     */
    protected function _createTable() {
        $_scheme = DbScheme::get_instance();
        $_scheme->create_scheme('#__vm_order_shipper_' . $this->_selement);
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
            , 'shipper_id' => array(
                'type' => 'text'
                , 'null' => false
            )
            , 'nb_products' => array(
                'type' => 'int'
                , 'length' => 11
                , 'null' => false
            )
            , 'cost' => array(
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
        $_schemeIdx = array(
            'idx_order_shipping' => array(
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
        $i = 1;
        $nbProducts = $this->_getNbProducts($cart);
        $address = (($cart->ST == 0) ? $cart->BT : $cart->ST);
        foreach ($this->shippers as $shipper_id => $shipper_name) {
            if ($selectedShipper == $shipper_id) {
                $checked = '"checked"';
            } else {
                $checked = '';
            }
            $i = 1;
            $cost = 0;
            $found = false;
            $shipping_params = $this->getVmShipperParams($cart->vendorId, $shipper_id);
            $params = new JParameter($shipping_params);
            $countries_list = $params->get('countries');
            if (!is_array($countries_list)) {
                $countries[0] = $countries_list;
            } else {
                $countries = array();
                $countries = $countries_list;
            }
            if (in_array($address['virtuemart_country_id'], $countries)) {
                $cost = $params->get('cost_first_product') +
                        (($nbProducts > 1 ) ? ($nbProducts - 1) * $params->get('cost_next_products') : 0);

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

                //$rateID = $this->selectShippingRate($cart, $id);
                //$price = $this->getShippingRate($_rateID);

                $html .= '<input type="radio" name="shipper_id" id="shipper_id_' . $shipper_id . '" value="' . $shipper_id . '" ' . $checked . '>'
                        . '<label for="shipper_id_' . $shipper_id . '">' . $params->get('shipping_name') . " ($shippingCostDisplay)</label><br/>\n";
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

        $nbProducts = $this->_getNbProducts($cart);
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

    /**
     * Fill the array with all carriers found with this plugin for the current vendor
     * @return virtuemart_shippingcarrier_id
     * @author Valerie Isaksen
     */
    function getShippingCarrierId($vendorId) {
        $db = &JFactory::getDBO();
        if (VmConfig::isJ15()) {
            $q = 'SELECT v.`virtuemart_shippingcarrier_id`   AS id '
                    . ',      v.`shipping_carrier_name` AS name '
                    . 'FROM   #__virtuemart_shippingcarriers v '
                    . ',      #__plugins             j '
                    . 'WHERE j.`element` = "' . $this->_selement . '" '
                    . 'AND   v.`shipping_carrier_jplugin_id` = j.`id` '
                    . 'AND  (v.`virtuemart_vendor_id` = "' . $vendorId . '" '
                    . ' OR   v.`virtuemart_vendor_id` = "0") '
            ;
        } else {
            $q = 'SELECT v.`virtuemart_shippingcarrier_id`   AS id '
                    . ',      v.`shipping_carrier_name` AS name '
                    . 'FROM   #__virtuemart_shippingcarriers AS v '
                    . ',      #__extensions    AS      j '
                    . 'WHERE j.`folder` = "vmshipper" '
                    . 'AND j.`element` = "' . $this->_selement . '" '
                    . 'AND   v.`shipping_carrier_jplugin_id` = j.`extension_id` '
                    . 'AND  (v.`virtuemart_vendor_id` = "' . $vendorId . '" '
                    . ' OR   v.`virtuemart_vendor_id` = "0") '
            ;
        }


        $db->setQuery($q);
        if (!$result = $db->loadResult()) {
//			$app = JFactory::getApplication();
//			$app->enqueueMessage(JText::_('COM_VIRTUEMART_CART_NO_CARRIER'));
            return false;
        }

        return $result;
    }

}

// No closing tag




