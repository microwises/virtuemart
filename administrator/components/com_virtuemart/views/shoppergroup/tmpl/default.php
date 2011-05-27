<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage ShopperGroup
* @author Markus �hler
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
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->shoppergroups); ?>);" />
				</th>
				<th>
					<?php echo JText::_('COM_VIRTUEMART_SHOPPERGROUP_LIST_NAME'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_VIRTUEMART_VENDOR'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_VIRTUEMART_SHOPPERGROUP_LIST_DESCRIPTION'); ?>
				</th>
				<th width="20">
					<?php echo JText::_('COM_VIRTUEMART_DEFAULT'); ?>
				</th>
				<th width="40px" >
					<?php echo JText::_('COM_VIRTUEMART_PUBLISHED'); ?>
				</th>
		    </tr>
	    </thead><?php

	    $k = 0;
	    for ($i = 0, $n = count( $this->shoppergroups ); $i < $n; $i++) {
		    $row = $this->shoppergroups[$i];
			$published = JHTML::_('grid.published', $row, $i );
		    $checked = JHTML::_('grid.id', $i, $row->virtuemart_shoppergroup_id,null,'virtuemart_shoppergroup_id');
		    $editlink = JROUTE::_('index.php?option=com_virtuemart&view=shoppergroup&task=edit&virtuemart_shoppergroup_id[]=' . $row->virtuemart_shoppergroup_id); ?>

	      <tr class="<?php echo "row$k"; ?>">
			    <td width="10">
				    <?php echo $checked; ?>
			    </td>
			    <td align="left">
			      <a href="<?php echo $editlink; ?>"><?php echo $row->shopper_group_name; ?></a>
			    </td>
			    <td align="left">
            <?php echo $row->virtuemart_vendor_id; ?>
          </td>
			    <td align="left">
				    <?php echo $row->shopper_group_desc; ?>
			    </td>
			    <td>
				    <?php echo $row->default; ?>
			    </td>
				<td><?php echo $published; ?></td>
	      </tr><?php
		    $k = 1 - $k;
	    } ?>
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
  <input type="hidden" name="controller" value="shoppergroup" />
  <input type="hidden" name="view" value="shoppergroup" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <?php echo JHTML::_( 'form.token' ); ?>
</form><?php
AdminMenuHelper::endAdminArea(); ?>