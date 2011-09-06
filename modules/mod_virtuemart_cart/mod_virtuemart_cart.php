<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
*Cart Ajax Module
*
* @version $Id$
* @package VirtueMart
* @subpackage modules
*
* 	@copyright (C) 2010 - Patrick Kohl
// W: demo.st42.fr
// E: cyber__fr|at|hotmail.com
*
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/
/*if (!class_exists( 'VmConfig' )) {
require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');}
//VmConfig::loadConfig();

vmJsApi::jPrice();
vmJsApi::cssSite();*/
$jsVars  = ' jQuery(document).ready(function(){
	jQuery(".vmCartModule").productUpdate();

});' ;

if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');
            
if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
$cart = VirtueMartCart::getCart(false,false);
$data = $cart->prepareAjaxData();
		if ($data->totalProduct>1) $data->totalProductTxt = JText::sprintf('COM_VIRTUEMART_QUANTITY_PRODUCTS', $data->totalProduct);
		else if ($data->totalProduct == 1) $data->totalProductTxt = JText::_('COM_VIRTUEMART_QUANTITY_PRODUCT');
		else $data->totalProductTxt = JText::_('COM_VIRTUEMART_EMPTY_CART');
		if ($data->dataValidated == true) {
			$taskRoute = '&task=confirm';
			$linkName = JText::_('COM_VIRTUEMART_CART_CONFIRM');
		} else {
			$taskRoute = '';
			$linkName = JText::_('COM_VIRTUEMART_CART_SHOW');
		}
		$data->cart_show = '<a style ="float:right;" href="'.JRoute::_("index.php?option=com_virtuemart&view=cart".$taskRoute).'">'.$linkName.'</a>';
		$data->billTotal = $lang->_('COM_VIRTUEMART_TOTAL').' : <strong>'. $data->billTotal .'</strong>';

vmJsApi::jQuery();
vmJsApi::jPrice();
vmJsApi::cssSite();
$document = JFactory::getDocument();
//$document->addScriptDeclaration($jsVars);

$show_price = (bool)$params->get( 'show_price', 1 ); // Display the Product Price?
$show_product_list = (bool)$params->get( 'show_product_list', 1 ); // Display the Product Price?
/* Laod tmpl default */
require(JModuleHelper::getLayoutPath('mod_virtuemart_cart'));
 ?>