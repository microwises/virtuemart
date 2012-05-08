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
<?php echo $this->langList;
$i=0;
?>
<table class="adminform">
	<tr>
		<td valign="top">
			<fieldset>
				<legend>
				<?php echo JText::_('COM_VIRTUEMART_PRODUCT_INFORMATION'); echo ' id: '.$this->product->virtuemart_product_id ?></legend>
				<table class="adminform">
					<tr class="row<?php echo $i?>">
						<td width="21%"><div style="text-align: right; font-weight: bold;">
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PUBLISH') ?></div>
						</td>
						<td width="79%">
							<fieldset class="radio">
							<?php echo JHTMLSelect::booleanlist('published', null, $this->product->published); ?>
							</fieldset>
						</td>
					</tr>
				<?php $i = 1 - $i; ?>
				<tr class="row<?php echo $i?>">
					<td width="21%" >
						<div style="text-align:right;font-weight:bold;"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_SKU') ?></div>
					</td>
					<td width="79%" height="2">
						<input type="text" class="inputbox" name="product_sku" id="product_sku" value="<?php echo $this->product->product_sku; ?>" size="32" maxlength="64" />
					</td>
				</tr>
				<?php $i = 1 - $i; ?>
				<tr class="row<?php echo $i?>">
					<td width="21%" height="18"><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_NAME') ?></div>
					</td>
					<td width="79%" height="18" >
						<input type="text" class="inputbox"  name="product_name" id="product_name" value="<?php echo htmlspecialchars($this->product->product_name); ?>" size="32" maxlength="255" />
					</td>
				</tr>
				<?php $i = 1 - $i; ?>
				<tr class="row<?php echo $i?>">
					<td width="21%" height="18"><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_ALIAS') ?></div>
					</td>
					<td width="79%" height="18" >
						<input type="text" class="inputbox"  name="slug" id="slug" value="<?php echo $this->product->slug; ?>" size="32" maxlength="255" />
					</td>
				</tr>
				<?php $i = 1 - $i; ?>
				<tr class="row<?php echo $i?>">
					<td width="21%"><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_URL') ?></div>
					</td>
					<td width="79%">
						<input type="text" class="inputbox" name="product_url" value="<?php echo $this->product->product_url; ?>" size="32" maxlength="255" />
					</td>
				</tr>
						<?php $i = 1 - $i; ?>
			<?php	if(Vmconfig::get('multix','none')!=='none'){ ?>
				<tr class="row<?php echo $i?>">
					<td width="21%"><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_VENDOR') ?></div>
					</td>
				<td width="79%">
					<?php echo $this->lists['vendors'];?>
				</td>
				</tr>
				<?php $i = 1 - $i; ?>
				<?php } ?>


				<?php if(isset($this->lists['manufacturers'])){?>
				<tr class="row<?php echo $i?>">
					<td width="21%" ><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_MANUFACTURER') ?></div>
					</td>
					<td width="79%">
						<?php echo $this->lists['manufacturers'];?>
					</td>
				</tr>
				<?php $i = 1 - $i; ?>
				<?php }?>
				<tr class="row<?php echo $i?>">
					<td width="29%" valign="top">
						<div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_CATEGORY_S') ?></div>
					</td>
					<td width="71%" >
						<select class="inputbox" id="categories" name="categories[]" multiple="multiple" size="10">
							<option value=""><?php echo JText::_('COM_VIRTUEMART_UNCATEGORIZED')  ?></option>
							<?php echo $this->category_tree; ?>
						</select>
					</td>
				</tr>
				<?php $i = 1 - $i; ?>
				<tr class="row<?php echo $i?>">
					<td width="21%" ><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_SHOPPER_FORM_GROUP') ?></div>
					</td>
					<td width="79%">
						<?php echo $this->shoppergroupList; ?>
					</td>
				</tr>
				<?php $i = 1 - $i; ?>
				<tr class="row<?php echo $i?>">
					<td width="21%" ><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_DETAILS_PAGE') ?></div>
					</td>
					<td width="79%">
						<?php echo JHTML::_('Select.genericlist', $this->productLayouts, 'layout', 'size=1', 'value', 'text', $this->product->layout); ?>
					</td>
				</tr>
				<?php $i = 1 - $i; ?>
				<tr class="row<?php echo $i?>">
					<td width="21%" ><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_SPECIAL') ?></div>

					</td>
					<td width="79%" >

					<?php echo VmHTML::checkbox('product_special', $this->product->product_special); ?>
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

					<tr class="row0" >
						<td width="17%" >
							<div style="text-align: right; font-weight: bold;">
								<span
									class="hasTip"
									title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_COST_TIP'); ?>">
									<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_COST') ?>
								</span>
							</div>
						</td>
						<td width="71%" colspan="2" ><input
							type="text"
							class="inputbox"
							name="product_price"
							size="12"
							style="text-align:right;"
							value="<?php echo $this->product->prices['costPrice']; ?>" />
							<?php echo $this->currencies; ?>
						</td>
					</tr>
					<tr class="row1">
						<td >
							<div style="text-align: right; font-weight: bold;">

								<span
									class="hasTip"
									title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_BASE_TIP'); ?>">
									<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_BASE') ?>
								</span>
							</div>
						</td>
						<td colspan="2" ><input
							type="text"
							readonly
							class="inputbox readonly"
							name="basePrice"
							size="12"
							value="<?php echo $this->product->prices['basePrice']; ?>" />
						<?php echo $this->vendor_currency;   ?>
						</td>
					</tr>
					<tr class="row1">
						<td  >
							<div style="text-align: right; font-weight: bold;">
								<span
									class="hasTip"
									title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_FINAL_TIP'); ?>">
									<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_FINAL') ?>
								</span>
							</div>
						</td>
						<td ><input
							type="text"
							name="salesPrice"
							size="12"
							style="text-align:right;"
							value="<?php echo $this->product->prices['salesPriceTemp']; ?>" />

							<?php echo $this->vendor_currency;   ?>
						</td>
						<td >	<input type="checkbox" name="use_desired_price" value="1" />
							<span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_CALCULATE_PRICE_FINAL_TIP'); ?>">
							<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_CALCULATE_PRICE_FINAL'); ?>
							</span>
						</td>
					</tr>
					<tr class="row0">
						<td>
							<div style="text-align: right; font-weight: bold;">
								<span
									class="hasTip"
									title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_OVERRIDE_TIP'); ?>">
									<?php echo JText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_OVERRIDE') ?>
								</span>
							</div>
						</td>
						<td>
							<input type="text" size="12" style="text-align:right;" name="product_override_price" value="<?php echo $this->product->product_override_price ?>"/>
							<?php echo $this->vendor_currency;   ?>
						</td>
						<td><?php
