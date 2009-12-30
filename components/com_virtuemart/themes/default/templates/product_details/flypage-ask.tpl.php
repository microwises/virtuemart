<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
mm_showMyFileName(__FILE__);
 ?>

<?php echo $buttons_header // The PDF, Email and Print buttons ?>

<?php
if( $this->get_cfg( 'showPathway' )) {
	echo "<div class=\"pathway\">$navigation_pathway</div>";
}
if( $this->get_cfg( 'product_navigation', 1 )) {
	if( !empty( $previous_product )) {
		echo '<a class="previous_page" href="'.$previous_product_url.'">'.shopMakeHtmlSafe($previous_product['product_name']).'</a>';
	}
	if( !empty( $next_product )) {
		echo '<a class="next_page" href="'.$next_product_url.'">'.shopMakeHtmlSafe($next_product['product_name']).'</a>';
	}
}
?>
<br style="clear:both;" />
<table border="0" align="center" style="width: 100%;" >
    <tr>
	    <td rowspan="1" colspan="2" align="center">
	        <div style="text-align: center;">
                <h1><?php echo $product_name; echo ' ' . $edit_link; ?></h1>
            </div>
            <div style="text-align: center; padding: 0px 0px 10px 0px">
                <?php echo $product_vendor_lbl ?>
            </div>

        </td>
        <td>
        </td>
    </tr>
    <tr>
        <td>
	        <?php echo $product_s_desc ?>
	    </td>
    </tr>
    <tr>
	    <td colspan="2"><hr style="width: 100%; height: 2px;" /></td>
    </tr>
    <tr>
        <td align="left" valign="top" width="220">
            <div><?php echo $product_image ?></div>
        </td>
        <td valign="top">
            <div style="text-align: center;">
            <span style="font-style: italic;"></span><?php echo $addtocart ?><span style="font-style: italic;"></span></div>
        </td></tr>
        <tr>
  <td rowspan="1" colspan="2"><?php echo $manufacturer_link ?><br /></td>
</tr>
<tr>
      <td valign="top" align="left">
      	<?php echo $product_price;
			//ct //show the ex tax when inc
			if ($product_price_without_tax != ""){echo "<br/>".$product_price_without_tax;}
			if ($product_price_with_tax != ""){echo "<br/>".$product_price_with_tax;}
		?><br />
      </td>
</tr>
<tr>
      <td valign="top"><?php echo $product_packaging ?><br /></td>
</tr>
	<tr>
	  <td ><?php echo $ask_seller ?></td>
	</tr>
	<tr>
	    <td rowspan="1" colspan="2">
            <hr style="width: 100%; height: 2px;" />
            <?php echo $product_description ?>
	        <br/><span style="font-style: italic;"><?php echo $file_list ?></span>
        </td>
	</tr>
	<tr>
	    <td colspan="2">
	    <hr style="width: 100%; height: 2px;" />
	    <?php  echo $related_products ?>
	    <br />
	    </td>
	</tr>
    <tr>
	    <td colspan="2"><hr style="width: 100%; height: 2px;" />
        <div style="text-align: center;">
                </div>
                <?php echo $navigation_childlist ?><br /></td>
	</tr>

	<tr>
	  <td colspan="2"><?php echo $product_reviewform ?><br /></td>
	</tr>
  <tr>
	  <td colspan="3"><div style="text-align: center;"><?php echo $vendor_link ?><br /></div><br /></td>
	</tr>
</table><br style="clear:both"/>
<div class="back_button"><a href='javascript:history.go(-1)'> <?php echo JText::_('BACK') ?></a></div>
