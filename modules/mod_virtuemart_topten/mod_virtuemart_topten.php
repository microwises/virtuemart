<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
* Best selling Products module for VirtueMart
* @version $Id$
* @package VirtueMart
* @subpackage modules
*
* @copyright (C) John Syben (john@webme.co.nz)
* Conversion to Mambo and the rest:
* 	@copyright (C) 2004-2005 Soeren Eberhardt
*
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*----------------------------------------------------------------------
* This code creates a list of the bestselling products
* and displays it wherever you want
*----------------------------------------------------------------------
*/
global $mosConfig_absolute_path, $sess;

// Load the virtuemart main parse code
if( file_exists(dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' )) {
	require_once( dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' );
} else {
	require_once( dirname(__FILE__).'/../components/com_virtuemart/virtuemart_parser.php' );
}

require_once(CLASSPATH.'ps_product.php');
$ps_product = new ps_product;

// change the number of items you wanna haved listed via module parameters
$num_topsellers = $params->get ('num_topsellers', 10);

$list  = "SELECT p.product_id, p.product_sales as sales, p.product_parent_id, p.product_name, c.category_id, c.category_flypage ";
$list .= "FROM #__{vm}_product p, #__{vm}_product_category_xref pc, #__{vm}_category c WHERE ";
$q = "p.published='1' AND ";
$q .= "pc.product_id = p.product_id AND ";
$q .= "pc.category_id = c.category_id AND ";
$q .= "p.product_sales > 0 ";
$q .= "GROUP BY p.product_id ";
$q .= "ORDER BY sales DESC, p.product_name ";
$list .= $q . " LIMIT 0, $num_topsellers "; 

$db = new ps_DB;
$db->query($list);

$tt_item=0;
$i = 0;
?>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
<?php

  while ($db->next_record()) {
      if ($i == 0) {
          $sectioncolor = "sectiontableentry2";
          $i += 1;
      }
      else {
          $sectioncolor = "sectiontableentry1";
          $i -= 1;
      } 
      if( !$db->f('category_flypage') ) {
      	$flypage = ps_product::get_flypage( $db->f('product_id'));
      }
      else {
      	$flypage = $db->f('category_flypage');
      }
      $tt_item++;
      $pid = $db->f("product_parent_id") ? $db->f("product_parent_id") : $db->f("product_id");

      ?>
    <tr class="<?php echo $sectioncolor ?>">
      <td width="15%"><?php printf("%02d", $tt_item); ?></td>
      <td width="85%">
        <a href="<?php  $sess->purl(URL . "index.php?page=shop.product_details&flypage=$flypage&product_id=" . $pid . "&category_id=" . $db->f("category_id")) ?>">
            <?php $db->p("product_name"); ?>
        </a>
      </td>
    </tr>
    <?php 
  } ?>
</table>

<!--Top 10 End-->
