<?php defined('_JEXEC') or die('Restricted access');
/**
*
* Layout for the shopping cart
*
* @package	VirtueMart
* @subpackage Cart
* @author Max Milbers
* @author Patrick Kohl
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

// jimport( 'joomla.application.component.view');
// $viewEscape = new JView();
// $viewEscape->setEscape('htmlspecialchars');
?>
<div class="billto-shipto">
	<div class="width50 floatleft">

		<span><span class="vmicon vm2-billto-icon"></span><?php echo JText::_('COM_VIRTUEMART_USER_FORM_BILLTO_LBL'); ?></span>

		<?php // Output Bill To Address ?>
		<div class="output-billto">
		<?php

		foreach($this->cart->BTaddress as $item){
			if(!empty($item['value'])){
				if($item['name']==='agreed'){
					$item['value'] =  ($item['value']===0) ? JText::_('COM_VIRTUEMART_USER_FORM_BILLTO_TOS_NO'):JText::_('COM_VIRTUEMART_USER_FORM_BILLTO_TOS_YES');
				}
				?><!-- span class="titles"><?php echo $item['title'] ?></span -->
					<span class="values vm2<?php echo '-'.$item['name'] ?>" ><?php echo $this->escape($item['value']) ?></span>
				<?php if ($item['name'] != 'first_name' and $item['name'] != 'middle_name' and $item['name'] != 'zip') { ?>
					<br class="clear" />
				<?php
				}
			}
		} ?>
		<div class="clear"></div>
		</div>

		<a class="details" href="index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=BT">
		<?php echo JText::_('COM_VIRTUEMART_USER_FORM_EDIT_BILLTO_LBL'); ?>
		</a>

		<input type="hidden" name="billto" value="<?php echo $this->cart->lists['billTo']; ?>"/>
	</div>

	<div class="width50 floatleft">

		<span><span class="vmicon vm2-shipto-icon"></span><?php echo JText::_('COM_VIRTUEMART_USER_FORM_SHIPTO_LBL'); ?></span>
		<?php // Output Bill To Address ?>
		<div class="output-shipto">
		<?php
		foreach($this->cart->STaddress as $item){
			if(!empty($item['value'])){ ?>
				<!-- <span class="titles"><?php echo $item['title'] ?></span> -->
				<?php
				if ($item['name'] == 'first_name' || $item['name'] == 'middle_name' || $item['name'] == 'zip') { ?>
					<span class="values<?php echo '-'.$item['name'] ?>" ><?php echo $this->escape($item['value']) ?></span>
				<?php } else { ?>
					<span class="values" ><?php echo $this->escape($item['value']) ?></span>
					<br class="clear" />
				<?php
				}
			}
		} ?>
		<div class="clear"></div>
		</div>
		<?php if(!isset($this->cart->lists['current_id'])) $this->cart->lists['current_id'] = 0; ?>
		<a class="details" href="index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=ST&shipto=0&cid[]=<?php echo $this->cart->lists['current_id']; ?>">
		<?php echo JText::_('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL'); ?>
		</a>

	</div>

<div class="clear"></div>
</div>

<fieldset>
		<table class="cart-summary" cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<th align="left"><?php echo JText::_('COM_VIRTUEMART_CART_NAME') ?></th>
				<th align="left" ><?php echo JText::_('COM_VIRTUEMART_CART_SKU') ?></th>
 				<th align="center" width="60px" ><?php echo JText::_('COM_VIRTUEMART_CART_PRICE') ?></th>
				<th align="right" width="140px" ><?php echo JText::_('COM_VIRTUEMART_CART_QUANTITY') ?> / <?php echo JText::_('COM_VIRTUEMART_CART_ACTION') ?></th>

                                        <?php if ( VmConfig::get('show_tax')) { ?>
                                <th align="right" width="60px"><?php  echo "<span  style='color:gray'>".JText::_('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT') ?></th>
				<?php } ?>
                                <th align="right" width="60px"><?php echo "<span  style='color:gray'>".JText::_('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT') ?></th>
				<th align="right" width="70px"><?php echo JText::_('COM_VIRTUEMART_CART_TOTAL') ?></th>
			</tr>
		<?php
		$i=1;
		foreach( $this->cart->products as $pkey =>$prow ) { ?>
			<tr valign="top" class="sectiontableentry<?php echo $i ?>">
				<td align="left" >
					<?php if ( $prow->virtuemart_media_id) {  ?>
						<span class="cart-images">
						 <?php
						 if(!empty($prow->image)) echo $prow->image->displayMediaThumb('',false);
						 ?>
						</span>
					<?php } ?>
					<?php echo JHTML::link(JURI::root(true) .'/'.$prow->url, $prow->product_name).$prow->customfields; ?>

				</td>
				<td align="left" ><?php echo $prow->product_sku ?></td>
				<td align="center" >
					<?php if ($prow->basePriceWithTax != $prow->salesPrice ) {
						echo '<span style="text-decoration:line-through">'.$prow->basePriceWithTax .'</span><br />' ;
					}
					echo $prow->salesPrice ;
					?>
				</td>
				<td align="right" ><form action="index.php" method="post" style="display: inline;">
				<input type="hidden" name="option" value="com_virtuemart" />
				<input type="text" title="<?php echo  JText::_('COM_VIRTUEMART_CART_UPDATE') ?>" class="inputbox" size="3" maxlength="4" name="quantity" value="<?php echo $prow->quantity ?>" />
				<input type="hidden" name="view" value="cart" />
				<input type="hidden" name="task" value="update" />
				<input type="hidden" name="cart_virtuemart_product_id" value="<?php echo $prow->cart_item_id  ?>" />
				<input type="submit" class="vmicon vm2-add_quantity_cart" name="update" title="<?php echo  JText::_('COM_VIRTUEMART_CART_UPDATE') ?>" align="middle" value=" "/>
			  </form>
					<a class="vmicon vm2-remove_from_cart" title="<?php echo JText::_('COM_VIRTUEMART_CART_DELETE') ?>" align="middle" href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart&task=delete&cart_virtuemart_product_id='.$prow->cart_item_id  ) ?>"> </a>
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
				<td align="right"><?php echo $this->cart->prices[$rule['virtuemart_calc_id'].'Diff'];  ?> </td>
				<td align="right"><?php echo $this->cart->prices[$rule['virtuemart_calc_id'].'Diff'];   ?> </td>
			</tr>
			<?php
			if($i) $i=1; else $i=0;
		} ?>
		<?php
		if (VmConfig::get('coupons_enable')) {
		?>
			<tr class="sectiontableentry2">
				<td colspan="2" align="left"><?php if(!empty($this->layoutName) && $this->layoutName=='default') echo JHTML::_('link', JRoute::_('index.php?view=cart&task=edit_coupon'), JText::_('COM_VIRTUEMART_CART_EDIT_COUPON')); ?> </td>
				<?php if (!empty($this->cart->cartData['couponCode'])) { ?>
					<td colspan="2" align="left"><?php
						echo $this->cart->cartData['couponCode'] ;
                                                echo $this->cart->cartData['couponDescr'] ? (' (' . $this->cart->cartData['couponDescr'] . ')' ): '';
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
                    <?php if (!$this->cart->automaticSelectedShipping) { ?>
				<td colspan="2" align="left"><?php if(!empty($this->layoutName) && $this->layoutName=='default' && !$this->cart->automaticSelectedShipping  ) echo JHTML::_('link', JRoute::_('index.php?view=cart&task=edit_shipping'), JText::_('COM_VIRTUEMART_CART_EDIT_SHIPPING'),'class="highlight"'); else JText::_('COM_VIRTUEMART_CART_SHIPPING'); ?> </td>
		<?php	/*	<td colspan="2" align="right"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING'); ?> </td> */?>
				<td colspan="2" align="left"><?php echo $this->cart->cartData['shippingName']; ?> </td>
                                <?php } else { ?>
                                    <td colspan="4" align="left"><?php echo $this->cart->cartData['shippingName']; ?> </td>
                                 <?php } ?>

                                     <?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php echo "<span  style='color:gray'>".$this->cart->prices['shippingTax']."</span>"; ?> </td>
                                <?php } ?>
				<td></td>
				<td align="right"><?php echo $this->cart->prices['salesPriceShipping']; ?> </td>
		</tr>

		<tr class="sectiontableentry1">
                          <?php if (!$this->cart->automaticSelectedPayment) { ?>
				<td colspan="2" align="left"><?php if(!empty($this->layoutName) && $this->layoutName=='default') echo JHTML::_('link', JRoute::_('index.php?view=cart&task=editpayment'), JText::_('COM_VIRTUEMART_CART_EDIT_PAYMENT'),'class="highlight"'); else JText::_('COM_VIRTUEMART_CART_PAYMENT'); ?> </td>
		<?php	/*	<td colspan="2" align="left"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PAYMENT_LBL') ?> </td> */?>
				<td colspan="2" align="left"><?php echo $this->cart->cartData['paymentName']; ?> </td>
                         <?php } else { ?>
                                    <td colspan="4" align="left"><?php echo $this->cart->cartData['paymentName']; ?> </td>
                                 <?php } ?>
                                     <?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php //echo $this->cart->prices['paymentTax']; ?> </td>
                                <?php } ?>
				<td align="right"><?php echo "<span  style='color:gray'>".$this->cart->prices['paymentDiscount']."</span>"; ?></td>
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
				<td> </td>
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
</fieldset>
