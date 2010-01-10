<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'image.php');

$iCol = 1;
//Number of featured products to show per row
$featured_per_row = 2;
//Set the cell width
$cellwidth = intval( (100 / $featured_per_row) - 2 );

if( empty( $featured_products )) {
	return; // Do nothing, if there are no Featured!
}
echo "<h3>".JText::_('VM_FEATURED_PRODUCT')."</h3>";
foreach( $featured_products as $featured ) {
	?>
	<div style="float:left;width:<?php echo $cellwidth ?>%;text-align:top;padding:0px;" >
         <a title="<?php echo $featured["product_name"] ?>" href="<?php $sess->purl(URL."index.php?option=com_virtuemart&amp;page=shop.product_details&amp;flypage=".$featured["flypage"]."&amp;product_id=".$featured["product_id"]) ?>"> 
			<h4><?php echo $featured["product_name"] ?></h4></a>
			<?php echo $featured['product_price'] ?><br />
            <?php
			if ( $featured["product_thumb"] ) { ?>
                <a title="<?php echo $featured["product_name"] ?>" href="<?php $sess->purl(URL."index.php?option=com_virtuemart&amp;page=shop.product_details&amp;flypage=".$featured["flypage"]."&amp;product_id=".$featured["product_id"]) ?>"> 
				<?php /* echo ps_product::image_tag( $featured["product_thumb"], "class=\"browseProductImage\" border=\"0\" alt=\"".$featured["product_name"]."\""); */?>
				<?php ImageHelper::displayShopImage($featured["product_thumb"], 'product', 'class="browseProductImage" border="0" alt="'.$featured["product_name"].'"');?>
				</a><br /><br/>
            <?php
			}?>
            <?php echo $featured['product_s_desc'] ?><br />
            
            <?php echo $featured['form_addtocart'] 
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