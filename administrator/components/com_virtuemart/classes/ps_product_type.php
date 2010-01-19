<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
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
 * Product Type Handling Class
 *
 */
class ps_product_type {

	/**
	 * Validates the Input Parameters onBeforeProductTypeAdd
	 * @author Zdenek Dvorak
	 * @param array $d
	 * @return boolean
	 */
	function validate_add(&$d) {
		
		
		if (empty($d["product_type_name"])) {
			$GLOBALS['vmLogger']->err( JText::_('VM_PRODUCT_TYPE_ERR_NAME') );
			return False;
		}
		else {
			return True;
		}
	}

	/**
	 * Validates the Input Parameters onBeforeProductTypeDelete
	 * @author Zdenek Dvorak
	 * @param int $product_type_id
	 * @param array $d
	 * @return boolean
	 */
	function validate_delete( $product_type_id, &$d) {
		
		
		$db = new ps_DB;

		if (empty( $product_type_id)) {
			$GLOBALS['vmLogger']->err( JText::_('VM_PRODUCT_TYPE_DELETE_SELECT') );
			return False;
		}

		return True;
	}

	/**
	 * Validates the Input Parameters onBeforeProductTypeUpdate
	 * @author Zdenek Dvorak
	 * @param array $d
	 * @return boolean
	 */
	function validate_update(&$d) {
		
		
		if (!$d["product_type_name"]) {
			$GLOBALS['vmLogger']->err( JText::_('VM_PRODUCT_TYPE_ERR_NAME') );
			return False;
		}
		else {
			return True;
		}
	}

	/**
	 * creates a new Product Type record
	 * @author Zdenek Dvorak
	 *
	 * @param array $d
	 * @return boolean
	 */
	function add(&$d) {
		
		
		$db = new ps_DB;

		if ($this->validate_add($d)) {

			// find product_type_id
			$q  = "SELECT MAX(product_type_id) AS product_type_id FROM #__{vm}_product_type";
			$db->query( $q );
			$db->next_record();
			$product_type_id = intval($db->f("product_type_id")) + 1;

			// Let's find out the last Product Type
			$q = "SELECT MAX(product_type_list_order) AS list_order FROM #__{vm}_product_type";
			$db->query( $q );
			$db->next_record();
			$list_order = intval($db->f("list_order"))+1;
			if ($d["published"] != "1") {
				$d["published"] = "0";
			}
			
			$fields = array( 'product_type_id' => $product_type_id,
										'product_type_name' => vmGet($d, 'product_type_name' ),
										'product_type_description' => vmGet($d, 'product_type_description'),
										'published' => vmGet($d, 'published'),
										'product_type_browsepage' => vmGet($d, 'product_type_browsepage'),
										'product_type_flypage' => vmGet($d, 'product_type_flypage'),
										'product_type_list_order' => $list_order
										);			
			$db->buildQuery('INSERT', '#__{vm}_product_type', $fields );
			$db->query();
			
			$_REQUEST['product_type_id'] = $product_type_id;
			
			// Make new table product_type_<id>
			$q = "CREATE TABLE `#__{vm}_product_type_";
			$q .= $product_type_id . "` (";
			$q .= "`product_id` int(11) NOT NULL,";
			$q .= "PRIMARY KEY (`product_id`)";
			$q .= ") TYPE=MyISAM;";
			$db->setQuery($q);
			
			if( $db->query() === false ) {
				$GLOBALS['vmLogger']->err( JText::_('VM_PRODUCT_TYPE_ADD_FAILED') );
				return false;
			} else {
				$GLOBALS['vmLogger']->info( JText::_('VM_PRODUCT_TYPE_ADDED') );
				return true;
			}
			
		}
		else {
			return False;
		}

	}

