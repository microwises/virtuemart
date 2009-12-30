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
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->stores); ?>);" />
		    </th>
		    <th>
			<?php echo JText::_( 'VM_STORE_FORM_STORE_NAME' ); ?>
		    </th>
		    <th>
			<?php echo JText::_( 'VM_CREDITCARD_CODE' ); ?>
		    </th>
		</tr>
	    </thead>
	    <tbody>
		<?php
		$k = 0;
		for ($i=0, $n=count( $this->stores ); $i < $n; $i++) {
		    $row = $this->stores[$i];
		    $checked = JHTML::_('grid.id', $i, $row->vendor_id);
		    $editlink = JROUTE::_('index.php?option=com_virtuemart&view=store&task=edit&cid[]=' . $row->vendor_id);
		    ?>
		<tr class="<?php echo "row$k"; ?>">
		    <td width="10">
			    <?php echo $checked; ?>
		    </td>
		    <td align="left">
			    <?php echo JHTML::_('link', $editlink, JTEXT::_($row->vendor_store_name)); ?>
		    </td>
		    <td>
			    <?php echo JText::_($row->vendor_name); ?>
		    </td>
		</tr>
		    <?php
		    $k = 1 - $k;
		}
		?>
	    </tbody>
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
    <input type="hidden" name="controller" value="store" />
    <input type="hidden" name="view" value="store" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
</form>



<?php AdminMenuHelper::endAdminArea(); ?>