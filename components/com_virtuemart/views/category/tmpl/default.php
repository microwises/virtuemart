<?php
/**
*
* Show the products in a category
*
* @package	VirtueMart
* @subpackage 
* @author RolandD
* @author Max Milbers
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

/* Show child categories */
if ($this->category->haschildren) {
	?>
	<table width="100%" cellspacing="0" cellpadding="0">
	<?php
	$iCol = 1;
	$categories_per_row = 2;
	$cellwidth = 25;
	foreach ($this->category->children as $category ) {
		if ($iCol == 1) { // this is an indicator wether a row needs to be opened or not
			echo "<tr>\n";
		}
		?>
		<td align="center" width="<?php echo $cellwidth ?>%" >
			<br />
			<?php
				$caturl = JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$category->category_id);
				$cattext = ''; 
				if ($category->category_thumb_image) {
					$cattext .= ImageHelper::generateImageHtml('resized/'.$category->category_thumb_image, VmConfig::get('media_category_path'), 'alt="'.$category->category_name.'"', false);
					$cattext .= "<br /><br/>\n";
				}
				$cattext .= $category->category_name;
				$cattext .= ' ('.$category->number_of_products.')';
				echo JHTML::link($caturl, $cattext);
				?>
			 <br/>
		</td>
		
		
		<?php
		// Do we need to close the current row now?
		if ($iCol == $categories_per_row) { // If the number of products per row has been reached
			echo "</tr>\n";
			$iCol = 1;
		}
		else {
			$iCol++;
		}
	}
	// Do we need a final closing row tag?
	if ($iCol != 1) {
		echo "</tr>\n";
	}
	?>
	</table>
<?php
}

