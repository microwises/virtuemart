<?php
/**
*
* Main product information
*
* @package	VirtueMart
* @subpackage Product
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
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PUBLISH') ?></div>
					</td>
					<td width="79%">
						<fieldset class="radio">
						<?php echo JHTMLSelect::booleanlist('published', null, $this->product->published); ?>
						</fieldset>
					</td>
				</tr>
				<tr class="row1">
					<td width="21%" >
						<div style="text-align:right;font-weight:bold;"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_SKU') ?></div>
					</td>
					<td width="79%" height="2">
						<input type="text" class="inputbox" name="product_sku" value="<?php echo $this->product->product_sku; ?>" size="32" maxlength="64" />
					</td>
				</tr>
				<tr class="row0">
					<td width="21%" height="18"><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_NAME') ?></div>
					</td>
					<td width="79%" height="18" >
						<input type="text" class="inputbox"  name="product_name" value="<?php echo $this->product->product_name; ?>" size="32" maxlength="255" />
					</td>
				</tr>
				<tr class="row0">
					<td width="21%" height="18"><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_ALIAS') ?></div>
					</td>
					<td width="79%" height="18" >
						<input type="text" class="inputbox"  name="slug" value="<?php echo $this->product->slug; ?>" size="32" maxlength="255" />
					</td>
				</tr>
				<tr class="row1">
					<td width="21%"><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_URL') ?></div>
					</td>
					<td width="79%">
						<input type="text" class="inputbox"  name="product_url" value="<?php echo $this->product->product_url; ?>" size="32" maxlength="255" />
					</td>
				</tr>
				<tr class="row0">
					<td width="21%"><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_VENDOR') ?></div>
					</td>
				<td width="79%">
					<?php echo $this->lists['vendors'];?>
				</td>
			</tr>
			<tr class="row1">
				<td width="21%" ><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_MANUFACTURER') ?></div>
				</td>
				<td width="79%">
					<?php echo $this->lists['manufacturers'];?>
				</td>
			</tr>
			<?php //if (!$this->product->product_parent_id) { ?>
			<tr class="row0">
				<td width="29%" valign="top">
					<div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('COM_VIRTUEMART_CATEGORIES') ?>:</div>
				</td>
				<td width="71%" >
					<select class="inputbox" id="categories" name="categories[]" multiple="multiple" size="10">
						<?php echo $this->category_tree; ?>
					</select>
				</td>
			</tr>
			<tr class="row1">
				<td width="21%" ><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('COM_VIRTUEMART_PRODUCT_DETAILS_PAGE') ?>:</div>
				</td>
				<td width="79%">
					<?php echo JHTML::_('Select.genericlist', $this->productLayouts, 'layout', 'size=1', 'text', 'text', $this->product->layout); ?>
				</td>
			</tr>
			<?php // } ?>
		</table>
	</td>
	<td>
		<!-- Product pricing -->
		<fieldset>
		<legend><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICES'); ?></legend>
		<table class="adminform">

			<tr class="row0">
				<td width="29%">
					<div style="text-align:right;font-weight:bold;">
                
                                        <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_COST_TIP'); ?>">
					   <?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_COST') ?>:
				      </span>
                                      </div>
				</td>
				<td width="71%">
					<input type="text" class="inputbox" name="product_price" size="10" value="<?php echo $this->product->prices['costPrice']; ?>" />

					<?php    echo $this->currencies;    ?>
				</td>
			</tr>
			<tr class="row1">
				<td width="29%">
                                    <div style="text-align:right;font-weight:bold;">

                                        <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_BASE_TIP'); ?>">
					   <?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_BASE') ?>:
				      </span>
                                      </div>

				</td>
				<td width="71%">
					<input type="text" readonly class="inputbox" name="basePrice" size="10" value="<?php echo $this->product->prices['basePrice']; ?>" />
					<?php echo $this->vendor_currency;   ?>
				</td>
			</tr>
			<tr class="row1">
				<td width="29%">
                                        <div style="text-align:right;font-weight:bold;">
                                        <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_FINAL_TIP'); ?>">
					   <?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_FINAL') ?>:
				      </span>
                                     </div>
				</td>
				<td width="71%">
					<input type="text" readonly class="inputbox" name="product_price_incl_tax" size="10" value="<?php echo $this->product->prices['salesPrice']; ?>" />
					<?php echo $this->vendor_currency;   ?>
				</td>
			</tr>
		</table>
		</fieldset>
		<!-- Product rules overrides -->
		<fieldset>
		<legend><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_RULES_OVERRIDES'); ?></legend>
		<table class="adminform">
			<tr class="row0">
				<td width="29%" ><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('COM_VIRTUEMART_RATE_FORM_VAT_ID') ?>:</div>
				</td>
				<td width="71%" >
					<?php echo $this->lists['taxrates']; ?><br />
                                    <?php echo $this->taxRules ?>
				</td>
			</tr>
			<tr class="row1">
				<td width="21%" ><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCOUNT_TYPE') ?>:</div>
				</td>
				<td width="79%">
					<?php echo $this->lists['discounts']; ?>
                                    <br />
                                        <?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCOUNT_EFFECTING').$this->dbTaxRules;  ?>
				</td>
			</tr>

<?php	/*	The problem here is that we can only override one discount (there is only one field for it)
			<tr class="row1">
				<td width="21%" ><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_DBDISCOUNT_TYPE') ?>:</div>
				</td>
				<td width="79%">
					<?php echo $this->lists['dbdiscounts']; echo $this->dbTaxRules;  ?>
				</td>
			</tr>
			<tr class="row1">
				<td width="21%" ><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_DADISCOUNT_TYPE') ?>:</div>
				</td>
				<td width="79%">
					<?php echo $this->lists['dadiscounts']; echo $this->daTaxRules ?>
				</td>
			</tr> */ ?>
			<tr class="row0">
				<td width="21%" >
					<div style="text-align:right;font-weight:bold;">
                                            <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCOUNTED_PRICE_TIP'); ?>">
					   <?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCOUNTED_PRICE') ?>:
				      </span>
                                        </div>
				</td>
				<td width="79%" >
					<input type="text" size="10" name="product_override_price" value="<?php echo $this->product_override_price ?>"/>
					<?php
					 
					$checked = '';
					if ($this->override) $checked = 'checked="checked"' ?>
					<input type="checkbox" name="override" value="1" <?php echo $checked; ?> />
				</td>
			</tr>
		</table>
		</fieldset>
                <table class="adminform">
                <tr class="row1">
                            <td width="21%" >
                                    <div style="text-align:right;font-weight:bold;">
                                    <?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_SPECIAL') ?>:</div>
                            </td>
                            <td width="79%" >
                                    <?php
                                            $checked = '';
                                            if (strtoupper($this->product->product_special) == "Y") $checked = 'checked="checked"' ?>
                                            <input type="checkbox" name="product_special" value="Y" <?php echo $checked; ?> />
                            </td>
                    </tr>
                </table>
		<table class="adminform">
			<tr>
				<td width="29%" valign="top">
					<div style="text-align:right;font-weight:bold;"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_INTNOTES'); ?>:</div>
				</td>
				<td width="71%" valign="top">
					<textarea class="inputbox" name="intnotes" id="intnotes" cols="35" rows="6" ><?php echo $this->product->intnotes; ?></textarea>
				</td>
			</tr>

		</table>
	</td>
	</tr>
</table>
<script type="text/javascript">
var tax_rates = new Array();
<?php
if( property_exists($this, 'taxrates') && is_array( $this->taxrates )) {
	foreach( $this->taxrates as $key => $tax_rate ) {
		echo 'tax_rates["'.$tax_rate->tax_rate_id.'"] = '.$tax_rate->tax_rate."\n";
	}
}
?>

</script>
