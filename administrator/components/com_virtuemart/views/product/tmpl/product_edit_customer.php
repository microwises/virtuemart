<?php
/**
*
* Handle the waitinglist
*
* @package	VirtueMart
* @subpackage Product
* @author RolandD
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: product_edit_waitinglist.php 3872 2011-08-15 16:56:50Z electrocity $
*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

 ?>
<span ID="customer-list-BT">
<div class="button2-left" data-type="reserved">
	<div class="blank" style="padding:0 6px;cursor: pointer;">
		<div class="vmicon vmicon-16-forward" ></div>
		<?php echo Jtext::_('COM_VIRTUEMART_PRODUCT_LIST_BOOKED_BUYER'); ?>
	</div>
</div>
<div class="button2-left" data-type="all">
	<div class="blank" style="padding:0 6px;cursor: pointer;">
		<div class="vmicon vmicon-16-forward-off" ></div>
		<?php echo Jtext::_('COM_VIRTUEMART_PRODUCT_LIST_ALL_BUYER'); ?>

	</div>
</div>
<div class="button2-left" data-type="delivered">
	<div class="blank" style="padding:0 6px;cursor: pointer;">
		<div class="vmicon vmicon-16-forward-off" ></div>
		<?php echo Jtext::_('COM_VIRTUEMART_PRODUCT_LIST_DELIVERED_BUYER'); ?>

	</div>
</div>
</span>
<table class="adminlist" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th class="title"><?php echo JText::_('COM_VIRTUEMART_NAME');?></th>
			<th class="title"><?php echo JText::_('COM_VIRTUEMART_EMAIL');?></th>
			<th class="title"><?php echo JText::_('COM_VIRTUEMART_SHOPPER_FORM_PHONE');?></th>
			<th class="title"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_ORDERED_STOCK');?></th>
		</tr>
	</thead>
	<tbody id="customers-list">
	<?php
	foreach ($this->customers as $virtuemart_order_user_id => $customer) { 
		?>
			<tr class="customer" data-cid="<?php echo $virtuemart_order_user_id ?>">
				<td class=''><?php echo $customer['customer_name'] ?></td>
				<td><a class='mailto' href="<?php echo $customer['mail_to'] ?>"><span class='mail'><?php echo $customer['email'] ?></span></a></td>
				<td class='customer_phone'><?php echo $customer['customer_phone'] ?></td>
				<td class='quantity'><?php echo $customer['quantity'] ?></td>
			</tr>
		<?php
	}
	?>
	</tbody>
</table>
<script type="text/javascript">
<!--

/* JS for list changes */
var $customerListLink = '<?php echo 'index.php?option=com_virtuemart&view=product&format=json&type=userlist&virtuemart_product_id='.$this->product->virtuemart_product_id ?>';
var $customerListtype='reserved';
jQuery('#customer-list-BT .button2-left').click(function() {
	//document.orderStatForm.task.value = 'updateOrderItemStatus';
	that = jQuery(this).find('.vmicon');
	if (that.hasClass('vmicon-16-forward-off')) 
	{
		that.removeClass('vmicon-16-forward-off').addClass('vmicon-16-forward');
		jQuery(this).siblings().children().children().addClass('vmicon-16-forward-off').removeClass('vmicon-16-forward');
		$type = jQuery(this).data('type');
		jQuery.get($customerListLink,{ listType: $type },function(data){
				jQuery("#customers-list").html(data.value);
		});
	}
	
});


-->
</script>