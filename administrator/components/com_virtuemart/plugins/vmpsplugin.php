<?php
/**
* abstract class for payment/shipment plugins
*
* @package	VirtueMart
* @subpackage Plugins
* @author Max Milbers
* @author Valérie Isaksen
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: vmpaymentplugin.php 4601 2011-11-03 15:50:01Z alatak $
*/

if (!class_exists('vmPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmplugin.php');

abstract class vmPSPlugin extends vmPlugin {


	function getDebug() {
		return $this->_debug;
	}

	function setDebug($params) {
		return $this->_debug = $params->get('debug');
	}

	/**
	 * logPaymentInfo
	 * to help debugging Payment notification
	 */

	protected function logInfo($text, $type = 'message') {

		if ($this->_debug) {
			$file = JPATH_ROOT . "/logs/" . $this->_name . ".log";
			$date = JFactory::getDate();

			$fp = fopen($file, 'a');
			fwrite($fp, "\n\n" . $date->toFormat('%Y-%m-%d %H:%M:%S'));
			fwrite($fp, "\n" . $type . ': ' . $text);
			fclose($fp);
		}
	}

	/**
	 * Overwrites the standard function in vmplugin. Extendst the input data by virtuemart_order_id
	 * Calls the parent to execute the write operation
	 *
	 * @author Max Milbers
	 * @param array $_values
	 * @param string $_table
	 */
	protected function writeData($_values, $_table) {
		if (!class_exists('VirtueMartModelOrders'))
		require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
		if (!isset($_values['virtuemart_order_id'])) {
			$_values['virtuemart_order_id'] = VirtueMartModelOrders::getOrderIdByOrderNumber($_values['order_number']);
		}
		parent::writeData($_values, $_table);
	}


	/**
	 * Something went wrong, Send notification to all administrators
	 * @param string subject of the mail
	 * @param string message
	 */

	protected function sendEmailToVendorAndAdmins($subject, $message) {
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
	protected function displayLogos($logo_list) {

		$img = "";

		if (!(empty($logo_list))) {
			$url = JURI::root() . 'images/stories/virtuemart/' . $this->_type . '/';
			if (!is_array($logo_list))
			$logo_list = (array) $logo_list;
			foreach ($logo_list as $logo) {
				$alt_text = substr($logo, 0, strpos($logo, '.'));
				$img .= '<img align="middle" src="' . $url . $logo . '"  alt="' . $alt_text . '" /> ';
			}
		}
		return $img;
	}

	protected function getHtmlHeaderBE() {
		$class = "class='key'";
		$html = ' 	<thead>' . "\n"
		. '		<tr>' . "\n"
		. '			<td ' . $class . ' style="text-align: center;" colspan="2">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_' . $this->_type . '_LBL') . '</td>' . "\n"
		. '		</tr>' . "\n"
		. '	</thead>' . "\n";

		return $html;
	}

	protected function getHtmlRow($key, $value, $class='') {
		$lang = & JFactory::getLanguage();
		$key_text = '';
		$complete_key =  strtoupper($this->_type . '_' . $key);
		// vmdebug('getHtmlRow',$key,$complete_key);
		if ($lang->hasKey($complete_key)) {
			$key_text = JText::_($complete_key);
		}
		$more_key = $complete_key . '_' . $value;
		if ($lang->hasKey($more_key)) {
			$value .=" (" . JText::_($more_key) . ")";
		}
		$html = "<tr>\n<td " . $class . ">" . $key_text . "</td>\n <td align='left'>" . $value . "</td>\n</tr>\n";
		return $html;
	}

	protected function getHtmlRowBE($key, $value) {
		return $this->getHtmlRow($key, $value, "class='key'");
	}


}