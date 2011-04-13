<?php
/**
*
* Layout for the Footer of the mails
*
* @package	VirtueMart
* @subpackage Cart
* @author Max Milbers
*
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
*/
dump($this,'my mailshopper');
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
	//Header for shopper ?>

<?php echo JText::sprintf('COM_VIRTUEMART_CART_MAIL_SHOPPER_CONTENT',$this->shopperName,$this->vendor->vendor_store_name,$this->prices['billTotal']) ?>
<?php
echo $this->vendor->images[0]->displayMediaThumb();

//TODO Ordernumber

//PriceList
include(JPATH_VM_SITE.DS.'views'.DS.'cart'.DS.'tmpl'.DS.'pricelist.php');

include(JPATH_VM_SITE.DS.'views'.DS.'cart'.DS.'tmpl'.DS.'shopperadresses.php');

//TODO if silent registration logindata
//TODO if Paymentmethod needs Bank account data of vendor

//We may wish to integrate later a kind of signature
//include(JPATH_VM_SITE.DS.'views'.DS.'cart'.DS.'tmpl'.DS.'footer.php');

	//Footer for shopper
?>

