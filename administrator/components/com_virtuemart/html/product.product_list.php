<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: product.product_list.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

/* ROLANDD: MVC TEST START */
require(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'product.php');
$productlist = new VirtueMartModelProduct();
require(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'product_files.php');
require(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'productreviews.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');

/* Handle any publish/unpublish */
switch (JRequest::getVar('task')) {
	case 'publish':
	case 'unpublish':
		$productlist->setPublish();
		break;
}
$productlist->getProductList();

require(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'product'.DS.'tmpl'.DS.'product.php');

/* ROLANDD: MVC TEST END */
if (0) {
	mm_showMyFileName( __FILE__ );
	require_once( CLASSPATH .'ps_product_files.php');
	require_once( CLASSPATH .'pageNavigation.class.php' );
	
	global $ps_product, $ps_product_category,$hVendor;
	
	$ps_product = new ps_product();
	$keyword = JRequest::getVar( 'keyword' );
	
	//The vmGet didnt worked for this purpose by Max Milbers 
	$vendor = $hVendor -> getLoggedVendor();
	
	$product_parent_id = JRequest::getVar( 'product_parent_id', null);
	
	$product_type_id = JRequest::getVar( 'product_type_id', null); // Changed Product Type
	
	$search_date = JRequest::getVar( 'search_date', null); // Changed search by date
	
	
	$now = getdate();
	$nowstring = $now["hours"].":".$now["minutes"]." ".$now["mday"].".".$now["mon"].".".$now["year"];
	if(isset($_REQUEST['search_order']) && @$_REQUEST['search_order'] == '<') {
		$search_order = '<';
	}
	else {
		$search_order = '>';
	}
	$search_type = JRequest::getVar( 'search_type', 'product');
	
	
	// uuuh, we're using modern methods.
	vmCommonHTML::loadExtjs(); // Having a modal window is good
	?>
	<div align="right">
	
		<form style="float:right;" action="<?php $_SERVER['PHP_SELF'] ?>" method="get"><?php echo JText::_('VM_PRODUCT_LIST_SEARCH_BY_DATE') ?>&nbsp;
			  <select class="inputbox" name="search_type">
			  <option value="product"><?php echo JText::_('VM_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_PRODUCT') ?></option>
				  <option value="price" <?php echo $search_type == "price" ? 'selected="selected"' : ''; ?>><?php echo JText::_('VM_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_PRICE') ?></option>
				  <option value="withoutprice" <?php echo $search_type == "withoutprice" ? 'selected="selected"' : ''; ?>><?php echo JText::_('VM_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_WITHOUTPRICE') ?></option>
			  </select>
			  <select class="inputbox" name="search_order">
				  <option value="&lt;"><?php echo JText::_('VM_PRODUCT_LIST_SEARCH_BY_DATE_BEFORE') ?></option>
				  <option value="&gt;" <?php echo $search_order == ">" ? 'selected="selected"' : ''; ?>><?php echo JText::_('VM_PRODUCT_LIST_SEARCH_BY_DATE_AFTER') ?></option>
			  </select>
			  <input type="hidden" name="option" value="com_virtuemart" />
			  <input class="inputbox" type="text" size="15" name="search_date" value="<?php echo JRequest::getVar( 'search_date', $nowstring) ?>" />
			  <input type="hidden" name="page" value="product.product_list" />
			  <input class="button" type="submit" name="search" value="<?php echo JText::_('VM_SEARCH_TITLE')?>" />
		</form>
		<br/>
	</div>
	<?php
	
	
	
	$search_sql = " (#__{vm}_product.product_name LIKE '%$keyword%' OR \n";
	$search_sql .= "#__{vm}_product.vendor_id LIKE '%$keyword%' OR \n";
	$search_sql .= "#__{vm}_product.product_sku LIKE '%$keyword%' OR \n";
	$search_sql .= "#__{vm}_product.product_s_desc LIKE '%$keyword%' OR \n";
	$search_sql .= "#__{vm}_product.product_desc LIKE '%$keyword%'";
	$search_sql .= ") \n";
	
	// Check to see if this is a search or a browse by category
	// Default is to show all products
	if (!empty($category_id) && empty( $product_parent_id)) {
		$list  = "SELECT #__{vm}_category.category_name,#__{vm}_product.product_id,#__{vm}_product.vendor_id,#__{vm}_product.product_name,#__{vm}_product.product_sku,#__{vm}_product.vendor_id,product_publish, product_list, product_full_image, product_thumb_image";
		$list .= " FROM #__{vm}_product, #__{vm}_product_category_xref, #__{vm}_category WHERE ";
		$count  = "SELECT count(*) as num_rows FROM #__{vm}_product, #__{vm}_product_category_xref, #__{vm}_category WHERE ";
	
		$q = "#__{vm}_product_category_xref.category_id='$category_id' ";
		$q .= "AND #__{vm}_category.category_id=#__{vm}_product_category_xref.category_id ";
		$q .= "AND #__{vm}_product.product_id=#__{vm}_product_category_xref.product_id ";
		$q .= "AND #__{vm}_product.product_parent_id='' ";
		if (!$perm->check("admin")) {
			$q  .= "AND #__{vm}_product.vendor_id = '$vendor' ";
		}
		
		if( !empty( $keyword)) {
			$q .= " AND $search_sql";
		}
		$count .= $q;
		$q .= "ORDER BY product_list, product_publish DESC,product_name ";
		
	}
	elseif (!empty($keyword)) {
		$list  = "SELECT * FROM #__{vm}_product WHERE ";
		$count = "SELECT COUNT(*) as num_rows FROM #__{vm}_product WHERE ";
		$q = $search_sql;
		$q .= "AND #__{vm}_product.product_parent_id='' ";
		if (!$perm->check("admin")) {
			$q  .= "AND #__{vm}_product.vendor_id = '$vendor' ";
		}
	
		$count .= $q;
		$q .= " ORDER BY product_publish DESC,product_name ";
	}
	elseif (!empty($product_parent_id)) {
		$list  = "SELECT * FROM #__{vm}_product WHERE ";
		$count = "SELECT COUNT(*) as num_rows FROM #__{vm}_product WHERE ";
		$q = "product_parent_id='$product_parent_id' ";
		$q .= !empty($vendor) ? "AND #__{vm}_product.vendor_id='$vendor'" : "";
		if( !empty( $keyword)) {
			$q .= " AND $search_sql";
		}
		//$q .= "AND #__{vm}_product.product_id=#__{vm}_product_reviews.product_id ";
		//$q .= "AND #__{vm}_category.category_id=#__{vm}_product_category_xref.category_id ";
		$count .= $q;
		$q .= " ORDER BY product_publish DESC,product_name ";
	}
	/** Changed Product Type - Begin */
	elseif (!empty($product_type_id)) {
		$list  = "SELECT * FROM #__{vm}_product,#__{vm}_product_product_type_xref WHERE ";
		$count = "SELECT count(*) as num_rows FROM #__{vm}_product,#__{vm}_product_product_type_xref WHERE ";
		$q = "#__{vm}_product.product_id=#__{vm}_product_product_type_xref.product_id ";
		$q .= "AND product_type_id='$product_type_id' ";
		if (!$perm->check("admin")) {
			$q  .= "AND #__{vm}_product.vendor_id = '$vendor' ";
		}
	
		if( !empty( $keyword)) {
			$q .= " AND $search_sql";
		}
		$q .= " ORDER BY product_publish DESC,product_name ";
		$count .= $q;
	}  /** Changed Product Type - End */
	/** Changed search by date - Begin */
	elseif (!empty($search_date)) {
		list($time,$date) = explode(" ",$search_date);
		list($d["search_date_hour"],$d["search_date_minute"]) = explode(":",$time);
		list($d["search_date_day"],$d["search_date_month"],$d["search_date_year"]) = explode(".",$date);
		$d["search_date_use"] = true;
		if (process_date_time($d,"search_date",JText::_('VM_SEARCH_LBL'))) {
			$date = $d["search_date"];
			switch( $search_type ) {
				case "product" :
					$list  = "SELECT * FROM #__{vm}_product WHERE ";
					$count = "SELECT COUNT(*) as num_rows FROM #__{vm}_product WHERE ";
				break;
				case "withoutprice" :
				case "price" :
				$list  = "SELECT DISTINCT #__{vm}_product.product_id,product_name,product_sku,vendor_id,";
				$list .= "product_publish,product_parent_id FROM #__{vm}_product ";
				$list .= "LEFT JOIN #__{vm}_product_price ON #__{vm}_product.product_id = #__{vm}_product_price.product_id WHERE ";
				$count = "SELECT DISTINCT count(*) as num_rows FROM #__{vm}_product ";
				$count.= "LEFT JOIN #__{vm}_product_price ON #__{vm}_product.product_id = #__{vm}_product_price.product_id WHERE ";
				break;
			}
			$where = array();
			//         $where[] = "#__{vm}_product.product_parent_id='0' ";
			if (!$perm->check("admin")) {
				$where[] = " #__{vm}_product.vendor_id = '$vendor' ";
			}
	
			$q = "";
			switch( $search_type ) {
				case "product" :
				$where[] = "#__{vm}_product.mdate ". $search_order . " $date ";
				break;
				case "price" :
				$where[] = "#__{vm}_product_price.mdate ". $search_order . " $date ";
				$q = "GROUP BY #__{vm}_product.product_sku ";
				break;
				case "withoutprice" :
				$where[] = "#__{vm}_product_price.mdate IS NULL ";
				$q = "GROUP BY #__{vm}_product.product_sku ";
				break;
			}
	
			$q = implode(" AND ",$where) . $q . " ORDER BY #__{vm}_product.product_publish DESC,#__{vm}_product.product_name ";
			$count .= $q;
		}
		else {
			echo "<script type=\"text/javascript\">alert('".$d["error"]."')</script>\n";
		}
	}
	/** Changed search by date - End */
	else {
		$list  = "SELECT * FROM #__{vm}_product WHERE ";
		$count = "SELECT COUNT(*) as num_rows FROM #__{vm}_product WHERE ";
		$q = "product_parent_id='0' ";
		if (!$perm->check("admin")) {
			$q  .= "AND #__{vm}_product.vendor_id = '$vendor' ";
		}
	
		//$q .= "AND #__{vm}_product.product_id=#__{vm}_product_reviews.product_id ";
		//$q .= "AND #__{vm}_category.category_id=#__{vm}_product_category_xref.category_id ";
		$count .= $q;
		$q .= " ORDER BY product_publish DESC,product_name ";
	}
	$GLOBALS['vmLogger']->debug('The query in product.product_list: '.$count);
	$db = new ps_DB();
	$db->query($count);
	$db->next_record();
	$num_rows = $db->f("num_rows");
	
	// Create the Page Navigation
	$pageNav = new vmPageNav( $num_rows, $limitstart, $limit );
	
	$limitstart = $pageNav->limitstart;
	$list .= $q . " LIMIT $limitstart, " . $limit;
	
	if ($num_rows > 0) {
		$db->query($list);
		$num_rows = $db->num_rows();
	}
	
	// Create the List Object with page navigation
	$listObj = new listFactory( $pageNav );
	
	// print out the search field and a list heading
	$listObj->writeSearchHeader(JText::_('VM_PRODUCT_LIST_LBL'), VM_ADMIN_ICON_URL.'icon_48/vm_product_code_48.png', "product", "product_list");
	
	echo JText::_('VM_FILTER') ?>:
	 <select class="inputbox" id="category_id" name="category_id" onchange="window.location='<?php echo $_SERVER['PHP_SELF'] ?>?option=com_virtuemart&page=product.product_list&category_id='+document.getElementById('category_id').options[selectedIndex].value;">
		<option value=""><?php echo JText::_('SEL_CATEGORY') ?></option>
		<?php
		$ps_product_category->list_tree( $category_id );
		?>
	</select>
	<?php 
	echo vmToolTip( JText::_('VM_PRODUCT_LIST_REORDER_TIP') );
			 
	// start the list table
	$listObj->startTable();
	
	// these are the columns in the table
	$columns = Array(  '#' => '',
					"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".$num_rows.")\" />" => "",
					JText::_('VM_PRODUCT_LIST_NAME') => "width=\"30%\"",
					JText::_('VM_PRODUCT_LIST_VENDOR_NAME') => "width=\"30%\"",
					JText::_('VM_PRODUCT_LIST_MEDIA') => 'width="5%"',
					JText::_('VM_PRODUCT_LIST_SKU') => "width=\"15%\"",
					JText::_('VM_PRODUCT_PRICE_TITLE') => "width=\"10%\"",
					JText::_('VM_CATEGORY') => "width=\"15%\"" );
	
	// Only show reordering fields when a category ID is selected!
	if( $category_id ) {
		$columns[JText::_('VM_FIELDMANAGER_REORDER')] ="width=\"5%\"";
		$columns[vmCommonHTML::getSaveOrderButton( $num_rows, 'changeordering' )] ='width="8%"';
	}
	$columns[JText::_('VM_MANUFACTURER_MOD')] ="width=\"10%\"";
	$columns[JText::_('VM_REVIEWS')] ="width=\"10%\"";
	$columns[JText::_('VM_PRODUCT_LIST_PUBLISH')] ="";
	$columns[JText::_('VM_PRODUCT_CLONE')] = "";
	$columns[JText::_('E_REMOVE')] = "width=\"5%\"";
	$columns['Id'] = '';
	
	$listObj->writeTableHeader( $columns );
	
	if ($num_rows > 0) {
	
		$i = 0;
		$db_cat = new ps_DB;
		$dbtmp = new ps_DB;
		
		while ($db->next_record()) {
	
			$listObj->newRow();
	
			// The row number
			$listObj->addCell( $pageNav->rowNumber( $i ) );
			
			// The Checkbox
			$listObj->addCell( vmCommonHTML::idBox( $i, $db->f("product_id"), false, "product_id" ) );
			
			$link = $_SERVER['PHP_SELF'] . "?page=$modulename.product_form&limitstart=$limitstart&keyword=".urlencode($keyword) . 
							"&product_id=" . $db->f("product_id")."&product_parent_id=".$product_parent_id;
			if( $vmLayout != 'standard' ) {
			$link .= "&no_menu=1&tmpl=component";
			$link = defined('_VM_IS_BACKEND') 
							? str_replace('index2.php', 'index3.php', str_replace('index.php', 'index3.php', $link )) 
							: str_replace('index.php', 'index2.php', $link );
			}
			$link = $sess->url( $link );
			
			$text = shopMakeHtmlSafe($db->f("product_name"));
			
			// The link to the product form / to the child products
			if( $vmLayout == 'standard') {
				$tmpcell = vmCommonHTML::hyperLink( $link, $text, '', 'Edit: '.$text );
			} else {
				$tmpcell = vmCommonHTML::hyperLink($link, $text, '', 'Edit: '.$text, 'onclick="parent.addSimplePanel( \''.$db->getEscaped($db->f("product_name")).'\', \''.$link.'\' );return false;"');
			}		
			if( $ps_product->parent_has_children( $db->f("product_id") ) ) {
				$tmpcell .= "&nbsp;&nbsp;&nbsp;<a href=\"";
				$link = $sess->url($_SERVER['PHP_SELF'] . "?page=$modulename.product_list&product_parent_id=" . $db->f("product_id"));
				if( $vmLayout != 'standard' ) {
					$link .= "&no_menu=1&tmpl=component";
					$link = defined('_VM_IS_BACKEND') 
								? str_replace('index2.php', 'index3.php', str_replace('index.php', 'index3.php', $link )) 
								: str_replace('index.php', 'index2.php', $link );
				}
				$tmpcell .= $link;
				$tmpcell .=  "\">[ ".JText::_('VM_PRODUCT_FORM_ITEM_INFO_LBL'). " ]</a>";
			}
			$listObj->addCell( $tmpcell );
			
			//Product Vendor nick by Max Milbers
			$product_vendor_id = $db->f("vendor_id");
			if($product_vendor_id==0){
				$listObj->addCell( "Set a nick for this vendor!" );
			}else{
				$dbtmp = ps_vendor::get_vendor_fields($product_vendor_id, array("vendor_name"),"");
				if(empty($dbtmp)){
					echo("product.product_list ps_vendor::get_vendor_fields for vendor_name failed ");
				}
				$listObj->addCell( $dbtmp->f("vendor_name") );
			}
			
			// Product Media Link
			$numFiles = ps_product_files::countFilesForProduct($db->f('product_id'));
			if( $db->f('product_full_image')) $numFiles++;
			if( $db->f('product_thumb_image')) $numFiles++;
			$link = $sess->url( $_SERVER['PHP_SELF']. '?page=product.file_list&product_id='.$db->f('product_id').'&no_menu=1' );
			$link = defined('_VM_IS_BACKEND') 
							? str_replace('index2.php', 'index3.php', str_replace('index.php', 'index3.php', $link )) 
							: str_replace('index.php', 'index2.php', $link );
			$text = '<img src="'.$mosConfig_live_site.'/includes/js/ThemeOffice/media.png" align="middle" border="0" />&nbsp;('.$numFiles.')';
			$tmpcell = vmPopupLink( $link, $text, 800, 540, '_blank', '', 'screenX=100,screenY=100' );
			$listObj->addCell( $tmpcell );
			
			// The product sku
			$listObj->addCell( $db->f("product_sku") );
			
			// The product Price
			$price = $ps_product->getPriceByShopperGroup( $db->f('product_id'), '');
			$tmp_cell = '<span class="editable priceform">'.$GLOBALS['CURRENCY_DISPLAY']->getValue( $price['product_price']).' '.$price['product_currency'];
			$tmp_cell .= '&nbsp;&nbsp;&nbsp;</span>';
			
			$listObj->addCell( $tmp_cell, 'id="'.$db->f('product_id').'" onclick="showPriceForm(this.id)" title="'.JText::_('VM_PRICE_FORM_LBL').'"' );
			
			// The Categories or the parent product's name
			$tmpcell = "";
			if( empty($product_parent_id) ) {
				$db_cat->query("SELECT #__{vm}_category.category_id, category_name FROM #__{vm}_category,#__{vm}_product_category_xref
								WHERE #__{vm}_category.category_id=#__{vm}_product_category_xref.category_id
								AND #__{vm}_product_category_xref.product_id='".$db->f("product_id") ."'");
				while($db_cat->next_record()) {
					$tmpcell .= $db_cat->f("category_name") . "<br/>";
				}
			}
			else {
				$tmpcell .= JText::_('VM_CATEGORY_FORM_PARENT') .": <a href=\"";
				$url = $_SERVER['PHP_SELF'] . "?page=$modulename.product_form&limitstart=$limitstart&keyword=".urlencode($keyword)."&product_id=$product_parent_id";
				$tmpcell .= $sess->url( $url );
				$tmpcell .= "\">".$ps_product->get_field($product_parent_id,"product_name"). "</a>";
			}
			$listObj->addCell( $tmpcell );
	
			if( $category_id ) {
				$tmp_cell = "<div align=\"center\">"
				. $pageNav->orderUpIcon( $i, $i > 0, "orderup", JText::_('CMN_ORDER_UP'), $page, "changeordering" )
				. "\n&nbsp;"
				. $pageNav->orderDownIcon( $i, $db->num_rows(), $i-1 <= $db->num_rows(), 'orderdown', JText::_('CMN_ORDER_DOWN'), $page, "changeordering" )
				. "</div>";
				$listObj->addCell( $tmp_cell );
	
				$listObj->addCell( vmCommonHTML::getOrderingField( $db->f('product_list') ) );
			}
			$listObj->addCell( $ps_product->get_mf_name($db->f("product_id")) );
	
			$db_cat->query("SELECT count(*) as num_rows FROM #__{vm}_product_reviews WHERE product_id='".$db->f("product_id")."'");
			$db_cat->next_record();
			if ($db_cat->f("num_rows")) {
				$link = $_SERVER["PHP_SELF"]."?option=com_virtuemart&page=product.review_list&product_id=".$db->f("product_id");
				if( $vmLayout != 'standard' ) {
					$link .= "&no_menu=1&tmpl=component";
					$link = defined('_VM_IS_BACKEND') 
								? str_replace('index2.php', 'index3.php', str_replace('index.php', 'index3.php', $link )) 
								: str_replace('index.php', 'index2.php', $link );
				}
				$tmpcell = $db_cat->f("num_rows")."&nbsp;";
				$tmpcell .= "<a href=\"".$link."\">";
				$tmpcell .= "[".JText::_('VM_SHOW')."]</a>";
			}
			else {
				$link = $sess->url( $_SERVER['PHP_SELF'].'?page=product.review_form&product_id='.$db->f('product_id'));
				if( $vmLayout != 'standard' ) {
					$link .= "&no_menu=1&tmpl=component";
					$link = defined('_VM_IS_BACKEND') 
								? str_replace('index2.php', 'index3.php', str_replace('index.php', 'index3.php', $link )) 
								: str_replace('index.php', 'index2.php', $link );
				}
				$text = '['.JText::_('VM_REVIEW_FORM_LBL').']';
				$tmpcell = " - <a href=\"$link\">$text</a>\n";
			}
			$listObj->addCell( $tmpcell );
	
			$tmpcell = "<a href=\"". $sess->url( $_SERVER['PHP_SELF']."?page=product.product_list&category_id=$category_id&product_id=".$db->f("product_id")."&func=changePublishState" );
			if ($db->f("product_publish")=='N') {
				$tmpcell .= "&task=publish\">";
			}
			else {
				$tmpcell .= "&task=unpublish\">";
			}
			$tmpcell .= vmCommonHTML::getYesNoIcon( $db->f("product_publish"), JText::_('CMN_PUBLISH'), JText::_('CMN_UNPUBLISH') );
			$tmpcell .= "</a>";
			$listObj->addCell( $tmpcell );
	
			$tmpcell = "<a title=\"".JText::_('VM_PRODUCT_CLONE')."\" onmouseout=\"MM_swapImgRestore();\"  onmouseover=\"MM_swapImage('copy_$i','','". IMAGEURL ."ps_image/copy_f2.gif',1);\" href=\"";
			$url = $_SERVER['PHP_SELF'] . "?page=$modulename.product_form&clone_product=1&limitstart=$limitstart&keyword=".urlencode($keyword)."&product_id=" . $db->f("product_id");
			if( !empty($product_parent_id) )
			$url .= "&product_parent_id=$product_parent_id";
			$tmpcell .= $sess->url( $url );
			$tmpcell .= "\"><img src=\"".IMAGEURL."/ps_image/copy.gif\" name=\"copy_$i\" border=\"0\" alt=\"".JText::_('VM_PRODUCT_CLONE')."\" /></a>";
			$listObj->addCell( $tmpcell );
	
			$listObj->addCell( $ps_html->deleteButton( "product_id", $db->f("product_id"), "productDelete", $keyword, $limitstart ) );
	
			$listObj->addCell( $db->f('product_id') );
			$i++;
			
		}
		
	}
	
	$listObj->writeTable();
	
	$listObj->endTable();
	
	$listObj->writeFooter( $keyword,  "&product_parent_id=$product_parent_id&category_id=$category_id&product_type_id=$product_type_id&search_date=$search_date");
	
	$path = defined('_VM_IS_BACKEND' ) ? '/administrator/' : '/';
	?>
	<script type="text/javascript">
	var priceDlg = null;
	function showPriceForm(prodId) {
		
		// define some private variables
		var showBtn;
		sUrl = '<?php $sess->purl( $mm_action_url .'index3.php?page=product.ajax_tools&task=getPriceForm&no_html=1', false, false, true ) ?>&product_id=' + prodId;
		callback = { success : function(o) { 
			
							priceDlg = Ext.Msg.show({
								width:300,
								height:250,
							   title:'<?php echo JText::_('VM_PRICE_FORM_LBL') ?>',
							   msg: o.responseText,
							   buttons: Ext.Msg.OKCANCEL,
							   fn: handleResult
						   });
		}};	
		Ext.Ajax.request({method:'GET', url: sUrl, success: callback.success });
	}
	
	function handleResult( btn ) {
		switch( btn ) {
			case 'ok':
				submitPriceForm( 'priceForm' );
				break;
			case 'cancel':
				break;
		}
	}
	function submitPriceForm(formId) {	
		// define some private variables
		var dialog, showBtn, hideTask;
	   
		function showDialog( content ) {
			var msgbox = Ext.Msg.show( { 
						title: '<?php echo JText::_('PEAR_LOG_NOTICE') ?>',
						msg: content,
						autoCreate: true,
						width:300,
						height:150,
						fn: msgBoxClick,
						modal: false,
						resizable: false,
						buttons: Ext.Msg.OK,
						shadow:true,
						animEl:Ext.get( 'vm-toolbar' )
				});
			// This Dialog shows the result of the price update. We want it to autohide after 3000 seconds
			// Here we need to use "DelayedTask" because we need to cancel the autohide function if the user clicked
			// the dialog away
			hideTask = new Ext.util.DelayedTask(msgbox.hide, msgbox);
			hideTask.delay( 3000 );
		}
	
		var msgBoxClick = function(result) {
			if( result == 'ok' ) {
				hideTask.cancel();
			}
		};
		// return a public interface
		var callback = {
			success: function(o) {
				//Ext.DomHelper.insertHtml( document.body, o.responseText );
				
				showDialog( o.responseText );
			},
			failure: function(o) {
				Ext.Msg.alert('Error!', 'Something went wrong while posting the form data (possibly 404 error).');
			},
			upload : function(o){
				//Ext.DomHelper.insertHtml( 'beforeEnd', document.body, o.responseText );
				showDialog( o.responseText );
			}
		};
		
		Ext.Ajax.request({method:'POST', url: '<?php echo $_SERVER['PHP_SELF'] ?>', success: callback.success, failure: callback.failure, form: formId});
		
	}
	function cancelPriceForm(id) {
		updatePriceField( id );
	}
	function updatePriceField( id ) {	
		sUrl = '<?php  $sess->purl( $mm_action_url .'index3.php?option=com_virtuemart&no_html=1&page=product.ajax_tools&task=getpriceforshoppergroup&formatPrice=1', false, false, true ) ?>&product_id=' + id;
		callback = { success : function(o) { Ext.get("priceform-dlg").innerHTML = o.responseText;	}};
		Ext.Ajax.request({method:'GET', url: sUrl, success:callback.success });
	}
	function reloadForm( parentId, keyName, keyValue ) {
		sUrl = '<?php  $sess->purl( $mm_action_url .'index3.php?option=com_virtuemart&no_html=1&page=product.ajax_tools&task=getPriceForm', false, false, true ) ?>&product_id='+parentId+'&'+keyName+'='+keyValue;
		callback = { success : function(o) { priceDlg.updateText( o.responseText) }};
		Ext.Ajax.request({method:'GET', url: sUrl, success:callback.success });
	}
	</script>
	<?php 
	$formName = uniqid('priceForm');
	?>
	<div id="priceform-dlg"></div>
<?php } ?>
