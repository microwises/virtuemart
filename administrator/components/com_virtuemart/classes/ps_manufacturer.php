<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: ps_manufacturer.php 1755 2009-05-01 22:45:17Z rolandd $
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
 * The class is is used to manage the manufacturers in your store.
 *
 */
class ps_manufacturer {

	/**
	 * Validates the Input Parameters onBeforeManufacturerAdd
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_add(&$d) {
		
		require_once(CLASSPATH . 'imageTools.class.php' );
		$valid = true;
		
		$db = new ps_DB;

		if (empty($d["mf_name"])) {
			$GLOBALS['vmLogger']->err( JText::_('VM_MANUF_ERR_NAME') );
			$valid = false;
		}
		else {
			$q = "SELECT count(*) as rowcnt from #__{vm}_manufacturer where";
			$q .= " mf_name='" .  $db->getEscaped($d["mf_name"]) . "'";
			$db->query($q);
			$db->next_record();
			if ($db->f("rowcnt") > 0) {
				$GLOBALS['vmLogger']->err( JText::_('VM_MANUF_ERR_EXISTS') );
			$valid = false;
			}
		}

		/** Image Upload Validation **/

		// do we have an image URL or an image File Upload?
		if (!empty( $d['mf_thumb_image_url'] )) {
			// Image URL
			if (substr( $d['mf_thumb_image_url'], 0, 4) != "http") {
				$GLOBALS['vmLogger']->err( JText::_('VM_PRODUCT_IMAGEURL_MUSTBEGIN') );
				$valid =  false;
			}

			$d["mf_thumb_image"] = $d['mf_thumb_image_url'];
		}
		else {
			// File Upload
			if (!vmImageTools::validate_image( $d, "mf_thumb_image", "manufacturer")) {
				$valid = false;
			}
		}

		if (!empty( $d['mf_full_image_url'] )) {
			// Image URL
			if (substr( $d['mf_full_image_url'], 0, 4) != "http") {
				$GLOBALS['vmLogger']->err( JText::_('VM_PRODUCT_IMAGEURL_MUSTBEGIN') );
				return false;
			}
			$d["mf_full_image"] = $d['mf_full_image_url'];
		}
		else {
			// File Upload
			if (!vmImageTools::validate_image( $d, "mf_full_image", "manufacturer")) {
				$valid = false;
			}
		}

