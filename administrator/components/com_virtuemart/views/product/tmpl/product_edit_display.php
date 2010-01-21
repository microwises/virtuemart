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
<table class="adminform">
	<tr>
		<td align="left" colspan="2"></td>
	</tr>
	<tr class="row1">
		<td width="21%"  style="vertical-align: middle;"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_DISPLAY_USE_PARENT_LABEL'); ?></div></td>
		<td width="79%" style="vertical-align: middle;" colspan="2">
			<input type="checkbox" class="checkbox"  id="display_use_parent" name="display_use_parent" value="Y" <?php
				if ($this->product->display_use_parent == "Y" && !$this->product->display_use_parent_disabled) echo "checked=\"checked\"";
				else if($this->product->display_use_parent_disabled) echo ' disabled="disabled" '; ?>  
			/>
			<label for="display_use_parent" ><?php echo JText::_('VM_DISPLAY_USE_PARENT'); ?></label><br/>
		</td>
	</tr>
	<tr class="row0">
		<td width="21%"  style="vertical-align: top;"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_DISPLAY_LIST_TYPE'); ?></div></td>
		<td width="20%"  style="vertical-align: top;"> <?php
			echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"product_list_check\" name=\"product_list\" value=\"Y\" onclick=\"javascript: toggleProductList( this.checked);\" ";
			if ($this->product->product_list =="Y" || $this->product->product_list =="YM" ) {
				echo "checked=\"checked\" ";
			}
			if($this->product->product_parent_id !=0) {
				echo ' disabled="disabled" ';
			}
			echo '/> <label for="product_list_check">'.JText::_('VM_DISPLAY_USE_LIST_BOX').'</label>';
			?> <br />
			<?php
			echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"display_desc\" name=\"display_desc\" value=\"Y\" ";
			if ($this->product->display_desc) {
				echo 'checked="checked" ';
			}
			echo '/> <label for="display_desc">'.JText::_('VM_DISPLAY_CHILD_DESCRIPTION').'</label><br />
				<input type="inputbox" style="vertical-align: middle;" class="inputbox" size="8" id="desc_width" name="desc_width" value="'.$this->product->desc_width.'" />';
			echo JText::_('VM_DISPLAY_DESC_WIDTH'); ?>
			<br />
			<?php
			echo "<input type=\"inputbox\" style=\"vertical-align: middle;\" class=\"inputbox\" size=\"8\" id=\"attrib_width\" name=\"attrib_width\" value=\"".$this->product->attrib_width."\"  ";
			echo "/> ".JText::_('VM_DISPLAY_ATTRIB_WIDTH'); ?>
			<br />
			<?php
			echo JText::_('VM_DISPLAY_CHILD_SUFFIX')."<br /><input type=\"inputbox\" style=\"vertical-align: middle;\" class=\"inputbox\" size=\"20\" id=\"child_class_sfx\" name=\"child_class_sfx\" value=\"".$this->product->child_class_sfx."\"  ";
			echo "/> "; ?>
			<br />
		</td>
		<td width="20%" >
			<fieldset>
				<legend><?php echo JText::_('VM_DISPLAY_LIST_STYLE'); ?></legend>
				<input type="radio" class="radio" style="vertical-align: middle;" id="list_style0" name="list_style" value="one"
					<?php if ($this->product->product_list == "Y") echo "checked=\"checked\"";
					if($this->product->product_parent_id !=0 || $this->product->product_list =="" || $this->product->product_list =="N") {
						echo ' disabled="disabled" ';
					}
					?>
				/>
				<label for="list_style0" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_ONE'); ?></label><br/>
				<input type="radio" class="radio" style="vertical-align: middle;" id="list_style1" name="list_style" value="many"
					<?php
					if ($this->product->product_list == "YM") echo "checked=\"checked\"";
					if($this->product->product_parent_id !=0 || $this->product->product_list =="" || $this->product->product_list =="N") {
						echo ' disabled="disabled" ';
					}
					?>
				/>
				<label for="list_style1" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_MANY') ?> </label><br />
				<?php if ($this->product->display_header =="Y" && ($this->product->product_list =="Y" || $this->product->product_list =="YM" )) {
					echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"display_headers\" name=\"display_headers\" value=\"Y\" checked=\"checked\" ";
				}
				else {
					echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"display_headers\" name=\"display_headers\" value=\"Y\" ";
				}
				if ($this->product->product_list =="Y"  || $this->product->product_list =="YM" ) {
					echo " /> "; 
				}
				else {
					echo ' disabled=true /> ';
				}
				echo JText::_('VM_DISPLAY_TABLE_HEADER');
				?><br />
				<?php if ($this->product->product_list_child =="Y" && ($this->product->product_list =="Y"  || $this->product->product_list =="YM" )) {
					echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"product_list_child\" name=\"product_list_child\" value=\"Y\" checked=\"checked\" ";
				}
				else {
					echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"product_list_child\" name=\"product_list_child\" value=\"Y\" ";
				}
				if ($this->product->product_list =="Y"  || $this->product->product_list =="YM" ) {
					echo " /> "; 
				}
				else {
					echo ' disabled=true /> ';
				}
				echo JText::_('VM_DISPLAY_LINK_TO_CHILD')."<br />";
				?>
				<?php if ($this->product->product_list_type =="Y" && ($this->product->product_list =="Y"  || $this->product->product_list =="YM" )) {
					echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"product_list_type\" name=\"product_list_type\" value=\"Y\" checked=\"checked\" ";
				}
				else {
					echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"product_list_type\" name=\"product_list_type\" value=\"Y\" ";
				}
				if ($this->product->product_list =="Y"  || $this->product->product_list =="YM" ) {
					echo " /> "; 
				}
				else {
					echo " disabled=true /> ";
				}
				echo JText::_('VM_DISPLAY_INCLUDE_PRODUCT_TYPE');
				?>
			</fieldset>
		</td>
		<td width="39%">
		</td>
	</tr>
	<tr class="row1">
		<td width="21%"  style="vertical-align: top;"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_DISPLAY_CHILD_ORDER_BY'); ?></div></td>
		<td width="79%" colspan="2">
			<?php 
			$options = array();
			$options[] = JHTML::_('select.option', '`#__{vm}_product`.`product_sku`', JText::_('VM_CART_SKU'));
			$options[] = JHTML::_('select.option', '`#__{vm}_product`.`product_id`', JText::_('VM_CHILD_PRODUCT_ID'));
			$options[] = JHTML::_('select.option', '`#__{vm}_product`.`product_name`', JText::_('VM_PRODUCT_NAME_TITLE'));
			echo JHTML::_('select.genericlist', $options, 'child_order_by', 'id="child_order_by"', 'value', 'text', $this->product->child_order_by);
			?>
			<label for="child_order_by" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_CHILD_ORDER_DESC'); ?></label><br/>
		</td>
	</tr>
	<tr class="row0">
		<td width="21%"  style="vertical-align: top;"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_EXTRA_PRODUCT_ID'); ?></div></td>
		<td width="79%" colspan="2"><input type="inputbox" class="inputbox" size="35" id="included_product_id" name="included_product_id" value="<?php echo $this->product->child_option_ids; ?>" />
			<label for="included_product_id" style="vertical-align: middle;"><?php echo JText::_('VM_INCLUDED_PRODUCT_ID'); ?></label><br/>
		</td>
	</tr>
