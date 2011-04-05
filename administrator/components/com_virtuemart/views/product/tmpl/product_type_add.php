<?php
/**
*
* Add product types to a product
*
* @package	VirtueMart
* @subpackage Product
* @author RolandD
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
		<td width="23%" height="20" valign="middle" > 
			<div align="right"><?php echo JText::_('VM_PRODUCT_PRODUCT_TYPE_FORM_PRODUCT_TYPE') ?>:</div>
		</td>
		<td width="77%" height="10" >
			<?php echo $this->producttypes; ?> 
		</td>
	</tr>
</table>
<!-- Hidden Fields -->
<input type="hidden" name="task" value="" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="product" />
<input type="hidden" name="product_id" value="<?php echo $this->product->product_id; ?>" />
<input type="hidden" name="product_parent_id" value="<?php echo JRequest::getInt('product_parent_id', $this->product->product_parent_id); ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?> 