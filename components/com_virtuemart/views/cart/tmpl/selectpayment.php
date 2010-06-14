<?php
/**
*
* Template for the payment selection
*
* @package	VirtueMart
* @subpackage Cart
* @author Max Milbers
*
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

JHTML::_('behavior.formvalidation');
JHTML::stylesheet('vmpanels.css', VM_THEMEURL);

?>
<style type="text/css">
.invalid {
	border-color: #f00;
	background-color: #ffd;
	color: #000;
}
label.invalid {
	background-color: #fff;
	color: #f00;
}
</style>
<script language="javascript">
function myValidator(f, t)
{
	f.task.value=t;
	if (f.task.value=='cancel') {
		f.submit();
		return true;
	}
	if (document.formvalidator.isValid(f)) {
		f.submit();
		return true;
	} else {
		var msg = '<?php echo JText::_('VM_USER_FORM_MISSING_REQUIRED'); ?>';
		alert (msg);
	}
	return false;
}
</script>
<form method="post" id="userForm" name="chooseShippingRate" action="<?php echo JRoute::_( 'index.php' ); ?>" class="form-validate">
<div style="text-align: right; width: 100%;">
	<button class="button" type="submit" onclick="javascript:return myValidator(userForm, 'save');" /><?php echo JText::_('SAVE'); ?></button>
	&nbsp;
	<button class="button" type="submit" onclick="javascript:return myValidator(userForm, 'cancel');" /><?php echo JText::_('CANCEL'); ?></button>
</div>
<?php
echo 'Todo: only a rough view to have something to work with';
echo '<p>Please select a paymentmethod that fit your needs:</p><br />';

if($this->withCC){
?>
	<table border="0" cellspacing="0" cellpadding="2" width="100%">
			    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="order_payment_name"><?php echo JText::_('PHPSHOP_CHECKOUT_CONF_PAYINFO_NAMECARD') ?>:</label>
		        </td>
		        <td>
		        <input type="text" class="inputbox" id="order_payment_name" name="order_payment_name" value="<?php if(!empty($_SESSION['ccdata']['order_payment_name'])) echo $_SESSION['ccdata']['order_payment_name'] ?>" autocomplete="off" />
		        </td>
		    </tr>
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="order_payment_number"><?php echo JText::_('PHPSHOP_CHECKOUT_CONF_PAYINFO_CCNUM') ?>:</label>
		        </td>
		        <td>
		        <input type="text" class="inputbox" id="order_payment_number" name="order_payment_number" value="<?php if(!empty($_SESSION['ccdata']['order_payment_number'])) echo $_SESSION['ccdata']['order_payment_number'] ?>" autocomplete="off" />
		        </td>
		    </tr>
		<?php //if( $this->require_cvv_code == "YES" ) { 
				//	$_SESSION['ccdata']['need_card_code'] = 1;	
			?>
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="credit_card_code">
		        		<?php //echo vmToolTip( JText::_->_('PHPSHOP_CUSTOMER_CVV2_TOOLTIP'), '', '', '',JText::_('PHPSHOP_CUSTOMER_CVV2_TOOLTIP_TITLE') ) ?>:
		        	</label>
		        </td>		        		
		        <td>
		            <input type="text" class="inputbox" id="credit_card_code" name="credit_card_code" value="<?php if(!empty($_SESSION['ccdata']['credit_card_code'])) echo $_SESSION['ccdata']['credit_card_code'] ?>" autocomplete="off" />
		        
		        </td>
		    </tr>
		<?php //} ?>
		    <tr>
		        <td nowrap width="10%" align="right"><?php echo JText::_('PHPSHOP_CHECKOUT_CONF_PAYINFO_EXDATE') ?>:</td>
		        <td> <?php 
		        shopfunctions::listMonths('order_payment_expire_month', @$this->cart->order_payment_expire_month);
		        echo "/";
		        shopfunctions::listYears('order_payment_expire_year', @$this->cart->order_payment_expire_year);
		        
//		        $ps_html->list_month("order_payment_expire_month", @$_SESSION['ccdata']['order_payment_expire_month']);
//		        echo "/";
//		        $ps_html->list_year("order_payment_expire_year", @$_SESSION['ccdata']['order_payment_expire_year']) ?>
		       </td>
		    </tr>
    	</table>
	<?php	
}
		foreach($this->payments as $item){
			$checked='';
			if($item->paym_id==$this->selectedPaym){					
				$checked='"checked"';
			}
			echo '<input type="radio" name="paym_id" value="'.$item->paym_id.'" '.$checked.'>'.$item->paym_name.' <br />';
			if($item->paym_creditcards){
				echo ($this->paymentModel->renderCreditCardRadioList($this->selectedCC,$item->paym_creditcards));
			}
			echo ' <br />';
		}
		
		$listHTML;

?>	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="view" value="cart" />
	<input type="hidden" name="task" value="setpayment" />
	<input type="hidden" name="controller" value="cart" />
</form>