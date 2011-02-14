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

/**
 * Currency table class
 * The class is is used to manage the currencies in the shop.
 *
 * @package		VirtueMart
 * @author RickG, Max Milbers
 */
class TableCurrency extends JTable {
  
	/** @var int Primary key */
	var $currency_id				= 0;
	/** @var int vendor id */
	var $vendor_id					= 1;
	/** @var string Currency name*/
	var $currency_name           	= '';
	/** @var char Currency code */
	var $currency_code_2			= '';
	var $currency_code         		= ''; //should be renamed to $currency_code_2
	/** @var char Currency symbol */
	var $currency_symbol         	= '';
	/** @var char Currency rate */
	var $exchange_rate         		= '';
	/** @var char display style */
	var $display_style         		= '';
	var $cdate         					;
	var $mdate         					;
	var $published					= 0;
	var $shared						= 0;


	/**
	 * @author RickG
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_currency', 'currency_id', $db);
	}


	/**
	 * Validates the currency record fields.
	 *
	 * @author RickG
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check()
	{
        if (!$this->currency_name) {
			$this->setError(JText::_('Currency records must contain a currency name.'));
			return false;
		}
		if (!$this->currency_code) {
			$this->setError(JText::_('Currency records must contain a currency code.'));
			return false;
		}

		if (($this->currency_name) && ($this->currency_id == 0)) {
		    $db =& JFactory::getDBO();

			$q = 'SELECT count(*) FROM `#__vm_currency` ';
			$q .= 'WHERE `currency_name`="' .  $this->currency_name . '"';
            $db->setQuery($q);
		    $rowCount = $db->loadResult();
			if ($rowCount > 0) {
				$this->setError(JText::_('The given currency name already exists.'));
				return false;
			}
		}

		return true;
	}

}
// pure php no closing tag
