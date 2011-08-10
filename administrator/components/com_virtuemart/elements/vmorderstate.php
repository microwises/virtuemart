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
if (!class_exists('ShopFunctions'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
if (!class_exists('TableCategories'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'tables' . DS . 'categories.php');
if (!class_exists('VmElements'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'elements' . DS . 'vmelements.php');



        class VmElementVmOrderState extends VmElements {

            var $_name = 'OrderState';

            // This line is required to keep Joomla! 1.6/1.7 from complaining
            function getInput() {
                $key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
                $val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);

                $db = JFactory::getDBO();

                $query = 'SELECT `order_status_code` AS value, `order_status_name` AS text
        			FROM `#__virtuemart_orderstates`
        			WHERE `virtuemart_vendor_id` = "1" ORDER BY `ordering` ASC '
                ;


                $db->setQuery($query);
                $fields = $db->loadObjectList();
                $class = '';

                return JHTML::_('select.genericlist', $fields, $this->name, 'class="inputbox" multiple="true" size="10"', $key, $val, $this->value, $this->id);
            }

            function fetchElement($name, $value, &$node, $control_name) {
                $db = JFactory::getDBO();

                $query = 'SELECT `order_status_code` AS value, `order_status_name` AS text
        			FROM `#__virtuemart_orderstates`
        			WHERE `virtuemart_vendor_id` = "1" ORDER BY `ordering` ASC '
                ;


                $db->setQuery($query);
                $fields = $db->loadObjectList();
                $class = '';

                $class = 'multiple="true" size="10"';
                return JHTML::_('select.genericlist', $fields, $control_name . '[' . $name . '][]', $class, 'value', 'text', $value, $control_name . $name);
            }

        }

if (version_compare(JVERSION, '1.6.0', 'ge')) {

    class JFormFieldVmOrderState extends VmElementVmOrderState {

    }

} else {

    class JElementVmOrderState extends VmElementVmOrderState {

    }

}


