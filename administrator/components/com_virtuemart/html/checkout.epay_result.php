<?php 
/**
* @version $Id: checkout.epay_result.php,v 1.4 2005/05/22 09:21:15 soeren_nb Exp $
* @package VirtueMart
* @subpackage html
* @copyright (C) 2007-2008 Thomas Knudsen
*
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.VirtueMart.net
*
* ePay Order Confirmation Handler
*/
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );   

function addPaymentLog($dbConn, $log, $order_id)
{
	$dbConn->query( "UPDATE #__{vm}_order_payment SET order_payment_log = concat('" . $dbConn->getEscaped($log) . "<br>', order_payment_log) where order_id = '" .$dbConn->getEscaped( $order_id ) . "'");
}

function orderPaymentNotYetUpdated($dbConn, $order_id, $tid)
{
	$res = false;
	$dbConn->query("SELECT COUNT(*) `qty` FROM `#__{vm}_order_payment` WHERE `order_payment_number` = '" . $dbConn->getEscaped($order_id ) . "' and order_payment_trans_id = '" .$dbConn->getEscaped( $tid) . "'");
	if($dbConn->next_record()) {
		if ($dbConn->f('qty') == 0) {
			$res = true;
		}
	}
	return $res;
}

require_once(  CLASSPATH ."payment/ps_epay.cfg.php");

$accept = $_REQUEST["accept"];
$tid = $_REQUEST["tid"];
$order_id = $_REQUEST["orderid"];
$order_amount = $_REQUEST["amount"];
$order_currency = $_REQUEST["cur"];
$order_ekey = $_REQUEST["eKey"];
$error = $_REQUEST["error"];
$order_currency = $_REQUEST["cur"];



//////////////////////



////////////////////////

