<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
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
*/
mm_showMyFileName( __FILE__ );
require_once( CLASSPATH . "pageNavigation.class.php" );
require_once( CLASSPATH . "htmlTools.class.php" );

$where = array();
if ( $keyword!= "" ) {
	$where[] = "(f.name LIKE '%$keyword%' OR f.type LIKE '%$keyword%')";
}

$db->setQuery( "SELECT COUNT(*)"
	. "\nFROM #__{vm}_userfield AS f "
	. (count( $where ) ? "\nWHERE " . implode( ' AND ', $where ) : "")
);
$total = $db->loadResult();
echo $db->getErrorMsg();

$pageNav = new vmPageNav( $total, $limitstart, $limit  );

// Create the List Object with page navigation
$listObj = new listFactory( $pageNav );

// print out the search field and a list heading
$listObj->writeSearchHeader(JText::_('VM_MANAGE_USER_FIELDS'), $mosConfig_live_site."/administrator/images/addusers.png", "admin", "user_field_list");

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => "", 
					"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".$pageNav->limit.")\" />" => "",
					JText::_('VM_FIELDMANAGER_NAME') => "width=\"20%\"",
					JText::_('VM_FIELDMANAGER_TITLE') => "width=\"20%\"",
					JText::_('VM_FIELDMANAGER_TYPE') => "width=\"10%\"",
					JText::_('VM_FIELDMANAGER_REQUIRED') => "width=\"5%\"",
					JText::_('VM_FIELDMANAGER_PUBLISHED') => "width=\"5%\"",
					JText::_('VM_FIELDMANAGER_SHOW_ON_REGISTRATION') => "width=\"5%\"",
					JText::_('VM_FIELDMANAGER_SHOW_ON_SHIPPING') => "width=\"5%\"",
					JText::_('VM_FIELDMANAGER_SHOW_ON_ACCOUNT') => "width=\"5%\"",
					JText::_('VM_FIELDMANAGER_REORDER') => "width=\"5%\"",
					vmCommonHTML::getSaveOrderButton( min($total - $pageNav->limitstart, $pageNav->limit ), 'changeordering' ) => 'width="8%"',
					JText::_('E_REMOVE') => "width=\"5%\""
				);
$listObj->writeTableHeader( $columns );

$db->query( "SELECT f.fieldid, f.sys, f.title, f.name, f.description, f.type, f.required, f.published, f.account, f.ordering, f.registration, f.shipping"
	. "\nFROM #__{vm}_userfield AS f"
	. (count( $where ) ? "\nWHERE " . implode( ' AND ', $where ) : "")
	. "\n ORDER BY f.ordering"
	. "\nLIMIT $pageNav->limitstart, $pageNav->limit"
);
// The list of fields which CAN'T BE UNPUBLISHED OR UNREQUIRED
$coreFields = array( 'username', 'email', 'password', 'password2' );

