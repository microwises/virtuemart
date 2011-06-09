<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Eugen Stranz
 * @author RolandD,
 * @todo handle child products
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
defined ( '_JEXEC' ) or die ( 'Restricted access' );

// addon for joomla modal Box
JHTML::_ ( 'behavior.modal' );
JHTML::_('behavior.tooltip');

/* Let's see if we found the product */
if (empty ( $this->product )) {
	echo JText::_ ( 'COM_VIRTUEMART_PRODUCT_NOT_FOUND' );
	echo '<br /><br />  ' . $this->continue_link_html;
	return;
}
?>

<div class="productdetails-view">

	<?php // Product Navigation
	if (VmConfig::get ( 'product_navigation', 1 )) { ?>
		<div class="product-neighbours">
		<?php
		if (! empty ( $this->product->neighbours ['previous'] )) {
			$prev_link = JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['previous'] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id );
			echo JHTML::_ ( 'link', $prev_link, $this->product->neighbours ['previous'] ['product_name'], array ('class' => 'previous-page' ) );
		}
		if (! empty ( $this->product->neighbours ['next'] )) {
			$next_link = JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['next'] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id );
			echo JHTML::_ ( 'link', $next_link, $this->product->neighbours ['next'] ['product_name'], array ('class' => 'next-page' ) );
		}
		?>
		<div class="clear"></div>
		</div>
	<?php } ?>

	<?php // product Title ?>
	<h1><?php echo $this->product->product_name ?></h1>

	<?php // Product Edit Link
	echo $this->edit_link ?>

	<?php // PDF - Print - Email Icon
	if (VmConfig::get('pdf_button_enable', 1) == '1' || VmConfig::get('show_emailfriend', 1) == '1' || VmConfig::get('show_printicon', 1) == '1') { ?>
	<div class="icons">
		<?php $link = (VmConfig::isJ15()) ? 'index2.php' : 'index.php';
		$link .= '?tmpl=component&option=com_virtuemart&view=productdetails&virtuemart_product_id='.$this->product->virtuemart_product_id;
		echo shopFunctionsF::PdfIcon($link.'&output=pdf' );
		echo shopFunctionsF::PrintIcon($link.'&print=1');
		echo shopFunctionsF::EmailIcon($this->product->virtuemart_product_id); ?>
	<div class="clear"></div>
	</div>
	<?php } ?>

	<?php // Product Short Description
	if (!empty($this->product->product_s_desc)) { ?>
	<div class="product-short-description">
		<?php /** @todo Test if content plugins modify the product description */
		echo $this->product->product_s_desc; ?>
	</div>
	<?php } ?>

	<div>
		<div class="width50 floatleft">

		<?php // Product Main Image
		if (!empty($this->product->images[0])) { ?>
			<div class="main-image">
			<?php echo $this->product->images[0]->displayMediaFull('class="product-image"'); ?>
			</div>
		<?php } ?>

		<?php // Showing the additional images
		if(!empty($this->product->images) && count($this->product->images)>1) { ?>
			<div class="additional-images">
			<?php // List all Images
			foreach ($this->product->images as $image) {
				echo $image->displayMediaThumb('class="product-image"'); //'class="modal"'
			} ?>
			</div>
		<?php } ?>

		</div>

		<div class="width50 floatright">

			<div class="spacer-buy-area">

				<?php // TO DO in Multi-Vendor not needed at the moment and just would lead to confusion
				/* $link = JRoute::_('index2.php?option=com_virtuemart&view=virtuemart&task=vendorinfo&virtuemart_vendor_id='.$this->product->virtuemart_vendor_id);
				$text = JText::_('COM_VIRTUEMART_VENDOR_FORM_INFO_LBL');
				echo '<span class="bold">'. JText::_('COM_VIRTUEMART_PRODUCT_DETAILS_VENDOR_LBL'). '</span>'; ?><a class="modal" href="<?php echo $link ?>"><?php echo $text ?></a><br />
				*/ ?>

				<?php
				$rating = empty($this->rating)? JText::_('COM_VIRTUEMART_UNRATED'):$this->rating->rating;
				echo JText::_('COM_VIRTUEMART_RATING') . $rating;

				// Product Price
				if ($this->show_prices) { ?>
				<div class="product-price" id="productPrice<?php echo $this->product->virtuemart_product_id ?>">
				<?php
				if ($this->product->product_unit && VmConfig::get ( 'vm_price_show_packaging_pricelabel' )) {
					echo "<strong>" . JText::_ ( 'COM_VIRTUEMART_CART_PRICE_PER_UNIT' ) . ' (' . $this->product->product_unit . "):</strong>";
				} else {
					echo "<strong>" . JText::_ ( 'COM_VIRTUEMART_CART_PRICE' ) . ": </strong>";
				}

				if ($this->showBasePrice) {
					echo shopFunctionsF::createPriceDiv ( 'basePrice', 'COM_VIRTUEMART_PRODUCT_BASEPRICE', $this->product->prices );
					echo shopFunctionsF::createPriceDiv ( 'basePriceVariant', 'COM_VIRTUEMART_PRODUCT_BASEPRICE_VARIANT', $this->product->prices );
				}

				echo shopFunctionsF::createPriceDiv ( 'variantModification', 'COM_VIRTUEMART_PRODUCT_VARIANT_MOD', $this->product->prices );
				echo shopFunctionsF::createPriceDiv ( 'basePriceWithTax', 'COM_VIRTUEMART_PRODUCT_BASEPRICE_WITHTAX', $this->product->prices );
				echo shopFunctionsF::createPriceDiv ( 'discountedPriceWithoutTax', 'COM_VIRTUEMART_PRODUCT_DISCOUNTED_PRICE', $this->product->prices );
				echo shopFunctionsF::createPriceDiv ( 'salesPriceWithDiscount', 'COM_VIRTUEMART_PRODUCT_SALESPRICE_WITH_DISCOUNT', $this->product->prices );
				echo shopFunctionsF::createPriceDiv ( 'salesPrice', 'COM_VIRTUEMART_PRODUCT_SALESPRICE', $this->product->prices );
				echo shopFunctionsF::createPriceDiv ( 'priceWithoutTax', 'COM_VIRTUEMART_PRODUCT_SALESPRICE_WITHOUT_TAX', $this->product->prices );
				echo shopFunctionsF::createPriceDiv ( 'discountAmount', 'COM_VIRTUEMART_PRODUCT_DISCOUNT_AMOUNT', $this->product->prices );
				echo shopFunctionsF::createPriceDiv ( 'taxAmount', 'COM_VIRTUEMART_PRODUCT_TAX_AMOUNT', $this->product->prices ); ?>
				</div>
				<?php } ?>

				<?php // Add To Cart Button
				if (!VmConfig::get('use_as_catalog',0)) { ?>
				<div class="addtocart-area">
					<form method="post" class="product" action="index.php" id="addtocartproduct<?php echo $this->product->virtuemart_product_id ?>">
	<?php // Product custom_fields
	if (!empty($this->product->customfieldsCart)) {  ?>
	<div class="product-fields">
		<?php foreach ($this->product->customfieldsCart as $field)
		{ ?><div style="display:inline-block;" class="product-field product-field-type-<?php echo $field->field_type ?>">
			<span class="product-fields-title" ><b><?php echo $field->custom_title ?></b></span>
			<?php echo JHTML::tooltip($field->custom_tip, $field->custom_title, 'tooltip.png'); ?>
			<span class="product-field-display"><?php echo $field->display ?></span>

			<span class="product-field-desc"><?php echo $field->custom_field_desc ?></span>
			</div><br/ >
			<?php
		}
		?>
	</div>
	<?php } ?>

		<div class="addtocart-bar">

			<?php // Display the quantity box ?>
			<!-- <label for="quantity<?php echo $this->product->virtuemart_product_id;?>" class="quantity_box"><?php echo JText::_('COM_VIRTUEMART_CART_QUANTITY'); ?>: </label> -->
			<span class="quantity-box">
				<input type="text" class="quantity-input" name="quantity[]" value="1" />
			</span>
			<span class="quantity-controls">
				<input type="button" class="quantity-controls quantity-plus" />
				<input type="button" class="quantity-controls quantity-minus" />
			</span>
			<?php // Display the quantity box END ?>

			<?php // Add the button
			$button_lbl = JText::_('COM_VIRTUEMART_CART_ADD_TO');
			$button_cls = ''; //$button_cls = 'addtocart_button';
			if (VmConfig::get('check_stock') == '1' && !$this->product->product_in_stock) {
				$button_lbl = JText::_('COM_VIRTUEMART_CART_NOTIFY');
				$button_cls = 'notify-button';
			} ?>

			<?php // Display the add to cart button ?>
			<span class="addtocart-button">
				<input type="submit" name="addtocart"  class="addtocart-button" value="<?php echo $button_lbl ?>" title="<?php echo $button_lbl ?>" />
			</span>

		<div class="clear"></div>
		</div>

		<?php // Display the add to cart button END ?>
		<input type="hidden" class="pname" value="<?php echo $this->product->product_name ?>">
		<input type="hidden" name="option" value="com_virtuemart" />
		<input type="hidden" name="view" value="cart" />
		<noscript><input type="hidden" name="task" value="add" /></noscript>
		<input type="hidden" name="virtuemart_product_id[]" value="<?php echo $this->product->virtuemart_product_id ?>" />
		<?php /** @todo Handle the manufacturer view */ ?>
		<input type="hidden" name="virtuemart_manufacturer_id" value="<?php echo $this->product->virtuemart_manufacturer_id ?>" />
		<input type="hidden" name="virtuemart_category_id[]" value="<?php echo $this->product->virtuemart_category_id ?>" />
	</form>

				<div class="clear"></div>
				</div>
			<?php }  // Add To Cart Button END ?>

				<?php // Availability Image
				/* TO DO add width and height to the image */
				if (!empty($this->product->product_availability)) { ?>
				<div class="availability">
					<?php echo JHTML::image(JURI::root().VmConfig::get('assets_general_path').'images/availability/'.$this->product->product_availability, $this->product->product_availability, array('class' => 'availability')); ?>
				</div>
				<?php } ?>

				<?php // Ask a question about this product
				$url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&task=askquestion&virtuemart_product_id='.$this->product->virtuemart_product_id.'&virtuemart_category_id='.$this->product->virtuemart_category_id.'&tmpl=component'); ?>
				<div class="ask-a-question">
					<a class="ask-a-question modal" rel="{handler: 'iframe', size: {x: 700, y: 550}}" href="<?php echo $url ?>"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL') ?></a>
				</div>

				<?php // Manufacturer of the Product
				if(VmConfig::get('show_manufacturer', 1) && !empty($this->product->virtuemart_manufacturer_id)) { ?>
				<div class="manufacturer">
				<?php
					$link = JRoute::_('index.php?option=com_virtuemart&view=manufacturer&virtuemart_manufacturer_id='.$this->product->virtuemart_manufacturer_id.'&tmpl=component');
					$text = $this->product->mf_name;

					/* Avoid JavaScript on PDF Output */
					if (strtolower(JRequest::getVar('output')) == "pdf"){
						echo JHTML::_('link', $link, $text);
					} else { ?>
						<span class="bold"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_DETAILS_MANUFACTURER_LBL') ?></span><a class="modal" href="<?php echo $link ?>"><?php echo $text ?></a>
				<?PHP } ?>
				</div>
				<?php } ?>

			</div>
		</div>
	<div class="clear"></div>
	</div>

	<?php // Product Description
	if (!empty($this->product->product_desc)) { ?>
	<div class="product-description">
		<?php /** @todo Test if content plugins modify the product description */ ?>
		<span class="title"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_DESC_TITLE') ?></span>
		<?php echo $this->product->product_desc; ?>
	</div>
	<?php } // Product Description END ?>
	<?php // Product custom_fields
	if (!empty($this->product->customfields)) { ?>
	<div class="product-fields">
		<?php foreach ($this->product->customfields as $field)
		{ ?><div style="display:inline-block;" class="product-field product-field-type-<?php echo $field->field_type ?>">
			<span class="product-fields-title" ><b><?php echo JText::_($field->custom_title); ?></b></span>
			<?php echo JHTML::tooltip($field->custom_tip, $field->custom_title, 'tooltip.png'); ?>
			<span class="product-field-display"><?php echo $field->display ?></span>
			<span class="product-field-desc"><?php echo jText::_($field->custom_field_desc) ?></span>
			</div>
			<?php
		}
		?>
	</div>
	<?php } // Product custom_fields END ?>

	<?php // Product Packaging
	$product_packaging = '';
	if ($this->product->packaging || $this->product->box) { ?>
	<div class="product-packaging">
		<span class="bold"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_PACKAGING2') ?></span>
		<br />
		<?php
		if ($this->product->packaging) {
			$product_packaging .= JText::_('COM_VIRTUEMART_PRODUCT_PACKAGING1').$this->product->packaging;
			if ($this->product->box) $product_packaging .= '<br />';
		}
		if ($this->product->box) $product_packaging .= JText::_('COM_VIRTUEMART_PRODUCT_PACKAGING2').$this->product->box;
		echo str_replace("{unit}",$this->product->product_unit ? $this->product->product_unit : JText::_('COM_VIRTUEMART_PRODUCT_FORM_UNIT_DEFAULT'), $product_packaging); ?>
	</div>
	<?php } // Product Packaging END ?>

	<?php // Product Files
	// foreach ($this->product->images as $fkey => $file) {
		// Todo add downloadable files again
		// if( $file->filesize > 0.5) $filesize_display = ' ('. number_format($file->filesize, 2,',','.')." MB)";
		// else $filesize_display = ' ('. number_format($file->filesize*1024, 2,',','.')." KB)";

		/* Show pdf in a new Window, other file types will be offered as download */
		// $target = stristr($file->file_mimetype, "pdf") ? "_blank" : "_self";
		// $link = JRoute::_('index.php?view=productdetails&task=getfile&virtuemart_media_id='.$file->virtuemart_media_id.'&virtuemart_product_id='.$this->product->virtuemart_product_id);
		// echo JHTMl::_('link', $link, $file->file_title.$filesize_display, array('target' => $target));
	// }
	?>

	<?php // Related Products
