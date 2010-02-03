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
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->userfields); ?>);" />
		    </th>
		    <th>
			<?php echo JText::_( 'VM_USERFIELD_LIST_NAME' ); ?>
		    </th>
		    <th>
			<?php echo JText::_( 'VM_USERFIELD_LIST_TITLE' ); ?>
		    </th>
		    <th>
			<?php echo JText::_( 'VM_USERFIELD_LIST_TYPE' ); ?>
		    </th>
		    <th>
			<?php echo JText::_( 'VM_USERFIELD_LIST_REQUIRED' ); ?>
		    </th>
		    <th width="20">
			<?php echo JText::_( 'PUBLISH' ); ?>
		    </th>
		    <th width="20">
			<?php echo JText::_( 'VM_USERFIELD_LIST_REGISTRATION' ); ?>
		    </th>
		    <th width="20">
			<?php echo JText::_( 'VM_USERFIELD_LIST_SHIPPING' ); ?>
		    </th>
		    <th width="20">
			<?php echo JText::_( 'VM_USERFIELD_LIST_MAINTENANCE' ); ?>
		    </th>
		</tr>
	    </thead>
	    <?php
	    $k = 0;
	    for ($i=0, $n=count( $this->userfields ); $i < $n; $i++) {
		$row = $this->userfields[$i];

		$checked = JHTML::_('grid.id', $i, $row->userfield_id);
		$published = JHTML::_('grid.published', $row, $i);		
		$editlink = JROUTE::_('index.php?option=com_virtuemart&controller=userfield&task=edit&cid[]=' . $row->userfield_id);
		
		?>
	    <tr class="<?php echo "row$k"; ?>">
		<td width="10">
			<?php echo $checked; ?>
		</td>
		<td align="left">
		    <a href="<?php echo $editlink; ?>"><?php echo $row->userfield_name; ?></a>
		</td>
		<td align="left">
			<?php echo JText::_($row->userfield_title); ?>
		</td>
		<td>
			<?php echo JText::_($row->userfield_type); ?>
		</td>
		<td>
			<?php echo JText::_($row->userfield_required); ?>
		</td>
		<td align="center">
			<?php echo $row->required; ?>
		</td>
		<td align="center">
			<?php echo $published; ?>
		</td>
		<td align="center">
			<?php echo $row->required; ?>
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
    <input type="hidden" name="controller" value="country" />
    <input type="hidden" name="view" value="country" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
</form>


<?php AdminMenuHelper::endAdminArea(); ?> 