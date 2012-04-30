<?php
/**
*
* Order history table
*
* @package	VirtueMart
* @subpackage Product
* @author Valerie Isaksen
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

if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

/**
 * Product Emails to  table class
 * The class is is used to manage the product emails sent to customers in the shop.
 *
 * @package	VirtueMart
 * @author RolandD
 */
class TableProduct_Emails extends VmTable {

	/** @var int Primary key */
	var $virtuemart_product_email_id = 0;
	/** @var int Order ID */
	var $virtuemart_product_id = 0;
	/** @var char Order status code */
	/** @var text Comments */
	var $email_content = NULL;

	/**
	 * @param $db Class constructor; connect to the database
	 */
	function __construct($db) {
		parent::__construct('#__virtuemart_product_emails', 'virtuemart_product_email_id', $db);

		$this->setObligatoryKeys('virtuemart_product_id');

		$this->setLoggable();
	}
}
// pure php no closing tag
