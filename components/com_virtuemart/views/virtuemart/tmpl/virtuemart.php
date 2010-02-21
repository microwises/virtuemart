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
defined('_JEXEC') or die('Restricted access');

/** @todo Add vendor description */
//echo $this->vendor_store_desc."<br />";
echo "<br /><h4>".JText::_('VM_CATEGORIES')."</h4>";
foreach ($this->categories as $category) {
	echo JHTML::_('link', 
			JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$category->category_id), 
			JHTML::_('image', JURI::root().'components/com_virtuemart/shop_image/category/'.$category->category_thumb_image, $category->category_name).$category->category_name
			);
}

/* Recent products */
if ($this->recentProducts) {
	?>
	<div class="vmRecent">
		<!-- List of recent products -->
		<h3><?php echo JText::_('VM_RECENT_PRODUCTS') ?></h3>
		<ul class="vmRecentDetail">
		<?php 
		foreach ($this->recentProducts as $recent ) { // Loop through all recent products
			/**
			 * Available indexes:
			 * 
			 * $recent["product_name"] => The user ID of the comment author
			 * $recent["category_name"] => The username of the comment author
			 * $recent["product_thumb_image"] => The name of the comment author
			 * $recent["product_url"] => The UNIX timestamp of the comment ("when" it was posted)
			 * $recent["category_url"] => The rating; an integer from 1 - 5
			 * $recent["product_s_desc"] => The comment text
			 * 
			 */
			?>
			<li>
			<a href="<?php echo $recent["product_url"]; ?>" >
			<?php echo $recent["product_name"]; ?></a>&nbsp;(<?php echo JText::_('VM_CATEGORY') ?>:&nbsp;
			<a href="<?php echo $recent["category_url"]; ?>" ><?php echo $recent["category_name"]; ?></a>)
			</li>
			<?php
		}
		?>
		</ul>
	</div>
	<?php
}

/* Show Featured Products */
if (VmConfig::get('showFeatured', 1) && !empty($this->featuredProducts)) {
	$iCol = 1;
	//Number of featured products to show per row
	$featured_per_row = 2;
	//Set the cell width
	$cellwidth = intval( (100 / $featured_per_row) - 2 );
	
	echo "<h3>".JText::_('VM_FEATURED_PRODUCT')."</h3>";
	foreach ($this->featuredProducts as $featured) {
		?>
		<div style="float:left;width:<?php echo $cellwidth ?>%;text-align:top;padding:0px;" >
			<?php
			echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&flypage='.$featured->flypage.'&product_id='.$featured->product_id), $featured->product_name);
			?>
				<h4><?php echo $featured->product_name; ?></h4></a>
				<?php echo JRequest::getVar('currencyDisplay')->getFullValue($featured->product_price['salesPrice']); ?><br />
				<?php if ($featured->product_price['withDiscount']){ ?>
				 <span class="product-Old-Price">
				<?php echo JRequest::getVar('currencyDisplay')->getFullValue($featured->product_price['basePriceWithTax']); ?></span>
				<?php
				}
				if ($featured->product_thumb_image) {
					echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&flypage='.$featured->flypage.'&product_id='.$featured->product_id), 
						ImageHelper::displayShopImage($featured->product_thumb_image, 'product', 'class="browseProductImage" border="0" alt="'.$featured->product_name.'"'));
				?>
				<br /><br/>
				<?php } ?>
				<?php echo $featured->product_s_desc; ?><br />
				
				<?php echo addToCart($featured); ?>
		</div>
		<?php
		// Do we need to close the current row now?
		if ($iCol == $featured_per_row) { // If the number of products per row has been reached
			echo "<br style=\"clear:both;\" />\n";
			$iCol = 1;
		}
		else {
			$iCol++;
		}
	}
	?>
	<br style="clear:both;" />
	<?php
}

/* Show Latest Products */
if (VmConfig::get('showlatest', 1) && !empty($this->latestProducts)) {
	$iCol = 1;
	//Number of featured products to show per row
	$product_per_row = 2;
	//Set the cell width
	$cellwidth = intval( (100 / $product_per_row) - 2 );
	
	echo "<h3>".JText::_('VM_LATEST_PRODUCT')."</h3>";
	foreach ($this->latestProducts as $product ) {
		?>
		<div style="float:left;width:<?php echo $cellwidth ?>%;text-align:top;padding:0px;" >
			<?php
			echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&flypage='.$product->flypage.'&product_id='.$product->product_id), $product->product_name);
			?>
				<h4><?php echo $product->product_name; ?></h4></a>
				<?php echo $product->product_price['salesPrice']; ?><br />
				<?php
				if ($product->product_thumb_image) {
					echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&flypage='.$product->flypage.'&product_id='.$product->product_id), 
						ImageHelper::displayShopImage($product->product_thumb_image, 'product', 'class="browseProductImage" border="0" alt="'.$product->product_name.'"'));
				?>
				<br /><br/>
				<?php } ?>
				<?php echo $product->product_s_desc; ?><br />
				
				<?php echo addToCart($product); ?>
		</div>
		<?php
		// Do we need to close the current row now?
		if ($iCol == $product_per_row) { // If the number of products per row has been reached
			echo "<br style=\"clear:both;\" />\n";
			$iCol = 1;
		}
		else {
			$iCol++;
		}
	}
	?>
	<br style="clear:both;" />
	<?php
}

