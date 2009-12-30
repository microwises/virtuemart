<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<?php echo $buttons_header // The PDF, Email and Print buttons ?>

<?php
if( $this->get_cfg( 'showPathway' )) {
	echo '<p><div class="pathway"' . $navigation_pathway . '</div></p>';
}
?>
<?php
// 	< Previous	|	Next >
if( $this->get_cfg( 'product_navigation', 1 )) {
	if( !empty( $previous_product )) {
		echo '<a class="previous_page" href="'.$previous_product_url.'">'.shopMakeHtmlSafe($previous_product['product_name']).'</a>';
	}
	if( !empty( $next_product )) {
		echo '<a class="next_page" href="'.$next_product_url.'">'.shopMakeHtmlSafe($next_product['product_name']).'</a>';
	}
}
?>
<p style="clear:both;"><?php echo $navigation_childlist ?></p>
<table border="0" align="center" style="width: 100%;">
  <tbody>
	<tr>
	  <td rowspan="1">
	  <h1><?php echo $product_name ?> <?php echo $edit_link ?></h1>
	  &nbsp;<?php echo $manufacturer_link ?>
	  </td>
<td align="center" valign="top" rowspan="4"><?php echo $product_image ?><br/><br/><?php echo $more_images ?></td>
	</tr>
	<tr>
	  <td rowspan="1"><font size="2">
	  <?php
	  		echo $product_price;
			//ct //show the ex tax when inc
			if ($product_price_without_tax != ""){echo "<br/>".$product_price_without_tax;}
			if ($product_price_with_tax != ""){echo "<br/>".$product_price_with_tax;}
		?></font><br />
	   </td>
	</tr>
	<tr>
	  <td style="text-align: center;"><br /></td>
	</tr>
	<tr>
	  <td rowspan="1">
	  	<hr style="width: 100%; height: 2px;" />
	  	<p><?php echo $product_description ?></p>
	  	<span style="font-style: italic;"><?php echo $file_list ?></span></td>
	</tr>
	<tr>
	  <td><hr style="width: 100%; height: 2px;" />
	  	<div style="text-align: center;">
	  		<?php echo $addtocart ?>
	  	</div>
	  </td>
	  <td style="text-align: center;" rowspan="1"><?php echo $product_availability ?></td>
	</tr>
	<tr>
	  <td colspan="2"><?php echo $stock_level ?></td>
	</tr>
	<tr>
	  <td colspan="2"><hr /><?php echo $product_reviews ?></td>
	</tr>
	<tr>
	  <td colspan="2"><?php echo $product_reviewform ?><br /></td>
	</tr>
	<tr>
	  <td colspan="2"><?php echo $related_products ?><br /></td>
	</tr>
	<tr>
	  <td rowspan="1" colspan="2">
	  	<div style="text-align: center;"><?php echo $vendor_link ?></div>
	  	<br />
	  </td>
	</tr>
  </tbody>
</table>
