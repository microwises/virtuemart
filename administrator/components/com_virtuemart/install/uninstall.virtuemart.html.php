<?php
/**
 * VirtueMart uninstall HTML file.
 *
 * @author Max Milbers, RickG
 * @package VirtueMart
 */

defined('_JEXEC') or die('Restricted access');
$lang = JFactory::getLanguage();
$lang->load('com_virtuemart.sys',JPATH_ADMINISTRATOR);
$lang->load('com_virtuemart',JPATH_ADMINISTRATOR);
?>

<div>
	<table width="100%" border="0">
	<tr>
		<td>
			<h1><?php echo JText::_('COM_VIRTUEMART_UNINSTALL_THANKYOU') ; ?></h1>
		</td>
	</tr>
	<tr>
		<td valign="top" align="center">
			<h2><?php echo JText::_('COM_VIRTUEMART_UNINSTALL_NOTE') ; ?></h2>
			<p>
				<big><?php echo JText::_('COM_VIRTUEMART_UNINSTALL_NOTE_DESC') ; ?></big>
			</p>
		</td>
	</tr>
	</table>
</div>
