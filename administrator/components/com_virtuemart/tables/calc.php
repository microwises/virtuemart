<?php
/**
*
* Calc table ( for calculations)
*
* @package	VirtueMart
* @subpackage Calculation tool
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/
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
	/** @var string Currency used in the calculation */
	var $calc_currency				= '';
	var $ordering					= 0;
	/** @var array affecting Categories of the rule */
	var $calc_categories			= array();
	/** @var array affecting Shoppergroups of the rule */
	var $calc_shopper_groups		= array();	
	/** @var array affecting Countries of the rule */
	var $calc_countries				= array();	
	/** @var array affecting States of the rule */
	var $calc_states				= array();	
	/** @var string Visible for shoppers */
	var $calc_shopper_published		= 0;
	/** @var string Visible for Vendors */
	var $calc_vendor_published		= 0;
	/** @var string start date */
	var $publish_up;
	/** @var string end date */
	var $publish_down;
	/** @var string modified date */
	var $modified;
	/** @var string conditional amount to trigger the rule */
	var $calc_amount_cond;	
	/** @var string The dimension of the amount, maybe unnecessary*/
	var $calc_amount_dimunit;
	/** @var Affects the rule all products of all Vendors? */
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
        if (!$this->calc_vendor_id) {
			$this->calc_vendor_id = 1; //default to mainvendor
		}
		
        if (!$this->calc_name) {
			$this->setError(JText::_('COM_VIRTUEMART_CALCULATION_RULES_RECORDS_MUST_CONTAIN_RULES_NAME'));
			return false;
		}

        if (!$this->calc_kind) {
			$this->setError(JText::_('COM_VIRTUEMART_CALCULATION_RULES_RECORDS_MUST_CONTAIN_CALCULATION_KIND'));
			return false;
		}

		if (($this->calc_name) && ($this->calc_id == 0)) {
		    $db = JFactory::getDBO();
		    
			$q = 'SELECT count(*) FROM `#__vm_calc` ';
			$q .= 'WHERE `calc_name`="' .  $this->calc_name . '"';
            $db->setQuery($q);        
		    $rowCount = $db->loadResult();		
			if ($rowCount > 0) {
				$this->setError(JText::_('COM_VIRTUEMART_CALCULATION_RULE_NAME_ALREADY_EXISTS'));
				return false;
			}
		}
		
		
		return true;
	}

}
// pure php no closing tag
