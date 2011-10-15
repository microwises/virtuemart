<?php
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
// jimport( 'joomla.application.component.view');
// $viewEscape = new JView();
// $viewEscape->setEscape('htmlspecialchars');

$u =& JURI::getInstance( );
$root = $u->toString( array( 'scheme', 'host') );

		?>
		<table class="cart-summary" cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<th align="left" width="220px" ><?php echo JText::_('COM_VIRTUEMART_CART_NAME') ?></th>
				<th align="left" ><?php echo JText::_('COM_VIRTUEMART_CART_SKU') ?></th>
 				<th align="center" width="60px" ><?php echo JText::_('COM_VIRTUEMART_CART_PRICE') ?></th>
				<th align="right" ><?php echo JText::_('COM_VIRTUEMART_CART_QUANTITY') ?></th>

                                        <?php if ( VmConfig::get('show_tax')) { ?>
                                <th align="right" width="60px"><?php  echo "<span  style='color:gray'>".JText::_('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT') ?></th>
				<?php } ?>
                                <th align="right" width="60px"><?php echo "<span  style='color:gray'>".JText::_('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT') ?></th>
				<th align="right" width="70px"><?php echo JText::_('COM_VIRTUEMART_CART_TOTAL') ?></th>
			</tr>
		<?php
		$i=1;
		foreach( $this->cart->products as $prow ) { ?>
			<tr valign="top" class="sectiontableentry<?php echo $i ?>">
				<td align="left" ><?php echo JHTML::link( $root.$prow->url, $prow->product_name).$prow->customfields; ?></td>
				<td align="left" ><?php echo $prow->product_sku ?></td>
				<td align="center" >
					<?php if ($prow->basePriceWithTax != $prow->salesPrice ) {
						echo '<span style="text-decoration:line-through">'.$prow->basePriceWithTax .'</span><br />' ;
					}
					echo $prow->salesPrice ;
					?>
				</td>
				<td align="right" >
					<?php echo $prow->quantity; ?>
				</td>

				<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php echo "<span  style='color:gray'>".$prow->subtotal_tax_amount."</span>" ?></td>
                                <?php } ?>
				<td align="right"><?php echo "<span  style='color:gray'>".$prow->subtotal_discount."</span>" ?></td>
				<td colspan="1" align="right"><?php echo $prow->subtotal_with_tax ?></td>
			</tr>
		<?php
			$i = 1 ? 2 : 1;

		} ?>
		<!--Begin of SubTotal, Tax, Shipping, Coupon Discount and Total listing -->
                  <?php if ( VmConfig::get('show_tax')) { $colspan=3; } else { $colspan=2; } ?>
		<tr>
			<td colspan="4">&nbsp;</td>

			<td colspan="<?php echo $colspan ?>"><hr /></td>
		</tr>
		  <tr class="sectiontableentry1">
			<td colspan="4" align="right"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_PRICES_TOTAL'); ?></td>

                        <?php if ( VmConfig::get('show_tax')) { ?>
			<td align="right"><?php echo "<span  style='color:gray'>".$this->cart->prices['taxAmount']."</span>" ?></td>
                        <?php } ?>
			<td align="right"><?php echo "<span  style='color:gray'>".$this->cart->prices['discountAmount']."</span>" ?></td>
			<td align="right"><?php echo $this->cart->prices['salesPrice'] ?></td>
		  </tr>

		<?php
		foreach($this->cart->cartData['dBTaxRulesBill'] as $rule){ ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="4" align="right"><?php echo $rule['calc_name'] ?> </td>

                                   <?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"> </td>
                                <?php } ?>
				<td align="right"><?php echo -$this->cart->prices[$rule['virtuemart_calc_id'].'Diff'];  ?> </td>
				<td align="right"><?php echo $this->cart->prices[$rule['virtuemart_calc_id'].'Diff'];   ?> </td>
			</tr>
			<?php
			if($i) $i=1; else $i=0;
		} ?>
		<?php
		if (VmConfig::get('coupons_enable')) {
		?>
			<tr class="sectiontableentry2">
				<td colspan="2" align="left"> </td>
				<?php if (!empty($this->cart->cartData['couponCode'])) { ?>
					<td colspan="2" align="left"><?php
						echo $this->cart->cartData['couponCode'] . ' (' . $this->cart->cartData['couponDescr'] . ')';
					?> </td>

                                        <?php if ( VmConfig::get('show_tax')) { ?>
					<td align="right"><?php echo $this->cart->prices['couponTax']; ?> </td>
                                        <?php } ?>
					<td align="right">&nbsp;</td>
					<td align="right"><?php echo $this->cart->prices['salesPriceCoupon']; ?> </td>
				<?php } else { ?>
					<td colspan="6" align="left">&nbsp;</td>
				<?php } ?>
			</tr>
		<?php } ?>
		<tr class="sectiontableentry1">
			<td colspan="4" align="left"><?php echo $this->cart->cartData['shippingName']; ?> </td>
			<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php echo "<span  style='color:gray'>".$this->cart->prices['shippingTax']."</span>"; ?> </td>
			<?php } ?>
			 <td></td>
			<td align="right"><?php echo $this->cart->prices['salesPriceShipping']; ?> </td>
		</tr>

		<tr class="sectiontableentry1">
			<td colspan="4" align="left"><?php echo $this->cart->cartData['paymentName']; ?> </td>
							 <?php if ( VmConfig::get('show_tax')) { ?>
			<td align="right"><?php //echo $this->prices['paymentTax']; ?> </td>
							<?php } ?>
			<td align="right"><?php //echo "<span  style='color:gray'>".$this->cart->prices['paymentDiscount']."</span>"; ?></td>
			<td align="right"><?php  echo $this->cart->prices['salesPricePayment']; ?> </td>
		</tr>
		<?php

		foreach($this->cart->cartData['taxRulesBill'] as $rule){ ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="4" align="right"><?php echo $rule['calc_name'] ?> </td>
				<td> </td>
				<td align="right"><?php echo $this->cart->prices[$rule['virtuemart_calc_id'].'Diff']; ?> </td>
				<td align="right"><?php    ?> </td>
				<td align="right"><?php echo $this->cart->prices[$rule['virtuemart_calc_id'].'Diff'];   ?> </td>
			</tr>
			<?php
			if($i) $i=1; else $i=0;
		}

		foreach($this->cart->cartData['dATaxRulesBill'] as $rule){ ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="4" align="right"><?php echo $rule['calc_name'] ?> </td>
				
                                     <?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php  ?> </td>
                                <?php } ?>
				<td align="right"><?php echo $this->cart->prices[$rule['virtuemart_calc_id'].'Diff'];   ?> </td>
				<td align="right"><?php echo $this->cart->prices[$rule['virtuemart_calc_id'].'Diff'];   ?> </td>
			</tr>
			<?php
			if($i) $i=1; else $i=0;
		} ?>

		  <tr>
			<td colspan="4">&nbsp;</td>
			<td colspan="<?php echo $colspan ?>"><hr /></td>
		  </tr>
		  <tr class="sectiontableentry2">
			<td colspan="4" align="right"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?>: </td>

                        <?php if ( VmConfig::get('show_tax')) { ?>
			<td align="right"> <?php echo "<span  style='color:gray'>".$this->cart->prices['billTaxAmount']."</span>" ?> </td>
                        <?php } ?>
			<td align="right"> <?php echo "<span  style='color:gray'>".$this->cart->prices['billDiscountAmount']."</span>" ?> </td>
			<td align="right"><strong><?php echo $this->cart->prices['billTotal'] ?></strong></td>
		  </tr>



	</table>
