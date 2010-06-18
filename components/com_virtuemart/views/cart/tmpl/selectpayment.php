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
<form method="post" id="userForm" name="choosePaymentRate" action="<?php echo JRoute::_( 'index.php' ); ?>" class="form-validate">
<div style="text-align: right; width: 100%;">
	<button class="button" type="submit" onclick="javascript:return myValidator(userForm, 'save');" /><?php echo JText::_('SAVE'); ?></button>
	&nbsp;
	<button class="button" type="submit" onclick="javascript:return myValidator(userForm, 'cancel');" /><?php echo JText::_('CANCEL'); ?></button>
</div>
<?php
echo 'Todo: only a rough view to have something to work with';
echo '<p>Please select a paymentmethod that fit your needs:</p><br /><br />';
echo '<pre>'.print_r($this->cart).'</pre>';
if($this->withCC){
?> <fieldset>
	<table border="0" cellspacing="0" cellpadding="2" width="100%">
			    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="order_payment_name"><?php echo JText::_('VM_CHECKOUT_PAYINFO_NAMECARD') ?>:</label>
		        </td>
		        <td>
		        <input type="text" class="inputbox" id="cart_cc_name" name="cart_cc_name" value="<?php if(!empty($this->cart['cc_name'])) echo $this->cart['cc_name'] ?>" autocomplete="off" />
		        </td>
		    </tr>
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="order_payment_number"><?php echo JText::_('VM_CHECKOUT_PAYINFO_CCNUM') ?>:</label>
		        </td>
		        <td>
		        <input type="text" class="inputbox" id="cart_cc_number" name="cart_cc_number" value="<?php if(!empty($this->cart['cc_number'])) echo $this->cart['cc_number'] ?>" autocomplete="off" />
		        </td>
		    </tr>
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="credit_card_code"><?php echo JText::_('VM_CHECKOUT_PAYINFO_CVV2')  ?>: </label>
		        </td>		        		
		        <td>
		            <input type="text" class="inputbox" id="cart_cc_code" name="cart_cc_code" value="<?php if(!empty($this->cart['cc_code'])) echo $this->cart['cc_code'] ?>" autocomplete="off" />   
		        </td>
		    </tr>
		    <tr>
		        <td nowrap width="10%" align="right"><?php echo JText::_('VM_CHECKOUT_PAYINFO_EXDATE') ?>:</td>
		        <td> <?php 
		        $cc_expire_month = 0;
		        if(!empty($this->cart['cc_expire_month'])) $cc_expire_month =  $this->cart['cc_expire_month'];
		        echo shopfunctions::listMonths('cart_cc_expire_month', $cc_expire_month );
		        echo "/";
		        $cc_expire_year = 0;
		        if(!empty($this->cart['cc_expire_year'])) $cc_expire_year =  $this->cart['cc_expire_year'];
		        echo shopfunctions::listYears('cart_cc_expire_year', $cc_expire_year);
		        
 ?>
		       </td>
		    </tr>
    	</table>
    	</fieldset>
	<?php	
}
		foreach($this->payments as $item){
			$checked='';
			if($item->paym_id==$this->selectedPaym){					
				$checked='"checked"';
			}
			echo '<fieldset>';
			echo '<input type="radio" name="paym_id" value="'.$item->paym_id.'" '.$checked.'>'.$item->paym_name.' ';
			if($item->paym_creditcards){
				echo ($this->paymentModel->renderCreditCardRadioList($this->selectedCC,$item->paym_creditcards));
			}else {
				echo '<br />';
			}
			echo ' </fieldset> ';
		}
		
		$listHTML;

?>	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="view" value="cart" />
	<input type="hidden" name="task" value="setpayment" />
	<input type="hidden" name="controller" value="cart" />
</form>