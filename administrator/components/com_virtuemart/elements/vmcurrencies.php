<?php

/**
 *
 * @package	VirtueMart
 * @subpackage Plugins  - Elements
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: $
 */
class JElementVmCurrencies extends JElement {

    /**
     * Element name
     *
     * @access	protected
     * @var		string
     */
    var $_name = 'Currencies';
   

    function fetchElement($name, $value, &$node, $control_name) {

        $db = & JFactory::getDBO();
        $query = 'SELECT `virtuemart_currency_id` AS value, `currency_name` AS text'
                . ' FROM `#__virtuemart_currencies` '
                . ' WHERE `virtuemart_vendor_id` = 1  AND `published` = 1 '
                . ' ORDER BY `currency_name` ASC '
        ;
        // default value should be vendor currency
        $db->setQuery($query);
        $currencies = $db->loadObjectList();
        if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
         $vendor_id = VirtueMartModelVendor::getLoggedVendor();
        if (empty($value)) {
            $currency=VirtueMartModelVendor::getVendorCurrency ($vendorId);
            $value= $currency->currency_id;
        }
        return JHTML::_('select.genericlist', $currencies, $control_name . '[' . $name . '][]', $class, 'value', 'text', $value, $control_name . $name);
    }

}