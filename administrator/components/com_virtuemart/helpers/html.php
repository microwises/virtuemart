<?php
/**
 * HTML helper class
 *
 * This class was developed to provide some standard HTML functions.
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author RickG
 * @copyright Copyright (c) 2004-2008 Soeren Eberhardt-Biermann, 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * HTML Helper
 *
 * @package		VirtueMart
 * @subpackage Helpers
 * @author RickG
 */
class VmHTML
{
    /**
     * Generate HTML code for a checkbox
     *
     * @param string Name for the chekcbox
     * @param mixed Current value of the checkbox
     * @param mixed Value to assign when checkbox is checked
     * @param mixed Value to assign when checkbox is not checked
     * @return string HTML code for checkbox
     */
    function checkbox($name, $value, $checkedValue=1, $uncheckedValue=0) {
	if ($value == $checkedValue) {
	    $checked = 'checked="checked"';
	}
	else {
	    $checked = '';
	}
	$htmlcode = '<input type="hidden" name="' . $name . '" value="' . $uncheckedValue . '">';
	$htmlcode .= '<input type="checkbox" name="' . $name . '" value="' . $checkedValue . '" ' . $checked . ' />';
	return $htmlcode;
    }

}