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
 * @version $Id: view.html.php 5432 2012-02-14 02:20:35Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmView'))require(JPATH_VM_SITE.DS.'helpers'.DS.'vmview.php');

// Set to '0' to use tabs i.s.o. sliders
// Might be a config option later on, now just here for testing.
define ('__VM_ORDER_USE_SLIDERS', 0);

/**
 * Handle the orders view
 */
class VirtuemartViewInvoice extends VmView {

	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		$this->setLayout('details');

		$document = JFactory::getDocument();

		$orderModel = VmModel::getModel('orders');

		$orderDetails = $this->order;

		if(empty($orderDetails['details'])){
			echo JText::_('COM_VIRTUEMART_ORDER_NOTFOUND');
			return;
		}

		$userFieldsModel = VmModel::getModel('userfields');
		$_userFields = $userFieldsModel->getUserFields(
			 'account'
		, array('captcha' => true, 'delimiters' => true) // Ignore these types
		, array('delimiter_userinfo','user_is_vendor' ,'username','password', 'password2', 'agreed', 'address_type') // Skips
		);
		$orderbt = $orderDetails['details']['BT'];
		$orderst = (array_key_exists('ST', $orderDetails['details'])) ? $orderDetails['details']['ST'] : $orderbt;
		$userfields = $userFieldsModel->getUserFieldsFilled(
		$_userFields
		,$orderbt
		);
		$_userFields = $userFieldsModel->getUserFields(
			 'shipment'
		, array() // Default switches
		, array('delimiter_userinfo', 'username', 'email', 'password', 'password2', 'agreed', 'address_type') // Skips
		);

		$shipmentfields = $userFieldsModel->getUserFieldsFilled(
		$_userFields
		,$orderst
		);


		if (empty($orderDetails['shipmentName']) ) {
		    if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
		    JPluginHelper::importPlugin('vmshipment');
		    $dispatcher = JDispatcher::getInstance();
		    $returnValues = $dispatcher->trigger('plgVmOnShowOrderFEShipment',array(  $orderDetails['details']['BT']->virtuemart_order_id, $orderDetails['details']['BT']->virtuemart_shipmentmethod_id, &$orderDetails['shipmentName']));
		 }
		 if (empty($orderDetails['paymentName']) ) {
		    if(!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS.DS.'vmpsplugin.php');
		    JPluginHelper::importPlugin('vmpayment');
		    $dispatcher = JDispatcher::getInstance();
		    $returnValues = $dispatcher->trigger('plgVmOnShowOrderFEPayment',array( $orderDetails['details']['BT']->virtuemart_order_id, $orderDetails['details']['BT']->virtuemart_paymentmethod_id,  &$orderDetails['paymentName']));
		 }

// 			if($format=='pdf'){

		$invoice_number = $orderModel->createInvoiceNumber($orderDetails['details']['BT']);
		$this->assignRef('invoice_number', $invoice_number);
// 			}

			$this->assignRef('userfields', $userfields);
			$this->assignRef('shipmentfields', $shipmentfields);
			//$this->assignRef('shipment_name', $shipment_name);
			//$this->assignRef('payment_name', $payment_name);
			$this->assignRef('orderdetails', $orderDetails);

			$tmpl = JRequest::getWord('tmpl');
			$print = false;
			if($tmpl){
				$print = true;
			}

			//Todo multix
			$vendorId=1;
			$vendorModel = VmModel::getModel('vendor');
			$vendorModel->setId($vendorId);
			$vendor = $vendorModel->getVendor();
			$vendorModel->addImages($vendor,1);

			$this->vendorEmail = $vendorModel->getVendorEmail($vendor->virtuemart_vendor_id);
			$this->assignRef('vendor', $vendor);
			$this->assignRef('print', $print);

			// Implement the Joomla panels. If we need a ShipTo tab, make it the active one.
			// In tmpl/edit.php, this is the 4th tab (0-based, so set to 3 above)
			// jimport('joomla.html.pane');
			// $pane = JPane::getInstance((__VM_ORDER_USE_SLIDERS?'Sliders':'Tabs'));
			// $this->assignRef('pane', $pane);


