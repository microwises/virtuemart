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
* @version $Id: calc.php 3151 2011-05-03 16:28:43Z Milbo $
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
	var $virtuemart_calc_id					= 0;
	/** @var string VendorID of the rule creator */
	var $virtuemart_virtuemart_vendor_id				= 0;
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
	//var $calc_categories			= array();
	/** @var array affecting Shoppergroups of the rule */
	//var $virtuemart_shoppergroup_ids		= array();
	/** @var array affecting Countries of the rule */
	//var $calc_countries				= array();
	/** @var array affecting States of the rule */
	//var $virtuemart_state_ids				= array();
	/** @var string Visible for shoppers */
	var $calc_shopper_published		= 0;
	/** @var string Visible for Vendors */
	var $calc_vendor_published		= 0;
	/** @var string start date */
	var $publish_up;
	/** @var string end date */
	var $publish_down;
	/** @var string created date */
	var $created_on				= null;
	/** @var string modified date */
	var $modified_on	= null;
        /** @var string   */
	var $calc_qualify;
         /** @var string   */
	var $calc_affected;
	/** @var string conditional amount to trigger the rule */
	var $calc_amount_cond;
	/** @var string The dimension of the amount, maybe unnecessary*/
	var $calc_amount_dimunit;
	/** @var Affects the rule all products of all Vendors? */
	var $shared				= 0;//this must be forbidden to set for normal vendors, that means only setable Administrator permissions or vendorId=1
    /** @var int published or unpublished */
	var $published 		        = 0;
             /** @var boolean */
	var $locked_on	= 0;
	/** @var time */
	var $locked_by	= 0;

	/**
	 * @author Max Milbers
	 * @param $db A database connector object
	 */
	function __construct(&$db){
		parent::__construct('#__virtuemart_calcs', 'virtuemart_calc_id', $db);
	}


	/**
	 * Validates the calculation rule record fields.
	 *
	 * @author Max Milbers
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check()
	{
        if (!$this->virtuemart_virtuemart_vendor_id) {
			$this->virtuemart_virtuemart_vendor_id = 1; //default to mainvendor
		}

        if (!$this->calc_name) {
			$this->setError(JText::_('COM_VIRTUEMART_CALCULATION_RULES_RECORDS_MUST_CONTAIN_RULES_NAME'));
			return false;
		}

        if (!$this->calc_kind) {
			$this->setError(JText::_('COM_VIRTUEMART_CALCULATION_RULES_RECORDS_MUST_CONTAIN_CALCULATION_KIND'));
			return false;
		}

		if (($this->calc_name) ) {
		    $db = JFactory::getDBO();

			$q = 'SELECT `virtuemart_calc_id` FROM `#__virtuemart_calcs` ';
			$q .= 'WHERE `calc_name`="' .  $this->calc_name . '"';
            $db->setQuery($q);
		    $virtuemart_calc_id = $db->loadResult();
			if (!empty($virtuemart_calc_id) && $virtuemart_calc_id!=$this->virtuemart_calc_id) {
				$this->setError(JText::_('COM_VIRTUEMART_CALCULATION_RULE_NAME_ALREADY_EXISTS'));
				return false;
			}
		}

		$date = JFactory::getDate();
		$today = $date->toMySQL();
		if(empty($this->created_on)){
			$this->created_on = $today;
		}
     	$this->modified_on = $today;

		return true;
	}

}
// pure php no closing tag
