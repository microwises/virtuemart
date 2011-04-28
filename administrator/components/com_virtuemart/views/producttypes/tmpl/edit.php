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
* @version $Id: producttypes_edit.php 2978 2011-04-06 14:21:19Z alatak $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
AdminMenuHelper::startAdminArea();
$document = JFactory::getDocument();
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminform">
	<tr>
		<td width="110px" class="key">
			<label for="title">
				<?php echo JText::_('COM_VIRTUEMART_PUBLISHED'); ?>:
			</label>
		</td>
		<td>
			<fieldset class="radio">
				<?php echo JHTML::_( 'select.booleanlist',  'published', 'class="inputbox"', $this->producttype->published); ?>
			</fieldset>
		</td>
	</tr>
	<tr>
          <td class="labelcell"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_TYPE_FORM_NAME') ?>:</td>
          <td >
            <input type="text" class="inputbox" name="product_type_name" size="60" value="<?php echo $this->producttype->product_type_name; ?>" />
          </td>
	</tr>
	<tr>
        <td class="labelcell"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_TYPE_FORM_DESCRIPTION') ?>:</td>
        <td valign="top">
        	<?php echo $this->editor->display('product_type_description',  $this->producttype->product_type_description, '100%;', '200', '75', '20', array('pagebreak', 'readmore') ) ; ?>
  		</td>
	</tr>
	<tr>
      <td class="labelcell"><?php echo JText::_('COM_VIRTUEMART_MODULE_LIST_ORDER') ?>: </td>
      <td valign="top"><?php
      	echo $this->producttype->list_order;
         echo "<input type=\"hidden\" name=\"currentpos\" value=\"".$this->producttype->ordering."\" />";
      ?>
      </td>
	</tr>
	<tr>
      <td colspan="2">
<?php /*	<tr>
      <td class="labelcell"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_TYPE_FORM_BROWSEPAGE') ." ". JText::_('COM_VIRTUEMART_LEAVE_BLANK') ?>: </td>
      <td valign="top">
      <input type="text" class="inputbox" name="product_type_browsepage" value="<?php echo $this->producttype->product_type_browsepage; ?>" />
      </td>
	</tr>
	<tr>
      <td class="labelcell">
        <?php echo JText::_('COM_VIRTUEMART_PRODUCT_TYPE_FORM_FLYPAGE') ." ". JText::_('COM_VIRTUEMART_LEAVE_BLANK') ?>:
      </td>
      <td valign="top">
      <input type="text" class="inputbox" name="product_type_flypage" value="<?php echo $this->producttype->product_type_flypage; ?>" />
      </td>
	</tr> */ ?>
	<?php 
	if ($this->producttypeParameter) {
		/* Load some behaviour */
		JHTML::_('behavior.tooltip');
		jimport('joomla.html.pane');
		$pane = JPane::getInstance();
		echo $pane->startPane("parameter-pane");
		foreach ( $this->producttypeParameter as $parameter ) {
			echo $pane->startPanel($parameter->parameter_name,$parameter->parameter_name);
				include ('edit_parameters.php');
			echo $pane->endPanel();
		}
		echo $pane->endPane();
	}
	?><br /></td>
	</tr>
</table>
<!-- Hidden Fields -->
<input type="hidden" name="task" value="producttypes" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="producttypes" />
<input type="hidden" name="cid[]" value="<?php echo $this->producttype->product_type_id; ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>