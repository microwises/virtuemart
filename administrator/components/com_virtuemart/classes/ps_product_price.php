<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
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
 * This class handles product prices
 *
 */
class ps_product_price extends vmAbstractObject  {
	var $key = 'product_price_id'; 
	var $_table_name = '#__{vm}_product_price';

	/**
	 * Validates the Input Parameters on price add/update
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate(&$d) {
		global $vmLogger;
		$valid = true;
		
		if (!isset($d["product_price"]) || $d["product_price"] === '') {
			$vmLogger->err( JText::_('VM_PRODUCT_PRICE_MISSING',false) );
			$valid = false;
		}
		if (empty($d["product_id"])) {
			$vmLogger->err(  JText::_('VM_PRODUCT_ID_MISSING',false) );
			$valid = false;
		}
		
		// convert all "," in prices to decimal points.
		if (stristr($d["product_price"],",")) {
			$d['product_price'] = floatval(str_replace(',', '.', $d["product_price"]));
		}

		//This is not needed because ps_product takes then the main currency from the store
		if (!$d["product_currency"]) {
			
			$vendor_id = ps_product::get_vendor_id_ofproduct($d["product_id"]);
			$db = ps_vendor::get_vendor_fields($vendor_id,array('vendor_currency'));
			if(empty($db)){
				$vmLogger->err( JText::_('VM_PRODUCT_PRICE_CURRENCY_MISSING',false) );
				$valid = false;
			}
			$vendor_currency = $db->f("vendor_currency");		
			$d["product_currency"] = $vendor_currency;
	
			$vmLogger->err( 'Ja was geht: '.$d["product_currency"] );			

		}

		$d["price_quantity_start"] = intval(@$d["price_quantity_start"]);
		$d["price_quantity_end"] = intval(@$d["price_quantity_end"]);

		if ($d["price_quantity_end"] < $d["price_quantity_start"]) {
			$vmLogger->err(  JText::_('VM_PRODUCT_PRICE_QEND_LESS',false) );
			$valid = false;
		}

		$db = new ps_DB;
		$q = "SELECT count(*) AS num_rows FROM #__{vm}_product_price WHERE";
		if (!empty($d["product_price_id"])) {
			$q .= " product_price_id != '".$d['product_price_id']."' AND";
		}
		$q .= " shopper_group_id = '".$d["shopper_group_id"]."'";
		$q .= " AND product_id = '".$d['product_id']."'";
		$q .= " AND product_currency = '".$d['product_currency']."'";
		$q .= " AND (('".$d['price_quantity_start']."' >= price_quantity_start AND '".$d['price_quantity_start']."' <= price_quantity_end)";
		$q .= " OR ('".$d['price_quantity_end']."' >= price_quantity_start AND '".$d['price_quantity_end']."' <= price_quantity_end))";
		$db->query( $q ); $db->next_record();

		if ($db->f("num_rows") > 0) {
			$vmLogger->err( JText::_('VM_PRODUCT_PRICE_ALREADY',false) );
			$valid = false;
		}
		return $valid;
	}
	/**
	 * Adds a new price record for a given product
	 *
	 * @param array $d
	 * @return boolean
	 */
	function add(&$d) {
		global $vmLogger;
		if (!$this->validate($d)) {
			return false;
		}
		if( $d["product_price"] === '') {
			$vmLogger->err( JText::_('VM_PRODUCT_PRICE_NOTENTERED',false) );
			return false;
		}
		$timestamp = time();
		if (empty($d["product_price_vdate"])) $d["product_price_vdate"] = '';
		if (empty($d["product_price_edate"])) $d["product_price_edate"] = '';

		$fields = array('product_id' => $d["product_id"],
								'shopper_group_id' => vmRequest::getInt('shopper_group_id'),
								'product_price' => vmRequest::getFloat('product_price'),
								'product_currency' => vmGet($d, 'product_currency' ),
								'product_price_vdate' => vmGet($d, 'product_price_vdate'),
								'product_price_edate' => vmGet($d, 'product_price_edate'),
								'cdate' => $timestamp,
								'mdate' => $timestamp,
								'price_quantity_start' => vmRequest::getInt('price_quantity_start'),
								'price_quantity_end' =>vmRequest::getInt('price_quantity_end')
						);
		$db = new ps_DB;
		$db->buildQuery('INSERT', '#__{vm}_product_price', $fields );
		
		if( $db->query() !== false ) {		
			$_REQUEST['product_price_id'] = $db->last_insert_id();
			$vmLogger->info( JText::_('VM_PRODUCT_PRICE_ADDED',false));
			return true;
		}
		$vmLogger->err( JText::_('VM_PRODUCT_PRICE_ADDING_FAILED',false) );
		return false;
	}

	/**
	 * Updates a product price
	 *
	 * @param array $d
	 * @return boolean
	 */
	function update(&$d) {
		global $vmLogger;
		if (!$this->validate($d)) {
			return false;
		}
		if( $d["product_price"] === '') {
			return $this->delete( $d );
		}
		$timestamp = time();

		$db = new ps_DB;
		if (empty($d["product_price_vdate"])) $d["product_price_vdate"] = '';
		if (empty($d["product_price_edate"])) $d["product_price_edate"] = '';
		$fields = array(
								'shopper_group_id' => vmRequest::getInt('shopper_group_id'),
								'product_price' => vmRequest::getFloat('product_price'),
								'product_currency' => vmGet($d, 'product_currency' ),
								'product_price_vdate' => vmGet($d, 'product_price_vdate'),
								'product_price_edate' => vmGet($d, 'product_price_edate'),
								'mdate' => $timestamp,
								'price_quantity_start' => vmRequest::getInt('price_quantity_start'),
								'price_quantity_end' => vmRequest::getInt('price_quantity_end')
						);
		$db = new ps_DB;
		$db->buildQuery('UPDATE', '#__{vm}_product_price', $fields, 'WHERE product_price_id=' .(int)$d["product_price_id"] );
		
		if( $db->query() !== false ) {
			$vmLogger->info( JText::_('VM_PRODUCT_PRICE_UPDATED',false) );
			return true;
		}
		$vmLogger->err( JText::_('VM_PRODUCT_PRICE_UPDATING_FAILED',false) );
		return false;
	}


	/**
	* Controller for Deleting Records.
	*/
	function delete(&$d) {

		$record_id = $d["product_price_id"];

		if( is_array( $record_id)) {
			foreach( $record_id as $record) {
				if( !$this->delete_record( $record, $d ))
				return false;
			}
			return true;
		}
		else {
			return $this->delete_record( $record_id, $d );
		}
	}
	/**
	* Deletes one Record.
	*/
	function delete_record( $record_id, &$d ) {
		global $db, $vmLogger;
		$q  = "DELETE FROM #__{vm}_product_price ";
		$q .= "WHERE product_price_id =".intval($record_id).' LIMIT 1';
		$db->query($q);
		$vmLogger->info( JText::_('VM_PRODUCT_PRICE_DELETED',false) );
		return True;
	}


}
?>
