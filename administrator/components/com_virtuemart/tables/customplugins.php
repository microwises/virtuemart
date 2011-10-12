<?php
/**
*
* custom Carrier table
*
* @package	VirtueMart
* @subpackage customCarrier
* @author RickG
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: customcarriesr.php -1   $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmTableXarray'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtablexarray.php');

/**
 * custom Carrier table class
 * The class is is used to manage the custom carriers in the shop.
 *
 * @package	VirtueMart
 * @author RickG, Max Milbers
 */
class TableCustomplugins extends VmTable {

	/** @var int Primary key */
	var $id					= null;
	var $virtuemart_custom_id					= 0;
	/** @var string VendorID of the custom_plugin creator */
	var $virtuemart_vendor_id				= 0;
	/** @var id for the used plugin */
	var $custom_jplugin_id			= 0;
	/** @var string customplugin name */
	var $custom_name           		= '';
	/** @var string Element of customplugin */
	var $custom_element           	= '';
	///** @var string Shoppergroups allowed to use custom_plugin */
	//var $custom_shoppervirtuemart_shoppergroup_id         = '';	  // virtuemart_shoppergroup_id?

	/** @var string discount of the customplugin */
	var $discount       		 	= '';
	/** @var string discount_is_percentage of the customplugin */
	var $discount_is_percentage     = '';
	/** @var string discount_max_amount, maximum amount of money to transfers,... todo ask for what we need that? */
	var $discount_max_amount       	= '';
	/** @var string discount_min_amount of the customplugin */
	var $discount_min_amount		='';

//	/** @var string Type of the customplugin */
//	var $custom_type       		 	= '';
/** @var string extra information to hold with the customplugin */
	/** @var string parameter of the customplugin*/
	var $custom_params				= 0;

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
		parent::__construct('#__virtuemart_customplugins', 'id', $db);

		//it should work without, we had users who need that, lets try
// 		$this->setUniqueName('custom_name');
		//$this->setObligatoryKeys('custom_name');
		//$this->setPrimaryKey('id');
		//$this->setSecondaryKey('custom_jplugin_id');
		$this->setLoggable();
	}

}
// pure php no closing tag
