<?php

/**
 * abstract class for payment plugins
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: vmplugin.php 4599 2011-11-02 18:29:04Z alatak $
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

	// var Must be overriden in every plugin file by adding this code to the constructor:
	// $this->_pelement = basename(__FILE, '.php');
	// just as note: protected can be accessed only within the class itself and by inherited and parent classes

	protected $_pelement = '';
	protected $_vmplugin = '';
	protected $_tablename = '';
	protected $_debug = false;

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
		if (!class_exists('JParameter')) require(JPATH_VM_LIBRARIES . DS . 'joomla' . DS . 'html' . DS . 'parameter.php' );
		parent::__construct($subject, $config);
	}

	function getDebug() {
		return $this->_debug;
	}

	function setDebug($params) {
		return $this->_debug = $params->get('debug');
	}
	/*
	 * logPaymentInfo
	* to help debugging Payment notification
	*/

	public function logInfo($text, $type = 'message') {

		if ($this->_debug) {
			$file = JPATH_ROOT . "/logs/" . $this->_pelement . ".log";
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
		$db = JFactory::getDBO();
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
			foreach ($logo_list as $logo) {
				$alt_text = substr($logo, 0, strpos($logo, '.'));
				$img .= '<img align="middle" src="' . $url . $logo . '"  alt="' . $alt_text . '" /> ';
			}
		}
		return $img;
	}

	function getHtmlHeaderBE() {
		$class = "class='key'";
		$html = ' 	<thead>' . "\n"
		. '		<tr>' . "\n"
		. '			<td ' . $class . ' style="text-align: center;" colspan="2">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_' . $this->_vmplugin . '_LBL') . '</td>' . "\n"
		. '		</tr>' . "\n"
		. '	</thead>' . "\n";

		return $html;
	}

	function getHtmlRow($key, $value, $class='') {
		$lang = & JFactory::getLanguage();
		$key_text = '';
		$complete_key = 'VM' . $this->_vmplugin . '_' . $key;
		// vmdebug('getHtmlRow',$key,$complete_key);
		if ($lang->hasKey($complete_key)) {
			$key_text = JText::_($complete_key);
		}
		$more_key = 'VM' . $this->_vmplugin . '_' . $key . '_' . $value;
		if ($lang->hasKey($more_key)) {
			$value .=" (" . JText::_($more_key) . ")";
		}
		$html = "<tr>\n<td " . $class . ">" . $key_text . "</td>\n <td align='left'>" . $value . "</td>\n</tr>\n";
		return $html;
	}

	function getHtmlRowBE($key, $value) {
		return $this->getHtmlRow($key, $value, "class='key'");
	}


	/**
	 * This method writes all  plugin specific data to the plugin's table
	 *
	 * @param array $_values Indexed array in the format 'column_name' => 'value'
	 * @param string $_table Table name
	 * @author Oscar van Eijk
	 */
	protected function writeData($_values, $_table) {
		if (count($_values) == 0) {
			JError::raiseWarning(500, 'writeData got no data to save to ' . $_table);
			return;
		}
		if (!class_exists('VirtueMartModelOrders'))
		require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
		if (!isset($_values['virtuemart_order_id'])) {
			$_values['virtuemart_order_id'] = VirtueMartModelOrders::getOrderIdByOrderNumber($_values['order_number']);
		}
		$_cols = array();
		$_vals = array();
		foreach ($_values as $_col => $_val) {
			$_cols[] = "`$_col`";
			$_vals[] = "'$_val'";
		}
		$_db = JFactory::getDBO();
		$_q = 'INSERT INTO `' . $_table . '` ('
		. implode(',', $_cols)
		. ') VALUES ('
		. implode(',', $_vals)
		. ')';
		$_db->setQuery($_q);
		if (!$_db->query()) {
			JError::raiseWarning(500, $_db->getErrorMsg());
		}
	}

	/**
	 * This method updates all  plugin specific data to the plugin's table
	 *
	 * @param array $_values Indexed array in the format 'column_name' => 'value'
	 * @param string $_table Table name
	 * @author Valerie Isaksen
	 *
	 */
	protected function updateData($values, $table, $where_key, $where_value) {
		if (count($values) == 0) {
			JError::raiseWarning(500, 'updateData got no data to update to ' . $table);
			return;
		}
		$cols = array();
		$vals = array();
		foreach ($values as $col => $val) {
			$fields[] = "`$col`" . "=" . "'$val'";
		}
		$db = JFactory::getDBO();
		$q = 'UPDATE `' . $table . '` SET ';
		foreach ($values as $key => $value) {
			$q .= $db->getEscaped($key) . '="' . $value . '",';
		}
		$q = substr($q, 0, strlen($q) - 1);
		$q .= ' WHERE `' . $where_key . '` =' . $where_value;


		$db->setQuery($q);
		if (!$db->query()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}
	}

}
