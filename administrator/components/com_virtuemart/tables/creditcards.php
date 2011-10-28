<?php
/**
*
* Credit Card table
*
* @package	VirtueMart
* @subpackage CreditCard
* @author RickG
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: creditcards.php 3284 2011-05-18 20:52:40Z Electrocity $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

/**
 * Credit card table class
 * The class is is used to manage the credit cards in the shop.
 *
 * @package		VirtueMart
 * @author RickG
 */
class TableCreditcards extends VmTable {

	/** @var int Primary key */
	var $virtuemart_creditcard_id				= 0;
	/** @var string credit card name */
	var $creditcard_name           = '';
	/** @var char Credit card code */
	var $creditcard_code           = '';
	/** @var char Credit card code */
	var $virtuemart_vendor_id		           = 0;
	var $ordering = 0;
	var $shared = 0;
	var $published = 0;
  	/** @var date Category creation date */
	var $created_on = null;
	/** @var int User id */
	var $created_by = 0;
	/** @var date Category last modification date */
	var $modified_on = null;
	/** @var int User id */
	var $modified_by = 0;
	/** @var boolean */
	var $locked_on	= 0;
	/** @var time */
	var $locked_by	= 0;

	/**
	 * @author Max Milbers
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__virtuemart_creditcards', 'virtuemart_creditcard_id', $db);

		$this->setUniqueName('creditcard_name');
		$this->setObligatoryKeys('creditcard_code');

		$this->setLoggable();

	}

}
// pure php no closing tag
