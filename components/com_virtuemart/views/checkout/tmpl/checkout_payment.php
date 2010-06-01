<?php
/**
*
* Template for the checkout
*
* @package	VirtueMart
* @subpackage Checkout
* @author RolandD
* @todo create the totalsales value in the cart
* @todo Come up with a better solution for the zone shipping module
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

echo '<h4>'. JText::_('CHECK_OUT_GET_PAYMENT_METHOD') . '</h4>';

// echo ps_checkout::list_payment_methods( $payment_method_id );

if( $nocc_payments &&  $cc_payments ) {
	echo '<table><tr valign="top"><td width="50%">';
}
        
if ($cc_payments==true) { 
  	?>
	<fieldset><legend><strong><?php echo JText::_('VM_CHECKOUT_PAYMENT_CC') ?></strong></legend>
		<table border="0" cellspacing="0" cellpadding="2" width="100%">
		    <tr>
		        <td colspan="2">
		        	<?php $vmPaymentMethod->list_cc($payment_method_id, false) ?>
		        </td>
		    </tr>
		    <tr>
		        <td colspan="2"><strong>&nbsp;</strong></td>
		    </tr>
		    <tr>
		        <td nowrap width="10%" align="right"><?php echo JText::_('VM_CREDIT_CARD_TYPE'); ?>:</td>
		        <td>
		        <?php echo $ps_creditcard->creditcard_lists( $db_cc ); ?>
		        <script language="Javascript" type="text/javascript"><!--
				writeDynaList( 'class="inputbox" name="creditcard_code" size="1"',
				orders, originalPos, originalPos, originalOrder );
				//-->
				</script>
		<?php 
		            $db_cc->reset();
		            $element = $db_cc->f("element");
		            $require_cvv_code = "YES";
		            $_PAYMENT = vmPaymentMethod::getPaymentPlugin($element);
		            if(is_object($_PAYMENT) && is_a($_PAYMENT->params, 'vmparameters')) {
		                if( $_PAYMENT->params->get('CHECK_CARD_CODE' ) ) {
		                	$require_cvv_code = $_PAYMENT->params->get('CHECK_CARD_CODE' );
		                }
		            }
		?>      </td>
		    </tr>
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="order_payment_name"><?php echo JText::_('VM_CHECKOUT_CONF_PAYINFO_NAMECARD') ?>:</label>
		        </td>
		        <td>
		        <input type="text" class="inputbox" id="order_payment_name" name="order_payment_name" value="<?php if(!empty($_SESSION['ccdata']['order_payment_name'])) echo $_SESSION['ccdata']['order_payment_name'] ?>" autocomplete="off" />
		        </td>
		    </tr>
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="order_payment_number"><?php echo JText::_('VM_CHECKOUT_CONF_PAYINFO_CCNUM') ?>:</label>
		        </td>
		        <td>
		        <input type="text" class="inputbox" id="order_payment_number" name="order_payment_number" value="<?php if(!empty($_SESSION['ccdata']['order_payment_number'])) echo $_SESSION['ccdata']['order_payment_number'] ?>" autocomplete="off" />
		        </td>
		    </tr>
		<?php if( $require_cvv_code == "YES" ) { 
					$_SESSION['ccdata']['need_card_code'] = 1;	
			?>
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="credit_card_code">
		        		<?php echo vmToolTip( JText::_('VM_CUSTOMER_CVV2_TOOLTIP'), '', '', '', JText::_('VM_CUSTOMER_CVV2_TOOLTIP_TITLE') ) ?>:
		        	</label>
		        </td>		        		
		        <td>
		            <input type="text" class="inputbox" id="credit_card_code" name="credit_card_code" value="<?php if(!empty($_SESSION['ccdata']['credit_card_code'])) echo $_SESSION['ccdata']['credit_card_code'] ?>" autocomplete="off" />
		        
		        </td>
		    </tr>
		<?php } ?>
		    <tr>
		        <td nowrap width="10%" align="right"><?php echo JText::_('VM_CHECKOUT_CONF_PAYINFO_EXDATE') ?>:</td>
		        <td><?php 
		        $ps_html->list_month("order_payment_expire_month", @$_SESSION['ccdata']['order_payment_expire_month']);
		        echo "/";
		        $ps_html->list_year("order_payment_expire_year", @$_SESSION['ccdata']['order_payment_expire_year']) ?>
		       </td>
		    </tr>
    	</table>
    </fieldset>
  <?php  
}

if( $nocc_payments &&  $cc_payments ) {
	echo '</td><td width="50%">';
}

if ($nocc_payments==true) {
    if ($cc_payments==true) { 
    	$title = JText::_('VM_CHECKOUT_PAYMENT_OTHER');
    }
    else {
    	$title = JText::_('VM_ORDER_PRINT_PAYMENT_LBL');
    }
    	
   ?>
    <fieldset><legend><strong><?php echo $title ?></strong></legend>
		<table border="0" cellspacing="0" cellpadding="2" width="100%">
		    <tr>
		        <td colspan="2"><?php 
		            $vmPaymentMethod->list_nocheck($payment_method_id,  false); 
		            $vmPaymentMethod->list_bank($payment_method_id,  false);
		            $vmPaymentMethod->list_paypalrelated($payment_method_id,  false); ?>
		        </td>
		    </tr>
		 </table>
	</fieldset>
	<?php
}

if( $nocc_payments &&  $cc_payments ) {
	echo '</td></tr></table>';
}
  ?>