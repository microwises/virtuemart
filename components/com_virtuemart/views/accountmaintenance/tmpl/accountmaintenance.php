<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage AccountMaintenance
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
if ($this->perm->isRegisteredCustomer()) {
?>
  <strong><?php echo JText::_('VM_ACC_CUSTOMER_ACCOUNT') ?></strong>
  <?php echo $this->user->name; ?><br />
  <br />
  <table border="0" cellspacing="0" cellpadding="10" width="100%" align="center">
  
<?php if( $this->user->id > 0)  { ?>
    <tr>
      <td>
      <strong>
      	<?php
      		echo JHTML::_('link', JRoute::_(VmConfig::get('secureurl', JURI::root()).'index.php?option=com_virtuemart&view=accountmaintenance&task=accountbilling'), 
      				JHTML::_('image', JURI::root().'components/com_virtuemart/assets/images/identity.png', JText::_('VM_ACCOUNT_TITLE'), array('align' => 'middle')).' '.JText::_('VM_ACC_ACCOUNT_INFO')); ?>
       </strong>
       <br /><?php echo JText::_('VM_ACC_UPD_BILL') ?>
      </td>
    </tr>
    <?php
    if (VmConfig::get('no_shipto') != '1') {
	?>
		<tr><td>&nbsp;</td></tr>
		
		<tr>
		  <td><hr />
		  <strong>
		  	<?php echo JHTML::_('link', JRoute::_(VmConfig::get('secureurl')."index.php?option=com_virtuemart&view=accountmaintenance&task=accountshipping"), 
		  				JHTML::_('image', JURI::root().'components/com_virtuemart/assets/images/web.png', JText::_('VM_ACC_SHIP_INFO'), array('align' => 'middle')).' '.JText::_('VM_ACC_SHIP_INFO')); ?>
		  </strong>
                        <br />
                        <?php echo JText::_('VM_ACC_UPD_SHIP') ?>
                  </td>
                </tr>
                <?php
	}
	?>
    <tr><td>&nbsp;</td></tr>
<?php } ?>
    <tr>
      <td>
      	<hr />
      	<strong>
      	<?php echo JHTML::_('image', JURI::root().'components/com_virtuemart/assets/images/package.png', JText::_('VM_ACC_ORDER_INFO'), array('align' => 'middle')).' '.JText::_('VM_ACC_ORDER_INFO'); ?>
	    </strong>
	    <table class="adminlist" width="100%">
        <?php 
        //$ps_order->list_order("A", "1" );
        /** @todo Add date format */
        /** @todo Add currency format */
        foreach ($this->orders as $order) { ?>
        	<tr>
				<td><?php echo JHTML::_('image', JURI::root().'components/com_virtuemart/assets/images/goto.png', JText::_('VM_ORDER_LINK')).JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=accountmaintenance&task=accountorder&order_id='.$order->order_id), JText::_('VM_VIEW')); ?></td>
				<td><strong><?php echo JText::_('VM_ORDER_PRINT_PO_DATE'); ?></strong> <?php echo shopFunctions::formatDate($order->cdate); ?><br /><strong><?php echo JText::_('VM_ORDER_PRINT_TOTAL');?></strong> <?php echo $this->currencyDisplay->getFullValue($order->order_total); ?></td>
        		<td><strong><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS'); ?></strong> <?php echo $order->order_status_name; ?><br /><strong><?php echo JText::_('VM_ORDER_PRINT_PO_NUMBER');?></strong> <?php echo sprintf("%08d", $order->order_id);?></td>
        	</tr>
        <?php } ?>
        </table>
      </td>
    </tr>
    
</table>
<!-- Body ends here -->
<?php 
} 
else { 
	// You're not allowed... you need to login.
    echo JText::_('DO_LOGIN') .'<br/><br/><br/>';
   // include(PAGEPATH.'checkout.login_form.php');
} 
?>