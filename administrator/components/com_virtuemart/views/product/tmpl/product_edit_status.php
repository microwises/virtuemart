<?php
/**
*
* Information regarding the product status
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
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access'); ?>
<table class="adminform" width="100%">
	<tr class="row0">
		<td width="21%">
			<div style="text-align:right;font-weight:bold;">
			<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_IN_STOCK') ?></div>
		</td>
		<td width="79%">
			<input  type="text" class="inputbox js-change-stock"  name="product_in_stock" value="<?php echo $this->product->product_in_stock; ?>" size="10" />

			<?php if (isset($this->waitinglist) && count($this->waitinglist) > 0) { ?>
			<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=product&task=sentproductemailtoshoppers&virtuemart_product_id='.$this->product->virtuemart_product_id.'&token='.JUtility::getToken() ); ?>
			<div class="button2-left">
				<div class="blank">
					<a onclick="Joomla.submitbutton('notifyuserproductinstock')" href="#">
					<span class="icon-nofloat vmicon icon-16-messages"></span><?php echo Jtext::_('COM_VIRTUEMART_PRODUCT_NOTIFY_USER'); ?>
					</a>
				</div>
			</div>
			<?php } ?>
		</td>



	</tr>
	<tr class="row0">
		<td width="21%">
			<div style="text-align:right;font-weight:bold;">
			<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_ORDERED_STOCK') ?></div>
		</td>
		<td width="79%" colspan="2">
			<input type="text" class="inputbox js-change-stock"  name="product_ordered" value="<?php echo $this->product->product_ordered; ?>" size="10" />
		</td>
	</tr>
	<!-- low stock notification -->
	<tr class="row1">
		<td width="21%">
			<div style="text-align:right;font-weight:bold;">
				<?php echo JText::_('COM_VIRTUEMART_LOW_STOCK_NOTIFICATION'); ?>
			</div>
		</td>
		<td width="79%">
			<input type="text" class="inputbox" name="low_stock_notification" value="<?php echo $this->product->low_stock_notification; ?>" size="3" />
		</td>
	</tr>
	<!-- end low stock notification -->
	<tr class="row0">
		<td width="21%">
			<div style="text-align:right;font-weight:bold;">
				<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_MIN_ORDER') ?>
			</div>
		</td>
		<td width="79%">
			<input type="text" class="inputbox"  name="min_order_level" value="<?php echo $this->product->min_order_level; ?>" size="10" />
		</td>
	</tr>
	<tr class="row1">
		<td width="21%">
			<div style="text-align:right;font-weight:bold;">
				<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_MAX_ORDER') ?>
			</div>
		</td>
		<td width="79%">
			<input type="text" class="inputbox"  name="max_order_level" value="<?php echo $this->product->max_order_level; ?>" size="10" />
		</td>
	</tr>
	<tr class="row0">
		<td width="21%" >
			<div style="text-align:right;font-weight:bold;">
				<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_AVAILABLE_DATE') ?>
			</div>
		</td>
		<td width="79%">
			<?php

			echo vmJsApi::jDate($this->product->product_available_date, 'product_available_date'); ?>
		</td>
	</tr>
	<tr class="row1">
		<td valign="top" width="21%" >
			<div style="text-align:right;font-weight:bold;">
				<?php echo JText::_('COM_VIRTUEMART_AVAILABILITY') ?>
			</div>
		</td>
		<td width="79%">
			<input type="text" class="inputbox" id="product_availability" name="product_availability" value="<?php echo $this->product->product_availability; ?>" />
			<span class="icon-nofloat vmicon vmicon-16-info tooltip" title="<?php echo '<b>'.JText::_('COM_VIRTUEMART_AVAILABILITY').'</b><br/ >'.JText::_('COM_VIRTUEMART_PRODUCT_FORM_AVAILABILITY_TOOLTIP1') ?>"></span>

			<?php echo JHTML::_('list.images', 'image', $this->product->product_availability, " ", $this->imagePath); ?>
			<span class="icon-nofloat vmicon vmicon-16-info tooltip" title="<?php echo '<b>'.JText::_('COM_VIRTUEMART_AVAILABILITY').'</b><br/ >'.JText::sprintf('COM_VIRTUEMART_PRODUCT_FORM_AVAILABILITY_TOOLTIP2',  $this->imagePath ) ?>"></span>
		</td>
	</tr>
	<tr class="row1">
		<td width="21%">&nbsp;</td>
		<td width="79%"><img border="0" id="imagelib" alt="<?php echo JText::_('COM_VIRTUEMART_PREVIEW'); ?>" name="imagelib" src="<?php if ($this->product->product_availability) echo JURI::root(true).$this->imagePath.$this->product->product_availability;?>"/></td>
	</tr>
	<?php if ( VmConfig::get('stockhandle',0) == 'disableadd' && !empty($this->waitinglist ) OR 1) { ?>
		<tr class="row0">
			<td width="21%">&nbsp;</td>
			<td>
				<fieldset>
					<legend>
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_WAITING_LIST_USERLIST');?>
					</legend>
					<input type="hidden" value="<?php echo $this->product->product_in_stock; ?>" name="product_in_stock_old" />
					<input type="checkbox" value="1" checked="checked" id="notify_users" name="notify_users" />

					<label for="notify_users"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_WAITING_LIST_NOTIFYUSERS');?></label>
					<br /><br />

						<table class="adminlist" cellspacing="0" cellpadding="0">
							<thead>
								<tr>
									<th class="title"><?php echo JText::_('COM_VIRTUEMART_NAME');?></th>
									<th class="title"><?php echo JText::_('COM_VIRTUEMART_USERNAME');?></th>
									<th class="title"><?php echo JText::_('COM_VIRTUEMART_EMAIL');?></th>
									<th class="title"><?php echo JText::_('COM_VIRTUEMART_NOTIFIED');?></th>
								</tr>
							</thead>
							<tbody>
							<?php
							if (isset($this->waitinglist) && count($this->waitinglist) > 0) {
								foreach ($this->waitinglist as $key => $wait) {
									if ($wait->notified == 1) {
										$waiting_notified = JText::_('COM_VIRTUEMART_PRODUCT_WAITING_LIST_NOTIFIED') . ' ' . $wait->notify_date;
									} else {
										$waiting_notified = '';
									}
									if ($wait->virtuemart_user_id==0) {
										$row = '<tr><td></td><td></td><td><a href="mailto:' . $wait->notify_email . '">' . $wait->notify_email . '</a></td><td>'.$waiting_notified.'</td></tr>';
									}
									else {
										$row = '<tr><td>'.$wait->name.'</td><td>'.$wait->username . '</td><td>' . '<a href="mailto:' . $wait->notify_email . '">' . $wait->notify_email . '</a>' . '</td><td>' . $waiting_notified.'</td></tr>';
									}
									echo $row;
								}

							} else 
							{ ?>
									<tr>
										<td colspan="4">
											<?php echo JText::_('COM_VIRTUEMART_PRODUCT_WAITING_NOWAITINGUSERS'); ?>
										</td>
									</tr>
								<?php
							} ?>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
	<?php } ?>
</table>

<script type="text/javascript">
	jQuery('#image').change( function() {
		var $newimage = jQuery(this).val();
		jQuery('#product_availability').val($newimage);
		jQuery('#imagelib').attr({ src:'<?php echo JURI::root(true).$this->imagePath ?>'+$newimage, alt:$newimage });
		})
	jQuery('.js-change-stock').change( function() {
		
		var in_stock = jQuery('.js-change-stock[name="product_in_stock"]');
		var ordered = jQuery('.js-change-stock[name="product_ordered"]');
		var product_in_stock= parseInt(in_stock.val());
		if (var oldstock == "undefined") var oldstock = product_in_stock ;
		var product_ordered=parseInt(ordered.val());
		if (product_in_stock>product_ordered && product_in_stock!=oldstock )
			jQuery('#notify_users').attr('checked','checked');
		else jQuery('#notify_users').attr('checked','');
	});
</script>