// 							echo VmHtml::checkbox('override',$this->product->override);
						$options = array(0 => 'Disabled', 1 => 'Overwrite final',-1 =>'Overwrite price to tax');
							echo VmHtml::radioList('override',$this->product->override,$options);

						?>
						</td>
					</tr>
				</table>
			</fieldset> <!-- Product rules overrides -->
			<fieldset>
			<legend>
			<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_RULES_OVERRIDES'); ?></legend>
			<table class="adminform">
				<tr class="row0">
					<td width="17%"><div style="text-align: right; font-weight: bold;">
						<?php echo JText::_('COM_VIRTUEMART_RATE_FORM_VAT_ID') ?></div>
					</td>
					<td width="30%">
						<?php echo $this->lists['taxrates']; ?><br />
					</td>
					<td>
						<?php echo JText::_('COM_VIRTUEMART_TAX_EFFECTING').'<br />'.$this->taxRules ?>
					</td>
				</tr>
				<tr class="row1">
					<td><div style="text-align: right; font-weight: bold;">
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCOUNT_TYPE') ?></div>
					</td>
					<td >
						<?php echo $this->lists['discounts']; ?> <br />
							<td>
							<?php if(!empty($this->DBTaxRules)){

								echo JText::_('COM_VIRTUEMART_RULES_EFFECTING').'<br />'.$this->DBTaxRules.'<br />';

							}
							if(!empty($this->DATaxRules)){
								echo JText::_('COM_VIRTUEMART_RULES_EFFECTING').'<br />'.$this->DATaxRules;

							}
