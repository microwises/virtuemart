<?php
/**
*
* Handle the product variants
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
* @version $Id$
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
if (JRequest::getInt('product_parent_id', 0) == 0 && $this->product->product_parent_id == 0 && count($this->product->attribute_names) > 0) {
	?>
	<table class="adminlist">
		<thead>
		<tr class="row1">
			<th colspan="<?php echo count($this->product->attribute_names)+2; ?>"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_ITEMS_LBL') ?></th>
		</tr>
		<!-- Child products -->
		<tr class="row0">
			<th class="title"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_NAME') ?></th>
			<th class="title"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_SKU') ?></th>
			<?php
				foreach ($this->product->attribute_names as $key => $attribute_title) {
					?>
					<th class="title"><?php echo $attribute_title; ?></th>
					<?php
				}
			?> 
		</tr>
		</thead>
		<tbody>
		<?php
			foreach ($this->product->child_products as $product_sku => $child_product) {
				?>
				<tr class="row0">
					<td><?php
						$link = 'index.php?option='.$option.'&view=product&task=edit&product_id='.$child_product->product_id.'&product_parent_id='.$this->product->product_id;
						echo JHTML::_('link', JRoute::_($link), $child_product->product_name);
						?>
					</td>
					<td><?php echo $product_sku; ?> </td>
					<?php
						foreach ($this->product->attribute_names as $key => $attribute_title) {
							echo '<td>'.$child_product->$attribute_title.'</td>';
						}
					?>
				</tr>
			<?php } ?>
			</tbody>
	</table>
<?php
} elseif (JRequest::getInt('product_parent_id', 0) > 0 || $this->product->product_parent_id > 0) {?>
	<table class="adminform">
		<tr class="row0">
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr class="row1">
			<td colspan="2"><strong><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_ITEM_ATTRIBUTES_LBL') ?></strong></td>
		</tr>
		<?php foreach ($this->product->attribute_names as $key => $attribute_title) { ?>
				<tr>
					<td width="21%" height="22" >
						<div style="text-align:right;font-weight:bold;"><?php
						echo $attribute_title . ":"?></div>
					</td>
					<td width="79%" >
						<input type="text" class="inputbox" name="attribute_<?php echo $this->product->attribute_values[$attribute_title]['attribute_id'];?>" size="32" maxlength="255" value="<?php echo $this->product->attribute_values[$attribute_title]['attribute_value']; ?>" />
					</td>
				</tr>
			<?php } ?>
	</table>
	<?php
}
	
	?>
	<table class="adminform">
		<tr class="row0">
			<td align="right" width="21%" valign="top"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_ATTRIBUTE_LIST') ?>:</div></td>
			<td width="79%" id="attribute_container">
			<?php
			// ATTRIBUTE EXTENSION by Tobias (eaxs)
			// ps_product_attribute::loadAttributeExtension($db->sf("attribute"));
			echo '<input type="hidden" name="js_lbl_title" value="' . JText::_('COM_VIRTUEMART_PRODUCT_FORM_TITLE') . '" />
		      <input type="hidden" name="js_lbl_property" value="' . JText::_('COM_VIRTUEMART_PRODUCT_FORM_PROPERTY') . '" />
		      <input type="hidden" name="js_lbl_property_new" value="' . JText::_('COM_VIRTUEMART_PRODUCT_FORM_PROPERTY_NEW') . '" />
		      <input type="hidden" name="js_lbl_attribute_new" value="' . JText::_('COM_VIRTUEMART_PRODUCT_FORM_ATTRIBUTE_NEW') . '" />
		      <input type="hidden" name="js_lbl_attribute_delete" value="' . JText::_('COM_VIRTUEMART_PRODUCT_FORM_ATTRIBUTE_DELETE') . '" />
		      <input type="hidden" name="js_lbl_price" value="' . JText::_('COM_VIRTUEMART_CART_PRICE') . '" />' ;
		
		      
			// product has no attributes
			if (!$this->product->attribute) { ?>
				<table id="attributeX_table_0" cellpadding="0" cellspacing="0"
					border="0" class="adminform" width="30%">
					<tbody style="width: 30%;">
						<tr>
							<td width="5%"><?php
							echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_TITLE') ;
							?></td>
							<td align="left" colspan="2"><input type="text"
								name="attributeX[0][name]" value="" size="60" /></td>
							<td colspan="3" align="left">
								<a href="javascript: newAttribute(1)"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_ATTRIBUTE_NEW') ; ?></a> | <a href="javascript: newProperty(0)"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PROPERTY_NEW') ; ?></a>
							</td>
						</tr>
						<tr id="attributeX_tr_0_0">
							<td width="5%">&nbsp;</td>
							<td width="10%" align="left"><?php
							echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PROPERTY') ;
							?></td>
							<td align="left" width="20%">
								<input type="text" name="attributeX[0][value][]" value="" size="40" />
							</td>
							<td align="left" width="5%">
								<?php echo JText::_('COM_VIRTUEMART_PRODUCT_PRICE_TITLE') ; ?>
							</td>
							<td align="left" width="60%">
								<input type="text" name="attributeX[0][price][]" size="10" value="" />
							</td>
						</tr>
					</tbody>
				</table>
			<?php } 
			else {
				// split multiple attributes
				$dropdownlists = explode( ';', $this->product->attribute ) ;
				
				for( $i = 0, $n = count( $dropdownlists ) ; $i < $n ; $i ++ ) {
					$dropdownlist = $dropdownlists[$i] ;
					$options = explode( ',', $dropdownlist ) ;
					$dropdown_name = $options[0] ;
					
					// display each attribute in the first loop...
					?>
					<table id="attributeX_table_<?php echo $i ;?>" cellpadding="0" cellspacing="0" border="0" class="adminform" width="30%">
						<tbody style="width: 30%">
							<tr>
								<td width="5%">
									<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_TITLE') ; ?>
								</td>
								<td align="left" colspan="2">
									<input type="text" name="attributeX[<?php echo $i ; ?>][name]" value="<?php echo $dropdown_name ; ?>" size="60" />
								</td>
								<td colspan="3" align="left">
									<a href="javascript:newAttribute(<?php echo ($i + 1) ; ?>)"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_ATTRIBUTE_NEW') ; ?></a> | 
									<?php
										if( $i != 0 ) {
											?><a href="javascript:deleteAttribute(<?php echo ($i) ; ?>)"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_ATTRIBUTE_DELETE') ; ?></a> | 
										<?php }	?>
										<a href="javascript:newProperty(<?php echo ($i) ; ?>)"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PROPERTY_NEW') ; ?></a>
								</td>
							</tr>
							<?php
							// ... and the properties and prices in the second
							for( $i2 = 1, $n2 = count( $options ) ; $i2 < $n2 ; $i2 ++ ) {
								$value = $options[$i2] ;
						
								if( explode( '[', $value ) ) {
									$value_price = explode( '[', $value ) ;
									?>
									<tr id="attributeX_tr_<?php
									echo $i . "_" . $i2 ;
								?>">
								<td width="5%">&nbsp;</td>
								<td width="10%" align="left"><?php
										echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PROPERTY') ;
										?></td>
								<td align="left" width="20%"><input type="text"
									name="attributeX[<?php
										echo $i ;
										?>][value][]"
									value="<?php
										echo $value_price[0] ;
										?>" size="40" /></td>
								<td align="left" width="5%"><?php
										echo JText::_('COM_VIRTUEMART_CART_PRICE') ;
										?></td>
								<td align="left" width="60%"><input type="text"
									name="attributeX[<?php
										echo $i ;
										?>][price][]" size="5"
									value="<?php
										echo str_replace( ']', '', @$value_price[1] ) ;
										?>" /><a
									href="javascript:deleteProperty(<?php
										echo ($i) ;
										?>,'<?php
										echo $i . "_" . $i2 ;
								?>');">X</a></td>
								</tr>
						  <?php } 
						  else { ?>
						  	  <tr id="attributeX_tr_<?php
										echo $i . "_" . $i2 ;
										?>">
								<td width="5%">&nbsp;</td>
								<td width="10%" align="left"><?php
										echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PROPERTY') ;
										?></td>
								<td align="left" width="20%"><input type="text"
									name="attributeX[<?php
										echo $i ;
										?>][value][]"
									value="<?php
										echo $value ;
										?>" size="40" /></td>
								<td align="left" width="5%"><?php
										echo JText::_('COM_VIRTUEMART_CART_PRICE') ;
										?></td>
								<td align="left" width="60%"><input type="text"
									name="attributeX[<?php
										echo $i ;
										?>][price][]" size="10" /><a
									href="javascript:deleteProperty(<?php
										echo ($i) ;
										?>,'<?php
										echo $i . "_" . $i2 ;
										?>');">X</a></td>
							</tr>
						<?php }
						} ?>
						</tbody>
				</table>
		<?php
			}
			} ?>
		</tr>
		<tr class="row0">
			<td>&nbsp;</td>
			<td><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_ATTRIBUTE_LIST_EXAMPLES') ?></td>
		</tr>
		<tr class="row0">
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr class="row1">
			<td align="right" width="21%" valign="top"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_CUSTOM_ATTRIBUTE_LIST') ?>:</div></td>
			<td width="79%" >
				<input class="inputbox" type="text" name="custom_attribute" value="<?php echo $this->product->custom_attribute; ?>" size="100" />
		</tr>
		<tr class="row1">
			<td>&nbsp;</td>
			<td><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_CUSTOM_ATTRIBUTE_LIST_EXAMPLES') ?></td>
		</tr>
</table>
<script type="text/javascript">
function newAttribute()
{
	var d = document;
	
	// get field labels
	var lbl_attribute_new = d.adminForm.js_lbl_attribute_new.value;
	var lbl_attribute_del = d.adminForm.js_lbl_attribute_delete.value;
	var lbl_property_new  = d.adminForm.js_lbl_property_new.value;
	var lbl_price         = d.adminForm.js_lbl_price.value;
	var lbl_property      = d.adminForm.js_lbl_property.value;
	var lbl_title         = d.adminForm.js_lbl_title.value;
	
	
	var container = document.getElementById('attribute_container');
	var next_inc  = container.getElementsByTagName('table').length + 1;
	var toolbar   = "<a href='javascript:newAttribute();'>"+lbl_attribute_new+"</a> | <a href='javascript:deleteAttribute("+next_inc+")'>"+lbl_attribute_del+"</a> | <a href='javascript:newProperty("+next_inc+")'>"+lbl_property_new+"</a>";
	
	var table = d.createElement('table');
	    table.id  = 'attributeX_table_'+next_inc;
	    table.className = 'adminform';
	
	var tbody = d.createElement("tbody");
	var tr    = d.createElement('tr');
	var tr2   = d.createElement('tr');
	    tr2.id    = "attributeX_tr_"+next_inc+"_0";
	

	var td_01 = d.createElement('td');
	    td_01.style.width = '5%';
	    td_01.innerHTML = lbl_title;
	
	var td_02 = d.createElement('td');
	    td_02.colSpan = '2';
	    td_02.align = 'left';
	    td_02.innerHTML = '<input type="text" name="attributeX['+next_inc+'][name]" value="" size="60"/>';
	
	var td_03 = d.createElement('td');
	    td_03.colSpan = '3';
	    td_03.align = 'left';
	    td_03.innerHTML = toolbar;
	
	var td_04 = d.createElement('td');
	    td_04.style.width = '5%';
	    td_04.innerHTML = '&nbsp;';
	
	var td_05 = d.createElement('td');
	    td_05.style.width = '10%';
	    td_05.align = 'left';
	    td_05.innerHTML = lbl_property;
	
	var td_06 = d.createElement('td');
	    td_06.style.width = '20%';
	    td_06.align = 'left';
	    td_06.innerHTML = "<input type='text' name='attributeX["+next_inc+"][value][]' value='' size='40'/>";
	
	var td_07 = d.createElement('td');
	    td_07.style.width = '5%';
	    td_07.align = 'left';
	    td_07.innerHTML = lbl_price;
	
	var td_08 = d.createElement('td');
	    td_08.style.width = '60%';
	    td_08.align = 'left';
	    td_08.innerHTML = "<input type='text' name='attributeX["+next_inc+"][price][]' size='10' value=''/><a href='javascript:deleteProperty("+next_inc+",\""+next_inc+"_0\");'>X</a>";
	
	
	table.appendChild(tbody);
	   tbody.appendChild(tr);
	      tr.appendChild(td_01);
	      tr.appendChild(td_02);
	      tr.appendChild(td_03);
	   tbody.appendChild(tr2);  
	      tr2.appendChild(td_04); 
	      tr2.appendChild(td_05);
	      tr2.appendChild(td_06);
	      tr2.appendChild(td_07);
	      tr2.appendChild(td_08);
	
	container.appendChild(table);
}


function deleteAttribute(attribute_id)
{
	var container = document.getElementById('attribute_container');
	
	var table = document.getElementById("attributeX_table_"+attribute_id);
	
	container.removeChild(table);
}


function newProperty(attribute_id)
{
	var d = document;
	
	// get field labels
    var lbl_property      = d.adminForm.js_lbl_property.value;
    var lbl_price         = d.adminForm.js_lbl_price.value;

    
	var table = document.getElementById("attributeX_table_"+attribute_id);
	var tbody = table.getElementsByTagName('tbody')[0];
	var tr_id = table.getElementsByTagName('tr').length + 1;
	
	// create new HTML elements
	var tr = d.createElement('tr');
	    tr.id = "attributeX_tr_"+attribute_id+"_"+tr_id;
	
	var td_01 = d.createElement('td');
	    td_01.style.width = '5%';
	    td_01.innerHTML = '&nbsp;';
	
	var td_02 = d.createElement('td');
	    td_02.style.width = '10%';
	    td_02.align = 'left';
	    td_02.innerHTML = lbl_property;
	
	var td_03 = d.createElement('td');
	    td_03.style.width = '20%';
	    td_03.align = 'left';
	    td_03.innerHTML = "<input type='text' name='attributeX["+attribute_id+"][value][]' value='' size='40'/>";
	
	var td_04 = d.createElement('td');
	    td_04.style.width = '5%';
	    td_04.align = 'left';
	    td_04.innerHTML = lbl_price;
	
	var td_05 = d.createElement('td');
	    td_05.style.width = '60%';
	    td_05.align = 'left';
	    td_05.innerHTML = "<input type='text' name='attributeX["+attribute_id+"][price][]' size='10' value=''/><a href='javascript:deleteProperty("+attribute_id+",\""+attribute_id+"_"+tr_id+"\");'>X</a>";
	
	// append new elements    
	tbody.appendChild(tr);
	   tr.appendChild(td_01);
	   tr.appendChild(td_02);
	   tr.appendChild(td_03);
	   tr.appendChild(td_04);
	   tr.appendChild(td_05);
}


function deleteProperty(attribute_id, property_id)
{
	var d     = document;
	var table = document.getElementById("attributeX_table_"+attribute_id);
	var tbody = table.getElementsByTagName('tbody')[0];
	var tr    = d.getElementById("attributeX_tr_"+property_id);
	
	tbody.removeChild(tr);
}
</script>
