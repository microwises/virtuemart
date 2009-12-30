<?php 
defined('_JEXEC') or die('Restricted access');

AdminMenuHelper::startAdminArea(); 
?>

<form action="index.php" method="post" name="adminForm">

<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'State Details' ); ?></legend>
	<table class="admintable">			
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'State Name' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="state_name" id="state_name" size="50" value="<?php echo $this->state->state_name; ?>" />				
			</td>
		</tr>	
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('Shipping Zone'); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('Select.genericlist', $this->shippingZones, 'zone_id', '', 'zone_id', 'zone_name', $this->state->zone_id); ?>			
			</td>
		</tr>		
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'State (3) Code' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="state_3_code" id="state_3_code" size="10" value="<?php echo $this->state->state_3_code; ?>" />				
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'State (2) Code' ); ?>:
				</label>
			</td>
			<td>
				<?php/* echo JHTML::_('Select.radiolist', $this->shippingZones, 'zone_id', '', 'zone_id', 'zone_name', $this->country->zone_id);*/ ?>							
				<input class="inputbox" type="text" name="state_2_code" id="state_2_code" size="10" value="<?php echo $this->state->state_2_code; ?>" />				
			</td>
		</tr>																
	</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="country_id" value="<?php echo $this->state->country_id; ?>" />
	<input type="hidden" name="state_id" value="<?php echo $this->state->state_id; ?>" />
	<input type="hidden" name="published" value="<?php echo $this->state->published; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="state" />
</form>


<?php AdminMenuHelper::endAdminArea(); ?> 