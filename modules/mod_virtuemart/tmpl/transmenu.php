<?php
/**
* @version $Id: vm_transmenu.php 2281 2010-01-31 19:02:47Z Milbo $
* @package VirtueMart
* @copyright (C) 2005 MamboTheme.com
* @license http://www.mambotheme.com
* 
*/

if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

/**
/* Loads main class file
*/	
$params->set( 'module_name', 'ShopMenu' );
$params->set( 'module', 'vm_transmenu' );
$params->set( 'absPath', JPATH_ROOT.DS.'modules'.DS.'mod_virtuemart'.DS.'tmpl'.DS.'vm_transmenu'.DS );
$params->set( 'LSPath', JURI::root() .'modules/mod_virtuemart/tmpl/vm_transmenu');

include_once(  JPATH_ROOT.DS.'modules'.DS.'mod_virtuemart'.DS.'tmpl'.DS.'vm_transmenu'.DS.'Shop_Menu.php' );

$mbtmenu= new Shop_Menu($params);

$mbtmenu->genMenu();

?>