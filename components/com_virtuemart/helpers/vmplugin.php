<?php

/**
 * abstract class for payment plugins
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Max Milbers
 * @author Oscar van Eijk
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */
// Load the helper functions that are needed by all plugins
if (!class_exists('ShopFunctions'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
if (!class_exists('DbScheme'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'dbscheme.php');
if (!class_exists('vmPlugin'))
    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'vmplugin.php');

// Get the plugin library
jimport('joomla.plugin.plugin');

abstract class vmPlugin extends JPlugin {

    var $_vmplugin = '';
    var $_debug = false;


    /**
     * Constructor
     *
     * @param object $subject The object to observe
     * @param array  $config  An array that holds the plugin configuration
     * @since 1.5
     */
    function __construct(& $subject, $config) {

	$lang = JFactory::getLanguage();
	$filename = 'plg_vm' . $this->_vmplugin . '_' . $this->_pelement;
	$lang->load($filename, JPATH_ADMINISTRATOR);
	if (!class_exists('JParameter'))
	    require(JPATH_VM_LIBRARIES . DS . 'joomla' . DS . 'html' . DS . 'parameter.php' );
	parent::__construct($subject, $config);
    }




    /*
     * logPaymentInfo
     * to help debugging Payment notification
     */

    public function logInfo($text, $type = 'message') {

	if ($this->_debug) {
	    $file = JPATH_ROOT . "/logs/" . $this->_pelement . "log";
	    $date = JFactory::getDate();

	    $fp = fopen($file, 'a');
	    fwrite($fp, "\n\n" . $date->toFormat('%Y-%m-%d %H:%M:%S'));
	    fwrite($fp, "\n" . $type . ': ' . $text);
	    fclose($fp);
	}
    }

    /*
     * Something went wrong, Send notification to all administrators
     * @param string subject of the mail
     * @param string message
     */

    public function sendEmailToVendorAndAdmins($subject, $message) {
	// recipient is vendor and admin
	$vendorId = 1;
	if (!class_exists('VirtueMartModelVendor'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
	$vendorModel = new VirtueMartModelVendor();
	$vendorEmail = $vendorModel->getVendorEmail($vendorId);
	$vendorName = $vendorModel->getVendorName($vendorId);
	JUtility::sendMail($vendorEmail, $vendorName, $vendorEmail, $subject, $message);
	if (VmConfig::isJ15()) {
	    //get all super administrator
	    $query = 'SELECT name, email, sendEmail' .
		    ' FROM #__users' .
		    ' WHERE LOWER( usertype ) = "super administrator"';
	} else {
	    $query = 'SELECT name, email, sendEmail' .
		    ' FROM #__users' .
		    ' WHERE sendEmail=1';
	}
	$db= JFactory::getDBO();
	$db->setQuery($query);
	$rows = $db->loadObjectList();

	$subject = html_entity_decode($subject, ENT_QUOTES);

	// get superadministrators id
	foreach ($rows as $row) {
	    if ($row->sendEmail) {
		$message = html_entity_decode($message, ENT_QUOTES);
		JUtility::sendMail($vendorEmail, $vendorName, $row->email, $subject, $message);
	    }
	}
    }

    /**
     * displays the logos of a VirtueMart plugin
     *
     * @author Valerie Isaksen
     * @author Max Milbers
     * @param array $logo_list
     * @return html with logos
     */
    public function displayLogos($logo_list) {

	$img = "";

	if (!(empty($logo_list))) {
	    $url = JURI::root() . 'images/stories/virtuemart/' . $this->_vmplugin . '/';
	    if (!is_array($logo_list))
		$logo_list = (array) $logo_list;
	    foreach ($logo_list as $logo => $alt_text) {
		$img .= '<img align="middle" src="' . $url . $logo . '"  alt="' . $alt_text . '" /> ';
	    }
	}
	return $img;
    }

}
