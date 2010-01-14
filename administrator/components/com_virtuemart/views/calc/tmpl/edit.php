<?php 
defined('_JEXEC') or die('Restricted access');

AdminMenuHelper::startAdminArea(); 
?>

<form action="index.php" method="post" name="adminForm">

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
				<input class="inputbox" type="text" name="calc_kind" id="calc_kind" size="12" value="<?php echo $this->calc->calc_kind; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('Mathematical operation'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="calc_value_mathop" id="calc_value_mathop" size="4" value="<?php echo $this->calc->calc_value_mathop; ?>" />
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
	</table>
	</fieldset>
	<?php print_r($this->calc);?>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="calc_id" value="<?php echo $this->calc->calc_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="calc" />
</form>

<?php AdminMenuHelper::endAdminArea(); ?> 