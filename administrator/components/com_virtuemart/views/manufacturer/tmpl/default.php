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

AdminUIHelper::startAdminArea();

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
    <table>
	<tr>
	    <td width="100%">
		<?php echo JText::_('COM_VIRTUEMART_FILTER').' '. JText::_('COM_VIRTUEMART_MANUFACTURER_NAME').' '; ?>:
		<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
		<button onclick="this.form.submit();"><?php echo JText::_('COM_VIRTUEMART_GO'); ?></button>
		<button onclick="document.getElementById('search').value='';document.getElementById('virtuemart_manufacturercategories_id').value='0';this.form.submit();"><?php echo JText::_('COM_VIRTUEMART_RESET'); ?></button>
	    </td>
	    <td nowrap="nowrap">
		<?php
		echo $this->lists['virtuemart_manufacturercategories_id'];
		?>
	    </td>
	</tr>
    </table>
    <div id="editcell">
	<table class="adminlist" cellspacing="0" cellpadding="0">
	    <thead>
		<tr>
		    <th width="10">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->manufacturers); ?>);" />
		    </th>
		    <th>
				<?php echo JHTML::_('grid.sort', JText::_('COM_VIRTUEMART_MANUFACTURER_NAME') , 'mf_name', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?>
		    </th>
		    <th>
				<?php echo JHTML::_('grid.sort', JText::_('COM_VIRTUEMART_MANUFACTURER_EMAIL') , 'mf_email', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?>
		    </th>
		    <th>
				<?php echo JHTML::_('grid.sort', JText::_('COM_VIRTUEMART_MANUFACTURER_DESCRIPTION') , 'mf_desc', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?>
		    </th>
		    <th>
				<?php echo JHTML::_('grid.sort', JText::_('COM_VIRTUEMART_MANUFACTURER_CATEGORY') , 'mf_category_name', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?>
		    </th>
		    <th>
				<?php echo JHTML::_('grid.sort', JText::_('COM_VIRTUEMART_MANUFACTURER_URL') , 'mf_url', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?>
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

		$checked = JHTML::_('grid.id', $i, $row->virtuemart_manufacturer_id,null,'virtuemart_manufacturer_id');
		$published = JHTML::_('grid.published', $row, $i);
		$editlink = JROUTE::_('index.php?option=com_virtuemart&view=manufacturer&task=edit&virtuemart_manufacturer_id=' . $row->virtuemart_manufacturer_id);
		?>
	    <tr class="row<?php echo $k ; ?>">
		<td width="10">
			<?php echo $checked; ?>
		</td>
		<td align="left">
		    <a href="<?php echo $editlink; ?>"><?php echo $row->mf_name; ?></a>

		</td>
		<td align="left">
			<?php if (!empty($row->mf_email)) echo  '<a href="mailto:'.$row->mf_name.'<'.$row->mf_email.'>">'.$row->mf_email ; ?>
		</td>
		<td>
			<?php echo $row->mf_desc; ?>
		</td>
		<td>
			<?php echo $row->mf_category_name; ?>
		</td>
		<td>
			<?php if (!empty($row->mf_url)) echo '<a href="'. $row->mf_url.'">'. $row->mf_url ; ?>
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
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>


<?php AdminUIHelper::endAdminArea(); ?>