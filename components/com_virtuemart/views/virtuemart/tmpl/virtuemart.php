<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<?php
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
	<?php
	if ($this->recentProducts) {
		echo 'load recent products template';
		//return $tpl->fetch( 'common/recent.tpl.php' );
	}
	
	?>
</div>
<?php
// Show Featured Products
if (Vmconfig::getVar('showFeatured', 1)) {
    /* Load template edit featuredproduct.tpl.php to edit layout */
    echo $this->loadTemplate('featuredproducts');
}
// Show Latest Products
if (Vmconfig::getVar('showlatest', 1)) {
    /* latestproducts(random, no_of_products,month_based,category_based) no_of_products 0 = all else numeric amount
    edit latestproduct.tpl.php to edit layout */
    echo 'load latest products template';
}
?>