/*	if ($this->product->related && !empty($this->product->related)) {
		$iRelatedCol = 1;
		$iRelatedProduct = 1;
		$RelatedProducts_per_row = 4 ;
		$Relatedcellwidth = ' width'.floor ( 100 / $RelatedProducts_per_row );
		$verticalseparator = " vertical-separator"; ?>

		<div class="related-products-view">
			<h4><?php echo JText::_('COM_VIRTUEMART_RELATED_PRODUCTS_HEADING') ?></h4>

		<?php // Start the Output
		foreach ($this->product->related as $rkey => $related) {

			// Show the horizontal seperator
			if ($iRelatedCol == 1 && $iRelatedProduct > $RelatedProducts_per_row) { ?>
				<div class="horizontal-separator"></div>
			<?php }

			// this is an indicator wether a row needs to be opened or not
			if ($iRelatedCol == 1) { ?>
				<div class="row">
			<?php }

			// Show the vertical seperator
			if ($iRelatedProduct == $RelatedProducts_per_row or $iRelatedProduct % $RelatedProducts_per_row == 0) {
				$show_vertical_separator = ' ';
			} else {
				$show_vertical_separator = $verticalseparator;
			}

					// Show Products ?>
					<div class="product floatleft<?php echo $Relatedcellwidth . $show_vertical_separator ?>">
						<div class="spacer">
							<div>
								<h3><?php echo JHTML::_('link', $related->link, $related->product_name); ?></h3>

								<?php // Product Image
								echo JHTML::link($related->link, $related->images[0]->displayMediaThumb('title="'.$related->product_name.'"')); ?>

								<div class="product-price">
								<?php /** @todo Format pricing  ?>
								<?php if (is_array($related->price)) echo $related->price['salesPrice']; ?>
								</div>

								<div>
								<?php // Product Details Button
								echo JHTML::link($related->link, JText::_ ( 'COM_VIRTUEMART_PRODUCT_DETAILS' ), array ('title' => $related->product_name, 'class' => 'product-details' ) ); ?>
								</div>
							</div>
						<div class="clear"></div>
						</div>
					</div>
			<?php
			$iRelatedProduct ++;

			// Do we need to close the current row now?
			if ($iRelatedCol == $RelatedProducts_per_row) { ?>
				<div class="clear"></div>
				</div>
			<?php
			$iRelatedCol = 1;
			} else {
				$iRelatedCol ++;
			}
		}
		// Do we need a final closing row tag?
		if ($iRelatedCol != 1) { ?>
			<div class="clear"></div>
			</div>
		<?php } ?>
		</div>
	<?php } */ ?>




	<?php // Show child categories
	if ( VmConfig::get('showCategory',1) ) {
		if ($this->category->haschildren) {
			$iCol = 1;
			$iCategory = 1;
			$categories_per_row = VmConfig::get ( 'categories_per_row', 3 );
			$category_cellwidth = ' width'.floor ( 100 / $categories_per_row );
			$verticalseparator = " vertical-separator"; ?>

		<div class="category-view">

			<?php // Start the Output
			if(!empty($this->category->children)){
			foreach ( $this->category->children as $category ) {

			// Show the horizontal seperator
			if ($iCol == 1 && $iCategory > $categories_per_row) { ?>
				<div class="horizontal-separator"></div>
			<?php }

			// this is an indicator wether a row needs to be opened or not
			if ($iCol == 1) { ?>
			<div class="row">
			<?php }

			// Show the vertical seperator
			if ($iCategory == $categories_per_row or $iCategory % $categories_per_row == 0) {
				$show_vertical_separator = ' ';
			} else {
				$show_vertical_separator = $verticalseparator;
			}

			// Category Link
			$caturl = JRoute::_ ( 'index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $category->virtuemart_category_id );

				// Show Category ?>
				<div class="category floatleft<?php echo $category_cellwidth . $show_vertical_separator ?>">
					<div class="spacer">
						<h2>
							<a href="<?php echo $caturl ?>" title="<?php echo $category->category_name ?>">
							<?php echo $category->category_name ?>
							<br />
							<?php // if ($category->ids) {
								echo $category->images[0]->displayMediaThumb(0,false);
							//} ?>
							</a>
						</h2>
					</div>
				</div>
			<?php
			$iCategory ++;

			// Do we need to close the current row now?
			if ($iCol == $categories_per_row) { ?>
			<div class="clear"></div>
			</div>
			<?php
			$iCol = 1;
			} else {
				$iCol ++;
			}
		}
		}
		// Do we need a final closing row tag?
		if ($iCol != 1) { ?>
			<div class="clear"></div>
			</div>
		<?php } ?>
		</div>
	<?php }
	} ?>

	<?php /**
	* Reviews/Rating
	* Author max Milbers ?
	* Author Kohl Patrick
	* Available indexes:
	* $review->userid => The user ID of the comment author
	* $review->username => The username of the comment author
	* $review->name => The name of the comment author
	* $review->created_on => The UNIX timestamp of the comment ("when" it was posted)
	* $review->user_rating => The rating; an integer from 1 - 5
	*
	*/

	?> <?php

	if($this->allowRating || $this->showReview){
?> 
	<div class="customer-reviews">
<form method="post" action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$this->product->virtuemart_product_id.'&virtuemart_category_id='.$this->product->virtuemart_category_id) ; ?>" name="reviewForm" id="reviewform">
	<?php
	}

	if($this->showRating){
		?><h4><?php echo JText::_('COM_VIRTUEMART_RATING') ?></h4>
		<?php
		$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
		$ratingsShow = VmConfig::get('vm_num_ratings_show',3); // TODO add  vm_num_ratings_show in vmConfig
		$starsPath = JURI::root().VmConfig::get('assets_general_path').'images/stars/';
		$stars = array();
		$showall = JRequest::getBool('showall', false);
		for ($num=0 ; $num <= $maxrating; $num++  ) {
			$title = (JText::_("VM_RATING_TITLE").' : '. $num . '/' . $maxrating) ;
			$stars[] = JHTML::image($starsPath.$num.'.gif', JText::_($num.'_STARS'), array("title" => $title) );
		}

		if($this->allowRating){
			echo JText::_('COM_VIRTUEMART_REVIEW_RATE')  ?>
			<table cellpadding="5" summary="<?php echo JText::_('COM_VIRTUEMART_REVIEW_RATE') ?>">
				<tr>
			<?php
				for ($num=0 ; $num<=$maxrating;  $num++ ) {?>
						<th id="<?php echo $num ?>_stars">
							<label for="rate<?php echo $num ?>"><?php echo $stars[ $num ]; ?></label>
						</th>
					<?php
				} ?>
			</tr>
			<tr>
			<?php
			if(!class_exists('VmHtml')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
			$showReviewFor = array();
			for ($num=0 ; $num<=$maxrating;  $num++ ) {
				$showReviewFor[$num] = $num;
			}
//			if($this->vote->vote!==null){
//
//			}
			$vote = !empty($this->vote)? $this->vote->vote:$maxrating;
			echo VmHTML::radioList('vote', $vote,$showReviewFor);
//			for ($num=0 ; $num<=$maxrating;  $num++ ) {

/*
				?>
				<td headers="<?php echo $num ?>_stars" style="text-align:center;"> <?php

			<?php /*	<input type="radio" id="rate<?php echo ((int)$num+1) ?>" name="rate" value="<?php echo ((int)$num+1) ?>" />
				</td> */

//			} ?>
			</tr>
		</table> <?php
		}
	}


	if($this->showReview){
		?><h4><?php echo JText::_('COM_VIRTUEMART_REVIEWS') ?></h4>

		<?php $alreadycommented = false;?>

		<div class="list-reviews">
		<?php // Loop through all reviews
			$i=0;
			foreach($this->rating_reviews as $review ) {
				if ($i % 2 == 0) {
   					$color = 'normal';
				} else {
					$color = 'highlight';
				}

				/* Check if user already commented */
//				if ($review->virtuemart_userid == $this->user->id) {
//					$alreadycommented = true;
//				} ?>

				<div class="<?php echo $color ?>">
					<span class="date"><?php echo JHTML::date($review->created_on, JText::_('DATE_FORMAT_LC')); ?></span>
					<?php //echo $stars[ $review->review_rating ] ?>
					<blockquote><?php echo $review->comment; ?></blockquote>
					<span class="bold"><?php echo $review->customer ?></span>
				</div>
				<?php
				$i++ ;
				if ( $i == $ratingsShow && !$showall) break;
			}

			if (count($this->rating_reviews) < 1) echo JText::_('COM_VIRTUEMART_NO_REVIEWS'); // "There are no reviews for this product"
			else {
				/* Show all reviews */
				if (!$showall && count($this->rating_reviews) >=$ratingsShow ) {
					$attribute = array('class'=>'product-details', 'title'=>JText::_('COM_VIRTUEMART_MORE_REVIEWS'));
					echo JHTML::link($this->more_reviews, JText::_('COM_VIRTUEMART_MORE_REVIEWS'),$attribute);
				}
			} ?>
			<div class="clear"></div>
			</div> <?php

			if($this->allowReview && !$alreadycommented){
				?>
			<div class="write-reviews">
					<?php echo JText::_('COM_VIRTUEMART_WRITE_FIRST_REVIEW'); // "Be the first to write a review!"
					$reviewJavascript = "
					function check_reviewform() {
						var form = document.getElementById('reviewform');

						var ausgewaehlt = false;
						for (var i=0; i<form.user_rating.length; i++)
							if (form.user_rating[i].checked)
								ausgewaehlt = true;
							if (!ausgewaehlt)  {
								alert('".JText::_('COM_VIRTUEMART_REVIEW_ERR_RATE',false)."');
								return false;
							}
							else if (form.comment.value.length < ". VmConfig::get('reviews_minimum_comment_length', 100).") {
								alert('". JText::sprintf('COM_VIRTUEMART_REVIEW_ERR_COMMENT1', VmConfig::get('reviews_minimum_comment_length', 100))."');
								return false;
							}
							else if (form.comment.value.length > ". VmConfig::get('reviews_maximum_comment_length', 2000).") {
								alert('". JText::sprintf('COM_VIRTUEMART_REVIEW_ERR_COMMENT2', VmConfig::get('reviews_maximum_comment_length', 2000))."');
								return false;
							}
							else {
								return true;
							}
						}

						function refresh_counter() {
							var form = document.getElementById('reviewform');
							form.counter.value= form.comment.value.length;
						}";
						$document = &JFactory::getDocument();
						$document->addScriptDeclaration($reviewJavascript);
						?>


						<h4><?php echo JText::_('COM_VIRTUEMART_WRITE_REVIEW')  ?></h4>
						<br />


							<br /><br />
							<?php
							echo JText::sprintf('COM_VIRTUEMART_REVIEW_COMMENT', VmConfig::get('reviews_minimum_comment_length', 100), VmConfig::get('reviews_maximum_comment_length', 2000));
							?>
							<br />
							<textarea title="<?php echo JText::_('COM_VIRTUEMART_WRITE_REVIEW') ?>" class="inputbox" id="comment" onblur="refresh_counter();" onfocus="refresh_counter();" onkeyup="refresh_counter();" name="comment" rows="5" cols="60"><?php if(!empty($this->review->comment))echo $this->review->comment; ?></textarea>
							<br />
							<input class="button" type="submit" onclick="return( check_reviewform());" name="submit_review" title="<?php echo JText::_('COM_VIRTUEMART_REVIEW_SUBMIT')  ?>" value="<?php echo JText::_('COM_VIRTUEMART_REVIEW_SUBMIT')  ?>" />
							<div align="right"><?php echo JText::_('COM_VIRTUEMART_REVIEW_COUNT')  ?>
								<input type="text" value="0" size="4" class="inputbox" name="counter" maxlength="4" readonly="readonly" />
							</div>

						<?php
				}
	}
//					} else {
//						echo '<strong>'.JText::_('COM_VIRTUEMART_DEAR').$this->user->name.',</strong><br />' ;
//						echo JText::_('COM_VIRTUEMART_REVIEW_ALREADYDONE');
//					}

	if($this->allowRating || $this->showReview){
	?>
			<input type="hidden" name="virtuemart_product_id" value="<?php echo $this->product->virtuemart_product_id; ?>" />
			<input type="hidden" name="option" value="com_virtuemart" />
			<input type="hidden" name="virtuemart_category_id" value="<?php echo JRequest::getInt('virtuemart_category_id'); ?>" />
			<input type="hidden" name="virtuemart_rating_review_id" value="0" />
			<input type="hidden" name="task" value="review" />
		</form>
		</div></div>
<?php }


//				else echo JText::_('COM_VIRTUEMART_REVIEW_LOGIN'); // Login to write a review!
				?>

	

</div>