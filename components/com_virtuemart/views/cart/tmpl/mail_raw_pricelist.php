<?php defined('_JEXEC') or die('Restricted access');
/**
*
* Layout for the shopping cart
*
* @package	VirtueMart
* @subpackage Cart
* @author Max Milbers
*
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
*/


// Plain text formating
// printf("[%s]\n",      $s); // affichage d'une chaîne standard
// printf("[%10s]\n",    $s); // justification à droite avec des espaces
// printf("[%-10s]\n",   $s); // justification à gauche avec des espaces
// printf("[%010s]\n",   $s); // l'espacement nul fonctionne aussi sur les chaînes
// printf("[%'#10s]\n",  $s); // utilisation du caractère personnalisé de séparation '#'
// printf("[%10.10s]\n", $t); // justification à gauche mais avec une coupure à 10 caractères
// $s = 'monkey';
// [monkey]
// [    monkey]
// [monkey    ]
// [0000monkey]
// [####monkey]
// [many monke]

// Check to ensure this file is included in Joomla!

// jimport( 'joomla.application.component.view');
// $viewEscape = new JView();
// $viewEscape->setEscape('htmlspecialchars');

		//of course, some may argue that the $product_rows should be generated in the view.html.php, but
		//
		$product_rows = array();

		$i=0;
		foreach ($this->cart->products as $priceKey=>$product){
			// Added for the zone shipping module
			//$vars["zone_qty"] += $product["quantity"];

			$product->virtuemart_category_id = $this->cart->getCardCategoryId($product->virtuemart_product_id);
			/* Create product URL */
			$url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id);

			/** @todo Add variants */
			$product_rows[$i]['product_name'] = $product->product_name;



			// Add the variants
			if(!empty($product->customfieldsCart)){
				$product_rows[$i]['customfieldsCart'] = ShopFunctions::customFieldInCartDisplay($priceKey,$product->customfieldsCart);
			} else {
				$product_rows[$i]['customfieldsCart'] ='';
			}


			$product_rows[$i]['product_sku'] = $product->product_sku;

			/* Product PRICE */
			$product_rows[$i]['salesPrice'] = empty($this->prices[$priceKey]['salesPrice'])? 0:$this->prices[$priceKey]['salesPrice'];
			$product_rows[$i]['basePriceWithTax'] = empty($this->prices[$priceKey]['salesPrice'])? 0:$this->prices[$priceKey]['basePriceWithTax'];
//			$product_rows[$i]['basePriceWithTax'] = $this->prices[$priceKey]['basePriceWithTax'];
			$product_rows[$i]['subtotal'] = $this->prices[$priceKey]['subtotal'];
			$product_rows[$i]['subtotal_tax_amount'] = $this->prices[$priceKey]['subtotal_tax_amount'];
			$product_rows[$i]['subtotal_discount'] = $this->prices[$priceKey]['subtotal_discount'];
			$product_rows[$i]['subtotal_with_tax'] = $this->prices[$priceKey]['subtotal_with_tax'];
			$product_rows[$i]['quantity'] = $product->quantity;
			$i++;
		} // End of for loop through the Cart

// TODO Temp fix !!!!! *********************************>>>
$this->cartData['paymentDiscount'] = $this->cartData['shippingTax'] ='';
$skuPrint = shopFunctionsF::tabPrint(64,'COM_VIRTUEMART_CART_NAME',1);
		
// Head of table
echo shopFunctionsF::tabPrint(64,'COM_VIRTUEMART_CART_NAME',1).shopFunctionsF::tabPrint(20,'COM_VIRTUEMART_CART_PRICE',1).shopFunctionsF::tabPrint(10,'COM_VIRTUEMART_CART_QUANTITY',1).shopFunctionsF::tabPrint(10,'COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT',1).shopFunctionsF::tabPrint(10,'COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT',1).shopFunctionsF::tabPrint(10,'COM_VIRTUEMART_CART_TOTAL',1);
// list render
 foreach( $product_rows as $prow ) { 
echo shopFunctionsF::tabPrint(64,$prow['product_name']).shopFunctionsF::tabPrint(20,$prow['salesPrice']).shopFunctionsF::tabPrint(10,$prow['quantity']).shopFunctionsF::tabPrint(10,$prow['subtotal_tax_amount']).shopFunctionsF::tabPrint(10,$prow['subtotal_discount']).shopFunctionsF::tabPrint(10,$prow['subtotal_with_tax']);
echo $skuPrint. shopFunctionsF::tabPrint(64,$prow['product_sku']);
}
 //SubTotal, Tax, Shipping, Coupon Discount and Total listing
echo sprintf("\n%94.94s",strtoupper ( JText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_PRICES_TOTAL') ) ).shopFunctionsF::tabPrint(10,$this->prices['taxAmount']).shopFunctionsF::tabPrint(10,$this->prices['discountAmount']).shopFunctionsF::tabPrint(10,$this->prices['salesPrice']);
foreach($this->cartData['dBTaxRulesBill'] as $rule){
	echo sprintf("\n%94.94s",strtoupper ($rule['calc_name']) ).shopFunctionsF::tabPrint(10,$this->prices[$rule['virtuemart_calc_id'].'Diff']);
}
// Coupon
if (!empty($this->cartData['couponCode'])) {
	echo shopFunctionsF::tabPrint(64,$this->cartData['couponDescr']).'(' .shopFunctionsF::tabPrint(20,$this->cartData['couponCode']).')' .shopFunctionsF::tabPrint(10,$this->cartData['couponTax']) .shopFunctionsF::tabPrint(10,$this->cartData['salesPriceCoupon']) .shopFunctionsF::tabPrint(10,$this->cartData['couponCode']);
}
echo sprintf("\n%94.94s",strtoupper ($this->cartData['shippingName']) ).shopFunctionsF::tabPrint(10,$this->cartData['shippingTax']);//.shopFunctionsF::tabPrint(10,$this->cartData['salesPriceShipping']);
echo shopFunctionsF::tabPrint(64,$this->cartData['paymentName']).shopFunctionsF::tabPrint(10,$this->cartData['paymentDiscount']);//.shopFunctionsF::tabPrint(10,$this->cartData['salesPricePayment']);

foreach($this->cartData['taxRulesBill'] as $rule){ 
	echo sprintf("\n%94.94s",strtoupper ($rule['calc_name']) ).shopFunctionsF::tabPrint(10,$this->prices[$rule['virtuemart_calc_id'].'Diff']);
}
		
foreach($this->cartData['dATaxRulesBill'] as $rule){ 
	echo sprintf("\n%94.94s",strtoupper ($rule['calc_name']) ).shopFunctionsF::tabPrint(10,$this->prices[$rule['virtuemart_calc_id'].'Diff']);
}
// total order 
echo sprintf("\n%94.94s",strtoupper (JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ) ).shopFunctionsF::tabPrint(10,$this->prices['billTaxAmount']).shopFunctionsF::tabPrint(10,$this->prices['billDiscountAmount']);
?>
Helllo  PRICE LIST !!
