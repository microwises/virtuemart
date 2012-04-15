<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a label element
 */
if (JVM_VERSION === 2) {
     if (!defined ('JPATH_VMKLARNAPLUGIN')) define('JPATH_VMKLARNAPLUGIN', JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna');
     if (!defined ('VMKLARNAPLUGINWEBROOT')) define('VMKLARNAPLUGINWEBROOT', 'plugins/vmpayment/klarna');

} else {
     if (!defined ('JPATH_VMKLARNAPLUGIN')) define('JPATH_VMKLARNAPLUGIN', JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment');
     if (!defined ('VMKLARNAPLUGINWEBROOT')) define('VMKLARNAPLUGINWEBROOT', 'plugins/vmpayment');
 }

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
		return '<p><a href="https://www.klarna.com" target="_blank"><img src="'. JURI::root() . VMKLARNAPLUGINWEBROOT . '/klarna/assets/images/logo/logo_small.png" /></a></p>';

	}
}