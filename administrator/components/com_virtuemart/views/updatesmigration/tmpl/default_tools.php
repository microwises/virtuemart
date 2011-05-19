<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage UpdatesMigration
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

echo JText::_('COM_VIRTUEMART_UPDATE_MIGRATION_TOOLS_WARNING');
?>
<table cellpadding="10">
    <tr>
	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=installSampleData'); ?>
	    <span onclick="javascript:confirmation('<?php echo JText::_('COM_VIRTUEMART_UPDATE_INSTALLSAMPLE_CONFIRM'); ?>', '<?php echo $link; ?>');">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_install_48.png">
	    </span>
	    <br />
	    Install<br />Sample Data
	</td>
	<td align="center">
	    <a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=userSync'); ?>">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_shoppers_48.png">
	    </a>
	    <br />
	    Sync Joomla<br />Users
	</td>
	<td align="center">
	    &nbsp;
	</td>
	<td align="center">
	    &nbsp;
	</td>
    </tr>
    <tr><td colspan="4"></td></tr>
    <tr>
	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=restoreSystemDefaults'); ?>
	    <span onclick="javascript:confirmation('<?php echo JText::_('COM_VIRTUEMART_UPDATE_RESTOREDEFAULTS_CONFIRM'); ?>', '<?php echo $link; ?>');">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_cpanel_48.png">
	    </span>
	    <br />
	    Restore<br />System Defaults
	</td>
	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=deleteVmData'); ?>
	    <span onclick="javascript:confirmation('<?php echo JText::_('COM_VIRTUEMART_UPDATE_REMOVEDATA_CONFIRM'); ?>', '<?php echo $link; ?>');">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_trash_48.png">
	    </span>
	    <br />
	    Remove<br />Virtuemart Data
	</td>
	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=deleteVmTables'); ?>
	    <span onclick="javascript:confirmation('<?php echo JText::_('COM_VIRTUEMART_UPDATE_REMOVETABLES_CONFIRM'); ?>', '<?php echo $link; ?>');">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_trash_48.png">
	    </span>
	    <br />
	    Remove<br />Virtuemart Tables
	</td>

    <td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=refreshCompleteInstall'); ?>
	    <span onclick="javascript:confirmation('<?php echo JText::_('COM_VIRTUEMART_DELETES_ALL_VM_TABLES_AND_FRESH'); ?>', '<?php echo $link; ?>');">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_trash_48.png">
	    </span>
	    <br />
	    Reset all tables <br /> and install sampledata
	</td>

	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=portCurrency'); ?>
	    <span onclick="javascript:confirmation('<?php echo 'Start port?'; ?>', '<?php echo $link; ?>');">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_trash_48.png">
	    </span>
	    <br />
	    Port old currency table to new
	</td>
    </tr>
</table>

<script type="text/javascript">
<!--
function confirmation(message, destnUrl) {
	var answer = confirm(message);
	if (answer) {
		window.location = destnUrl;
	}
}
//-->
</script>