<?php 
defined('_JEXEC') or die('Restricted access'); 
?>
<table>	
    <tr>
	<td align="center">
	    <a onclick="alert('Please don\'t interrupt the next Step! \n It is essential for running VirtueMart.');" href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesMigration&task=installSampleData'); ?>">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_install_48.png">
	    </a>
	    <br />
			Install Sample Data
	</td>
	<td align="center">
	    <a onclick="alert('Please don\'t interrupt the next Step! \n It is essential for running VirtueMart.');" href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesMigration&task=installSampleData'); ?>">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_install_48.png">
	    </a>
	    <br />
			Remove Virtuemart Data
	</td>
    </tr>
    <tr>
	<td align="center">
	    <a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesMigration&task=deleteVmTables'); ?>">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_install_48.png">
	    </a>
	    <br />
	    Remove all Virtuemart Tables
	</td>
	<td align="center">
	    <a onclick="alert('Please don\'t interrupt the next Step! \n It is essential for running VirtueMart.');" href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesMigration&task=installSampleData'); ?>">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_install_48.png">
	    </a>
	    <br />
			Remove Virtuemart Data
	</td>
    </tr>
</table>	      