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
		<td valign="top" width="%100">

			<?php
			$i=0;
			$tables= array('categories'=>'','products'=>'','fields'=>'','childs'=>'',);
			if (isset($this->product->customfields)) {
				$this->fieldTypes['R']=JTEXT::_('COM_VIRTUEMART_RELATED_PRODUCTS');
				$this->fieldTypes['Z']=JTEXT::_('COM_VIRTUEMART_RELATED_CATEGORIES');
				foreach ($this->product->customfields as $customRow) {
					if ($customRow->is_cart_attribute) $cartIcone=  'default';
					else  $cartIcone= 'default-off'; 
					if ($customRow->field_type == 'Z') { 

						$tables['categories'] .=  '<div class="vm_thumb_image">
							<span>'.$customRow->display.'</span>
							<span>'.JText::_($this->fieldTypes[$customRow->field_type]).'</span>
							<input type="hidden" value="'.$customRow->field_type .'" name="field['.$i .'][field_type]" />
							<input type="hidden" value="'.$customRow->virtuemart_custom_id.'" name="field['.$i .'][virtuemart_custom_id]" />
							<input type="hidden" value="'.$customRow->admin_only.'" checked="checked" name="admin_only" />
							<div class="trash"></div></div>';

					} elseif ($customRow->field_type == 'R') {

						$tables['products'] .=  '<div class="vm_thumb_image">
							<span>'.$customRow->display.'</span>
							<input type="hidden" value="'.$customRow->field_type .'" name="field['.$i .'][field_type]" />
							<input type="hidden" value="'.$customRow->virtuemart_custom_id.'" name="field['.$i .'][virtuemart_custom_id]" />
							<input type="hidden" value="'.$customRow->admin_only.'" checked="checked" name="admin_only" />
							<div class="trash"></div></div>';

					} elseif ($customRow->field_type == 'G') {
						// no display (group of) child , handled by plugin;
					} elseif ($customRow->field_type == 'C' or $customRow->field_type == 'E'){

						$tables['childs'] .=  '<div class="removable"><div>'.JText::_($customRow->custom_title).'</div>
							<span>'.$customRow->display.$customRow->custom_tip.'</span>
							<span>'.JText::_($this->fieldTypes[$customRow->field_type]).'</span>
							<input type="hidden" value="'.$customRow->field_type .'" name="field['.$i .'][field_type]" />
							<input type="hidden" value="'.$customRow->virtuemart_custom_id.'" name="field['.$i .'][virtuemart_custom_id]" />
							<input type="hidden" value="'.$customRow->admin_only.'" name="admin_only" />
							<span class="vmicon vmicon-16-'.$cartIcone.'"></span>
							<span class="trash"></span></div>';
					} else {
						$tables['fields'] .= '<tr class="removable">
							<td>'.JText::_($customRow->custom_title).'</td>
							<td>'.$customRow->custom_tip.'</td>
							<td>'.$customRow->display.'</td>
							<td>'.JText::_($this->fieldTypes[$customRow->field_type]).'
							<input type="hidden" value="'.$customRow->field_type .'" name="field['.$i .'][field_type]" />
							<input type="hidden" value="'.$customRow->virtuemart_custom_id.'" name="field['.$i .'][virtuemart_custom_id]" />
							<input type="hidden" value="'.$customRow->admin_only.'" checked="checked" name="admin_only" />
							</td>
							<td>
							<span class="vmicon vmicon-16-'.$cartIcone.'"></span>
							</td>
							<td><span class="trash"></span></td>
						 </tr>';
						}

					$i++;
				}
			} 
			
			 $emptyTable = '
				<tr>
					<td colspan="6">'.JText::_( 'COM_VIRTUEMART_CUSTOM_NO_TYPES').'</td>
				<tr>';
			?>
			<fieldset style="background-color:#F9F9F9;">
				<legend><?php echo JText::_('COM_VIRTUEMART_RELATED_CATEGORIES'); ?></legend>
				<?php echo JText::_('COM_VIRTUEMART_CATEGORIES_RELATED_SEARCH'); ?>
				<div class="jsonSuggestResults" style="width: auto;">
					<input type="text" size="40" name="search" id="relatedcategoriesSearch" value="" />
				</div>
				<div id="custom_categories"><?php echo  $tables['categories']; ?></div>
			</fieldset>
			<fieldset style="background-color:#F9F9F9;">
				<legend><?php echo JText::_('COM_VIRTUEMART_RELATED_PRODUCTS'); ?></legend>
				<?php echo JText::_('COM_VIRTUEMART_PRODUCT_RELATED_SEARCH'); ?>
				<div class="jsonSuggestResults" style="width: auto;">
					<input type="text" size="40" name="search" id="relatedproductsSearch" value="" />
				</div>
				<div id="custom_products"><?php echo  $tables['products']; ?></div>
			</fieldset>

			<fieldset style="background-color:#F9F9F9;">
				<legend><?php echo JText::_('COM_VIRTUEMART_CUSTOM_FIELD_TYPE' );?></legend>
				<div><?php echo JText::_('COM_VIRTUEMART_SELECT').'<div class="inline">'.$this->customsList; ?></div>

				<table id="custom_fields" class="adminlist" cellspacing="0" cellpadding="0">
					<thead>
					<tr class="row1">
						<th><?php echo JText::_('COM_VIRTUEMART_TITLE');?></th>
						<th><?php echo JText::_('COM_VIRTUEMART_CUSTOM_TIP');?></th>
						<th><?php echo JText::_('COM_VIRTUEMART_VALUE');?></th>
						<th><?php echo JText::_('COM_VIRTUEMART_CART_PRICE');?></th>
						<th><?php echo JText::_('COM_VIRTUEMART_TYPE');?></th>
						<th><?php echo JText::_('COM_VIRTUEMART_CUSTOM_IS_CART_ATTRIBUTE');?></th>
						<th><?php echo JText::_('COM_VIRTUEMART_DELETE'); ?></th>
					</tr>
					</thead>
					<tbody id="custom_fields">
						<?php 
						if ($tables['fields']) echo $tables['fields'] ;
						else echo $emptyTable;
						?>
					</tbody>
				</table>
			</fieldset>
			<fieldset style="background-color:#F9F9F9;">
				<legend><?php echo JText::_('COM_VIRTUEMART_CUSTOM_EXTENSION'); ?></legend>
				<div id="custom_childs"><?php echo  $tables['childs']; ?></div>
			</fieldset>
		</td>

	</tr>
