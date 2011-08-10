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
if (!class_exists('VmElements'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'elements' . DS . 'vmelements.php');

class VmElementVmTaxes extends VmElements {

    var $_name = 'taxes';

// This line is required to keep Joomla! 1.6/1.7 from complaining
    function getInput() {
        $key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
        $val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);

// return ShopFunctions::renderTaxList($this->name, $this->name);
    }

    function fetchElement($name, $value, &$node, $control_name) {


        return ShopFunctions::renderTaxList($control_name . $name, $control_name . '[' . $name . '][]');

// $class = 'multiple="true" size="10"';
// return JHTML::_('select.genericlist', $taxrates, $control_name . '[' . $name . '][]', $class, 'value', 'text', $value, $control_name . $name);
    }

}

if (version_compare(JVERSION, '1.6.0', 'ge')) {

    class JFormFieldVmTaxes extends VmElementVmTaxes {

    }
} else {

    class JElementVmTaxes extends VmElementVmTaxes {

    }
}