// 						vmdebug('my rules',$this->DBTaxRules,$this->DATaxRules); echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCOUNT_EFFECTING').$this->DBTaxRules;  ?>
						</td>
					</td>
				</tr>

			</table>
		</fieldset>
		</td>
	</tr>
	<tr>
	<td colspan="2" >
	<fieldset>
		<legend>
		<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_CHILD_PARENT'); ?></legend>
		<table class="adminform">
			<tr class="row<?php echo $i?>">
				<td width="50%">
				<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=product&task=createVariant&virtuemart_product_id='.$this->product->virtuemart_product_id.'&token='.JUtility::getToken() ); ?>

						<div class="button2-left">
							<div class="blank">
								<a href="<?php echo $link ?>">
								<?php echo Jtext::_('COM_VIRTUEMART_PRODUCT_ADD_CHILD'); ?>
								</a>
							</div>
						</div>
				</td>

				<td width="29%"><div style="text-align:right; font-weight: bold;">
					<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PARENT') ?>
				</td>
				<td width="71%"> <?php
				if ($this->product->product_parent_id) {
					$parentRelation= VirtueMartModelCustomfields::getProductParentRelation($this->product->virtuemart_product_id);

					$result = JText::_('COM_VIRTUEMART_EDIT').' ' . $this->product_parent->product_name;
					echo ' | '.JHTML::_('link', JRoute::_('index.php?view=product&task=edit&virtuemart_product_id='.$this->product->product_parent_id.'&option=com_virtuemart'), $this->product_parent->product_name, array('title' => $result)).' | '.$parentRelation;
				}
				?>
				</td>

			</tr>

			<?php $i = 1 - $i; ?>

			<tr class="row<?php echo $i?>" >
				<td width="79%" colspan = "3"><?php
                if (count($this->product_childs)>0 ) {

                	$customs = array();
                	if(!empty($this->product->customfields)){
                		foreach($this->product->customfields as $custom){
                			vmdebug('my custom',$custom);
                			if($custom->field_type=='A'){
                				$customs[] = $custom;
                			}
                		}
                	}

//					vmdebug('ma $customs',$customs);
					?>

					<table class="adminlist">
						<tr>
							<th><?php echo JText::_('COM_VIRTUEMART_PRODUCT_CHILD') ?></th>
							<th><?php echo JText::_('COM_VIRTUEMART_PRODUCT_CHILD_NAME')?></th>
							<th><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_COST')?></th>
							<th><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_IN_STOCK')?></th>
							<th><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_ORDERED_STOCK')?></th>
							<?php foreach($customs as $custom){ ?>
								<th><?php echo JText::sprintf('COM_VIRTUEMART_PRODUCT_CUSTOM_FIELD_N',$custom->custom_value)?></th>
							<?php } ?>
							<th><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PUBLISH')?></th>
						</tr>
						<?php
						foreach ($this->product_childs as $child  ) {
							$i = 1 - $i;
							 ?>
							<tr class="row<?php echo $i ?>">
								<td><?php echo JHTML::_('link', JRoute::_('index.php?view=product&task=edit&product_parent_id='.$this->product->virtuemart_product_id.'&virtuemart_product_id='.$child->virtuemart_product_id.'&option=com_virtuemart'), $child->slug, array('title' => JText::_('COM_VIRTUEMART_EDIT').' '.$child->product_name)) ?></td>
								<td><input type="text" class="inputbox" name="childs[<?php echo $child->virtuemart_product_id ?>][product_name]" size="32" value="<?php echo $child->product_name ?>" /></td>
								<td><input type="text" class="inputbox" name="childs[<?php echo $child->virtuemart_product_id ?>][product_price]" size="10" value="<?php echo $child->product_price ?>" /></td>
								<td><?php echo $child->product_in_stock ?></td>
								<td><?php echo $child->product_ordered ?></td>
								<?php foreach($customs as $custom){
									$attrib = $custom->custom_value;
									if(isset($child->$attrib)){
										$childAttrib = $child->$attrib;
									} else {
										vmdebug('unset? use Fallback product_name instead $attrib '.$attrib,$custom);
										$attrib = 'product_name';
										$childAttrib = $child->$attrib;

									}
									?>
									<td><input type="text" class="inputbox" name="childs[<?php echo $child->virtuemart_product_id ?>][<?php echo $attrib ?>]" size="10" value="<?php echo $childAttrib ?>" /></td>
									<?php
								}
								?>
								<td>
									<?php echo VmHTML::checkbox('childs['.$child->virtuemart_product_id.'][published]', $this->product->published) ?></td>
							</tr>
							<?php
						} ?>
						</table>
					 <?php
					 }
					 ?>
				</td>
			</tr>
		</table>
	</fieldset>
	</tr>

	<tr>
		<td
			width="100%"
			valign="top"
			colspan="2">
			<fieldset>
				<legend>
				<?php echo JText::_('COM_VIRTUEMART_PRODUCT_PRINT_INTNOTES'); ?></legend>
				<textarea style="width: 100%;" class="inputbox" name="intnotes" id="intnotes" cols="35" rows="6">
					<?php echo $this->product->intnotes; ?></textarea>
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

<script type="text/javascript">
<!--

/* JS for editstatus */

jQuery('.sendEmailFormSubmit').click(function() {
	//document.orderStatForm.task.value = 'updateOrderItemStatus';
	document.sendEmailFormSubmit.submit();

	return false
});

var editingItem = 0;

</script>
