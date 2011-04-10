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
$show_price = (bool)$params->get( 'show_price', 1 ); // Display the Product Price?
$show_product_list = (bool)$params->get( 'show_product_list', 1 ); // Display the Product Price?
 ?>
<div class="vmCartModule">
<?php
if ($show_product_list) {
	?>
	<div id="hiddencontainer" style=" display: none; ">
		<div class="container">
			<div class="product_row">
				<span class="quantity"></span>&nbsp;x&nbsp;<span class="product_name"></span>
			</div>
			<?php if ($show_price) { ?>
				<div class="prices"></div>
			<?php } ?>
			<div class="product_attributes"></div>
		</div>
	</div>

<div class="vm_cart_products">

<?php
// ALL THE DISPLAY IS Done by Ajax in hiddencontainer
?>
</div>
<?php
}
?>
<div class="total"></div>
<div class="total_products"><?php echo JText::_('VM_AJAX_CART_WAITING') ?></div>
<div class="show_cart"></div>
<noscript>
<?php echo JText::_('VM_AJAX_CART_PLZ_JAVASCRIPT') ?>
</noscript>
</div>

