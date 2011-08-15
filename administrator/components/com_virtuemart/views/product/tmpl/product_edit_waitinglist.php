<?php
/**
*
* Handle the waitinglist
*
* @package	VirtueMart
* @subpackage Product
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
defined('_JEXEC') or die('Restricted access'); ?>

<h2><?php echo JText::_('COM_VIRTUEMART_PRODUCT_WAITING_LIST_USERLIST');?>:</h2>
<input type="hidden" value="<?php echo $this->product->product_in_stock; ?>" name="product_in_stock_old" />
<input type="checkbox" value="1" checked="checked" id="notify_users" name="notify_users" /> 
<label for="notify_users"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_WAITING_LIST_NOTIFYUSERS');?></label>
<br /><br />
<table class="adminlist" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th class="title"><?php echo JText::_('COM_VIRTUEMART_NAME');?></th>
			<th class="title"><?php echo JText::_('COM_VIRTUEMART_USERNAME');?></th>
			<th class="title"><?php echo JText::_('COM_VIRTUEMART_EMAIL');?></th>
			<th class="title"><?php echo JText::_('COM_VIRTUEMART_NOTIFIED');?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ($this->waitinglist as $key => $wait) {
		if ($wait->notified == 1) {
			$waiting_notified = JText::_('COM_VIRTUEMART_PRODUCT_WAITING_LIST_NOTIFIED') . ' ' . $wait->notify_date;
		} else {
			$waiting_notified = '';
		}
		if ($wait->virtuemart_user_id==0) {
			$row = '<tr><td></td><td></td><td><a href="mailto:' . $wait->notify_email . '">' . $wait->notify_email . '</a></td><td>'.$waiting_notified.'</td></tr>';
		} 
		else {
			$row = '<tr><td>'.$wait->name.'</td><td>'.$wait->username . '</td><td>' . '<a href="mailto:' . $wait->notify_email . '">' . $wait->notify_email . '</a>' . '</td><td>' . $waiting_notified.'</td></tr>';
		}
		echo $row;
	}
	?>
	</tbody>
</table>