		if (!class_exists('CurrencyDisplay')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');

		$currency = CurrencyDisplay::getInstance();
		$this->assignRef('currency', $currency);

		$orderStatusModel = VmModel::getModel('orderstatus');

		$_orderstatuses = $orderStatusModel->getOrderStatusList();
		$orderstatuses = array();
		foreach ($_orderstatuses as $_ordstat) {
			$orderstatuses[$_ordstat->order_status_code] = JText::_($_ordstat->order_status_name);
		}

		$this->assignRef('orderstatuses', $orderstatuses);

		if(!class_exists('ShopFunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');

		// this is no setting in BE to change the layout !
		//shopFunctionsF::setVmTemplate($this,0,0,$layoutName);

		//vmdebug('renderMailLayout invoice '.date('H:i:s'),$this->order);

		if ($this->doVendor) {
			$this->subject = JText::sprintf('COM_VIRTUEMART_VENDOR_NEW_ORDER_CONFIRMED', $this->shopperName, $currency->priceDisplay($orderDetails['details']['BT']->order_total), $orderDetails['details']['BT']->order_number);
			$recipient = 'vendor';
		} else {
			$this->subject = JText::sprintf('COM_VIRTUEMART_SHOPPER_NEW_ORDER_CONFIRMED', $vendor->vendor_store_name, $currency->priceDisplay($orderDetails['details']['BT']->order_total), $orderDetails['details']['BT']->order_number, $orderDetails['details']['BT']->order_pass );
			$recipient = 'shopper';
		}

		$pdf = 'pdf';
		$this->assignRef('format', $pdf);

		if($this->fromPdf){
			$document->setTitle( JText::_('COM_VIRTUEMART_INVOICE') );
		}



		parent::display($tpl);
	}

	// FE public function renderMailLayout($doVendor=false)
	function renderMailLayout ($doVendor, $recipient) {

		$this->doVendor=$doVendor;
		$this->fromPdf=false;
		$this->display();
		// don't need to get the payment name, the Order is sent from the payment trigger
/*		$tpl = (VmConfig::get('order_html_email',1)) ? 'mail_html' : 'mail_raw';

		if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		if(!class_exists('CurrencyDisplay')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');

		$currency = CurrencyDisplay::getInstance();

		$userFieldsModel = VmModel::getModel('userfields');
		$userFields = $userFieldsModel->getUserFields(
				     'account'
		, array('captcha' => true, 'delimiters' => true) // Ignore these types
		, array('delimiter_userinfo','user_is_vendor' ,'username','password', 'password2', 'agreed', 'address_type') // Skips
		);

		$orderbt = $this->order['details']['BT'];
		$orderst = (array_key_exists('ST', $this->order['details'])) ? $this->order['details']['ST'] : $orderbt;
		$billfields = $userFieldsModel->getUserFieldsFilled(
		$userFields
		,$orderbt
		);

		$userFields = $userFieldsModel->getUserFields(
				     'shipment'
		, array() // Default switches
		, array('delimiter_userinfo', 'username', 'email', 'password', 'password2', 'agreed', 'address_type') // Skips
		);

		$shipmentfields = $userFieldsModel->getUserFieldsFilled(
		$userFields
		,$orderst
		);

		if(!class_exists('VirtueMartModelOrderstatus')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'orderstatus.php');
		$vendorModel = VmModel::getModel('vendor');
		$vendor = $vendorModel->getVendor();
		$vendorModel->addImages($vendor,1);
		$this->assignRef('vendor', $vendor);

		//From FE
		if ($doVendor) {
			$this->subject = JText::sprintf('COM_VIRTUEMART_VENDOR_NEW_ORDER_CONFIRMED', $this->shopperName, $currency->priceDisplay($this->order['details']['BT']['order_total']), $this->order['details']['BT']['order_number']);
			$recipient = 'vendor';
		} else {
			$this->subject = JText::sprintf('COM_VIRTUEMART_SHOPPER_NEW_ORDER_CONFIRMED', $this->vendor->vendor_store_name, $currency->priceDisplay($this->order['details']['BT']['order_total']), $this->order['details']['BT']['order_number'], $this->order['details']['BT']['order_pass'] );
			$recipient = 'shopper';
		}
		$this->doVendor = true;
		//From FE end

		//From BE
// 		$this->subject = JText::sprintf('COM_VIRTUEMART_SHOPPER_NEW_ORDER_CONFIRMED', $this->vendor->vendor_store_name, $currency->priceDisplay($this->order['details']['BT']['order_total']), $this->order['details']['BT']['order_number'], $this->order['details']['BT']['order_pass'] );
// 		$recipient = 'shopper';
		//From BE end

		$this->assignRef('recipient', $recipient);
		$this->assignRef('currency', $currency);
		$this->assignRef('shipment_name', $this->order['shipmentName']);
		$this->assignRef('payment_name', $this->order['paymentName']);
		$this->assignRef('billfields', $billfields);
		$this->assignRef('shipmentfields', $shipmentfields);

		$this->vendorEmail = $vendorModel->getVendorEmail($this->vendor->virtuemart_vendor_id);

		$this->layoutName = $tpl;
		$this->setLayout($tpl);

		$path = VmConfig::get('forSale_path',0);

		vmdebug('renderMailLayout invoice '.date('H:i:s'),$this->order);

		if($this->order['details']['BT']['order_status']  == 'C' and $path!==0){

			if(!class_exists('VirtueMartControllerInvoice')) require_once( JPATH_VM_SITE.DS.'controllers'.DS.'invoice.php' );
			$controller = new VirtueMartControllerInvoice( array(
									  'model_path' => JPATH_VM_SITE.DS.'models',
									  'view_path' => JPATH_VM_SITE.DS.'views'
			));

			$this->mediaToSend[] = $controller->checkStoreInvoice($this->order);
		}

		parent::display();*/
	}


}
