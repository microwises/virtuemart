<?php 
defined('_JEXEC') or die('Restricted access');

AdminMenuHelper::startAdminArea(); 
?>

<form action="index.php" method="post" name="adminForm">


<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_('Currency Details'); ?></legend>
	<table class="admintable">			
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'VM_CURRENCY_LIST_NAME' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="currency_name" id="currency_name" size="50" value="<?php echo $this->currency->currency_name; ?>" />				
			</td>
		</tr>					
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'VM_CURRENCY_LIST_CODE' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="currency_code" id="currency_code" size="3" value="<?php echo $this->currency->currency_code; ?>" />				
			</td>
		</tr>					
	</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="currency_id" value="<?php echo $this->currency->currency_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="currency" />
</form>


<?php AdminMenuHelper::endAdminArea(); ?> 