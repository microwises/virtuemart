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
 * VirtueMart Shopper Group Handler
 *
 */
class ps_shopper_group extends vmAbstractObject  {

	var $key = 'shopper_group_id';
	var $_required_fields = array('shopper_group_name');
	var $_table_name = '#__{vm}_shopper_group';

	/**
	 * Validates the Input Parameters onBeforeShopperGroupAdd
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_add(&$d) {
		

		$db = new ps_DB;

		//of product or order
//		$vendor_id = $_SESSION["ps_vendor_id"];

		if (empty($d["shopper_group_name"])) {
			$GLOBALS['vmLogger']->err(JText::_('VM_SHOPPER_GROUP_MISSING_NAME'));
			return False;
		}
		else {
			$q = "SELECT COUNT(*) as num_rows "
				."FROM `#__{vm}_shopper_group` "
				."WHERE `shopper_group_name`='" .$db->getEscaped(vmGet($d,'shopper_group_name')) . "' ";
//			$q .= "AND `vendor_id`='" . $vendor_id . "'";

			$db->query($q);
			$db->next_record();
			if ($db->f("num_rows") > 0) {
				$GLOBALS['vmLogger']->err(JText::_('VM_SHOPPER_GROUP_ALREADY_EXISTS'));
				return False;
			}
		}
// Change this
		if (empty($d["shopper_group_discount"])) {
			$d["shopper_group_discount"] = 0;
		}

		$d["show_price_including_tax"] = isset( $d["show_price_including_tax"] ) ? $d["show_price_including_tax"] : 0;

		return True;
	}

	/**
	 * Validates the Input Parameters onBeforeShopperGroupUpdate
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_update(&$d) {
		

		if (!$d["shopper_group_name"]) {
			$GLOBALS['vmLogger']->err(JText::_('VM_SHOPPER_GROUP_MISSING_NAME'));
			return False;
		}
		if (empty($d["shopper_group_discount"])) {
			$d["shopper_group_discount"] = 0;
		}

		$d["show_price_including_tax"] = isset( $d["show_price_including_tax"] ) ? $d["show_price_including_tax"] : 0;

		return True;
	}

	/**
	 * Validates the Input Parameters onBeforeShopperGroupDelete
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_delete( $shopper_group_id, &$d) {
		

		$db = new ps_DB;
		$shopper_group_id = intval( $shopper_group_id );
		if (empty($shopper_group_id)) {
			$GLOBALS['vmLogger']->err(JText::_('VM_SHOPPER_GROUP_DELETE_SELECT'));
			return False;
		}
		// Check if the Shopper Group still has Payment Methods assigned to it
		// Move to payment?
		$db->query( "SELECT `payment_method_id` FROM `#__{vm}_payment_method` WHERE `shopper_group_id`=".$shopper_group_id);
		if( $db->next_record()) {
			$GLOBALS['vmLogger']->err(str_replace('{id}',$shopper_group_id,JText::_('VM_SHOPPER_GROUP_DELETE_PAYMENT_METHODS_ASS')));
			return False;
		}
		// Check if there are Users in this Shopper Group
		// mvoe to vendor?
		$db->query( "SELECT `user_id` FROM `#__{vm}_shopper_vendor_xref` WHERE `shopper_group_id`=".$shopper_group_id);
		if( $db->next_record()) {
			$GLOBALS['vmLogger']->err(str_replace('{id}',$shopper_group_id,JText::_('VM_SHOPPER_GROUP_DELETE_USERS_ASS')));
			return False;
		}

		$q = "SELECT `shopper_group_id` FROM `#__{vm}_shopper_group` WHERE `shopper_group_id`=". $shopper_group_id
					. " AND `default`='1'";
		$db->query($q);
		if ($db->next_record()) {
			$GLOBALS['vmLogger']->err(JText::_('VM_SHOPPER_GROUP_DELETE_DEFAULT'));
			return False;
		}

		return True;
	}

	/**
	 * Adds a new Shopper Group
	 *
	 * @internal use addUpdateShopperGroup( $d )
	 * @deprecated 1.2.0
	 * @param array $d
	 * @return boolean
	 */
	function add(&$d) {
		return self::addUpdateShopperGroup( $d );
	}

	/**
	 * Updates an existing Shopper Group
	 *
 	 * @internal use addUpdateShopperGroup( $d )
	 * @deprecated 1.2.0
	 * @param array $d
	 * @return boolean
	 */
	function update($d) {
		return self::addUpdateShopperGroup( $d );
	}

