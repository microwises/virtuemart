<?php
/**
*
* Calc table ( for calculations)
*
* @package	VirtueMart
* @subpackage Payment Methods
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
class TablePayment_method extends JTable
{
	/** @var int Primary key */
	var $paym_id					= 0;
	/** @var string VendorID of the payment_method creator */
	var $paym_vendor_id				= 0;
	/** @var string Paymentmethod name */
	var $paym_name           		= '';	
	/** @var string Element of paymentmethod */
	var $paym_element           	= '';	
	/** @var string Shoppergroups allowed to use payment_method */
	var $paym_shoppergroup_id         = '';	
	/** @var string Type of the paymentmethod */
	var $paym_type       		 	= '';
	/** @var string is paymentmethod a creditcard */
	var $paym_is_creditcard			= '';
	/** @var string parameter of the paymentmethod*/
	var $paym_params				= 0;
	/** @var accepted creditcard */
	var $paym_accepted_creditcard	= '';
	/** @var string extra information to hold with the paymentmethod */
	var $paym_extra_info			= '';
	/** @var blob secret key of the paymentmethod */
	var $paym_secret_key			= '';
	/** @var for all Vendors? */
	var $shared				= 0;//this must be forbidden to set for normal vendors, that means only setable Administrator permissions or vendorId=1
    /** @var int Published or unpublished */
	var $published 		        = 0;	
   	/** @var string ordering */
	var $ordering       	= '';

	/**
	 * @author Max Milbers
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_payment_method', 'paym_id', $db);
	}


	/**
	 * Validates the calculation rule record fields.
	 *
	 * @author Max Milbers
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check() 
	{
        if (!$this->paym_name) {
			$this->setError(JText::_('Paymentmethods records must contain a name.'));
			return false;
		}

		if (($this->paym_name) && ($this->paym_id == 0)) {
		    $db =& JFactory::getDBO();
		    
			$q = 'SELECT count(*) FROM `#__vm_payment_method` ';
			$q .= 'WHERE `paym_name`="' .  $this->paym_name . '"';
            $db->setQuery($q);        
		    $rowCount = $db->loadResult();		
			if ($rowCount > 0) {
				$this->setError(JText::_('The given paymentmethod name already exists.'));
				return false;
			}
		}
		
		return true;
	}
	
	
	

}
?>
