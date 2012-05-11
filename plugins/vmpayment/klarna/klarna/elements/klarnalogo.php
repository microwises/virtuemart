<?php

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();


/**
 * Renders a label element
 */
if (JVM_VERSION === 2) {
    require ( JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');
    if (!class_exists('KlarnaHandler'))
    require ( JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
} else {
    require ( JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');
    if (!class_exists('KlarnaHandler'))
    require ( JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
}

class JElementKlarnaLogo extends JElement {

    /**
     * Element name
     *
     * @access	protected
     * @var		string
     */
    var $_name = 'KlarnaLogo';

    function fetchElement($name, $value, &$node, $control_name) {
	$countriesData = KlarnaHandler::countriesData();
	$logo = '<a href="https://www.klarna.com" target="_blank"><img src="' . JURI::root() . VMKLARNAPLUGINWEBROOT . '/klarna/assets/images/logo/logo_small.png" /></a> ';
$flagImgHtml='';
	foreach ($countriesData as $countryData) {
	    $flagImg = JURI::root(true) . '/administrator/components/com_virtuemart/assets/images/flag/' . strtolower($countryData['language_code']) . '.png';
	    $flagImgHtml.='<img style="margin-right: 5px;margin-top: 15px;" src="' . $flagImg . '"  alt="' . JText::_('VMPAYMENT_KLARNA_CONF_SETTINGS_' . $countryData['language_code']) . '"/>';
	}
	return $logo . $flagImgHtml;
    }

}