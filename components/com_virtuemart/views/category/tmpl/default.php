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
JHTML::_( 'behavior.modal' );
/* javascript for list Slide
  Only here for the order list
  can be changed by the template maker
*/
$js = "
jQuery(document).ready(function () {
	jQuery('.orderlistcontainer').hover(
		function() { jQuery(this).find('.orderlist').stop().show()},
		function() { jQuery(this).find('.orderlist').stop().hide()}
	)
});
";
$document = JFactory::getDocument();
$document->addScriptDeclaration($js);
if ($this->search) { ?>
<!--BEGIN Search Box -->
<form action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=category&search=true&limitstart=0&virtuemart_category_id='.$this->category->virtuemart_category_id ); ?>" method="post">
<div class="virtuemart_search">
<?php
$option  = array('virtuemart_custom_id' =>null, 'custom_title' => JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'));
$options = array_merge(array($option), $this->searchcustom->selectList);
echo JText::_('COM_VIRTUEMART_SET_PRODUCT_TYPE').' '.JHTML::_('select.genericlist', $options, 'custom_parent_id', 'class="inputbox"', 'virtuemart_custom_id', 'custom_title', $this->searchcustom->custom_parent_id); ?>
<br />
<?php if ($this->searchcustom->custom_parent_id) {
	foreach ($this->searchcustom->selected as $key =>$custom){
		$option  = array('custom_value' =>null, 'title' => JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'));
		$options = array_merge(array($option), $custom->fields[$custom->virtuemart_custom_id]);
		dump ($options);
		echo JText::_('COM_VIRTUEMART_SET_PRODUCT_TYPE').' '.JHTML::_('select.genericlist', $options, 'customfields['.$custom->virtuemart_custom_id.']', 'class="inputbox"', 'custom_value', 'title', 0);
	}
} ?>
<input style="height:16px;vertical-align :middle;" name="keyword" class="inputbox" type="text" size="20" value="<?php echo $this->keyword ?>" />
<input type="submit" value="<?php echo JText::_('COM_VIRTUEMART_SEARCH') ?>" class="button" onclick="this.form.keyword.focus();"/>
</div>
		<input type="hidden" name="search" value="true" />
		<input type="hidden" name="category" value="0" />
	  </form>

<!-- End Search Box -->
<?php }
/* Show child categories */

if ( VmConfig::get('showCategory',1) ) {
	if ($this->category->haschildren) {

		// Category and Columns Counter
		$iCol = 1;
		$iCategory = 1;

		// Calculating Categories Per Row
		$categories_per_row = VmConfig::get ( 'categories_per_row', 3 );
		$category_cellwidth = ' width'.floor ( 100 / $categories_per_row );

		// Separator
		$verticalseparator = " vertical-separator";
		?>

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
}

// Show child categories
if (!empty($this->products)) {
	$search='';
	if (!empty($this->keyword)) {
		$search ='&search=true&keyword='.$this->keyword;
		?>
		<h3><?php echo $this->keyword; ?></h3>
		<?php
	}
	?>

<?php // Category and Columns Counter
$iBrowseCol = 1;
$iBrowseProduct = 1;

// Calculating Products Per Row
$BrowseProducts_per_row = (empty($this->category->products_per_row)) ?  VmConfig::get('products_per_row',2) : $this->category->products_per_row;
$Browsecellwidth = ' width'.floor ( 100 / $BrowseProducts_per_row );

// Separator
$verticalseparator = " vertical-separator";
?>

<div class="browse-view">

	<h1><?php echo $this->category->category_name; ?></h1>

	<div class="orderby-displaynumber">
		<div class="width70 floatleft">
			<?php echo $this->orderByList; ?>
		</div>
		<div class="width30 floatright display-number">
			<form  method="post" >
				<input type="hidden" name="keyword" value="<?php echo JRequest::getWord('keyword') ?>" >
				<?php echo $this->pagination->getListFooter(); ?>
			</form>
		</div>
	<div class="clear"></div>
	</div>

<?php // Start the Output
foreach ( $this->products as $product ) {

	// Show the horizontal seperator
	if ($iBrowseCol == 1 && $iBrowseProduct > $BrowseProducts_per_row) { ?>
	<div class="horizontal-separator"></div>
	<?php }

	// this is an indicator wether a row needs to be opened or not
	if ($iBrowseCol == 1) { ?>
	<div class="row">
	<?php }

	// Show the vertical seperator
	if ($iBrowseProduct == $BrowseProducts_per_row or $iBrowseProduct % $BrowseProducts_per_row == 0) {
		$show_vertical_separator = ' ';
	} else {
		$show_vertical_separator = $verticalseparator;
	}

		// Show Products ?>
		<div class="product floatleft<?php echo $Browsecellwidth . $show_vertical_separator ?>">
			<div class="spacer">
				<div class="width30 floatleft center">
					<?php /** @todo make image popup */
							echo $product->images[0]->displayMediaThumb('class="browseProductImage" border="0" title="'.$product->product_name.'" ');
						?>


						<!-- The "Average Customer Rating" Part -->
						<?php if (VmConfig::get('pshop_allow_reviews') == 1) { ?>
						<span class="contentpagetitle"><?php echo JText::_('COM_VIRTUEMART_CUSTOMER_RATING') ?>:</span>
						<br />
						<?php
						// $img_url = JURI::root().VmConfig::get('assets_general_path').'/reviews/'.$product->votes->rating.'.gif';
						// echo JHTML::image($img_url, $product->votes->rating.' '.JText::_('COM_VIRTUEMART_REVIEW_STARS'));
						// echo JText::_('COM_VIRTUEMART_TOTAL_VOTES').": ". $product->votes->allvotes; ?>
						<?php } ?>


						<div class="paddingtop8">
						<?php // Show Stock Status
						echo JHTML::image(JURI::root().VmConfig::get('assets_general_path').'images/vmgeneral/'.$product->stock->stock_level.'.png', $product->stock->stock_tip, array('title' => $product->stock->stock_tip));
						echo '<br /><span class="stock-level">'.JText::_('COM_VIRTUEMART_STOCK_LEVEL_DISPLAY_TITLE_TIP').'</span>';
						?>
						</div>
				</div>

				<div class="width70 floatright">

					<h2><?php echo JHTML::link($product->link, $product->product_name); ?></h2>

						<?php // Product Short Description
						if(!empty($product->product_s_desc)) { ?>
						<p class="product_s_desc">
						<?php echo shopFunctionsF::limitStringByWord($product->product_s_desc, 40, '...') ?>
						</p>
						<?php } ?>

					<div class="product-price marginbottom12" id="productPrice<?php echo $product->virtuemart_product_id ?>">
					<?php
					if ($this->show_prices == '1') {
						if( $product->product_unit && VmConfig::get('vm_price_show_packaging_pricelabel')) {
							echo "<strong>". JText::_('COM_VIRTUEMART_CART_PRICE_PER_UNIT').' ('.$product->product_unit."):</strong>";
						}

						//todo add config settings
						if( $this->showBasePrice){
							echo shopFunctionsF::createPriceDiv('basePrice','COM_VIRTUEMART_PRODUCT_BASEPRICE',$product->prices);
							echo shopFunctionsF::createPriceDiv('basePriceVariant','COM_VIRTUEMART_PRODUCT_BASEPRICE_VARIANT',$product->prices);
						}
						echo shopFunctionsF::createPriceDiv('variantModification','COM_VIRTUEMART_PRODUCT_VARIANT_MOD',$product->prices);
						echo shopFunctionsF::createPriceDiv('basePriceWithTax','COM_VIRTUEMART_PRODUCT_BASEPRICE_WITHTAX',$product->prices);
						echo shopFunctionsF::createPriceDiv('discountedPriceWithoutTax','COM_VIRTUEMART_PRODUCT_DISCOUNTED_PRICE',$product->prices);
						echo shopFunctionsF::createPriceDiv('salesPriceWithDiscount','COM_VIRTUEMART_PRODUCT_SALESPRICE_WITH_DISCOUNT',$product->prices);
						echo shopFunctionsF::createPriceDiv('salesPrice','COM_VIRTUEMART_PRODUCT_SALESPRICE',$product->prices);
						echo shopFunctionsF::createPriceDiv('priceWithoutTax','COM_VIRTUEMART_PRODUCT_SALESPRICE_WITHOUT_TAX',$product->prices);
						echo shopFunctionsF::createPriceDiv('discountAmount','COM_VIRTUEMART_PRODUCT_DISCOUNT_AMOUNT',$product->prices);
						echo shopFunctionsF::createPriceDiv('taxAmount','COM_VIRTUEMART_PRODUCT_TAX_AMOUNT',$product->prices);
					} ?>
					</div>

					<p>
					<?php // Product Details Button
					echo JHTML::link($product->link, JText::_('COM_VIRTUEMART_PRODUCT_DETAILS'), array('title' => $product->product_name,'class' => 'product-details'));
					?>
					</p>

				</div>
			<div class="clear"></div>
			</div>
		</div>
	<?php
	$iBrowseProduct ++;

	// Do we need to close the current row now?
	if ($iBrowseCol == $BrowseProducts_per_row) { ?>
	<div class="clear"></div>
	</div>
		<?php
		$iBrowseCol = 1;
	} else {
		$iBrowseCol ++;
	}
}
// Do we need a final closing row tag?
if ($iBrowseCol != 1) { ?>
	<div class="clear"></div>
	</div>
<?php
}
?>
	<div class="page-results"><?php echo $this->pagination->getResultsCounter();?></div>
</div>
<?php } ?>
