<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
* Special Products Module
*
* @version $Id$
* @package VirtueMart
* @subpackage modules
*
* 	@copyright (C) 2000 - 2004 Mr PHP
// W: www.mrphp.com.au
// E: info@mrphp.com.au
// P: +61 418 436 690
* Conversion to Mambo and many enhancements:
* 	@copyright (C) 2004-2005 Soeren Eberhardt
*
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/
global $mosConfig_absolute_path;

// Load the virtuemart main parse code
if( file_exists(dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' )) {
	require_once( dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' );
} else {
	require_once( dirname(__FILE__).'/../components/com_virtuemart/virtuemart_parser.php' );
}

$max_items = $params->get( 'max_items', 2 ); //maximum number of items to display
$category_id = $params->get( 'category_id', null ); // Display products from this category only
$display_style = $params->get( 'display_style', "vertical" ); // Display Style
$products_per_row = $params->get( 'products_per_row', 4 ); // Display X products per Row
$show_price = (bool)$params->get( 'show_price', 1 ); // Display the Product Price?
$show_addtocart = (bool)$params->get( 'show_addtocart', 1 ); // Display the "Add-to-Cart" Link?

require_once ( CLASSPATH. 'ps_product.php');
$ps_product = new ps_product;
$db = new ps_DB;

if ( $category_id ) {
	// BEGIN - MultiCategory Display - deneb
	$cat_ids = explode(",",$category_id);
	if (count($cat_ids) > 1){
		$multi_cats = 1;
	}
	// END - MultiCategory Display - deneb

	$q  = "SELECT DISTINCT product_sku FROM #__{vm}_product, #__{vm}_product_category_xref, #__{vm}_category WHERE \n";
	$q .= "(#__{vm}_product.product_parent_id='' OR #__{vm}_product.product_parent_id='0') \n";
	$q .= "AND #__{vm}_product.product_id=#__{vm}_product_category_xref.product_id \n";
	$q .= "AND #__{vm}_category.category_id=#__{vm}_product_category_xref.category_id \n";
	// BEGIN - MultiCategory Display - deneb
	if ($multi_cats){
		$i = 1;
		$q .= "AND (";
		foreach ($cat_ids as $cat_id){
			if ($i == count($cat_ids)){
				$q .= "(#__{vm}_category.category_id='$cat_id')";
			} else {
				$q .= "(#__{vm}_category.category_id='$cat_id') OR \n";
			}
			$i++;
		}
		$q .= ")  \n";
	} else {
		$q .= "AND #__{vm}_category.category_id='$category_id' \n";
	}
	// END - MultiCategory Display - deneb
	$q .= "AND #__{vm}_product.product_publish='Y' \n";
	$q .= "AND #__{vm}_product.product_special='Y' \n";
	if( CHECK_STOCK && PSHOP_SHOW_OUT_OF_STOCK_PRODUCTS != "1") {
		$q .= " AND product_in_stock > 0 \n";
	}
	$q .= "ORDER BY RAND() LIMIT 0, $max_items";
}
else {
	$q  = "SELECT DISTINCT product_sku FROM #__{vm}_product WHERE ";
	$q .= "(#__{vm}_product.product_parent_id='' OR #__{vm}_product.product_parent_id='0') AND vendor_id='".$_SESSION['ps_vendor_id']."' ";
	$q .= "AND #__{vm}_product.product_publish='Y' ";
	$q .= "AND #__{vm}_product.product_special='Y' ";
	if( CHECK_STOCK && PSHOP_SHOW_OUT_OF_STOCK_PRODUCTS != "1") {
		$q .= " AND product_in_stock > 0 ";
	}
	$q .= "ORDER BY RAND() LIMIT 0, $max_items";
}
$db->query($q);
if( $db->num_rows() > 0 ) {
	$width = intval(100 / intval($db->num_rows()));
	?>
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<?php
	$i = 0;
	while($db->next_record() ){
		if ($i%2)
		$sectioncolor = "sectiontableentry2";
		else
		$sectioncolor = "sectiontableentry1";

		if( $display_style == "vertical" ) {
        ?>
			<tr align="center" class="<?php echo $sectioncolor ?>">
				<td width="<?php echo $width ?>%">
					<?php 
					$ps_product->show_snapshot($db->f("product_sku"), $show_price, $show_addtocart);
					?><br />
				</td>
			</tr>
		<?php
		}
		elseif( $display_style== "horizontal" ) {
			if( $i == 0 )
			echo "<tr>\n";
			echo "<td width=\"$width%\" align=\"center\">";
			$ps_product->show_snapshot($db->f("product_sku"), $show_price, $show_addtocart);
			echo "</td>\n";
			if( ($i+1) == $db->num_rows() )
			echo "</tr>\n";
		}
		elseif( $display_style== "table" ) {
			if( $i == 0 )
			echo "<tr>\n";
			echo "<td width=\"$width%\" align=\"center\">";
			$ps_product->show_snapshot($db->f("product_sku"), $show_price, $show_addtocart);
			echo "</td>\n";
			if ( ($i+1) % $products_per_row == 0)
			echo "</tr><tr>\n";
			if( ($i+1) == $max_items )
			echo "</tr>\n";
		}
		$i++;
	}
?>
</table>
<?php
}
?>