<?php
/**
*
* Order detail view
*
* @package	VirtueMart
* @subpackage Orders
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
JHTML::stylesheet('vmpanels.css', JURI::root().'components/com_virtuemart/assets/css/');

if($this->print){
	?>

		<body onload="javascript:print();">';
		<h1><?php echo JText::_('COM_VIRTUEMART_ACC_ORDER_INFO'); ?></h1>

		<div style="padding: 0px; margin: 5px; spacing: 0px;">
		<?php
		echo $this->loadTemplate('order');
		?>
		</div>

		<div style="padding: 0px; margin: 0px; spacing: 0px;">
		<?php
		echo $this->loadTemplate('items');
		?>
		</div>
		<?php	echo $this->vendor->vendor_legal_info; ?>
		</body>
		<?php
} else {

	?>
	<h1><?php echo JText::_('COM_VIRTUEMART_ACC_ORDER_INFO'); ?>
	<?php

	/* Print view URL */
	$details_url = juri::root().'index.php?option=com_virtuemart&view=orders&layout=details&tmpl=component&virtuemart_order_id=' . $this->orderdetails['details']['BT']->virtuemart_order_id;
	$details_link = "<a href=\"javascript:void window.open('$details_url', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');\"  >";
	//$details_link .= '<span class="hasTip print_32" title="' . JText::_('COM_VIRTUEMART_PRINT') . '">&nbsp;</span></a>';
	$folder = (JVM_VERSION==1) ? '/images/M_images/' : '/media/system/images/';
	$details_link .= '<img alt="Email" src="'.$folder.'printButton.png"></a>';


	echo $details_link; ?>

	</h1><div style="padding: 0px; margin: 5px; spacing: 0px;">
	<?php
	echo $this->loadTemplate('order');
	?>
	</div>

	<div style="padding: 0px; margin: 0px; spacing: 0px;">
	<?php

	$tabarray = array();

	$tabarray['items'] = 'COM_VIRTUEMART_ORDER_ITEM';
	$tabarray['history'] = 'COM_VIRTUEMART_ORDER_HISTORY';

	shopFunctionsF::buildTabs ($tabarray);
	echo '</div><br clear="all"/><br/>';
}

?>






