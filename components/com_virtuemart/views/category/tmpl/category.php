<?php
/**
*
* Show the products in a category
*
* @package	VirtueMart
* @subpackage 
* @author RolandD
* @todo add pagination
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
/* Process all products */
foreach ($this->products as $product) {
	?>
	<div class="browseProductContainer">
		<h3 class="browseProductTitle">
			<?php echo JHTML::link($product->link, $product->product_name); ?>
		</h3>
	
	<div class="browsePriceContainer">
		<?php echo $product->product_price['salesPrice']; ?>
	</div>
	
	<div class="browseProductImageContainer">
		<?php 
			/** @todo make image popup */	
			echo ImageHelper::displayShopImage($product->product_thumb_image, "product", 'class="browseProductImage" border="0" title="'.$product->product_name.'" alt="'.$product->product_name .'"'); 
		?>
	</div>
	
	<!-- The "Average Customer Rating: xxxxX (2 votes) " Part -->
	<div class="browseRatingContainer">
		<span class="contentpagetitle"><?php echo JText::_('VM_CUSTOMER_RATING') ?>:</span>
		<br />
		<?php
		$img_url = JURI::root().'/components/com_virtuemart/shop_image/reviews/'.$product->votes->rating.'.gif';
		echo JHTML::image($img_url, $product->votes->rating.' '.JText::_('REVIEW_STARS'));
		echo JText::_('VM_TOTAL_VOTES').": ". $product->votes->allvotes; ?>
	</div>
	<div class="browseProductDescription">
		<?php echo $product->product_s_desc.'<br />';
			echo JHTML::link($product->link, JText::_('PRODUCT_DETAILS'), array('title' => $product->product_name));
		?>
	</div>
	<br />
	<div >
		<?php 
		echo JText::_('VM_STOCK_LEVEL_DISPLAY_DETAIL_LABEL').' '.JHTML::image(JURI::root().'components/com_virtuemart/assets/images/'.$product->stock->stock_level.'.gif', $product->stock->stock_tip, array('title' => $product->stock->stock_tip));
		?>
	</div>
	<span class="browseAddToCartContainer">
		<?php 
		
			/* Display the quantity box */
			?>
			<label for="quantity<?php echo $product->product_id;?>" class="quantity_box"><?php echo JText::_('VM_CART_QUANTITY'); ?>': </label>
			<input type="text" class="inputboxquantity" size="4" id="quantity<?php echo $product->product_id;?>" name="quantity[]" value="1" />
			<input type="button" class="quantity_box_button quantity_box_button_up" onclick="var qty_el = document.getElementById('quantity<?php echo $product->product_id;?>'); var qty = qty_el.value; if( !isNaN( qty )) qty_el.value++;return false;" />
			<input type="button" class="quantity_box_button quantity_box_button_down" onclick="var qty_el = document.getElementById('quantity<?php echo $product->product_id;?>'); var qty = qty_el.value; if( !isNaN( qty ) && qty > 0 ) qty_el.value--;return false;" />
			<?php
			
			/* Add the button */
			$button_lbl = JText::_('VM_CART_ADD_TO');
			$button_cls = 'addtocart_button';
			if (VmConfig::get('check_stock') == '1' && !$product->product_in_stock) {
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
	</span>
	
	</div>
<?php } ?>