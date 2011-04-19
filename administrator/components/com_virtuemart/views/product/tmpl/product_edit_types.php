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
* @version $Id: product_edit_waitinglist.php 2978 2011-04-06 14:21:19Z alatak $
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

?>
<h2><?php echo JText::_('COM_VIRTUEMART_TYPES');?>:</h2>
<?php
if ($this->productTypes) {
	foreach ($this->productTypes as $key=>$type) { 
		include 'product_edit_type.php';
	}
} else {
echo jText::_( 'COM_VIRTUEMART_TYPES_NO_TYPES') ;
}
?>
<div style="clear:both;"></div>
