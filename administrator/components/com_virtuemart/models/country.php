<?php
/**
*
* Data module for shop countries
*
* @package	VirtueMart
* @subpackage Country
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

// Load the model framework
jimport( 'joomla.application.component.model');

if(!class_exists('VmModel')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model class for shop countries
 *
 * @package	VirtueMart
 * @subpackage Country
 * @author RickG
 */
class VirtueMartModelCountry extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('countries');
	}

    /**
     * Retreive a country record given a country code.
     *
     * @author RickG
     * @param string $code Country code to lookup
     * @return object Country object from database
     */
    function getCountryByCode($code) {
	$db = JFactory::getDBO();

	$countryCodeLength = strlen($code);
	switch ($countryCodeLength) {
	    case 2:
		$countryCodeFieldname = 'country_2_code';
		break;
	    case 3:
		$countryCodeFieldname = 'country_3_code';
		break;
	    default:
		return false;
	}

	$query = 'SELECT *';
	$query .= ' FROM `#__virtuemart_countries`';
	$query .= ' WHERE `' . $countryCodeFieldname . '` = ' . (int)$code;
	$db->setQuery($query);

	return $db->loadObject();
    }

    /**
     * Retrieve a list of countries from the database.
     *
     * @author RickG
     * @param string $onlyPublished True to only retrieve the publish countries, false otherwise
     * @param string $noLimit True if no record count limit is used, false otherwise
     * @return object List of country objects
     */
    function getCountries($onlyPublished=true, $noLimit=false, $filterCountry = false) {

		$where = array();
		$query = 'SELECT * FROM `#__virtuemart_countries` ';
		/* add filters */
		if ($onlyPublished) $where[] = '`published` = 1';

		if($filterCountry){
			$filterCountry = '"%' . $this->_db->getEscaped( $filterCountry, true ) . '%"' ;
			//$keyword = $this->_db->Quote($filterCountry, false);
			$where[] = '`country_name` LIKE '.$filterCountry;
		}

		if (count($where) > 0) $query .= ' WHERE '.implode(' AND ', $where) ;

		if($filterCountry){
			$query .= $this->_getOrdering('country_name');
		} else {
			$query .= ' ORDER BY country_name';
		}

		if ($noLimit) {
		    $this->_data = $this->_getList($query);
		}
		else {
		    $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		// set total for pagination
		$this->_total = $this->_getListCount($query) ;

		return $this->_data;
    }


}

//no closing tag pure php