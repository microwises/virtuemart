<?php

if (!defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * Shipper plugin for products_zone shippers, like regular postal services
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
 * http://virtuemart.net
 */
class plgVmShipperProducts_zone extends vmShipperPlugin {

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
    function plgVmShipperProducts_zone(&$subject, $config) {
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
     * This event is fired during the checkout process. It allows the shopper to select
     * one of the available shippers.
     * It should display a radio button (name: shipper_id) to select the shipper. In the description,
     * the shipping cost can also be displayed, based on the total order weight and the shipto
     * country (this wil be calculated again during order confirmation)
     *
     * @param object $_cart the cart object
     * @param integer $_selected ID of the shipper currently selected
     * @return HTML code to display the form
     * @author Oscar van Eijk
     */
    function plgVmOnSelectShipper($cart, $selectedShipper = 0) {

        $html = '';

        if ($selectedShipper == $id) {
            $checked = '"checked"';
        } else {
            $checked = '';
        }
        $nbProducts = $this->_getNbProducts($cart);
        $i = 1;
        $cost = 0;
        $found = false;
        $address = (($cart->ST == 0) ? $cart->BT : $cart->ST);
        // @TODO more intellligen tllop
       for ($i = 1; $i < 5; $i++) {
           // @TODO take the parameters from Virtuemart tables not joomla plugins
            $zone_countries = explode("|", $this->params->get('zone_countries_' . $i));
            if (in_array($address['virtuemart_country_id'], $zone_countries)) {
                $cost = $this->params->get('zone_cost_first_product_' . $i) +
                        (($nbProducts > 1 ) ? ($nbProducts - 1) * $this->params->get('zone_cost_next_product_' . $i) : 0);
                $found = true;
                break;
            }
        }
        if ($found) {
            if (!class_exists('CurrencyDisplay'))
                require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
            $currency = CurrencyDisplay::getInstance();

            $price = $currency->priceDisplay($cost);
            //$rateID = $this->selectShippingRate($cart, $id);
            //$price = $this->getShippingRate($_rateID);
            $html = "<fieldset>\n";
            $html .= '<input type="radio" name="shipper_id" id="shipper_id_' . $id . '" value="' . $_id . '" ' . $checked . '>'
                    . '<label for="shipper_id_' . $id . '">' . $this->params->get('zone_name_' . $i) . " ($price)</label><br/>\n";
            $html .= "</fieldset>\n";
        }

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
            if ($_prod->product_weight) {
                $nbProducts += $product->quantity;
            }
        }
        return $nbProducts;
    }

}

// No closing tag


