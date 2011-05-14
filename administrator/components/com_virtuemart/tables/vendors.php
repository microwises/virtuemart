<?php
/**
*
* Vendor Table
*
* @package	VirtueMart
* @subpackage Vendor
* @author RickG
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

/**
 * Vendor table class
 * The class is is used to manage the vendors in the shop.
 *
 * @package		VirtueMart
 * @author RickG
 * @author Max Milbers
 */
class TableVendors extends VmTable {

    /** @var int Primary key */
    var $virtuemart_vendor_id			= 0;
    /** @var varchar Vendor name*/
    var $vendor_name  	         	= '';
    /** @var varchar Vendor phone number */
    var $vendor_phone         		= '';
    /** @var varchar Vendor store name */
    var $vendor_store_name		= '';
    /** @var text Vendor store description */
    var $vendor_store_desc   		= '';

    /** @var varchar Currency */
    var $vendor_currency	  		= '';
    /** @var varchar Path to vendor images */
    var $vendor_image_path   		= '';
    /** @var text Vendor terms of service */
    var $vendor_terms_of_service	= '';
    /** @var varchar Vendor url */
    var $vendor_url					= '';
    /** @var decimal Min POV */
    var $vendor_min_pov 	   		= 0;
    /** @var decimal Freeshipping */
    var $vendor_freeshipping  		= 0;
    /** @var varchar Currency display style */
    var $vendor_currency_display_style = '';
    /** @var text Currencies accepted by this vendor */
    var $vendor_accepted_currencies = array();
    /** @var text Vendor address format */
    var $vendor_address_format		= '';
    /** @var varchar Vendor date format */
    var $vendor_date_format			= '';

    /* @author RickG, Max Milbers
     * @param $db A database connector object
     */
    function __construct(&$db) {
		parent::__construct('#__virtuemart_vendors', 'virtuemart_vendor_id', $db);

		$this->setUniqueName('vendor_name','COM_VIRTUEMART_VENDOR_NAME_ALREADY_EXISTS');
//		$this->setPrimaryKeys('country_2_code','COM_VIRTUEMART_COUNTRY_RECORDS_MUST_CONTAIN_2_SYMBOL_CODE');
//		$this->setPrimaryKeys('country_3_code','COM_VIRTUEMART_COUNTRY_RECORDS_MUST_CONTAIN_3_SYMBOL_CODE');

		$this->setLoggable();
    }


    /**
     * Validates the vendor record fields.
     *
     * @author RickG
     * @return boolean True if the table buffer is contains valid data, false otherwise.
     */
//    function check() {
//		if (($this->vendor_name) && ($this->virtuemart_vendor_id == 0)) {
//		    $db = JFactory::getDBO();
//
//		    $q = 'SELECT count(*) FROM `#__virtuemart_vendors` ';
//		    $q .= 'WHERE `vendor_name`="' .  $this->vendor_name . '"';
//		    $db->setQuery($q);
//		    $rowCount = $db->loadResult();
//		    if ($rowCount > 0) {
//				$this->setError(JText::_('COM_VIRTUEMART_VENDOR_NAME_ALREADY_EXISTS'));
//				return false;
//		    }
//		}
//
//		$date = JFactory::getDate();
//		$today = $date->toMySQL();
//		if(empty($this->created_on)){
//			$this->created_on = $today;
//		}
//     	$this->modified_on = $today;
//
//		return true;
//    }

 	/**
	 * Records in this table do not need to exist, so we might need to create a record even
	 * if the primary key is set. Therefore we need to overload the store() function.
	 *
	 * @author Oscar van Eijk
	 * @author Max Milbers
	 * @see libraries/joomla/database/JTable#store($updateNulls)
	 */
	public function store()
	{
		$_qry = 'SELECT virtuemart_vendor_id '
				. 'FROM #__virtuemart_vendors '
				. 'WHERE virtuemart_vendor_id = ' . $this->virtuemart_vendor_id
		;
		$this->_db->setQuery($_qry);
		$_count = $this->_db->loadResultArray();

		if (count($_count) > 0) {
			$returnCode = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, false );
		} else {
			$returnCode = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key);
		}

		if (!$returnCode){
			$this->setError(get_class( $this ).'::store failed - '.$this->_db->getErrorMsg());
			return false;
		}
		else return true;
	}
}

//pure php no closing tag
