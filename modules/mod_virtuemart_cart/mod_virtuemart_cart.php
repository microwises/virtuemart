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

VmConfig::jPrice();
VmConfig::cssSite();*/
$jsVars  = ' jQuery(document).ready(function(){
	jQuery().productUpdate();

});' ;

if (!class_exists( 'VmConfig' )) {
	require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');}
VmConfig::jQuery();
VmConfig::jPrice();
VmConfig::cssSite();
$document = JFactory::getDocument();
$document->addScriptDeclaration($jsVars);

 ?>
<div class="vmCartModule">
 <div id="hiddencontainer" style=" display: none; ">
	<div class="container">
		<div class="product_row">
			<span class="productquantity">1</span>&nbsp;x&nbsp;<span class="product_name">1</span>
		</div>
		<div class="prices">1</div>
		<div class="product_attributes">1</div>
	</div>
</div>
<div class="vm_cart_products">
<noscript>
<?php echo JText::_('VM_AJAX_CART_PLZ_JAVASCRIPT') ?>
</noscript> 
<?php 
// ALL THE DISPLAY IS IN CART TMPL FOLDER OF VIRTUEMART COMPONENT Done by Ajax
?>
</div>
<div class="total"></div>
<div class="total_products"><?php echo JText::_('VM_AJAX_CART_WAITING') ?></div>
<div class="show_cart"></div>
</div>
