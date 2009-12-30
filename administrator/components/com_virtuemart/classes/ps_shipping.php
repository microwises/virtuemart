<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: ps_shipping.php 1760 2009-05-03 22:58:57Z Aravot $
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
 * This class handles the rates and carriers of the standard shipping module!
 *
 */
class ps_shipping {

	/**
	 * Validate onCarrierAdd
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_add(&$d) {
		global $error_msg;
		$db = new ps_DB;

		$q = "SELECT shipping_carrier_id FROM #__{vm}_shipping_carrier WHERE shipping_carrier_id='" . (int)vmGet($d,'shipping_carrier_id') . "'";
		$db->query($q);
		if ($db->next_record()) {
			$d["error"] = JText::_('VM_ERR_MSG_CARRIER_EXIST');
			return False;
		}

		return True;
	}
	/**
	 * Validate onCarrierDelete
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_delete( $shipping_carrier_id, &$d) {
		
		if (!$shipping_carrier_id) {
			$d["error"] = JText::_('VM_ERR_MSG_CARRIER_ID_REQ');
			return False;
		}

		$db = new ps_DB;
		$q = "SELECT shipping_rate_carrier_id FROM #__{vm}_shipping_rate WHERE shipping_rate_carrier_id='" . $shipping_carrier_id . "'";
		$db->query($q);
		if ($db->next_record()) {
			$d["error"] = JText::_('VM_ERR_MSG_CARRIER_INUSE');
			return False;
		}

		$db = new ps_DB;
		$q = "SELECT shipping_carrier_id FROM #__{vm}_shipping_carrier WHERE shipping_carrier_id='" . $shipping_carrier_id . "'";
		$db->query($q);
		if (!$db->next_record()) {
			$d["error"] = JText::_('VM_ERR_MSG_CARRIER_NOTFOUND');
			return False;
		}

		return True;
	}
	/**
	 * Validate onCarrierUpdate
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_update(&$d) {
		global $error_msg;;
		$db = new ps_DB;

		if (!$d["shipping_carrier_id"]) {
			$d["error"] = JText::_('VM_ERR_MSG_CARRIER_ID_REQ');
			return False;
		}

		$db = new ps_DB;
		$q = "SELECT * FROM #__{vm}_shipping_carrier WHERE shipping_carrier_id=" .(int)$d["shipping_carrier_id"];
		$db->query($q);
		if (!$db->next_record()) {
			$d["error"] = JText::_('VM_ERR_MSG_CARRIER_NOTFOUND');
			return False;
		}

		return True;
	}

	/**
	 * Add a new Carrier
	 *
	 * @param array $d
	 * @return boolean
	 */
	function add(&$d) {

		$db = new ps_DB;
		$timestamp = time();

		if (!$this->validate_add($d)) {
			return False;
		}
		
		$fields = array( 'shipping_carrier_name' => vmGet($d, 'shipping_carrier_name'),
									'shipping_carrier_list_order' => (int)$d['shipping_carrier_list_order']);
		$db->buildQuery('INSERT', '#__{vm}_shipping_carrier', $fields );
		
		if( $db->query() === false ) {
			$GLOBALS['vmLogger']->err( 'Failed to add the Shipping Carrier.');
			return false;
		}
		$_REQUEST['shipping_carrier_id'] = $db->last_insert_id();
		$GLOBALS['vmLogger']->info('The Shipping Carrier has been added.');
		return True;

	}
	/**
	 * Update a Carrier
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
		$fields = array( 'shipping_carrier_name' => vmGet($d,'shipping_carrier_name'),
									'shipping_carrier_list_order' => (int)$d['shipping_carrier_list_order']);
		$db->buildQuery('UPDATE', '#__{vm}_shipping_carrier', $fields, 'WHERE shipping_carrier_id=' . (int)$d["shipping_carrier_id"] );
		if( $db->query() === false ) {
			$GLOBALS['vmLogger']->err( 'Failed to update the Shipping Carrier.');
			return false;
		}
		
		$GLOBALS['vmLogger']->info('The Shipping Carrier has been updated.');
		return True;
	}

	/**
	 * Controller for Deleting Records.
	 *
	 * @param array $d
	 * @return boolean
	 */
	function delete(&$d) {

		$record_id = vmGet($d,"shipping_carrier_id");

		if( is_array( $record_id)) {
			foreach( $record_id as $record) {
				if( !$this->delete_record( (int)$record, $d ))
				return false;
			}
			return true;
		}
		else {
			return $this->delete_record( (int)$record_id, $d );
		}
	}

	/**
	 * Deletes a Carrier
	 *
	 * @param int $record_id
	 * @param array $d
	 * @return boolean
	 */
	function delete_record( $record_id, &$d ) {
		global $db;

		if (!$this->validate_delete( $record_id, $d)) {
			return False;
		}

		$q = 'DELETE FROM #__{vm}_shipping_carrier WHERE shipping_carrier_id='.$record_id;
		$db->query($q);
		
		return True;
	}

