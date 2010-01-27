<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage 
* @author RolandD
* @todo Price update calculations
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
defined('_JEXEC') or die('Restricted access'); ?>
<table class="adminform">
	<tr>
		<td valign="top">
			<table width="100%" border="0">
				<tr class="row0">
					<td  width="21%" ><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('VM_PRODUCT_FORM_PUBLISH') ?>:</div>
					</td>
					<td width="79%">
						<?php
							$checked = '';//todo roland please checkt this
							if (strtoupper($this->product->published) == "1") $checked = 'checked="checked"';
							echo '<input type="checkbox" name="published" value="1" '.$checked.' />';
						?> 
					</td>
				</tr>
				<tr class="row1">
					<td width="21%" >
						<div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_SKU') ?>:</div>
					</td>
					<td width="79%" height="2">
						<input type="text" class="inputbox" name="product_sku" value="<?php echo $this->product->product_sku; ?>" size="32" maxlength="64" />
					</td>
				</tr>
				<tr class="row0">
					<td width="21%" height="18"><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('VM_PRODUCT_FORM_NAME') ?>:</div>
					</td>
					<td width="79%" height="18" >
						<input type="text" class="inputbox"  name="product_name" value="<?php echo $this->product->product_name; ?>" size="32" maxlength="255" />
					</td>
				</tr>
				<tr class="row1">
					<td width="21%"><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('VM_PRODUCT_FORM_URL') ?>:</div>
					</td>
					<td width="79%">
						<input type="text" class="inputbox"  name="product_url" value="<?php echo $this->product->product_url; ?>" size="32" maxlength="255" />
					</td>
				</tr>
				<tr class="row0">
					<td width="21%"><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('VM_PRODUCT_FORM_VENDOR') ?>:</div>
					</td>
				<td width="79%">
					<?php echo $this->lists['vendors'];?>
				</td>
			</tr>
			<tr class="row1">
				<td width="21%" ><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('VM_PRODUCT_FORM_MANUFACTURER') ?>:</div>
				</td>
				<td width="79%">
					<?php echo $this->lists['manufacturers'];?>
				</td>
			</tr>
			<?php if (!$this->product->product_parent_id) { ?>
			<tr class="row0">
				<td width="29%" valign="top">
					<div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('VM_CATEGORIES') ?>:</div>
				</td>
				<td width="71%" >
					<select class="inputbox" id="product_categories" name="product_categories[]" multiple="multiple" size="10">
						<?php echo $this->category_tree; ?>
					</select>
				</td>
			</tr>
			<tr class="row1">
				<td width="21%" ><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('VM_PRODUCT_DETAILS_PAGE') ?>:</div>
				</td>
				<td width="79%">
					<?php echo $this->lists['detailspage'];?>
				</td>
			</tr>
			<?php } ?>
		</table>
	</td>
	<td>
		<table class="adminform">
			<tr class="row1">
				<td width="29%">
					<div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_PURCHASE_PRICE') ?>:</div>
				</td>
				<td width="31%">
					<input type="text" class="inputbox" name="purchase_price" size="10" value="<?php echo $this->product->purchase_price; ?>" />
				</td>
				<td width="40%">
					<?php echo $this->currencies; ?>
				</td>
			</tr>
			<tr class="row0">
				<td width="29%" >
					<div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_PRICE_NET') ?>:</div>
				</td>
				<td width="71%" >
					<table border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td>
								<input type="text" value="<?php echo $this->product->product_price; ?>" class="inputbox" name="product_price" onkeyup="updateGross();" size="10" maxlength="10" />
								<input type="hidden" name="product_price_id" value="<?php echo $this->product->product_price_id; ?>" />
								<input type="hidden" name="price_quantity_start" value="<?php echo $this->product->price_quantity_start; ?>" />
								<input type="hidden" name="price_quantity_end" value="<?php echo $this->product->price_quantity_end; ?>" />
							</td>

							<td>
								<input type="hidden" name="shopper_group_id" value="<?php echo $this->product->shopper_group_id ?>" />
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="row1">
				<td width="29%">
					<div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_PRICE_GROSS') ?>:</div>
				</td>
				<td width="71%">
					<input type="text" class="inputbox" onkeyup="updateNet();" name="product_price_incl_tax" size="10" />
				</td>
			</tr>
			<tr class="row0">
				<td width="29%" ><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('VM_RATE_FORM_VAT_ID') ?>:</div>
				</td>
				<td width="71%" >
					<?php echo $this->lists['taxrates']; ?>
				</td>
			</tr>
			<tr class="row1">
				<td width="21%" ><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('VM_PRODUCT_FORM_DISCOUNT_TYPE') ?>:</div>
				</td>
				<td width="79%">
					<?php echo $this->lists['discounts']; ?>
				</td>
			</tr>
			<tr class="row0">
				<td width="21%" >
					<div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('VM_PRODUCT_FORM_DISCOUNTED_PRICE') ?>:</div>
				</td>
				<td width="79%" >
					<input type="text" size="10" name="discounted_price_override" onchange="try { document.adminForm.product_discount_id[document.adminForm.product_discount_id.length-1].selected=true; } catch( e ) {}" />&nbsp;&nbsp;
					<?php 
					// echo vmToolTip( JText::_('VM_PRODUCT_FORM_DISCOUNTED_PRICE_TIP') ) 
					?>
				</td>
			</tr>
			<tr>
				<td width="29%" valign="top">
					<div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_ORDER_PRINT_INTNOTES'); ?>:</div>
				</td>
				<td width="71%" valign="top">
					<textarea class="inputbox" name="intnotes" id="intnotes" cols="35" rows="6" ><?php echo $this->product->intnotes; ?></textarea>
				</td>
			</tr>
			<tr class="row1">
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr class="row1">
				<td width="29%" valign="top">
					<div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('VM_PRODUCT_FORM_S_DESC') ?>:</div>
				</td>
				<td width="71%"  valign="top">
					<textarea class="inputbox" name="product_s_desc" id="short_desc" cols="35" rows="6" ><?php echo $this->product->product_s_desc; ?></textarea>
				</td>
			</tr>
		</table>
	</td>
	</tr>
