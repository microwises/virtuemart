<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<?php
/** @todo Add vendor description */
//echo $this->vendor_store_desc."<br />";
echo "<br /><h4>".JText::_('VM_CATEGORIES')."</h4>";
foreach ($this->categories as $category) {
	echo JHTML::_('link', 
			JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$category->category_id), 
			JHTML::_('image', JURI::root().'components/com_virtuemart/shop_image/category/'.$category->category_thumb_image, $category->category_name)
			);
}

?>
<div class="vmRecent">
	<?php if ($this->recentProducts) echo $this->loadTemplate('recentproducts'); ?>
</div>
<?php
/* Show Featured Products */
if (VmConfig::get('showFeatured', 1) && !empty($this->featuredProducts)) echo $this->loadTemplate('featuredproducts');

/* Show Latest Products */
if (VmConfig::get('showlatest', 1) && !empty($this->latestProducts)) echo $this->loadTemplate('latestproducts');
?>