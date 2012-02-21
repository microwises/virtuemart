<?php
/**
*
* Order detail view
*
* @package	VirtueMart
* @subpackage Orders
* @author Oscar van Eijk, Valerie Isaksen
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: details.php 5412 2012-02-09 19:27:55Z alatak $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
JHTML::stylesheet('vmpanels.css', JURI::root().'components/com_virtuemart/assets/css/');
if ($this->_layout=="invoice") {
$document = &JFactory::getDocument();
$document->setTitle(JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_NUMBER').' '.$this->orderdetails['details']['BT']->order_number.' '.$this->vendor->vendor_store_name);
//$document->setName( JText::_('COM_VIRTUEMART_ACC_ORDER_INFO').' '.$this->orderdetails['details']['BT']->order_number);
//$document->setDescription( JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_NUMBER').' '.$this->orderdetails['details']['BT']->order_number);
}

if($this->print){
	?>

		<body onload="javascript:print();">
		<h1><?php echo JText::_('COM_VIRTUEMART_INVOICE'); ?> </h1>
		<div class='spaceStyle'>
		<?php
		echo $this->loadTemplate('order');
		?>
		</div>

		<div class='spaceStyle'>
		<?php
		echo $this->loadTemplate('items');
		?>
		</div>
		<?php	echo $this->vendor->vendor_legal_info; ?>
		</body>
		<?php
} else {

	?>

	<?php
	echo $this->loadTemplate('order');
	?>
	</div>

	<div class='spaceStyle'>
	<?php

	$tabarray = array();

	$tabarray['items'] = 'COM_VIRTUEMART_ORDER_ITEM';
	$tabarray['history'] = 'COM_VIRTUEMART_ORDER_HISTORY';

	shopFunctionsF::buildTabs ($tabarray);
	echo '</div><br clear="all"/><br/>';
}

?>