	/**
	 * updates Product Type information
	 * @author Zdenek Dvorak
	 * @param array $d
	 * @return boolean
	 */
	function update(&$d) {
		$db = new ps_DB;

		if ($this->validate_update($d)) {
			if (empty($d["published"])) {
				$d["published"] = "0";
			}
			$fields = array( 
										'product_type_name' => vmGet($d, 'product_type_name' ),
										'product_type_description' => vmGet($d, 'product_type_description'),
										'published' => vmGet($d, 'published'),
										'product_type_browsepage' => vmGet($d, 'product_type_browsepage'),
										'product_type_flypage' => vmGet($d, 'product_type_flypage'),
										'product_type_list_order' => vmRequest::getInt('list_order')
										);			
			$db->buildQuery('UPDATE', '#__{vm}_product_type', $fields, 'WHERE product_type_id=' .(int)$d["product_type_id"] );
			$db->query();

			// Re-Order the Product Type table IF the list_order has been changed
			if( intval($d['list_order']) != intval($d['currentpos'])) {
				$dbu = new ps_DB;

				/* Moved UP in the list order */
				if( intval($d['list_order']) < intval($d['currentpos']) ) {

					$q  = "SELECT product_type_id FROM #__{vm}_product_type WHERE ";
					$q .= "product_type_id <> '" . $d["product_type_id"] . "' ";
					$q .= "AND product_type_list_order >= '" . intval($d["list_order"]) . "'";
					$db->query( $q );

					while( $db->next_record() ) {
						$dbu->query("UPDATE #__{vm}_product_type SET product_type_list_order=product_type_list_order+1 WHERE product_type_id='".$db->f("product_type_id")."'");
					}
				}
				// Moved DOWN in the list order
				else {

					$q = "SELECT product_type_id FROM #__{vm}_product_type WHERE ";
					$q .= "product_type_id <> '" . $d["product_type_id"] . "' ";
					$q .= "AND product_type_list_order > '" . intval($d["currentpos"]) . "'";
					$q .= "AND product_type_list_order <= '" . intval($d["list_order"]) . "'";
					$db->query( $q );

					while( $db->next_record() ) {
						$dbu->query("UPDATE #__{vm}_product_type SET product_type_list_order=product_type_list_order-1 WHERE product_type_id='".$db->f("product_type_id")."'");
					}

				}
			} // END Re-Ordering

			return True;
		}
		else {
			return False;
		}
	}

