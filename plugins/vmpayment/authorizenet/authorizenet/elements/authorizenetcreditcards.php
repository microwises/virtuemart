<?php
defined('_JEXEC') or die('Restricted access');

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
/*
 * This class is used by VirtueMart Payment  Plugins
 * which uses JParameter
 * So It should be an extension of JElement
 * Those plugins cannot be configured througth the Plugin Manager anyway.
 */
if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');
if (!class_exists('ShopFunctions'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');

/**
 * @copyright	Copyright (C) 2009 Open Source Matters. All rights reserved.
 * @license	GNU/GPL
 */
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a multiple item select element
 *
 */

class JElementAuthorizenetCreditCards extends JElement {

    /**
     * Element name
     *
     * @access	protected
     * @var		string
     */

    var $_name = 'authorizenetcreditcards';

    function fetchElement($name, $value, &$node, $control_name) {
	// Base name of the HTML control.
	$ctrl = $control_name . '[' . $name . ']';

	// Construct an array of the HTML OPTION statements.
	$options = array();
	foreach ($node->children() as $option) {
	    $text = $option->data();
	    $val = $option->attributes('value');
	    $options[] = JHTML::_('select.option', $val, JText::_($text));
	}

	// Construct the various argument calls that are supported.
	$attribs = ' ';

	$attribs .= ' multiple="multiple"';
	$ctrl .= '[]';


	// Render the HTML SELECT list.
	return JHTML::_('select.genericlist', $options, $ctrl, $attribs, 'value', 'text', $value, $control_name . $name);
    }

}