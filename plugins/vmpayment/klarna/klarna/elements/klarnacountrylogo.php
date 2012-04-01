<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a label element
 */

class JElementKlarnaCountryLogo extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'KlarnacountryLogo';

	function fetchElement($name, $value, &$node, $control_name)
	{

		return '<img style="margin-right: 5px;margin-top: 15px;" src="'. JURI::root() . VMKLARNAPLUGINWEBROOT . '/klarna/assets/images/share/flags/'.  strtolower($value).'.png" />'. JText::_('VMPAYMENT_KLARNA_CONF_SETTINGS_'.$value);

	}
}