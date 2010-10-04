<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage
 * @author
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
$document = JFactory::getDocument();
$document->addScript($mainframe->getSiteURL().'components/com_virtuemart/assets/js/jquery.js');
//$document->addScript(JURI::base().'components/com_virtuemart/assets/js/jquery.alerts.js');

AdminMenuHelper::startAdminArea();
$orderbt = $this->order['details']['BT'];

$orderst = (array_key_exists('ST', $this->order['details'])) ? $this->order['details']['ST'] : $orderbt;
$history = $this->order['history'];
$items = $this->order['items'];
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <table class="adminlist" style="table-layout: fixed;">
	<tr>
	    <td valign="top">
		<table class="admintable" width="100%">
		    <tr>
			<td class="key" style="text-align: center;" colspan="2"><?php echo JText::_('VM_ORDER_PRINT_PO_LBL') ?></td>
		    </tr>
		    <tr>
			<td class="key"><strong><?php echo JText::_('VM_ORDER_PRINT_PO_NUMBER') ?>:</strong></td>
			<td><?php printf("%08d", $this->orderbt->order_id);?></td>
		    </tr>
		    <tr>
			<td class="key"><strong><?php echo JText::_('VM_ORDER_PRINT_PO_DATE') ?>:</strong></td>
			<td><?php echo date('Y-m-d H:i:s', $this->orderbt->cdate);?></td>
		    </tr>
		    <tr>
			<td class="key"><strong><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') ?>:</strong></td>
			<td><?php echo $this->orderbt->order_status_name; ?></td>
		    </tr>
		    <tr>
			<td class="key"><strong><?php echo JText::_('VM_ORDER_PRINT_PO_IPADDRESS') ?>:</strong></td>
			<td><?php $this->orderbt->ip_address; ?></td>
		    </tr>
		    <?php
		    if (VmConfig::get('enable_coupons') == '1') { ?>
		    <tr>
			<td class="key"><strong><?php echo JText::_('VM_COUPON_COUPON_HEADER') ?>:</strong></td>
			<td><?php echo $this->orderbt->coupon_code; ?></td>
		    </tr>
			<?php } ?>
		</table>
	    </td>
	    <td valign="top">
		<table class="adminlist">
		    <thead>
			<tr>
			    <th><?php echo JText::_('VM_ORDER_HISTORY_DATE_ADDED') ?></th>
			    <th><?php echo JText::_('VM_ORDER_HISTORY_CUSTOMER_NOTIFIED') ?></th>
			    <th><?php echo JText::_('VM_ORDER_LIST_STATUS') ?></th>
			    <th><?php echo JText::_('VM_COMMENT') ?></th>
			</tr>
		    </thead>
		    <?php
		    foreach ($history as $this->orderbt_event ) {
			echo "<tr>";
			echo "<td>".$this->orderbt_event->date_added."</td>\n";
			if ($this->orderbt_event->customer_notified == 1) {
			    echo '<td align="center">Yes</td>';
			}
			else {
			    echo '<td align="center">No</td>';
			}
			echo '<td align="center">'.$this->orderstatuslist[$this->orderbt_event->order_status_code].'</td>';
			echo "<td>".$this->orderbt_event->comments."</td>\n";
			echo "</tr>\n";
		    }
		    ?>
		    <tr>
			<td colspan="4">
			    <?php $statusLink=JRoute::_('index.php?option=com_virtuemart&view=orders&order_id='.$this->orderbt->order_id.'&tmpl=component&task=editOrderStatus'); ?>
			    <a href="<?php echo $statusLink; ?>" class="modal">
				<?php echo JHTML::_('image',  'administrator/components/com_virtuemart/assets/images/icon_16/icon-16-editadd.png', "Update Status"); ?>
				Update Status
			    </a>
			</td>
		    </tr>
		</table>
	    </td>
	</tr>
    </table>
    &nbsp;
    <table width="100%">
	<tr>
	    <td width="50%" valign="top">
		<table class="admintable" width="100%">
		    <thead>
			<tr>
			    <td class="key" style="text-align: center;"  colspan="2"><?php echo JText::_('VM_ORDER_PRINT_BILL_TO_LBL') ?></td>
			</tr>
		    </thead>

		<?php 
			foreach ($this->userfields['fields'] as $_field ) {
				echo '		<tr>'."\n";
				echo '			<td class="key">'."\n";
				echo '				'.$_field['title']."\n";
				echo '			</td>'."\n";
				echo '			<td>'."\n";
				echo '				'.$_field['value']."\n";
				echo '			</td>'."\n";
				echo '		</tr>'."\n";
			}
		?>

	</table>
	    </td>
	    <td width="50%" valign="top">
		<table class="admintable" width="100%">
		    <thead>
			<tr>
			    <td class="key" style="text-align: center;"  colspan="2"><?php echo JText::_('VM_ORDER_PRINT_SHIP_TO_LBL') ?></td>
			</tr>
		    </thead>

		<?php 
			foreach ($this->shippingfields['fields'] as $_field ) {
				echo '		<tr>'."\n";
				echo '			<td class="key">'."\n";
				echo '				'.$_field['title']."\n";
				echo '			</td>'."\n";
				echo '			<td>'."\n";
				echo '				'.$_field['value']."\n";
				echo '			</td>'."\n";
				echo '		</tr>'."\n";
			}
		?>

		</table>
	    </td>
	</tr>
    </table>

    <table width="100%">
	<tr>
	    <td colspan="2">
		<table class="adminlist">
		    <thead>
			<tr>
			    <th class="title" width="5%" align="left"><?php echo JText::_('VM_ORDER_EDIT_ACTIONS') ?></th>
			    <th class="title" width="50" align="left"><?php echo JText::_('VM_ORDER_PRINT_QUANTITY') ?></th>
			    <th class="title" width="*" align="left"><?php echo JText::_('VM_ORDER_PRINT_NAME') ?></th>
			    <th class="title" width="10%" align="left"><?php echo JText::_('VM_ORDER_PRINT_SKU') ?></th>
			    <th class="title" width="10%"><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') ?></th>
			    <th class="title" width="50"><?php echo JText::_('VM_PRODUCT_FORM_PRICE_NET') ?></th>
			    <th class="title" width="50"><?php echo JText::_('VM_PRODUCT_FORM_PRICE_GROSS') ?></th>
			    <th class="title" width="5%"><?php echo JText::_('VM_ORDER_PRINT_TOTAL') ?></th>
			</tr>
		    </thead>
		    <?php
		    foreach ($items as $item) {
		    ?>
		    <tr valign="top">
			<td>
			    <?php $removeLineLink=JRoute::_('index.php?option=com_virtuemart&view=orders&orderId='.$this->orderbt->order_id.'&orderLineId='.$item->order_item_id.'&task=removeOrderItem'); ?>
			    <span onclick="javascript:confirmation('<?php echo $removeLineLink; ?>');">
				<?php echo JHTML::_('image',  'administrator/components/com_virtuemart/assets/images/icon_16/icon-16-bug.png', "Remove", NULL, "Remove"); ?>
			    </span>
			    <?php $editLineLink=JRoute::_('index.php?option=com_virtuemart&view=orders&orderId='.$this->orderbt->order_id.'&orderLineId='.$item->order_item_id.'&tmpl=component&task=editOrderItem'); ?>
			    <a href="<?php echo $editLineLink; ?>" class="modal">
				<?php echo JHTML::_('image',  'administrator/components/com_virtuemart/assets/images/icon_16/icon-16-category.png', "Edit", NULL, "Edit"); ?>
			    </a>
			</td>
			<td><?php echo $item->product_quantity; ?></td>
			<td><?php echo $item->order_item_name; ?></td>
			<td><?php echo $item->order_item_sku; ?></td>
			<td align="center">
			    <?php $statusLink=JRoute::_('index.php?option=com_virtuemart&view=orders&orderId='.$this->orderbt->order_id.'&orderLineId='.$item->order_item_id.'&tmpl=component&task=updateOrderItemStatus'); ?>
			    <a href="<?php echo $statusLink; ?>" class="modal">
					<?php echo $this->orderstatuslist[$item->order_status]; ?>
			    </a>
			</td>
			<td><?php echo $this->currency->getFullValue($item->product_item_price); ?></td>
			<td><?php echo $this->currency->getFullValue($item->product_final_price); ?></td>
			<td><?php echo $this->currency->getFullValue($item->product_quantity * $item->product_final_price); ?></td>
		    </tr>
			<?php } ?>
		</table>
		<table  class="adminlist">
		    <tr>
			<td align="left" colspan="6">
			    <?php $editLineLink=JRoute::_('index.php?option=com_virtuemart&view=orders&orderId='.$this->orderbt->order_id.'&orderLineId=0&tmpl=component&task=editOrderItem'); ?>
			    <a href="<?php echo $editLineLink; ?>" class="modal">				
				<?php echo JHTML::_('image',  'administrator/components/com_virtuemart/assets/images/icon_16/icon-16-editadd.png', "New Item"); ?>
				New Item
			    </a>
			</td>
			<td align="right">
			    <div align="right"><strong> <?php echo JText::_('VM_ORDER_PRINT_SUBTOTAL') ?>: </strong></div></td>
			<td width="5%" align="right" style="padding-right: 5px;"><?php echo $this->currency->getFullValue($this->orderbt->order_subtotal); ?></td>
		    </tr>
		    <?php
		    /* COUPON DISCOUNT */
		    if (VmConfig::get('payment_discount_before') == '1') {
			if ($this->orderbt->order_discount != 0) {
			    ?>
		    <tr>
			<td align="right" colspan="7"><strong>
					<?php
					if ($this->orderbt->order_discount > 0) echo JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
					else echo JText::_('VM_FEE');
					?>:</strong></td>
			<td width="5%" align="right" style="padding-right: 5px;"><?php
				    if ($this->orderbt->order_discount > 0 ) echo "-" . $this->currency->getFullValue($this->orderbt->order_discount);
				    elseif ($this->orderbt->order_discount < 0 )  echo "+" . $this->currency->getFullValue($ordert->order_discount); ?>
			</td>
		    </tr>
			    <?php
			}
			if ($this->orderbt->coupon_discount > 0 || $this->orderbt->coupon_discount < 0) {
			    ?>
		    <tr>
			<td align="right" colspan="7"><strong><?php echo JText::_('VM_COUPON_DISCOUNT') ?>:</strong></td>
			<td  width="5%" align="right" style="padding-right: 5px;"><?php
				    echo "- ".$this->orderbt->coupon_discount; ?>
			</td>
		    </tr>
			    <?php
			}
		    }?>
		    <tr>
			<td align="right" colspan="7"><strong><?php echo JText::_('VM_ORDER_PRINT_TOTAL_TAX') ?>:</strong></td>
			<td width="5%" align="right" style="padding-right: 5px;"><?php echo $this->currency->getFullValue($this->orderbt->order_tax); ?></td>
		    </tr>
		    <tr>
			<td align="right" colspan="7"><strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING') ?>:</strong></td>
			<td width="5%" align="right" style="padding-right: 5px;"><?php echo $this->currency->getFullValue($this->orderbt->order_shipping); ?></td>
		    </tr>
		    <tr>
			<td align="right" colspan="7"><strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_TAX') ?>:</strong></td>
			<td width="5%" align="right" style="padding-right: 5px;"><?php echo $this->currency->getFullValue($this->orderbt->order_shipping_tax); ?></td>
		    </tr>
		    <?php
		    if (VmConfig::get('payment_discount_before') != '1') {
			if ($this->orderbt->order_discount != 0) {
			    ?>
		    <tr>
			<td align="right" colspan="7"><strong><?php
					if( $this->orderbt->order_discount > 0) echo JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
					else echo JText::_('VM_FEE');
					?>:</strong>
			</td>
			<td width="5%" align="right" style="padding-right: 5px;"><?php
				    if ($this->orderbt->order_discount > 0 )
					echo "-" . $this->currency->getFullValue($this->orderbt->order_discount);
				    elseif ($this->orderbt->order_discount < 0 ) echo "+".$this->currency->getFullValue($this->orderbt->order_discount); ?>
			</td>
		    </tr>
			    <?php
			}
			if( $this->orderbt->coupon_discount > 0 || $this->orderbt->coupon_discount < 0) {
			    ?>
		    <tr>
			<td align="right" colspan="7"><strong><?php echo JText::_('VM_COUPON_DISCOUNT') ?>:</strong></td>
			<td width="5%" align="right" style="padding-right: 5px;"><?php echo "- ".$this->currency->getFullValue($this->orderbt->coupon_discount); ?></td>
		    </tr>
			    <?php
			}
		    }
		    ?>
		    <tr>
			<td align="right" colspan="7"><strong><?php echo JText::_('VM_CART_TOTAL') ?>:</strong></td>
			<td width="5%" align="right" style="padding-right: 5px;">
			    <strong><?php echo $this->currency->getFullValue($this->orderbt->order_total); ?></strong>
			</td>
		    </tr>
		    <?php
		    /* Get the tax details, if any */
		    //$tax_details = ps_checkout::show_tax_details( $db->f('order_tax_details'), $db->f('order_currency') );
		    ?>
		    <?php if (!empty( $tax_details)) { ?>
		    <tr>
			<td colspan="8" align="right"><?php echo $tax_details; ?></td>
		    </tr>
			<?php }; ?>
		</table>
		<?php //$ps_order_change_html->html_change_add_item();
		?>
	    </td>
	</tr>
    </table>
    &nbsp;
    <table width="100%">
	<tr>
	    <td valign="top">
		<table class="admintable">
		    <thead>
			<tr>
			    <td class="key" style="text-align: center;" colspan="2"><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_LBL') ?></td>
			</tr>
		    </thead>
		    <tr>
			<td class="key">
			    <?php echo JText::_('VM_ORDER_PRINT_SHIPPING_CARRIER_LBL') ?>:
			</td>
			<td align="left">
			    <?php
			    if  ($this->orderbt->ship_method_id) {
				$details = explode( "|", $this->orderbt->ship_method_id);
			    }
			    echo $details[1]; ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <?php echo JText::_('VM_ORDER_PRINT_SHIPPING_MODE_LBL') ?>:
			</td>
			<td>
			    <?php echo $details[2]; ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <?php echo JText::_('VM_ORDER_PRINT_SHIPPING_PRICE_LBL') ?>:
			</td>
			<td align="left">
			    <?php echo $details[3]; ?>
			</td>
		    </tr>
		</table>
	    </td>
	    <td valign="top">
		<?php 
		JPluginHelper::importPlugin('vmpayment');
		$_dispatcher =& JDispatcher::getInstance();
		// I don't like the variables to be in the orderBT details array, but that's the way iot is for now
		// TODO, in VM1.6, when user address handling is redone, the variables will probably come from somehwere else...
		$_returnValues = $_dispatcher->trigger('plgVmOnShowStoredOrder',array(
					 $orderbt->order_id
					,$orderbt->payment_method_id
		));
		foreach ($_returnValues as $_returnValue) {
			if ($_returnValue !== null) {
				echo $_returnValue;
			}
		}
		?>
	    </td>
	</tr>
	<tr>
	    <!-- Customer Note -->
	    <td valign="top" width="30%" colspan="2">
		<table class="adminlist">
		    <thead>
			<tr>
			    <th><?php echo JText::_('VM_ORDER_PRINT_CUSTOMER_NOTE') ?></th>
			</tr>
		    </thead>
		    <tr>
			<td valign="top" align="center" width="50%">
			    <?php //$ps_order_change_html->html_change_customer_note();
			    ?>
			</td>
		    </tr>
		</table>
	    </td>
	</tr>
    </table>
    <!-- Hidden Fields -->
    <input type="hidden" name="task" value="save" />
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="pshop_mode" value="admin" />
    <input type="hidden" name="page" value="product.product_list" />
    <input type="hidden" name="view" value="orders" />
    <input type="hidden" name="func" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>

<?php AdminMenuHelper::endAdminArea(); ?>

<script type="text/javascript">
<!--
function confirmation(destnUrl) {
	var answer = confirm("<?php echo JText::_('VM_ORDER_DELETE_ITEM_MSG'); ?>")
	if (answer) {
		window.location = destnUrl;
	}
}
//-->
</script>