function addToCart($product) {
	$call_for_pricing = false;
	if ($product->product_price['salesPrice'] == JText::_('CALL_FOR_PRICING')) $call_for_pricing = true;
	$button_lbl = JText::_('VM_CART_ADD_TO');
	$button_cls = 'addtocart_button';
	if (VmConfig::get('check_stock') == '1' 
		&& !$product->product_in_stock 
		&& !$product->haschildren 
		&& !$product->hasattributes
		&& !$call_for_pricing) {
			$button_lbl = JText::_('VM_CART_NOTIFY');
			$button_cls = 'notify_button';
			$notify = true;
	} 
	/* The details and options button lead to the product detail page as customer has to make extra selections before adding to cart */
	else if ($product->haschildren
		|| $product->hasattributes
		|| $call_for_pricing) {
			if($call_for_pricing) {
				$button_lbl = JText::_('DETAILS');
				$button_cls = 'details_button';
			} 
			else {
				$button_lbl = JText::_('OPTIONS');
				$button_cls = 'options_button';
			}
			$notify = true;
	}
	else $notify = false;
	
	/* Make the form */
	?>
	<form action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$product->product_id.'&flypage='.$product->flypage); ?>" method="post" name="addtocart" id="addtocart<?php echo $product->product_id ?>" class="addtocart_form" <?php if( VmConfig::get('useAjaxCartActions', 1) && !$notify ) { echo 'onsubmit="handleAddToCart( this.id );return false;"'; } ?>>
		<?php 
			if (!$notify) { 
				/* Display the quantity box */
				?>
				<label for="quantity<?php echo $product->product_id;?>" class="quantity_box"><?php echo JText::_('VM_CART_QUANTITY'); ?>: </label>
				<input type="text" class="inputboxquantity" size="4" id="quantity<?php echo $product->product_id;?>" name="quantity[]" value="1" />
				<input type="button" class="quantity_box_button quantity_box_button_up" onclick="var qty_el = document.getElementById('quantity<?php echo $product->product_id;?>'); var qty = qty_el.value; if( !isNaN( qty )) qty_el.value++;return false;" />
				<input type="button" class="quantity_box_button quantity_box_button_down" onclick="var qty_el = document.getElementById('quantity<?php echo $product->product_id;?>'); var qty = qty_el.value; if( !isNaN( qty ) && qty > 0 ) qty_el.value--;return false;" />
				<?php
				/** @todo Make the add to cart button work, so it puts products in the basket */
				?>
				
				<?php /** @todo Complete form */ ?>
				<!--
				<input type="hidden" name="manufacturer_id" value="<?php echo $manufacturer_id ?>" />
				<input type="hidden" name="category_id" value="<?php echo $category_id ?>" />
				<input type="hidden" name="func" value="cartAdd" />
				<input type="hidden" name="option" value="<?php echo $option ?>" />
				-->
			<?php } 
		?>
		<input type="submit" class="<?php echo $button_cls ?>" value="<?php echo $button_lbl	?>" title="<?php echo $button_lbl ?>" />
		<input type="hidden" name="category_id" value="<?php echo  JRequest::getInt('category_id'); ?>" />
		<input type="hidden" name="product_id" value="<?php echo $product->product_id; ?>" />
		<input type="hidden" name="prod_id[]" value="<?php echo $product->product_id; ?>" />
		<input type="hidden" name="flypage" value="<?php echo $product->flypage; ?>" />
		<input type="hidden" name="page" value="shop.cart" />
		<input type="hidden" name="func" value="cartadd" />
		<input type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid'); ?>" />
		<input type="hidden" name="option" value="com_virtuemart" />
		<input type="hidden" name="set_price[]" value="" />
		<input type="hidden" name="adjust_price[]" value="" />
		<input type="hidden" name="master_product[]" value="" />
	</form>
	<?php
}
?>