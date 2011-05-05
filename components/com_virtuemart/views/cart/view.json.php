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
* @version $Id: view.html.php 2797 2011-03-01 11:33:24Z enytheme $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport( 'joomla.application.component.view');

/**
* View for the shopping cart
* @package VirtueMart
* @author Max Milbers
* @author Oscar van Eijk
*/
class VirtuemartViewCart extends JView {

	private $_cart ;
	private $cartdata = null;
	private $data = null;

    function display($tpl = null)
    {
		if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
		$this->_cart = VirtueMartCart::getCart(false);
		$this->cartdata = $this->_cart->prepareCartData();
		$this->data->cart_show = '<a style ="float:right;" href="'.JRoute::_("index.php?option=com_virtuemart&view=cart").'">'.JText::_('COM_VIRTUEMART_CART_SHOW').'</a>';
		$this->data->billTotal = JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL').' : <strong>'. $this->cartdata->prices['billTotal'] .'</strong>';
		
		//self::prepareCartData($prepareCartData);
		self::getProductData();
		echo json_encode($this->data);
		return;
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
			$product->category_id = $this->_cart->getCardCategoryId($product->product_id);
			//Create product URL
			$url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$product->product_id.'&category_id='.$product->category_id);

			// @todo Add variants
			$this->data->products[$i]['product_name'] = JHTML::link($url, $product->product_name);
			$this->data->products[$i]['customfieldsCart'] ='';
//			/* Add the variants */
			if (!is_int($priceKey)) {
				if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
				$calculator = calculationHelper::getInstance();
				$variantmods = $calculator->parseModifier($priceKey);

				foreach ($variantmods as $variantmod) {
						$row=0 ;
						foreach($variantmod as $variant=>$selected){
//							dump($selected,$priceKey.' are vari ID : '.$variant);
//							dump($product->customfieldsCart[$row],' are vari ID : '.$row);
							$this->data->products[$i]['customfieldsCart'] .= '<br/ ><b>'.$product->customfieldsCart[$row]->custom_title.' : </b>'.$product->customfieldsCart[$row]->options[$selected]->custom_value;
							$row++;
						}
				}
			}
			$this->data->products[$i]['product_name'] .= $this->data->products[$i]['customfieldsCart'] ;
			$this->data->products[$i]['product_sku'] = $product->product_sku;

			//** @todo WEIGHT CALCULATION
			//$weight_subtotal = vmShippingMethod::get_weight($product["product_id"]) * $product->quantity'];
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

		if ($this->data->totalProduct>1) $this->data->totalProductTxt = JText::sprintf('COM_VIRTUEMART_AJAX_PRODUCTS_LBL', $this->data->totalProduct);
		else if ($this->data->totalProduct == 1) $this->data->totalProductTxt = JText::_('COM_VIRTUEMART_AJAX_PRODUCT_LBL');
		else $this->data->totalProductTxt = JText::_('COM_VIRTUEMART_EMPTY_CART');
	}

}
//no closing tag