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
JHTML::stylesheet('vmpanels.css', JURI::root().'components/com_virtuemart/assets/css/');

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
		var msg = '<?php echo JText::_('COM_VIRTUEMART_USER_FORM_MISSING_REQUIRED'); ?>';
		alert (msg);
	}
	return false;
}
</script>
<form method="post" id="userForm" name="chooseShippingRate" action="<?php echo JRoute::_( 'index.php' ); ?>" class="form-validate">
<div style="text-align: right; width: 100%;">
	<button class="button" type="submit" ><?php echo JText::_('COM_VIRTUEMART_SAVE'); ?></button>
	&nbsp;
	<button class="button" type="reset" onClick="window.location.href='<?php echo JRoute::_( 'index.php?option=com_virtuemart&view=cart' ); ?>'" ><?php echo JText::_('COM_VIRTUEMART_CANCEL'); ?></button>
</div>
<?php
	echo JText::_('COM_VIRTUEMART_CART_SELECT_SHIPPER');

	$_dispatcher = JDispatcher::getInstance();
	$_tmp = array('cart'=>$this->cart,'checked'=>$this->selectedShipper);
	$_html = $_dispatcher->trigger('plgVmOnSelectShipper', $_tmp);

	foreach($_html as $_item){
		echo $_item;
	}

?>
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="view" value="cart" />
	<input type="hidden" name="task" value="setshipping" />
	<input type="hidden" name="controller" value="cart" />
</form>
