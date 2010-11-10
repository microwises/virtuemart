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

//This displays the pricelist MUST be done with tables, because it is also used for the emails
include(JPATH_COMPONENT.DS.'views'.DS.'cart'.DS.'tmpl'.DS.'pricelist.php');
	
?>
<form method="post" id=userForm name="checkoutForm" action="<?php echo JRoute::_( 'index.php' ); ?>">

<fieldset>
	<legend>
		<?php echo JText::_('VM_USER_FORM_BILLTO_LBL'); ?>
	</legend>

	  
	<a class="vmicon vmicon-16-editadd" href="index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=BT">
		<?php echo JText::_('VM_USER_FORM_EDIT_BILLTO_LBL'); ?>
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
		<?php echo JText::_('VM_USER_FORM_SHIPTO_LBL'); ?>
	</legend>
	<a class="vmicon vmicon-16-editadd" href="index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=ST&shipto=0&cid[]=<?php echo $this->lists['current_id']; ?>">
	<?php echo JText::_('VM_USER_FORM_ADD_SHIPTO_LBL'); ?>
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
	<legend>
		<?php echo JText::_('VM_COMMENT'); ?>
		<div>
			<textarea name="customer_comment" cols="50" rows="3"><?php echo $this->cart->customer_comment; ?></textarea>
		</div>
	</legend>
</fieldset>
<fieldset>
	<legend>
		<?php echo JText::_('VM_CART_TOS'); ?>
	</legend>
	<div>
	<?php echo $this->vendor->vendor_terms_of_service; echo '</div>';
	$checked = '';
	if ($this->cart->tosAccepted) $checked = 'checked="checked"';
	echo '<input type="checkbox" name="tosAccepted" value="1" ' . $checked . '/>'. JText::_('VM_CART_TOS_READ_AND_ACCEPTED');
	?>
</fieldset>
<?php

		/** @todo handle coupon field */
		/* Input Field for the Coupon Code */
		/**
		if( PSHOP_COUPONS_ENABLE=='1'
			&& !@$_SESSION['coupon_redeemed']
			//&& ($page == "shop.cart" )
		) {
			$basket_html .= $tpl->fetch( 'common/couponField.tpl.php' );
		}
		*/
	echo '<div class="cartfooterlinks" >';
	if ($this->continue_link_html != '') {			
		echo $this->continue_link_html;
	}
	echo $this->checkout_link_html;

	$text = JText::_('VM_ORDER_CONFIRM_MNU');
?>
</div>

<input type='hidden' name='task' value='<?php echo $this->checkout_task; ?>'/>
<input type='hidden' name='option' value='com_virtuemart'/>
<input type='hidden' name='view' value='cart'/>
</form>