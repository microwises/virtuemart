<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage State
* @author RickG, Max Milbers
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
    <div><?php echo JHTML::_('link','index.php?option=com_virtuemart&view=country&virtuemart_country_id='.$this->virtuemart_country_id,JText::sprintf('COM_VIRTUEMART_STATES_COUNTRY',$this->country_name)); ?></div>
	<table class="adminlist">
	    <thead>
		<tr>
		    <th width="10">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->states ); ?>);" />
		    </th>
		    <th>
			<?php echo JText::_('COM_VIRTUEMART_STATE_LIST_NAME'); ?>
		    </th>
		    <th>
			<?php echo JText::_('COM_VIRTUEMART_ZONE_ASSIGN_CURRENT_LBL'); ?>
		    </th>
		    <th>
			<?php echo JText::_('COM_VIRTUEMART_STATE_LIST_3_CODE'); ?>
		    </th>
		    <th>
			<?php echo JText::_('COM_VIRTUEMART_STATE_LIST_2_CODE'); ?>
		    </th>
		    <th width="20">
			<?php echo JText::_('COM_VIRTUEMART_PUBLISH'); ?>
		    </th>
		</tr>
	    </thead>
	    <?php
	    $k = 0;

	    for ($i=0, $n=count( $this->states ); $i < $n; $i++) {
		$row =& $this->states[$i];

		$checked = JHTML::_('grid.id', $i, $row->virtuemart_state_id);
		$published = JHTML::_('grid.published', $row, $i);
		$editlink = JROUTE::_('index.php?option=com_virtuemart&view=state&task=edit&virtuemart_state_id=' . $row->virtuemart_state_id);

		?>
	    <tr class="<?php echo "row$k"; ?>">
		<td width="10">
			<?php echo $checked; ?>
		</td>
		<td align="left">
		    <a href="<?php echo $editlink; ?>"><?php echo $row->state_name; ?></a>
		</td>
		<td align="left">
			<?php echo $row->virtuemart_worldzone_id; ?>
		</td>
		<td>
			<?php echo $row->state_2_code; ?>
		</td>
		<td>
			<?php echo $row->state_3_code; ?>
		</td>
		<td>
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
    <input type="hidden" name="controller" value="state" />
    <input type="hidden" name="view" value="state" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="virtuemart_country_id" value="<?php echo $this->virtuemart_country_id; ?>" />
</form>



<?php AdminMenuHelper::endAdminArea(); ?>