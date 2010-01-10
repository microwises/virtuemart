<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
* ONLY for fallback reasons,.. if a View is not provided yet
* @version $Id: admin.virtuemart.php 1755 2009-05-01 22:45:17Z rolandd $
* @package JMart
* @subpackage core
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* JMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_jmart/COPYRIGHT.php for copyright notices and details.
*
* http://joomlacode.org/gf/project/jmart/
*/
defined( '_VM_IS_BACKEND' ) or define( '_VM_IS_BACKEND', '1' );


include( dirname(__FILE__).'/compat.joomla1.5.php');



//* INSTALLER SECTION *
//include( $mosConfig_absolute_path.'/administrator/components/com_jmart/install.virtuemart.php' );
//include( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'install.virtuemart.php' );

// * END INSTALLER SECTION *

// Load the virtuemart main parse code
require_once( JPATH_COMPONENT_SITE.DS.'virtuemart_parser.php' );

// bass28 6/12/09 - Use Joomla methods to get task
//$task = vmGet( $_GET, 'task', null);
$task = JRequest::getVar('task');

// Include The Version File
include_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'version.php' );

if( !isset( $VMVERSION ) || !is_object( $VMVERSION ) ) {
	$VMVERSION =& new vmVersion();
}

// Get the Layout Type from the Cookie
$vmLayout = vmGet( $_COOKIE, 'vmLayout', 'standard' );

// Change the Layout Type if it is provided through GET
if( !empty( $_GET['vmLayout'])) {
	$vmLayout = $_GET['vmLayout'] == 'standard' ? $_GET['vmLayout'] : 'extended';
}
// Remember the Cookie for 1 Week
ob_get_level() or ob_start();
setcookie('vmLayout', $vmLayout, time()+604800);

// pages, which are called through index3.php are PopUps, they should not need a menu (but it can be overridden by $_REQUEST['no_menu'])
$no_menu_default = strstr( $_SERVER['SCRIPT_NAME'], 'index3.php') ? 1 : 0;
$no_menu = $_REQUEST['no_menu'] = JRequest::getVar( 'no_menu', $no_menu_default );

// Display the toolbar?
$no_toolbar = JRequest::getVar('no_toolbar', 0 );

// Display just the naked page without toolbar, menu and footer?
$only_page_default = strstr( $_SERVER['SCRIPT_NAME'], 'index3.php') ? 1 : 0;
$only_page = $_REQUEST['only_page'] = JRequest::getVar( 'only_page', $only_page_default );

if( empty( $page ) || empty( $_REQUEST['page'])) {
	if( !empty($_REQUEST['amp;page'])) {
		$page = $_REQUEST['amp;page'];
		foreach( $_REQUEST as $key => $val ) {
			if( strstr( $key, 'amp;')) {
				$key = str_replace( 'amp;', '', $key );
				$_REQUEST[$key] = $val;
			}
		}
	}
	else {
		$page = JRequest::getVar( 'last_page', 'store.index' );
		
	}
}

$limit = $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
$limitstart = $mainframe->getUserStateFromRequest( "view{$page}{$product_id}{$category_id}limitstart", 'limitstart', 0 );

if (defined('_DONT_VIEW_PAGE') && !isset($install_type) ) {
    echo "<script type=\"text/javascript\">alert('$error. Your permissions: ".$_SESSION['auth']['perms']."')</script>\n";
}


// renew Page-Information
if( $pagePermissionsOK ) {
	$my_page= explode ( '.', $page );
	$modulename = $my_page[0];
	$pagename = $my_page[1];
}
if( !defined('_VM_TOOLBAR_LOADED') && $no_toolbar != 1 ) {
	if( $vmLayout == 'standard' && strstr($_SERVER['SCRIPT_NAME'], 'index3.php')) {
		echo '<div align="right" class="menudottedline">';
		//include_once( ADMINPATH.'toolbar.jmart.php');
		include( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'toolbar.php' );
		echo '</div>';
	} else {
//		include( ADMINPATH.'toolbar.php');
		include( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'toolbar.php' );
	}
	
}
// Include the Stylesheet
// bass28 6/15/09 - Remove VM admin stylesheets, Add stylesheet
//$vm_mainframe->addStyleSheet( JM_THEMEURL.'admin.styles.css' );
//$vm_mainframe->addStyleSheet( JM_THEMEURL.'theme.css' );
$vm_mainframe->addScript( JURI::root().'components/'.VM_COMPONENT_NAME.'/js/functions.js' );

if( $no_menu != 1 && $vmLayout != 'extended' ) {
	echo '<table style="width:100%;table-layout:fixed;"><tr><td style="vertical-align:top;">';
//	include(ADMINPATH.'header.php');
	include(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'header.php');
	echo '</td>';
}

echo '<td id="vmPage" style="width:78%;vertical-align:top;">';

// Load PAGE
if( !$pagePermissionsOK ) {
	$error = JText::_('PHPSHOP_MOD_NO_AUTH');
	include( PAGEPATH. ERRORPAGE .'.php');
	return;
}

if(file_exists(PAGEPATH.$modulename.".".$pagename.".php")) {
	
	if( $only_page ) {
		if( @$_REQUEST['format'] == 'raw' ) while( @ob_end_clean());
		if( $func ) echo vmCommonHTML::getSuccessIndicator( $ok, $vmDisplayLogger );

		include( PAGEPATH.$modulename.".".$pagename.".php" );
		if( @$_REQUEST['format'] == 'raw' ) {
			$vm_mainframe->close(true);
		}
	} else {
		include( PAGEPATH.$modulename.".".$pagename.".php" );
	}
}
else {
	include( PAGEPATH.'store.index.php' );
}

if( $vmLayout != 'extended' ) {
	echo '<br style="clear:both;"/><div class="smallgrey" align="center">'
                .$VMVERSION->PRODUCT.' '.$VMVERSION->RELEASE
                .' (<a href="http://joomlacode.org/gf/project/jmart//index2.php?option=com_versions&amp;catid=1&amp;myVersion='.@$VMVERSION->RELEASE.'" onclick="javascript:void window.open(this.href, \'win2\', \'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=580,directories=no,location=no\'); return false;" title="'.JText::_('JM_VERSIONCHECK_TITLE').'" target="_blank">'.JText::_('JM_VERSIONCHECK_NOW').'</a>)</div>';
}
if( DEBUG == '1' && $no_menu != 1 ) {
        // Load PAGE
	include( PAGEPATH."shop.debug.php" );
}

echo '</td></tr></table>';

// Render the script and style resources into the document head
$vm_mainframe->close();


?>