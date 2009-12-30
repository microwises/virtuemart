<?php defined('_JEXEC') or die('Restricted access');?>
<h2><?php echo JText::_('PRODUCT_WAITING_LIST_USERLIST');?>:</h2>
<input type="hidden" value="<?php echo $this->product->product_in_stock; ?>" name="product_in_stock_old" />
<input type="checkbox" value="1" checked="checked" id="notify_users" name="notify_users" /> 
<label for="notify_users"><?php echo JText::_('PRODUCT_WAITING_LIST_NOTIFYUSERS');?></label>
<br /><br />
<table class="adminlist">
	<thead>
		<tr>
			<th class="title"><?php echo JText::_('NAME');?></th>
			<th class="title"><?php echo JText::_('USERNAME');?></th>
			<th class="title"><?php echo JText::_('EMAIL');?></th>
			<th class="title"><?php echo JText::_('NOTIFIED');?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ($this->waitinglist as $key => $wait) {
		if ($wait->notified == 1) {
			$waiting_notified = JText::_('PRODUCT_WAITING_LIST_NOTIFIED') . ' ' . $wait->notify_date;
		} else {
			$waiting_notified = '';
		}
		if ($wait->user_id==0) {
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
