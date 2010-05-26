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
			$this->assignRef('orderdetails', $orderDetails);
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

		$this->loadHelper('currencydisplay');
		$currency = new CurrencyDisplay();
		$this->assignRef('currency', $currency);
		
		
		parent::display($tpl);
	}
}
