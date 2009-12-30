<?php
/**
 * Currency table
 *
 * @package	VirtueMart
 * @subpackage Currency
 * @author RickG 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Currency table class
 * The class is is used to manage the currencies in the shop.
 *
 * @author RickG
 * @package		VirtueMart
 */
class TableCurrency extends JTable
{
	/** @var int Primary key */
	var $currency_id				= 0;
	/** @var string Currency name*/
	var $currency_name           	= '';	
	/** @var char Currency code */
	var $currency_code         		= '';				


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
?>
