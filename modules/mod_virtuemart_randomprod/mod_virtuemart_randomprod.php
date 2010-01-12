<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/* Random Products Module
*
* @version $Id$
* @package VirtueMart
* @subpackage modules
* @copyright (C) Mr PHP
// W: www.mrphp.com.au
// E: info@mrphp.com.au
// P: +61 418 436 690
* Conversion to VirtueMart:
* 	@copyright (C) 2004-2007 soeren
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

if( empty($max_items))
  $max_items = $params->get( 'max_items', 2 ); //maximum number of items to display
if( empty($category_id))
  $category_id = (int)$params->get( 'category_id', 0 ); // Display products from this category only
if( empty($display_style))
  $display_style = $params->get( 'display_style', "vertical" ); // Display Style
if( empty($products_per_row))
  $products_per_row = $params->get( 'products_per_row', 4 ); // Display X products per Row
if( empty($show_price))
  $show_price = (bool)$params->get( 'show_price', 1 ); // Display the Product Price?
if( empty($show_addtocart))
  $show_addtocart = (bool)$params->get( 'show_addtocart', 1 ); // Display the "Add-to-Cart" Link?

require_once( CLASSPATH. 'ps_product.php');
$ps_product = new ps_product;
$db=new ps_DB;
if ( $category_id ) {
	$q  = "SELECT DISTINCT product_sku FROM #__{vm}_product, #__{vm}_product_category_xref, #__{vm}_category WHERE ";
	$q .= "product_parent_id=''";
	$q .= "AND #__{vm}_product.product_id=#__{vm}_product_category_xref.product_id ";
	$q .= "AND #__{vm}_category.category_id=#__{vm}_product_category_xref.category_id ";
	$q .= "AND #__{vm}_category.category_id='$category_id'";
	$q .= "AND #__{vm}_product.published='1' ";
	if( CHECK_STOCK && PSHOP_SHOW_OUT_OF_STOCK_PRODUCTS != "1") {
		$q .= " AND product_in_stock > 0 ";
	}
  $q .= "ORDER BY product_name DESC";
}
else {
	$q  = "SELECT DISTINCT product_sku FROM #__{vm}_product WHERE ";
	$q .= "product_parent_id='' AND vendor_id='".$_SESSION['ps_vendor_id']."' ";
	$q .= "AND #__{vm}_product.published='1' ";
	if( CHECK_STOCK && PSHOP_SHOW_OUT_OF_STOCK_PRODUCTS != "1") {
		$q .= " AND product_in_stock > 0 ";
	}
	$q .= "ORDER BY product_name DESC";
}
$db->query($q);

$i=0;
while($db->next_record()){
  $prodlist[$i]=$db->f("product_sku");
  $i++;
}

if($db->num_rows() == 0) {
	return;
} ?>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <?php

srand ((double) microtime() * 10000000);

if (sizeof($prodlist) < $max_items) {
    $max_items = sizeof($prodlist);
}
if (sizeof($prodlist)>1) {
    $rand_prods = array_rand ($prodlist, $max_items);
} else {
  	$rand_prods = rand (4545.3545, $max_items);
}

if ($max_items==1) { 
		?>
        <tr align="center" class="sectiontableentry1">
			<td><?php $ps_product->show_snapshot($prodlist[$rand_prods], $show_price, $show_addtocart);  ?><br />
			</td>
		</tr><?php
}
else { 
	for($i=0; $i<$max_items; $i++) {
		$sectioncolor = $i%2 ? 'sectiontableentry2' : 'sectiontableentry1';
          
              
        if( $display_style == "vertical" ) {
        	?>
			<tr align="center" class="<?php echo $sectioncolor ?>">
				<td><?php $ps_product->show_snapshot($prodlist[$rand_prods[$i]], $show_price, $show_addtocart); ?><br />
				</td>
			</tr><?php
        }
        elseif( $display_style== "horizontal" ) {
        	if( $i == 0 ) {
        		echo "<tr>\n";
        	}
            echo "<td align=\"center\">";
            $ps_product->show_snapshot($prodlist[$rand_prods[$i]], $show_price, $show_addtocart);
            echo "</td>\n";
            if( ($i+1) == $max_items ) {
            	echo "</tr>\n";
          	}
        }
		elseif( $display_style== "table" ) {
			if( $i == 0 ) {
            	echo "<tr>\n";
            }
            echo "<td align=\"center\">";
            $ps_product->show_snapshot($prodlist[$rand_prods[$i]], $show_price, $show_addtocart);
            echo "</td>\n";
          	if( ($i+1) == $max_items ) {
            	echo "</tr>\n";
          	} elseif( ($i+1) % $products_per_row == 0) {
          		echo "</tr><tr>\n";
          	}  
        }
	}
}
?>
</table>