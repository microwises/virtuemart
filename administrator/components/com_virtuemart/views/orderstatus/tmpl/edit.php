<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage OrderStatus
* @author Oscar van Eijk
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
AdminUIHelper::imitateTabs('start','COM_VIRTUEMART_ORDERSTATUS_DETAILS');
$j15 = VmConfig::isJ15();
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">


<div class="col50">
	<fieldset>
	<legend><?php echo JText::_('COM_VIRTUEMART_ORDERSTATUS_DETAILS'); ?></legend>
        <?php
          $editcoreStatus = (in_array($this->orderStatus->order_status_code, $this->lists['vmCoreStatusCode']));
          $orderStatusCodeTip = ($editcoreStatus) ? 'COM_VIRTUEMART_ORDER_STATUS_CODE_CORE':'COM_VIRTUEMART_ORDER_STATUS_CODE_TIP';
                            ?>
	<table class="admintable">
		<?php

                            $lang = JFactory::getLanguage();
                            $text = $lang->hasKey($this->orderStatus->order_status_name) ? ' ('.JText::_($this->orderStatus->order_status_name).')' : ' ';

		echo VmHTML::row('input','COM_VIRTUEMART_ORDER_STATUS_NAME','order_status_name',$this->orderStatus->order_status_name,'class="inputbox"','',50,50, $text)  ; ?>

		<?php echo VmHTML::row('input','COM_VIRTUEMART_ORDER_STATUS_CODE','order_status_code',$this->orderStatus->order_status_code,'class="inputbox readonly" readonly','',3,1); ?>
		<tr>
			<td width="110" class="key">
				<label for="order_status_description">
					<?php echo JText::_('COM_VIRTUEMART_DESCRIPTION'); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->editor->display('order_status_description',  $this->orderStatus->order_status_description, '100%;', '250', '75', '20', array('image', 'pagebreak', 'readmore') ) ; ?>
			</td>
		</tr>
		<?php echo VmHTML::row('raw','COM_VIRTUEMART_VENDOR', $this->lists['vendors'] ); ?>
		<?php echo VmHTML::row('raw','COM_VIRTUEMART_ORDERING', $this->ordering); ?>

	</table>
	</fieldset>
</div>

	<input type="hidden" name="virtuemart_orderstate_id" value="<?php echo $this->orderStatus->virtuemart_orderstate_id; ?>" />
	<?php echo VmHTML::HiddenEdit() ?>
</form>


<?php
AdminUIHelper::imitateTabs('end');
AdminUIHelper::endAdminArea(); ?>
