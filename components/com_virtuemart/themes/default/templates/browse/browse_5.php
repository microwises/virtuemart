<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
mm_showMyFileName(__FILE__);
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'image.php');
 ?>
 <table width="100%" cellspacing="0" cellpadding="0" border="0" >
  <tr>
    <td >
        <a style="font-size: 16px; font-weight: bold;" href="<?php echo $product_flypage ?>"><?php echo $product_name ?></a>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap >
    	<?php echo $product_price;
    		//ct //show the ex tax when inc
			if ($product_price_without_tax != ""){echo "<br/>".$product_price_without_tax;}
			if ($product_price_with_tax != ""){echo "<br/>".$product_price_with_tax;}
    	?>
    </td>
  </tr>
  <tr>
    <td ><a href="<?php echo $product_flypage ?>">
          <?php /*echo ps_product::image_tag( $product_thumb_image, 'class="browseProductImage" border="0" title="'.$product_name.'" alt="'.$product_name .'"' )*/ ?>
          <?php ImageHelper::displayShopImage($product_thumb_image, 'product', 'class="browseProductImage" border="0" title="'.$product_name.'" alt="'.$product_name .'"'); ?>
       </a>
    </td>
  </tr>
  <tr>
    <td height="80" valign="top"><?php echo $product_s_desc ?><br />
      <a href="<?php echo $product_flypage ?>" title="<?php echo $product_name ?>"><?php echo $product_details ?>&nbsp;<strong><?php echo $product_name ?></strong></a>
    </td>
  </tr>
  <tr>
    <td ><hr /></td>
  </tr>
  <tr>
    <td ><?php echo $product_rating ?></td>
  </tr>
  <tr>
    <td ><?php echo $stock_level ?></td>
  </tr>
  <tr>
    <td ><?php echo $form_addtocart ?></td>
  </tr>
</table>
