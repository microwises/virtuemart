<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
* List all files of a specific product
* @author Soeren Eberhardt
* @param int product_id
*
* @version $Id$
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*
*/
/* ROLANDD: MVC TEST START */
require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'models'.DS.'files.php');
$productfiles = new VirtuemartModelFiles();
/* Handle any publish/unpublish */
switch (JRequest::getVar('task')) {
	case 'publish':
	case 'unpublish':
		$productlist->setPublish();
		break;
}
$productfiles->getProductFilesList();

require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'views'.DS.'product_files'.DS.'tmpl'.DS.'product_files.php');

/* ROLANDD: MVC TEST END */
if (0) {
mm_showMyFileName( __FILE__ );

require_once( CLASSPATH . "pageNavigation.class.php" );
require_once( CLASSPATH . "htmlTools.class.php" );

global $option;

$task = JRequest::getVar( 'task' );
$product_id = JRequest::getVar( 'product_id', 0 );

$q = "SELECT product_id, product_name, product_full_image as file_name, product_thumb_image, product_publish FROM #__{vm}_product WHERE product_id=".intval($product_id); 
$db->query($q);
$db->next_record();
$product_name = $db->f("product_name");

$files = array();
if( $db->f('file_name')) {
	 $file = new stdClass();
	 $file->file_id = 'product_images';
	 $file->file_name = IMAGEPATH.'product/'.$db->f('file_name');
	 $file->product_name = $db->f('product_name');
	 $file->file_url = IMAGEURL.'product/'.$db->f('file_name');
	 $file->product_thumb_image = $db->f('product_thumb_image');
	 $file->file_title = $db->f('file_name');
	 $file->file_is_image = 1;
	 $file->file_product_id = $product_id;
	 $file->file_extension = strrchr($db->f('file_name'), '.');
	 $file->file_published = $db->f('product_publish');
	 $files[] = $file;
}

$dbf = new ps_DB;
$sql = 'SELECT attribute_value FROM #__{vm}_product_attribute WHERE `product_id` = '.$product_id.' AND attribute_name=\'download\'';
$dbf->query( $sql );
$downloadFiles = array();
while( $dbf->next_record() ) {
	$downloadFiles[] = $dbf->f('attribute_value');
}

$q = "SELECT file_id, file_is_image, file_product_id, file_extension, file_url, file_published, file_name, file_title, file_image_thumb_height, file_image_thumb_width FROM #__{vm}_product_files  ";
$q .= "WHERE file_product_id = '$product_id' ";
$q .= "ORDER BY file_is_image DESC";
$db->query($q);
$db->next_record();
if( !empty( $files)) {
	$db->record = array_merge( $files, $db->record );
}

if( $db->num_rows() < 1 && $task != "cancel" ) {
  	vmRedirect( $_SERVER['PHP_SELF']."?option=com_virtuemart&page=product.file_form&product_id=$product_id&no_menu=".@$_REQUEST['no_menu'] );
}
$db->reset();
$arr = array(); $arr2 = array();
while ($db->next_record()) {
	
	// Reorder the whole recordset and put pay-download files at the top
	$filename = $mosConfig_absolute_path. str_replace($mosConfig_absolute_path, '', $db->f("file_name") );
	$isProductDownload = in_array( basename($filename), $downloadFiles ) ? true : false;
	if( $isProductDownload ) {
		$arr[] = $db->getCurrentRow();
	}
	else {
		$arr2[] = $db->getCurrentRow();
	}
}

$db->record = array_merge( $arr, $arr2 );

$num_rows = $db->num_rows();

// Create the Page Navigation
$pageNav = new vmPageNav( $num_rows, $limitstart, $limit );

// Create the List Object with page navigation
$listObj = new listFactory( $pageNav );

// print out the search field and a list heading
$listObj->writeSearchHeader(JText::_('VM_FILES_LIST') ." " . $product_name, $mosConfig_live_site."/administrator/images/mediamanager.png", $modulename, "file_list");

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => 'width="20"', 
					"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll($num_rows)\" />" => 'width="20"',
					JText::_('VM_FILES_LIST_FILENAME') => '',
					JText::_('VM_FILES_LIST_ROLE') => '',
					JText::_('VM_VIEW') => '',
					JText::_('VM_FILES_LIST_FILETITLE') => '',
					JText::_('VM_FILES_LIST_FILETYPE') => '',
					JText::_('VM_FILEMANAGER_PUBLISHED') => '',
					JText::_('E_REMOVE') => "width=\"5%\""
				);
$listObj->writeTableHeader( $columns );

$roles= array( 'isDownlodable' => IMAGEURL.'ps_image/downloadable.gif',
				'isImage' => IMAGEURL.'ps_image/image.gif',
				'isProductImage' => IMAGEURL.'ps_image/image.png',
				'isFile' => IMAGEURL.'ps_image/attachment.gif',
				'isRemoteFile' => IMAGEURL.'ps_image/url.gif'
		);
// Reset Result pointer
$db->reset();

