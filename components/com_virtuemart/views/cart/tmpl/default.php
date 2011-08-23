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
JHTML::_ ( 'behavior.modal' );
?>

<div class="cart-view">
	<div>
	<div class="width50 floatleft">
		<h1><?php echo JText::_('COM_VIRTUEMART_CART_TITLE'); ?></h1>
	</div>
	<div class="width50 floatleft right">
		<?php // Continue Shopping Button
		if ($this->continue_link_html != '') {
			echo $this->continue_link_html;
		} ?>
	</div>
<div class="clear"></div>
</div>

<?php // Continue and Checkout Button
/* The problem here is that we use a form for the quantity boxes and so we cant let the form start here,
 * because we would have then a form in a form.
 *
 * But we cant make an extra form here, because then pressing the above checkout button would not send the
 * user notices for exampel. The solution is to write a javascript which checks and unchecks both tos checkboxes simultan
 * The upper checkout button should than just fire the form below.
 *
<div class="checkout-button-top">

	<?php // Terms Of Service Checkbox
	if(!class_exists('VmHtml'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
	echo VmHtml::checkbox('tosAccepted',$this->cart->tosAccepted,1,0,'class="terms-of-service"');
	$checked = '';
	//echo '<input class="terms-of-service" type="checkbox" name="tosAccepted" value="1" ' . $this->cart->tosAccepted . '/>

	echo '<span class="tos">'. JText::_('COM_VIRTUEMART_CART_TOS_READ_AND_ACCEPTED').'</span>';
	?>

	<?php // Checkout Button
	echo $this->checkout_link_html;
	$text = JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU');
	?>

</div>
	<form method="post" id="checkoutForm" name="checkoutForm" action="<?php echo JRoute::_( 'index.php?option=com_virtuemart' ); ?>">

	<input type='hidden' name='task' value='<?php echo $this->checkout_task; ?>'/>
	<input type='hidden' name='option' value='com_virtuemart'/>
	<input type='hidden' name='view' value='cart'/>
*/

	// This displays the pricelist MUST be done with tables, because it is also used for the emails
	include(JPATH_VM_SITE.DS.'views'.DS.'cart'.DS.'tmpl'.DS.'price_list.php');
	?>

	<form method="post" id="checkoutForm" name="checkoutForm" action="<?php echo JRoute::_( 'index.php?option=com_virtuemart' ); ?>">

		<?php // Leave A Comment Field ?>
		<div class="customer-comment marginbottom15">
			<span class="comment"><?php echo JText::_('COM_VIRTUEMART_COMMENT'); ?></span><br />
			<textarea class="customer-comment" name="customer_comment" cols="50" rows="4"><?php echo $this->cart->customer_comment; ?></textarea>
		</div>
		<?php // Leave A Comment Field END ?>

		<?php // Terms Of Service ?>
		<div class="terms-of-service">
			<span class="terms-of-service"><span class="vmicon vm2-termsofservice-icon"></span><?php echo JText::_('COM_VIRTUEMART_CART_TOS'); ?></span>
			<div>
			<?php echo $this->vendor->vendor_terms_of_service;?>
			</div>
		</div>
		<?php // Terms Of Service END ?>

		<?php // Continue and Checkout Button ?>
		<div class="checkout-button-top">

			<?php // Terms Of Service Checkbox
				if(!class_exists('VmHtml'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
				echo VmHtml::checkbox('tosAccepted',$this->cart->tosAccepted,1,0,'class="terms-of-service"');
			//$checked = '';
			//echo '<input class="terms-of-service" type="checkbox" name="tosAccepted" value="1" ' . $checked . '/>
			echo '<span class="tos">'. JText::_('COM_VIRTUEMART_CART_TOS_READ_AND_ACCEPTED').'</span>';
			echo $this->checkout_link_html;
			$text = JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU');
			?>
		</div>
		<?php // Continue and Checkout Button END ?>

		<input type='hidden' name='task' value='<?php echo $this->checkout_task; ?>'/>
		<input type='hidden' name='option' value='com_virtuemart'/>
		<input type='hidden' name='view' value='cart'/>
	</form>
</div>