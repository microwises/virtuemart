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
<form method="post" name="adminForm" action="index.php" enctype="multipart/form-data">
<table class="adminform">
	<tr> 
		<td width="23%" height="20" valign="top"> 
			<div align="right"><?php echo JText::_('COM_VIRTUEMART_ATTRIBUTE_FORM_NAME') ?>:</div>
		</td>
		<td width="77%" height="20"> 
			<input type="text" class="inputbox" name="attribute_name" value="<?php echo $this->attribute->attribute_name; ?>" size="32" maxlength="255" />
		</td>
	</tr>
	<tr> 
		<td width="23%" height="10" valign="top"> 
			<div align="right"><?php echo JText::_('COM_VIRTUEMART_ATTRIBUTE_FORM_ORDER') ?>:</div>
		</td>
		<td width="77%" height="10"> 
			<?php echo $this->lists['listorder']; ?>
		</td>
	</tr>
	<tr> 
		<td colspan="2" height="22">&nbsp;</td>
	</tr>
</table>
<!-- Hidden Fields -->
<input type="hidden" name="task" value="" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="attributes" />
<input type="hidden" name="attribute_sku_id" value="<?php echo $this->attribute->attribute_sku_id; ?>" />
<input type="hidden" name="product_id" value="<?php echo JRequest::getInt('product_id', 0); ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?> 