	/**************************************************************************
	* name: carrier_list()
	* created by: Ekkehard Domning
	* description: 
	* parameters: $selected_carrier_id, select this Item
	* returns:
	**************************************************************************/
	/**
	 * prints the HTML code of selectable carrier list
	 *
	 * @param unknown_type $select_name
	 * @param unknown_type $selected_carrier_id
	 */
	function carrier_list($select_name, $selected_carrier_id) {
		

		$db = new ps_DB;
		$carrier_arr[''] = JText::_('VM_SELECT');
		
		$q = "SELECT shipping_carrier_id,shipping_carrier_name FROM #__{vm}_shipping_carrier";
		// Get list of Values
		$db->query($q);
		while ($db->next_record()) {
			$carrier_arr[$db->f("shipping_carrier_id")] = $db->f("shipping_carrier_name");
		}
		
		echo ps_html::selectList($select_name, $selected_carrier_id, $carrier_arr );
	}

	/**************************************************************************
	* name: country_multiple_list()
	* created by: Ekkehard Domning
	* description: prints the HTML code of a multiple selectable country list
	* parameters: $selected_countries, a String in the Form "<Country1>;<Country2>"
	*             e.g. "GER;AUT;NED";
	* returns:
	**************************************************************************/
	function country_multiple_list($select_name, $selected_countries) {
		
		$db = new ps_DB;

		echo "<select  class=\"inputbox\" multiple size=\"10\" name=\"$select_name\">\n";
		$q = "SELECT * FROM #__{vm}_country ORDER BY country_name ASC";
		$db->query($q);
		while ($db->next_record()) {

			echo "<option value=\"" . $db->f("country_3_code") . "\"";
			$pos = strpos($selected_countries, $db->f("country_3_code"));
			if (is_integer($pos)) {
				echo " selected=\"selected\"";
			}
			echo ">" . $db->f("country_name") . "</option>\n";
		}
		echo "</select>\n";
		return True;
	}

	/**
	 * Validates input parameters onBeforeShippingrateAdd
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_rate_add(&$d) {
		
		$db = new ps_DB;

		if (!$d["shipping_rate_carrier_id"]) {
			$d["error"] = JText::_('VM_ERR_MSG_RATE_CARRIER_ID_REQ');
			return False;
		}

		$q = "SELECT shipping_carrier_id FROM #__{vm}_shipping_carrier WHERE shipping_carrier_id=" .(int)$d["shipping_rate_carrier_id"];
		$db->query($q);
		if (!$db->next_record()) {
			$d["error"] = JText::_('VM_ERR_MSG_RATE_CARRIER_ID_INV');
			return False;
		}
		if (!$d["shipping_rate_name"]) {
			$d["error"] = JText::_('VM_ERR_MSG_RATE_NAME_REQ');
			return False;
		}
		for($i=0;$i<count($d["shipping_rate_country"]);$i++){
			if ($d["shipping_rate_country"][$i] != "") {
				$q = "SELECT * FROM #__{vm}_country WHERE country_3_code='" . $d["shipping_rate_country"][$i] . "'";
				$db->query($q);
				if (!$db->next_record()) {
					$d["error"] = sprintf(JText::_('VM_ERR_MSG_RATE_COUNTRY_CODE_INV'), $d["shipping_rate_country"][$i]);
					return False;
				}
			}
		}

		if ($d["shipping_rate_weight_start"] == "") {
			$d["error"] = JText::_('VM_ERR_MSG_RATE_WEIGHT_START_REQ');
			return False;
		}
		if ($d["shipping_rate_weight_end"] == "") {
			$d["error"] = JText::_('VM_ERR_MSG_RATE_WEIGHT_END_REQ');
			return False;
		}

		if( $d["shipping_rate_zip_start"] == "") {
			$d["shipping_rate_zip_start"] = '00000';
		}
		if ($d["shipping_rate_zip_end"] == "") {
			$d["shipping_rate_zip_end"] = '99999';
		}

		if ($d["shipping_rate_weight_start"] >= $d["shipping_rate_weight_end"]) {
			$d["error"] = JText::_('VM_ERR_MSG_RATE_WEIGHT_STARTEND_INV');
			return False;
		}
		if ($d["shipping_rate_value"] == "") {
			$d["error"] = JText::_('VM_ERR_MSG_RATE_WEIGHT_VALUE_REQ');
			return False;
		}
		if ($d["shipping_rate_package_fee"] == "") {
			$d["shipping_rate_package_fee"] = '0';
		}
		$q = 'SELECT currency_id FROM #__{vm}_currency WHERE currency_id=' .(int)$d['shipping_rate_currency_id'];
		$db->query($q);
		if (!$db->next_record()) {
			$d["error"] = JText::_('VM_ERR_MSG_RATE_CURRENCY_ID_INV');
			return False;
		}

		if (!$d["shipping_rate_list_order"]) {
			$d["shipping_rate_list_order"] = '0';
		}
		return True;
	}
	/**
	 * Validates input parameters onBeforeShippingrateDelete
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_rate_delete(&$d) {
		
		if (!$d["shipping_rate_id"]) {
			$d["error"] = JText::_('VM_ERR_MSG_SHIPPING_RATE_ID_REQ');
			return False;
		}
		return True;
	}

	/**
	 * Creates a new rate record
	 *
	 * @param array $d
	 * @return boolean
	 */
	function rate_add(&$d) {
		$db = new ps_DB;
		$timestamp = time();

		if (!$this->validate_rate_add($d)) {
			return False;
		}
		
		$country_str = "";
		if(!empty($d["shipping_rate_country"])) {
			for($i=0;$i<count($d["shipping_rate_country"]);$i++){
				if ($d["shipping_rate_country"][$i] != "") {
					$country_str .= $d["shipping_rate_country"][$i] . ";";
				}
			}
			chop($country_str,";");
		}
		$fields = array('shipping_rate_name' => vmGet($d, 'shipping_rate_name'),
									'shipping_rate_carrier_id' => (int)vmGet($d, 'shipping_rate_carrier_id'),
									'shipping_rate_country' => $country_str,
									'shipping_rate_zip_start' => vmGet($d, 'shipping_rate_zip_start'),
									'shipping_rate_zip_end' => vmGet($d, 'shipping_rate_zip_end'),
									'shipping_rate_weight_start' => vmGet($d, 'shipping_rate_weight_start'),
									'shipping_rate_weight_end' => vmGet($d, 'shipping_rate_weight_end'),
									'shipping_rate_value' => vmGet($d, 'shipping_rate_value'),
									'shipping_rate_package_fee' => vmGet($d, 'shipping_rate_package_fee'),
									'shipping_rate_currency_id' => vmGet($d, 'shipping_rate_currency_id'),
									'shipping_rate_vat_id' => vmGet($d, 'shipping_rate_vat_id'),
									'shipping_rate_list_order' => (int)vmGet($d, 'shipping_rate_list_order'));
							
		$db->buildQuery('INSERT', '#__{vm}_shipping_rate', $fields );
		if( $db->query() === false ) {
			$GLOBALS['vmLogger']->err( 'Failed to add the shipping rate.');
			return false;
		}
		$_REQUEST['shipping_rate_id'] = $db->last_insert_id();
		$GLOBALS['vmLogger']->info('The shipping rate has been added.');
		
		
		return True;
	}

