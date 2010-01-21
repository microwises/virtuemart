<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage 
* @author
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
AdminMenuHelper::startAdminArea();
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminform">
	<tr> 
		<td width="25%" nowrap><div align="right"><?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_NAME') ?>
			<?php echo JHTML::tooltip(JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_NAME_DESCRIPTION'), JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_NAME'), 'tooltip.png', '', '', false); ?>
		</td>
		<td width="75%">
			<input type="text" class="inputbox" name="parameter_name" size="60" value="<?php echo $this->parameter->parameter_name; ?>" />
		</td>
	</tr>
	<tr>
		<td valign="top">
			<div align="right"><?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_LABEL') ?>:</div>
		</td>
		<td width="75%">
			<textarea class="inputbox" name="parameter_label" cols="60" rows="3" ><?php echo $this->parameter->parameter_label; ?></textarea>
		</td>
	</tr>
	<tr> 
		<td width="25%" nowrap valign="top">
			<div align="right"><?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_DESCRIPTION') ?>:</div>
		</td>
		<td width="75%" valign="top">
			<?php echo $this->editor->display('parameter_description',  $this->parameter->parameter_description, '100%;', '550', '75', '20', array('pagebreak', 'readmore') ) ; ?>
		</td>
	</tr>
	<tr>
		<td>
			<div align="right"><?php echo JText::_('VM_MODULE_LIST_ORDER') ?>: </div>
		</td>
		<td valign="top">
			<?php 
				echo $this->parameter->list_order;
				echo "<inputn type=\"hidden\" name=\"currentpos\" value=\"".$this->parameter->parameter_list_order."\" />";
			?>
		</td>
	</tr>
	<tr>
		<td colspan="2"><br /></td>
	</tr>
	<tr>
		<td>
			<div align="right"><?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE') ?>: </div>
		</td>
		<td valign="top">
			<?php echo $this->lists['parameter_type']; ?>
			<input type="hidden" name="parameter_old_type" value="<?php echo $this->parameter->parameter_type ?>" />
		</td>
	</tr>
	<tr> 
		<td width="25%" nowrap valign="top">
			<div align="right"><?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_VALUES') ?>:</div>
		</td>
		<td width="75%" valign="top">
			<input type="text" class="inputbox" name="parameter_values" size="60" value="<?php echo $this->parameter->parameter_values; ?>" />
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_VALUES_DESCRIPTION') ?></td>
	</tr>
	<tr> 
		<td width="25%" nowrap valign="top">
			<div align="right"><?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_MULTISELECT') ?>:</div>
		</td>
		<td width="75%" valign="top">
			<input type="checkbox" name="parameter_multiselect" value="Y" <?php if ($this->parameter->parameter_multiselect == "Y") echo "checked"; ?>/>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<br />
		</td>
	</tr>
	<tr> 
		<td width="25%" nowrap valign="top">
			<div align="right"><?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_DEFAULT') ?>:</div>
		</td>
		<td width="75%" valign="top">
			<input type="text" class="inputbox" name="parameter_default" size="60" value="<?php echo $this->parameter->parameter_default; ?>" />
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>
			<?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_DEFAULT_HELP_TEXT') ?>
		</td>
	</tr>
	<tr> 
		<td width="25%" nowrap valign="top">
			<div align="right"><?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_UNIT') ?>:</div>
		</td>
		<td width="75%" valign="top">
			<input type="text" class="inputbox" name="parameter_unit" size="60" value="<?php echo $this->parameter->parameter_unit; ?>" />
		</td>
	</tr>
</table>
<!-- Hidden Fields -->
<input type="hidden" name="task" value="producttypeparameters" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="pshop_mode" value="admin" />
<input type="hidden" name="view" value="producttypeparameters" />
<input type="hidden" name="product_type_id" value="<?php echo $this->parameter->product_type_id; ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>