<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Currency
* @author Max Milbers, RickG
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
AdminUIHelper::imitateTabs('start','COM_VIRTUEMART_CURRENCY_DETAILS');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">


<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_VIRTUEMART_CURRENCY_DETAILS'); ?></legend>
	<table class="admintable">
		<tr>
			<td width="110" class="key">
				<label for="currency_name">
					<?php echo JText::_('COM_VIRTUEMART_CURRENCY_NAME'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="currency_name" id="currency_name" size="50" value="<?php echo $this->currency->currency_name; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="published">
					<?php echo JText::_('COM_VIRTUEMART_PUBLISHED'); ?>
				</label>
			</td>
			<td>
				<fieldset class="radio">
				<?php echo JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $this->currency->published); ?>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="currency_exchange_rate">
					<?php echo JText::_('COM_VIRTUEMART_CURRENCY_EXCHANGE_RATE'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="currency_exchange_rate" id="currency_exchange_rate" size="6" value="<?php echo $this->currency->currency_exchange_rate; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="currency_code_2">
					<?php echo JText::_('COM_VIRTUEMART_CURRENCY_CODE_2'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="currency_code_2" id="currency_code_2" size="2" value="<?php echo $this->currency->currency_code_2; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="currency_code_3">
					<?php echo JText::_('COM_VIRTUEMART_CURRENCY_CODE_3'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="currency_code_3" id="currency_code_3" size="3" value="<?php echo $this->currency->currency_code_3; ?>" />
			</td>
		</tr>
                <tr>
			<td width="110" class="key">
				<label for="currency_numeric_code">
					<?php echo JText::_('COM_VIRTUEMART_CURRENCY_NUMERIC_CODE'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="currency_numeric_code" id="currency_numeric_code" size="3" value="<?php echo $this->currency->currency_numeric_code; ?>" />
			</td>
		</tr>
<?php /*		<tr>
		<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_START_DATE'); ?>
				</label>
			</td>
			<td>
				<?php
					$startDate = JFactory::getDate($this->currency->publish_up,$this->tzoffset);
					echo JHTML::_('calendar', $startDate->toFormat($this->dateformat), "publish_up", "publish_up", $this->dateformat);
 				?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_END_DATE'); ?>
				</label>
			</td>
			<td>
				<?php $endDate;
				if (empty($this->currency->publish_down) || !strcmp($this->currency->publish_down,'0000-00-00 00:00:00')  ) {
					$endDate = JText::_('COM_VIRTUEMART_NEVER');
				} else {
					$date = JFactory::getDate($this->currency->publish_down,$this->tzoffset);
					$endDate = $date->toFormat($this->dateformat);
				}
				echo JHTML::_('calendar', $endDate, "publish_down", "publish_down", $this->dateformat,array('class'=>'inputbox', 'size'=>'25',  'maxlength'=>'19')); ?>
			</td>
		</tr> */ ?>
	<tr>
		<td class="key">
			<?php echo JText::_('COM_VIRTUEMART_CURRENCY_SYMBOL'); ?>
		</td>
		<td>
			<input class="inputbox" type="text" name="currency_symbol" id="currency_symbol" size="20" value="<?php echo $this->currency->currency_symbol; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_('COM_VIRTUEMART_CURRENCY_DECIMALS'); ?>
		</td>
		<td>
			<input class="inputbox" type="text" name="currency_decimal_place" id="currency_decimal_place" size="20" value="<?php echo $this->currency->currency_decimal_place; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_('COM_VIRTUEMART_CURRENCY_DECIMALSYMBOL'); ?>
		</td>
		<td>
			<input class="inputbox" type="text" name="currency_decimal_symbol" id="currency_decimal_symbol" size="10" value="<?php echo $this->currency->currency_decimal_symbol; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_('COM_VIRTUEMART_CURRENCY_THOUSANDS'); ?>
		</td>
		<td>
			<input class="inputbox" type="text" name="currency_thousands" id="currency_thousands" size="10" value="<?php echo $this->currency->currency_thousands; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_('COM_VIRTUEMART_CURRENCY_POSITIVE_DISPLAY'); ?>
		</td>
		<td >
			<input class="inputbox" type="text" name="currency_positive_style" id="currency_positive_style" size="50" value="<?php echo $this->currency->currency_positive_style; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_('COM_VIRTUEMART_CURRENCY_NEGATIVE_DISPLAY'); ?>
		</td>
		<td>
			<input class="inputbox" type="text" name="currency_negative_style" id="currency_negative_style" size="50" value="<?php echo $this->currency->currency_negative_style; ?>" />
		</td>
	</tr>
	</table>
	<?php echo JText::_('COM_VIRTUEMART_CURRENCY_DISPLAY_EXPL'); ?>
	</fieldset>

</div>
	<input type="hidden" name="virtuemart_vendor_id" value="<?php echo $this->currency->virtuemart_vendor_id; ?>" />
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="virtuemart_currency_id" value="<?php echo $this->currency->virtuemart_currency_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="currency" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>


<?php 
AdminUIHelper::imitateTabs('end');
AdminUIHelper::endAdminArea(); ?>