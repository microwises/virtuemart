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
<table width="100%">
	<tr>
		<td width="50%">
			<table class="adminform">
				<tr class="row0">
					<td width="21%">
						<div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_IN_STOCK') ?></div>
					</td>
					<td width="79%">
						<input type="text" class="inputbox"  name="product_in_stock" value="<?php echo $this->product->product_in_stock; ?>" size="10" />
					</td>
				</tr>
				<tr class="row0">
					<td width="21%">
						<div style="text-align:right;font-weight:bold;">
						<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_ORDERED_STOCK') ?></div>
					</td>
					<td width="79%">
						<input type="text" class="inputbox"  name="product_ordered" value="<?php echo $this->product->product_ordered; ?>" size="10" />
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
					<td width="79%" >
						<?php

						echo vmJsApi::jDate($this->product->product_available_date, 'product_available_date'); ?>
					</td>
				</tr>
				<tr>
					<td valign="top" width="21%" >
						<div style="text-align:right;font-weight:bold;">
							<?php echo JText::_('COM_VIRTUEMART_AVAILABILITY') ?>
						</div>
					</td>
					<td width="79%" >
						<input type="text" class="inputbox" id="product_availability" name="product_availability" value="<?php echo $this->product->product_availability; ?>" />
						<span class="icon-nofloat vmicon vmicon-16-info tooltip" title="<?php echo '<b>'.JText::_('COM_VIRTUEMART_AVAILABILITY').'</b><br/ >'.JText::_('COM_VIRTUEMART_PRODUCT_FORM_AVAILABILITY_TOOLTIP1') ?>"></span>

						<?php echo JHTML::_('list.images', 'image', $this->product->product_availability, " ", $this->imagePath); ?>
						<span class="icon-nofloat vmicon vmicon-16-info tooltip" title="<?php echo '<b>'.JText::_('COM_VIRTUEMART_AVAILABILITY').'</b><br/ >'.JText::sprintf('COM_VIRTUEMART_PRODUCT_FORM_AVAILABILITY_TOOLTIP2',  $this->imagePath ) ?>"></span>
					</td>
				</tr>
				<tr>
					<td width="21%">&nbsp;</td>
					<td width="79%"><img border="0" id="imagelib" alt="<?php echo JText::_('COM_VIRTUEMART_PREVIEW'); ?>" name="imagelib" src="<?php if ($this->product->product_availability) echo JURI::root(true).$this->imagePath.$this->product->product_availability;?>"/></td>
				</tr>

				<tr class="row0">
					<td colspan="2">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<script type="text/javascript">
	jQuery('#image').change( function() {
		var $newimage = jQuery(this).val();
		jQuery('#product_availability').val($newimage);
		jQuery('#imagelib').attr({ src:'<?php echo JURI::root(true).$this->imagePath ?>'+$newimage, alt:$newimage });
		})
</script>
