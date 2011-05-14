<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Manufacturer
* @author Patrick Kohl
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

<form action="index.php" method="post" name="adminForm">
    <table>
	<tr>
	    <td width="100%">
		<?php echo JText::_('COM_VIRTUEMART_MANUFACTURER_FILTER'); ?>:
		<input type="text" name="search" id="search" value="<?php echo $this->list['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
		<button onclick="this.form.submit();"><?php echo JText::_('COM_VIRTUEMART_MANUFACTURER_GO_BTN'); ?></button>
		<button onclick="document.getElementById('search').value='';document.getElementById('virtuemart_manufacturercategories_id').value='0';this.form.submit();"><?php echo JText::_('COM_VIRTUEMART_MANUFACTURER_RESET_BTN'); ?></button>
	    </td>
	    <td nowrap="nowrap">
		<?php
		echo $this->list['virtuemart_manufacturercategories_id'];
		?>
	    </td>
	</tr>
    </table>
    <div id="editcell">
	<table class="adminlist">
	    <thead>
		<tr>
		    <th width="10">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->manufacturers); ?>);" />
		    </th>
		    <th>
			<?php echo JText::_('COM_VIRTUEMART_MANUFACTURER_NAME'); ?>
		    </th>
		    <th>
			<?php echo JText::_('COM_VIRTUEMART_MANUFACTURER_EMAIL'); ?>
		    </th>
		    <th>
			<?php echo JText::_('COM_VIRTUEMART_MANUFACTURER_DESC'); ?>
		    </th>
		    <th>
			<?php echo JText::_('COM_VIRTUEMART_MANUFACTURER_URL'); ?>
		    </th>
		    <th width="20">
			<?php echo JText::_('COM_VIRTUEMART_PUBLISH'); ?>
		    </th>
		</tr>
	    </thead>
	    <?php
	    $k = 0;
	    for ($i=0, $n=count( $this->manufacturers ); $i < $n; $i++) {
		$row = $this->manufacturers[$i];

		$checked = JHTML::_('grid.id', $i, $row->virtuemart_manufacturer_id);
		$published = JHTML::_('grid.published', $row, $i);
		$editlink = JROUTE::_('index.php?option=com_virtuemart&view=manufacturer&task=edit&virtuemart_manufacturer_id=' . $row->virtuemart_manufacturer_id);

		?>
	    <tr class="<?php echo "row$k"; ?>">
		<td width="10">
			<?php echo $checked; ?>
		</td>
		<td align="left">
		    <a href="<?php echo $editlink; ?>"><?php echo $row->mf_name; ?></a>

		</td>
		<td align="left">
			<?php echo $row->mf_email; ?>
		</td>
		<td>
			<?php echo $row->mf_desc; ?>
		</td>
		<td>
			<?php echo $row->mf_url; ?>
		</td>
		<td align="center">
			<?php echo $published; ?>
		</td>
	    </tr>
		<?php
		$k = 1 - $k;
	    }
	    ?>
	    <tfoot>
		<tr>
		    <td colspan="10">
			<?php echo $this->pagination->getListFooter(); ?>
		    </td>
		</tr>
	    </tfoot>
	</table>
    </div>

    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="controller" value="manufacturer" />
    <input type="hidden" name="view" value="manufacturer" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
</form>


<?php AdminMenuHelper::endAdminArea(); ?>