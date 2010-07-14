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
		<?php echo JText::_('VM_CART_TITLE'); ?>
	</legend>
<?php 

		// Added for the zone shipping module
		//$vars["zone_qty"] = 0;
		$weight_total = 0;
		$weight_subtotal = 0;

		//of course, some may argue that the $product_rows should be generated in the view.html.php, but
		//
		$product_rows = array();
	
		for ($i=0; $i < $this->cart['idx']; $i++) {
			$product = $this->products[$i];
			// Added for the zone shipping module
			//$vars["zone_qty"] += $cart[$i]["quantity"];
	
			if ($i % 2) $product_rows[$i]['row_color'] = "sectiontableentry2";
			else $product_rows[$i]['row_color'] = "sectiontableentry1";
	
			/* Create product URL */
			$url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$product->product_id.'&category_id='.$this->cart[$i]['category_id']);
			
			/** @todo Add variants */
			$product_rows[$i]['product_name'] = JHTML::link($url, $product->product_name).'';
			
			/* Add the variants */
			$product_rows[$i]['product_variants'] = '';
			foreach ($this->cart[$i]['variants'] as $vname => $vvalue) {
				$product_rows[$i]['product_variants'] .= '<br />'.$vname.': '.$vvalue;
			}
			
			/* Add the custom variants */
			$product_rows[$i]['product_customvariants'] = '';
			foreach ($this->cart[$i]['customvariants'] as $cname => $cvalue) {
				$product_rows[$i]['product_customvariants'] .= '<br />'.$cname.': '.$cvalue;
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
			//$weight_subtotal = vmShippingMethod::get_weight($cart[$i]["product_id"]) * $cart[$i]['quantity'];
			//$weight_total += $weight_subtotal;
	
			/* Product PRICE */
			/** @todo Format price */
			$product_rows[$i]['product_price'] = $this->prices[$i]['salesPrice'];

			/** @todo Format price */
			$product_rows[$i]['subtotal'] = $this->prices[$i]['priceWithoutTax'] * $this->cart[$i]['quantity'];
			$product_rows[$i]['subtotal_tax_amount'] = $this->prices[$i]['taxAmount'] * $this->cart[$i]['quantity'];
			$product_rows[$i]['subtotal_discount'] = $this->prices[$i]['discountAmount'] * $this->cart[$i]['quantity'];
			$product_rows[$i]['subtotal_with_tax'] = $this->prices[$i]['salesPrice'] * $this->cart[$i]['quantity'];
			
			// UPDATE CART / DELETE FROM CART
			if($this->layoutName=='cart'){
			$product_rows[$i]['update_form'] = '<form action="index.php" method="post" style="display: inline;">
				<input type="hidden" name="option" value="com_virtuemart" />
				<input type="text" title="'. JText::_('VM_CART_UPDATE') .'" class="inputbox" size="3" maxlength="4" name="quantity" value="'.$this->cart[$i]["quantity"].'" />
				<input type="hidden" name="view" value="cart" />
				<input type="hidden" name="task" value="update" />
				<input type="hidden" name="cart_id" value="'.$i.'" />
				<input type="hidden" name="product_id" value="'.$product->product_id.'" />
				<input type="image" name="update" title="'. JText::_('VM_CART_UPDATE') .'" src="'.JURI::root().'/components/com_virtuemart/assets/images/update_quantity_cart.png" alt="'. JText::_('VM_UPDATE') .'" align="middle" />
			  </form>';
			$product_rows[$i]['delete_form'] = '<form action="index.php" method="post" name="delete" style="display: inline;">
				<input type="hidden" name="option" value="com_virtuemart" />
				<input type="hidden" name="view" value="cart" />
				<input type="hidden" name="task" value="delete" />
				<input type="hidden" name="cart_id" value="'.$i.'" />
				<input type="hidden" name="product_id" value="'.$product->product_id.'" />
				<input type="image" name="delete" title="'. JText::_('VM_CART_DELETE') .'" src="'.JURI::root().'/components/com_virtuemart/assets/images/remove_from_cart.png" alt="'. JText::_('VM_CART_DELETE') .'" align="middle" />
			  </form>';
			} else {
				$product_rows[$i]['update_form'] = $this->cart[$i]["quantity"];
				$product_rows[$i]['delete_form'] ='';
			}
		} // End of for loop through the Cart


		?>
		<table width="100%" cellspacing="2" cellpadding="4" border="0">
			<tr align="left" class="sectiontableheader">
				<th><?php echo JText::_('VM_CART_NAME') ?></th>
				<th align="left" ><?php echo JText::_('VM_CART_SKU') ?></th>
				<th align="right" width="60px" ><?php echo JText::_('VM_CART_PRICE') ?></th>
				<th align="right" width="140px" ><?php echo JText::_('VM_CART_QUANTITY') ?> / <?php echo JText::_('VM_CART_ACTION') ?></th>
				<th align="right" width="70px"><?php echo JText::_('VM_CART_SUBTOTAL') ?></th>
				<th align="right" width="60px"><?php echo JText::_('VM_CART_SUBTOTAL_TAX_AMOUNT') ?></th>
				<th align="right" width="60px"><?php echo JText::_('VM_CART_SUBTOTAL_DISCOUNT_AMOUNT') ?></th>
				<th align="right" width="70px"><?php echo JText::_('VM_CART_TOTAL') ?></th>
			</tr>
		<?php foreach( $product_rows as $product ) { ?>
			<tr valign="top" class="<?php echo $product['row_color'] ?>">
				<td align="left" ><?php echo $product['product_name'].$product['product_variants'].$product['product_customvariants'].$product['product_attributes']; ?></td>
				<td align="left" ><?php echo $product['product_sku'] ?></td>
				<td align="right" ><?php echo $product['product_price'] ?></td>
				<td align="right" ><?php echo $product['update_form'] ?>
					<?php echo $product['delete_form'] ?>
				</td>
				<td colspan="1" align="right"><?php echo $product['subtotal'] ?></td>
				<td align="right"><?php echo $product['subtotal_tax_amount'] ?></td>
				<td align="right"><?php echo $product['subtotal_discount'] ?></td>			
				<td colspan="1" align="right"><?php echo $product['subtotal_with_tax'] ?></td>
			</tr>
		<?php } ?>
		<!--Begin of SubTotal, Tax, Shipping, Coupon Discount and Total listing -->
		<tr>
			<td colspan="4">&nbsp;</td>
			<td colspan="4"><hr /></td>
		</tr>
		  <tr class="sectiontableentry1">
			<td colspan="4" align="right"><?php echo JText::_('VM_ORDER_PRINT_PRODUCT_PRICES_TOTAL'); ?></td> 
			<td align="right"><?php echo $this->prices['priceWithoutTax'] ?></td>
			<td align="right"><?php echo $this->prices['taxAmount'] ?></td>
			<td align="right"><?php echo $this->prices['discountAmount'] ?></td>
			<td align="right"><?php echo $this->prices['salesPrice'] ?></td>
		  </tr>

		<?php 
		foreach($this->prices['dBTaxRulesBill'] as $rule){ ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="4" align="right"><?php echo $rule['calc_name'] ?> </td>
				<td> </td>
				<td align="right"><?php  ?> </td>
				<td align="right"><?php echo -$this->prices[$rule['calc_id'].'Diff'];  ?> </td>
				<td align="right"><?php echo $this->prices[$rule['calc_id'].'Diff'];   ?> </td>
			</tr>
			<?php 
			if($i) $i=1; else $i=0;
		}
		  
		if($this->prices['coupons']){ 
			if($this->layoutName=='cart') $couponlink = JRoute::_('index.php?view=cart&task=editcoupon'); else $couponlink= ''?> 
			<tr class="sectiontableentry2">
		<?php	/*	<td align="left"><?php echo JText::_('VM_COUPON_DISCOUNT'); ?> </td>  */  ?> 
				<td colspan="2" align="left"><?php echo JHTML::_('link', $couponlink, JText::_('VM_CART_EDIT_COUPON')); ?> </td>
				<td colspan="3" align="left"><?php echo $this->prices['couponName']; ?> </td>
				<td align="right"><?php echo $this->prices['couponTax']; ?> </td>
				<td align="right"><?php echo $this->prices['couponValue']; ?> </td>	
				<td align="right"><?php echo $this->prices['salesPriceCoupon']; ?> </td>
			</tr>
		<?php }  
		if($this->layoutName=='cart') $shippinglink = JRoute::_('index.php?view=cart&task=editshipping'); else $shippinglink= '' ?>
		<tr class="sectiontableentry1">
				<td colspan="2" align="left"><?php echo JHTML::_('link', $shippinglink, JText::_('VM_CART_EDIT_SHIPPING')); ?> </td>
		<?php	/*	<td colspan="2" align="right"><?php echo JText::_('VM_ORDER_PRINT_SHIPPING'); ?> </td> */?>
				<td colspan="2" align="left"><?php echo $this->prices['shippingName']; ?> </td>
				<td align="right"><?php echo $this->prices['shippingValue']; ?> </td>
				<td align="right"><?php echo $this->prices['shippingTax']; ?> </td>	
				<td></td>
				<td align="right"><?php echo $this->prices['salesPriceShipping']; ?> </td>
				
		</tr>
		<?php 
		if($this->layoutName=='cart') $paymentlink = JRoute::_('index.php?view=cart&task=editpayment'); else $paymentlink= '' ?>
		<tr class="sectiontableentry1">
				<td colspan="2" align="left"><?php echo JHTML::_('link', $paymentlink, JText::_('VM_CART_EDIT_PAYMENT'));?> </td>
			<?php	/*	<td colspan="2" align="left"><?php echo JText::_('VM_ORDER_PRINT_PAYMENT_LBL') ?> </td> */?>
				<td colspan="2" align="left"><?php echo $this->prices['paymentName']; ?> </td>
				<td align="right"><?php echo $this->prices['paymentValue']; ?> </td>
				<td align="right"><?php echo $this->prices['paymentTax']; ?> </td>	
				<td align="right"><?php echo $this->prices['paymentDiscount']; ?></td>
				<td align="right"><?php  echo $this->prices['salesPricePayment']; ?> </td>				  		
			</tr>
		<?php 
		
		foreach($this->prices['taxRulesBill'] as $rule){ ?>
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
		
		foreach($this->prices['dATaxRulesBill'] as $rule){ ?>
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
			<td colspan="4" align="right"><?php echo JText::_('VM_ORDER_PRINT_TOTAL') ?>: </td>
			<td align="right"> <?php echo $this->prices['billSub'] ?> </td>
			<td align="right"> <?php echo $this->prices['billTaxAmount'] ?> </td>
			<td align="right"> <?php echo $this->prices['billDiscountAmount'] ?> </td>
			<td align="right"><strong><?php echo $this->prices['billTotal'] ?></strong></td>
		  </tr>
		<?php if ( VmConfig::get('show_tax')) { ?>
		  <tr class="sectiontableentry1">
				<td colspan="4" align="right" valign="top"><?php echo JText::_('VM_ORDER_PRINT_TOTAL_TAX') ?>: </td> 
				<td colspan="4" align="right"><?php echo $this->prices['taxAmount'] ?></td>
		  </tr>
		<?php } ?>
		  <tr>
			<td colspan="10"><hr /></td>
		  </tr>

	</table>
</fieldset>
