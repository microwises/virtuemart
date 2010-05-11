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
		
		/* Add the cart title to the pathway */
		$pathway->addItem(JText::_('VM_CART_TITLE'));
		$mainframe->setPageTitle(JText::_('VM_CART_TITLE'));
		
		/* Load the cart helper */
		$this->loadHelper('cart');
		
		$cart = cart::getCart();
		$this->assignRef('cart', $cart);

		//For User address
		$_currentUser =& JFactory::getUser();
		$lists['current_id'] = $_currentUser->get('id');
		
		$user = $this->getModel('user');
		$this->assignRef('user', $user);
		
		$userDetails = $user->getUser();
		$_contactDetails = $user->getContactDetails();
		
		$userFieldsModel = $this->getModel('userfields', 'VirtuemartModel');
		
		// Shipping address(es)
		$_addressList = $user->getUserAddressList($userDetails->JUser->get('id') , 'ST');
		if (($_c = count($_addressList)) == 0) {
			$lists['shipTo'] = JText::_('VM_USER_NOSHIPPINGADDR');
		} else {
			$_shipTo = array();
			for ($_i = 0; $_i < $_c; $_i++) {
				$_shipTo[] = '<li>'.'<a href="index.php'
									.'?option=com_virtuemart'
									.'&view=user'
									.'&task=edit'
									.'&cid[]='.$_addressList[$_i]->user_id
									.'&shipto='.$_addressList[$_i]->user_info_id
								. '">'.$_addressList[$_i]->address_type_name.'</a>'.'</li>';
			
			}
			$lists['shipTo'] = '<ul>' . join('', $_shipTo) . '</ul>';
		}

		$_userFields = $userFieldsModel->getUserFields(
				 'account'
				, array() // Default toggles
				, array('delimiter_userinfo', 'username', 'email', 'password', 'password2'
					, 'agreed', 'address_type', 'bank') // Skips
		);
		if (($_addressCount = count($userDetails->userInfo)) == 0) {
			$_userDetailsList = null;
			$_userInfoID = null;
		} else {
			$_userDetailsList = current($userDetails->userInfo);
			for ($_i = 0; $_i < $_addressCount; $_i++) {
				if ($_userDetailsList->address_type == 'BT') {
					$_userInfoID = $_userDetailsList->user_info_id;
					reset($userDetails->userInfo);
					break;
				}
				$_userDetailsList = next($userDetails->userInfo);
			}
		}
		$userFields = $userFieldsModel->getUserFieldsByUser(
				 $_userFields
				,$_userDetailsList
		);

		// Bank details, reuse the current $_userDetailsList pointer that holds the BT info
		$_bankFields = $userFieldsModel->getUserFields(
			 'bank'
			, array() // Default toggles
		);
		$_bankInfo = $userFieldsModel->getUserFieldsByUser(
			 $_bankFields
			,$_userDetailsList
		);
		
		$this->assignRef('lists', $lists);
		$this->assignRef('userDetails', $userDetails);
		$this->assignRef('bankInfo', $_bankInfo);
		$this->assignRef('userFields', $userFields);
		$this->assignRef('userInfoID', $_userInfoID);
		$this->assignRef('contactDetails', $_contactDetails);
		
		
		
		/* Get the products for the cart */
		$model = $this->getModel('cart');
		$products = $model->getCartProducts($cart);
		$this->assignRef('products', $products);
		
		/* Get the prices for the cart */
		$prices = $model->getCartPrices($cart);
		$this->assignRef('prices', $prices);
		?><pre><?php
//		print_r($prices);
		?></pre><?php
		
		
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
?>