	/**
	 * Add/Update a shopper group
	 *
	 * @since 1.2.0
	 * @author Daniel Jonsson
	 * @param array $d
	 * @return boolean
	 */
	function addUpdateShopperGroup( &$d ) {

		global $perm, $vmLogger;
		$db = new ps_DB;


		$shopper_group_id = $d["shopper_group_id"];
		$vendor_id = $perm->check( "admin" ) ? $d["vendor_id"] : $_SESSION["ps_vendor_id"];
		$vendor_id = empty($vendor_id) ? 1 : $vendor_id; // If not set use default as 1.

		$answer = False;
		$default = @$d["default"]=="1" ? "1" : "0";

		// ADD LATER.
		/*if (!self::validate_addUpdateShopperGroup($d)) {
		 *	return False;
		 * }
		*/
		// Change shopper group discount.
		$fields = array('vendor_id' => $vendor_id,
						'shopper_group_name' => $d["shopper_group_name"],
						'shopper_group_desc' => $d["shopper_group_desc"],
						'shopper_group_discount' => $d["shopper_group_discount"],
						'show_price_including_tax' => $d["show_price_including_tax"],
						'default' => $default
					);

		if (empty($shopper_group_id)) {	// INSERT
			$db->buildQuery( 'INSERT', '#__{vm}_shopper_group', $fields );

			if( $db->query() !== false ) {
				$shopper_group_id = $db->last_insert_id();
				vmRequest::setVar( 'shopper_group_id', $shopper_group_id );
				$vmLogger->info(JText::_('VM_SHOPPER_GROUP_ADDED'));
				$answer = $_REQUEST['shopper_group_id'];
			} else {
				$vmLogger->err(JText::_('VM_SHOPPER_GROUP_ADD_FAILED'));
			}
		} else { // UPDATE
			$db->buildQuery( 'UPDATE', '#__{vm}_shopper_group', $fields, 'WHERE `shopper_group_id`=' . (int)$d["shopper_group_id"] );

			if( $db->query() ) {
				$GLOBALS['vmLogger']->info(JText::_('VM_SHOPPER_GROUP_UPDATED'));

				$answer = True;
			} else {
				$GLOBALS['vmLogger']->err(JText::_('VM_SHOPPER_GROUP_UPDATE_FAILED'));
			}
			if($answer) {
				if ($default == "1") {
					$q = "UPDATE `#__{vm}_shopper_group` "
						."SET `default`=0 "
						."WHERE `shopper_group_id` !=". $shopper_group_id  ." "
						."AND `vendor_id` =". $vendor_id;
					$db->query($q);
					$db->next_record();
				}
			}
		}
		unset($db);
		return $answer;
	}



	/**
	* Controller for Deleting Records.
	*/
	function delete(&$d) {

		$record_id = $d["shopper_group_id"];

		if( is_array( $record_id)) {
			foreach( $record_id as $record) {
				if( !self::delete_record( $record, $d )) {
					return false;
				}
			}
			return true;
		}
		return self::delete_record( $record_id, $d );
	}
	/**
	* Deletes one Record.
	*/
	function delete_record( $record_id, &$d ) {

		$record_id = intval( $record_id );
		$answer= False;

		if (self::validate_delete( $record_id, $d)) {
			$db = new ps_DB;

			$q = "DELETE FROM `#__{vm}_shopper_group` WHERE `shopper_group_id`=".$record_id;
			$db->query($q);
			$db->next_record();

			$q = "DELETE FROM `#__{vm}_shopper_vendor_xref` WHERE `shopper_group_id`=".$record_id;
			$db->query($q);
			$db->next_record();

			$q = "DELETE FROM `#__{vm}_product_price` WHERE `shopper_group_id`=".$record_id;
			$db->query($q);
			$db->next_record();

			unset($db);
			return True;
		}

		return False;
	}

	/**
	 * Creates a Drop Down list of available Shopper Groups
	 *
	 * @param string $name
	 * @param int $shopper_group_id
	 * @param string $extra
	 * @return string
	 */
	function list_shopper_groups($name,$shopper_group_id='0', $extra='') {
		global $perm, $hVendor;
		$db = new ps_DB;

		if( !$perm->check("admin")) {
			$vendor_id = $hVendor->getLoggedVendor();
		} else {
			$vendor_id = "#__{vm}_vendor.vendor_id";
		}

		$q = "SELECT `shopper_group_id`, `shopper_group_name`, `#__{vm}_shopper_group`.`vendor_id`, `vendor_name` "
			."FROM `#__{vm}_shopper_group`, `#__{vm}_vendor` "
			."WHERE `#__{vm}_shopper_group`.`vendor_id` =  ".$vendor_id." "
			."ORDER BY `shopper_group_name`";

		$db->query($q);
		while ($db->next_record()) {
			$shopper_groups[$db->f("shopper_group_id")] = $db->f("shopper_group_name"); // . '; '.$db->f('vendor_name').' (Vendor ID: '.$db->f('vendor_id').")";
		}
		unset($db);

		return ps_html::selectList( $name, $shopper_group_id, $shopper_groups, 1, '', $extra );
	}

