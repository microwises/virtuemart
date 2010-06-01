<?php
/**
*
* Account shipping template
*
* @package	VirtueMart
* @subpackage AccountMaintenance
* @author RolandD
* @todo Create HTTPS links
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
?>
<fieldset>
	<legend class="sectiontableheader"><?php echo JText::_('VM_USER_FORM_SHIPTO_LBL') ?></legend>
	<br/>
	<br/>
		<div><?php echo JText::_('VM_ACC_BILL_DEF'); ?></div>
	<br />
	<?php 
		foreach ($this->shipping_addresses as $skey => $shipping_address) { ?>
		<div>
		- <?php echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=accountmaintenance&task=editshipto&user_info_id='.$shipping_address->user_info_id), $shipping_address->address_type_name); ?>
		</div>
	<br />
	<?php } ?> 
	<br /><br />
	<div>
		<?php echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=accountmaintenance&task=addshipto'), JText::_('VM_USER_FORM_ADD_SHIPTO_LBL'), array('class' => 'button')); ?>
	</div>
</fieldset>