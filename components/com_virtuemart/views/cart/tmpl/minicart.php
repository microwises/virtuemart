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
* @version $Id: cart.php 2551 2010-09-30 18:52:40Z milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

?>
	<div>
		<?php echo JText::_('VM_CART_TITLE'); ?>
	</div>
<?php

		// Added for the zone shipping module
		//$vars["zone_qty"] = 0;
		$weight_total = 0;
		$weight_subtotal = 0;

		//of course, some may argue that the $product_rows should be generated in the view.html.php, but
		//
		$product_rows = array();

		$i=0;
		$totalProduct = 0 ;
		foreach ($this->cart->products as $k=>$product){

			// Added for the zone shipping module
			//$vars["zone_qty"] += $product["quantity"];

			if ($i % 2) $product_rows[$i]['row_color'] = "sectiontableentry2";
			else $product_rows[$i]['row_color'] = "sectiontableentry1";

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
				$variantmod = $this->calculator->parseModifier($product->variant);
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
			$priceKey = $product->product_id.$variantmod;

			$product_rows[$i]['prices'] = $this->prices[$priceKey]['salesPrice'];

			/** @todo Format price */
//			$product_rows[$i]['subtotal'] = $this->prices[$i]['priceWithoutTax'] * $product->quantity;
//			$product_rows[$i]['subtotal_tax_amount'] = $this->prices[$i]['taxAmount'] * $product->quantity;
//			$product_rows[$i]['subtotal_discount'] = $this->prices[$i]['discountAmount'] * $product->quantity;
//			$product_rows[$i]['subtotal_with_tax'] = $this->prices[$i]['salesPrice'] * $product->quantity;

			$product_rows[$i]['subtotal'] = $this->prices[$priceKey]['subtotal'];
			$product_rows[$i]['subtotal_tax_amount'] = $this->prices[$priceKey]['subtotal_tax_amount'];
			$product_rows[$i]['subtotal_discount'] = $this->prices[$priceKey]['subtotal_discount'];
			$product_rows[$i]['subtotal_with_tax'] = $this->prices[$priceKey]['subtotal_with_tax'];

			// UPDATE CART / DELETE FROM CART
				$product_rows[$i]['update_form'] = $product->quantity;
				$totalProduct += $product->quantity ;

			$i++;
		} // End of for loop through the Cart


		?>
		<table width="100%" cellspacing="2" cellpadding="4" border="0">
		<?php foreach( $product_rows as $prow ) { ?>
			<tr valign="top" class="<?php echo $prow['row_color'] ?>">
				<td align="left" ><?php echo $prow['product_name'].$prow['product_variant'].$prow['product_customvariant'].$prow['product_attributes']; ?></td>
				<td align="left" ><?php echo $prow['product_sku'] ?></td>
				<td align="right" ><?php echo $prow['update_form'] ?>
				</td>
				<td colspan="1" align="right"><?php echo $prow['subtotal_with_tax'] ?></td>
			</tr>
		<?php } ?>
		<!--Begin of SubTotal, Tax, Shipping, Coupon Discount and Total listing -->
		  <tr>
			<td colspan="10"><hr /></td>
		  </tr>
		  <tr class="sectiontableentry2">
			<td align="right" valign="top"><?php echo JText::_('VM_ORDER_PRINT_PRODUCT_PRICES_TOTAL') ?> </td>
			<td align="right"><?php echo $totalProduct ?></td>
			<td align="right"><?php echo JText::_('VM_ORDER_PRINT_TOTAL') ?> </td>
			<td align="right"><strong><?php echo $this->prices['billTotal'] ?></strong></td>
		  </tr>
		<?php if ( VmConfig::get('show_tax')) { ?>
		  <tr class="sectiontableentry1">

				<td align="right" valign="top"><?php echo JText::_('VM_ORDER_PRINT_TOTAL_TAX') ?>: </td>
				<td align="right"><?php echo $this->prices['taxAmount'] ?></td>
		  </tr>
		<?php } ?>


	</table>
