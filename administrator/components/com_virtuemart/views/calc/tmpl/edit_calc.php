<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Calculation tool
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: edit.php 3617 2011-07-05 12:55:12Z enytheme $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
VmConfig::jDate();
?>
<form action="index.php" method="post" name="adminForm">

<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_VIRTUEMART_CALC_DETAILS'); ?></legend>
	<table class="admintable">
		<tr>
			<td width="110" class="key">
				<label for="calc_name">
					<?php echo JText::_('COM_VIRTUEMART_CALC_NAME'); ?>
				</label>
			</td>
			<td width="110">
				<input class="inputbox" type="text" name="calc_name" id="calc_name" size="50" value="<?php echo $this->calc->calc_name; ?>" />
			</td>
			<td width="110"></td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="published">
					<?php echo JText::_('COM_VIRTUEMART_PUBLISHED'); ?>
				</label>
			</td>
			<td width="110"  >
				<fieldset class="radio" >
				<?php echo JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $this->calc->published); ?>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td width="110" class="key" >
				<label for="ordering">
				<?php echo JText::_('COM_VIRTUEMART_ORDERING'); ?>
				</label>
			</td>
			<td colspan="2">
				<input class="inputbox" type="text" name="ordering" id="ordering" size="4" value="<?php echo $this->calc->ordering; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="calc_descr">
					<?php echo JText::_('COM_VIRTUEMART_DESCRIPTION'); ?>
				</label>
			</td>
			<td colspan="3" >
				<input class="inputbox" type="text" name="calc_descr" id="calc_descr" size="80" value="<?php echo $this->calc->calc_descr; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="entrypoint">
					<?php echo JText::_('COM_VIRTUEMART_CALC_KIND'); ?>
				</label>
			</td>
			<td colspan="3">
				<?php echo $this->entryPointsList; ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="mathops">
					<?php echo JText::_('COM_VIRTUEMART_CALC_VALUE_MATHOP'); ?>
				</label>
			</td>
			<td colspan="3">
				<?php echo $this->mathOpList; ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="calc_value">
					<?php echo JText::_('COM_VIRTUEMART_VALUE'); ?>
				</label>
			</td>
			<td colspan="3">
				<input class="inputbox" type="text" name="calc_value" id="calc_value" size="4" value="<?php echo $this->calc->calc_value; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="calc_currency">
					<?php echo JText::_('COM_VIRTUEMART_CURRENCY'); ?>
				</label>
			</td>
			<td colspan="3">
			<?php echo JHTML::_('Select.genericlist', $this->currencies, 'calc_currency', '', 'virtuemart_currency_id', 'currency_name', $this->calc->calc_currency); ?>

			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="calc_categories">
					<?php echo JText::_('COM_VIRTUEMART_CATEGORY'); ?>
				</label>
			</td>
			<td colspan="3">
				<select class="inputbox" id="calc_categories" name="calc_categories[]" multiple="multiple" size="10">
					<?php echo $this->categoryTree; ?>
				</select>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="shoppergroup">
					<?php echo JText::_('COM_VIRTUEMART_SHOPPERGROUP_IDS'); ?>
				</label>
			</td>
			<td colspan="3">
				<?php echo $this->shopperGroupList ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="coutry">
				<?php echo JText::_('COM_VIRTUEMART_COUNTRY'); ?>
				</label>
			</td>
			<td>
				<?php echo $this->countriesList?>
			</td>
		
		
			<td width="110" class="key">
		    	<?php echo JText::_('COM_VIRTUEMART_STORE_FORM_STATE'); ?>
			</td>
			<td>
		    	<?php echo $this->statesList?>
			</td>

		<tr>
			<td width="110" class="key">
				<label for="calc_shopper_published">
					<?php echo JText::_('COM_VIRTUEMART_VISIBLE_FOR_SHOPPER'); ?>
				</label>
			</td>
			<td colspan="3">
				<fieldset class="radio">
				<?php echo JHTML::_('select.booleanlist',  'calc_shopper_published', 'class="inputbox"', $this->calc->calc_shopper_published); ?>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="calc_vendor_published">
					<?php echo JText::_('COM_VIRTUEMART_VISIBLE_FOR_VENDOR'); ?>
				</label>
			</td>
			<td colspan="3">
				<fieldset class="radio">
				<?php echo JHTML::_('select.booleanlist',  'calc_vendor_published', 'class="inputbox"', $this->calc->calc_vendor_published); ?>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="publish_up">
					<?php echo JText::_('COM_VIRTUEMART_START_DATE'); ?>
				</label>
			</td>
			<td colspan="3">
				<?php
					$startDate = JFactory::getDate($this->calc->publish_up,$this->tzoffset);
					//echo JHTML::_('calendar', $startDate->toFormat($this->dateformat), "publish_up", "publish_up", $this->dateformat);
					echo VmConfig::jDate($startDate->toFormat($this->dateformat), 'publish_up'); 
 				?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="publish_down">
					<?php echo JText::_('COM_VIRTUEMART_END_DATE'); ?>
				</label>
			</td>
			<td colspan="3">
				<?php $endDate;
				if (empty($this->calc->publish_down) || !strcmp($this->calc->publish_down,'0000-00-00 00:00:00')  ) {
					$endDate = '';//JText::_('COM_VIRTUEMART_NEVER');
				} else {
					$date = JFactory::getDate($this->calc->publish_down,$this->tzoffset);
					$endDate = $date->toFormat($this->dateformat);
				}
				echo VmConfig::jDate($endDate, 'publish_down'); 
				//echo JHTML::_('calendar', $endDate, "publish_down", "publish_down", $this->dateformat,array('class'=>'inputbox', 'size'=>'25',  'maxlength'=>'19')); ?>

			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="calc_amount_cond">
					<?php echo JText::_('COM_VIRTUEMART_CALC_AMOUNT_COND'); ?>
				</label>
			</td>
			<td colspan="3">
				<input class="inputbox" type="text" name="calc_amount_cond" id="calc_amount_cond" size="4" value="<?php echo $this->calc->calc_amount_cond; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="calc_amount_dimunit">
				<?php echo JText::_('COM_VIRTUEMART_CALC_AMOUNT_DIMUNIT'); ?>
				</label>
			</td>
			<td colspan="3">
				<input class="inputbox" type="text" name="calc_amount_dimunit" id="calc_amount_cond" size="4" value="<?php echo $this->calc->calc_amount_dimunit; ?>" />
			</td>
		</tr>
		<?php if($this->perms->check('admin')){?>
		<tr>
			<td width="110" class="key">
				<label for="vendor">
				<?php echo JText::_('COM_VIRTUEMART_VENDOR'); ?>
				</label>
			</td>
     		<td width="69%" colspan="3"><?php
				echo $this->vendorList ?>
      		</td>
		</tr>
		<?php } ?>
	</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="virtuemart_calc_id" value="<?php echo $this->calc->virtuemart_calc_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="calc" />
	<input type="hidden" name="virtuemart_vendor_id" value="<?php echo $this->vendorId; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>

</form>