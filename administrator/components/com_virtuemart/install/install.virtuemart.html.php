<?php
defined('_JEXEC') or die('Restricted access');
$lang = JFactory::getLanguage();
$lang->load('com_virtuemart.sys',JPATH_ADMINISTRATOR);
$lang->load('com_virtuemart',JPATH_ADMINISTRATOR);
?>

<link rel="stylesheet" href="components/com_virtuemart/assets/css/install.css" type="text/css" />

<div align="center">
	<table width="100%" border="0">
	<tr>
		<td valign="top" align="center">
			<a href="http://virtuemart.net" target="_blank">
				<img border="0" align="center" src="components/com_virtuemart/assets/images/vm_menulogo.png" alt="Cart" />
			</a>
			<br /><br />
			<h1><?php echo JText::_('COM_VIRTUEMART_WELCOME'); ?></h1>
		</td>
		<td>
			<h1><?php
			if(JRequest::getWord('newInstall',false)){
				echo JText::_('COM_VIRTUEMART_INSTALLATION_SUCCESSFUL');
			} else {
				echo JText::_('COM_VIRTUEMART_UPGRADE_SUCCESSFUL');
			}
			 ?></h1>
			<br /><br />

			<table width="50%">
			<tr>
				<?php
					if(JRequest::getWord('newInstall')){
					?>	<td width="50%" align="center">
						<a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=installSampleData&token='.JUtility::getToken()); ?>">
						<img src="components/com_virtuemart/assets/images/icon_48/vm_install_48.png">
						</a>
						<br />
						<?php echo JText::_('COM_VIRTUEMART_INSTALL_SAMPLE_DATA'); ?>
						</td>
				<?php	} ?>

				<td width="50%" align="center">
					<a href="<?php echo JROUTE::_('index.php?option=com_virtuemart'); ?>">
						<img src="components/com_virtuemart/assets/images/icon_48/vm_frontpage_48.png">
					</a>
					<br />
					<?php echo JText::_('COM_VIRTUEMART_INSTALL_GO_SHOP'); ?>
				</td>
			</tr>
			<tr>
				<td align="center" colspan="2"><br /><br /><hr /><br /></td>
			</tr>
			<tr>
				<td align="center">
					<?php //echo JText::_('COM_VIRTUEMART_INSTALL_FURTHER_HELP'); ?>
				</td>
				<td align="center">

				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</div>