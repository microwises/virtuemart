<?php 
/**
*
* 2Checkout Order Confirmation Handler
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
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );   

/**
* Read the post from 2Checkout system 
* I have used $_REQUEST instead of $_POST, because
* the "direct return" feature comes here using the GET method
* and $_REQUEST includes $_POST as well as $_GET
**/
if( !isset( $_REQUEST["x_invoice_num"] ) || empty( $_REQUEST["x_invoice_num"] ))
  echo JText::_('VM_CHECKOUT_ORDERIDNOTSET');
else {
  
  /* Load the 2Checkout Configuration File */ 
  require_once( CLASSPATH. 'payment/ps_twocheckout.cfg.php' );
  
  /* x_invoice_num is the name of the variable that holds OUR order_number */
  $order_number = JRequest::getVar(  "x_invoice_num" ); 
  
  // In Demo Mode the MD5 Hash is built using a "1"
  if( isset($_REQUEST['demo']) )
      if($_REQUEST['demo']== "Y")
      $_REQUEST['order_number'] = "1";

  /* Concat some variables for MD5 Hashing (like 2Checkout does online)
  * order_number is the 2Checkout Order Number, not our one!
  */
  $compare_string = TWOCO_SECRETWORD . TWOCO_LOGIN . $_REQUEST['order_number'] . $_REQUEST['x_amount'];
  
  // make it md5
  $compare_hash1 = strtoupper(md5($compare_string));
  $compare_hash2 = $_REQUEST['x_MD5_Hash'];
  
  /* If both hashes are the same, the post should come from 2Checkout */
  if ($compare_hash1 != $compare_hash2) {
        ?>
        <img src="<?php echo VM_THEMEURL ?>images/button_cancel.png" align="middle" alt="<?php echo JText::_('VM_CHECKOUT_FAILURE'); ?>" border="0" />
        <span class="message"><?php echo JText::_('VM_PAYMENT_ERROR') ?></span><?php
  }
  else {
        $qv = "SELECT order_id, order_number FROM #__{vm}_orders ";
        $qv .= "WHERE order_number='".$order_number."'";
        $dbbt = new ps_DB;
        $dbbt->query($qv);
        $dbbt->next_record();
        $d['order_id'] = $dbbt->f("order_id");
        
        if ($_REQUEST['x_response_code'] == '1') {
            
            // UPDATE THE ORDER STATUS to 'VALID'
            $d['order_status'] = TWOCO_VERIFIED_STATUS;
            require_once ( CLASSPATH . 'ps_order.php' );
            $ps_order= new ps_order;
            $ps_order->order_status_update($d);
            
    ?> 
            <img src="<?php echo VM_THEMEURL ?>images/button_ok.png" align="middle" alt="<?php echo JText::_('VM_CHECKOUT_SUCCESS'); ?>" border="0" />
            <h2><?php echo JText::_('VM_PAYMENT_TRANSACTION_SUCCESS') ?></h2>
        <?php
        }
        else {
            // the Payment wasn't successful. Maybe the Payment couldn't
            // be verified and is pending
            // UPDATE THE ORDER STATUS to 'INVALID'
            $d['order_status'] = TWOCO_INVALID_STATUS;
            require_once ( CLASSPATH . 'ps_order.php' );
            $ps_order= new ps_order;
            $ps_order->order_status_update($d);
            
    ?> 
            <img src="<?php echo VM_THEMEURL ?>images/button_cancel.png" align="middle" alt="<?php echo JText::_('VM_CHECKOUT_FAILURE'); ?>" border="0" />
            <h2><?php echo JText::_('VM_PAYMENT_ERROR') ?></h2>
        <?php
        } 
  }
  ?>
<br />
<p><a href="<?php @$sess->purl( SECUREURL."index.php?option=com_virtuemart&page=account.order_details&order_id=".$d['order_id'] ) ?>">
   <?php echo JText::_('VM_ORDER_LINK') ?></a>
</p>
<?php
}

