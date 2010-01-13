<?php 
defined('_JEXEC') or die('Restricted access');

AdminMenuHelper::startAdminArea(); 
?>

<form action="index.php" method="post" name="adminForm">


<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_('Shipping Carrier'); ?></legend>
	<table class="admintable">			
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'VM_CARRIER_FORM_NAME' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="shipping_carrier_name" id="shipping_carrier_name" size="50" value="<?php echo $this->carrier->shipping_carrier_name; ?>" />				
			</td>
		</tr>					
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'VM_CARRIER_FORM_LIST_ORDER' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="shipping_carrier_list_order" id="shipping_carrier_list_order" size="3" value="<?php echo $this->carrier->shipping_carrier_list_order; ?>" />				
			</td>
		</tr>					
	</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="shipping_carrier_id" value="<?php echo $this->carrier->shipping_carrier_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="shippingcarrier" />
</form>


<?php AdminMenuHelper::endAdminArea(); ?> 