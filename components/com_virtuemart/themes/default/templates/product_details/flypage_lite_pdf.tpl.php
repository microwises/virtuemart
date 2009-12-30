<?php
// this template must have quirky html, because HTML2PDF doesn't fully understand
// CSS and XHTML
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<br><br>
<h1><?php echo $product_name ?></h1>
<?php echo $product_vendor_lbl ?>
<br><br>

<table width=100%>
<tr><td width=50%><br><?php
			echo $product_price;
			//ct //show the ex tax when inc
			if ($product_price_without_tax != ""){echo "<br/>".$product_price_without_tax;}
			if ($product_price_with_tax != ""){echo "<br/>".$product_price_with_tax;}
		?>
	</td>
<td width=50%><?php echo $product_image ?>&nbsp;</td>
</tr>
</table>



<?php echo $product_description ?>

<?php echo $product_type ?>
<table width=100%>
<tr><td><?php echo $vendor_link ?></td></tr>
</table>

<table>
<tr><td>
<?php echo $product_reviews ?>
</td></tr>
</table>