$i = 0;
while( $db->next_record() ) {
	
	$listObj->newRow();
	
	// The row number
	$listObj->addCell( $pageNav->rowNumber( $i ) );
		
	// The Checkbox
	$listObj->addCell( vmCommonHTML::idBox( $i, $db->f("fieldid"), 0, "fieldid" ) );
	
	$listObj->addCell( '<a href="'.$sess->url($_SERVER['PHP_SELF'].'?page=admin.user_field_form&fieldid='.$db->f('fieldid')).'">'.$db->f('name').'</a>' );
	$lang_string = $db->f('title');
	if( $lang_string[0] == '_' ) {
		$lang_string = substr( $lang_string, 1 );
	}
	$listObj->addCell( JText::_($lang_string) ? JText::_($lang_string) : $db->f('title') );
	$listObj->addCell( $db->f('type') );
	
	// Required?
	if( !in_array($db->f('name'), $coreFields )) {
		$tmp_cell = "<a href=\"". $sess->url( $_SERVER['PHP_SELF']."?page=admin.user_field_list&fieldid=".$db->f('fieldid')."&func=changePublishState&item=required" );
		$tmp_cell .= $db->f('required') ? "&task=unpublish\">" : $tmp_cell .= "&task=publish\">";
		$tmp_cell .= vmCommonHTML::getYesNoIcon( $db->f('required') );
		$tmp_cell .= "</a>";
	}
	else {
		$tmp_cell = vmCommonHTML::getYesNoIcon( $db->f('required') );
	}
	$listObj->addCell( $tmp_cell );  
	
	// Publish / Unpublish
	if( !in_array($db->f('name'), $coreFields )) {
		$tmp_cell = "<a href=\"". $sess->url( $_SERVER['PHP_SELF']."?page=admin.user_field_list&fieldid=".$db->f('fieldid')."&func=changePublishState&item=published" );
		$tmp_cell .= $db->f('published') ?	"&task=unpublish\">" : $tmp_cell .= "&task=publish\">";
		$tmp_cell .= vmCommonHTML::getYesNoIcon( $db->f('published') );
		$tmp_cell .= "</a>";
	}
	else {
		$tmp_cell = vmCommonHTML::getYesNoIcon( $db->f('published') );
	}
	$listObj->addCell( $tmp_cell );  
	
	// Show on registration?
	if( !in_array($db->f('name'), $coreFields )) {
		$tmp_cell = "<a href=\"". $sess->url( $_SERVER['PHP_SELF']."?page=admin.user_field_list&fieldid=".$db->f('fieldid')."&func=changePublishState&item=registration" );
		$tmp_cell .= $db->f('registration') ?	"&task=unpublish\">" : $tmp_cell .= "&task=publish\">";
		$tmp_cell .= vmCommonHTML::getYesNoIcon( $db->f('registration') );
		$tmp_cell .= "</a>";
	}
	else {
		$tmp_cell = vmCommonHTML::getYesNoIcon( $db->f('registration') );
	}
	$listObj->addCell( $tmp_cell );  
	
	// Show on shipping?
	if( !in_array($db->f('name'), $coreFields )) {
		$tmp_cell = "<a href=\"". $sess->url( $_SERVER['PHP_SELF']."?page=admin.user_field_list&fieldid=".$db->f('fieldid')."&func=changePublishState&item=shipping" );
		$tmp_cell .= $db->f('shipping') ?	"&task=unpublish\">" : $tmp_cell .= "&task=publish\">";
		$tmp_cell .= vmCommonHTML::getYesNoIcon( $db->f('shipping') );
		$tmp_cell .= "</a>";
	}
	else {
		$tmp_cell = vmCommonHTML::getYesNoIcon( $db->f('shipping') );
	}
	$listObj->addCell( $tmp_cell );  
	
	// Show on account maintenance?
	$tmp_cell = "<a href=\"". $sess->url( $_SERVER['PHP_SELF']."?page=admin.user_field_list&fieldid=".$db->f('fieldid')."&func=changePublishState&item=account" );
	$tmp_cell .= $db->f('account') ?	"&task=unpublish\">" : $tmp_cell .= "&task=publish\">";
	$tmp_cell .= vmCommonHTML::getYesNoIcon( $db->f('account') );
	$tmp_cell .= "</a>";
	$listObj->addCell( $tmp_cell );  
	
	$tmp_cell = "<div align=\"center\">"
			. $pageNav->orderUpIcon( $i, $i > 0, "orderup", JText::_('CMN_ORDER_UP'), $page, "changeordering" )
			. "\n&nbsp;" 
			. $pageNav->orderDownIcon( $i, $db->num_rows(), $i-1 <= $db->num_rows(), 'orderdown', JText::_('CMN_ORDER_DOWN'), $page, "changeordering" )
			. "</div>";
	$listObj->addCell( $tmp_cell );  
	
	$listObj->addCell( vmCommonHTML::getOrderingField( $db->f('ordering') ) );
	
	$tmp_cell = $db->f('sys') ? '' : $ps_html->deleteButton( "fieldid", $db->f('fieldid'), "userfieldDelete", $keyword, $limitstart );
	$listObj->addCell( $tmp_cell );
	
	$i++;
}

$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword );

?>