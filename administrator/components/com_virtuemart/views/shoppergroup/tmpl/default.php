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

AdminUIHelper::startAdminArea();

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
  <div id="editcell">
	  <table class="adminlist" cellspacing="0" cellpadding="0">
	    <thead>
		    <tr>
				<th width="10">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->shoppergroups); ?>);" />
				</th>
				<th>
					<?php echo JText::_('COM_VIRTUEMART_SHOPPERGROUP_NAME'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_VIRTUEMART_SHOPPERGROUP_DESCRIPTION'); ?>
				</th>
				<th width="20">
					<?php echo JText::_('COM_VIRTUEMART_DEFAULT'); ?>
				</th>
				<th width="30px" >
					<?php echo JText::_('COM_VIRTUEMART_PUBLISHED'); ?>
				</th>
				<?php if((Vmconfig::get('multix','none')!='none') && $this->perms->check( 'admin' )){ ?>
				<th>
					<?php echo JText::_('COM_VIRTUEMART_VENDOR'); ?>
				</th>
				<?php } ?>
		    </tr>
	    </thead><?php

	    $k = 0;
	    for ($i = 0, $n = count( $this->shoppergroups ); $i < $n; $i++) {
		    $row = $this->shoppergroups[$i];
			$published = JHTML::_('grid.published', $row, $i );
		    $checked = JHTML::_('grid.id', $i, $row->virtuemart_shoppergroup_id,null,'virtuemart_shoppergroup_id');
		    $editlink = JROUTE::_('index.php?option=com_virtuemart&view=shoppergroup&task=edit&virtuemart_shoppergroup_id[]=' . $row->virtuemart_shoppergroup_id); ?>

	      <tr class="row<?php echo $k ; ?>">
			    <td width="10">
				    <?php echo $checked; ?>
			    </td>
			    <td align="left">
			      <a href="<?php echo $editlink; ?>"><?php echo $row->shopper_group_name; ?></a>
			    </td>
			    <td align="left">
				    <?php echo $row->shopper_group_desc; ?>
			    </td>
			    <td>
					<?php
					if ($row->default == 1) {
						?>
						<img src="templates/khepri/images/menu/icon-16-default.png" alt="<?php echo JText::_( 'Default' ); ?>" />
						<?php
					} else {
						?>
						&nbsp;
						<?php
					} ?>
			    </td>
				<td><?php echo $published; ?></td>
				<?php if((Vmconfig::get('multix','none')!='none') && $this->perms->check( 'admin' )){ ?>
			    <td align="left">
            <?php echo $row->virtuemart_vendor_id; ?>
          	</td>
          	<?php } ?>

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
AdminUIHelper::endAdminArea(); ?>