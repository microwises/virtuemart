<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage 
* @author
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
<table width="100%">
	<tr>
		<td width="50%">
			<table class="adminform">
				<tr class="row0">
					<td width="21%">
						<div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('VM_PRODUCT_FORM_IN_STOCK') ?>:</div>
					</td>
					<td width="79%">
						<input type="text" class="inputbox"  name="product_in_stock" value="<?php echo $this->product->product_in_stock; ?>" size="10" />
					</td>
				</tr>
				<!-- low stock notification -->
				<tr class="row1">
					<td width="21%">
						<div style="text-align:right;font-weight:bold;">
							<?php echo JText::_( 'VM_LOW_STOCK_NOTIFICATION' ); ?>:
						</div>
					</td>
					<td width="79%">
						<input type="text" class="inputbox" name="low_stock_notification" value="<?php echo $this->product->low_stock_notification; ?>" size="3" />
					</td>
				</tr>
				<!-- end low stock notification -->
				<tr class="row0"> 
					<td width="21%">
						<div style="text-align:right;font-weight:bold;">
							<?php echo JText::_('VM_PRODUCT_FORM_MIN_ORDER') ?>:
						</div>
					</td>
					<td width="79%">
						<input type="text" class="inputbox"  name="min_order_level" value="<?php echo $this->min_order; ?>" size="10" />
					</td>
				</tr>
				<tr class="row1"> 
					<td width="21%">
						<div style="text-align:right;font-weight:bold;">
							<?php echo JText::_('VM_PRODUCT_FORM_MAX_ORDER') ?>:
						</div>
					</td>
					<td width="79%">
						<input type="text" class="inputbox"  name="max_order_level" value="<?php echo $this->max_order; ?>" size="10" />
					</td>
				</tr>
				<tr class="row0"> 
					<td width="21%" >
						<div style="text-align:right;font-weight:bold;">
							<?php echo JText::_('VM_PRODUCT_FORM_AVAILABLE_DATE') ?>:
						</div>
					</td>
					<td width="79%" >
						<?php echo JHTML::_('calendar', date('Y-m-d', $this->product->product_available_date), "product_available_date", "product_available_date"); ?>
					</td>
				</tr>
				<tr>
					<td valign="top" width="21%" >
						<div style="text-align:right;font-weight:bold;">
							<?php echo JText::_('VM_AVAILABILITY') ?>:
						</div>
					</td>
					<td width="79%" >
						<input type="text" class="inputbox" id="product_availability" name="product_availability" value="<?php echo $this->product->product_availability; ?>" />
						<?php
						echo JHTML::tooltip(JText::_('VM_PRODUCT_FORM_AVAILABILITY_TOOLTIP1'), JText::_('VM_AVAILABILITY'), 'tooltip.png', '', '', false);
						$path = str_ireplace(str_replace(DS, '/', JPATH_SITE), '', str_replace('\\', '/', VM_THEMEPATH)."images/availability/");
						?>
						<script type="text/javascript">
							jQuery('#image').live('click', function() { jQuery('#product_availability').val(jQuery('#image').val()); })
						</script>
						<?php
						echo JHTML::_('list.images', 'image', $this->product->product_availability, null, $path);
						echo JHTML::tooltip(str_replace('%s', $path, JText::_('VM_PRODUCT_FORM_AVAILABILITY_TOOLTIP2')), JText::_('VM_AVAILABILITY'), 'tooltip.png', '', '', false); 
						?> 
					</td>
				</tr>
				<tr>
					<td width="21%">&nbsp;</td>
					<td width="79%"><img border="0" id="imagelib" alt="<?php echo JText::_('PREVIEW'); ?>" name="imagelib" src="<?php echo JURI::root().$path.$this->product->product_availability;?>"/></td>
				</tr>
				<tr class="row1">
					<td width="21%" >
						<div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('VM_PRODUCT_FORM_SPECIAL') ?>:</div>
					</td>
					<td width="79%" >
						<?php
							$checked = '';
							if (strtoupper($this->product->product_special) == "Y") $checked = 'checked="checked"' ?>
							<input type="checkbox" name="product_special" value="Y" <?php echo $checked; ?> />
					</td>
				</tr>
				<tr class="row0">
					<td colspan="2">&nbsp;</td>
				</tr>
			</table>
		</td>
		<td width="50%" valign="top">
			<table class="adminform">
				<tr class="row1">
					<td colspan="3"><h2><?php echo JText::_('VM_RELATED_PRODUCTS'); ?></h2></td>
				</tr>
				<tr class="row0">
					<td style="vertical-align:top;"><br />
						<?php echo JText::_('VM_PRODUCT_RELATED_SEARCH'); ?>
						<input type="text" size="40" name="search" id="relatedProductSearch" value="" />
						<div class="jsonSuggestResults" style="width: 322px; display: none;"/>
					</td>
					<td>
						<input type="button" name="remove_related" onclick="removeSelectedOptions('related_products');" value="&nbsp; &lt; &nbsp;" />
					</td>
					<td>
						<?php echo $this->lists['related_products']; ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<script type="text/javascript">
jQuery('input#relatedProductSearch').autocomplete('index.php?option=com_virtuemart&view=product&task=getData&format=json&type=relatedproducts', {
		mustMatch: false,
		dataType: "json",
		parse: function(data) {
			return jQuery.map(data, function(row) {
				return {
					data: row,
					value: row.value,
					result: row.value
				}
			});
		},
		formatItem: function(item) {
			return item.value;
		}
	}).result(function(e, item) {
		/* Check if the item is already there */
		var items = [];
		jQuery('select#related_products').children().each(function() {
			items[items.length++] = jQuery(this).val();
		})
	
		if (jQuery.inArray(item.id, items) >= 0) {
			return;
		}
		jQuery("select#related_products").append('<option value="'+item.id+'" selected="selected">'+item.value+'</option>');
	});

function removeSelectedOptions(from) {
	jQuery('select#'+from+' :selected').remove()
}
</script>
