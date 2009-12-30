<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: ps_country.php 1755 2009-05-01 22:45:17Z rolandd $
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

/**
 * The class is is used to manage the countries in your store.
 *
 */
class ps_country extends vmAbstractObject  {
	var $key = 'country_id';
	var $_table_name = '#__{vm}_country';
	
	/**
	 * Returns the Country Name and ID of the country specified by $code
	 *
	 * @param string $code
	 * @return ps_DB
	 */
	function &get_country_by_code( $code ) {
		$db = new ps_DB();
		$country_code_type = strlen( $code );
		switch ($country_code_type) {
			case 2:
				$country_code_type_field = 'country_2_code';
				break;
			case 3:
				$country_code_type_field = 'country_3_code';
				break;
			default:
				return false;
		}
		$db->query('SELECT `country_id`, `country_name`, `country_2_code`, `country_3_code` 
							FROM `'.$this->getTable().'` WHERE `'.$country_code_type_field.'` = \''.$db->getEscaped($code).'\'' );
		$db->next_record();
		return $db;
	}
	/**
	 * Validates the input parameters onCountryAdd
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_add($d) {
		global $vmLogger;
		$db = new ps_DB;

		if (!$d["country_name"]) {
			$vmLogger->err( "You must enter a name for the country." );
			return False;
		}
		if (!$d["country_2_code"]) {
			$vmLogger->err( "You must enter a 2 symbol code for the country." );
			return False;
		}
		if (!$d["country_3_code"]) {
			$vmLogger->err( 'You must enter a 3 symbol code for the country.' );
			return False;
		}

		if ($d["country_name"]) {
			$q = "SELECT count(*) as rowcnt from #__{vm}_country where";
			$q .= " country_name='" .  $db->getEscaped($d["country_name"]) . "'";
			$db->query($q);
			$db->next_record();
			if ($db->f("rowcnt") > 0) {
				$vmLogger->err( "The given country name already exists." );
				return False;
			}
		}
		return True;
	}

	/**
	 * Validates the input parameters onBeforeCountryDelete
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_delete($d) {

		if (!$d["country_id"]) {
			$GLOBALS['vmLogger']->err( "Please select a country to delete." );
			return False;
		}
		else {
			return True;
		}
	}

	/**
	 * Validates the input parameters onBeforeCountryUpdate
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_update($d) {

		if (!$d["country_name"]) {
			$GLOBALS['vmLogger']->err( "You must enter a name for the country." );
			return False;
		}
		if (!$d["country_2_code"]) {
			$GLOBALS['vmLogger']->err( "You must enter a 2 symbol code for the country." );
			return False;
		}
		if (!$d["country_3_code"]) {
			$GLOBALS['vmLogger']->err( "You must enter a 3 symbol code for the country." );
			return False;
		}
		return true;
	}


	/**
	 * creates a new country record
	 *
	 * @param array $d
	 * @return boolean
	 */
	function add(&$d) {

		$db = new ps_DB;

		if (!$this->validate_add($d)) {
			$d["error"] = $this->error;
			return False;
		}
		$fields = array('country_name' => vmGet($d,'country_name'), 
					'zone_id' => vmRequest::getInt('zone_id'), 
					'country_2_code' => vmGet($d,'country_2_code'), 
					'country_3_code' => vmGet($d,'country_3_code') 
					);

		$db->buildQuery('INSERT', $this->getTable(), $fields );
		if( $db->query() ) {
			$GLOBALS['vmLogger']->info('The country has been added.');
			$_REQUEST['country_id'] = $db->last_insert_id();
			return True;
		}
		
		return false;

	}

	/**
	 * Updates a given country record
	 *
	 * @param array $d
	 * @return boolean
	 */
	function update(&$d) {
		$db = new ps_DB;
		$timestamp = time();

		if (!$this->validate_update($d)) {
			return False;
		}
		$fields = array('country_name' => vmGet($d,'country_name'), 
					'zone_id' => vmRequest::getInt('zone_id'), 
					'country_2_code' => vmGet($d,'country_2_code'), 
					'country_3_code' => vmGet($d,'country_3_code') 
					);

		$db->buildQuery('UPDATE', $this->getTable(), $fields, "WHERE country_id=".(int)$d["country_id"] );
		if( $db->query() ) {
			$GLOBALS['vmLogger']->info('The country has been updated.');
			return True;
		}
		return false;
	}

	/**
	 * Deletes a Country
	 *
	 * @param array $d
	 * @return boolean
	 */
	function delete(&$d) {
		$db = new ps_DB;

		if (!$this->validate_delete($d)) {
			return False;
		}
		if( is_array( $d["country_id"])) {
			foreach($d["country_id"] as $country ) {
				$q = 'DELETE FROM #__{vm}_country WHERE country_id='.(int)$country.' LIMIT 1';
				if( $db->query($q) !== false ) {
						$q = 'DELETE FROM #__{vm}_state where country_id=' . (int)$country;
						$db->query($q);
				}
			}
		}
		else {
			$q = 'DELETE FROM #__{vm}_country WHERE country_id=' . (int)$d["country_id"].' LIMIT 1';
			if( $db->query($q) !== false ) {
					$q = 'DELETE FROM #__{vm}_state where country_id=' . (int)$d["country_id"];
					$db->query($q);
			}
		}
		return True;
	}
	/**
	 * Adds a new state entry for a country specified by country_id
	 *
	 * @param array $d
	 * @return boolean
	 */
	function addState( &$d ) {

		$db = new ps_DB;
		if ( empty($d['country_id']) ) {
			$GLOBALS['vmLogger']->err('No country was selected for this State' );
			return False;
		}
		$fields = array('state_name' => vmGet($d,'state_name'), 
					'country_id' => vmRequest::getInt('country_id'), 
					'state_2_code' => vmGet($d,'state_2_code'), 
					'state_3_code' => vmGet($d,'state_3_code') );

		$db->buildQuery('INSERT', '#__{vm}_state', $fields );
		if( $db->query() ) {
			$GLOBALS['vmLogger']->info('The state has been added.');
			$_REQUEST['state_id'] = $db->last_insert_id();
			return True;
		}
		return false;

	}
	/**
	 * Updates a state entry
	 *
	 * @param array $d
	 * @return boolean
	 */
	function updateState( &$d ) {
		$db = new ps_DB;

		if (empty($d['state_id']) ||empty($d['country_id']) ) {
			$GLOBALS['vmLogger']->err('Please select a state or country for update!');
			return False;
		}
		$fields = array('state_name' => vmGet($d,'state_name'), 
					'country_id' => vmRequest::getInt('country_id'), 
					'state_2_code' => vmGet($d,'state_2_code'), 
					'state_3_code' => vmGet($d,'state_3_code') );

		$db->buildQuery('UPDATE', '#__{vm}_state', $fields, 'WHERE state_id='.(int)$d["state_id"] );
		if( $db->query() ) {
			$GLOBALS['vmLogger']->info('The state has been updated.');
			return True;
		}
		return false;

	}

	function deleteState( &$d ) {

		$db = new ps_DB;

		if (empty( $d['state_id'])) {
			$GLOBALS['vmLogger']->err('Please select a state to delete!');
			return false;
		}
		if( !is_array( $d['state_id'] )) {
			$d['state_id'] = array($d['state_id']);
		}
		foreach( $d['state_id'] as $state_id ) {
			$q = 'DELETE FROM #__{vm}_state where state_id=' . (int)$state_id . ' LIMIT 1';
			$db->query($q);
		}

		return True;
	}

}

?>