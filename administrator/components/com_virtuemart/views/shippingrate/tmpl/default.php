<?php 
defined('_JEXEC') or die('Restricted access'); 

AdminMenuHelper::startAdminArea(); 

?>
      	
<form action="index.php" method="post" name="adminForm">
	<div id="editcell">
		<table class="adminlist">
		<thead>
		<tr>
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->shippingRates); ?>);" />
			</th>			            		
			<th>
				<?php echo JText::_( 'VM_SHIPPING_RATE_LIST_CARRIER_LBL' ); ?>
			</th>				
			<th>
				<?php echo JText::_( 'VM_SHIPPING_RATE_LIST_RATE_NAME' ); ?>
			</th>				
			<th>
				<?php echo JText::_( 'VM_SHIPPING_RATE_LIST_RATE_WSTART' ); ?>
			</th>	
			<th>
				<?php echo JText::_( 'VM_SHIPPING_RATE_LIST_RATE_WEND' ); ?>
			</th>																						
		</tr>
		</thead>
		<?php
		$k = 0;
		for ($i=0, $n=count( $this->shippingRates ); $i < $n; $i++) {
			$row =& $this->shippingRates[$i];
			/** 
			 * @todo Add to database layout published column
			 */
			$row->published = 1;
			$checked = JHTML::_('grid.id', $i, $row->shipping_rate_id);
			$editlink = JROUTE::_('index.php?option=com_virtuemart&controller=shippingrate&task=edit&cid[]=' . $row->shipping_rate_id);
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td width="10">
					<?php echo $checked; ?>
				</td>							            
				<td align="left">
					<?php echo JText::_($row->shipping_carrier_name); ?>
				</td>					
				<td align="left">
					<?php echo JHTML::_('link', $editlink, JText::_($row->shipping_rate_name)); ?>
				</td>	
				<td align="left">
					<?php echo JText::_($row->shipping_rate_weight_start); ?>
				</td>					
				<td align="left">
					<?php echo JText::_($row->shipping_rate_weight_end); ?>
				</td>											        																														
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>		
	</table>	
</div>
	        
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="controller" value="shippingrate" />
	<input type="hidden" name="view" value="shippingrate" />	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
            
            
            
<?php AdminMenuHelper::endAdminArea(); ?> 