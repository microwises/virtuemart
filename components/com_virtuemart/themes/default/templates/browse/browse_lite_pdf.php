<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
mm_showMyFileName(__FILE__);
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'image.php');
 ?>
 <table><tr><td></td></tr></table>
<table width="100%">
  <tr>
	<td>
		<?php /* echo ps_product::image_tag( $product_thumb_image, 'class="browseProductImage" border="0" title="'.$product_name.'" alt="'.$product_name .'"' ) */?>
		<?php ImageHelper::displayShopImage($product_thumb_image, 'product', 'class="browseProductImage" border="0" title="'.$product_name.'" alt="'.$product_name .'"'); ?>
       </td>
	<td><h2><?php echo $product_name ?></h2>
			<br>
			<?php echo $product_price;
				//ct //show the ex tax when inc
				if ($product_price_without_tax != ""){echo "<br/>".$product_price_without_tax;}
				if ($product_price_with_tax != ""){echo "<br/>".$product_price_with_tax;}
			?>
	</td>
  </tr>
  <tr><td colspan="2"><?php echo $product_s_desc ?> <a href="<?php echo $product_flypage ?>" title="<?php echo $product_name ?>"><?php echo $product_details ?>&nbsp;<strong><?php echo $product_name ?></strong></a></td>
  </tr>
</table>
