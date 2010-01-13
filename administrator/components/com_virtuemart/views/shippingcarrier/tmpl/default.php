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
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->shippingCarriers); ?>);" />
			</th>			            		
			<th>
				<?php echo JText::_( 'VM_CARRIER_LIST_NAME_LBL' ); ?>
			</th>				
			<th>
				<?php echo JText::_( 'VM_CARRIER_LIST_ORDER_LBL' ); ?>
			</th>																		
		</tr>
		</thead>
		<?php
		$k = 0;
		for ($i=0, $n=count( $this->shippingCarriers ); $i < $n; $i++) {
			$row =& $this->shippingCarriers[$i];
			/** 
			 * @todo Add to database layout published column
			 */
			$row->published = 1;
			$checked = JHTML::_('grid.id', $i, $row->shipping_carrier_id);
			$editlink = JROUTE::_('index.php?option=com_virtuemart&controller=shippingcarrier&task=edit&cid[]=' . $row->shipping_carrier_id);
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td width="10">
					<?php echo $checked; ?>
				</td>							            
				<td align="left">
					<?php echo JHTML::_('link', $editlink, JText::_($row->shipping_carrier_name)); ?>
				</td>					
				<td align="left">
					<?php echo JText::_($row->shipping_carrier_list_order); ?>
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
	<input type="hidden" name="controller" value="shippingcarrier" />
	<input type="hidden" name="view" value="shippingcarrier" />	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
            
            
            
<?php AdminMenuHelper::endAdminArea(); ?> 