	/**
	 * Updates a rate entry
	 *
	 * @param array $d
	 * @return boolean
	 */
	function rate_update(&$d) {
		$db = new ps_DB;
		
		if( !$this->validate_rate_add($d)) return false;
		
		$country_str = "";
		if(!empty($d["shipping_rate_country"])) {
			for($i=0;$i<count($d["shipping_rate_country"]);$i++){
				if ($d["shipping_rate_country"][$i] != "") {
					$country_str .= $d["shipping_rate_country"][$i] . ";";
				}
			}
			chop($country_str,";");
		}
		$fields = array('shipping_rate_name' => vmGet($d, 'shipping_rate_name'),
									'shipping_rate_carrier_id' => (int)vmGet($d, 'shipping_rate_carrier_id'),
									'shipping_rate_country' => $country_str,
									'shipping_rate_zip_start' => vmGet($d, 'shipping_rate_zip_start'),
									'shipping_rate_zip_end' => vmGet($d, 'shipping_rate_zip_end'),
									'shipping_rate_weight_start' => vmGet($d, 'shipping_rate_weight_start'),
									'shipping_rate_weight_end' => vmGet($d, 'shipping_rate_weight_end'),
									'shipping_rate_value' => vmGet($d, 'shipping_rate_value'),
									'shipping_rate_package_fee' => vmGet($d, 'shipping_rate_package_fee'),
									'shipping_rate_currency_id' => vmGet($d, 'shipping_rate_currency_id'),
									'shipping_rate_vat_id' => vmGet($d, 'shipping_rate_vat_id'),
									'shipping_rate_list_order' => (int)vmGet($d, 'shipping_rate_list_order'));
							
		$db->buildQuery('UPDATE', '#__{vm}_shipping_rate', $fields, ' WHERE shipping_rate_id=' .(int)$d["shipping_rate_id"] );
		if( $db->query() === false ) {
			$GLOBALS['vmLogger']->err( 'Failed to update the shipping rate.');
			return false;
		}
		
		$GLOBALS['vmLogger']->info('The shipping rate has been updated.');
		
		return True;
	}

	/**
	* Controller for Deleting Shipping Rates.
	*/
	function rate_delete(&$d) {

		if (!$this->validate_rate_delete($d)) {
			return False;
		}
		$record_id = $d["shipping_rate_id"];

		if( is_array( $record_id)) {
			foreach( $record_id as $record) {
				if( !$this->delete_rate_record( $record, $d ))
				return false;
			}
			return true;
		}
		else {
			return $this->delete_rate_record( $record_id, $d );
		}
	}
	/**
	* Deletes one Shipping Rate.
	*/
	function delete_rate_record( $record_id, &$d ) {
		global $db;

		$q = 'DELETE FROM #__{vm}_shipping_rate WHERE ';
		$q .= 'shipping_rate_id = '.(int)$record_id.' LIMIT 1';
		$db->query($q);
		
		return True;
	}

}

?>