</table>
<table class="adminform">
	<tr class="row0">
		<td width="21%" style="vertical-align: top;">
		<fieldset>
			<legend><?php echo JText::_('VM_DISPLAY_QUANTITY_LABEL') ?></legend>
			<input type="radio" class="radio" style="vertical-align: middle;" id="quantity_box0" name="quantity_box" value="none" <?php
				if ($this->product->display_type == "none") echo "checked=\"checked\""; ?>  
			/>
			<label for="quantity_box0" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_NORMAL'); ?></label><br/>
			<input type="radio" class="radio" style="vertical-align: middle;" id="quantity_box1" name="quantity_box" value="hide" <?php
				if ($this->product->display_type == "hide") echo "checked=\"checked\""; ?> 
			/>
			<label for="quantity_box1" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_HIDE') ?> </label><br />
			<input type="radio" class="radio" style="vertical-align: middle;" id="quantity_box2" name="quantity_box" value="drop" <?php
				if ($this->product->display_type == "drop") echo "checked=\"checked\""; ?> 
			/>
			<label for="quantity_box2" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_DROPDOWN') ?> </label><br />
			<input type="radio" class="radio" style="vertical-align: middle;" id="quantity_box3" name="quantity_box" value="check" <?php
				if ($this->product->display_type == "check") echo "checked=\"checked\""; ?>
			/>
			<label for="quantity_box3" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_CHECKBOX') ?> </label><br />
			<input type="radio" class="radio" style="vertical-align: middle;" id="quantity_box4" name="quantity_box" value="radio" <?php
				if ($this->product->display_type == "radio") echo 'checked="checked"';
				if($this->product->product_parent_id !=0) echo ' disabled="true"'; ?>  
			/>
			<label for="quantity_box4" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_RADIOBOX') ?> </label><br />
		</td>
		<td width="20%" style="vertical-align: top;">
			<fieldset>
				<legend><?php echo JText::_('VM_DISPLAY_QUANTITY_DROPDOWN_LABEL') ?></legend>
				<input type="text" class="inputbox" style="vertical-align: middle;" id="quantity_start" name="quantity_start" size="4" value="<?php echo $this->product->quantity_start; ?>" />
				<label for="quantity_start" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_START') ?> </label><br />
				<input type="text" class="inputbox" style="vertical-align: middle;" id="quantity_end" name="quantity_end" size="4" value="<?php echo $this->product->quantity_end; ?>" />
				<label for="quantity_end" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_END') ?> </label><br />
				<input type="text" class="inputbox" style="vertical-align: middle;" id="quantity_step" name="quantity_step" size="4" value="<?php echo $this->product->quantity_step; ?>" />
				<label for="quantity_step" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_STEP') ?> </label><br />
			</fieldset>
		</td>
		<td width="39%">
		</td>
	</tr>
</table>
<script type="text/javascript">
function toggleProductList( enable ) {
	if(enable) {		
    	document.getElementById('list_style0').disabled = false;
       document.getElementById('list_style0').checked = true;
    	document.getElementById('list_style1').disabled = false;
       document.getElementById('display_headers').disabled = false;
    	document.getElementById('product_list_child').disabled = false;
       document.getElementById('product_list_type').disabled = false;
	}
    else {
    	document.getElementById('list_style0').disabled = true;
    	document.getElementById('list_style1').disabled = true;
       document.getElementById('display_headers').disabled = true;
    	document.getElementById('product_list_child').disabled = true;
       document.getElementById('product_list_type').disabled = true;
       document.getElementById('display_headers').checked = false;
    	document.getElementById('product_list_child').checked = false;
       document.getElementById('product_list_type').checked = false;
	}
}
</script>
