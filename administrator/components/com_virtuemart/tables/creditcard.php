<?php
/**
 * Credit Card table
 *
 * @package	VirtueMart
 * @subpackage CreditCard
 * @author RickG 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Credit card table class
 * The class is is used to manage the credit cards in the shop.
 *
 * @author RickG
 * @package		VirtueMart
 */
class TableCreditcard extends JTable
{
	/** @var int Primary key */
	var $creditcard_id				= 0;
	/** @var string credit card name */
	var $creditcard_name           = '';	
	/** @var char Credit card code */
	var $creditcard_code           = '';				
	/** @var char Credit card code */
	var $vendor_id		           = 0;	


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
			$this->setError(JText::_('Credit card records must contain a name.'));
			return false;
		}
		if (!$this->creditcard_code) {
			$this->setError(JText::_('Credit card records must contain a code.'));
			return false;
		}

		if (($this->creditcard_name) && ($this->creditcard_id == 0)) {
		    $db =& JFactory::getDBO();
		    
			$q = 'SELECT count(*) FROM `#__vm_creditcard` ';
			$q .= 'WHERE `creditcard_name`="' .  $this->creditcard_name . '"';
            $db->setQuery($q);        
		    $rowCount = $db->loadResult();		
			if ($rowCount > 0) {
				$this->setError(JText::_('The given credit card name already exists.'));
				return false;
			}
		}
		
		return true;
	}
	
	
	

}
?>