$i = 0;
while ($db->next_record()) {
	$filename = $mosConfig_absolute_path. str_replace($mosConfig_absolute_path, '', $db->f("file_name") );
	$listObj->newRow();
	
	// The row number
	$listObj->addCell( $pageNav->rowNumber( $i ) );
	
	$isProductDownload = in_array( basename($filename), $downloadFiles ) ? true : false;
	
	// The Checkbox
	$listObj->addCell( vmCommonHTML::idBox( $i, $db->f("file_id"), false, "file_id" ) );

	$tmp_cell = '';
	
	$tmp_cell = "<a href=\"".$sess->url( $_SERVER['PHP_SELF'].'?page=product.file_form&amp;product_id='.$product_id.'&amp;file_id='.$db->f("file_id")).'&amp;no_menu='.@$_REQUEST['no_menu'].'" title="'.JText::_('VM_MANUFACTURER_LIST_ADMIN').'">';
	$style = '';
	if($filename) {
		$role = $db->f("file_is_image") ? 'isImage' : 'isFile';
		if( $db->f('product_name')) {
			$role = 'isProductImage';
			$style = 'style="font-weight:bold;"';
		}
		if( $isProductDownload ) $role = 'isDownlodable';
		$tmp_cell .= basename($filename);
	}
	else {
		$role = 'isRemoteFile';
		$tmp_cell .= basename($db->f("file_url"));
	}
	$tmp_cell .= "</a>&nbsp;";
	
	$listObj->addCell( $tmp_cell, $style );	
	
	$tmp_cell = '<img src="'.$roles[$role].'" align="middle" title="'.$role.'" alt="'.$role.'" />';
	$listObj->addCell( $tmp_cell );	
	
	$tmp_cell = '';
	if( $db->f("file_is_image")) {
		$fullimg = $filename;
		$info = pathinfo( $fullimg );
		if( is_file( $fullimg ) ) {
			$tmp_cell .= JText::_('VM_FILES_LIST_FULL_IMG').": ";
			$tmp_cell .= '<a onclick="document.getElementById(\'file_form_iframe\').src=\''.$db->f("file_url") . '\';" href="#file_form" title="Click me!">[ '.JText::_('VM_VIEW') . ' ]</a>'; 
		}
		$tmp_cell .= '<br />';
		if( $db->f('product_thumb_image')) {
			$thumb = IMAGEPATH.'product/'.$db->f('product_thumb_image');
			$thumburl = IMAGEURL.'product/'.$db->f('product_thumb_image');
		}
		else {
			$thumb = $info["dirname"] ."/resized/". basename($filename,".".$info["extension"])."_".$db->f("file_image_thumb_height")."x".$db->f("file_image_thumb_width").".".$info["extension"];
			$thumburl = str_replace( $mosConfig_absolute_path, $mosConfig_live_site, $thumb );
		}
		
		if( is_file( $thumb ) ) {
			$tmp_cell .= JText::_('VM_FILES_LIST_THUMBNAIL_IMG').": ";
			$tmp_cell .= vmToolTip( '&nbsp;<img src="'.$thumburl.'" alt="thumbnail" />', JText::_('VM_FILES_LIST_THUMBNAIL_IMG'), '', '', '[ '.JText::_('VM_VIEW') . ' ]' ); 
		}
		if( !$db->f("file_name") ) {
			$tmp_cell = "&nbsp;<a target=\"_blank\" href=\"".$db->f("file_url"). "\">[ ".JText::_('VM_VIEW') . " ]</a><br/>"; 
		}
	}
	$listObj->addCell( $tmp_cell );
	
	$listObj->addCell( $db->f("file_title"));

	$listObj->addCell( $db->f("file_extension") );


	if( $db->f('file_id') == 'product_images' ) {
		if ($db->f("file_published")=="0") { 
			$tmp_cell = '<img src="'. $mosConfig_live_site .'/administrator/images/publish_x.png" border="0" alt="' . JText::_('CMN_PUBLISH') . '" />';
		} 
		else { 
			$tmp_cell = '<img src="'. $mosConfig_live_site .'/administrator/images/tick.png" border="0" alt="' . JText::_('CMN_UNPUBLISH') . '" />';
		} 
		$listObj->addCell( $tmp_cell );
	} else {
		$tmpcell = "<a href=\"". $sess->url( $_SERVER['PHP_SELF']."?page=product.file_list&file_id=" . $db->f("file_id") . "&product_id=$product_id&func=changePublishState" );
		if ($db->f("file_published")=='0') {
			$tmpcell .= "&task=publish\">";
		}
		else {
			$tmpcell .= "&task=unpublish\">";
		}
		$tmpcell .= vmCommonHTML::getYesNoIcon( $db->f("file_published"), JText::_('CMN_PUBLISH'), JText::_('CMN_UNPUBLISH') );
		$tmpcell .= "</a>";
		$listObj->addCell( $tmpcell );
	}

	$listObj->addCell( $ps_html->deleteButton( "file_id", $db->f("file_id"), "deleteProductFile", $keyword, $limitstart, "&product_id=$product_id" ) );
		
	$i++;

}
$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword,"&product_id=$product_id" );

?>
<br /><br />
<a name="file_form" href="#listheader">
<div id="file_form_container">
	<img align="middle" src="<?php echo $mosConfig_live_site ?>/administrator/images/restore_f2.png" border="0" alt="<?php echo JText::_('VM_FILES_LIST_GO_UP'); ?>" /><?php echo JText::_('VM_FILES_LIST_UP'); ?></a>
	<iframe id="file_form_iframe" src="" style="height: 1000px;" frameborder="0" width="100%"></iframe>
</div>
<?php } ?>