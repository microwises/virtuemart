<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* PayPal IPN Result Checker
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

if( !isset( $_REQUEST["order_id"] ) || empty( $_REQUEST["order_id"] )) {
	echo JText::_('VM_CHECKOUT_ORDERIDNOTSET');
}
else {
	include( CLASSPATH. "payment/ps_paypal.cfg.php" );
	$order_id = intval( JRequest::getVar(  "order_id" ));

	$q = "SELECT order_status FROM #__{vm}_orders WHERE ";
	$q .= "#__{vm}_orders.user_id= " . $auth["user_id"] . " ";
	$q .= "AND #__{vm}_orders.order_id= $order_id ";
	$db->query($q);
	if ($db->next_record()) {
		$order_status = $db->f("order_status");
		if($order_status == PAYPAL_VERIFIED_STATUS
      || $order_status == PAYPAL_PENDING_STATUS) {  ?> 
        <img src="<?php echo VM_THEMEURL ?>images/button_ok.png" align="middle" alt="<?php echo JText::_('VM_CHECKOUT_SUCCESS'); ?>" border="0" />
        <h2><?php echo JText::_('VM_PAYPAL_THANKYOU') ?></h2>
    
    <?php
      }
      else { ?>
        <img src="<?php echo VM_THEMEURL ?>images/button_cancel.png" align="middle" alt="<?php echo JText::_('VM_CHECKOUT_FAILURE'); ?>" border="0" />
        <span class="message"><?php echo JText::_('VM_PAYPAL_ERROR') ?></span>
    
    <?php
    } ?>
    <br />
     <p><a href="index.php?option=com_virtuemart&page=account.order_details&order_id=<?php echo $order_id ?>">
     <?php echo JText::_('VM_ORDER_LINK') ?></a>
     </p>
    <?php
	}
	else {
		echo JText::_('VM_CHECKOUT_ORDERNOTFOUND') . '!';
	}
}
?>