/* Process all products in the category*/
foreach ($this->products as $product) {
	?>
	<div class="browseProductContainer">
		<h3 class="browseProductTitle">
			<?php echo JHTML::link($product->link, $product->product_name); ?>
		</h3>
	
	<div class="browsePriceContainer">
<?php	if (VmConfig::get('show_prices') == '1') {
			if( $product->product_unit && VmConfig::get('vm_price_show_packaging_pricelabel')) {
				echo "<strong>". JText::_('VM_CART_PRICE_PER_UNIT').' ('.$product->product_unit."):</strong>";
			} else echo "<strong>". JText::_('VM_CART_PRICE'). ": </strong>";

			//todo add config settings
			if( Permissions::getInstance()->check('admin')){
				echo shopFunctionsF::createPriceDiv('basePrice','VM_PRODUCT_BASEPRICE',$product->prices);
				echo shopFunctionsF::createPriceDiv('basePriceVariant','VM_PRODUCT_BASEPRICE_VARIANT',$this->product->prices);
			}
			echo shopFunctionsF::createPriceDiv('variantModification','VM_PRODUCT_VARIANT_MOD',$product->prices);
			echo shopFunctionsF::createPriceDiv('basePriceWithTax','VM_PRODUCT_BASEPRICE_WITHTAX',$product->prices);
			echo shopFunctionsF::createPriceDiv('discountedPriceWithoutTax','VM_PRODUCT_DISCOUNTED_PRICE',$product->prices);
			echo shopFunctionsF::createPriceDiv('salesPriceWithDiscount','VM_PRODUCT_SALESPRICE_WITH_DISCOUNT',$product->prices);
			echo shopFunctionsF::createPriceDiv('salesPrice','VM_PRODUCT_SALESPRICE',$product->prices);
			echo shopFunctionsF::createPriceDiv('priceWithoutTax','VM_PRODUCT_SALESPRICE_WITHOUT_TAX',$product->prices);
			echo shopFunctionsF::createPriceDiv('discountAmount','VM_PRODUCT_DISCOUNT_AMOUNT',$product->prices);
			echo shopFunctionsF::createPriceDiv('taxAmount','VM_PRODUCT_TAX_AMOUNT',$product->prices);	
		} ?>
	</div>
	
	<div class="browseProductImageContainer">
		<?php 
			/** @todo make image popup */	
			echo ImageHelper::generateImageHtml($product->product_thumb_image, VmConfig::get('media_product_path'), 'class="browseProductImage" border="0" title="'.$product->product_name.'" alt="'.$product->product_name .'"'); 
		?>
	</div>
	
	<!-- The "Average Customer Rating" Part -->
	<?php if (VmConfig::get('pshop_allow_reviews') == 1) { ?>
		<div class="browseRatingContainer">
			<span class="contentpagetitle"><?php echo JText::_('VM_CUSTOMER_RATING') ?>:</span>
			<br />
			<?php
			$img_url = JURI::root().VmConfig::get('media_general_path').'/reviews/'.$product->votes->rating.'.gif';
			echo JHTML::image($img_url, $product->votes->rating.' '.JText::_('REVIEW_STARS'));
			echo JText::_('VM_TOTAL_VOTES').": ". $product->votes->allvotes; ?>
		</div>
	<?php } ?>
	<div class="browseProductDescription">
		<?php echo $product->product_s_desc.'<br />';
			echo JHTML::link($product->link, JText::_('PRODUCT_DETAILS'), array('title' => $product->product_name));
		?>
	</div>
	<br />
	<div >
		<?php 
		echo JText::_('VM_STOCK_LEVEL_DISPLAY_DETAIL_LABEL').' '.JHTML::image(JURI::root().VmConfig::get('media_general_path').'/'.$product->stock->stock_level.'.gif', $product->stock->stock_tip, array('title' => $product->stock->stock_tip));
		?>
	</div>
	<?php if (VmConfig::get('use_as_catalogue') != '1') { ?>
		<form  method="post" id="addtocartproduct<?php echo $product->product_id ?>">
		<div style="text-align: center;">
			<?php
				$variantExist=false;
				/* Show the variants */
				foreach ($product->variants as $variant_name => $variant) {
					
					$options = array();
					foreach ($variant as $name => $price) {
						if (!empty($price) && $price['basePrice'] > 0) $name .= ' ('.$price['basePrice'].')';
						$variantExist=true;
						$options[] = JHTML::_('select.option', $name, $name);
					}
					if (!empty($options)) echo $variant_name.' '.JHTML::_('select.genericlist', $options, $product->product_id.$variant_name).'<br />';
//																				genericlist   ,$arr     , $name               , $attribs = null, $key = 'value', $text = 'text', $selected = NULL, $idtag = false, $translate = false )
//					if (!empty($options)) echo $variant_name.' '.JHTML::_('select.genericlist', $options, $variant_name, null,'value','text',NULL,$product->product_id).'<br />';
				}
				?>
				<br style="clear: both;" />
				<?php
				/* Show the custom attributes */
				foreach($product->customvariants as $ckey => $customvariant) { 		
					?>
					<div class="vmAttribChildDetail" style="float: left;width:30%;text-align:right;margin:3px;">
					<label for="<?php echo $customvariant ?>_field"><?php echo $customvariant ?>
					</label>:
					</div>
					<div class="vmAttribChildDetail" style="float:left;width:60%;margin:3px;">
					<input type="text" class="inputboxattrib" id="<?php echo $customvariant ?>_field" size="30" name="<?php echo $product->product_id.$customvariant; ?>" />
					</div>
					<br style="clear: both;" />
				<?php
				}
				
				/* Display the quantity box */
				?>
				<label for="quantity<?php echo $product->product_id;?>" class="quantity_box"><?php echo JText::_('VM_CART_QUANTITY'); ?>: </label>
				<input type="text" class="inputboxquantity" size="4" id="quantity<?php echo $product->product_id;?>" name="quantity[]" value="1" />
				<input type="button" class="quantity_box_button quantity_box_button_up" onClick="add(<?php echo $product->product_id;?>); return false;" />
				<input type="button" class="quantity_box_button quantity_box_button_down" onClick="minus(<?php echo $product->product_id;?>); return false;" />
				<?php
				
				/* Add the button */
				$button_lbl = JText::_('VM_CART_ADD_TO');
				$button_cls = 'addtocart_button_module';
				if (VmConfig::get('check_stock') == '1' && !$product->product_in_stock) {
					$button_lbl = JText::_('VM_CART_NOTIFY');
					$button_cls = 'notify_button';
				}
				?>
				<input type="submit" id="<?php echo $product->product_id;?>" name="addtocart" class="<?php echo $button_cls ?>" value="<?php echo $button_lbl ?>" title="<?php echo $button_lbl ?>" />
				
				<?php  if($variantExist){ ?>
					<input id="<?php echo $product->product_id;?>" type="submit" name="setproducttype" class="setproducttype"  value="<?php echo JText::_('VM_SET_PRODUCT_TYPE'); ?>" title="<?php echo JText::_('VM_SET_PRODUCT_TYPE'); ?>" />
				<?php } ?>

				<input type="hidden" name="product_id[]" value="<?php echo $product->product_id ?>" />
				<?php /** @todo Handle the manufacturer view */ ?> 
				<input type="hidden" name="manufacturer_id" value="<?php echo $product->manufacturer_id ?>" />
				<input type="hidden" name="category_id[]" value="<?php echo $product->category_id ?>" />
			</div>
		</form>
	<?php } ?>
	
	</div>
<?php } ?>
