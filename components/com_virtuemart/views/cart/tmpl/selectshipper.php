<?php
/**
*
* Template for the shipper selection
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
* @version $Id: cart.php 2400 2010-05-11 19:30:47Z milbo $
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
	<button class="button" type="submit" /><?php echo JText::_('Save'); ?></button>
	&nbsp;
	<button class="button" type="reset" /><?php echo JText::_('Cancel'); ?></button>
</div>
<?php
echo 'Todo: only a rough view to have something to work with, checking for dimensions and country is missing';
echo '<p>Please select a shipper that fit your needs:</p><br />';
//echo '<form>';
foreach($this->shippingCarriers as $keyCarr=>$valueCarr){
	
	echo 'Shipping rates for '.$keyCarr;
	echo '<br />';
	
	foreach($valueCarr as $key=>$value){
		if(!empty($this->cart->shipping_rate_id) && $this->cart->shipping_rate_id==$value['shipping_rate_id']) $checked='"checked"'; else $checked='';
		echo '<input type="radio" name="shipping_rate_id" value="'.$value['shipping_rate_id'].' '.$checked.'">'.$value['shipping_rate_name'].' costs by the shipper '.$value['shipping_rate_value'].' our fee '. $value['shipping_rate_package_fee'].'<br />';

	}
	echo '<br />';
}
?>
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="view" value="cart" />
	<input type="hidden" name="task" value="setshipping" />
	<input type="hidden" name="controller" value="cart" />
</form>
