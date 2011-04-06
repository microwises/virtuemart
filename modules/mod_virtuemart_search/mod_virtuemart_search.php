<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* VirtueMart Search Module
* NOTE: THIS MODULE REQUIRES THE PHPSHOP COMPONENT FOR MOS!
*
* @version $Id$
* @package VirtueMart
* @subpackage modules
*
* @copyright (C) 2004-2007 soeren
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/

// Load the virtuemart main parse code
$button			 = $params->get('button', '');
$imagebutton	 = $params->get('imagebutton', '');
$button_pos		 = $params->get('button_pos', 'left');
$button_text	 = $params->get('button_text', JText::_('GO'));
$width			 = intval($params->get('width', 20));
$maxlength		 = $width > 20 ? $width : 20;
$text			 = $params->get('text', JText::_('SEARCH'));
$set_Itemid		 = intval($params->get('set_itemid', 0));
$moduleclass_sfx = $params->get('moduleclass_sfx', '');
//$category_id 	 = JRequest::getInt('category_id', 0); todo ?
$category_id = 0;
require(JModuleHelper::getLayoutPath('mod_virtuemart_search'));
?>