	/**
	* Controller for Deleting Records.
	*/
	function delete(&$d) {

		$record_id = (int)$d["product_type_id"];
		require_once( CLASSPATH.'ps_product_type_parameter.php');
		
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
	 * Should delete a Product Type and drop table product_type_<id>
	 *
	 * @param int $record_id
	 * @param array $d
	 * @return boolean True on success
	 */
	function delete_record( $record_id, &$d ) {
		global $db;

		if (!$this->validate_delete( $record_id, $d)) {
			return False;
		}
		// Delete all product parameters from this product type
		$q = 'SELECT `parameter_name` FROM `#__{vm}_product_type_parameter` WHERE `product_type_id`='.$record_id;
		$db->query($q);
		while( $db->next_record() ) {
			if( !isset($ps_product_type_parameter)) { $ps_product_type_parameter = new ps_product_type_parameter(); }
			$arr['product_type_id'] = $record_id;
			$arr['parameter_name'] = $db->f('parameter_name');
			$ps_product_type_parameter->delete_parameter( $arr );
		}
		
		$q = "DELETE FROM #__{vm}_product_type WHERE product_type_id='$record_id'";
		$db->setQuery($q);   $db->query();

		$q  = "DELETE FROM #__{vm}_product_product_type_xref WHERE product_type_id='$record_id'";
		$db->setQuery($q);   $db->query();

		$q  = "DROP TABLE IF EXISTS `#__{vm}_product_type_".$record_id."`";
		$db->setQuery($q);   $db->query();
		return True;
	}

	/**
	 * Calculates and returns number of products assigned to this Product Type
	 *
	 * @param int $product_type_id
	 * @return int
	 */
	function product_count($product_type_id) {
		
//		global $hVendor;
//		$vendor_id = $hVendor->getLoggedVendor();

		$db = new ps_DB;

		$count  = "SELECT count(*) as num_rows from #__{vm}_product,#__{vm}_product_product_type_xref WHERE ";
//		$q  = "#__{vm}_product.vendor_id = '$vendor_id' ";  // I think this is not senseful by Max Milbers
		$q .= "AND #__{vm}_product_product_type_xref.product_type_id='$product_type_id' ";
		$q .= "AND #__{vm}_product.product_id=#__{vm}_product_product_type_xref.product_id ";
		$q .= "AND #__{vm}_product.product_parent_id='' ";
		//$q .= "ORDER BY product_publish DESC,product_name ";
		$count .= $q;
		$db->query($count);
		$db->next_record();
		return $db->f("num_rows");
	}

	/**
	 * Calculates and returns number of parameters in given Product Type
	 *
	 * @param int $product_type_id
	 * @return int
	 */
	function parameter_count($product_type_id) {
		$db = new ps_DB;

		$count  = "SELECT count(*) as num_rows from #__{vm}_product_type_parameter WHERE ";
		$q = "product_type_id='$product_type_id' ";
		$count .= $q;
		$db->query($count);
		$db->next_record();
		return $db->f("num_rows");
	}

	/**
	 * Returns the Product Type name.
	 *
	 * @param int $product_type_id
	 * @return string
	 */
	function get_name($product_type_id) {
		$db = new ps_DB;

		$q = "SELECT product_type_name FROM #__{vm}_product_type WHERE product_type_id='$product_type_id' ";
		
		$db->query($q);
		$db->next_record();

		return $db->f("product_type_name");
	}

	/**
	 * Returns the Product Type Description
	 *
	 * @param int $product_type_id
	 * @return string
	 */
	function get_description($product_type_id) {
		$db = new ps_DB;

		$q = "SELECT product_type_description FROM #__{vm}_product_type ";
		$q .= "WHERE product_type_id='$product_type_id' ";
		
		$db->query($q);
		$db->next_record();

		return $db->f("product_type_description");
	}

	/**
	 * lists all Product Types
	 *
	 * @param int $product_type_id
	 * @param int $list_order
	 * @return string
	 */
	function list_order( $product_type_id='0', $list_order=0 ) {

		$db = new ps_DB;
		if (!$product_type_id) {
			return JText::_( 'CMN_NEW_ITEM_LAST' );
		}
		else {

			$q  = "SELECT product_type_list_order,product_type_name FROM #__{vm}_product_type ";
			if( $product_type_id ) {
				$q .= 'WHERE product_type_id='.$product_type_id;
			}
			$q .= " ORDER BY product_type_list_order ASC";
			$db->query( $q );

			$array = array();
			while( $db->next_record() ) {
				$array[$db->f("product_type_list_order")] = $db->f("product_type_list_order").". ".$db->f("product_type_name");
			}
			
			return ps_html::selectList('list_order', $list_order, $array );
		}
	}

	/**
	 * Changes the Product Type List Order
	 *
	 * @param array $d
	 */
	function reorder( &$d ) {
		$cb = JRequest::getVar( 'product_type_id', array(0) );

		$db = new ps_DB;
		switch( $d["task"] ) {
			case "orderup":
				$q = "SELECT product_type_list_order FROM #__{vm}_product_type ";
				$q .= "WHERE product_type_id='".(int)$cb[0]."' ";
				$db->query($q);
				$db->next_record();
				$currentpos = $db->f("product_type_list_order");
				//$category_parent_id = $db->f("category_parent_id");

				// Get the (former) predecessor and update it
				$q  = "SELECT product_type_list_order,product_type_id FROM #__{vm}_product_type WHERE ";
				$q .= "product_type_list_order<'". $currentpos . "' ";
				$q .= "ORDER BY product_type_list_order DESC";
				$db->query($q);
				$db->next_record();
				$pred = $db->f("product_type_id");
				$pred_pos = $db->f("product_type_list_order");

				// Update the Product Type and decrease the list_order
				$q = "UPDATE #__{vm}_product_type ";
				$q .= "SET product_type_list_order='".$pred_pos."' ";
				$q .= "WHERE product_type_id='".(int)$cb[0]."'";
				$db->query($q);

				$q = "UPDATE #__{vm}_product_type ";
				$q .= "SET product_type_list_order='".intval($pred_pos + 1)."' ";
				$q .= "WHERE product_type_id='$pred'";
				$db->query($q);

				break;

			case "orderdown":
				$q = "SELECT product_type_list_order FROM #__{vm}_product_type ";
				$q .= "WHERE product_type_id='".(int)$cb[0]."' ";
				$db->query($q);
				$db->next_record();
				$currentpos = $db->f("product_type_list_order");

				// Get the (former) successor and update it
				$q  = "SELECT product_type_list_order,product_type_id FROM #__{vm}_product_type WHERE ";
				$q .= "product_type_list_order>'". $currentpos . "' ";
				$q .= "ORDER BY product_type_list_order";
				$db->query($q);
				$db->next_record();
				$succ = $db->f("product_type_id");
				$succ_pos = $db->f("product_type_list_order");

				$q = "UPDATE #__{vm}_product_type ";
				$q .= "SET product_type_list_order='".$succ_pos."' ";
				$q .= "WHERE product_type_id='".(int)$cb[0]."' ";
				$db->query($q);

				$q = "UPDATE #__{vm}_product_type ";
				$q .= "SET product_type_list_order='".intval($succ_pos - 1)."' ";
				$q .= "WHERE product_type_id='$succ'";
				$db->query($q);

				break;
		}

	}
	function get_product_type_form($product_type_id, $product_id=0) {
		global $ps_html, $keyword, $limitstart, $product_parent_id, $next_page;
		$product_type_id = (int)$product_type_id;
		if( empty($product_type_id)) return;
		
		$dbpt = new ps_DB;
		$dbp = new ps_DB;
		
		$product_type_name = $this->get_name($product_type_id);
		
		$q  = "SELECT * FROM #__{vm}_product_type_parameter WHERE ";
		$q .= "product_type_id='$product_type_id' ";
		$q .= "ORDER BY parameter_list_order";
		$dbpt->query($q);
	
		$q  = "SELECT * FROM #__{vm}_product_type_$product_type_id WHERE ";
		$q .= "product_id=$product_id";
		$dbp->query($q);
		
		$html = '		
		  <table class="adminform">
		    <tr class="row0"> 
		      <td colspan="2"><h2>'. JText::_('VM_PRODUCT_TYPE_LBL').': '.$product_type_name.'</h2>';
		
		      if( $product_id > 0 ) {
		      	$html .= '<h3>'.JText::_('E_REMOVE').' =&gt; '.$ps_html->deleteButton( "product_type_id", $product_type_id, "productProductTypeDelete", $keyword, $limitstart, "&product_id=$product_id&product_parent_id=$product_parent_id&next_page=$next_page" ) . '</h3>';
		      }
		      $html .= '
		      </td>
		    </tr>';
		    
	    $i = 0;
	    while ($dbpt->next_record()) {
	    	if ($dbpt->f("parameter_type")!="B") {
	    		$html .= "<tr class=\"row".$i++ % 2 . "\">\n  <td width=\"21%\" height=\"2\" valign=\"top\"><div style=\"text-align:right;font-weight:bold;\">";
	    		$html .= $dbpt->f("parameter_label");
	    		$html .= ":</div>\n  </td>\n  <td width=\"79%\" valign=\"top\" >";
	
	    		$parameter_values=$dbpt->f("parameter_values");
	    		if (!empty($parameter_values)) { // List of values
	    			$fields=explode(";",$parameter_values);
	    			$html .= "<select class=\"inputbox\" name=\"product_type_".$product_type_id."_".$dbpt->f("parameter_name");
	
	    			if ($dbpt->f("parameter_type")=="V") { //  Type: Multiple Values
	    				$size = min(count($fields),6);
	    				$html .= "[]\" multiple size=\"$size\">\n";
	    				$selected_value = array();
	    				$get_item_value = $dbp->f($dbpt->f("parameter_name"));
	    				$get_item_value = explode(";",$get_item_value);
	    				foreach($get_item_value as $value) {
	    					$selected_value[$value] = 1;
	    				}
	    				foreach($fields as $field) {
	    					$html .= "<option value=\"$field\"".(($selected_value[$field]==1) ? " selected>" : ">"). $field."</option>\n";
	    				}
	    			}
	    			else {  // Other Parameter type
	    				$html .= "\">\n";
	    				foreach($fields as $field) {
	    					$html .= "<option value=\"$field\" ";
	    					if ($dbp->f($dbpt->f("parameter_name")) == $field) $html .= "selected=\"selected\"";
	    					$html .= " >".$field."</option>\n";
	    				}
	    			}
	    			echo "</select>\n";
	    		}
	    		else { // Input field
	    			switch( $dbpt->f("parameter_type") ) {
	    				case "I": // Integer
	    				case "F": // Float
	    				case "D": // Date & Time
	    				case "A": // Date
	    				case "M": // Time
	    					$html .= "    <input type=\"text\" class=\"inputbox\"  name=\"product_type_".$product_type_id."_".$dbpt->f("parameter_name")."\" value=\"".$dbp->f($dbpt->f("parameter_name"))."\" size=\"20\" />";
	    				break;
	    				case "T": // Text
	    				case "S": // Short Text
	    					$html .= "<textarea class=\"inputbox\" name=\"product_type_".$product_type_id."_".$dbpt->f("parameter_name")."\" cols=\"35\" rows=\"6\" >";
	    					$html .= $dbp->sf($dbpt->f("parameter_name"))."</textarea>";
	    				break;
	    				case "C": // Char
	    					$html .= "    <input type=\"text\" class=\"inputbox\"  name=\"product_type_".$product_type_id."_".$dbpt->f("parameter_name")."\" value=\"".$dbp->f($dbpt->f("parameter_name"))."\" size=\"5\" />";
	    				break;
	    				case "V": // Multiple Values
	    					$html .= "    <input type=\"text\" class=\"inputbox\"  name=\"product_type_".$product_type_id."_".$dbpt->f("parameter_name")."\" value=\"".$dbp->f($dbpt->f("parameter_name"))."\" size=\"20\" />";
	
	    				// 						$fields=explode(";",$parameter_values);
	    				// 						echo "<select class=\"inputbox\" name=\"product_type_".$product_type_id."_".$dbpt->f("parameter_name");
	    				// 						if ($db->f("parameter_multiselect")=="Y") {
	    				// 							$size = min(count($fields),6);
	    				// 							echo "[]\" multiple size=\"$size\">\n";
	    				// 							$selected_value = array();
	    				// 							$get_item_value = explode(",",$dbp->sf($dbpt->f("parameter_name")));
	    				// 							foreach($get_item_value as $value) {
	    				// 								$selected_value[$value] = 1;
	    				// 							}
	    				// 							foreach($fields as $field) {
	    				// 								echo "<option value=\"$field\"".(($selected_value[$field]==1) ? " selected>" : ">"). $field."</option>\n";
	    				// 							}
	    				// 						}
	    				// 						else {
	    				// 							echo "\">\n";
	    				// 							$get_item_value = $dbp->sf($dbpt->f("parameter_name"));
	    				// 							foreach($fields as $field) {
	    				// 								echo "<option value=\"$field\"".(($get_item_value==$field) ? " selected>" : ">"). $field."</option>\n";
	    				// 							}
	    				// 						}
	    				// 						echo "</select>";
	    				break;
	    					default: // Default type Short Text
	    						$html .= "    <input type=\"text\" class=\"inputbox\" name=\"product_type_".$product_type_id."_".$dbpt->f("parameter_name")."\" value=\"".$dbp->f($dbpt->f("parameter_name"))."\" size=\"20\" />";
	    				}
	    		}
	
	    		if ($dbpt->f("parameter_description")) {
    				$html .= "&nbsp;";
    				$html .= vmToolTip($dbpt->f("parameter_description"));
	    		}
	    		$html .= " ".$dbpt->f("parameter_unit");
	    		if ($dbpt->f("parameter_default")) {
	    			$html .= " (".JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_DEFAULT').": ";
	    			$html .= $dbpt->f("parameter_default").")";
	    		}
	    		$html .= " [ ".JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE').": ";
	    		switch( $dbpt->f("parameter_type") ) {
	    			case "I": $html .= JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_INTEGER'); break;	// Integer
	    			case "T": $html .= JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_TEXT'); break; 	// Text
	    			case "S": $html .= JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_SHORTTEXT'); break; // Short Text
	    			case "F": $html .= JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_FLOAT'); break; 	// Float
	    			case "C": $html .= JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_CHAR'); break; 	// Char
	    			case "D": 
	    				$html .= JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_DATETIME')." ";	// Date & Time
	    				$html .= JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_DATE_FORMAT')." ";
	    				$html .= JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_TIME_FORMAT');
	    			break;
	    			case "A": 
    					$html .= JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_DATE')." ";		// Date
	    				$html .= JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_DATE_FORMAT');
	    			break;
	    			case "M": 
    					$html .= JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_TIME')." ";		// Time
	    				$html .= JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_TIME_FORMAT');
	    			break;
	    			case "V": 
	    				$html .= JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_MULTIVALUE'); break; 	// Multiple Value
	    		}
	    		$html .= " ] ";
	    	}
	    	else {
	    		$html .= "<tr>\n  <td colspan=\"2\" height=\"2\" ><hr/>";
	    	}
	    	$html .= "  </td>\n</tr>";
	    }
	    $html .= '</table>';
    	return $html;
	}
	/**
	 * Returns the parameter list for form (hiden items)
	 * @author Zdenek Dvorak
	 *
	 * @param int $product_type_id
	 * @return string
	 */
	function get_parameter_form($product_type_id='0') {
		$db = new ps_DB;
		$q  = "SELECT * FROM #__{vm}_product_type_parameter ";
		$q .= "WHERE product_type_id='$product_type_id'";
		$db->query($q);

		$html = "";
		while ($db->next_record()) {
			if ($db->f("parameter_type")!="B") { // not Break line
				$item_name = "product_type_$product_type_id"."_".$db->f("parameter_name");
				if ($db->f("parameter_multiselect")=="Y" && $db->f("parameter_values")) { // Multiple section List of values
					$get_item_value = JRequest::getVar( $item_name, array());
					foreach($get_item_value as $value) {
						$html .= "<input type=\"hidden\" id=\"$value\" name=\"".$item_name."[]\"  value=\"".$value."\" />\n";
					}
					$html .= "<input type=\"hidden\" name=\"".$item_name."_comp\"  value=\"";
					$html .= JRequest::getVar( $item_name."_comp", "")."\" />\n";
				}
				else {
					$html .= "<input type=\"hidden\" name=\"".$item_name."\"  value=\"";
					$html .= JRequest::getVar( $item_name, "");
					$html .= "\" />\n";
					// comparison
					$html .= "<input type=\"hidden\" name=\"".$item_name."_comp\"  value=\"";
					$html .= JRequest::getVar( $item_name."_comp", "");
					$html .= "\" />\n";
				}
			}
		}
		// item for price search
		$html .= "<input type=\"hidden\" name=\"price\" value=\"".JRequest::getVar("price", "")."\" />\n";
		$html .= "<input type=\"hidden\" name=\"price_comp\" value=\"".JRequest::getVar("price_comp", "")."\" />\n";

		return $html;
	}

	/**
	 * Returns html code for show parameters in select ORDER BY
	 * @author Zdenek Dvorak
	 *
	 * @param int $product_type_id
	 * @param string $orderby
	 */
	function get_parameter_order_list($product_type_id,$orderby="") {
		$db = new ps_DB;
		$q  = "SELECT * FROM #__{vm}_product_type_parameter ";
		$q .= "WHERE product_type_id=$product_type_id ";
		$q .= "AND parameter_type<>'B' "; // NO Break Line
		$q .= "ORDER BY parameter_list_order";
		$db->query($q);
		while ($db->next_record()) {
			$value = "pshop_product_type_".$product_type_id.".".$db->f("parameter_name");
			echo "<option value=\"$value\" ";
			if ($orderby == $value) echo "selected ";
			echo ">".$db->f("parameter_label")."</option>\n";
		}
	}

	/**
	 * Returns true if the product is in a Product Type
	 * @author Zdenek Dvorak
	 *
	 * @param int $product_id
	 * @return boolean
	 */
	function product_in_product_type($product_id) {
		$db = new ps_DB;
		$q = "SELECT * FROM #__{vm}_product_product_type_xref WHERE product_id='$product_id'";
		$db->query($q);
		return $db->num_rows() > 0;
	}

	/**
	 * Returns html code for show parameters
	 * @author Zdenek Dvorak
	 *
	 * @param int $product_id
	 * @return string
	 */
	function list_product_type($product_id) {
		
		// $dbag = product_types;
		$dbag = new ps_DB;
		// $dba = Attributes of product_type param, holds product_id and values assign to each param;
		$dba = new ps_DB;
		// $dbp = Parameters of product_type, holds definitions of each parameter, but not value ;
		$dbp = new ps_DB;
		$html ="";
		$product_type = array();

        // Build array of all product types and clear them. This is required so that products
        // without product types have their tags cleared from the flypage.
        $q = "SELECT * FROM #__{vm}_product_type ";
        $dbag->query( $q );
		$q  = "SELECT * FROM #__{vm}_product_type_parameter ";
        $q .= "WHERE product_type_id=";
        while ($dbag->next_record()) {
        	$product_type_name = str_replace(" ","_",$dbag->f("product_type_name")); 
            if ($dbag->f("product_type_flypage")) {
				$flypage_file = PAGEPATH."templates/".$dbag->f("product_type_flypage").".php";
				if (file_exists($flypage_file)) {
                    $product_type[$product_type_name] = "";
				}
			}
            $q2  = "SELECT * FROM #__{vm}_product_type_".$dbag->f("product_type_id")." ";
			$dbp->query($q2);
			$dba->query($q.$dbag->f("product_type_id")." ORDER BY parameter_list_order");
			while ($dba->next_record()) {				
                $product_type[$product_type_name."_".$dba->f("parameter_name")."_label"] = "";
                $product_type[$product_type_name."_".$dba->f("parameter_name")."_desc"] = "";
                $product_type[$product_type_name."_".$dba->f("parameter_name")] = "";
            }
        }
        if (!$this->product_in_product_type($product_id)) {
			return $product_type;
		}
        // End clear
        
		$q  = "SELECT * FROM #__{vm}_product_product_type_xref ";
		$q .= "LEFT JOIN #__{vm}_product_type USING (product_type_id) ";
		$q .= "WHERE product_id='$product_id' AND published='1' ";
		$q .= "ORDER BY product_type_list_order";
		$dbag->query( $q );
		$q  = "SELECT * FROM #__{vm}_product_type_parameter ";
		$q .= "WHERE product_type_id=";
		$product_type = array();
		while ($dbag->next_record()) { // Show all Product Type
			$product_type_name = str_replace(" ","_",$dbag->f("product_type_name"));
			// SELECT parameter value of product
			$q2  = "SELECT * FROM #__{vm}_product_type_".$dbag->f("product_type_id");
			$q2 .= " WHERE product_id='$product_id'";
			$dbp->query($q2);
			// SELECT parameter of Product Type
			$dba->query($q.$dbag->f("product_type_id")." ORDER BY parameter_list_order");
			$parameter_count = 0;
			$custom_parameters = array();
			while ($dba->next_record()) {
				$parameter_name = $dba->f("parameter_name");
				$parameter_description = $dba->f("parameter_description");
				$product_type[$product_type_name."_".$parameter_name."_label"] = $dba->f("parameter_label");
				$product_type[$product_type_name."_".$parameter_name."_desc"] = $parameter_description;
                $product_type[$product_type_name."_".$parameter_name."_tooltip"] = $html;
                $product_type[$product_type_name."_".$parameter_name] = $dbp->f($dba->f("parameter_name"))." ".$dba->f("parameter_unit");
                // Modification to build custom array for custom flypge use. removes queries from custom templates
                if ($dbag->f("product_type_flypage")) {
					$flypage_file = VM_THEMEPATH."templates/".$dbag->f("product_type_flypage").".php";
					if (file_exists($flypage_file)) {
						$custom_parameters[$parameter_count]["parameter_value"] =	$dbp->f($dba->f("parameter_name"));
						$custom_parameters[$parameter_count]["parameter_unit"] =	$dba->f("parameter_unit");
						$custom_parameters[$parameter_count]["parameter_description"] = $parameter_description;
						
						$custom_parameters[$parameter_count]["parameter_label"] =	$dba->f("parameter_label");
						$custom_parameters[$parameter_count]["parameter_name"] =	$dba->f("parameter_name");
						$parameter_count++;	
					}
                }
			}
			// See if we have a flypage and if so include the file for custom display
			if ($dbag->f("product_type_flypage")) {
				$flypage_file = VM_THEMEPATH."templates/".$dbag->f("product_type_flypage").".php";
				if (file_exists($flypage_file)) {
					$product_type[$product_type_name] = include($flypage_file);
				}
			}
		}
		return $product_type;
}
}

?>
