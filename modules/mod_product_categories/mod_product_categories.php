<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* mambo-phphop Product Categories Module
* NOTE: THIS MODULE REQUIRES AN INSTALLED VirtueMart Component!
*
* @version $Id$
* @package VirtueMart
* @subpackage modules
* 
* @copyright (C) 2004-2008 soeren - All Rights Reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/
global $jscook_type, $jscookMenu_style, $jscookTree_style;

// Load the virtuemart main parse code
if( file_exists(dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' )) {
	require_once( dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' );
	$mosConfig_absolute_path = realpath( dirname(__FILE__).'/../..' );
} else {
	require_once( dirname(__FILE__).'/../components/com_virtuemart/virtuemart_parser.php' );
}

$category_id = vmGet( $_REQUEST, 'category_id');

/* Get module parameters */
$class_sfx = $params->get( 'class_sfx', "" );
$menutype = $params->get( 'menutype', "links" );
$jscookMenu_style = $params->get( 'jscookMenu_style', 'ThemeOffice' );
$jscookTree_style = $params->get( 'jscookTree_style', 'ThemeXP' );
$jscook_type = $params->get( 'jscook_type', 'menu' );
$menu_orientation = $params->get( 'menu_orientation', 'hbr' );
$_REQUEST['root_label'] = $params->get( 'root_label', 'Shop' );

$class_mainlevel = "mainlevel".$class_sfx;

global $VM_LANG, $sess;
if( vmIsJoomla('1.5' )) {
	$vm_path = $mosConfig_absolute_path.'/modules/mod_virtuemart';
} else {
	$vm_path = $mosConfig_absolute_path.'/modules';
}
switch( $menutype ) {

	case 'transmenu':
		/* TransMenu script to display a DHTML Drop-Down Menu */
		include( $vm_path . '/vm_transmenu.php' );
		break;

	case  'dtree':
		/* dTree script to display structured categories */
		include( $vm_path . '/vm_dtree.php' );
		break;
	
	case 'jscook':
		/* JSCook Script to display structured categories */
		include( $vm_path . '/vm_JSCook.php' );
		break;

	case 'tigratree':
		/* TigraTree script to display structured categories */
		include( $vm_path . '/vm_tigratree.php' );
		break;

	case 'links' :
	default:
		/* MENUTPYE LINK LIST */
		require_once(CLASSPATH.'ps_product_category.php');
		$ps_product_category = new ps_product_category();

		echo $ps_product_category->get_category_tree( $category_id, $class_mainlevel );
}

 ?>