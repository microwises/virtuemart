<?php
/**
 * Shopper group data access object.
 *
 * @package	VirtueMart
 * @subpackage ShopperGroup
 * @author Markus �hler 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Shopper group table. 
 * 
 * This class is a template.
 *
 * @author Markus �hler
 * @package	VirtueMart
 */
class TableShopperGroup extends JTable
{
	/** @var int primary key */
	var $shopper_group_id	 = 0;
	
	/** @var int Vendor id */
	var $vendor_id = 0;
	
	/** @var string Shopper group name; no more than 32 characters */
	var $shopper_group_name  = '';	
	
	/** @var string Shopper group description */
	var $shopper_group_desc  = '';  
	
    /** @var int default group that new customers are associated with. There can only be one 
     * default group per vendor. */
	var $default = 0;	


	/**
	 * @author Markus �hler
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_shopper_group', 'shopper_group_id', $db);
	}


	/**
	 * Validates the shopper group record fields.
	 *
	 * @author Markus �hler
	 * @return boolean True if the table buffer contains valid data, false otherwise.
	 */
	function check() 
	{
    if (!$this->shopper_group_name) {
			$this->setError(JText::_('Shopper groups records must have a name.'));
			return false;
		} else if (mb_strlen($this->shopper_group_name) > 32) {
			$this->setError(JText::_('Shopper groups names must not be longer than 32 characters.'));
      return false;
		}

		if (($this->country_name) && ($this->shopper_group_id == 0)) {
		  
			$db =& JFactory::getDBO();  
			$query = 'SELECT count(*) FROM '
			  . $db->nameQuote('#__vm_shopper_group')
			  . ' WHERE '
			  . $db->nameQuote('shopper_group_name')
			  . ' = '
			  . $db->Quote($this->shopper_group_name)
			  . ' AND '
			  . $db->nameQuote('vendor_id')
			  . ' = ' . $this->vendor_id;
      
			$db->setQuery($query);        
		  $rowCount = $db->loadResult();	
		  	
			if ($rowCount > 0) {
				$this->setError(JText::_('The given shopper group name already exists for the given vendor.'));
				return false;
			}
			
			// TODO-MOE: 
			//  * Add check for "default" - so that only one shopper group can have the "default" flag set 
			//    per vendor id.
		}
		
		return true;
	}

}
// pure php no closing tag
