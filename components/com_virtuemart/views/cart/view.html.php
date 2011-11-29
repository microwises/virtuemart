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
jimport('joomla.application.component.view');

/**
 * View for the shopping cart
 * @package VirtueMart
 * @author Max Milbers
 * @author Patrick Kohl
 */
class VirtueMartViewCart extends JView {

	public function display($tpl = null) {
		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();
		$document = JFactory::getDocument();

		$layoutName = $this->getLayout();
		if (!$layoutName)
		$layoutName = JRequest::getWord('layout', 'default');
		$this->assignRef('layoutName', $layoutName);
		$format = JRequest::getWord('format');
		// if(!class_exists('virtueMartModelCart')) require(JPATH_VM_SITE.DS.'models'.DS.'cart.php');
		// $model = new VirtueMartModelCart;

		if (!class_exists('VirtueMartCart'))
		require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		$cart = VirtueMartCart::getCart(false);
		$this->assignRef('cart', $cart);

		//Why is this here, when we have view.raw.php
		if ($format == 'raw') {
			$cart->prepareCartViewData();
			JRequest::setVar('layout', 'mini_cart');
			$this->setLayout('mini_cart');
			$this->prepareContinueLink();
		}
		/*
	  if($layoutName=='edit_coupon'){

		$cart->prepareCartViewData();
		$this->lSelectCoupon();
		$pathway->addItem(JText::_('COM_VIRTUEMART_CART_OVERVIEW'),JRoute::_('index.php?option=com_virtuemart&view=cart'));
		$pathway->addItem(JText::_('COM_VIRTUEMART_CART_SELECTCOUPON'));
		$document->setTitle(JText::_('COM_VIRTUEMART_CART_SELECTCOUPON'));

		} else */
		if ($layoutName == 'select_shipment') {
			if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
			JPluginHelper::importPlugin('vmshipment');
			$this->lSelectShipment();

			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_OVERVIEW'), JRoute::_('index.php?option=com_virtuemart&view=cart'));
			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_SELECTSHIPMENT'));
			$document->setTitle(JText::_('COM_VIRTUEMART_CART_SELECTSHIPMENT'));
		} else if ($layoutName == 'select_payment') {

			/* Load the cart helper */
			//			$cartModel = $this->getModel('cart');

			$this->lSelectPayment();

			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_OVERVIEW'), JRoute::_('index.php?option=com_virtuemart&view=cart'));
			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_SELECTPAYMENT'));
			$document->setTitle(JText::_('COM_VIRTUEMART_CART_SELECTPAYMENT'));
		} else if ($layoutName == 'order_done') {

			$this->lOrderDone();

			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_THANKYOU'));
			$document->setTitle(JText::_('COM_VIRTUEMART_CART_THANKYOU'));
		} else if ($layoutName == 'default') {

			$cart->prepareCartViewData();

			$cart->prepareAddressRadioSelection();

			$this->prepareContinueLink();
			$this->lSelectCoupon();
			if ($cart->getDataValidated()) {
				$pathway->addItem(JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU'));
				$document->setTitle(JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU'));
				$text = JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU');
				$checkout_task = 'confirm';
			} else {
				$pathway->addItem(JText::_('COM_VIRTUEMART_CART_OVERVIEW'));
				$document->setTitle(JText::_('COM_VIRTUEMART_CART_OVERVIEW'));
				$text = JText::_('COM_VIRTUEMART_CHECKOUT_TITLE');
				$checkout_task = 'checkout';
			}
			$this->assignRef('checkout_task', $checkout_task);
			$this->checkPaymentMethodsConfigured();
			$this->checkShipmentMethodsConfigured();
			if ($cart->virtuemart_shipmentmethod_id) {
				$this->assignRef('select_shipment_text', JText::_('COM_VIRTUEMART_CART_CHANGE_SHIPPING'));
			} else {
				$this->assignRef('select_shipment_text', JText::_('COM_VIRTUEMART_CART_EDIT_SHIPPING'));
			}
			if ($cart->virtuemart_paymentmethod_id) {
				$this->assignRef('select_payment_text', JText::_('COM_VIRTUEMART_CART_CHANGE_PAYMENT'));
			} else {
				$this->assignRef('select_payment_text', JText::_('COM_VIRTUEMART_CART_EDIT_PAYMENT'));
			}

			if (!VmConfig::get('use_as_catalog')) {
				$checkout_link_html = '<a class="vm-button-correct" href="javascript:document.checkoutForm.submit();" ><span>' . $text . '</span></a>';
			} else {
				$checkout_link_html = '';
			}
			$this->assignRef('checkout_link_html', $checkout_link_html);
		}
		//dump ($cart,'cart');
		$useSSL = VmConfig::get('useSSL', 0);
		$useXHTML = true;
		$this->assignRef('useSSL', $useSSL);
		$this->assignRef('useXHTML', $useXHTML);

		// @max: quicknirty
		$cart->setCartIntoSession();
		shopFunctionsF::setVmTemplate($this, 0, 0, $layoutName);

		vmdebug('my cart',$cart);
		parent::display($tpl);
	}

	public function renderMailLayout($doVendor=false) {
		if (!class_exists('VirtueMartCart'))
		require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');

		$cart = VirtueMartCart::getCart(false);
		$this->assignRef('cart', $cart);
		$cart->prepareCartViewData();
		$cart->prepareMailData();

		if ($doVendor) {
			$this->subject = JText::sprintf('COM_VIRTUEMART_VENDOR_NEW_ORDER_CONFIRMED', $this->shopperName, $this->cart->prices['billTotal'], $this->order['details']['BT']->order_number);
			$recipient = 'vendor';
		} else {
			$this->subject = JText::sprintf('COM_VIRTUEMART_SHOPPER_NEW_ORDER_CONFIRMED', $this->cart->vendor->vendor_store_name, $this->cart->prices['billTotal'], $this->order['details']['BT']->order_number, $this->order['details']['BT']->order_pass);
			$recipient = 'shopper';
		}
		$this->doVendor = true;
		if (VmConfig::get('order_mail_html'))
		$tpl = 'mail_html';
		else
		$tpl = 'mail_raw';
		$this->assignRef('recipient', $recipient);

		$vendorModel = $this->getModel('vendor');
		$this->vendorEmail = $vendorModel->getVendorEmail($cart->vendor->virtuemart_vendor_id);
		$this->layoutName = $tpl;
		$this->setLayout($tpl);
		parent::display();
	}

	private function prepareContinueLink() {
		// Get a continue link */
		$virtuemart_category_id = shopFunctionsF::getLastVisitedCategoryId();
		$categoryLink = '';
		if ($virtuemart_category_id) {
			$categoryLink = '&virtuemart_category_id=' . $virtuemart_category_id;
		}
		$continue_link = JRoute::_('index.php?option=com_virtuemart&view=category' . $categoryLink);

		$continue_link_html = '<a class="continue_link" href="' . $continue_link . '" >' . JText::_('COM_VIRTUEMART_CONTINUE_SHOPPING') . '</a>';
		$this->assignRef('continue_link_html', $continue_link_html);
		$this->assignRef('continue_link', $continue_link);
	}

	private function lSelectCoupon() {

		$this->couponCode = (isset($this->cart->couponCode) ? $this->cart->couponCode : '');
		$coupon_text = $this->cart->couponCode ? JText::_('COM_VIRTUEMART_COUPON_CODE_CHANGE') : JText::_('COM_VIRTUEMART_COUPON_CODE_ENTER');
		$this->assignRef('coupon_text', $coupon_text);
	}

	/*
	 * lSelectShipment
	* find al shipment rates available for this cart
	*
	* @author Valerie Isaksen
	*/

	private function lSelectShipment() {
		$found_shipment_method=false;
		$shipment_not_found_text = JText::_('COM_VIRTUEMART_CART_NO_SHIPPING_METHOD_PUBLIC');
		$this->assignRef('shipment_not_found_text', $shipment_not_found_text);

		$shipments_shipment_rates=array();
		if (!$this->checkShipmentMethodsConfigured()) {
			$this->assignRef('shipments_shipment_rates',$shipments_shipment_rates);
			$this->assignRef('found_shipment_method', $found_shipment_method);
			return;
		}
		$selectedShipment = (empty($this->cart->virtuemart_shipmentmethod_id) ? 0 : $this->cart->virtuemart_shipmentmethod_id);
		if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
		JPluginHelper::importPlugin('vmshipment');
		$dispatcher = JDispatcher::getInstance();
		$shipments_shipment_rates = $dispatcher->trigger('plgVmDisplayListFE', array('shipment','cart' => $this->cart, 'selectedShipment' => $selectedShipment));
		// if no shipment rate defined
		$found_shipment_method = false;
		vmdebug('$shipments_shipment_rates',$shipments_shipment_rates);
		foreach ($shipments_shipment_rates as $shipment_shipment_rates) {
			if (is_array($shipment_shipment_rates)) {
				foreach ($shipment_shipment_rates as $shipment_shipment_rate) {
					$found_shipment_method = true;
					break;
				}
			}
		}
/*		if (!$found_shipment_method) {
// 			$link=''; // todo
// 			$admintext = ('COM_VIRTUEMART_CART_NO_SHIPPING_METHOD_FITTING_ADMIN', '<a href="'.$link.'">'.$link.'</a>')
			$shipment_not_found_text = vmInfo($admintext,'COM_VIRTUEMART_CART_NO_SHIPPING_METHOD_PUBLIC');;
		}*/
		/*
	  $layoutName='select_shipment'; // by dafault should be the same
		if (!$found_shipment_method) {
		//  change the view?
		$layoutName='default';
		$this->assignRef('select_shipment_text',JText::_('COM_VIRTUEMART_CART_NO_SHIPPING_METHOD'), JText::_('COM_VIRTUEMART_CART_NO_SHIPPING_METHOD_PUBLIC'));
		}
	 */
$shipment_not_found_text = JText::_('COM_VIRTUEMART_CART_NO_SHIPPING_METHOD_PUBLIC');
		$this->assignRef('shipment_not_found_text', $shipment_not_found_text);
		$this->assignRef('shipments_shipment_rates', $shipments_shipment_rates);
		$this->assignRef('found_shipment_method', $found_shipment_method);
		return;
	}

	/*
	 * lSelectPayment
	* find al payment available for this cart
	*
	* @author Valerie Isaksen
	*/

	private function lSelectPayment() {

		$payment_not_found_text='';
		$payments_payment_rates=array();
		if (!$this->checkPaymentMethodsConfigured()) {
			$this->assignRef('paymentplugins_payments', $payments_payment_rates);
			$this->assignRef('found_payment_method', $found_payment_method);
		}

		$selectedPayment = empty($this->cart->virtuemart_paymentmethod_id) ? 0 : $this->cart->virtuemart_paymentmethod_id;

		if(!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS.DS.'vmpsplugin.php');
		JPluginHelper::importPlugin('vmpayment');
		$dispatcher = JDispatcher::getInstance();
		$paymentplugins_payments = $dispatcher->trigger('plgVmDisplayListFE', array('payment','cart' => $this->cart, 'checked' => $selectedPayment));
		// if no payment defined

		$found_payment_method = false;
		foreach ($paymentplugins_payments as $paymentplugin_payments) {
			if (is_array($paymentplugin_payments)) {
				foreach ($paymentplugin_payments as $paymentplugin_payment) {
					$found_payment_method = true;
					break;
				}
			}
		}

		if (!$found_payment_method) {
		    $link=''; // todo
		    $payment_not_found_text = JText::sprintf('COM_VIRTUEMART_CART_NO_PAYMENT_METHOD_PUBLIC', '<a href="'.$link.'">'.$link.'</a>');
		}
		$this->assignRef('payment_not_found_text', $payment_not_found_text);
		$this->assignRef('paymentplugins_payments', $paymentplugins_payments);
		$this->assignRef('found_payment_method', $found_payment_method);
	}

	private function lOrderDone() {
		$html = JRequest::getVar('html', JText::_('COM_VIRTUEMART_ORDER_PROCESSED'), 'post', 'STRING', JREQUEST_ALLOWRAW);
		$this->assignRef('html', $html);

		//Show Thank you page or error due payment plugins like paypal express
	}

	private function checkPaymentMethodsConfigured() {
		if (!class_exists('VirtueMartModelPaymentmethod'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'paymentmethod.php');
		//For the selection of the payment method we need the total amount to pay.
		$paymentModel = new VirtueMartModelPaymentmethod();
		$payments = $paymentModel->getPayments(true, false);
		if (empty($payments)) {

			$text = '';
			if (!class_exists('Permissions'))
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'permissions.php');
			if (Permissions::getInstance()->check("admin,storeadmin")) {
				$uri = JFactory::getURI();
				$link = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=paymentmethod';
				$text = JText::sprintf('COM_VIRTUEMART_NO_PAYMENT_METHODS_CONFIGURED_LINK', '<a href="' . $link . '">' . $link . '</a>');
			}

			vmInfo('COM_VIRTUEMART_NO_PAYMENT_METHODS_CONFIGURED', $text);

			$tmp = 0;
			$this->assignRef('found_payment_method', $tmp);

			return false;
		}
		return true;
	}

	private function checkShipmentMethodsConfigured() {
		if (!class_exists('VirtueMartModelShipmentMethod'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'shipmentmethod.php');
		//For the selection of the shipment method we need the total amount to pay.
		$shipmentModel = new VirtueMartModelShipmentmethod();
		$shipments = $shipmentModel->getShipments();
		if (empty($shipments)) {

			$text = '';
			if (!class_exists('Permissions'))
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'permissions.php');
			if (Permissions::getInstance()->check("admin,storeadmin")) {
				$uri = JFactory::getURI();
				$link = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=shipmentmethod';
				$text = JText::sprintf('COM_VIRTUEMART_NO_SHIPPING_METHODS_CONFIGURED_LINK', '<a href="' . $link . '">' . $link . '</a>');
			}

			vmInfo('COM_VIRTUEMART_NO_SHIPPING_METHODS_CONFIGURED', $text);

			$tmp = 0;
			$this->assignRef('found_shipment_method', $tmp);

			return false;
		}
		return true;
	}

}

//no closing tag
