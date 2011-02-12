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
	<div class="category-view">
	<?php
	$iCol = 1;

	// calculation of the categories per row
	$categories_per_row = VmConfig::get('categories_per_row',1);
	$cellwidth = floor( 100 / $categories_per_row);


	foreach ($this->category->children as $category ) {

		if ($iCol == 1) { // this is an indicator wether a row needs to be opened or not ?>
			<div class="category-row">
		<?php }
				?>

		<!-- Category Listing Output -->
		<div class="width<?php echo $cellwidth ?> floatleft center">
			<?php $caturl = JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$category->category_id); ?>
			<h3>
				<a href="<?php echo $caturl ?>" title="<?php echo $category->category_name ?>">
				<?php echo $category->category_name ?><span><?php echo ' ('.$category->number_of_products.')'?></span><br />
				<?php if ($category->category_thumb_image) {
					echo VmImage::getImageByCat($category)->displayImage();
				} ?>
				</a>
			</h3>
		</div>

		<?php
		// Do we need to close the current row now?
		if ($iCol == $categories_per_row) { // If the number of products per row has been reached
			echo "<div class='clear'></div></div>";
			$iCol = 1;
		}
		else {
			$iCol++;
		}
	}
	// Do we need a final closing row tag?
	if ($iCol != 1) {
		echo "<div class='clear'></div></div>";
	}
	?>
	<div class="clear"></div>
	</div>

<div class="horizontal-separator margintop20 marginbottom20"></div>
<?php } ?>



<?php // Show child categories
if (!empty($this->products)) {
	?>
	<div class="browse-view">
		<h1><?php echo $this->category->category_name; ?></h1>
<?php
	$iBrowse = 1;


	// calculation of the categories per row
	$products_per_row = $this->category->products_per_row;
	$browsecellwidth = floor( 100 / $products_per_row);




foreach ($this->products as $product) {

		if ($iBrowse == 1) { // this is an indicator wether a row needs to be opened or not ?>
		<div class="browse-row">
		<?php }
	?>

			<!-- Product Listing Output -->
			<div class="width<?php echo $browsecellwidth ?> floatleft">

				<div>
					<div class="width30 floatleft center">

						<?php /** @todo make image popup */
						//todo add the attributes 'class="browseProductImage" border="0" title="'.$product->product_name.'" alt="'.$product->product_name .'"');
						echo VmImage::getImageByProduct($product)->displayImage('class="browseProductImage" border="0" title="'.$product->product_name.'" ',$product->product_name);
						?>


						<!-- The "Average Customer Rating" Part -->
						<?php if (VmConfig::get('pshop_allow_reviews') == 1) { ?>
						<span class="contentpagetitle"><?php echo JText::_('VM_CUSTOMER_RATING') ?>:</span>
						<br />
						<?php
						// $img_url = JURI::root().VmConfig::get('assets_general_path').'/reviews/'.$product->votes->rating.'.gif';
						// echo JHTML::image($img_url, $product->votes->rating.' '.JText::_('REVIEW_STARS'));
						// echo JText::_('VM_TOTAL_VOTES').": ". $product->votes->allvotes; ?>
						<?php } ?>


						<div class="paddingtop8">
						<?php // Show Stock Status
						echo JHTML::image(JURI::root().VmConfig::get('assets_general_path').'images/vmgeneral/'.$product->stock->stock_level.'.png', $product->stock->stock_tip, array('title' => $product->stock->stock_tip));
						echo '<br /><span class="stock-level">'.JText::_('VM_STOCK_LEVEL_DISPLAY_TITLE_TIP').'</span>';
						?>
						</div>

					</div>

					<div class="width70 floatright">
						<h2><?php echo JHTML::link($product->link, $product->product_name); ?></h2>

						<?php // Product Short Description
						if(!empty($product->product_s_desc)) { ?>
						<p class="product_s_desc">
						<?php echo $product->product_s_desc; ?>
						</p>
						<?php } ?>


						<div class="product-price marginbottom12">
<?php	if (VmConfig::get('show_prices') == '1') {
			if( $product->product_unit && VmConfig::get('vm_price_show_packaging_pricelabel')) {
				echo "<strong>". JText::_('VM_CART_PRICE_PER_UNIT').' ('.$product->product_unit."):</strong>";
			} else echo "<strong>". JText::_('VM_CART_PRICE'). ": </strong>";

			//todo add config settings
			if( $this->showBasePrice){
				echo shopFunctionsF::createPriceDiv('basePrice','VM_PRODUCT_BASEPRICE',$product->prices);
				echo shopFunctionsF::createPriceDiv('basePriceVariant','VM_PRODUCT_BASEPRICE_VARIANT',$product->prices);
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
						<p>
						<?php // Product Details Button
						echo JHTML::link($product->link, JText::_('PRODUCT_DETAILS'), array('title' => $product->product_name,'class' => 'product-details'));
		?>
						</p>





		<!--
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

	 -->






					</div>




				<div class="clear"></div>
				</div>


			</div>

		<?php
		// Do we need to close the current row now?
		if ($iBrowse == $products_per_row) { // If the number of products per row has been reached
			echo "<div class='clear'></div></div>";
			$iBrowse = 1;
		}
		else {
			$iBrowse++;
		}
	}
	// Do we need a final closing row tag?
	if ($iBrowse != 1) {
		echo "<div class='clear'></div></div>";
	}
	?>
	<div class="clear"></div>
	</div>
<?php } ?>
