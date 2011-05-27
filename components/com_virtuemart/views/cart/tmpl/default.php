<?php
/**
*
* Layout for the shopping cart
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
* @version $Id: cart.php 2551 2010-09-30 18:52:40Z milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidation');

//This displays the pricelist MUST be done with tables, because it is also used for the emails
include(JPATH_VM_SITE.DS.'views'.DS.'cart'.DS.'tmpl'.DS.'price_list.php');

?>
<form method="post" id="checkoutForm" name="checkoutForm" action="<?php echo JRoute::_( 'index.php?option=com_virtuemart' ); ?>">

<fieldset>
	<legend>
		<?php echo JText::_('COM_VIRTUEMART_USER_FORM_BILLTO_LBL'); ?>
	</legend>

	<a class="vmicon vmicon-16-editadd" href="index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=BT">
		<?php echo JText::_('COM_VIRTUEMART_USER_FORM_EDIT_BILLTO_LBL'); ?>
	</a><br /><br />
<?php 	foreach($this->BTaddress as $item){
			if(!empty($item['value'])){
				echo $item['title'].': '.$item['value'].'<br/>';
			}
		} ?>
	<input type="hidden" name="billto" value="<?php echo $this->lists['billTo']; ?>"/>
</fieldset>

<fieldset>
	<legend>
		<?php echo JText::_('COM_VIRTUEMART_USER_FORM_SHIPTO_LBL'); ?>
	</legend>
	<a class="vmicon vmicon-16-editadd" href="index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=ST&shipto=0&cid[]=<?php echo $this->lists['current_id']; ?>">
	<?php echo JText::_('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL'); ?>
	</a><br />
	<?php echo $this->lists['shipTo'];
		echo '<br /><br />';
		foreach($this->STaddress as $item){
			if(!empty($item['value'])){
				echo $item['title'].': '.$item['value'].'<br/>';
			}
		} ?>
</fieldset>

<fieldset>
	<div class="customer-comment marginbottom15">
		<span class="bold"><?php echo JText::_('COM_VIRTUEMART_COMMENT'); ?></span><br />
		<textarea class="customer-comment" name="customer_comment" cols="50" rows="4"><?php echo $this->cart->customer_comment; ?></textarea>
	</div>
</fieldset>
<fieldset>

	<div class="marginbottom15">
	<span class="bold"><?php echo JText::_('COM_VIRTUEMART_CART_TOS'); ?></span><br />
	<?php echo '<span class="red">'.$this->vendor->vendor_terms_of_service.'</span><br />';
		if (VmConfig::get('agree_to_tos_onorder')) {
			$checked = '';
			if ($this->cart->tosAccepted) $checked = 'checked="checked"';
			echo '<input type="checkbox" name="tosAccepted" value="1" ' . $checked . '/>'. JText::_('COM_VIRTUEMART_CART_TOS_READ_AND_ACCEPTED');
		}
	?>
	</div>
</fieldset>
<?php
	echo '<div class="paddingbottom20">';
	echo $this->checkout_link_html;

	if ($this->continue_link_html != '') {
		echo $this->continue_link_html;
	}

	$text = JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU');
	echo '</div>';
?>

<input type='hidden' name='task' value='<?php echo $this->checkout_task; ?>'/>
<input type='hidden' name='option' value='com_virtuemart'/>
<input type='hidden' name='view' value='cart'/>
</form>