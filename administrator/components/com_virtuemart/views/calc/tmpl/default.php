<?php 
defined('_JEXEC') or die('Restricted access'); 

AdminMenuHelper::startAdminArea(); 
//if($vendor_id==1 || $perm->check( 'admin' )){

?>
      	
<form action="index.php" method="post" name="adminForm">
	<div id="editcell">
		<table class="adminlist">
		<thead>
		<tr>
			<th>
				<?php echo JText::_( '#' ); ?>
			</th>		            
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->calcs); ?>);" />
			</th>			
			<th width="60">
				<?php echo JText::_( 'VM_CALC_LIST_NAME' ); ?>
			</th>
			<?php if($this ->perm->check( 'admin' )){ ?>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_VENDOR' );  ?>
			</th><?php }?>						
			<th width="20">
				<?php echo JText::_( 'VM_CALC_DESCR' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_ORDERING' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_KIND' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_VALUE_MATHOP' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_VALUE' ); ?>
			</th>
			<th width="10">
				<?php echo JText::_( 'VM_CALC_CURRENCY' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_CATEGORY' ); ?>
			</th>			
			<th width="10">
				<?php echo JText::_( 'VM_CALC_VIS_SHOPPER' ); ?>
			</th>
			<th width="10">
				<?php echo JText::_( 'VM_CALC_VIS_VENDOR' ); ?>
			</th>

			<th width="20">
				<?php echo JText::_( 'VM_CALC_START_DATE' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_END_DATE' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_AMOUNT_COND' ); ?>
			</th>
			<th width="10">
				<?php echo JText::_( 'VM_CALC_AMOUNT_DIMUNIT' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_LOCATION' ); ?>
			</th>
			<th width="10">
				<?php echo JText::_( 'PUBLISHED' ); ?>
			</th>
			<th width="10">
				<?php echo JText::_( 'VM_CALC_SHARED' ); ?>
			</th>
		</tr>
		</thead>
		<?php
		$k = 0;

		for ($i=0, $n=count( $this->calcs ); $i < $n; $i++) {
			
			$row = $this->calcs[$i];
			$checked = JHTML::_('grid.id', $i, $row->calc_id);
			$published = JHTML::_('grid.published', $row, $i);
			$calc_vis_shopper = $this->model->published($row, $i, calc_shopper_published);
			$calc_vis_vendor = $this->model->published($row, $i, calc_vendor_published);
			$calc_shared = $this->model->published($row, $i, shared);
//			$calc_vis_vendor = JHTML::_('grid.published', $row, $i,'tick.png','publish_x.png','cal_vendor');

			$editlink = JROUTE::_('index.php?option=com_virtuemart&controller=calc&task=edit&cid[]=' . $row->calc_id);
			//$statelink	= JROUTE::_('index.php?option=com_virtuemart&view=calc&calc_id=' . $row->calc_id);
			$deletelink	= JROUTE::_('index.php?option=com_virtuemart&controller=calc&task=remove&cid[]=' . $row->calc_id);
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td width="10" align="right">
					<?php echo $row->calc_id; ?>
				</td>			            
				<td width="10">
					<?php echo $checked; ?>
				</td>
				<td align="left">
					<a href="<?php echo $editlink; ?>"><?php echo $row->calc_name; ?></a>
				</td>
				<?php if($this ->perm->check( 'admin' )){?>				
				<td align="left">
					<?php echo JText::_($row->calc_vendor_id); ?>
				</td>
				<?php } ?>
				<td>
					<?php echo JText::_($row->calc_descr); ?>
				</td>
				<td>
					<?php echo JText::_($row->ordering); ?>
				</td>				
				<td>
					<?php echo JText::_($row->calc_kind); ?>
				</td>
				<td>
					<?php echo JText::_($row->calc_value_mathop); ?>
				</td>
				<td>
					<?php echo JText::_($row->calc_value); ?>
				</td>
				<td>
					<?php echo JText::_($row->calc_currency); ?>
				</td>				
				<td>
					<?php echo JText::_($row->calc_categories); ?>
				</td>
				<td align="center">
					<?php echo $calc_vis_shopper; ?>
				</td>
				<td align="center">
					<?php echo $calc_vis_vendor; ?>
				</td>

				<td>
					<?php echo JText::_($row->calc_start_date); ?>
				</td>
				<td>
					<?php echo JText::_($row->calc_end_date); ?>
				</td>
				<td>
					<?php echo JText::_($row->calc_amount_cond); ?>
				</td>
				<td>
					<?php echo JText::_($row->calc_amount_dimunit); ?>
				</td>
				<td>
					<?php echo JText::_($row->calc_location); ?>
				</td>
				<td align="center">
					<?php echo $published; ?>
				</td>
				<td align="center">
					<?php echo $calc_shared ?>
				</td>				        																														
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		<tfoot>
			<tr>
				<td colspan="17">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>		
	</table>	
</div>
	        
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="controller" value="calc" />
	<input type="hidden" name="view" value="calc" />	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
            
            
<?php AdminMenuHelper::endAdminArea(); ?> 