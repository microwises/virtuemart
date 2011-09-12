<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage UpdatesMigration
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default_tools.php 4007 2011-08-31 07:31:35Z alatak $
*/

?>
<form action="index.php" method="post" name="adminForm" enctype="multipart/form-data" >
<input type="hidden" name="task" value="" />

<table>
<tr>
	<td align="left" colspan="5" >
		<h3> <?php echo JText::_('COM_VIRTUEMART_UPDATE_MIGRATION_TITLE'); ?> </h3>
	</td>
</tr>
<tr>
    <td align="center">
		<button class="default" type="submit" ><?php echo JText::_('COM_VIRTUEMART_MIGRATE'); ?></button>
    </td>
<tr>

<tr>
	<td>
		<?php echo JText::_('COM_VIRTUEMART_UPDATE_MIGRATION_STRING'); ?>
	</td>
	<td>
   <?php
		$options = array(
			'migrateGeneralFromVmOne'	=>	JText::_('COM_VIRTUEMART_UPDATE_GENERAL'),
			'migrateUsersFromVmOne'	=>	JText::_('COM_VIRTUEMART_UPDATE_USERS'),
			'migrateProductsFromVmOne'	=> JText::_('COM_VIRTUEMART_UPDATE_PRODUCTS'),
			'migrateOrdersFromVmOne'	=> JText::_('COM_VIRTUEMART_UPDATE_ORDERS'),
			'migrateAllInOne'	=> JText::_('COM_VIRTUEMART_UPDATE_ALL'),
			'setStoreOwner'	=> JText::_('COM_VIRTUEMART_SETSTOREOWNER')
		);
		echo VmHTML::radioList('task', 'all', $options);
	?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_VIRTUEMART_MIGRATION_DCAT_BROWSE'); ?>
	</td>
	<td>
		<input class="inputbox" type="text" name="default_category_browse" size="15" value="" />
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_VIRTUEMART_MIGRATION_DCAT_FLY'); ?>
	</td>
	<td>
		<input class="inputbox" type="text" name="default_category_fly" size="15" value="" />
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_VIRTUEMART_MIGRATION_DPROD_FLY'); ?>
	</td>
	<td>
		<input class="inputbox" type="text" name="default_product_fly" size="15" value="" />
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_VIRTUEMART_MIGRATION_STOREOWNERID'); ?>
	</td>
	<td>
		<input class="inputbox" type="text" name="storeOwnerId" size="15" value="" />
	</td>
</tr>


</table>
<!-- Hidden Fields -->
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="updatesmigration" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>