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
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Credit card table class
 * The class is is used to manage the credit cards in the shop.
 *
 * @package		VirtueMart
 * @author RickG
 */
class TableCreditcard extends JTable {

	/** @var int Primary key */
	var $creditcard_id				= 0;
	/** @var string credit card name */
	var $creditcard_name           = '';
	/** @var char Credit card code */
	var $creditcard_code           = '';
	/** @var char Credit card code */
	var $vendor_id		           = 0;
	
	var $published = 0;


	/**
	 * @author RickG
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_creditcard', 'creditcard_id', $db);
	}


	/**
	 * Validates the credit card record fields.
	 *
	 * @author RickG
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check()
	{
        if (!$this->creditcard_name) {
			$this->setError(JText::_('VM_CREDIT_CARD_RECORDS_MUST_CONTAIN_NAME'));
			return false;
		}
		if (!$this->creditcard_code) {
			$this->setError(JText::_('VM_CREDIT_CARD_RECORDS_MUST_CONTAIN_CODE'));
			return false;
		}

		if (($this->creditcard_name) && ($this->creditcard_id == 0)) {
		    $db =& JFactory::getDBO();

			$q = 'SELECT count(*) FROM `#__vm_creditcard` ';
			$q .= 'WHERE `creditcard_name`="' .  $this->creditcard_name . '"';
            $db->setQuery($q);
		    $rowCount = $db->loadResult();
			if ($rowCount > 0) {
				$this->setError(JText::_('VM_GIVEN_CREDIT_CARD_NAME_ALREADY_EXISTS'));
				return false;
			}
		}

		return true;
	}

}
// pure php no closing tag