</table>
<table class="adminform">
	<tr class="row1">
		<td valign="top" width="20%"><div style="font-weight:bold;">
			<?php echo JText::_('VM_PRODUCT_FORM_DESCRIPTION') ?>:</div>
		</td>
		<td width="60%">
			<?php
			echo $this->editor->display('product_desc',  $this->product->product_desc, '100%;', '550', '75', '20', array('pagebreak', 'readmore') ) ;
			?>
		</td>
		<td valign="top">
			<fieldset>
				<legend><?php echo JText::_('VM_META_INFORMATION') ?></legend>		
				<table valign="top">
					<tr>
						<td vlaign="top"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_META_DESC'); ?>: </div></td>
						<td valign="top">
							<textarea class="inputbox" name="metadesc" id="meta_desc" cols="30" rows="6"><?php echo $this->product->metadesc; ?></textarea>
						</td>
					</tr>
					<tr>
						<td >
							<div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_META_KEYWORDS'); ?>: </div>
						</td>
						<td valign="top">
							<textarea class="inputbox" name="metakeyword" id="meta_keyword" cols="30" rows="6"><?php echo $this->product->metakey; ?></textarea>
						</td>
					</tr>
					<tr>
						<td >
							<div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_META_ROBOTS'); ?>: </div>
						</td>
						<td valign="top">
							<input type="text" class="inputbox" size="20" name="metarobot" value="<?php echo $this->product->metarobot ?>" />
						</td>
					</tr>
					<tr>
						<td >
							<div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_META_AUTHOR'); ?>: </div>
						</td>
						<td valign="top">
							<input type="text" class="inputbox" size="20" name="metaauthor" value="<?php echo $this->product->metaauthor ?>" />
						</td>
					</tr>
				</table>
			</fieldset>
		</td>
	</tr>
</table>
<script type="text/javascript">
var tax_rates = new Array();
<?php
foreach( $this->taxrates as $key => $tax_rate ) {
	echo 'tax_rates["'.$tax_rate->tax_rate_id.'"] = '.$tax_rate->tax_rate."\n";
}
?>
function doRound(x, places) {
	return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
}

function getTaxRate() {
	var selected_value = document.adminForm.product_tax_id.selectedIndex;
	var parameterVal = document.adminForm.product_tax_id[selected_value].value;
	

	if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
		return tax_rates[parameterVal];
	} else {
		return 0;
	}
}

function updateGross() {
	if( document.adminForm.product_price.value != '' ) {
		var taxRate = getTaxRate();

		var r = new RegExp("\,", "i");
		document.adminForm.product_price.value = document.adminForm.product_price.value.replace( r, "." );

		var grossValue = document.adminForm.product_price.value;

		if (taxRate > 0) {
			grossValue = grossValue * (taxRate + 1);
		}

		document.adminForm.product_price_incl_tax.value = doRound(grossValue, 5);
	}
}

function updateNet() {
	if( document.adminForm.product_price_incl_tax.value != '' ) {
		var taxRate = getTaxRate();

		var r = new RegExp("\,", "i");
		document.adminForm.product_price_incl_tax.value = document.adminForm.product_price_incl_tax.value.replace( r, "." );

		var netValue = document.adminForm.product_price_incl_tax.value;

		if (taxRate > 0) {
			netValue = netValue / (taxRate + 1);
		}

		document.adminForm.product_price.value = doRound(netValue, 5);
	}
}

function updateDiscountedPrice() {
	if( document.adminForm.product_price.value != '' ) {
		try {
			var selected_discount = document.adminForm.product_discount_id.selectedIndex;
			var discountCalc = document.adminForm.product_discount_id[selected_discount].id;
			<?php if( PAYMENT_DISCOUNT_BEFORE == '1' ) : ?>
			var origPrice = document.adminForm.product_price.value;
			<?php else : ?>
			var origPrice = document.adminForm.product_price_incl_tax.value;
			<?php endif; ?>

			if( discountCalc ) {
				eval( 'var discPrice = ' + origPrice + discountCalc );
				if( discPrice != origPrice ) {
					document.adminForm.discounted_price_override.value = discPrice.toFixed( 2 );
				} else {
					document.adminForm.discounted_price_override.value = '';
				}
			}
		}
		catch( e ) { }
	}
}
updateGross();
updateDiscountedPrice();
</script>