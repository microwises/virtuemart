<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Country
* @author RickG
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
    <div id="editcell">
	<table class="adminlist">
	    <thead>
		<tr>
		    <th width="10">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->countries); ?>);" />
		    </th>
		    <th>
			<?php echo JText::_('COM_VIRTUEMART_COUNTRY_LIST_NAME'); ?>
		    </th>
		    <th>
			<?php echo JText::_('COM_VIRTUEMART_ZONE_ASSIGN_CURRENT_LBL'); ?>
		    </th>
		    <th>
			<?php echo JText::_('COM_VIRTUEMART_COUNTRY_LIST_2_CODE'); ?>
		    </th>
		    <th>
			<?php echo JText::_('COM_VIRTUEMART_COUNTRY_LIST_3_CODE'); ?>
		    </th>
		    <th width="20">
			<?php echo JText::_('COM_VIRTUEMART_PUBLISH'); ?>
		    </th>
		</tr>
	    </thead>
	    <?php
	    $k = 0;
	    for ($i=0, $n=count( $this->countries ); $i < $n; $i++) {
		$row = $this->countries[$i];

		$checked = JHTML::_('grid.id', $i, $row->country_id);
		$published = JHTML::_('grid.published', $row, $i);
		$editlink = JROUTE::_('index.php?option=com_virtuemart&controller=country&task=edit&cid[]=' . $row->country_id);
		$statelink	= JROUTE::_('index.php?option=com_virtuemart&controller=state&view=state&country_id=' . $row->country_id);
		?>
	    <tr class="<?php echo "row$k"; ?>">
		<td width="10">
			<?php echo $checked; ?>
		</td>
		<td align="left">
		    <a href="<?php echo $editlink; ?>"><?php echo $row->country_name; ?></a>
		    <a href="<?php echo $statelink; ?>">&nbsp;[States]</a>
		</td>
		<td align="left">
			<?php echo $row->zone_id; ?>
		</td>
		<td>
			<?php echo $row->country_2_code; ?>
		</td>
		<td>
			<?php echo $row->country_3_code ; ?>
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
    <input type="hidden" name="controller" value="country" />
    <input type="hidden" name="view" value="country" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
</form>


<?php AdminMenuHelper::endAdminArea(); ?> 