	/**
	 * Retrieves the Shopper Group ID of the currently logged-in User
	 *
	 * @return int
	 */
	function get_id() {
		$auth = $_SESSION['auth'];

		$db = new ps_DB;

		$q = "SELECT `#__{vm}_shopper_group`.`shopper_group_id` "
			."FROM `#__{vm}_shopper_group`, `#__{vm}_shopper_vendor_xref` "
			."WHERE `#__{vm}_shopper_vendor_xref`.`user_id`='" . $auth["user_id"] . "' "
			."AND `#__{vm}_shopper_group`.`shopper_group_id`=`#__{vm}_shopper_vendor_xref`.`shopper_group_id`";
		$db->query($q);
		$db->next_record();
		$shopper_group_id=$db->f("shopper_group_id");

		unset($db);
		return $shopper_group_id;
	}

	/**
	 * Retrieves the Shopper Group Info of the SG specified by $id
	 *
	 * @param int $id
	 * @param boolean $default_group
	 * @return array
	 */
  	function get_shoppergroup_by_id($id, $default_group = false) {
//		echo('get_shoppergroup_by_id: '.$id.' und defaultGroup: '.$default_group.' <br />');
    	//TODO
    	$vendorId = 1;
//    	$vendorId = vmGet($_SESSION, 'ps_vendor_id', 1 );
    	$db = new ps_DB;

    	$q =  "SELECT `#__{vm}_shopper_group`.`shopper_group_id`, `show_price_including_tax`, `default`, `shopper_group_discount`
    		FROM `#__{vm}_shopper_group`";
    	if( !empty( $id ) && !$default_group) {
      		$q .= ", `#__{vm}_shopper_vendor_xref`";
      		$q .= " WHERE `#__{vm}_shopper_vendor_xref`.`user_id`='" . $id . "' AND ";
      		$q .= "`#__{vm}_shopper_group`.`shopper_group_id`=`#__{vm}_shopper_vendor_xref`.`shopper_group_id`";
    	} else {
    		$q .= " WHERE `#__{vm}_shopper_group`.`vendor_id`='$vendorId' AND `default`='1'";
    	}
    	$db->query($q);
//    	echo('get_shoppergroup_by_id query: '.$q);	
    	if (!$db->next_record()){ //not sure that is is filled in database (Steve)
			$q = "SELECT `shopper_group_id`, `show_price_including_tax`, `default`, `shopper_group_discount` "
    			."FROM `#__{vm}_shopper_group` "
    			."WHERE `vendor_id`='$vendorId' "
    			."AND `default`='1'";
			$db->query($q);
			$db->next_record();

		}
		$group["shopper_group_id"] = $db->f("shopper_group_id");
        $group["shopper_group_discount"] = $db->f("shopper_group_discount");
        $group["show_price_including_tax"] = $db->f("show_price_including_tax");
        $group["default_shopper_group"] = $db->f("default");

		unset($db);
    	return $group;
  	}
  	/**
  	 * Creates superglobals with the information regarding the default shopper group
  	 *
  	 */
  	function makeDefaultShopperGroupInfo($vendor_id ) {

//		$vendor_id = ps_product::get_vendor_id_ofproduct($product_id);

		if( empty($GLOBALS['vendor_info'][$vendor_id]['default_shopper_group_id']) ) {
			$db = new ps_DB;
			// Get the default shopper group id for this vendor
			$q = "SELECT `shopper_group_id`, `shopper_group_discount` FROM `#__{vm}_shopper_group` WHERE ";
			$q .= "`vendor_id`='$vendor_id' AND `default`='1'";
//			$q .= " `default`='1'";
			$db->query( $q );
			$db->next_record();
			$GLOBALS['vendor_info'][$vendor_id]['default_shopper_group_id'] = $default_shopper_group_id = $db->f("shopper_group_id");
			$GLOBALS['vendor_info'][$vendor_id]['default_shopper_group_discount']= $default_shopper_group_discount = $db->f("shopper_group_discount");
			unset( $db );
		}
  	}

	/**
	 * Retrieves the Customer Number of the user specified by ID
	 *
	 * @internal use ps_shopper::get_customer_num($id)
	 * @deprecated 1.2.0
	 * @param int $id
	 * @return string
	 */
	function get_customer_num($id) {
		require_once( CLASSPATH . "ps_shopper.php");
		return ps_shopper::get_customer_num($id);
	}


}
$ps_shopper_group = new ps_shopper_group;

?>
