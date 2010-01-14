<?php
/**
 * Calc table ( for calculations)
 *
 * @package	VirtueMart
 * @subpackage Calculation tool
 * @author Max Milbers 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Calculator table class
 * The class is is used to manage the calculation in the shop.
 *
 * @author Max Milbers
 * @package		VirtueMart
 */
class TableCalc extends JTable
{
	/** @var int Primary key */
	var $calc_id					= 0;
	/** @var string VendorID of the rule creator */
	var $calc_vendor_id				= 0;
	/** @var string Calculation name */
	var $calc_name           		= '';	
	/** @var string Calculation description */
	var $calc_descr           		= '';	
	/** @var string Calculation kind */
	var $calc_kind           		= '';	
   	/** @var string Calculation mathematical Operation */
	var $calc_value_mathop       	= '';
	/** @var string Calculation value of the mathop */
	var $calc_value       		 	= '';
	var $calc_currency				= '';
	var $ordering					= 0;
	/** @var array affecting Categories of the rule */
	var $calc_categories			= array();
	
	var $calc_shopper_published		= 0;	
	var $calc_vendor_published		= 0;	
	var $shared				= 0;//this must be forbidden to set for normal vendors, that means only setable Administrator permissions or vendorId=1
	

    /** @var int Published or unpublished */
	var $published 		        = 0;	


	/**
	 * @author Max Milbers
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_calc', 'calc_id', $db);
//		echo 'TableCalc <br />';
	}


	/**
	 * Validates the calculation rule record fields.
	 *
	 * @author Max Milbers
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check() 
	{
        if (!$this->calc_name) {
			$this->setError(JText::_('Calculation rules records must contain a Rules name.'));
			return false;
		}

		if (($this->calc_name) && ($this->calc_id == 0)) {
		    $db =& JFactory::getDBO();
		    
			$q = 'SELECT count(*) FROM `#__vm_calc` ';
			$q .= 'WHERE `calc_name`="' .  $this->calc_name . '"';
            $db->setQuery($q);        
		    $rowCount = $db->loadResult();		
			if ($rowCount > 0) {
				$this->setError(JText::_('The given calculation rule name already exists.'));
				return false;
			}
		}
		
		return true;
	}
	
	
	

}
?>
