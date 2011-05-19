<?php
/**
*
* Currency table
*
* @package	VirtueMart
* @subpackage Currency
* @author RickG
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
 * Currency table class
 * The class is is used to manage the currencies in the shop.
 *
 * @package		VirtueMart
 * @author RickG, Max Milbers
 */
class TableCurrencies extends VmTable {

	/** @var int Primary key */
	var $virtuemart_currency_id				= 0;
	/** @var int vendor id */
	var $virtuemart_vendor_id					= 1;
	/** @var string Currency name*/

	var $currency_name 			= '';
	var $currency_code_2		= '';
	var $currency_code_3		= '';
	var $currency_numeric_code	= 0;
	var $currency_exchange_rate = 0.0;
	var $currency_symbol		= '';
	var $currency_decimal_place		= 0;
	var $currency_decimal_symbol		= '';
	var $currency_thousands		= '';
	var $currency_positive_style	= '';
	var $currency_negative_style	= '';
	var $shared					= 0;
	var $published				= 0;

	/**
	 * @author Max Milbers
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__virtuemart_currencies', 'virtuemart_currency_id', $db);

		$this->setUniqueName('currency_name');
//		$this->setObligatoryKeys('creditcard_code');

		$this->setLoggable();

		$this->setOrderable();
	}


//	/**
//	 * Validates the currency record fields.
//	 *
//	 * @author RickG, Max Milbers
//	 * @return boolean True if the table buffer is contains valid data, false otherwise.
//	 */
//	function check() {
//
//        if (!$this->currency_name) {
//			$this->setError(JText::_('COM_VIRTUEMART_CURRENCY_RECORDS_MUST_CONTAIN_CURRENCY_NAME'));
//			return false;
//		}
//		if (!$this->currency_code) {
//			$this->setError(JText::_('COM_VIRTUEMART_CURRENCY_RECORDS_MUST_CONTAIN_CURRENCY_CODE'));
//			return false;
//		}
//
//		if (($this->currency_name) && ($this->virtuemart_currency_id == 0)) {
//		    $db =& JFactory::getDBO();
//
//			$q = 'SELECT count(*) FROM `#__virtuemart_currencies` ';
//			$q .= 'WHERE `currency_name`="' .  $this->currency_name . '"';
//            $db->setQuery($q);
//		    $rowCount = $db->loadResult();
//			if ($rowCount > 0) {
//				$this->setError(JText::_('COM_VIRTUEMART_CURRENCY_NAME_ALREADY_EXISTS'));
//				return false;
//			}
//		}
//
//		$date = JFactory::getDate();
//		$today = $date->toMySQL();
//		if(empty($this->created_on)){
//			$this->created_on = $today;
//		}
//     	$this->modified_on = $today;
//
//		return true;
//	}

}
// pure php no closing tag
