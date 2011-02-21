<?php
/**
 *
 * Handle the orders view
 *
 * @package	VirtueMart
 * @subpackage Orders
 * @author Oscar van Eijk
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport('joomla.application.component.view');

// Set to '0' to use tabs i.s.o. sliders
// Might be a config option later on, now just here for testing.
define ('__VM_ORDER_USE_SLIDERS', 0);

/**
 * Handle the orders view
 */
class VirtuemartViewOrders extends JView {

	public function display($tpl = null)
	{
//		$mainframe = JFactory::getApplication();
//		$pathway = $mainframe->getPathway();
		$layoutName = JRequest::getVar('layout', $this->getLayout());

		$_currentUser =& JFactory::getUser();
		$orderModel = $this->getModel('orders');

		if ($layoutName == 'details') {
			$orderDetails = $orderModel->getOrder();
			$cuid = $_currentUser->get('id');
			if(!empty($cuid)){
				if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
				if(!Permissions::getInstance()->check("admin")) {
					if(!empty($orderDetails['details']['BT']->user_id)){
						if ($orderDetails['details']['BT']->user_id != $cuid) {
							echo JText::_('VM_RESTRICTED_ACCESS');
							return;
						}
					}
				}
			} else {
				echo JText::_('VM_RESTRICTED_ACCESS');
				return;
			}
			$this->assignRef('orderdetails', $orderDetails);

			// Implement the Joomla panels. If we need a ShipTo tab, make it the active one.
			// In tmpl/edit.php, this is the 4th tab (0-based, so set to 3 above)
			jimport('joomla.html.pane');
			$pane = JPane::getInstance((__VM_ORDER_USE_SLIDERS?'Sliders':'Tabs'));
			$this->assignRef('pane', $pane);
		} else { // 'list' -. default
			if ($_currentUser->get('id') == 0) {
				// getOrdersList() returns all orders when no userID is set (admin function),
				// so explicetly define an empty array when not logged in.
				$orderList = array();
			} else {
				$orderList = $orderModel->getOrdersList($_currentUser->get('id'), true);
			}
			$this->assignRef('orderlist', $orderList);
		}

		if (!class_exists('CurrencyDisplay')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');

		$currency = CurrencyDisplay::getCurrencyDisplay();
		$this->assignRef('currency', $currency);

		// Create a simple indexed array woth ordertatuses
		$_orderstatuses = $this->get('OrderStatusList');
		$orderstatuses = array();
		foreach ($_orderstatuses as $_ordstat) {
			$orderstatuses[$_ordstat->value] = $_ordstat->text;
		}
		$this->assignRef('orderstatuses', $orderstatuses);

		if (!class_exists('ShopFunctions')) {
			if(!class_exists('ShopFunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');
		}
		shopFunctionsF::setVmTemplate($this,0,0,$layoutName);
		parent::display($tpl);
	}
}
