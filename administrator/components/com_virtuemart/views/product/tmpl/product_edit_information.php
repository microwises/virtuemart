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
                    <fieldset>
		<legend><?php echo JText::_('COM_VIRTUEMART_PRODUCT_INFORMATION'); ?></legend>
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
			<?php	if(Vmconfig::get('multix','none')!=='none'){ ?>
				<tr class="row0">
					<td width="21%"><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_VENDOR') ?></div>
					</td>
				<td width="79%">
					<?php echo $this->lists['vendors'];?>
				</td>
			<?php } ?>
			</tr>
			<tr class="row1">
				<td width="21%" ><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('COM_VIRTUEMART_MANUFACTURER') ?></div>
				</td>
				<td width="79%">
					<?php echo $this->lists['manufacturers'];?>
				</td>
			</tr>
			<tr class="row0">
				<td width="29%" valign="top">
					<div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('COM_VIRTUEMART_CATEGORY_S') ?>:</div>
				</td>
				<td width="71%" >
					<select class="inputbox" id="categories" name="categories[]" multiple="multiple" size="10">
						<option value=""><?php echo JText::_('COM_VIRTUEMART_UNCATEGORIZED')  ?></option>
						<?php echo $this->category_tree; ?>
					</select>
				</td>
			</tr>
			<tr class="row1">
				<td width="21%" ><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('COM_VIRTUEMART_PRODUCT_DETAILS_PAGE') ?></div>
				</td>
				<td width="79%">
					<?php echo JHTML::_('Select.genericlist', $this->productLayouts, 'layout', 'size=1', 'text', 'text', $this->product->layout); ?>
				</td>
			</tr>
			<tr class="row0">
				<td width="21%" >

				</td>
				<td width="79%" >
					<span style="font-weight:bold;">
					<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_SPECIAL') ?></span>
					<?php
					$checked = '';
					if (!empty($this->product_special)) $checked = 'checked="checked"' ?>
					<input type="checkbox" name="product_special" value="1" <?php echo $checked; ?> />
				</td>
			</tr>
		</table>
			</fieldset>

		<fieldset>
		<legend><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_CHILD_PARENT'); ?></legend>
		<table class="adminform">
			<tr class="row0">
				<td width="29%" ><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PARENT') ?></div>
				</td>
				<td width="71%" > <?php
                                if ($this->product->product_parent_id) {
                                	$parentRelation= VirtueMartModelCustomfields::getProductParentRelation($this->product->virtuemart_product_id);
                                    $result = JText::_('COM_VIRTUEMART_EDIT').' ' . $this->product_parent->product_name;
                                    echo JHTML::_('link', JRoute::_('index.php?view=product&task=edit&virtuemart_product_id='.$this->product->product_parent_id.'&option=com_virtuemart'), $this->product_parent->product_name, array('title' => $result)).$parentRelation;
                                }
                ?>
				</td>
			</tr>
			<tr class="row1">
				<td width="21%" valign="top"><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('COM_VIRTUEMART_PRODUCT_CHILD') ?></div>
				</td>
				<td width="79%"><?php
                	if ($this->product_child) {
						foreach ($this->product_child as $child  ) {
							$ChildCustom = VirtueMartModelCustomfields::getProductChildCustom($child->virtuemart_product_id);
							echo JHTML::_('link', JRoute::_('index.php?view=product&task=edit&product_parent_id='.$this->product->virtuemart_product_id.'&virtuemart_product_id='.$child->virtuemart_product_id.'&option=com_virtuemart'), $child->product_name, array('title' => JText::_('COM_VIRTUEMART_EDIT').' '.$child->product_name)).' ';
							echo JHTML::_('select.genericlist', $this->ChildCustomRelation,'ChildCustomRelation['.$child->virtuemart_product_id.'][virtuemart_custom_id]','','value','text',$ChildCustom->virtuemart_custom_id).' <input type="text" name="ChildCustomRelation['.$child->virtuemart_product_id.'][custom_value]" value="'.$ChildCustom->custom_value.'"><br />';
						}
					 }
                                 ?>
				</td>
			</tr>


		</table>
		</fieldset>


	</td>
	<td valign="top">
		<!-- Product pricing -->
		<fieldset>
		<legend><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICES'); ?></legend>
		<table class="adminform">

			<tr class="row0">
				<td width="29%">
					<div style="text-align:right;font-weight:bold;">

                                        <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_COST_TIP'); ?>">
					   <?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_COST') ?>
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

                                        <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_BASE_TIP'); ?>">
					   <?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_BASE') ?>
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
                                        <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_FINAL_TIP'); ?>">
					   <?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_FINAL') ?>
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
					<?php echo JText::_('COM_VIRTUEMART_RATE_FORM_VAT_ID') ?></div>
				</td>
				<td width="71%" >
					<?php echo $this->lists['taxrates']; ?><br />
                                    <?php echo $this->taxRules ?>
				</td>
			</tr>
			<tr class="row1">
				<td width="21%" ><div style="text-align:right;font-weight:bold;">
					<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCOUNT_TYPE') ?></div>
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
                                            <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCOUNTED_PRICE_TIP'); ?>">
					   <?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCOUNTED_PRICE') ?>
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

	</td>
	</tr>
	<tr>
		<td width="100%" valign="top" colspan="2">
			<fieldset>
				<legend><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_INTNOTES'); ?></legend>
				<textarea style="width:100%;" class="inputbox" name="intnotes" id="intnotes" cols="35" rows="6" ><?php echo $this->product->intnotes; ?></textarea>
			</fieldset>
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
