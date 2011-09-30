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
		if(!$layoutName) $layoutName = JRequest::getWord('layout', 'default');
		$this->assignRef('layoutName', $layoutName);
		$format = JRequest::getWord('format');
		// if(!class_exists('virtueMartModelCart')) require(JPATH_VM_SITE.DS.'models'.DS.'cart.php');
		// $model = new VirtueMartModelCart;

		if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
		$cart = VirtueMartCart::getCart(false);
		$this->assignRef('cart', $cart);

		//Why is this here, when we have view.raw.php
		if ($format == 'raw') {
			$cart->prepareCartViewData();
			JRequest::setVar( 'layout', 'mini_cart'  );
			$this->setLayout('mini_cart');
			$this->prepareContinueLink();
		}

		if($layoutName=='edit_coupon'){

			$cart->prepareCartViewData();
			$this->lSelectCoupon();
			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_OVERVIEW'),JRoute::_('index.php?option=com_virtuemart&view=cart'));
			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_SELECTCOUPON'));
			$document->setTitle(JText::_('COM_VIRTUEMART_CART_SELECTCOUPON'));

		} else if($layoutName=='select_shipper'){
			if(!class_exists('vmShipperPlugin')) require(JPATH_VM_SITE.DS.'helpers'.DS.'vmshipperplugin.php');
			JPluginHelper::importPlugin('vmshipper');
			$this->lSelectShipper();

			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_OVERVIEW'),JRoute::_('index.php?option=com_virtuemart&view=cart'));
			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_SELECTSHIPPER'));
			$document->setTitle(JText::_('COM_VIRTUEMART_CART_SELECTSHIPPER'));

		} else if($layoutName=='select_payment'){

			/* Load the cart helper */
//			$cartModel = $this->getModel('cart');
			if(!class_exists('vmPaymentPlugin')) require(JPATH_VM_SITE.DS.'helpers'.DS.'vmpaymentplugin.php');
			JPluginHelper::importPlugin('vmpayment');

			$this->lSelectPayment();

			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_OVERVIEW'),JRoute::_('index.php?option=com_virtuemart&view=cart'));
			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_SELECTPAYMENT'));
			$document->setTitle(JText::_('COM_VIRTUEMART_CART_SELECTPAYMENT'));

		} else if($layoutName=='order_done'){

			$this->lOrderDone();

			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_THANKYOU'));
			$document->setTitle(JText::_('COM_VIRTUEMART_CART_THANKYOU'));

		} else if($layoutName=='default' ){

			$cart->prepareCartViewData();

			$cart->prepareAddressRadioSelection();

			$this->prepareContinueLink();

			if($cart->getDataValidated()){
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

			if(!VmConfig::get('use_as_catalog')){
				$checkout_link_html = '<a class="checkout_link" href="javascript:document.checkoutForm.submit();" /><span>'.$text.'</span></a>';
			} else {
				$checkout_link_html = '';
			}
			$this->assignRef('checkout_link_html', $checkout_link_html);
		}
	//dump ($cart,'cart');
		$useSSL = VmConfig::get('useSSL',0);
		$useXHTML = true;
		$this->assignRef('useSSL', $useSSL);
		$this->assignRef('useXHTML', $useXHTML);

                // @max: quicknirty
                $cart->setCartIntoSession();
		shopFunctionsF::setVmTemplate($this,0,0,$layoutName);

		parent::display($tpl);
	}

	public function renderMail ($doVendor=false) {
		if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');

		$cart = VirtueMartCart::getCart(false);
		$this->assignRef('cart', $cart);
		$cart->prepareCartViewData();
		$cart->prepareMailData();

		if ($doVendor) {
			$this->subject = JText::sprintf('COM_VIRTUEMART_NEW_ORDER_CONFIRMED',	$this->shopperName,  $this->cart->prices['billTotal'],$this->order['details']['BT']->order_number);
			$recipient = 'vendor';
		} else {
			$this->subject = JText::sprintf('COM_VIRTUEMART_NEW_ORDER_CONFIRMED', $this->cart->vendor->vendor_store_name, $this->cart->prices['billTotal'], $this->order['details']['BT']->order_number, $this->order['details']['BT']->order_pass);
			$recipient = 'shopper';
		}
		$this->doVendor = true;
		if (VmConfig::get('order_mail_html')) $tpl = 'mail_html';
		else $tpl = 'mail_raw';
		$this->assignRef('recipient', $recipient);

		$vendorModel = $this->getModel('vendor');
		$this->vendorEmail = $vendorModel->getVendorEmail($cart->vendor->virtuemart_vendor_id);
		$this->layoutName = $tpl;
		$this->setLayout($tpl);
		parent::display();
	}

	private function prepareContinueLink(){
		// Get a continue link */
		$virtuemart_category_id = shopFunctionsF::getLastVisitedCategoryId();
		$categoryLink='';
		if($virtuemart_category_id){
			$categoryLink='&virtuemart_category_id='.$virtuemart_category_id;
		}
		$continue_link = JRoute::_('index.php?option=com_virtuemart&view=category'.$categoryLink);

		$continue_link_html = '<a class="continue_link" href="'.$continue_link.'" />'.JText::_('COM_VIRTUEMART_CONTINUE_SHOPPING').'</a>';
		$this->assignRef('continue_link_html', $continue_link_html);
		$this->assignRef('continue_link', $continue_link);
	}

	private function lSelectCoupon(){

		$this->couponCode =  (isset($this->cartData['couponCode']) ? $this->cartData['couponCode'] : '');
	}

	private function lSelectShipper(){
		$this->selectedShipper = (empty($this->cart->virtuemart_shippingcarrier_id) ? 0 : $this->cart->virtuemart_shippingcarrier_id);
	}

	private function lSelectPayment(){

		//For the selection of the payment method we need the total amount to pay.
		$paymentModel = $this->getModel('paymentmethod');

		$selectedPaym = empty($this->cart->virtuemart_paymentmethod_id) ? 0 : $this->cart->virtuemart_paymentmethod_id;
		$this->assignRef('selectedPaym',$selectedPaym);

		$this->payments = $paymentModel->getPayments(false,true);
		if(empty($this->payments)){

			$text ='';
			if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
			if (Permissions::getInstance()->check("admin,storeadmin")) {
				$uri = JFactory::getURI();
				$link = $uri->root().'administrator/index.php?option=com_virtuemart&view=paymentmethod';
				$text = JText::sprintf('COM_VIRTUEMART_NO_PAYMENT_METHODS_CONFIGURED_LINK','<a href="'.$link.'">'.$link.'</a>');
			}
			$app = JFactory::getApplication();
			$app -> enqueueMessage(JText::sprintf('COM_VIRTUEMART_NO_PAYMENT_METHODS_CONFIGURED',$text));
		}
	}

	private function lOrderDone(){
                $html = JRequest::getVar('html',JText::_('COM_VIRTUEMART_ORDER_PROCESSED'),'post','STRING',JREQUEST_ALLOWRAW);
		$this->assignRef('html', $html);

		//Show Thank you page or error due payment plugins like paypal express


	}





}

//no closing tag
