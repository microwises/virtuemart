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
?>
<fieldset>
	<legend>
		<?php echo JText::_('COM_VIRTUEMART_CART_TITLE'); ?>
	</legend>
<?php

		// Added for the zone shipping module
		//$vars["zone_qty"] = 0;
		$weight_total = 0;
		$weight_subtotal = 0;

		//of course, some may argue that the $product_rows should be generated in the view.html.php, but
		//
		$product_rows = array();

		$i=0;
		foreach ($this->cart->products as $priceKey=>$product){
			// Added for the zone shipping module
			//$vars["zone_qty"] += $product["quantity"];

			if ($i % 2) $product_rows[$i]['row_color'] = "sectiontableentry2";
			else $product_rows[$i]['row_color'] = "sectiontableentry1";
			$product->category_id = $this->cart->getCardCategoryId($product->product_id);
			/* Create product URL */
			$url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$product->product_id.'&category_id='.$product->category_id);

			/** @todo Add variants */
			$product_rows[$i]['product_name'] = JHTML::link($url, $product->product_name);

			/* Add the variants */
			$variant = '';
			$variantmod = '0';
			if(!empty($product->variant)){
				$product_rows[$i]['product_variant'] = '';

				foreach ($product->variant as $vname => $vvalue) {
					$product_rows[$i]['product_variant'] .= '<br />'.$vname.': '.$vvalue;
					$variant .=$vvalue;
				}
			} else {
				$product_rows[$i]['product_variant']='';
			}


			/* Add the custom variants */
			$cvariant = '';
			if(!empty($product->customvariant)){
				$product_rows[$i]['product_customvariant'] = '';

				foreach ($product->customvariant as $cname => $cvalue) {
					$product_rows[$i]['product_customvariant'] .= '<br />'.$cname.': '.$cvalue;
					$cvariant .= $cvalue;
				}
			} else {
				$product_rows[$i]['product_customvariant']='';
			}

			// Display attribute values if this an item
			$product_rows[$i]['product_attributes'] = '';
			if ($product->product_parent_id > 0) {
				foreach ($product->attributes as $attribute) {
					$product_rows[$i]['product_attributes'] .= "<br />".$attribute->attribute_name."&nbsp;";
					$product_rows[$i]['product_attributes'] .= "(" . $attribute->attribute_value.")";
				}
			}
			$product_rows[$i]['product_sku'] = $product->product_sku;

			/** @todo WEIGHT CALCULATION */
			//$weight_subtotal = vmShippingMethod::get_weight($product["product_id"]) * $product->quantity'];
			//$weight_total += $weight_subtotal;

			/* Product PRICE */
			$product_rows[$i]['prices'] = $this->prices[$priceKey]['salesPrice'];

			$product_rows[$i]['subtotal'] = $this->prices[$priceKey]['subtotal'];
			$product_rows[$i]['subtotal_tax_amount'] = $this->prices[$priceKey]['subtotal_tax_amount'];
			$product_rows[$i]['subtotal_discount'] = $this->prices[$priceKey]['subtotal_discount'];
			$product_rows[$i]['subtotal_with_tax'] = $this->prices[$priceKey]['subtotal_with_tax'];

			// UPDATE CART / DELETE FROM CART
			if($this->layoutName=='default'){
			$product_rows[$i]['update_form'] = '<form action="index.php" method="post" style="display: inline;">
				<input type="hidden" name="option" value="com_virtuemart" />
				<input type="text" title="'. JText::_('COM_VIRTUEMART_CART_UPDATE') .'" class="inputbox" size="3" maxlength="4" name="quantity" value="'.$product->quantity.'" />
				<input type="hidden" name="view" value="cart" />
				<input type="hidden" name="task" value="update" />
				<input type="hidden" name="cart_product_id" value="'.$priceKey.'" />
				<input type="image" name="update" title="'. JText::_('COM_VIRTUEMART_CART_UPDATE') .'" src="'.JURI::root().'components/com_virtuemart/assets/images/vmgeneral/update_quantity_cart.png" alt="'. JText::_('COM_VIRTUEMART_UPDATE') .'" align="middle" />
			  </form>';
			$product_rows[$i]['delete_form'] = '<form action="index.php" method="post" name="delete" style="display: inline;">
				<input type="hidden" name="option" value="com_virtuemart" />
				<input type="hidden" name="view" value="cart" />
				<input type="hidden" name="task" value="delete" />
				<input type="hidden" name="cart_product_id" value="'.$priceKey.'" />
				<input type="image" name="delete" title="'. JText::_('COM_VIRTUEMART_CART_DELETE') .'" src="'.JURI::root().'components/com_virtuemart/assets/images/vmgeneral/remove_from_cart.png" alt="'. JText::_('COM_VIRTUEMART_CART_DELETE') .'" align="middle" />
			  </form>';
			} else {
				$product_rows[$i]['update_form'] = $product->quantity;
				$product_rows[$i]['delete_form'] ='';
			}
			$i++;
		} // End of for loop through the Cart


		?>
		<table width="100%" cellspacing="2" cellpadding="0" border="0">
			<tr align="left" class="sectiontableheader">
				<th><?php echo JText::_('COM_VIRTUEMART_CART_NAME') ?></th>
				<th align="left" ><?php echo JText::_('COM_VIRTUEMART_CART_SKU') ?></th>
				<th align="center" width="60px" ><?php echo JText::_('COM_VIRTUEMART_CART_PRICE') ?></th>
				<th align="right" width="140px" ><?php echo JText::_('COM_VIRTUEMART_CART_QUANTITY') ?> / <?php echo JText::_('COM_VIRTUEMART_CART_ACTION') ?></th>
				<th align="right" width="70px"><?php echo JText::_('COM_VIRTUEMART_CART_SUBTOTAL') ?></th>
				<th align="right" width="60px"><?php echo JText::_('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT') ?></th>
				<th align="right" width="60px"><?php echo JText::_('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT') ?></th>
				<th align="right" width="70px"><?php echo JText::_('COM_VIRTUEMART_CART_TOTAL') ?></th>
			</tr>
		<?php foreach( $product_rows as $prow ) { ?>
			<tr valign="top" class="<?php echo $prow['row_color'] ?>">
				<td align="left" ><?php echo $prow['product_name'].$prow['product_variant'].$prow['product_customvariant'].$prow['product_attributes']; ?></td>
				<td align="left" ><?php echo $prow['product_sku'] ?></td>
				<td align="center" ><?php echo $prow['prices'] ?></td>
				<td align="right" ><?php echo $prow['update_form'] ?>
					<?php echo $prow['delete_form'] ?>
				</td>
				<td colspan="1" align="right"><?php echo $prow['subtotal'] ?></td>
				<td align="right"><?php echo $prow['subtotal_tax_amount'] ?></td>
				<td align="right"><?php echo $prow['subtotal_discount'] ?></td>
				<td colspan="1" align="right"><?php echo $prow['subtotal_with_tax'] ?></td>
			</tr>
		<?php } ?>
		<!--Begin of SubTotal, Tax, Shipping, Coupon Discount and Total listing -->
		<tr>
			<td colspan="4">&nbsp;</td>
			<td colspan="4"><hr /></td>
		</tr>
		  <tr class="sectiontableentry1">
			<td colspan="4" align="right"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_PRICES_TOTAL'); ?></td>
			<td align="right"><?php echo $this->prices['priceWithoutTax'] ?></td>
			<td align="right"><?php echo $this->prices['taxAmount'] ?></td>
			<td align="right"><?php echo $this->prices['discountAmount'] ?></td>
			<td align="right"><?php echo $this->prices['salesPrice'] ?></td>
		  </tr>

		<?php
		foreach($this->cartData['dBTaxRulesBill'] as $rule){ ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="4" align="right"><?php echo $rule['calc_name'] ?> </td>
				<td> </td>
				<td align="right"> </td>
				<td align="right"><?php echo -$this->prices[$rule['calc_id'].'Diff'];  ?> </td>
				<td align="right"><?php echo $this->prices[$rule['calc_id'].'Diff'];   ?> </td>
			</tr>
			<?php
			if($i) $i=1; else $i=0;
		} ?>
		<?php
		if (VmConfig::get('coupons_enable')) {
		?>
			<tr class="sectiontableentry2">
				<td colspan="2" align="left"><?php if($this->layoutName=='default') echo JHTML::_('link', JRoute::_('index.php?view=cart&task=editcoupon'), JText::_('COM_VIRTUEMART_CART_EDIT_COUPON')); ?> </td>
				<?php if (!empty($this->cartData['couponCode'])) { ?>
					<td colspan="2" align="left"><?php
						echo $this->cartData['couponCode'] . ' (' . $this->cartData['couponDescr'] . ')';
					?> </td>
					<td align="right"><?php echo $this->prices['couponValue']; ?> </td>
					<td align="right"><?php echo $this->prices['couponTax']; ?> </td>
					<td align="right">&nbsp;</td>
					<td align="right"><?php echo $this->prices['salesPriceCoupon']; ?> </td>
				<?php } else { ?>
					<td colspan="6" align="left">&nbsp;</td>
				<?php } ?>
			</tr>
		<?php } ?>
		<tr class="sectiontableentry1">
				<td colspan="2" align="left"><?php if($this->layoutName=='default') echo JHTML::_('link', JRoute::_('index.php?view=cart&task=editshipping'), JText::_('COM_VIRTUEMART_CART_EDIT_SHIPPING')); else JText::_('COM_VIRTUEMART_CART_SHIPPING'); ?> </td>
		<?php	/*	<td colspan="2" align="right"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING'); ?> </td> */?>
				<td colspan="2" align="left"><?php echo $this->cartData['shippingName']; ?> </td>
				<td align="right"><?php echo $this->prices['shippingValue']; ?> </td>
				<td align="right"><?php echo $this->prices['shippingTax']; ?> </td>
				<td></td>
				<td align="right"><?php echo $this->prices['salesPriceShipping']; ?> </td>
		</tr>

		<tr class="sectiontableentry1">
				<td colspan="2" align="left"><?php if($this->layoutName=='default') echo JHTML::_('link', JRoute::_('index.php?view=cart&task=editpayment'), JText::_('COM_VIRTUEMART_CART_EDIT_PAYMENT')); else JText::_('COM_VIRTUEMART_CART_PAYMENT'); ?> </td>
		<?php	/*	<td colspan="2" align="left"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PAYMENT_LBL') ?> </td> */?>
				<td colspan="2" align="left"><?php echo $this->cartData['paymentName']; ?> </td>
				<td align="right"><?php echo $this->prices['paymentValue']; ?> </td>
				<td align="right"><?php echo $this->prices['paymentTax']; ?> </td>
				<td align="right"><?php echo $this->prices['paymentDiscount']; ?></td>
				<td align="right"><?php  echo $this->prices['salesPricePayment']; ?> </td>
			</tr>
		<?php

		foreach($this->cartData['taxRulesBill'] as $rule){ ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="4" align="right"><?php echo $rule['calc_name'] ?> </td>
				<td> </td>
				<td align="right"><?php echo $this->prices[$rule['calc_id'].'Diff']; ?> </td>
				<td align="right"><?php    ?> </td>
				<td align="right"><?php echo $this->prices[$rule['calc_id'].'Diff'];   ?> </td>
			</tr>
			<?php
			if($i) $i=1; else $i=0;
		}

		foreach($this->cartData['dATaxRulesBill'] as $rule){ ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="4" align="right"><?php echo $rule['calc_name'] ?> </td>
				<td> </td>
				<td align="right"><?php  ?> </td>
				<td align="right"><?php echo $this->prices[$rule['calc_id'].'Diff'];   ?> </td>
				<td align="right"><?php echo $this->prices[$rule['calc_id'].'Diff'];   ?> </td>
			</tr>
			<?php
			if($i) $i=1; else $i=0;
		} ?>

		  <tr>
			<td colspan="4">&nbsp;</td>
			<td colspan="4"><hr /></td>
		  </tr>
		  <tr class="sectiontableentry2">
			<td colspan="4" align="right"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?>: </td>
			<td align="right"> <?php echo $this->prices['billSub'] ?> </td>
			<td align="right"> <?php echo $this->prices['billTaxAmount'] ?> </td>
			<td align="right"> <?php echo $this->prices['billDiscountAmount'] ?> </td>
			<td align="right"><strong><?php echo $this->prices['billTotal'] ?></strong></td>
		  </tr>
		<?php if ( VmConfig::get('show_tax')) { ?>
		  <tr class="sectiontableentry1">
				<td colspan="4" align="right" valign="top"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL_TAX') ?>: </td>
				<td colspan="4" align="right"><?php echo $this->prices['taxAmount'] ?></td>
		  </tr>
		<?php } ?>
		  <tr>
			<td colspan="10"><hr /></td>
		  </tr>

	</table>
</fieldset>
