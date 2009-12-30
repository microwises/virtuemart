<?php 
defined('_JEXEC') or die('Restricted access');

AdminMenuHelper::startAdminArea(); 
?>

<form action="index.php" method="post" name="adminForm">

<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'Coupon Details' ); ?></legend>
	<table class="admintable">			
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_COUPON_COUPON_HEADER'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="coupon_code" id="coupon_code" size="20" value="<?php echo JText::_($this->coupon->coupon_code); ?>" />				
			</td>
		</tr>		
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_COUPON_PERCENT_TOTAL'); ?>:
				</label>
			</td>
			<td>
				<?php 
				$radioOptions = '';
				$radioOptions[] = JHTML::_('select.option', 'percent', JText::_('VM_COUPON_PERCENT'));
				$radioOptions[] = JHTML::_('select.option', 'total', JText::_('VM_COUPON_TOTAL'));
				echo JHTML::_('select.radiolist',  $radioOptions, 'percent_or_total', '', 'value', 'text', $this->coupon->percent_or_total); 
				?>							
			</td>
		</tr>		
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_COUPON_TYPE'); ?>:
				</label>
			</td>
			<td>
				<?php 
				$listOptions = '';
				$listOptions[] = JHTML::_('select.option', 'permanent', JText::_('VM_COUPON_TYPE_PERMANENT'));
				$listOptions[] = JHTML::_('select.option', 'gift', JText::_('VM_COUPON_TYPE_GIFT'));
				echo JHTML::_('select.genericlist',  $listOptions, 'coupon_type', '', 'value', 'text', $this->coupon->coupon_type); 
				?>				
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_COUPON_VALUE'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="coupon_value" id="coupon_value" size="10" value="<?php echo $this->coupon->coupon_value; ?>" />										
			</td>
		</tr>		
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_COUPON_VALUE_VALID_AT'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="coupon_value_valid" id="coupon_value_valid" size="10" value="<?php echo $this->coupon->coupon_value_valid; ?>" />				
			</td>
		</tr>							
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_COUPON_START'); ?>:
				</label>
			</td>
			<td>
				<?php 
                $mydate = JFactory::getDate($this->coupon->coupon_start_date);
                echo JHTML::_('calendar', $mydate->toFormat(VM_DATE_FORMAT), "coupon_start_date", "coupon_start_date", VM_DATE_FORMAT); 
                ?>
			</td>
		</tr>		
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_COUPON_EXPIRY'); ?>:
				</label>
			</td>
			<td>
				<?php 
                $expireDate = JFactory::getDate($this->coupon->coupon_expiry_date);
                echo JHTML::_('calendar', $expireDate->toFormat(VM_DATE_FORMAT), "coupon_expiry_date", "coupon_expiry_date", VM_DATE_FORMAT); 
                ?>				
			</td>
		</tr>		
	</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="coupon_id" value="<?php echo $this->coupon->coupon_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="coupon" />
</form>


<?php AdminMenuHelper::endAdminArea(); ?> 