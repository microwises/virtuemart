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

if(!VmConfig::get('dangeroustools', false)){
	$uri = JFactory::getURI();
	$link = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=config';
	?>

	<div class="vmquote" style="text-align:left;margin-left:20px;">
	<span style="font-weight:bold;color:green;"> <?php echo JText::sprintf('COM_VIRTUEMART_SYSTEM_DANGEROUS_TOOL_ENABLED',JText::_('COM_VIRTUEMART_ADMIN_CFG_DANGEROUS_TOOLS'),$link) ?></span>
	</div>

	<?php
}

?>
<div id="cpanel">
<table cellpadding="10">
    <tr>
<?php /*	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=installSampleData&token='.JUtility::getToken()); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo JText::_('COM_VIRTUEMART_UPDATE_INSTALLSAMPLE_CONFIRM'); ?>', '<?php echo $link; ?>');">
		<span class="vmicon48 vm_install_48"></span>
	    <br /><?php echo JText::_('COM_VIRTUEMART_SAMPLE_DATA'); ?>
		</a></div>
	</td>
	<td align="center">
	    <a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=userSync&token='.JUtility::getToken()); ?>">
		<span class="vmicon48 vm_shoppers_48"></span>
	    </a>
	    <br /><?php echo JText::_('COM_VIRTUEMART_SYNC_JOOMLA_USERS'); ?>
		</a></div>
	</td>*/ ?>
 	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=portMedia&token='.JUtility::getToken() ); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo JText::sprintf('COM_VIRTUEMART_UPDATE_MIGRATION_STRING_CONFIRM', JText::_('COM_VIRTUEMART_MEDIA_S')); ?>', '<?php echo $link; ?>');">
			<span class="vmicon48 vm_shop_products_48"></span>
			<br /><?php echo JText::_('COM_VIRTUEMART_SYNC_MEDIA_FILES'); ?>
	    
		</a></div>
	</td>
	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=renewConfig&token='.JUtility::getToken() ); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo JText::_('COM_VIRTUEMART_TOOLS_RENEW_CONFIG_CONFIRM'); ?>', '<?php echo $link; ?>');">
		<span class="vmicon48 vm_install_48"></span>
	    <br />
            <?php echo Jtext::_('COM_VIRTUEMART_TOOLS_RENEW_CONFIG'); ?>
		
		</a></div>
	</td>
	<td align="left" colspan="3" >
		<?php echo JText::sprintf('COM_VIRTUEMART_UPDATE_MEDIAS_EXPLAIN',VmConfig::get('media_product_path') ,VmConfig::get('media_category_path') , VmConfig::get('media_manufacturer_path')); ?>
	    
	</td>
    </tr>

    <tr>
   <td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=migrateUsersFromVmOne&token='.JUtility::getToken() ); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo JText::sprintf('COM_VIRTUEMART_UPDATE_MIGRATION_STRING_CONFIRM', JText::_('COM_VIRTUEMART_UPDATE_USERS')); ?>', '<?php echo $link; ?>');">
		<span class="vmicon48 vm_install_48"></span>
	    <br />
	    <?php echo JText::sprintf('COM_VIRTUEMART_UPDATE_MIGRATION_STRING', JText::_('COM_VIRTUEMART_UPDATE_USERS')); ?>
	</a></div>
	</td>

   <td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=migrateGeneralFromVmOne&token='.JUtility::getToken() ); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo JText::sprintf('COM_VIRTUEMART_UPDATE_MIGRATION_STRING_CONFIRM', JText::_('COM_VIRTUEMART_UPDATE_GENERAL')); ?>', '<?php echo $link; ?>');">
		<span class="vmicon48 vm_install_48"></span>
	    <br />
		<?php echo JText::sprintf('COM_VIRTUEMART_UPDATE_MIGRATION_STRING', JText::_('COM_VIRTUEMART_UPDATE_GENERAL')); ?>
		</a></div>

	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=migrateProductsFromVmOne&token='.JUtility::getToken() ); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo JText::sprintf('COM_VIRTUEMART_UPDATE_MIGRATION_STRING_CONFIRM', JText::_('COM_VIRTUEMART_UPDATE_PRODUCTS')); ?>', '<?php echo $link; ?>');">
		<span class="vmicon48 vm_install_48"></span>
	    <br />
	    <?php echo JText::sprintf('COM_VIRTUEMART_UPDATE_MIGRATION_STRING', JText::_('COM_VIRTUEMART_UPDATE_PRODUCTS')); ?>
		</a></div>
	</td>
		<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=migrateOrdersFromVmOne&token='.JUtility::getToken() ); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo JText::sprintf('COM_VIRTUEMART_UPDATE_MIGRATION_STRING_CONFIRM', JText::_('COM_VIRTUEMART_UPDATE_ORDERS')); ?>', '<?php echo $link; ?>');">
		<span class="vmicon48 vm_install_48"></span>
	    <br />
		<?php echo JText::sprintf('COM_VIRTUEMART_UPDATE_MIGRATION_STRING', JText::_('COM_VIRTUEMART_UPDATE_ORDERS')); ?>
		</a></div>
	</td>

 	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=migrateAllInOne&token='.JUtility::getToken() ); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo JText::sprintf('COM_VIRTUEMART_UPDATE_MIGRATION_STRING_CONFIRM', JText::_('COM_VIRTUEMART_UPDATE_ALL')); ?>', '<?php echo $link; ?>');">
		<span class="vmicon48 vm_install_48"></span>
	    <br />
	    <?php echo JText::sprintf('COM_VIRTUEMART_UPDATE_MIGRATION_STRING', JText::_('COM_VIRTUEMART_UPDATE_ALL')); ?>
		</a></div>
	</td>
