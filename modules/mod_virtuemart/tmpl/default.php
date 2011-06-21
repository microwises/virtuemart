<?php
/**
* @version $Id: vm_transmenu.php 2281 2010-01-31 19:02:47Z Milbo $
* @package VirtueMart
* @copyright (C) 2005 MamboTheme.com
* @license http://www.mambotheme.com
* 
*/

if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

//echo $ps_product_category->get_category_tree( $virtuemart_category_id, $class_mainlevel );
   JPlugin::loadLanguage('com_virtuemart', JPATH_ADMINISTRATOR);

        echo $categorylist = ShopFunctions::categoryListTree(array($virtuemart_category_id));
?>