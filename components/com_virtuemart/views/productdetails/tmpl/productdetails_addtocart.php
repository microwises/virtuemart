<?php
/**
*
* Show an add to curt button on the product details page
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
defined('_JEXEC') or die('Restricted access');

/* Show the variants */
foreach ($this->product->variants as $variant_name => $variant) {
	$options = array();
	foreach ($variant as $name => $price) {
		if (!empty($price) && $price['basePrice'] > 0) $name .= ' ('.$price['basePrice'].')';
		$options[] = JHTML::_('select.option', $variant_name.'_'.$name, $name);
	}
	echo $variant_name.JHTML::_('select.genericlist', $options, $variant_name);
}
/* Display the quantity box */
?>
<label for="quantity<?php echo $this->product->product_id;?>" class="quantity_box"><?php echo JText::_('VM_CART_QUANTITY'); ?>': </label>
<input type="text" class="inputboxquantity" size="4" id="quantity<?php echo $this->product->product_id;?>" name="quantity[]" value="1" />
<input type="button" class="quantity_box_button quantity_box_button_up" onclick="var qty_el = document.getElementById('quantity<?php echo $this->product->product_id;?>'); var qty = qty_el.value; if( !isNaN( qty )) qty_el.value++;return false;" />
<input type="button" class="quantity_box_button quantity_box_button_down" onclick="var qty_el = document.getElementById('quantity<?php echo $this->product->product_id;?>'); var qty = qty_el.value; if( !isNaN( qty ) && qty > 0 ) qty_el.value--;return false;" />
<?php

/* Add the button */
$button_lbl = JText::_('VM_CART_ADD_TO');
$button_cls = 'addtocart_button';
if (VmConfig::get('check_stock') == '1' && !$this->product->product_in_stock) {
	$button_lbl = JText::_('VM_CART_NOTIFY');
	$button_cls = 'notify_button';
}
/** @todo Make the add to cart button work, so it puts products in the basket */
?>
<input type="submit" class="<?php echo $button_cls ?>" value="<?php echo $button_lbl ?>" title="<?php echo $button_lbl ?>" />

<?php /** @todo Complete form */ ?>
<!--
<input type="hidden" name="manufacturer_id" value="<?php echo $manufacturer_id ?>" />
<input type="hidden" name="category_id" value="<?php echo $category_id ?>" />
<input type="hidden" name="func" value="cartAdd" />
<input type="hidden" name="option" value="<?php echo $option ?>" />
-->