<?php /*	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=migrateVmOneUsers&token='.JUtility::getToken() ); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo 'Start migrate Users?'; ?>', '<?php echo $link; ?>');">
		<span class="vmicon48 vm_install_48"></span>
	    <br />
	    Migrate VM1.1 users to VM2
		</a></div>
	</td>
 	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=migrateVmOneOrders&token='.JUtility::getToken() ); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo 'Start migrate Orders?'; ?>', '<?php echo $link; ?>');">
		<span class="vmicon48 vm_install_48"></span>
	    <br />
	    Migrate VM1.1 orders to VM2
		</a></div>
	</td>
	<td align="left">
		To import data from virtuemart1.1 you must have the tables of virtuemart1.1 in your database. That means the migrator
		is looking for tables with the format "joomla prefix" + "vm". When you followed the defaults than it should be
		jos_vm_.<br />
		You should start with migrating medias, then users (not working yet), then products, then orders (not working yet).
		</a></div>
	</td>*/ ?>
    </tr>

    <tr><td colspan="4"><?php echo JText::_('COM_VIRTUEMART_UPDATE_MIGRATION_TOOLS_WARNING'); ?></td></tr>
    <tr>
	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=restoreSystemDefaults&token='.JUtility::getToken()); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo JText::_('COM_VIRTUEMART_UPDATE_RESTOREDEFAULTS_CONFIRM'); ?>', '<?php echo $link; ?>');">
		<span class="vmicon48 vm_cpanel_48"></span>
	    <br /><?php echo JText::_('COM_VIRTUEMART_UPDATE_RESTOREDEFAULTS'); ?>
		</a></div>
	</td>
	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=deleteVmData&token='.JUtility::getToken() ); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo JText::_('COM_VIRTUEMART_UPDATE_REMOVEDATA_CONFIRM'); ?>', '<?php echo $link; ?>');">
		<span class="vmicon48 vm_trash_48"></span>
	    <br /> <?php echo Jtext::_('COM_VIRTUEMART_UPDATE_REMOVEDATA'); ?>
		</a></div>
	</td>
	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=deleteVmTables&token='.JUtility::getToken() ); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo JText::_('COM_VIRTUEMART_UPDATE_REMOVETABLES_CONFIRM'); ?>', '<?php echo $link; ?>');">
		<span class="vmicon48 vm_trash_48"></span>
	    <br />
            <?php echo Jtext::_('COM_VIRTUEMART_UPDATE_REMOVETABLES'); ?>
		</a></div>
	</td>

    <td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=refreshCompleteInstall&token='.JUtility::getToken() ); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo JText::_('COM_VIRTUEMART_DELETES_ALL_VM_TABLES_AND_FRESH_CONFIRM'); ?>', '<?php echo $link; ?>');">
		<span class="vmicon48 vm_trash_48"></span>
	    <br />
            <?php echo Jtext::_('COM_VIRTUEMART_DELETES_ALL_VM_TABLES_AND_FRESH'); ?>
		</a></div>
	</td>
    </tr>
</table>
</div>
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