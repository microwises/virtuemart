<?php 
defined('_JEXEC') or die('Restricted access'); 
?>
<table cellpadding="10">
    <tr>
	<td align="center">
	    <a onclick="alert('Please don\'t interrupt the next Step! \n It is essential for running VirtueMart.');" href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesMigration&task=installSampleData'); ?>">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_install_48.png">
	    </a>
	    <br />
	    Install<br />Sample Data
	</td>
	<td align="center">
	    <a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesMigration&task=deleteVmData'); ?>">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_trash_48.png">
	    </a>
	    <br />
	    Remove<br />Virtuemart Data
	</td>
	<td align="center">
	    <a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesMigration&task=deleteVmTables'); ?>">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_trash_48.png">
	    </a>
	    <br />
	    Remove<br />Virtuemart Tables
	</td>
	<td align="center">
	    &nbsp;
	</td>
    </tr>
</table>	      