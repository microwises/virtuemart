<?php
/**
 * Vendor Table
 *
 * @package	VirtueMart
 * @subpackage Vendor
 * @author RickG
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Vendor table class
 * The class is is used to manage the vendors in the shop.
 *
 * @author RickG
 * @package		VirtueMart
 */
class TableVendor extends JTable {
    /** @var int Primary key */
    var $vendor_id			= 0;
    /** @var varchar Vendor name*/
    var $vendor_name  	         	= '';
    /** @var varchar Vendor phone number */
    var $vendor_phone         		= '';
    /** @var varchar Vendor store name */
    var $vendor_store_name		= '';
    /** @var text Vendor store description */
    var $vendor_store_desc   		= '';
    /** @var int Category Id */
    var $vendor_category_id   		= '';
    /** @var varchar Vendor thumb image */
    var $vendor_thumb_image   		= '';
    /** @var varchar Vendor full image */
    var $vendor_full_image   		= '';
    /** @var varchar Currency */
    var $vendor_currency	  		= '';
    /** @var int Vendor created date */
    var $cdate	 	 				= '';
    /** @var int Vendor modified date */
    var $mdate				  		= '';
    /** @var varchar Path to vendor images */
    var $vendor_image_path   		= '';
    /** @var text Vendor terms of service */
    var $vendor_terms_of_service	= '';
    /** @var varchar Vendor url */
    var $vendor_url					= '';
    /** @var decimal Min POV */
    var $vendor_min_pov 	   		= '';
    /** @var decimal Freeshipping */
    var $vendor_freeshipping  		= '';
    /** @var varchar Currency display style */
    var $vendor_currency_display_style = '';
    /** @var text Currencies accepted by this vendor */
    var $vendor_accepted_currencies = array();
    /** @var text Vendor address format */
    var $vendor_address_format		= '';
    /** @var varchar Vendor date format */
    var $vendor_date_format			= '';

    /**
     * @author RickG
     * @param $db A database connector object
     */
    function __construct(&$db) {
	parent::__construct('#__vm_vendor', 'vendor_id', $db);
    }


    /**
     * Validates the vendor record fields.
     *
     * @author RickG
     * @return boolean True if the table buffer is contains valid data, false otherwise.
     */
    function check() {
	if (($this->vendor_name) && ($this->vendor_id == 0)) {
	    $db = JFactory::getDBO();

	    $q = 'SELECT count(*) FROM `#__vm_vendor` ';
	    $q .= 'WHERE `vendor_name`="' .  $this->vendor_name . '"';
	    $db->setQuery($q);
	    $rowCount = $db->loadResult();
	    if ($rowCount > 0) {
		$this->setError(JText::_('The given vendor name already exists.'));
		return false;
	    }
	}

	return true;
    }




}
?>
