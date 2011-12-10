<?php
defined('_JEXEC') or  die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* @version $Id$
* @package VirtueMart
* @subpackage modules
*
* @copyright (C) 2011 Patrick Kohl
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
$button_text	 = $params->get('button_text', JText::_('Search'));
$width			 = intval($params->get('width', 20));
$maxlength		 = $width > 20 ? $width : 20;
$text			 = $params->get('text', JText::_('search...'));
$set_Itemid		 = intval($params->get('set_itemid', 0));
$moduleclass_sfx = $params->get('moduleclass_sfx', '');

if ( $params->get('filter_category', 0) )	$category_id = JRequest::getInt('virtuemart_category_id', 0);
else $category_id = 0 ;
require(JModuleHelper::getLayoutPath('mod_virtuemart_search'));
?>
