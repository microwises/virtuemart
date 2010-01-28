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
* @version $Id$
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

AdminMenuHelper::startAdminArea(); 
?>

<form action="index.php" method="post" name="adminForm">
<?php //echo print_r($this->c) ?>
<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'Calculation Rule Details' ); ?></legend>
	<table class="admintable">			
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'Calculation Name' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="calc_name" id="calc_name" size="50" value="<?php echo $this->calc->calc_name; ?>" />				
			</td>
		</tr>	
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('Published'); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $this->calc->published); ?>
			</td>
		</tr>			
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('Description'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="calc_descr" id="calc_descr" size="200" value="<?php echo $this->calc->calc_descr; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('Kind of the rule'); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->entryPointsList; ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('Mathematical operation'); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->mathOpList; ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('Value'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="calc_value" id="calc_value" size="4" value="<?php echo $this->calc->calc_value; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('Affected Categories'); ?>:
				</label>
			</td>			
			<td>
				<select class="inputbox" id="calc_categories" name="calc_categories[]" multiple="multiple" size="10">
					<?php echo $this->category_tree; ?>
				</select>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('Affected Shoppergroups'); ?>:
				</label>
			</td>			
			<td>
				<?php echo $this->shopper_tree; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('VM_STORE_FORM_COUNTRY'); ?>:
			</td>
			<td>
				<?php echo ShopFunctions::renderCountryList($this->store->userInfo->country,1);?>
			</td>
		</tr>
		<tr>
			<td class="key">
		    	<?php echo JText::_('VM_STORE_FORM_STATE'); ?>:
			</td>
			<td>
		    	<?php echo ShopFunctions::renderStateList($this->store->userInfo->state, $this->store->userInfo->country, 'country_id','',1);?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('Visible for shopper'); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist',  'calc_shopper_published', 'class="inputbox"', $this->calc->calc_shopper_published); ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('Visible for vendor'); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist',  'calc_vendor_published', 'class="inputbox"', $this->calc->calc_vendor_published); ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('start Date'); ?>:
				</label>
			</td>
			<td>			
				<?php $startDate = JFactory::getDate($this->calc->publish_up);
				echo JHTML::_('calendar', $startDate->toFormat(VM_DATE_FORMAT), "publish_up", "publish_up", VM_DATE_FORMAT); ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('end Date'); ?>:
				</label>
			</td>
			<td>			
				<?php $endDate = JFactory::getDate($this->calc->publish_down);
				echo JHTML::_('calendar', $endDate->toFormat(VM_DATE_FORMAT), "publish_down", "publish_down", VM_DATE_FORMAT); ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_CALC_AMOUNT_COND'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="calc_amount_cond" id="calc_amount_cond" size="4" value="<?php echo $this->calc->calc_amount_cond; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
				<?php echo JText::_('VM_CALC_AMOUNT_DIMUNIT'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="calc_amount_cond" id="calc_amount_cond" size="4" value="<?php echo $this->calc->calc_amount_dimunit; ?>" />
			</td>
		</tr>	
	</table>
	</fieldset>
	<?php //print_r($this->calc);?>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="calc_id" value="<?php echo $this->calc->calc_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="calc" />
	<input type="hidden" name="calc_vendor_id" value="<?php echo $this->vendorId; ?>" />

</form>

<?php AdminMenuHelper::endAdminArea(); ?> 