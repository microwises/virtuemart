<?php 
defined('_JEXEC') or die('Restricted access');

AdminMenuHelper::startAdminArea(); 
?>

<form action="index.php" method="post" name="adminForm">


<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'Country Details' ); ?></legend>
	<table class="admintable">			
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'Country Name' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="country_name" id="country_name" size="50" value="<?php echo $this->country->country_name; ?>" />				
			</td>
		</tr>	
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('Published'); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $this->country->published); ?>							
			</td>
		</tr>			
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('Shipping Zone'); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('Select.genericlist', $this->shippingZones, 'zone_id', '', 'zone_id', 'zone_name', $this->country->zone_id); ?>			
			</td>
		</tr>		
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'Country (3) Code' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="country_3_code" id="country_3_code" size="10" value="<?php echo $this->country->country_3_code; ?>" />				
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'Country (2) Code' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="country_2_code" id="country_2_code" size="10" value="<?php echo $this->country->country_2_code; ?>" />				
			</td>
		</tr>					
	</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="country_id" value="<?php echo $this->country->country_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="country" />
</form>

<?php AdminMenuHelper::endAdminArea(); ?> 