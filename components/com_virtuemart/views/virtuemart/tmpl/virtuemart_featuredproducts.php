<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'image.php');

$iCol = 1;
//Number of featured products to show per row
$featured_per_row = 2;
//Set the cell width
$cellwidth = intval( (100 / $featured_per_row) - 2 );

if( empty( $this->featuredProducts )) {
	return; // Do nothing, if there are no Featured!
}
echo "<h3>".JText::_('VM_FEATURED_PRODUCT')."</h3>";
foreach( $this->featuredProducts as $featured ) {
	?>
	<div style="float:left;width:<?php echo $cellwidth ?>%;text-align:top;padding:0px;" >
		<?php
		echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&flypage='.$featured->flypage.'&product_id='.$featured->product_id), $featured->product_name);
		?>
			<h4><?php echo $featured->product_name; ?></h4></a>
			<?php echo $featured->product_price['salesPrice']; ?><br />
            <?php
			if ($featured->product_thumb_image) {
				echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&flypage='.$featured->flypage.'&product_id='.$featured->product_id), 
					ImageHelper::displayShopImage($featured->product_thumb_image, 'product', 'class="browseProductImage" border="0" alt="'.$featured->product_name.'"'));
			?>
			<br /><br/>
            <?php } ?>
            <?php echo $featured->product_s_desc; ?><br />
            
            <?php //echo $featured->form_addtocart; 
            ?>
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