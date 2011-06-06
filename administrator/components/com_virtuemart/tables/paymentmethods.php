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

if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

/**
 * Calculator table class
 * The class is is used to manage the calculation in the shop.
 *
 * @author Max Milbers
 * @package		VirtueMart
 */
class TablePaymentmethods extends VmTable
{
	/** @var int Primary key */
	var $virtuemart_paymentmethod_id					= 0;
	/** @var string VendorID of the payment_method creator */
	var $virtuemart_vendor_id				= 0;
	/** @var id for the used plugin */
	var $paym_jplugin_id			= 0;
	/** @var string Paymentmethod name */
	var $paym_name           		= '';
	/** @var string Element of paymentmethod */
	var $paym_element           	= '';
	///** @var string Shoppergroups allowed to use payment_method */
	//var $paym_shoppervirtuemart_shoppergroup_id         = '';	  // virtuemart_shoppergroup_id?

	/** @var string discount of the paymentmethod */
	var $discount       		 	= '';
	/** @var string discount_is_percentage of the paymentmethod */
	var $discount_is_percentage     = '';
	/** @var string discount_max_amount, maximum amount of money to transfers,... todo ask for what we need that? */
	var $discount_max_amount       	= '';
	/** @var string discount_min_amount of the paymentmethod */
	var $discount_min_amount		='';

//	/** @var string Type of the paymentmethod */
//	var $paym_type       		 	= '';
/** @var string extra information to hold with the paymentmethod */
	var $paym_extra_info			= '';
/** @var blob secret key of the paymentmethod */
	var $paym_secret_key			= '';
	/** @var string parameter of the paymentmethod*/
	var $paym_params				= 0;

	/** @var string ordering */
	var $ordering       	= '';
        /** @var for all Vendors? */
        var $shared				= 0;
        ////this must be forbidden to set for normal vendors, that means only setable Administrator permissions or vendorId=1
    /** @var int published or unpublished */
	var $published 		        = 0;


	/**
	 * @author Max Milbers
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__virtuemart_paymentmethods', 'virtuemart_paymentmethod_id', $db);

		$this->setUniqueName('paym_name');

		$this->setLoggable();

	}


//	/**
//	 * Validates the calculation rule record fields.
//	 *
//	 * @author Max Milbers
//	 * @return boolean True if the table buffer is contains valid data, false otherwise.
//	 */
//	function check() {
//
//        if (!$this->paym_name) {
//			$this->setError(JText::_('COM_VIRTUEMART_PAYMENTMETHODS_RECORDS_MUST_CONTAIN_NAME'));
//			return false;
//		}
//
//        if (!$this->virtuemart_vendor_id) {
//			$this->setError(JText::_('COM_VIRTUEMART_PAYMENTMETHODS_RECORDS_MUST_HAVE_VENDOR'));
//			return false;
//		}
//
//		if (($this->paym_name)) {
//		    $db = JFactory::getDBO();
//
//			$q = 'SELECT virtuemart_paymentmethod_id FROM `#__virtuemart_paymentmethods` ';
//			$q .= 'WHERE `paym_name`="' .  $this->paym_name . '"';
//            $db->setQuery($q);
//		    $virtuemart_paymentmethod_id = $db->loadResult();
//		    if(!empty($virtuemart_paymentmethod_id) && $virtuemart_paymentmethod_id!=$this->virtuemart_paymentmethod_id){
//				$this->setError(JText::_('COM_VIRTUEMART_PAYMENTMETHOD_NAME_ALREADY_EXISTS'));
//				return false;
//			}
//		}
//
//		return true;
//	}




}
// pure php no closing tag
