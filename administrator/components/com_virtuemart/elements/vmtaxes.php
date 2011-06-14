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
if (!class_exists('VmConfig'))
    require(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');
class JElementVmTaxes extends JElement {

    /**
     * Element name
     * @access	protected
     * @var		string
     */
    var $_name = 'taxes';

    function fetchElement($name, $value, &$node, $control_name) {

        $db =  JFactory::getDBO();
        $nullDate = $db->getNullDate();
        $now = JFactory::getDate()->toMySQL();
        $q = 'SELECT   `virtuemart_calc_id` AS value, `calc_name` AS text FROM `#__virtuemart_calcs` WHERE    ';
        $q .= ' `calc_kind`="TAX" OR `calc_kind`="TaxBill" ';
        $q .= ' AND `virtuemart_vendor_id` = 1  ';
        $q .= ' AND ( publish_up = ' . $db->Quote($nullDate) . ' OR publish_up <= ' . $db->Quote($now) . ' )' ;
        $q .= ' AND ( publish_down = ' . $db->Quote($nullDate) . ' OR publish_down >= ' . $db->Quote($now) . ' ) ';

        $db->setQuery($q);
        $taxrates = $db->loadObjectList();

       // $class = 'multiple="true" size="10"';
        return JHTML::_('select.genericlist', $taxrates, $control_name . '[' . $name . '][]', $class, 'value', 'text', $value, $control_name . $name);
    }

}