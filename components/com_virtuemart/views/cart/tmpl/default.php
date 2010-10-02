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

<fieldset>
	<legend>
		<?php echo JText::_('VM_USER_FORM_BILLTO_LBL'); ?>
	</legend>

	<?php  ?>
	<a class="vmicon vmicon-16-editadd" href="index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=BT">
		<?php echo JText::_('VM_USER_FORM_EDIT_BILLTO_LBL'); ?>
	</a>
	<input type="hidden" name="billto" value="<?php echo $this->lists['billTo']; ?>"/>
</fieldset>

<fieldset>
	<legend>
		<?php echo JText::_('VM_USER_FORM_SHIPTO_LBL'); ?>
	</legend>
	<a class="vmicon vmicon-16-editadd" href="index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=ST&shipto=0&cid[]=<?php echo $this->lists['current_id']; ?>">
	<?php echo JText::_('VM_USER_FORM_ADD_SHIPTO_LBL'); ?>
	</a><br />
	<?php echo $this->lists['shipTo'];  ?>
</fieldset>

<?php
		echo '<fieldset>';
		echo JText::_('VM_USER_FORM_BILLTO_YOUR').' <br/>';
		foreach($this->BTaddress as $item){				
//		foreach($this->cart->BT as $item){				
			echo $item['title'].': '.$item['value'].'<br/>';
		}
		echo '<br/><br/>';
		
		echo JText::_('VM_USER_FORM_SHIPTO_YOUR').' <br/>';
		foreach($this->STaddress as $item){		
//		foreach($this->cart->ST as $item){		
			echo $item['title'].': '.$item['value'].'<br/>';
		}
		
		echo '</fieldset>';
		
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

//	if(!empty($this->cart->totalsales)) $totalsalesCart = $this->cart->totalsales ; else $totalsalesCart=0;
//	if (VmStore::get('vendor_min_pov', 0) < $totalsalesCart) {
//		/** @todo currency format totalsales */
//		
//		<span style="font-weight:bold;"><?php echo JText::_('VM_CHECKOUT_ERR_MIN_POV2'). " ".$totalsalesCart </span>
//		<?php
//	}
//	else {
		echo $this->checkout_link_html;
		
//	} 
echo '</div>';
?>
