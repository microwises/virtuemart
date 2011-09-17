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

if(!class_exists('VmTableData'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtabledata.php');

/**
 * Vendor table class
 * The class is is used to manage the vendors in the shop.
 *
 * @package		VirtueMart
 * @author RickG
 * @author Max Milbers
 */
class TableVendors extends VmTableData {

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
    var $vendor_currency	  		= 0;
    /** @var varchar Path to vendor images */
//     var $vendor_image_path   		= '';
    /** @var text Vendor terms of service */
    var $vendor_terms_of_service	= '';
    /** @var varchar Vendor url */
    var $vendor_url					= '';
    /** @var text Currencies accepted by this vendor */
    var $vendor_accepted_currencies = array();

    var $vendor_params = '';

//     var $_params = array();

//     /** @var decimal Min POV */
//     var $vendor_min_pov 	   		= 0;
//     /** @var decimal Freeshipping */
//     var $vendor_freeshipping  		= 0;

//     /** @var text Vendor address format */
//     var $vendor_address_format		= '';
//     /** @var varchar Vendor date format */
//     var $vendor_date_format			= '';

    /* @author RickG, Max Milbers
     * @param $db A database connector object
     */
    function __construct(&$db) {
		parent::__construct('#__virtuemart_vendors', 'virtuemart_vendor_id', $db);
		$this->setPrimaryKey('virtuemart_vendor_id');
		$this->setUniqueName('vendor_name');
//		$this->setObligatoryKeys('country_2_code');
//		$this->setObligatoryKeys('country_3_code');

		$this->setLoggable();

		foreach($this->_varsToPushParam as $k=>$v){
				$this->$k = $v;
		}
    }

    private $_varsToPushParam =
    				array('vendor_min_pov'=>0.0,'vendor_min_poq'=>1,'vendor_freeshipping'=>0.0,'vendor_address_format'=>'','vendor_date_format'=>'');

    /**
     * Test of technic to inject params as table attributes
     * @author Max Milbers
     */
    function load($int){

    	parent::load($int);

    	if(!empty($this->vendor_params)){

    	$config = explode('|', $this->vendor_params);
    		foreach($config as $item){
    			$item = explode('=',$item);
    			if(count($item)===2){
    				$this->$item[0] = unserialize($item[1]);
    			}

    		}
    	}

    	foreach($this->_varsToPushParam as $k=>$v){
    		if(!isset($this->$k)){
    			$this->$k = $v;
    		}
    	}

    	return $this;
    }

    function store(){

    	foreach($this->_varsToPushParam as $k=>$v){
    		if(isset($this->$k)){
    			$this->vendor_params .= $k.'='.serialize($this->$k).'|';
    		} else {
    			$this->vendor_params .= $k.'='.serialize($v).'|';
    		}
    		unset($this->$k);
    	}

    	vmdebug('my data in vendors store', $this);
    	return parent::store();
    }

}

//pure php no closing tag