</table>

<div style="clear:both;"></div>
<div style="display:none;" class="customDelete remove"><span class="vmicon vmicon-16-trash"></span><?php echo JText::_('COM_VIRTUEMART_DELETE'); ?></div>

<script type="text/javascript">
	nextCustom = <?php echo $i ?>;
	// jQuery('#new_stockable_product').click(function() {
		// var Prod = jQuery('#new_stockable');// input[name^="stockable"]').serialize();
		// console.log (Prod);
		// jQuery.getJSON('index.php?option=com_virtuemart&view=product&task=saveJS&token=<?php echo JUtility::getToken(); ?>' ,
			// {
				// product_sku: Prod.find('input[name*="product_sku"]').val(),
				// product_name: Prod.find('input[name*="product_name"]').val(),
				// product_price: Prod.find('input[name*="product_price"]').val(),
				// product_in_stock: Prod.find('input[name*="product_in_stock"]').val(),
				// product_parent_id: <?php echo $this->product->virtuemart_product_id ?>,
				// published: 1,
				// format: "json"
			// },
			// function(data) {
				//jQuery.each(data.msg, function(index, value){
					// jQuery("#new_stockable").append(data.msg);
				//});
			// });
	// });
		    // $("select##customlist").chosen().change(function() {
			          // var str = "";
          // $(this).find("option:selected").each(function () {
                // str += $(this).text() + " ";
              // });

         // console.log(str);//$("#someInput").first().focus();
     // });
	jQuery('select#customlist').chosen().change(function() {
		selected = jQuery(this).find( 'option:selected').val() ;
		jQuery.getJSON('index.php?option=com_virtuemart&view=product&task=getData&format=json&type=fields&id='+selected+'&row='+nextCustom+'&virtuemart_product_id=<?php echo $this->product->virtuemart_product_id; ?>',
		function(data) {
			jQuery.each(data.value, function(index, value){
				jQuery("#custom_"+data.table).append(value);
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
					jQuery.each(data.value, function(index, value){
						jQuery("#custom_"+$type).append(value);
					});
				});
			nextCustom++;

		});

	}
	customautocomplete( 'products' ,<?php echo (int)$this->product->virtuemart_product_id; ?>) 
	customautocomplete( 'categories' ,<?php echo (int)$this->product->virtuemart_category_id; ?>) 
</script>