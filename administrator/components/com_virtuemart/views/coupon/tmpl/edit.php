<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Coupon
* @author RickG
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

AdminUIHelper::startAdminArea();
AdminUIHelper::imitateTabs('start','COM_VIRTUEMART_COUPON_DETAILS');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<div class="col50">
	<fieldset>
	<legend><?php echo JText::_('COM_VIRTUEMART_COUPON_DETAILS'); ?></legend>
	<table class="admintable">
		<tr>
			<td width="110" class="key">
                              <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_COUPON_TOOLTIP'); ?>">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_COUPON'); ?>
				</label>
                              </span>
			</td>
			<td>
				<input class="inputbox" type="text" name="coupon_code" id="coupon_code" size="20" maxlength="32" value="<?php echo JText::_($this->coupon->coupon_code); ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
                            <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_COUPON_PERCTOT_TOOLTIP'); ?>">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_COUPON_PERCENT_TOTAL'); ?>
				</label>
                            </span>
			</td>
			<td>

				<?php

				$radioOptions = '';
				$radioOptions[] = JHTML::_('select.option', 'percent', JText::_('COM_VIRTUEMART_COUPON_PERCENT'));
				$radioOptions[] = JHTML::_('select.option', 'total', JText::_('COM_VIRTUEMART_COUPON_TOTAL'));
				echo '<fieldset class="radio">' . JHTML::_('select.radiolist',  $radioOptions, 'percent_or_total', '', 'value', 'text', $this->coupon->percent_or_total) . '</fieldset>';

				?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
                              <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_COUPON_TYPE_TOOLTIP'); ?>">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_COUPON_TYPE'); ?>
				</label>
                              </span>
			</td>
			<td>

				<?php
				$listOptions = '';
				$listOptions[] = JHTML::_('select.option', 'permanent', JText::_('COM_VIRTUEMART_COUPON_TYPE_PERMANENT'));
				$listOptions[] = JHTML::_('select.option', 'gift', JText::_('COM_VIRTUEMART_COUPON_TYPE_GIFT'));
				echo JHTML::_('select.genericlist',  $listOptions, 'coupon_type', '', 'value', 'text', $this->coupon->coupon_type);

				?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
                              <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_COUPON_VALUE_TOOLTIP'); ?>">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_VALUE'); ?>
				</label>
                              </span>
			</td>
			<td>
				<input class="inputbox" type="text" name="coupon_value" id="coupon_value" size="10" value="<?php echo $this->coupon->coupon_value; ?>" />

			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_COUPON_VALUE_VALID_AT'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="coupon_value_valid" id="coupon_value_valid" size="10" value="<?php echo $this->coupon->coupon_value_valid; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
                             <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_COUPON_START_TIP'); ?>">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_COUPON_START'); ?>
				</label>
                             </span>
			</td>
			<td>
				<?php
                $mydate = JFactory::getDate($this->coupon->coupon_start_date);
				echo VmConfig::jDate($mydate->toFormat($this->dateformat), 'coupon_start_date'); 
				// echo JHTML::_('calendar', $mydate->toFormat($this->dateformat), "coupon_start_date", "coupon_start_date", $this->dateformat);
                ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
                              <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_COUPON_EXPIRY_TIP'); ?>">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_COUPON_EXPIRY'); ?>
				</label>
                              </span>
			</td>
			<td>
				<?php
                $expireDate = JFactory::getDate($this->coupon->coupon_expiry_date);
				echo VmConfig::jDate($expireDate->toFormat($this->dateformat), 'coupon_expiry_date'); 
                //echo JHTML::_('calendar', $expireDate->toFormat($this->dateformat), "coupon_expiry_date", "coupon_expiry_date", $this->dateformat);
                ?>

			</td>
		</tr>
	</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="virtuemart_coupon_id" value="<?php echo $this->coupon->virtuemart_coupon_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="coupon" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>


<?php 
AdminUIHelper::imitateTabs('end');
AdminUIHelper::endAdminArea(); ?>