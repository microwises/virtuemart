<?php
/**
*
* View for the shopping cart
*
* @package	VirtueMart
* @subpackage
* @author Max Milbers
* @author Oscar van Eijk
* @author RolandD
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
jimport( 'joomla.application.component.view');

/**
* RAW text view for the shopping cart
* @package VirtueMart
* @author Max Milbers
* @author Oscar van Eijk
* @author Christopher Roussel
*/
class VirtueMartViewCart extends JView {

	private $_cart;
	private $_user;
	private $_userDetails;
	public $lists;
    public function display($tpl = null) {
		if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
		$cart = VirtueMartCart::getCart(false);
		$cart->prepareCartData();
		$this->data = $cart->prepareAjaxData();

		echo json_encode($this->data);
		Jexit();
    }

	public function renderMail ($doVendor=false) {
		
		if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');

		$cart = VirtueMartCart::getCart(false);
		$this->assignRef('cart', $cart);
		$cart->prepareCartViewData();
		$this->prepareMailData();

		if ($doVendor) {
			$this->subject = Text::sprintf('COM_VIRTUEMART_NEW_ORDER_CONFIRMED',	$this->shopperName, $this->order['details']['BT']->order_total, $this->order['details']['BT']->order_number);
			$recipient = 'vendor';
		} else {
			$this->subject = JText::sprintf('COM_VIRTUEMART_NEW_ORDER_CONFIRMED', $this->cart->vendor->vendor_store_name, $this->order['details']['BT']->order_total, $this->order['details']['BT']->order_number, $this->order['details']['BT']->order_pass);
			$recipient = 'shopper';
		}
		if (VmConfig::get('order_mail_html')) $tpl = 'mail_html';
		else $tpl = 'mail_raw';
		$this->assignRef('recipient', $recipient);

		$this->doVendor = true;

		$vendorModel = $this->getModel('vendor');
		$this->vendorEmail = $vendorModel->getVendorEmail($this->cart->vendor->virtuemart_vendor_id);
		$this->layoutName = $tpl;
		$this->setLayout($tpl);
		parent::display();
	}

	/*
	 *TODO adding mails specific data
	 */
	private function prepareMailData(){

		//TODO add orders, for the orderId
		//TODO add registering userdata
		// In general we need for every mail the shopperdata (with group), the vendor data, shopperemail, shopperusername, and so on
	}
}

//no closing tag