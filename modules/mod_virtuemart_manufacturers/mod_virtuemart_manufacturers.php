<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* Manufacturer Module
*
* NOTE: THIS MODULE REQUIRES THE VIRTUEMART COMPONENT!
/*
* @version $Id$
* @package VirtueMart
* @subpackage modules
*
* @copyright (C) 2004-2007 Soeren Eberhardt
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/

global $mosConfig_absolute_path, $sess, $VM_LANG;
// Load the virtuemart main parse code
if( file_exists(dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' )) {
	require_once( dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' );
} else {
	require_once( dirname(__FILE__).'/../components/com_virtuemart/virtuemart_parser.php' );
}
$text_before = $params->get( 'text_before', '');
$show_dropdown = $params->get( 'show_dropdown', 1);
$show_linklist = $params->get( 'show_linklist', 1);
$auto = $params->get( 'auto', 0);

$category_id = vmGet( $_REQUEST, 'category_id', '' );

$sess = new ps_session;


$query  = "SELECT DISTINCT m.manufacturer_id, m.mf_name
					FROM #__{vm}_manufacturer m
					LEFT JOIN #__{vm}_product_mf_xref mx ON mx.manufacturer_id = m.manufacturer_id
					LEFT JOIN #__{vm}_product p ON p.product_id = mx.product_id
					LEFT JOIN #__{vm}_product_category_xref cx ON cx.product_id = p.product_id
					WHERE cx.category_id = '$category_id' ";
$query .= "ORDER BY m.mf_name ASC";

$query_all  = "SELECT m.manufacturer_id,m.mf_name FROM #__{vm}_manufacturer m ";
$query_all .= "ORDER BY m.mf_name ASC";

$db = new ps_DB;
if ($auto == 1 && !empty( $category_id ) ) {
	$db->query( $query );
} else {
	$db->query( $query_all );
}
$res = $db->record;
if( empty( $res )) {
	if( $auto == 1 ) {
		$db->query( $query_all );
		$res = $db->record;
	} else {
		echo 'No manufacturers defined!';
		return;
	}
}
?>
<?php if( $show_linklist == 1 ) { ?>
  <!--BEGIN manufacturer DropDown List --> 
	<?php echo $text_before ?><br />
     
        <?php foreach( $res as $manufacturer) { ?>
            <div><a href="<?php echo $sess->url( URL."index.php?option=com_virtuemart&page=shop.browse&manufacturer_id=". $manufacturer->manufacturer_id ) ?>">
                    <?php echo $manufacturer->mf_name; ?>
                    </a>
            </div>
        <?php } ?>

<?php 
}
if( $show_dropdown == 1 ) { ?>
  <div> 
  	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" name="vm_manufacturer">
        <br/>
        <select class="inputbox" name="manufacturer_id" onchange="document.vm_manufacturer.submit()">
            <option value=""><?php echo $VM_LANG->_('PHPSHOP_SELECT') ?></option>
        <?php  
        foreach ($res as $manufacturer) { 
                $selected = '';
                if( @$_REQUEST['manufacturer_id'] == $manufacturer->manufacturer_id ) {
                        $selected = 'selected="selected"';      
                }
                echo "<option value=\"".$manufacturer->manufacturer_id ."\" $selected>". $manufacturer->mf_name ."</option>\n";

        } 
        ?>
        </select>
    <br />
      <input class="button" type="submit" name="manufacturerSearch" value="<?php echo $VM_LANG->_('PHPSHOP_SEARCH_TITLE') ?>" />
	    <input type="hidden" name="option" value="com_virtuemart" />
	    <input type="hidden" name="page" value="shop.browse" />
	    <input type="hidden" name="Itemid" value="<?php echo $sess->getShopItemid() ?>" />
      </form>
  </div>
<?php 
} 
?>
<!-- End Manufacturer Module --> 
