<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: get_shipping_address.tpl.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage templates
* @copyright Copyright (C) 2007-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

ps_checkout::show_checkout_bar();

echo $basket_html;
   
echo '<br />';

?>
<div style="width: 100%; align:left;">
	<?php
// CHECK_OUT_GET_SHIPPING_ADDR
// let the user choose a shipto address
//echo ps_checkout::display_address($auth["user_id"], "ship_to_info_id", $ship_to_info_id);
echo ps_checkout::display_address();
	?>
<br />
</div>
<div class="sectiontableheader" style="width: 100%; align: left; float: left;"> <?php echo JText::_('VM_ORDER_PRINT_CUST_SHIPPING_LBL') ?></div>

<div style="width: 100%; align:left; float:left;">
<?php
$varname = 'VM_CHECKOUT_MSG_' . CHECK_OUT_GET_SHIPPING_ADDR;
echo '<h4>'. JText::_($varname) . '</h4>';
?>
</div>
<!-- Customer Ship To -->

<div style="width: 40%; align: left; float: left;">
	<?php
	$ps_checkout->ship_to_addresses_radio($auth["user_id"], "ship_to_info_id", $ship_to_info_id);
	?>
</div>
<br />
<div style="width: 100%; align: left; float:left;">
	<?php echo JText::_('VM_ADD_SHIPTO_1') ?>
        <a href="<?php $sess->purl(SECUREURL .basename($_SERVER['PHP_SELF']). "?page=account.shipto&next_page=checkout.index");?>">
        <?php echo JText::_('VM_ADD_SHIPTO_2') ?></a>.
 </div>

<!-- END Customer Ship To -->
<br />