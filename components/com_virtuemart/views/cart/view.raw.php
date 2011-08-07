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
		$this->_cart = VirtueMartCart::getCart(false);
		$this->cartdata = $this->_cart->prepareCartData();
		$this->data->cart_show = '<a style ="float:right;" href="'.JRoute::_("index.php?option=com_virtuemart&view=cart").'">'.JText::_('COM_VIRTUEMART_CART_SHOW').'</a>';
		$this->data->billTotal = JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL').' : <strong>'. $this->cartdata->prices['billTotal'] .'</strong>';

		//self::prepareCartData($prepareCartData);
		$this->getProductData();
		echo json_encode($this->data);
		return;
    }

	public function renderMail ($doVendor=false) {
		$tpl = ($doVendor) ? 'mail_raw_vendor' : 'mail_raw_shopper';
		if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');

		$this->_cart = VirtueMartCart::getCart(false);
		$this->assignRef('cart', $this->_cart);
		$this->assignRef('lists', $this->lists);
		$this->prepareCartData();
		$this->prepareUserData();
		$this->prepareAddressDataInCart();
		$this->prepareMailData();

		$this->subject = ($doVendor) ? JText::sprintf('COM_VIRTUEMART_NEW_ORDER_CONFIRMED',	$this->shopperName, $this->order['details']['BT']->order_total, $this->order['details']['BT']->order_number) : JText::sprintf('COM_VIRTUEMART_NEW_ORDER_CONFIRMED', $this->vendor->vendor_store_name, $this->order['details']['BT']->order_total, $this->order['details']['BT']->order_number, $this->order['details']['BT']->order_pass);

		$this->doVendor = true;
		$vendorModel = $this->getModel('vendor');
		$this->vendorEmail = $vendorModel->getVendorEmail($this->vendor->virtuemart_vendor_id);
		$this->layoutName = $tpl;
		$this->setLayout($tpl);
		parent::display();
	}

	private function prepareUserData(){

		//For User address
		$_currentUser = JFactory::getUser();
		$this->lists['current_id'] = $_currentUser->get('id');
//		$this->assignRef('virtuemart_user_id', $this->lists['current_id']);
		if($this->lists['current_id']){
			$this->_user = $this->getModel('user');
			$this->_user->setCurrent();
			if(!$this->_user){

			}else{
				$this->assignRef('user', $this->_user);

				$this->_userDetails = $this->_user->getUser();

				//This are other contact details, like used in CB or so.
	//			$_contactDetails = $this->_user->getContactDetails();

				$this->assignRef('userDetails', $this->_userDetails);
			}
		}
	}

	private function prepareCartData(){

		/* Get the products for the cart */
		$prepareCartData = $this->_cart->prepareCartData();

		$this->assignRef('automaticSelectedShipping', false);
		$this->assignRef('automaticSelectedPayment', false);

		$this->assignRef('prices', $prepareCartData->prices);

		$this->assignRef('cartData',$prepareCartData->cartData);
		$this->assignRef('calculator',$prepareCartData->calculator);

	}

	private function prepareAddressDataInCart(){

		$userFieldsModel = $this->getModel('userfields', 'VirtuemartModel');

		$BTaddress['fields']= array();
		if(!empty($this->_cart->BT)){
			$userFieldsBT = $userFieldsModel->getUserFieldsFor('cart','BT');
			$userAddressData = $this->_cart->getCartAdressData('BT');
			$BTaddress = $userFieldsModel->getUserFieldsByUser(
							 $userFieldsBT
							,$userAddressData
							);

		}
		$this->assignRef('BTaddress',$BTaddress['fields']);

		$STaddress['fields']= array();
		if(!empty($this->_cart->ST)){
			$userFieldsST = $userFieldsModel->getUserFieldsFor('cart','ST');
			$userAddressData = $this->_cart->getCartAdressData('ST');
			$STaddress = $userFieldsModel->getUserFieldsByUser(
							 $userFieldsST
							,$userAddressData
							);

		}
		$this->assignRef('STaddress',$STaddress['fields']);

/*		$userFieldsModel = $this->getModel('userfields');

		//Here we define the fields to skip
		$skips = array('delimiter_userinfo', 'delimiter_billto', 'username', 'password', 'password2'
						, 'address_type', 'bank');

		$BTaddress['fields']= array();
		if(!empty($this->_cart->BT)){

			//Here we get the fields
			$_userFieldsBT = $userFieldsModel->getUserFields(
				 'account'
				, array() // Default toggles
				,  $skips// Skips
			);
			if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
			$cart = VirtueMartCart::getCart(false);
			$BTaddress = $cart->getAddress(
				 $userFieldsModel
				,$_userFieldsBT
				,'BT'
			);
		}

		$this->assignRef('BTaddress',$BTaddress['fields']);

		$STaddress['fields']= array();
		if(!empty($this->_cart->ST)){

			$_userFieldsST = $userFieldsModel->getUserFields(
				'shipping'
				, array() // Default toggles
				, $skips
			);
			if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
			$cart = VirtueMartCart::getCart(false);
			$STaddress = $cart->getAddress(
				 $userFieldsModel
				,$_userFieldsST
				,'ST'
			);

		}

		$this->assignRef('STaddress',$STaddress['fields']);*/
	}

	private function prepareVendor(){

		$vendor = $this->getModel('vendor','VirtuemartModel');
		$vendor->setId($this->_cart->vendorId);
		$_vendor = $vendor->getVendor();
		$vendor->addImages($_vendor);
		$this->assignRef('vendor',$_vendor);
	}

	private function prepareMailData(){

		if(empty($this->vendor)) $this->prepareVendor();
		//TODO add orders, for the orderId
		//TODO add registering userdata
		// In general we need for every mail the shopperdata (with group), the vendor data, shopperemail, shopperusername, and so on
	}

	private function getProductData(){
		// Added for the zone shipping module
		//$vars["zone_qty"] = 0;
		$weight_total = 0;
		$weight_subtotal = 0;

		//of course, some may argue that the $this->data->products should be generated in the view.html.php, but
		//

		$this->data->totalProduct = 0;
		$i=0;
		foreach ($this->_cart->products as $priceKey=>$product){

			//$vars["zone_qty"] += $product["quantity"];
			$product->virtuemart_category_id = $this->_cart->getCardCategoryId($product->virtuemart_product_id);
			//Create product URL
			$url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id);

			// @todo Add variants
			$this->data->products[$i]['product_name'] = JHTML::link($url, $product->product_name);
			$this->data->products[$i]['customfieldsCart'] ='';
//			/* Add the variants */
			if (!is_int($priceKey)) {
				if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
				$calculator = calculationHelper::getInstance();
				$variantmods = $calculator->parseModifier($priceKey);
				$row = 0 ;
				foreach ($variantmods as $variant=>$selected){
							$this->data->products[$i]['customfieldsCart'] .= '<br/ ><b>'.$product->customfieldsCart[$row]->custom_title.' : </b>'.$product->customfieldsCart[$row]->options[$selected]->custom_value;
							$row++;
				}
			}
			$this->data->products[$i]['product_name'] .= $this->data->products[$i]['customfieldsCart'] ;
			$this->data->products[$i]['product_sku'] = $product->product_sku;

			//** @todo WEIGHT CALCULATION
			//$weight_subtotal = vmShippingMethod::get_weight($product["virtuemart_product_id"]) * $product->quantity'];
			//$weight_total += $weight_subtotal;



			$this->data->products[$i]['prices'] = $this->cartdata->prices[$priceKey]['subtotal_with_tax'];

			//** @todo Format price
//			$this->data->products[$i]['subtotal'] = $this->prices[$i]['priceWithoutTax'] * $product->quantity;
//			$this->data->products[$i]['subtotal_tax_amount'] = $this->prices[$i]['taxAmount'] * $product->quantity;
//			$this->data->products[$i]['subtotal_discount'] = $this->prices[$i]['discountAmount'] * $product->quantity;
//			$this->data->products[$i]['subtotal_with_tax'] = $this->prices[$i]['salesPrice'] * $product->quantity;

			$this->data->products[$i]['subtotal'] = $this->cartdata->prices[$priceKey]['subtotal'];
			$this->data->products[$i]['subtotal_tax_amount'] = $this->cartdata->prices[$priceKey]['subtotal_tax_amount'];
			$this->data->products[$i]['subtotal_discount'] = $this->cartdata->prices[$priceKey]['subtotal_discount'];
			$this->data->products[$i]['subtotal_with_tax'] = $this->cartdata->prices[$priceKey]['subtotal_with_tax'];

			// UPDATE CART / DELETE FROM CART
				$this->data->products[$i]['quantity'] = $product->quantity;
				$this->data->totalProduct += $product->quantity ;

			$i++;
		}
                JPlugin::loadLanguage('com_virtuemart', JPATH_ADMINISTRATOR); //  when AJAX it needs to be loaded manually here
		if ($this->data->totalProduct>1) $this->data->totalProductTxt = JText::sprintf('COM_VIRTUEMART_AJAX_PRODUCTS_LBL', $this->data->totalProduct);
		else if ($this->data->totalProduct == 1) $this->data->totalProductTxt = JText::_('COM_VIRTUEMART_AJAX_PRODUCT_LBL');
		else $this->data->totalProductTxt = JText::_('COM_VIRTUEMART_EMPTY_CART');
	}

}

//no closing tag