		return $valid;
	}
	/**
	 * Validates the Input Parameters onBeforeManufacturerUpdsate
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_update(&$d) {
		
		$valid = true;
		require_once(CLASSPATH . 'imageTools.class.php' );
		
		if (empty($d["mf_name"])) {
			$GLOBALS['vmLogger']->err( JText::_('VM_MANUF_ERR_NAME') );
				$valid =  false;
		}

		$db = new ps_DB;
		$q = 'SELECT mf_thumb_image, mf_full_image FROM #__{vm}_manufacturer WHERE manufacturer_id='. (int) $d["manufacturer_id"];
		$db->query( $q );
		$db->next_record();

		/** Image Upload Validation **/

		// do we have an image URL or an image File Upload?
		if (!empty( $d['mf_thumb_image_url'] )) {
			// Image URL
			if (substr( $d['mf_thumb_image_url'], 0, 4) != "http") {
				$GLOBALS['vmLogger']->err( JText::_('VM_PRODUCT_IMAGEURL_MUSTBEGIN') );
				$valid =  false;
			}

			// if we have an uploaded image file, prepare this one for deleting.
			if( $db->f("mf_thumb_image") && substr( $db->f("mf_thumb_image"), 0, 4) != "http") {
				$_REQUEST["mf_thumb_image_curr"] = $db->f("product_thumb_image");
				$d["mf_thumb_image_action"] = "delete";
				if (!vmImageTools::validate_image( $d, "product_thumb_image", "manufacturer")) {
					return false;
				}
			}
			$d["mf_thumb_image"] = $d['mf_thumb_image_url'];
		}
		else {
			// File Upload
			if (!vmImageTools::validate_image( $d, "mf_thumb_image", "manufacturer")) {
				$valid = false;
			}
		}

		if (!empty( $d['mf_full_image_url'] )) {
			// Image URL
			if (substr( $d['mf_full_image_url'], 0, 4) != "http") {
				$GLOBALS['vmLogger']->err( JText::_('VM_PRODUCT_IMAGEURL_MUSTBEGIN') );
				return false;
			}
			// if we have an uploaded image file, prepare this one for deleting.
			if( $db->f("mf_full_image") && substr( $db->f("mf_thumb_image"), 0, 4) != "http") {
				$_REQUEST["mf_full_image_curr"] = $db->f("mf_full_image");
				$d["mf_full_image_action"] = "delete";
				if (!vmImageTools::validate_image( $d, "mf_full_image", "manufacturer")) {
					return false;
				}
			}
			$d["mf_full_image"] = $d['mf_full_image_url'];
		}
		else {
			// File Upload
			if (!vmImageTools::validate_image( $d, "mf_full_image", "manufacturer")) {
				$valid = false;
			}
		}

		return $valid;
	}
	/**
	 * Validates the Input Parameters onBeforeManufacturerDelete
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_delete($mf_id, &$d) {
		
		$db = new ps_DB();
		require_once(CLASSPATH . 'imageTools.class.php' );
		
		$mf_id = (int) $mf_id;

		if (empty( $mf_id )) {
			$GLOBALS['vmLogger']->err( JText::_('VM_MANUF_ERR_DELETE_SELECT') );
			return False;
		}
		
		$db->query( "SELECT `#__{vm}_product`.product_id, manufacturer_id  	FROM `#__{vm}_product`, `#__{vm}_product_mf_xref` WHERE manufacturer_id =".intval($mf_id)." AND `#__{vm}_product`.product_id = `#__{vm}_product_mf_xref`.product_id" );				
		if( $db->num_rows() > 0 ) {
			$GLOBALS['vmLogger']->err( JText::_('VM_MANUF_ERR_DELETE_STILLPRODUCTS') );
			return false;
		}
		
		$q = "SELECT mf_thumb_image,mf_full_image FROM #__{vm}_manufacturer WHERE manufacturer_id='$mf_id'";
		$db->query( $q );
		$db->next_record();

		/* Prepare mf_thumb_image for Deleting */
		if( !stristr( $db->f("mf_thumb_image"), "http") ) {
			$_REQUEST["mf_thumb_image_curr"] = $db->f("mf_thumb_image");
			$d["mf_thumb_image_action"] = "delete";
			if (!vmImageTools::validate_image($d,"mf_thumb_image","category")) {
				$GLOBALS['vmLogger']->err( JText::_('VM_PRODUCT_MANUFACTURER_ERR_DELETE_IMAGES') );
				return false;
			}
		}
		/* Prepare product_full_image for Deleting */
		if( !stristr( $db->f("mf_full_image"), "http") ) {
			$_REQUEST["mf_full_image_curr"] = $db->f("mf_full_image");
			$d["mf_full_image_action"] = "delete";
			if (!vmImageTools::validate_image($d,"mf_full_image","category")) {
				return false;
			}
		}
		
		return True;

	}

	/**
	 * creates a new manufacturer record
	 *
	 * @param array $d
	 * @return boolean
	 */
	function add(&$d) {
		
		
		$db = new ps_DB;
		
		if (!$this->validate_add($d)) {
			return false;
		}
		
		// Check the images
		if (!vmImageTools::process_images($d)) {
			return false;
		}
		
		$fields = array( 'mf_name' => vmGet( $d, 'mf_name' ),
					'mf_email' => vmGet( $d, 'mf_email' ),
					'mf_desc' => vmGet( $d, 'mf_desc', '', VMREQUEST_ALLOWHTML ),
					'mf_category_id' => vmRequest::getInt('mf_category_id'),
					'mf_url' => vmGet( $d, 'mf_url'),
					'mf_thumb_image' => vmGet( $d, 'mf_thumb_image' ),
					'mf_full_image' => vmGet( $d, 'mf_full_image' )
		);
		
		$db->buildQuery('INSERT', '#__{vm}_manufacturer', $fields );
		if( $db->query() !== false ) {
			$GLOBALS['vmLogger']->info( JText::_('VM_MANUF_ADDED') );
			$_REQUEST['manufacturer_id'] = $db->last_insert_id();
			return true;	
		}
		return false;

	}

	/**
	 * updates manufacturer information
	 *
	 * @param array $d
	 * @return boolean
	 */
	function update(&$d) {
		
		
		$db = new ps_DB;
		
		if (!$this->validate_update($d)) {
			return False;
		}
		
		// Check the images
		if (!vmImageTools::process_images($d)) {
			return false;
		}
		
		$fields = array( 'mf_name' => vmGet( $d, 'mf_name' ),
					'mf_email' => vmGet( $d, 'mf_email' ),
					'mf_desc' => vmGet( $d, 'mf_desc', '', VMREQUEST_ALLOWHTML ),
					'mf_category_id' => vmRequest::getInt('mf_category_id'),
					'mf_url' => vmGet( $d, 'mf_url'),
					'mf_thumb_image' => vmGet( $d, 'mf_thumb_image' ),
					'mf_full_image' => vmGet( $d, 'mf_full_image' )
		);
		$db->buildQuery('UPDATE', '#__{vm}_manufacturer', $fields, 'WHERE manufacturer_id='.(int)$d["manufacturer_id"] );
		if( $db->query() ) {
			$GLOBALS['vmLogger']->info( JText::_('VM_MANUF_UPDATED') );
			return true;	
		}
		return false;
	}

	/**
	* Controller for Deleting Records.
	*/
	function delete(&$d) {

		$record_id = $d["manufacturer_id"];

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
		global $db;
		if (!$this->validate_delete($record_id, $d)) {
			return False;
		}
		$q = 'DELETE from #__{vm}_product_mf_xref WHERE manufacturer_id='.(int)$record_id.' LIMIT 1';
		$db->query($q);
		$q = 'DELETE from #__{vm}_manufacturer WHERE manufacturer_id='.(int)$record_id.' LIMIT 1';
		$db->query($q);

		// Delete the image files
		if (!vmImageTools::process_images($d)) {
			return false;
		}

		return True;
	}
	/**
	 * Prints a drop-down list of manufacturer names and their ids.
	 *
	 * @param int $manufacturer_id
	 */
	function list_manufacturer($manufacturer_id='0') {

		$db = new ps_DB;

		$q = "SELECT manufacturer_id as id,mf_name as name FROM #__{vm}_manufacturer ORDER BY mf_name";
		$db->query($q);
		$db->next_record();

		// If only one vendor do not show list
		if ($db->num_rows() == 1) {

			echo '<input type="hidden" name="manufacturer_id" value="'. $db->f("id").'" />';
			echo $db->f("name");
		}
		elseif( $db->num_rows() > 1) {
			$db->reset();
			$array = array();
			while ($db->next_record()) {
				$array[$db->f("id")] = $db->f("name");
			}
			$code = ps_html::selectList('manufacturer_id', $manufacturer_id, $array ). "<br />\n";
			echo $code;
		}
		else  {
			echo '<input type="hidden" name="manufacturer_id" value="1" />Please create at least one Manufacturer!!';
		}
	}
}

?>