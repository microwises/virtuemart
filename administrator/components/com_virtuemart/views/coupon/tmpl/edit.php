<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage Coupon
 * @author RickG
 * @author Valérie Isaksen
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
AdminUIHelper::imitateTabs('start', 'COM_VIRTUEMART_COUPON_DETAILS');
?>

<form action="index.php" method="post" name="adminForm" ID="adminForm">

	<fieldset>
	    <legend><?php echo JText::_('COM_VIRTUEMART_COUPON_DETAILS'); ?></legend>
	    <table class="admintable">
			<?php echo VmHTML::row('input','COM_VIRTUEMART_COUPON','coupon_code',$this->coupon->coupon_code,'class="inputbox"','',20,32); ?>
			<?php 
				$radioOptions = array();
				$radioOptions[] = JHTML::_('select.option', 'percent', JText::_('COM_VIRTUEMART_COUPON_PERCENT'));
				$radioOptions[] = JHTML::_('select.option', 'total', JText::_('COM_VIRTUEMART_COUPON_TOTAL'));
				echo VmHTML::row('radio','COM_VIRTUEMART_COUPON_PERCENT_TOTAL','percent_or_total',$radioOptions,$this->coupon->percent_or_total); ?>
			<?php
				$listOptions = array();
				$listOptions[] = JHTML::_('select.option', 'permanent', JText::_('COM_VIRTUEMART_COUPON_TYPE_PERMANENT'));
				$listOptions[] = JHTML::_('select.option', 'gift', JText::_('COM_VIRTUEMART_COUPON_TYPE_GIFT'));
				 echo VmHTML::row('select','COM_VIRTUEMART_COUPON_TYPE', 'coupon_type', $listOptions ,$this->coupon->coupon_type,'','value', 'text',false) ; ?>
			<?php echo VmHTML::row('input','COM_VIRTUEMART_VALUE','coupon_value',$this->coupon->coupon_value,'class="inputbox"','',10,32); ?>
			<tr>
				<td width="110" class="key">
					<label for="coupon_value_valid">
						<?php echo JText::_('COM_VIRTUEMART_COUPON_VALUE_VALID_AT'); ?>
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="coupon_value_valid" id="coupon_value_valid" size="10" value="<?php echo $this->coupon->coupon_value_valid; ?>" /><?php echo " " . $this->vendor_currency; ?>
				</td>
			</tr>
			<?php $mydate = JFactory::getDate($this->coupon->coupon_start_date);
			echo VmHTML::row('raw','COM_VIRTUEMART_COUPON_START',  vmJsApi::jDate($mydate->toFormat($this->dateformat), 'coupon_start_date') ); ?>
			<?php
			$expireDate = JFactory::getDate($this->coupon->coupon_expiry_date);
			echo VmHTML::row('raw','COM_VIRTUEMART_COUPON_EXPIRY',  vmJsApi::jDate($expireDate->toFormat($this->dateformat), 'coupon_expiry_date') ); ?>
	    </table>
	</fieldset>
    <input type="hidden" name="virtuemart_coupon_id" value="<?php echo $this->coupon->virtuemart_coupon_id; ?>" />

 	<?php echo VmHTML::HiddenEdit() ?>
</form>


    <?php
    AdminUIHelper::imitateTabs('end');
    AdminUIHelper::endAdminArea();
    ?>


