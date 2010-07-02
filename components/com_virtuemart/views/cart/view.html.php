<?php
/**
*
* View for the shopping cart
*
* @package	VirtueMart
* @subpackage 
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
* @author RolandD
*/
class VirtueMartViewCart extends JView {
	
	public function display($tpl = null) {	  	    
		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();
		
		
		$layoutName = JRequest::getVar('layout', $this->getLayout());
		$this->assignRef('layoutName', $layoutName);
//		echo 'my layout:'.$layoutName;
		
		/* Load the cart helper */
		$this->loadHelper('cart');
		
		$cart = cart::getCart(false);
		$this->assignRef('cart', $cart);
		
		if($layoutName=='selectshipper'){
			//For the selection of the shipper we need the weight and maybe the dimension.
			//Just for developing
			$cartweight= '2';
			$this->assignRef('cartweight', $cartweight);
			
			$shippingCarrierModel = $this->getModel('shippingcarrier');
			$shippingCarriers = $shippingCarrierModel->getShippingCarrierRates($cartweight);
			
			$this->assignRef('shippingCarriers',$shippingCarriers);
			$this->loadHelper('shopfunctions');
			
			/* Add the cart title to the pathway */
			$pathway->addItem(JText::_('VM_CART_SELECTSHIPPER'));
			$mainframe->setPageTitle(JText::_('VM_CART_SELECTSHIPPER'));
			
		} else if($layoutName=='selectpayment'){
			//For the selection of the payment method we need the total amount to pay.
			$paymentModel = $this->getModel('paymentmethod');
			
			$selectedPaym = empty($cart['paym_id']) ? 0 : $cart['paym_id'];
			$selectedCC = empty($cart['creditcard_id']) ? 0 : $cart['creditcard_id'];
			$this->assignRef('selectedPaym',$selectedPaym);
			$this->assignRef('selectedCC',$selectedCC);

			$payments = $paymentModel->getPayms(false,true);
			$withCC=false;
			foreach($payments as $item){
				if(isset($item->accepted_creditcards)){
					$withCC=true;
				}
			}
			$this->assignRef('withCC',$withCC);

			$this->assignRef('paymentModel',$paymentModel);
			$this->assignRef('payments',$payments);
			
			/* Add the cart title to the pathway */
			$pathway->addItem(JText::_('VM_CART_SELECTPAYMENT'));
			$mainframe->setPageTitle(JText::_('VM_CART_SELECTPAYMENT'));
			
		} else if($layoutName=='orderdone'){
			//Show Thank you page or error due payment plugins like paypal express
			
			/* Add the cart title to the pathway */
			$pathway->addItem(JText::_('VM_CART_THANKYOU'));
			$mainframe->setPageTitle(JText::_('VM_CART_THANKYOU'));
			
		} else if($layoutName=='cart' || $layoutName=='mailshopper' || $layoutName=='mailvendor'){
			//cart and pricelist
			/* Get the products for the cart */
			$model = $this->getModel('cart');
			$products = $model->getCartProducts($cart);
			$this->assignRef('products', $products);
					
			$prices = $model->getCartPrices($cart);
			$this->assignRef('prices', $prices);
	
			//For User address
			$_currentUser =& JFactory::getUser();
			$lists['current_id'] = $_currentUser->get('id');
			$this->assignRef('user_id', $lists['current_id']);
			if($lists['current_id']){
				$user = $this->getModel('user');
				$user->setId($lists['current_id']);
				$this->assignRef('user', $user);
				
				$userDetails = $user->getUser();
				$_contactDetails = $user->getContactDetails();
				
				$userFieldsModel = $this->getModel('userfields', 'VirtuemartModel');
				
				// Shipping address(es)
				$_addressBT = $user->getUserAddressList($userDetails->JUser->get('id') , 'BT');
				// Overwrite the address name for display purposes
				$_addressBT[0]->address_type_name = JText::_('VM_ACC_BILL_DEF');
				$_addressST = $user->getUserAddressList($userDetails->JUser->get('id') , 'ST');
				$_addressList = array_merge(
					array($_addressBT[0])// More BT addresses can exist for shopowners :-(
					, $_addressST
				);
				for ($_i = 0; $_i < count($_addressList); $_i++) {
					$_addressList[$_i]->address_type_name = '<a href="index.php'
										.'?option=com_virtuemart'
										.'&view=user'
										.'&layout=edit'
										.'&rview=cart'
										.'&cid[]='.$_addressList[$_i]->user_id
										.(($_i == 0) ? '&tab=1#BT' : '&shipto='.$_addressList[$_i]->user_info_id) // BT = BillTo 
									. '">'.$_addressList[$_i]->address_type_name.'</a>'.'<br />';
				}
	//			for ($_i = 0; $_i < count($_addressList); $_i++) {
	//				$_addressList[$_i]->address_type_name = '<a href="index.php'
	//									.'?option=com_virtuemart'
	//									.'&view=user'
	//									.'&task=editaddress'
	////									.'&addrtype='.($_i == 0) ? '&tab=1#BT' : 'ST'
	//									.($_i == 0) ? '&tab=1#BT':'&addrtype=ST'
	//									.'&cid[]='.$_addressList[$_i]->user_id
	//									.'&shipto='.$_addressList[$_i]->user_info_id
	////									.(($_i == 0) ? '&tab=1#BT' : '&shipto='.$_addressList[$_i]->user_info_id) // BT = BillTo 
	//								. '">'.$_addressList[$_i]->address_type_name.'</a>'.'<br />';
	//			}
				$_selectedAddress = (
					empty($cart['address_shipto_id'])
						? $_addressList[0]->user_info_id // Defaults to BillTo
						: $cart['address_shipto_id']
					);
				
				$lists['shipTo'] = JHTML::_('select.radiolist', $_addressList, 'shipto', null, 'user_info_id', 'address_type_name', $_selectedAddress);
				$lists['billTo'] = $_addressList[0]->user_info_id;
				
				$this->assignRef('lists', $lists);
				$this->assignRef('userDetails', $userDetails);
				
				/* Add the cart title to the pathway */
				$pathway->addItem(JText::_('VM_CART_TITLE'));
				$mainframe->setPageTitle(JText::_('VM_CART_TITLE'));
	
			} else {
	
				//This else is for anonymous case
				$_address = array();
				$_address[] = new stdClass();
	
				$_addressBT[0]->address_type_name = '<a href="index.php'
										.'?option=com_virtuemart'
										.'&view=user'
										.'&task=editaddress'
										.'&cid[]=0'			//I think this cid=0 says already that there is a dynID
										.'&shipto=BT_dynID'
									. '">'.JText::_('VM_ACC_BILL_DEF').'</a>'.'<br />';
				$_addressBT[0]->user_info_id = 'BT_dynID';	//Do we need that?
				$lists['shipTo'] = JHTML::_('select.radiolist', $_addressBT, 'shipto', null, 'user_info_id', 'address_type_name', 'BT_dynID');
				$lists['billTo'] = 'BT_dynID';
				$this->assignRef('lists', $lists);
			}
		}	
			
		if($layoutName=='mailshopper' || $layoutName=='mailvendor'){
			$store = $this->getModel('store','VirtuemartModel');
			if(empty($cart['vendor_id'])) $cart['vendor_id']=1;
			$store->setId($cart['vendor_id']);
			$_store = $store->getStore();
			$this->assignRef('store',$_store);
			
			// TODO add orders, for the orderId
			// TODO add registering userdata
			// In general we need for every mail the shopperdata (with group), the vendor data, shopperemail, shopperusername, and so on
		}		
		
		/* Get a continue link */
		$category_id = JRequest::getInt('category_id');
		$product_id = JRequest::getInt('product_id');
		$manufacturer_id = JRequest::getInt('manufacturer_id');
		
		if (!empty($category_id)) $continue_link = JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$category_id);
		elseif (empty($category_id) && !empty($product_id)) $continue_link = JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$this->get('categoryid'));
		elseif (!empty($manufacturer_id)) $continue_link = JRoute::_('index.php?option=com_virtuemart&view=manufacturer&manufacturer_id='.$manufacturer_id);
		else $continue_link = JRoute::_('index.php?option=com_virtuemart');
		
		$this->assignRef('continue_link', $continue_link);
		
		
		parent::display($tpl);
	}
}

//no closing tag