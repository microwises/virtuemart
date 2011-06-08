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
						<input type="text" class="inputbox"  name="min_order_level" value="<?php echo $this->min_order; ?>" size="10" />
					</td>
				</tr>
				<tr class="row1"> 
					<td width="21%">
						<div style="text-align:right;font-weight:bold;">
							<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_MAX_ORDER') ?>
						</div>
					</td>
					<td width="79%">
						<input type="text" class="inputbox"  name="max_order_level" value="<?php echo $this->max_order; ?>" size="10" />
					</td>
				</tr>
				<tr class="row0"> 
					<td width="21%" >
						<div style="text-align:right;font-weight:bold;">
							<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_AVAILABLE_DATE') ?>
						</div>
					</td>
					<td width="79%" >
						<?php echo JHTML::_('calendar', date('Y-m-d', $this->product->product_available_date), "product_available_date", "product_available_date"); ?>
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
						<?php echo JHTML::tooltip(JText::_('COM_VIRTUEMART_PRODUCT_FORM_AVAILABILITY_TOOLTIP1'), JText::_('COM_VIRTUEMART_AVAILABILITY'), 'tooltip.png', '', '', false); ?>
						<script type="text/javascript">
							jQuery('#image').live('click', function() { jQuery('#product_availability').val(jQuery('#image').val()); })
						</script>
						<?php
						echo JHTML::_('list.images', 'image', $this->product->product_availability, null, $this->imagePath);
						echo JHTML::tooltip(str_replace('%s', $this->imagePath, JText::_('COM_VIRTUEMART_PRODUCT_FORM_AVAILABILITY_TOOLTIP2')), JText::_('COM_VIRTUEMART_AVAILABILITY'), 'tooltip.png', '', '', false); 
						?> 
					</td>
				</tr>
				<tr>
					<td width="21%">&nbsp;</td>
					<td width="79%"><img border="0" id="imagelib" alt="<?php echo JText::_('COM_VIRTUEMART_PREVIEW'); ?>" name="imagelib" src="<?php echo JURI::root().$this->imagePath.$this->product->product_availability;?>"/></td>
				</tr>
				
				<tr class="row0">
					<td colspan="2">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

