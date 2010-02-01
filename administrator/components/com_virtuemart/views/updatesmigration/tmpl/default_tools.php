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
?>
<table cellpadding="10">
    <tr>
	<td align="center">
	    <a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesMigration&task=installSampleData'); ?>">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_install_48.png">
	    </a>
	    <br />
	    Install<br />Sample Data
	</td>
	<td align="center">
	    <a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesMigration&task=userSync'); ?>">
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
	    <a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesMigration&task=restoreSystemDefaults'); ?>">
		<img src="components/com_virtuemart/assets/images/icon_48/vm_cpanel_48.png">
	    </a>
	    <br />
	    Restore<br />System Defaults
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
    </tr>
</table>	      