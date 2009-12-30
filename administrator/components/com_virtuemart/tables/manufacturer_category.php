<?php
/**
 * Manufacturer Category table
 *
 * @package	VirtueMart
 * @subpackage Manufacturer category
 * @author vhv_alex 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Manufacturer category table class
 * The class is used to manage the manufacturer category in the shop.
 *
 * @author vhv_alex
 * @package		VirtueMart
 */
class TableManufacturer_Category extends JTable
{
	/** @var int Primary key */
	var $mf_category_id = 0;
	/** @var string manufacturer category name */
	var $mf_category_name = '';
	/** @var string manufacturer category description */
	var $mf_category_desc = '';	
	/** @var int Published or unpublished */
	var $published = 1;	


	/**
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_manufacturer_category', 'mf_category_id', $db);
	}


	/**
	 * Validates the manufacturer category record fields before saving to db.
	 *
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check() 
	{
        if (!$this->mf_category_name) {
			$this->setError(JText::_('Manufacturer category name is empty.'));
			return false;
		}

		if (($this->mf_category_name) && ($this->mf_category_id == 0)) {
		    $db =& JFactory::getDBO();
		    
			$q = 'SELECT count(*) FROM #__vm_manufacturer_category ';
			$q .= 'WHERE mf_category_name="' .  $this->mf_category_name . '"';
            $db->setQuery($q);        
		    $rowCount = $db->loadResult();		
			if ($rowCount > 0) {
				$this->setError(JText::_('The given manufacturer category name already exists.'));
				return false;
			}
		}
		
		return true;
	}
	/*
	 * Verify that user have to delete all manufacturers of a particular category before that category can be removed
	 * 
	 * @return boolean True if category is ready to be removed, otherwise False
	 */
	function checkManufacturer($categoryId = 0)
	{
		if($categoryId > 0) {
			$db = JFactory::getDBO();
			
			$q = 'SELECT count(*)'
				.' FROM #__vm_manufacturer'
				.' WHERE mf_category_id = '.$categoryId;
			$db->setQuery($q);
			$mCount = $db->loadResult();
			
			if($mCount > 0) {
				return false;
			}
			
		}
		return true;
	}
	
	
	

}
?>
