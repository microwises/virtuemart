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
* @version $Id$
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

echo '<pre>'.print_r($this->cart,1).'</pre>';

/* Show Continue Shopping link when the cart is empty */ 
if ($this->cart["idx"] == 0) {
	echo '<h2>'. JText::_('VM_CART_TITLE') .'</h2>';
	echo JText::_('VM_EMPTY_CART');
	echo '<br />';
	echo JHTML::link($this->continue_link, JText::_('VM_CONTINUE_SHOPPING'), array('class' => 'continue_link'));
}
else { 

	include(JPATH_COMPONENT.DS.'views'.DS.'cart'.DS.'tmpl'.DS.'pricelist.php');
	
?>
<form action="index.php">
<!-- 
<?php 
// FIXME Note for Max; I (Oscar) outcommented this, we should discuss this when both online
?>
<fieldset>
	<legend>
		<?php echo JText::_('VM_USER_FORM_BILLTO_LBL'); ?>
	</legend>

	<?php echo $this->lists['shipTo']; ?>
	<a class="vmicon vmicon-16-editadd" href="index.php?option=com_virtuemart&view=user&layout=edit&shipto=0&cid[]=<?php echo $this->user_id; ?>">
		<?php echo JText::_('VM_USER_FORM_LBL'); ?>
	</a>
	<input type="hidden" name="billto" value="<?php echo $this->lists['billTo']; ?>"/>
</fieldset>
 -->

<fieldset>
	<legend>
		<?php echo JText::_('VM_USER_FORM_SHIPTO_LBL'); ?>
	</legend>

	<?php echo $this->lists['shipTo']; ?>
	<a class="vmicon vmicon-16-editadd" href="index.php?option=com_virtuemart&view=user&layout=edit&shipto=0&cid[]=<?php echo $this->user_id; ?>">
		<?php echo JText::_('VM_USER_FORM_ADD_SHIPTO_LBL'); ?>
	</a>
	<input type="hidden" name="billto" value="<?php echo $this->lists['billTo']; ?>"/>
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
	
		
		?>
		<div align="center">
			<?php
			if ($this->continue_link != '') {
				echo "<input type='button' class='continue_link' value='".JText::_('VM_CONTINUE_SHOPPING')."' />";
//				echo JHTML::link($this->continue_link, JText::_('VM_CONTINUE_SHOPPING'), array('class' => 'continue_link'));
			}
			
			if(!empty($this->cart['totalsales'])) $totalsalesCart = $this->cart['totalsales'] ; else $totalsalesCart=0;
			if (VmStore::get('vendor_min_pov', 0) < $totalsalesCart) {
				/** @todo currency format totalsales */
				?>
				<span style="font-weight:bold;"><?php echo JText::_('VM_CHECKOUT_ERR_MIN_POV2'). " ".$totalsalesCart ?></span>
				<?php
			}
			else {
//				$href = JRoute::_('index.php?option=com_virtuemart&view=cart&task=checkout');
//				$href2 = JRoute::_('index2.php?option=com_virtuemart&view=checkout');
				$class_att = array('class' => 'checkout_link', 'onClick' => 'javascript:document.checkout.submit(); return true;');
				if(!empty($this->cart['dataValidated']) && $this->cart['dataValidated']){
					$text = JText::_('VM_ORDER_CONFIRM_MNU');
				} else {
					$text = JText::_('VM_CHECKOUT_TITLE');
				}

				/** @todo build the greybox checkout */
				//if ($this->get_cfg('useGreyBoxOnCheckout', 1)) echo vmCommonHTML::getGreyBoxPopupLink( $href2, $text, '', $text, $class_att, 500, 600, $href );
//				echo JHTML::link('#', $text, $class_att);
//				echo "<a href='#' class='checkout_link' onClick='checkout.submit(); return true;'>$text</a>";
				echo "<input type='submit' class='checkout_link' value='$text' />";
			} ?>
		</div>
		<input type="hidden" name="option" value="com_virtuemart"/>
		<input type="hidden" name="view" value="cart"/>
		<input type="hidden" name="task" value="checkout"/>
		</form>
<?php } ?>