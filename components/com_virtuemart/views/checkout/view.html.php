<?php
/**
*
* View for the checkout
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
* View for the checkout
* @package VirtueMart
* @author RolandD
*/
class VirtueMartViewCheckout extends JView {
	
	public function display($tpl = null) {	  	    
		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();
		
		/* Add the cart title to the pathway */
		$pathway->addItem(JText::_('VM_CHECKOUT_TITLE'));
		$mainframe->setPageTitle(JText::_('VM_CHECKOUT_TITLE'));
		
		/* Load the cart helper */
		$this->loadHelper('cart');
		
		$cart = cart::getCart();
		$this->assignRef('cart', $cart);
		
		/* Get the products for the cart */
		$model = $this->getModel('cart');
		$products = $model->getCartProducts($cart);
		$this->assignRef('products', $products);
		
		/* Get the prices for the cart */
		$prices = $model->getCartPrices($cart);
		$this->assignRef('prices', $prices);
		?><pre><?php
		print_r($prices);
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