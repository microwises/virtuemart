<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a label element
 */

class JElementKlarnaLogo extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'KlarnaLogo';

	function fetchElement($name, $value, &$node, $control_name)
	{
		return '<p><a href="https://www.klarna.com" target="_blank"><img src="'. JURI::root() . VMKLARNAPLUGINWEBROOT . 'klarna/assets/images/logo/logo_small.png" /></a></p>';

	}
}