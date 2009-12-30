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
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->currencies); ?>);" />
		    </th>
		    <th>
			<?php echo JText::_( 'VM_CURRENCY_LIST_NAME' ); ?>
		    </th>
		    <th>
			<?php echo JText::_( 'VM_CURRENCY_LIST_CODE' ); ?>
		    </th>
		</tr>
	    </thead>
	    <?php
	    $k = 0;
	    for ($i=0, $n=count( $this->currencies ); $i < $n; $i++) {
		$row =& $this->currencies[$i];
		/**
		 * @todo Add to database layout published column
		 */
		$row->published = 1;
		$checked = JHTML::_('grid.id', $i, $row->currency_id);
		$published = JHTML::_('grid.published', $row, $i);
		$editlink = JROUTE::_('index.php?option=com_virtuemart&controller=currency&task=edit&cid[]=' . $row->currency_id);
		?>
	    <tr class="<?php echo "row$k"; ?>">
		<td width="10">
			<?php echo $checked; ?>
		</td>				
		<td align="left">
		    <a href="<?php echo $editlink; ?>"><?php echo JText::_($row->currency_name); ?></a>
		</td>
		<td align="left">
			<?php echo JText::_($row->currency_code); ?>
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
    <input type="hidden" name="controller" value="currency" />
    <input type="hidden" name="view" value="currency" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
</form>



<?php AdminMenuHelper::endAdminArea(); ?> 