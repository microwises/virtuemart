<?php

/**
 *
 * @package	VirtueMart
 * @subpackage Plugins  - Elements
 * @author Val?rie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: $
 */
/*
 * This class is used by VirtueMart Payment or Shipping Plugins
 * which uses JParameter
 * So It should be an extension of JElement
 * Those plugins cannot be configured througth the Plugin Manager anyway.
 */
class JElementKlarnaCountries extends JElement {

    /**
     * Element name
     * @access	protected
     * @var		string
     */
    var $_name = 'klarnacountries';
    var $type = 'klarnacountries';

    function fetchElement($name, $value, &$node, $control_name) {
	$db = JFactory::getDBO();
	$klarna_countries= '"se", "de", "dk", "nl", "fi", "no"';
	$query = 'SELECT `country_3_code` AS value, `country_name` AS text FROM `#__virtuemart_countries`
               		WHERE `published` = 1 AND `country_2_code` IN ('.$klarna_countries.') ORDER BY `country_name` ASC ';

	$db->setQuery($query);
	$fields = $db->loadObjectList();

	$class = 'multiple="true" size="10"';
	return JHTML::_('select.genericlist', $fields, $control_name . '[' . $name . '][]', $class, 'value', 'text', $value, $control_name . $name);
    }

}