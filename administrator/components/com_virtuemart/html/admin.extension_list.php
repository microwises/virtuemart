<?php
if (! defined ( '_VALID_MOS' ) && ! defined ( '_JEXEC' ))
	die ( 'Direct Access to ' . basename ( __FILE__ ) . ' is not allowed.' );
	
/**
*
* @version $Id: installer.extension_list.class.php 27/09/2008
* @package VirtueMart
* @subpackage classes
* @copyright Copyright 2008 HoaNT-Vsmarttech for this class
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

JHTML::_('behavior.tooltip');

require_once (CLASSPATH . 'pageNavigation.class.php');
require_once (CLASSPATH . 'htmlTools.class.php');
require_once (CLASSPATH . 'installer.class.php');
$available_extensions = vmInstaller::get_extension_types();

$extension_type = JRequest::getVar('extension_type', 'payment');

$link = $_SERVER['SCRIPT_NAME'].'?option=com_virtuemart&page='.$page.'&extension_type=';
 
echo '<div id="submenu-box">';
echo '<div id="submenu">';
foreach( $available_extensions as $type => $extension_classfile ) {
		
	if(!file_exists($extension_classfile)) continue;
	require_once( $extension_classfile );
	$classname = 'vmInstaller'.$type;
	if( !class_exists($classname)) continue;
	$vmInstallerInstance = new $classname();
	if( $extension_type != $type ) {
		echo '<a href="'.$link.$type.'">'.$vmInstallerInstance->getTitle().'</a>';
	} else {
		echo '<span class="nolink">'.$vmInstallerInstance->getTitle().'</span>';
	}
	echo '&nbsp;&nbsp;&nbsp;';
}
echo '</div>';
echo '</div>';

if( !empty($available_extensions[$extension_type])) {
	
	if(!file_exists($extension_classfile)) return;
	require_once( $extension_classfile );
	$classname = 'vmInstaller'.$extension_type;
	if( !class_exists($classname)) return;
	$vmInstallerInstance = new $classname();
	
	$modules = $vmInstallerInstance->get_extension_list($keyword, $limit, $limitstart);
	$num_rows = count($modules);
	// Create the Page Navigation
	$pageNav = new vmPageNav ( $num_rows, $limitstart, $limit );
	// Create the List Object with page navigation
	$listObj = new listFactory ( $pageNav );
	$listObj->writeSearchHeader('List of installed '.$vmInstallerInstance->getTitle(), '', 'admin', 'admin.extension_list');
	// start the list table
	$listObj->startTable();
	$i = 0;
	$hd = array ("#" => "width=\"20\"", 
						"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".$num_rows.")\" />" => "width=\"20\"",
						"Module Name" => "width=\"150\"", 
						"Author" => '', 
						"Version" => '', 
						"Remove" => "width=\"32\"" );
	$listObj->writeTableHeader ( $hd );
	foreach ( $modules as $method ) {
		$info=vminstaller::getInfo(ADMINPATH."plugins".DS.$extension_type.DS.$method.".xml");
		$listObj->newRow ();
		
		// The row number
		$listObj->addCell ( $pageNav->rowNumber ( $i ) );
		$listObj->addCell( vmCommonHTML::idBox( $i, $method, false, $extension_type.'_extension_name' ) );
		// The Payment method's name
		$cell = '<span style="cursor:default;" class="editlinktip hasTip" title="'. $method .'::'. vmGet( $info, 'description', 'No Description' ) .'">'.
						 $method.'
					</span>';
		$listObj->addCell ( $cell );
		
		$listObj->addCell (vmGet( $info, 'author', 'n/a' ));
		$listObj->addCell (vmGet( $info, 'version', 'n/a' ));
	
		$cell = ps_html::deleteButton($extension_type.'name', $method, 'uninstallExtension', $keyword, $limitstart, '&extension_type='.$extension_type);
		
		$listObj->addCell ( $cell );
		
		$i ++;
	}
	$listObj->writeTable ();
	
	$listObj->endTable ();
	$listObj->writeFooter($keyword, '&extension_type='.$extension_type);
	
}


?>