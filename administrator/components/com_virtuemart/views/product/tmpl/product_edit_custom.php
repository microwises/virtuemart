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
<fieldset style="background-color:#F9F9F9;">
		<legend><?php echo JText::_('COM_VIRTUEMART_CUSTOM');?></legend>
<table id="customfields">
	<?php
	$i=0;
	if (isset($this->product->customfields)) {
		
		foreach ($this->product->customfields as $customRow) {
		 echo '<tr>
				
				<td>'.$customRow->custom_title.'</td>
				<td>
				 '.$customRow->display.$customRow->custom_tip.'
				</td>
				<td>'.$this->fieldTypes[$customRow->field_type].'
				 <input type="hidden" value="'.$customRow->field_type .'" name="field['.$i .'][field_type]" />
				 <input type="hidden" value="'.$customRow->custom_id.'" name="field['.$i .'][custom_id]" />
				 <input type="hidden" value="'.$customRow->custom_field_id.'" name="field['.$i .'][custom_field_id]" />
				 <input type="checkbox" value="'.$customRow->admin_only.'" checked="checked" name="admin_only" />
				</td>
				<td><div style="float:left;" class="remove vmicon-16-trash">_ </div></td>
			 </tr>';

			$i++;
		}
	} else {
	echo '<tr>
			<td colspan="4">'.JText::_( 'COM_VIRTUEMART_TYPES_NO_TYPES').'
			</td>
		<tr>';
	}
	?>
</table>
</fieldset>
<div style="clear:both;"></div>
<div style="display:none;float:left;" class="customDelete remove vmicon-16-trash">_ </div>
<div><?php echo $this->customsList; ?></div>
<?php /*<div class="jsonSuggestResults" style="width: 322px;">
	<input type="text" size="40" name="search" id="ProductCustomSearch" value="" />
</div> */ ?>
<script type="text/javascript">
nextCustom = <?php echo $i ?>;
jQuery('div.remove').click( function() {
	jQuery(this).parents('tr').remove();
});

jQuery('input#ProductCustomSearch').autocomplete('index.php?option=com_virtuemart&view=product&task=getData&format=json&type=custom', {
		mustMatch: false,
		max : 50,
		dataType: "json",
		minChars:2,
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
		jQuery('customfields input').children().each(function() {
			items[items.length++] = jQuery(this).val();
		})
	
		if (jQuery.inArray(item.id, items) >= 0) {
			return;
		}
		var custom = item.value.split('::');
		var Attributes = item.id.split('|');

		jQuery("div#customfields").append('<div>'+custom[0]+'<input type="text" value="'+Attributes[1]+'" name="custom['+Attributes[0]+']"  size="120">'+custom[1]+'</div><div>Type : '+Attributes[2]+'</div>');
	});
jQuery('select#customlist').click(function() {
	selected = jQuery(this).find( 'option:selected').val() ;
	jQuery.getJSON('index.php?option=com_virtuemart&view=product&task=getData&format=json&type=customfield&id='+selected+'&row='+nextCustom+'&product_id=<?php echo $this->product->product_id; ?>',
		function(data) {
			var trash = jQuery("div.customDelete").clone().css('display', 'block').removeClass('customDelete');
			jQuery.each(data.value, function(index, value){
				jQuery("table#customfields").append(value);
//				jQuery("table#customfields td:last").append(trash);
			});
			jQuery("table#customfields tr").find("td:empty").append(trash).click( function() {
	jQuery(this).parents('tr').remove();
	//jQuery(this).remove();
	

});
		});
});

function removeSelectedOptions(from) {
	jQuery('select#'+from+' :selected').remove()
}
</script>
