<?php

defined('_JEXEC') or die('Restricted access');
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
// echo sprintf("[%s]\n",      $s); // affichage d'une cha�ne standard
// echo sprintf("[%10s]\n",    $s); // justification � droite avec des espaces
// echo sprintf("[%-10s]\n",   $s); // justification � gauche avec des espaces
// echo sprintf("[%010s]\n",   $s); // l'espacement nul fonctionne aussi sur les cha�nes
// echo sprintf("[%'#10s]\n",  $s); // utilisation du caract�re personnalis� de s�paration '#'
// echo sprintf("[%10.10s]\n", $t); // justification � gauche mais avec une coupure � 10 caract�res
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
// TODO Temp fix !!!!! *********************************>>>
$this->cart->cartData['paymentDiscount'] = $this->cart->cartData['shippingTax'] = '';
//$skuPrint = echo sprintf( "%64.64s",strtoupper (JText::_('COM_VIRTUEMART_CART_SKU') ) ) ;
$p = array();
// Head of table
echo strip_tags(JText::sprintf('COM_VIRTUEMART_CART_MAIL_SHOPPER_TOTAL_ORDER', $this->cart->prices['billTotal'])) . "\n";
echo sprintf("%'-64.64s", '') . "\n";
echo JText::_('COM_VIRTUEMART_ORDER_ITEM') . "\n";
foreach ($this->cart->products as $prow) {
    echo "\n";
    echo $prow->quantity . ' X ' . $prow->product_name . '(' . strtoupper(JText::_('COM_VIRTUEMART_CART_SKU')) . $prow->product_sku . ')' . "\n";
    echo JText::_('COM_VIRTUEMART_CART_PRICE')   . $prow->salesPrice . "\n";
    echo JText::_('COM_VIRTUEMART_CART_TOTAL') . $prow->subtotal_with_tax . ' (' . JText::_('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT') . ':' . $prow->subtotal_tax_amount . ')';
    echo "\n";
}
echo sprintf("%'-64.64s", '');
echo "\n";
//SubTotal, Tax, Shipping, Coupon Discount and Total listing
foreach ($this->cart->cartData['dBTaxRulesBill'] as $rule) {
    echo $rule['calc_name'] . ':' . $this->cart->prices[$rule['virtuemart_calc_id'] . 'Diff'];
    echo "\n";
}
// Coupon
if (!empty($this->cart->cartData['couponCode'])) {
    echo JText::_('COM_VIRTUEMART_COUPON_DISCOUNT') . ':' . $this->cart->cartData['couponCode'] . ' : ' . $this->cart->cartData['couponDescr'] . ' ' . JText::_('COM_VIRTUEMART_CART_PRICE') . ':' . $this->cart->cartData['salesPriceCoupon'] . '(' . $this->cart->cartData['couponTax'] . ')';
    echo "\n";
}

echo strtoupper(JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_MODE_LBL')) . ' (' . strip_tags($this->cart->cartData['shippingName']) . ' ) ' . "\n";
echo JText::_('COM_VIRTUEMART_CART_PRICE') . ':' . $this->cart->prices['salesPriceShipping'] . '(' . JText::_('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT') . $this->cart->prices['shippingTax'] . ')';
echo "\n";
echo strtoupper(JText::_('COM_VIRTUEMART_ORDER_PRINT_PAYMENT_LBL')) . ' (' . strip_tags($this->cart->cartData['paymentName']) . ' ) ' . "\n";
echo JText::_('COM_VIRTUEMART_CART_PRICE') . ':' . $this->cart->prices['salesPricePayment'] . '(' . JText::_('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT') . ':' . $this->cart->prices['paymentDiscount'] . ')';
echo "\n";
foreach ($this->cart->cartData['taxRulesBill'] as $rule) {
    echo $rule['calc_name'], '-', '-', $this->cart->prices[$rule['virtuemart_calc_id'] . 'Diff'];
    echo "\n";
}

foreach ($this->cart->cartData['dATaxRulesBill'] as $rule) {
    echo $rule['calc_name'], '-', '-', $this->cart->prices[$rule['virtuemart_calc_id'] . 'Diff'];
    echo "\n";
}
echo sprintf("%'-64.64s", '') . "\n";
// total order
echo JText::_('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT') . ' : ' . $this->cart->prices['billDiscountAmount'] . '(' . JText::_('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT') . ':' . $this->cart->prices['billTaxAmount'] . ')' . "\n";

echo strtoupper(JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL')) . ' : ' . $this->cart->prices['billTotal'];
echo "\n";
?>
