<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<?php echo $buttons_header // The PDF, Email and Print buttons ?>

<?php
if( $this->get_cfg( 'showPathway' )) {
	echo "<div class=\"pathway\">$navigation_pathway</div>";
} ?>
<br/>
<table border="0" style="width: 100%;">
  <tbody>
<tr>
  <td rowspan="3" valign="top" style="text-align:center;"><br/> <?php echo $product_image ?><br/><br/> <?php echo $more_images ?></td>
  <td rowspan="1" colspan="2">
  <h1> <?php echo $product_name ?>  <?php echo $edit_link ?></h1>
  </td>
</tr>
<tr>
  <td rowspan="1" colspan="2">
  <?php echo $product_vendor_lbl ?>
  </td>
</tr>
<tr>
  <td rowspan="1" colspan="2"> <?php echo $manufacturer_link ?><br /></td>
</tr>
<tr>
      <td width="33%" valign="top" align="left">
      	<?php
      		echo $product_price;
			//ct //show the ex tax when inc
			if ($product_price_without_tax != ""){echo "<br/>".$product_price_without_tax;}
			if ($product_price_with_tax != ""){echo "<br/>".$product_price_with_tax;}
		?><br />
	  </td>
      <td valign="top"> <?php echo $product_packaging ?><br /></td>
</tr>
<tr>
  <td rowspan="1" colspan="3"><hr /> <?php echo $product_description ?><br/><span style="font-style: italic;"> <?php echo $file_list ?></span></td>
</tr>
<tr>
  <td colspan="3" align="right"> <?php echo $favouriteButton; ?></td>
<tr>
<tr>
  <td> <?php echo $product_availability ?><br /></td>
  <td> <?php echo $$stock_level; ?></td>
  <td><br /> <?php echo $addtocart ?></td>
</tr>

<tr>
  <td colspan="3"><hr /> <?php echo $product_reviews ?></td>
</tr>
<tr>
  <td colspan="3"> <?php echo $product_reviewform ?><br /></td>
</tr>
<tr>
  <td colspan="3"> <?php echo $related_products ?><br /></td>
</tr>
<tr>
  <td colspan="3"><div style="text-align: center;"> <?php echo $vendor_link ?><br /></div><br /></td>
</tr>
  </tbody>
</table>
 <?php echo $navigation_childlist ?><br style="clear:both"/>