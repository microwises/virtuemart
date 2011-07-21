<?php
/**
*
* Handle the waitinglist
*
* @package	VirtueMart
* @subpackage Product
* @author RolandD
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: product_edit_waitinglist.php 2978 2011-04-06 14:21:19Z alatak $
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

?>
<table width="100%">
	<tr>
		<td valign="top">

			<table>
				<tr class="row0">
					<td style="vertical-align:top;">
					<fieldset style="background-color:#F9F9F9;">
						<legend><?php echo JText::_('COM_VIRTUEMART_RELATED_PRODUCTS');?></legend>
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_RELATED_SEARCH'); ?>
						<div class="jsonSuggestResults" style="width: 322px;">
						<input type="text" size="40" name="search" id="relatedproductSearch" value="" />
						</div>
					</fieldset>
					</td>
				</tr>	<tr class="row0">
					<td style="vertical-align:top;">
					<fieldset style="background-color:#F9F9F9;">
						<legend><?php echo JText::_('COM_VIRTUEMART_RELATED_CATEGORIES');?></legend>
						<?php echo JText::_('COM_VIRTUEMART_CATEGORIES_RELATED_SEARCH'); ?>
						<div class="jsonSuggestResults" style="width: 322px;">
						<input type="text" size="40" name="search" id="relatedcategorySearch" value="" />
						</div>
					</fieldset>
					</td>
				</tr>
				<tr class="row0">
					<td style="vertical-align:top;">
					<fieldset style="background-color:#F9F9F9;">
						<legend><?php echo JText::_('COM_VIRTUEMART_CUSTOM_FIELD_TYPE');?></legend>
						<div><?php echo JText::_('COM_VIRTUEMART_SELECT').' '.$this->customsList; ?></div>
					</fieldset>
					</td>
				</tr>
			</table>

		</td>
		<td valign="top" width="%100">
			<fieldset style="background-color:#F9F9F9;">
					<legend><?php echo JText::_('COM_VIRTUEMART_CUSTOM');?></legend>
			<table id="customfields" class="admin-table" cellspacing="0" cellpadding="0">
				<thead>
				<tr class="row1">
					<th><?php echo JText::_('COM_VIRTUEMART_TITLE');?></th>
					<th><?php echo JText::_('COM_VIRTUEMART_VALUE');?></th>
					<th><?php echo JText::_('COM_VIRTUEMART_TYPE');?></th>
					<th><?php echo JText::_('COM_VIRTUEMART_CUSTOM_IS_CART_ATTRIBUTE');?></th>
					<th><?php echo JText::_('COM_VIRTUEMART_DELETE'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				$i=0;
				if (isset($this->product->customfields)) {
					$this->fieldTypes['R']='COM_VIRTUEMART_RELATED_PRODUCT';
					$this->fieldTypes['Z']='COM_VIRTUEMART_RELATED_CATEGORY';
					foreach ($this->product->customfields as $customRow) {
					if ($customRow->is_cart_attribute) $cartIcone=  'icon-16-default.png';
					else  $cartIcone= 'icon-16-default-off.png'; 
					 echo '<tr>
							
							<td>'.JText::_($customRow->custom_title).'</td>
							<td>
							 '.$customRow->display.$customRow->custom_tip.'
							</td>
							<td>'.JText::_($this->fieldTypes[$customRow->field_type]).'
							 <input type="hidden" value="'.$customRow->field_type .'" name="field['.$i .'][field_type]" />
							 <input type="hidden" value="'.$customRow->virtuemart_custom_id.'" name="field['.$i .'][virtuemart_custom_id]" />
							<input type="hidden" value="'.$customRow->admin_only.'" checked="checked" name="admin_only" />
							</td>
							<td>
							 <img src="components/com_virtuemart/assets/images/icon_16/'.$cartIcone.'" width="16" height="16" border="0" />
							</td>
							<td><div class="remove vmicon-16-trash">'.JText::_('DELETE').'</div></td>
						 </tr>';

						$i++;
					}
				} else {
				echo '<tr>
						<td colspan="5">'.JText::_( 'COM_VIRTUEMART_CUSTOM_NO_TYPES').'
						</td>
					<tr>';
				}
				?>
				</tbody>
			</table>
			</fieldset>
		</td>

	</tr>
</table>

<div style="clear:both;"></div>
<div style="display:none;float:left;" class="customDelete remove vmicon-16-trash"><?php echo JText::_('COM_VIRTUEMART_DELETE'); ?></div>

<script type="text/javascript">
nextCustom = <?php echo $i ?>;
jQuery('div.remove').click( function() {
	jQuery(this).closest('tr').remove();
});


jQuery('select#customlist').click(function() {
	selected = jQuery(this).find( 'option:selected').val() ;
	jQuery.getJSON('index.php?option=com_virtuemart&view=product&task=getData&format=json&type=customfield&id='+selected+'&row='+nextCustom+'&virtuemart_product_id=<?php echo $this->product->virtuemart_product_id; ?>',
	function(data) {
		var trash = jQuery("div.customDelete").clone().css('display', 'block').removeClass('customDelete');
		jQuery.each(data.value, function(index, value){
			jQuery("table#customfields").append(value);
		});
		jQuery("table#customfields tr").find("td:empty").append(trash).click( function() {
			jQuery(this).closest('tr').remove();
		});
	});
	nextCustom++;
});

function removeSelectedOptions(from) {
	jQuery('select#'+from+' :selected').remove()
}

	function customautocomplete( $type ,$id) {
		jQuery('input#related'+$type+'Search').autocomplete('index.php?option=com_virtuemart&view=product&task=getData&format=json&type=related'+$type, {
			mustMatch: false,
			max : 50,
			dataType: "json",
			minChars:2,
			cacheLength:20,
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
			jQuery.getJSON('index.php?option=com_virtuemart&view=product&task=getData&format=json&type='+$type+'&id='+item.id+'&row='+nextCustom+'&virtuemart_'+$type+'_id='+$id,
				function(data) {
					var trash = jQuery("div.customDelete").clone().css('display', 'block').removeClass('customDelete');
					jQuery.each(data.value, function(index, value){
						jQuery("table#customfields").append(value);
					});
					jQuery("table#customfields tr").find("td:empty").append(trash).click( function() {
						jQuery(this).closest('tr').remove();
					});
				});
			nextCustom++;

		});

	}
	customautocomplete( 'product' ,<?php echo (int)$this->product->virtuemart_product_id; ?>) 
	customautocomplete( 'category' ,<?php echo (int)$this->product->virtuemart_category_id; ?>) 
</script>