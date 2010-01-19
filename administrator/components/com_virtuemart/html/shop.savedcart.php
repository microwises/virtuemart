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

$manufacturer_id = JRequest::getVar(  'manufacturer_id');

$mainframe->setPageTitle( JText::_('VM_CART_TITLE') );
$mainframe->appendPathWay( JText::_('VM_CART_TITLE') );

$continue_link = $_SERVER['HTTP_REFERER'];
$action_url = $mm_action_url.basename($_SERVER['PHP_SELF']);

$show_basket = true;

$tpl = new $GLOBALS['VM_THEMECLASS']();
$tpl->set('replaceSaved',  '<form action="'.$action_url.'" method="post" id="replacecart" name="replacecart" style="display: inline;">
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="page" value="'. $page .'" />
    <input type="hidden" name="Itemid" value="'. $sess->getShopItemid() .'" />
    <input type="hidden" name="func" value="replaceSavedCart" />
    <label for="replace">'. JText::_('VM_RECOVER_CART_REPLACE') .'</label>
  	<input type="image" id="replace" name="replace" title="'. JText::_('VM_RECOVER_CART_REPLACE') .'" src="'. VM_THEMEURL .'images/update_quantity_cart.png" alt="'. JText::_('VM_RECOVER_CART_REPLACE') .'" align="middle" />
  </form>');
$tpl->set('mergeSaved', '<form action="'.$action_url.'" method="post" id="mergecart" name="mergecart" style="display: inline;">
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="page" value="'. $page .'" />
    <input type="hidden" name="Itemid" value="'. $sess->getShopItemid() .'" />
    <input type="hidden" name="func" value="mergeSavedCart" />
    <label for="merge">'. JText::_('VM_RECOVER_CART_MERGE') .'</label>
  	<input type="image" id="merge" name="merge" title="'. JText::_('VM_RECOVER_CART_MERGE') .'" src="'. VM_THEMEURL .'images/add_quantity_cart.png" alt="'. JText::_('VM_RECOVER_CART_MERGE') .'" align="middle" />
  </form>');
$tpl->set('deleteSaved', '<form action="'.$action_url.'" method="post" id="deletecart" name="deletecart" style="display: inline;">
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="page" value="'. $page .'" />
    <input type="hidden" name="Itemid" value="'. $sess->getShopItemid() .'" />
    <input type="hidden" name="func" value="deleteSavedCart" />
    <label for="delete">'. JText::_('VM_RECOVER_CART_DELETE') .'</label>
  	<input type="image" id="delete" name="delete" title="'. JText::_('VM_RECOVER_CART_DELETE') .'" src="'. VM_THEMEURL .'images/remove_from_cart.png" alt="'. JText::_('VM_RECOVER_CART_DELETE') .'" align="middle" />
  </form>');
$tpl->set('show_basket', $show_basket );
$tpl->set('continue_link', $continue_link );
$tpl->set('category_id', $category_id );
$tpl->set('product_id', $product_id );
$tpl->set('manufacturer_id', $manufacturer_id );
$tpl->set('cart', @$_SESSION['savedcart'] );

echo $tpl->fetch( "pages/$page.tpl.php" );

?>

