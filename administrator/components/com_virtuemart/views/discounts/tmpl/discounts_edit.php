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
<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminform">
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td width="24%"><div align="right"><?php echo JText::_('VM_PRODUCT_DISCOUNT_AMOUNT') ?>:</div></td>
      <td width="76%"> 
        <input type="text" class="inputbox" name="amount" value="<?php echo $this->discount->amount; ?>" />
        <?php echo JHTML::tooltip(JText::_('VM_PRODUCT_DISCOUNT_AMOUNT_TIP'), JText::_('VM_PRODUCT_DISCOUNT_AMOUNT'), 'tooltip.png', '', '', false); ?>
      </td>
    </tr>
    <tr> 
      <td width="24%"><div align="right"><?php echo JText::_('VM_PRODUCT_DISCOUNT_AMOUNTTYPE') ?>:</div></td>
      <td width="76%"> 
        <?php echo $this->lists['discount_type']; ?>
        <?php echo JHTML::tooltip(JText::_('VM_PRODUCT_DISCOUNT_ISPERCENT_TIP'), JText::_('VM_PRODUCT_DISCOUNT_AMOUNTTYPE'), 'tooltip.png', '', '', false); ?>
      </td>
    </tr>
    <tr> 
      <td width="24%"><div align="right"><?php echo JText::_('VM_PRODUCT_DISCOUNT_STARTDATE') ?>:</div></td>
      <td width="76%"> 
        <?php echo JHTML::calendar(strftime("%d.%m.%Y", $this->discount->start_date), 'start_date', 'start_date', '%d.%m.%Y'); ?>
        <?php echo JHTML::tooltip(JText::_('VM_PRODUCT_DISCOUNT_STARTDATE_TIP'), JText::_('VM_PRODUCT_DISCOUNT_STARTDATE'), 'tooltip.png', '', '', false); ?>
      </td>
    </tr>
    <tr> 
      <td width="24%"><div align="right"><?php echo JText::_('VM_PRODUCT_DISCOUNT_ENDDATE') ?>:</div></td>
      <td width="76%"> 
        <?php echo JHTML::calendar(strftime("%d.%m.%Y", $this->discount->end_date), 'end_date', 'end_date', '%d.%m.%Y'); ?>
        <?php echo JHTML::tooltip(JText::_('VM_PRODUCT_DISCOUNT_ENDDATE_TIP'), JText::_('VM_PRODUCT_DISCOUNT_ENDDATE'), 'tooltip.png', '', '', false); ?>
      </td>
    </tr>
    <tr> 
      <td valign="top" colspan="2" align="right">&nbsp; </td>
    </tr>   
  </table>
<!-- Hidden Fields -->
<input type="hidden" name="task" value="discounts" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="pshop_mode" value="admin" />
<input type="hidden" name="view" value="discounts" />
<input type="hidden" name="discount_id" value="<?php echo $this->discount->discount_id; ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>