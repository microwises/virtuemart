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
?> 

<h1><?php echo JText::_('COM_VIRTUEMART_ACC_ORDER_INFO'); ?></h1>

<div style="padding: 0px; margin: 5px; spacing: 0px;">
<?php
echo $this->loadTemplate('order');
?>
</div>

<div style="padding: 0px; margin: 0px; spacing: 0px;">
<?php
// echo $this->pane->startPane("order-pane");

// echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_ORDER_ITEM'), 'details_items' );
// echo $this->loadTemplate('items');
// echo $this->pane->endPanel();

// echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_ORDER_HISTORY'), 'details_history' );
// echo $this->loadTemplate('history');
// echo $this->pane->endPanel();

//echo "<pre>\n";
//print_r ($this->orderdetails);
//echo "</pre>\n";
//echo $this->pane->endPanel();

// echo $this->pane->endPane();
$tabarray = array();

$tabarray['items'] = 'COM_VIRTUEMART_ORDER_ITEM';
$tabarray['history'] = 'COM_VIRTUEMART_ORDER_HISTORY';


shopFunctionsF::buildTabs ($tabarray);


?>
</div>
<br clear="all"/><br/>