//
// Now validat on the MD5 stamping. If the MD5 key is valid or if MD5 is disabled
//
if(($order_ekey == md5( $order_amount . $order_id . $tid  . EPAY_MD5_KEY)) || EPAY_MD5_TYPE == 0 ) {
	
			//
			// Find the corresponding order in the database
			//  
      $qv = "SELECT order_id, order_number FROM #__{vm}_orders WHERE order_id='".$order_id."'";
      $dbo = new ps_DB;
      $dbo->query($qv);
      if($dbo->next_record()) {
        $d['order_id'] = $dbo->f("order_id");
        
        //
        // Switch on the order accept code
        // accept = 1 (standard redirect) accept = 2 (callback)
        //
        if( empty($_REQUEST['errorcode']) && ($accept == "1" || $accept == "2") ) {	
        	
        	//
        	// Only update the order information once
        	//
        	if (orderPaymentNotYetUpdated($dbo, $order_id, $tid)) {
        		
            
	            // UPDATE THE ORDER STATUS to 'VALID'
	            $d['order_status'] = EPAY_VERIFIED_STATUS;
	            // Setting this to "Y" = yes is required by Danish Law
	            $d['notify_customer'] = "Y";
	            $d['include_comment'] = "Y";
	            // Notifying the customer about the transaction key and
	            // the order Status Update
	            $d['order_comment'] = JText::_('VM_EPAY_PAYMENT_ORDER_COMMENT') . urldecode($tid)."\n";
	                
	            require_once ( CLASSPATH . 'ps_order.php' );
	            $ps_order= new ps_order;
	            $ps_order->order_status_update($d);
	            
	            //
	            // Order payment
	            //
	            $dbo->query( "UPDATE #__{vm}_order_payment SET order_payment_number = '" . $dbo->getEscaped($order_id ). "', order_payment_trans_id = '" . $tid . "', order_payment_code = 0 where order_id = '" .$dbo->getEscaped( $order_id ). "'");
	            
	            // add history callback info
	            if ($accept == "2") {
	            	addPaymentLog($dbo, JText::_('VM_EPAY_PAYMENT_CALLBACK'), $order_id);
	            }
	            
	            // payment fee
	            if ($_REQUEST["transfee"]) {
	            	addPaymentLog($dbo, JText::_('VM_EPAY_PAYMENT_FEE') . $_REQUEST["transfee"], $order_id);
	            }
	            
	            // payment date
	            if ($_REQUEST["date"]) {
	            	addPaymentLog($dbo, JText::_('VM_EPAY_PAYMENT_DATE') . $_REQUEST["date"], $order_id);
	            }
	            
	            // payment fraud control
	            if ($_REQUEST["fraud"]) {
	            	addPaymentLog($dbo, sprintf(JText::_('VM_EPAY_FRAUD'), $_REQUEST["fraud"]), $order_id);
	            }
	            
	            // card id
	            if ($_REQUEST["cardid"]) {
	               $cardname = "Unknown";
	               $cardimage = "c" . $_REQUEST["cardid"] . ".gif";
	               switch ($_REQUEST["cardid"])
	               {
                    case 1: $cardname = 'Dankort (DK)'; break;
                    case 2: $cardname = 'Visa/Dankort (DK)'; break;
                    case 3: $cardname = 'Visa Electron (Udenlandsk)'; break;
                    case 4: $cardname = 'Mastercard (DK)'; break;
                    case 5: $cardname = 'Mastercard (Udenlandsk)'; break;
                    case 6: $cardname = 'Visa Electron (DK)'; break;
                    case 7: $cardname = 'JCB (Udenlandsk)'; break;
                    case 8: $cardname = 'Diners (DK)'; break;
                    case 9: $cardname = 'Maestro (DK)'; break;
                    case 10: $cardname = 'American Express (DK)'; break;
                    case 11: $cardname = 'Ukendt'; break;
                    case 12: $cardname = 'eDankort (DK)'; break;
                    case 13: $cardname = 'Diners (Udenlandsk)'; break;
                    case 14: $cardname = 'American Express (Udenlandsk)'; break;
                    case 15: $cardname = 'Maestro (Udenlandsk)'; break;
                    case 16: $cardname = 'Forbrugsforeningen (DK)'; break;
                    case 17: $cardname = 'eWire'; break;
                    case 18: $cardname = 'VISA'; break;
                    case 19: $cardname = 'IKANO'; break;
                    case 20: $cardname = 'Andre'; break;
                    case 21: $cardname = 'Nordea'; break;
                    case 22: $cardname = 'Danske Bank'; break;
                    case 23: $cardname = 'Danske Bank'; break;
                 }
	               addPaymentLog($dbo, sprintf(JText::_('VM_EPAY_PAYMENT_CARDTYPE'), $cardname, $cardimage), $order_id);
	            	  
	            }
	            
	            // creation information
	            addPaymentLog($dbo, JText::_('VM_EPAY_PAYMENT_LOG_TID') . $tid . JText::_('VM_EPAY_PAYMENT_EPAY_LINK'), $order_id);
	        }
  
?> 
            <img src="<?php echo VM_THEMEURL ?>images/button_ok.png" align="middle" alt="Success" border="0" />
            <h2><?php echo JText::_('VM_PAYMENT_TRANSACTION_SUCCESS'); ?></h2>
<?php
        }
        elseif( $accept == "0" ) {
            // the Payment wasn't successful. Maybe the Payment couldn't
            // be verified and is pending
            // UPDATE THE ORDER STATUS to 'INVALID'
            $d['order_status'] = EPAY_INVALID_STATUS;
            // Setting this to "Y" = yes is required by Danish Law
            $d['notify_customer'] = "Y";
            $d['include_comment'] = "Y";
            // Notifying the customer about the transaction key and
            // the order Status Update
            $d['order_comment'] = JText::_('VM_EPAY_PAYMENT_DECLINE') . $fejl;
            require_once ( CLASSPATH . 'ps_order.php' );
            $ps_order= new ps_order;
            $ps_order->order_status_update($d);
            
?> 
            <img src="<?php echo VM_THEMEURL ?>images/button_cancel.png" align="middle" alt="Failure" border="0" />
            <h2><?php echo JText::_('VM_PAYMENT_ERROR') ?></h2>
<?php
		
           
						echo JText::_('VM_EPAY_PAYMENT_RETRY_PAYMENT');
        }
        
?>
        <br/>
        <p><a href="<?php @$sess->purl( SECUREURL."index.php?option=com_virtuemart&page=account.order_details&order_id=$order_id" ) ?>">
           <?php echo JText::_('VM_ORDER_LINK') ?></a>
        </p>
<?php
      }
      else {
        ?>
        <img src="<?php echo VM_THEMEURL ?>images/button_cancel.png" align="middle" alt="Failure" border="0" />
        <span class="message"><? echo JText::_('VM_PAYMENT_ERROR') . JText::_('VM_EPAY_PAYMENT_ORDER_NOT_FOUND') ?> </span><?php
      }
}
else{
        ?>
        <img src="<?php echo VM_THEMEURL ?>images/button_cancel.png" align="middle" alt="Failure" border="0" />
        <span class="message"><? echo JText::_('VM_PAYMENT_ERROR') . JText::_('VM_EPAY_PAYMENT_MD5_CHECK_FAILURE') ?> </span><?php
  }
  ?>
