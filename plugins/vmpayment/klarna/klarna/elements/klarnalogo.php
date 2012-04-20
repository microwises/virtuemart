<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();


/**
 * Renders a label element
 */

if (JVM_VERSION === 2) {
     require ( JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna'. DS . 'klarna' . DS.'helpers' . DS . 'define.php');
} else {
     require ( JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS.'helpers' . DS . 'define.php');
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