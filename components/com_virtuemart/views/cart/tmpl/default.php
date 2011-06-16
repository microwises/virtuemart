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

	<?php // This displays the pricelist MUST be done with tables, because it is also used for the emails
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
			<span class="termsofservice"><?php echo JText::_('COM_VIRTUEMART_CART_TOS'); ?></span>
			<div>
			<?php echo $this->vendor->vendor_terms_of_service;?>
			</div>
		</div>
		<?php // Terms Of Service END ?>

		<?php // Continue and Checkout Button ?>
		<div class="checkout-button-top">
			<?php // Terms Of Service Checkbox
			$checked = '';
			echo '<input class="terms-of-service" type="checkbox" name="tosAccepted" value="1" ' . $checked . '/><span class="tos">'. JText::_('COM_VIRTUEMART_CART_TOS_READ_AND_ACCEPTED').'</span>';
	
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