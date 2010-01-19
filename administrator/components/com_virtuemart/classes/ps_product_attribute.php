<?php
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
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
 * The class is is used to manage the product attributes.
 *
 */
class ps_product_attribute {
	
	/**
	 * Validates that all variables for adding, updating an attribute
	 * are correct
	 *
	 * @param array $d
	 * @return boolean True when successful, false when not
	 */
	function validate( &$d ) {
		global $vmLogger;
		$valid = true ;
		if( $d["attribute_name"] == "" ) {
			$vmLogger->err( JText::_( 'VM_PRODUCT_ATTRIBUTE_ERR_ATTRNAME' ) ) ;
			$valid = false ;
		} elseif( $d["old_attribute_name"] != $d["attribute_name"] ) {
			$db = new ps_DB( ) ;
			$q = "SELECT attribute_name FROM #__{vm}_product_attribute_sku " ;
			$q .= "WHERE attribute_name = '" . $db->getEscaped( $d["attribute_name"] ) . "'" ;
			$q .= "AND product_id = '" . (int)$d["product_id"] . "'" ;
			$db->query( $q ) ;
			if( $db->next_record() ) {
				$vmLogger->err( JText::_( 'VM_PRODUCT_ATTRIBUTE_ERR_ATTRUNIQ' ) ) ;
				$valid = false ;
			}
		}
		return $valid ;
	}
	
	/**
	 * Validates all variables for deleting an attribute
	 *
	 * @param array $d
	 * @return boolean True when successful, false when not
	 */
	function validate_delete( &$d ) {
		global $vmLogger ;
		require_once (CLASSPATH . 'ps_product.php') ;
		
		$ps_product = new ps_product( ) ;
		
		$db = new ps_DB( ) ;
		$q = 'SELECT product_id FROM #__{vm}_product_attribute_sku WHERE product_id = ' . (int)$d["product_id"] ;
		$db->query( $q ) ;
		if( $db->num_rows() == 1 && $ps_product->parent_has_children( $d["product_id"] ) ) {
			$vmLogger->err( JText::_( 'VM_PRODUCT_ATTRIBUTE_ERR_DELITEMS' ) ) ;
			return false ;
		}
		
		return true ;
	
	}
	/**
	 * Adds an attribute record
	 *
	 * @param array $d
	 * @return boolean True when successful, false when not
	 */
	function add( &$d ) {
		
		if( ! $this->validate( $d ) ) {
			return false ;
		}
		
		$db = new ps_DB( ) ;
		$fields = array( 'product_id' => $d["product_id"] , 'attribute_name' => $d["attribute_name"] , 'attribute_list' => $d["attribute_list"] ) ;
		$db->buildQuery( 'INSERT', '#__{vm}_product_attribute_sku', $fields ) ;
		if( $db->query() === false ) {
			$GLOBALS['vmLogger']->err( JText::_( 'VM_PRODUCT_ATTRIBUTE_ERR_SAVING' ) ) ;
			return false ;
		}
		
		/** Insert new Attribute Name into child table **/
		$ps_product = new ps_product( ) ;
		$child_pid = $ps_product->get_child_product_ids( $d["product_id"] ) ;
		
		for( $i = 0 ; $i < count( $child_pid ) ; $i ++ ) {
			$fields = array( 'product_id' => $child_pid[$i] , 'attribute_name' => $d["attribute_name"] ) ;
			$db->buildQuery( 'INSERT', '#__{vm}_product_attribute', $fields ) ;
			$db->query() ;
		}
		$GLOBALS['vmLogger']->info( JText::_( 'VM_PRODUCT_ATTRIBUTE_SAVED' ) ) ;
		return true ;
	}
	
	/**
	 * Updates an attribute record
	 *
	 * @param array $d
	 * @return boolean True when successful, false when not
	 */
	function update( &$d ) {
		
		if( ! $this->validate( $d ) ) {
			return false ;
		}
		
		$db = new ps_DB( ) ;
		
		$fields = array( 'attribute_name' => $d["attribute_name"] , 'attribute_list' => $d["attribute_list"] ) ;
		$db->buildQuery( 'UPDATE', '#__{vm}_product_attribute_sku', $fields, "WHERE product_id='" . (int)$d["product_id"] . "' AND attribute_name='" . $db->getEscaped( $d["old_attribute_name"] ) . "'" ) ;
		if( $db->query() === false ) {
			$GLOBALS['vmLogger']->err( JText::_( 'VM_PRODUCT_ATTRIBUTE_ERR_UPDATING' ) ) ;
			return false ;
		}
		
		if( $d["old_attribute_name"] != $d["attribute_name"] ) {
			$ps_product = new ps_product( ) ;
			$child_pid = $ps_product->get_child_product_ids( $d["product_id"] ) ;
			
			for( $i = 0 ; $i < count( $child_pid ) ; $i ++ ) {
				$fields = array( 'attribute_name' => $d["attribute_name"] ) ;
				$db->buildQuery( 'UPDATE', '#__{vm}_product_attribute', $fields, "WHERE product_id='" . $child_pid[$i] . "' AND attribute_name='" . $db->getEscaped( $d["old_attribute_name"] ) . "' " ) ;
				$db->query() ;
			}
		}
		$GLOBALS['vmLogger']->info( JText::_( 'VM_PRODUCT_ATTRIBUTE_UPDATED' ) ) ;
		return true ;
	}
	
	/**
	 * Controller for Deleting Records.
	 */
	function delete( &$d ) {
		
		$record_id = $d["attribute_name"] ;
		
		if( is_array( $record_id ) ) {
			foreach( $record_id as $record ) {
				if( ! $this->delete_record( $record, $d ) )
					return false ;
			}
			return true ;
		} else {
			return $this->delete_record( $record_id, $d ) ;
		}
	}
	/**
	 * Deletes one Record.
	 */
	function delete_record( $record_id, &$d ) {
		global $db ;
		
		if( ! $this->validate_delete( $d ) ) {
			return false ;
		}
		
		$q = "DELETE FROM #__{vm}_product_attribute_sku " ;
		$q .= 'WHERE product_id = ' . (int)$d["product_id"] . ' ' ;
		$q .= "AND attribute_name = '" . $db->getEscaped( $record_id ) . "'" ;
		
		$db->query( $q ) ;
		$ps_product = new ps_product( ) ;
		$child_pid = $ps_product->get_child_product_ids( $d["product_id"] ) ;
		
		for( $i = 0 ; $i < count( $child_pid ) ; $i ++ ) {
			$q = "DELETE FROM #__{vm}_product_attribute " ;
			$q .= "WHERE product_id = '$child_pid[$i]' " ;
			$q .= "AND attribute_name = '" . $db->getEscaped( $record_id ) . "'" ;
			$db->query( $q ) ;
		}
		return True ;
	}
	/**
	 * Lists all child/sister products of the given product
	 *
	 * @param int $product_id
	 * @return string HTML code with Items, attributes & price
	 */
	function list_attribute( $product_id, $extra_ids = null ) {
		// The default listing method
		$product_list = "N" ;
		$display_use_parent = 'N';
		if(ps_product::get_build_product_id($product_id)) {
			return $this->build_product($product_id);
		} else {	
			$child_options = ps_product::get_child_options( $product_id ) ;
			if( ! empty( $child_options ) ) {
				extract( $child_options ) ;
			}
			$display_type = "";
			$quantity_options = ps_product::get_quantity_options( $product_id ) ;
			if( ! empty( $quantity_options['quantity_box'] ) ) {
			$display_type = $quantity_options['quantity_box'] ;
			}
			$child_option_ids = ps_product::get_field( $product_id, 'child_option_ids' ) ;
			/*if( $child_option_ids != '' && $product_list == "N" ) {
				$product_list = "Y" ;
			}*/
			
			if( $extra_ids ) {
				$child_option_ids .= $child_option_ids ? "," . $extra_ids : $extra_ids ;
			}
		
			if( empty( $class_suffix ) ) {
				$class_suffix = "" ;
			}
		
			switch( $product_list) {
				case "Y" :
					return $this->list_attribute_list( $product_id, $display_use_parent, $product_list_child, $display_type, $class_suffix, $child_option_ids, $dw, $aw, $display_header, $product_list_type, $product_list,$child_order_by ) ;
					break ;
				case "YM" :
					return $this->list_attribute_list( $product_id, $display_use_parent, $product_list_child, $display_type, $class_suffix, $child_option_ids, $dw, $aw, $display_header, $product_list_type, $product_list, $child_order_by ) ;
					break ;
				case "N" :
				default :
					return $this->list_attribute_list( $product_id, $display_use_parent, $product_list_child, $display_type, $class_suffix, $child_option_ids, $dw, $aw, $display_header, $product_list_type, $product_list, $child_order_by ) ;
					break ;
			}
		}
	}
	/**
	 * Lists all sub products of the given product for a build product
	 *
	 * @param int $product_id
	 * @return string HTML code with Items, attributes & price
	 */

	function build_product($product_id) {
		$tpl = vmTemplate::getInstance();
		$db_prod = new ps_DB();
		$db_cat = new ps_DB();
		$html = "";
		$build_id = ps_product::get_build_product_id($product_id);
		// SQL to get list of build categories
		$qc = "SELECT * FROM `#__{vm}_product_build_category` WHERE build_parent_id =`$product_id` ORDER BY `build_category_order";
		$db_cat->query($qc);
		while($db_cat->next_record()) {
			//output category description
			$tpl->set('description',$db_cat->f("build_category_description"));
			$html .= $tpl->fetch('product_details/includes/addtocart_build_category.tpl.php');
			//SQL to get products for this category
			$qp = "SELECT * FROM `#__{vm}_product_build_product`";
			$db_prod->query($qp);
			while($db_prod->next_record()) {
				
			}
			
		}
		return array( $html , 'build_product' ) ;
	}
	
	/**
	 * Lists all child/sister products of the given product
	 *
	 * @param int $product_id
	 * @return string HTML code with Items, attributes & price
	 */
	
	function list_attribute_list( $product_id, $display_use_parent, $child_link, $display_type, $cls_sfuffix, $child_ids, $dw, $aw, $display_header, $product_list_type, $product_list, $child_order_by ) {
		global $CURRENCY_DISPLAY, $mm_action_url ;
		require_once (CLASSPATH . 'ps_product.php') ;
		$ps_product = new ps_product( ) ;
		require_once (CLASSPATH . 'ps_product_type.php') ;
		$ps_product_type = new ps_product_type( ) ;
		
		$Itemid = JRequest::getVar( 'Itemid', "" ) ;
		$category_id = JRequest::getVar( 'category_id', "" ) ;
		$curr_product = JRequest::getVar( 'product_id', "" ) ;
		$db = new ps_DB( ) ;
		$db_sku = new ps_DB( ) ;
		$db_item = new ps_DB( ) ;
		$tpl = vmTemplate::getInstance();
		
		$price = $ps_product->get_adjusted_attribute_price($product_id);
		
		$tpl->set( "cls_suffix", $cls_sfuffix ) ;
		$tpl->set( "product_id", $product_id ) ;
		$tpl->set( "display_header", $display_header ) ;
		$tpl->set( "display_product_type", $product_list_type ) ;
		$tpl->set( "product_price", $price['product_price'] ) ;
		$html = '';
		// Get list of children
		$pp = $ps_product->parent_has_children( $product_id ) ;
		//SELECT `jos_vm_product`.`product_id`,product_name,product_parent_id,product_sku,product_in_stock,product_full_image,product_thumb_image FROM jos_vm_product LEFT JOIN `jos_vm_product_price` 
		//ON `jos_vm_product`.`product_id` = `jos_vm_product_price`.`product_id` WHERE product_publish='Y' AND product_parent_id='17' ORDER BY product_price
		
		if(isset($child_order_by)){
			$child_order_by_query = 'ORDER BY `'.$child_order_by.'`' ;
		}
		
		$q = "SELECT DISTINCT * FROM (";
		if( $pp ) {
			$q .= '(SELECT  `#__{vm}_product`.`product_id`,`product_name`,`product_parent_id`,`product_sku`,`product_in_stock`,`low_stock_notification`,`product_full_image`,`product_thumb_image` FROM `#__{vm}_product`  ' ;
			$q .= ' WHERE `product_publish`="Y" AND `product_parent_id`="'.$product_id.'" '.$child_order_by_query.' )';
			
		} else {
			$q .= "(SELECT `product_id`,`product_name`,`product_parent_id`,`product_sku`,`product_in_stock`,`low_stock_notification`,`product_full_image`,`product_thumb_image` FROM #__{vm}_product WHERE `product_publish`='Y' AND `product_id`='$product_id' )" ;
		}
		$has_extra = false;
		if( $child_ids ) {
			$ids = explode( ",", $child_ids ) ;
			$child_array = array( ) ;
			$parent_array = array( ) ;
			foreach( $ids as $extra_id ) {
				if( $ps_product->parent_has_children( $extra_id ) ) {
					$parent_array[] = $extra_id ;
				} else {
					$child_array[] = $extra_id ;
				}
			}
			$parent_ids = implode( ',', $parent_array ) ;
			$child_ids = implode( ',', $child_array ) ;
			$has_extra = true;
			if( $child_ids ) {
				$q .= " UNION ALL (SELECT product_id,product_name,product_parent_id,product_sku,product_in_stock,low_stock_notification,product_full_image,product_thumb_image FROM #__{vm}_product WHERE product_publish='Y' AND  product_id IN ($child_ids) )" ;
				//$q .= 'LEFT JOIN `#__{vm}_product_price` ON `#__{vm}_product`.`product_id` = `#__{vm}_product_price`.`product_id`';
			}
			if( $parent_ids ) {
				$q .= " UNION ALL (SELECT  `#__{vm}_product`.`product_id`,product_name,product_parent_id,product_sku,product_in_stock,low_stock_notification,product_full_image,product_thumb_image FROM #__{vm}_product " ;
				$q .= " WHERE product_publish='Y' AND  product_parent_id IN ($parent_ids) ".$child_order_by_query." )";
			}
		}
		$q .=  ") AS products";
		$db->query($q);
		if( $pp ) {
			$master_id = $product_id ;
		} else {
			$master_id = $db->f( "product_id" ) ;
		}
		$main_master = $master_id ;
		$master_child_count = 0 ;
		if( $db->num_rows() < 1 ) {
			// Try to Get list of sisters & brothers
			$q = "SELECT product_parent_id FROM #__{vm}_product WHERE product_id='$product_id'" ;
			$db->setQuery( $q ) ;
			$db->query() ;
			$child_id = $product_id ;
			$product_id = $db->f( "product_parent_id" ) ? $db->f( "product_parent_id" ) : $product_id ;
			$parent_id = $db->f( "product_parent_id" ) ;
			$q = "SELECT product_id,product_name,product_parent_id,product_sku,product_in_stock,low_stock_notification,,product_full_image,product_thumb_image FROM #__{vm}_product WHERE product_parent_id='" . $db->f( "product_parent_id" ) . "' AND product_parent_id<>0 AND product_publish='Y'" ;
			$db->query($q) ;
		}
		if( ($db->num_rows() > 0) ) {
			$products = array( ) ;
			$headings = array( ) ;
			$i = 0 ;
			$attrib_heading = array( ) ;
			$ci = 0 ;
			$ac = 0;
			while( $db->next_record() ) {
				$parent_id = $db->f( "product_parent_id" ) ;
				if( ($db->f( "product_id" ) != $curr_product) && @$child_id ) {
					continue ;
				}
				// Start row for this child
				$q = "SELECT product_id, attribute_name FROM #__{vm}_product_attribute_sku " ;
				$q .= "WHERE product_id='" . $db->f( "product_parent_id" ) . "' ORDER BY attribute_list ASC" ;
				$db_sku->query( $q ) ;
				$attrib_value = array( ) ;
				if(!$db_sku->next_record() && $ac != 0) {
					for($i=0;$i<=$ac;$i++) {
						$attrib_value[] = "";
					}
				}
				else {
					$db_sku->reset();
				}
				
				while( $db_sku->next_record() ) {
					$q = "SELECT attribute_name,attribute_value " ;
					$q .= "FROM #__{vm}_product_attribute WHERE " ;
					$q .= "product_id='" . $db->f( "product_id" ) . "' AND " ;
					$q .= "attribute_name='" . $db_sku->f( "attribute_name" ) . "'" ;
					$db_item->setQuery( $q ) ;
					$db_item->query() ;
					while( $db_item->next_record() ) {
						if( $ci == 0 ) {
							$attrib_heading[] = $db_item->f( "attribute_name" ) ;
							$tpl->set( 'headings', $attrib_heading ) ;
						}
						$attrib_value[] = $db_item->f( "attribute_value" ) ;
						$ac++;
					}
				}
				if( $main_master == $parent_id )
					$master_child_count ++ ;
				$tpl->set( 'desc_width', $dw ) ;
				$tpl->set( 'attrib_width', $aw ) ;
				// End show Header Row
				if( $ci % 2 ) {
					$bgcolor = "vmRowOne" ;
				} else {
					$bgcolor = "vmRowTwo" ;
				}
				$products[$ci]['bgcolor'] = $bgcolor ;
				
				$products[$ci]['product_id'] = $db->f( "product_id" ) ;
				$products[$ci]["category_id"] = $category_id ;
				
				$products[$ci]["Itemid"] = $Itemid ;
				// If this is a child of a parent set the correct product_id for page return
				if( (@$child_id || $has_extra) && $pp ) {
					$products[$ci]['parent_id'] = $main_master ;
				} else {
					//$master_id = $parent_id ;
					$products[$ci]['parent_id'] = $parent_id ;
				}
				
				$flypage = $ps_product->get_flypage( $products[$ci]['parent_id'] ) ;
				$products[$ci]["flypage"] = $flypage ;
				// Images
				// If it is item get parent:
				$product_parent_id = $db->f( "product_parent_id" ) ;
				if( $product_parent_id != 0 ) {
					$dbp = new PS_db( ) ;
					$dbp->query( "SELECT product_full_image,product_thumb_image,product_name,product_s_desc FROM #__{vm}_product WHERE product_id='$product_parent_id'" ) ;
					$dbp->next_record() ;
				}
				$product_full_image = $parent_id != 0 && ! $db->f( "product_full_image" ) ? $dbp->f( "product_full_image" ) : $db->f( "product_full_image" ) ; // Change
				$product_thumb_image = $parent_id != 0 && ! $db->f( "product_thumb_image" ) ? $dbp->f( "product_thumb_image" ) : $db->f( "product_thumb_image" ) ; // Change
				$productData = $db->get_row() ;
				$productArray = get_object_vars( $productData ) ;
				$productArray["product_id"] = $db->f( "product_id" ) ;
				$productArray["product_full_image"] = $product_full_image ; // to display the full image on flypage
				$productArray["product_thumb_image"] = $product_thumb_image ;
				
				$tpl->set( 'productArray', $productArray ) ;
				foreach( $productArray as $property => $value ) {
					$tpl->set( $property, $value ) ;
				}
				// Assemble the thumbnail image as a link to the full image
				// This function is defined in the theme (theme.php)
				$product_image = $tpl->vmBuildFullImageLink( $productArray ) ;
				$products[$ci]['product_image'] = $product_image ;
				//Product Description
				$link = "" ;
				if( ($child_link == "Y") && ! @$child_id ) {
					$link = "<input type=\"hidden\" id=\"index_id" . $db->f( "product_id" ) . "\" value=\"" . $db->f( "product_id" ) . "\" />\n" ;
					// If content plugins are enabled, reload the whole page; otherwise, use ajax 
					if( VM_CONTENT_PLUGINS_ENABLE == '1' ) {
						$link .= "<a name=\"" . $db->f( "product_name" ) . $db->f( "product_id" ) . "\"  onclick=\"var id = $('index_id" . $db->f( "product_id" ) . "').value; if(id != '') { document.location = '" . $mm_action_url . "index.php?option=com_virtuemart&page=shop.product_details&flypage=$flypage&Itemid=$Itemid&category_id=$category_id&product_id=' + id; }\" >" ;
					} else {
						$link .= "<a name=\"" . $db->f( "product_name" ) . $db->f( "product_id" ) . "\"  onclick=\"var id = $('index_id" . $db->f( "product_id" ) . "').value; if(id != '') { loadNewPage( 'vmMainPage', '" . $mm_action_url . "index2.php?option=com_virtuemart&page=shop.product_details&flypage=$flypage&Itemid=$Itemid&category_id=$category_id&product_id=' + id ); }\" >" ;
					}
					
					$tpl->set( 'child_link', true );
				} else {
					$tpl->set( 'child_link', false );
				}
				
				$html1 = $db->f( "product_name" ) ;
				if( ($child_link == "Y") && ! @$child_id ) {
					$html1 .= "</a>" ;
				}
				$products[$ci]['product_title'] = $link . $html1 ;
				// For each child get attribute values by looping through attribute list
				foreach( $attrib_value as $attribute ) {
					$products[$ci]['attrib_value'][] = $attribute ;
				}
				$products[$ci]['stock_level'] = $ps_product->stockIndicator($db->f("product_in_stock"),$db->f("low_stock_notification"),$db->f("product_id"),"child");
				//Show the quantity Box
				$products[$ci]['quantity_box'] = $this->show_quantity_box( $master_id, $db->f( "product_id" ), $product_list, $display_use_parent ) ;
				
				// Attributes for this item are done.
				// Now get item price
				$price = $ps_product->get_price( $db->f( "product_id" ) ) ;
				$price["product_price"] = $GLOBALS['CURRENCY']->convert( $price["product_price"], $price["product_currency"] ) ;
				$actual_price = $ps_product->get_adjusted_attribute_price( $db->f( "product_id" ) ) ;
				$actual_price["product_price"] = $GLOBALS['CURRENCY']->convert( $actual_price["product_price"], $actual_price["product_currency"] ) ;
				if( $_SESSION["auth"]["show_price_including_tax"] == 1 ) {
					$tax_rate = 1 + $ps_product->get_product_taxrate( $db->f( "product_id" ) ) ;
					$price['product_price'] *= $tax_rate ;
					$actual_price['product_price'] *= $tax_rate ;
				}
				$products[$ci]['price'] = $CURRENCY_DISPLAY->getFullValue( $price["product_price"] ) ;
				$products[$ci]['actual_price'] = $CURRENCY_DISPLAY->getFullValue( $actual_price["product_price"] ) ;
				
				// Ouput Product Type
				if( $db->f( "product_parent_id" ) != $product_id )
					$product_id = $db->f( "product_parent_id" ) ;
				$product_type = "" ;
				if( $product_id != 0 && ! $ps_product_type->product_in_product_type( $db->f( "product_id" ) ) ) {
					$product_type = $ps_product_type->list_product_type( $product_id ) ;
				} else {
					$product_type = $ps_product_type->list_product_type( $db->f( "product_id" ) ) ;
				}
				$products[$ci]['product_type'] = $product_type ;
				
				// Child stock
				if( $display_use_parent == 'Y' && !empty($master_id)) {
					$id = $master_id ;
				} else {
					$id = $db->f( "product_id" );
				}
				$products[$ci]['product_in_stock'] = ps_product::get_field( $db->f( "product_id" ), 'product_in_stock' ) ;
				$products[$ci]['product_sku'] = $db->f("product_sku");
				// Output Advanced Attributes
				$products[$ci]['advanced_attribute'] = $this->list_advanced_attribute( $db->f( "product_id" ) ) ;
				$products[$ci]['custom_attribute'] = $this->list_custom_attribute( $db->f( "product_id" ) ) ;
				$ci ++ ;
			}
			if( $display_type == "radio" ) {
				$list_type = "radio" ;
			} else {
				$list_type = "list" ;
			}
			// Get template and fill
			$tpl->set( 'products', $products ) ;
			$tpl->set('parent_id', $master_id);
			$master_child_count = ($master_child_count == 0) ? 1 : $master_child_count ;
			$tpl->set( 'child_count', $master_child_count ) ;
			if( $product_list == "Y" ) {
				$html = $tpl->fetch( 'product_details/includes/addtocart_list_single.tpl.php' ) ;
			} elseif ($product_list =="YM") {
				$list_type = "multi" ;
				$html = $tpl->fetch( 'product_details/includes/addtocart_list_multi.tpl.php' ) ;
			} elseif (($product_list == "N" || $product_list == "") && $ci == 1) {
				$html = $tpl->fetch('product_details/includes/addtocart_normal.tpl.php') ;
				$list_type = "drop" ;
			} else {
				$list_type = "drop";
				$html = $tpl->fetch( 'product_details/includes/addtocart_drop.tpl.php' ) ;
			}
		} else {
			print "here";
			$tpl->set('product_id', $product_id) ;
			// This function lists the "advanced" simple attributes
			$tpl->set('advanced_attribute', $this->list_advanced_attribute( $product_id )) ;
			// This function lists the "custom" simple attributes
			$tpl->set('custom_attribute', $this->list_custom_attribute( $product_id )) ;
			$html = $tpl->fetch('product_details/includes/addtocart_normal.tpl.php') ;
			$list_type = "drop" ;
		}
		
		return array( $html , $list_type ) ;
	}
	
	/**
	 * Creates drop-down boxes from advanced attribute format.
	 * @author Sean Tobin (byrdhuntr@hotmail.com)
	 * @param int $product_id
	 * @return string HTML code containing Drop Down Lists with Labels
	 */
	function list_advanced_attribute( $product_id, $prod_id = null ) {
		global $CURRENCY_DISPLAY ;
		$ps_product = new ps_product( ) ;
		$db = new ps_DB( ) ;
		$auth = $_SESSION['auth'] ;
		$tpl = new $GLOBALS['VM_THEMECLASS']( ) ;
		if($product_id == 0)
			$product_id = $prod_id;	
		$q = "SELECT product_id, attribute, product_parent_id FROM #__{vm}_product WHERE product_id='$product_id'";
		$db->query($q);
		$db->next_record();
		if(!$db->f("attribute")) {
			$parent_id = $db->f( "product_parent_id" ) ? $db->f( "product_parent_id" ) : $product_id ;
			$q = "SELECT product_id, attribute FROM #__{vm}_product WHERE product_id='$parent_id'";
			$db->query($q);
			$db->next_record();
		}
		$productPrice = $ps_product->get_price( $product_id ) ;
		
		$advanced_attribute_list = $db->f( "attribute" ) ;
		if( $advanced_attribute_list ) {
			$has_advanced_attributes = 1 ;
			$fields = explode( ";", $advanced_attribute_list ) ;
			
			$attributes = array( ) ;
			$i = 0 ;
			foreach( $fields as $field ) {
				$base = explode( ",", $field ) ;
				$title = array_shift( $base ) ;
				$titlevar = str_replace( " ", "_", $title ) ;
				$prod_index = $product_id ;
				if( $prod_id ) {
					$prod_index = $prod_id ;
				}
				$attributes[$i]['product_id'] = $prod_index ;
				$attributes[$i]['title'] = $title ;
				$attributes[$i]['titlevar'] = $titlevar ;
				$options_list = array( ) ;
				foreach( $base as $base_value ) {
					$options_item = array( ) ;
					// the Option Text
					$attribtxt = substr( $base_value, 0, strrpos( $base_value, '[' ) ) ;
					if( $attribtxt != "" ) {
						$vorzeichen = substr( $base_value, strrpos( $base_value, '[' ) + 1, 1 ) ; // negative, equal or positive?
						if( $_SESSION["auth"]["show_price_including_tax"] == 1 ) {
							$price = floatval( substr( $base_value, strrpos( $base_value, '[' ) + 2 ) ) * (1 + @$_SESSION['product_sess'][$product_id]['tax_rate']) ; // calculate Tax
						} else {
							$price = floatval( substr( $base_value, strrpos( $base_value, '[' ) + 2 ) ) ;
						}
						// Apply shopper group discount
						$price *= 1 - ($auth["shopper_group_discount"] / 100) ;
						$price = $GLOBALS['CURRENCY']->convert( $price, $productPrice['product_currency'] ) ;
						if( $price == "0" ) {
							$attribut_hint = "test" ;
						}
						$base_var = str_replace( " ", "_", $base_value ) ;
						$base_var = substr( $base_var, 0, strrpos( $base_var, '[' ) ) ;
						
						$options_item['base_var'] = $base_var ;
						$options_item['base_value'] = $attribtxt ;
						
						if( $_SESSION['auth']['show_prices'] && _SHOW_PRICES ) {
							$options_item['sign'] = $vorzeichen ;
							$options_item['display_price'] = $CURRENCY_DISPLAY->getFullValue( $price ) ;
						}
					
					} else {
						$base_var = str_replace( " ", "_", $base_value ) ;
						$options_item['base_var'] = $base_var ;
						$options_item['base_value'] = $base_value ;
					}
					$options_list[] = $options_item ;
				}
				
				$attributes[$i]['options_list'] = $options_list ;
				$i ++ ;
			}
		}
		
		if( $advanced_attribute_list ) {
			$tpl->set( 'attributes', $attributes ) ;
			return $tpl->fetch( 'product_details/includes/addtocart_advanced_attribute.tpl.php' ) ;
		}
	}
	
	/**
	 * Creates textfields for customizable products from the custom attribute format
	 * @author Denie van Kleef (denievk@in2sports)
	 * @param unknown_type $product_id
	 * @return unknown
	 */
	function list_custom_attribute( $product_id, $prod_id = null ) {
		global $mosConfig_secret ;
		$db = new ps_DB( ) ;
		$tpl = new $GLOBALS['VM_THEMECLASS']( ) ;
		if($product_id == 0)
			$product_id = $prod_id;	
		$q = "SELECT product_id, custom_attribute, product_parent_id FROM #__{vm}_product WHERE product_id='$product_id'";
		$db->query($q);
		$db->next_record();
		if(!$db->f("custom_attribute")) {
			$parent_id = $db->f( "product_parent_id" ) ? $db->f( "product_parent_id" ) : $product_id ;
			$q = "SELECT product_id, custom_attribute FROM #__{vm}_product WHERE product_id='$parent_id'";
			$db->query($q);
			$db->next_record();
		}
		
		$custom_attr_list = $db->f( "custom_attribute" ) ;
		if( $custom_attr_list ) {
			$has_custom_attributes = 1 ;
			$fields = explode( ";", $custom_attr_list ) ;
			$html = "" ;
			$prod_index = $product_id ;
			if( $prod_id ) {
				$prod_index = $prod_id ;
			}
			$attributes = array( ) ;
			$i = 0 ;
			foreach( $fields as $field ) {
				$titlevar = str_replace( " ", "_", $field ) ;
				$title = ucfirst( $field ) ;
				$attributes[$i]['product_id'] = $prod_index ;
				$attributes[$i]['title'] = $title ;
				$attributes[$i]['titlevar'] = $titlevar ;
				$i ++ ;
			}
		}
		
		if( $custom_attr_list ) {
			$tpl->set( 'attributes', $attributes ) ;
			$tpl->set( 'mosConfig_secret', $mosConfig_secret ) ;
			return $tpl->fetch( 'product_details/includes/addtocart_custom_attribute.tpl.php' ) ;
		}
	}
	/**
	 * This function returns an array with all "advanced" attributes of the product specified by
	 * $product_id
	 *
	 * @param int $product_id
	 */
	function getAdvancedAttributes( $product_id, $base_price_only = false ) {
		global $ps_product, $auth ;
		if( is_null( $ps_product ) ) {
			$ps_product = new ps_product( ) ;
		}
		$attributes_array = array( ) ;
		$attributes = $ps_product->get_field( $product_id, 'attribute' ) ;
		if( ! $attributes ) {
			$db = new ps_DB( ) ;
			//get parent_id and try again
			$q = "SELECT product_parent_id FROM #__{vm}_product WHERE product_id=$product_id" ;
			$db->query( $q ) ;
			$db->next_record() ;
			$attributes = $ps_product->get_field( $db->f( "product_parent_id" ), 'attribute' ) ;
		}
		// Get each of the attributes into an array
		$product_attribute_keys = explode( ";", $attributes ) ;
		foreach( $product_attribute_keys as $attribute ) {
			$attribute_name = substr( $attribute, 0, strpos( $attribute, "," ) ) ;
			$attribute_values = substr( $attribute, strpos( $attribute, "," ) + 1 ) ;
			$attributes_array[$attribute_name]['name'] = $attribute_name ;
			// Read the different attribute values into an array
			$attribute_values = explode( ',', $attribute_values ) ;
			$operand = '' ;
			$my_mod = 0 ;
			foreach( $attribute_values as $value ) {
				
				// Get the price modification for this attribute value
				$start = strpos( $value, "[" ) ;
				$finish = strpos( $value, "]", $start ) ;
				
				$o = substr_count( $value, "[" ) ;
				$c = substr_count( $value, "]" ) ;
				// check to see if we have a bracket (means: a price modifier)
				if( True == is_int( $finish ) ) {
					$length = $finish - $start ;
					
					// We found a pair of brackets (price modifier?)
					if( $length > 1 ) {
						$my_mod = substr( $value, $start + 1, $length - 1 ) ;
						//echo "before: ".$my_mod."<br>\n";
						if( $o != $c ) { // skip the tests if we don't have to process the string
							if( $o < $c ) {
								$char = "]" ;
								$offset = $start ;
							} else {
								$char = "[" ;
								$offset = $finish ;
							}
							$s = substr_count( $my_mod, $char ) ;
							for( $r = 1 ; $r < $s ; $r ++ ) {
								$pos = strrpos( $my_mod, $char ) ;
								$my_mod = substr( $my_mod, $pos + 1 ) ;
							}
						}
						$operand = substr( $my_mod, 0, 1 ) ;
						
						$my_mod = substr( $my_mod, 1 ) ;
					
					}
				}
				if( $start > 0 ) {
					$value = substr( $value, 0, $start ) ;
				}
				$attributes_array[$attribute_name]['values'][$value]['name'] = $value ;
				$attributes_array[$attribute_name]['values'][$value]['operand'] = $operand ;
				if( $base_price_only ) {
					$attributes_array[$attribute_name]['values'][$value]['adjustment'] = $my_mod ;
				} else {
					$attributes_array[$attribute_name]['values'][$value]['adjustment'] = $my_mod * (1 - ($auth["shopper_group_discount"] / 100)) ;
				}
				$operand = '' ;
				$my_mod = 0 ;
			}
		
		}
		return $attributes_array ;
	
	}
	/**
	 * This checks if attributes values were chosen by the user
	 * onCartAdd
	 *
	 * @param array $d
	 * @return array $result
	 */
	function cartGetAttributes( &$d ) {
		global $db ;
		
		// added for the advanced attributes modification
		//get listing of titles for attributes (Sean Tobin)
		$attributes = array( ) ;
		if( ! isset( $d["prod_id"] ) ) {
			$d["prod_id"] = $d["product_id"] ;
		}
		$q = "SELECT product_id, attribute, custom_attribute FROM #__{vm}_product WHERE product_id='" . (int)$d["prod_id"] . "'" ;
		$db->query( $q ) ;
		
		$db->next_record() ;
		
		if( ! $db->f( "attribute" ) && ! $db->f( "custom_attribute" ) ) {
			$q = "SELECT product_parent_id FROM #__{vm}_product WHERE product_id='" . (int)$d["prod_id"] . "'" ;
			
			$db->query( $q ) ;
			$db->next_record() ;
			$q = "SELECT product_id, attribute, custom_attribute FROM #__{vm}_product WHERE product_id='" . $db->f( "product_parent_id" ) . "'" ;
			$db->query( $q ) ;
			$db->next_record() ;
		}
		
		$advanced_attribute_list = $db->f( "attribute" ) ;
		if( $advanced_attribute_list ) {
			$fields = explode( ";", $advanced_attribute_list ) ;
			foreach( $fields as $field ) {
				$field = trim( $field ) ;
				$base = explode( ",", $field ) ;
				$title = array_shift( $base ) ;
				array_push( $attributes, $title ) ;
			}
		}
		// We need this for being able to work with attribute names and values which are using non-ASCII characters
		if( strtolower( vmGetCharset() ) != 'utf-8' ) {
			$encodefunc = 'utf8_encode' ;
			$decodefunc = 'utf8_decode' ;
		} else {
			$encodefunc = 'strval' ;
			$decodefunc = 'strval' ;
		}
		
		$description = "" ;
		$attribute_given = false ;
		// Loop through the simple attributes and check if one of the valid values has been provided
		foreach( $attributes as $a ) {
			
			$pagevar = str_replace( " ", "_", $a ) ;
			$pagevar .= $d['prod_id'] ;
			
			$pagevar = $encodefunc( $pagevar ) ;
			
			if( ! empty( $d[$pagevar] ) ) {
				$attribute_given = true ;
			}
			if( $description != '' ) {
				$description .= "; " ;
			}
			
			$description .= $a . ":" ;
			$description .= empty( $d[$pagevar] ) ? '' : $decodefunc( $d[$pagevar] ) ;
		
		}
		rtrim( $description ) ;
		$d["description"] = $description ;
		// end advanced attributes modification addition
		

		$custom_attribute_list = $db->f( "custom_attribute" ) ;
		$custom_attribute_given = false ;
		// Loop through the custom attribute list and check if a value has been provided
		if( $custom_attribute_list ) {
			$fields = explode( ";", $custom_attribute_list ) ;
			
			$description = $d["description"] ;
			
			foreach( $fields as $field ) {
				$pagevar = str_replace( " ", "_", $field ) ;
				$pagevar .= $d['prod_id'] ;
				$pagevar = $encodefunc( $pagevar ) ;
				
				if( ! empty( $d[$pagevar] ) ) {
					$custom_attribute_given = true ;
				}
				if( $description != '' ) {
					$description .= "; " ;
				}
				$description .= $field . ":" ;
				$description .= empty( $d[$pagevar] ) ? '' : $decodefunc( $d[$pagevar] ) ;
			
			}
			rtrim( $description ) ;
			$d["description"] = $description ;
			// END add for custom fields by denie van kleef
		

		}
		
		$result['attribute_given'] = $attribute_given ;
		$result['advanced_attribute_list'] = $advanced_attribute_list ;
		$result['custom_attribute_given'] = $custom_attribute_given ;
		$result['custom_attribute_list'] = $custom_attribute_list ;
		
		return $result ;
	}
	
	/**
	 * Displays the Quantity Box for a Radio-Selector-based add-to-cart form 
	 *
	 * @return string
	 */
	function show_radio_quantity_box() {
		$tpl = vmTemplate::getInstance() ;
		$html = $tpl->fetch( 'product_details/includes/quantity_box_radio.tpl.php' ) ;
		return $html ;
	}
	/**
	 * Creates the Quantity Input Boxes/Radio Buttons/Lists for Products
	 *
	 * @param int $product_id The Parent Product ID
	 * @param int $prod_id The actual Product ID
	 * @param string $child
	 * @param string $use_parent
	 * @return string
	 */
	function show_quantity_box( $product_id, $prod_id, $child = false, $use_parent = 'N' ) {
		$tpl = vmTemplate::getInstance() ;
		
		if( $child == 'Y' ) {
			//We have a child list so get the current quantity;
			$quantity = 0 ;
			for( $i = 0 ; $i < $_SESSION["cart"]["idx"] ; $i ++ ) {
				if( $_SESSION['cart'][$i]["product_id"] == $prod_id ) {
					$quantity = $_SESSION['cart'][$i]["quantity"] ;
				}
			}
		} else {
			$quantity = vmrequest::getInt( 'quantity', 1 ) ;
		}
		// Detremine which style to use
		if( $use_parent == 'Y' && $product_id !=0) {
			$id = $product_id ;
		} else {
			$id = $prod_id ;
		}
		//Get style to use
		$product_in_stock = ps_product::get_field( $id, 'product_in_stock' ) ;
		$quantity_options = ps_product::get_quantity_options( $id ) ;
		extract( $quantity_options ) ;
		
		//Start output of quantity
		//Check for incompatabilities and reset to normal
		if( CHECK_STOCK == '1' && ! $product_in_stock ) {
			$display_type = 'hide' ;
		}
		if( empty( $display_type ) || (@$display_type == "hide" && $child == 'Y') || (@$display_type == "radio" && $child == 'YM') || (@$display_type == "radio" && ! $child) ) {
			$display_type = "none" ;
		}
		unset( $quantity_options['display_type'] ) ;
		
		$tpl->set( 'prod_id', $prod_id ) ;
		$tpl->set( 'quantity', $quantity ) ;
		$tpl->set( 'display_type', $display_type ) ;
		$tpl->set( 'child', $child ) ;
		$tpl->set( 'quantity_options', $quantity_options ) ;
		
		//Determine if label to be used
		$html = $tpl->fetch( 'product_details/includes/quantity_box_general.tpl.php' ) ;
		
		return $html ;
	
	}
	
	function loadAttributeExtension( $attribute_string = false ) {
		
		echo '<input type="hidden" name="js_lbl_title" value="' . JText::_( 'VM_PRODUCT_FORM_TITLE' ) . '" />
		      <input type="hidden" name="js_lbl_property" value="' . JText::_( 'VM_PRODUCT_FORM_PROPERTY' ) . '" />
		      <input type="hidden" name="js_lbl_property_new" value="' . JText::_( 'VM_PRODUCT_FORM_PROPERTY_NEW' ) . '" />
		      <input type="hidden" name="js_lbl_attribute_new" value="' . JText::_( 'VM_PRODUCT_FORM_ATTRIBUTE_NEW' ) . '" />
		      <input type="hidden" name="js_lbl_attribute_delete" value="' . JText::_( 'VM_PRODUCT_FORM_ATTRIBUTE_DELETE' ) . '" />
		      <input type="hidden" name="js_lbl_price" value="' . JText::_( 'VM_CART_PRICE' ) . '" />' ;
		
		if( ! $attribute_string ) {
			// product has no attributes
			?>
<table id="attributeX_table_0" cellpadding="0" cellspacing="0"
	border="0" class="adminform" width="30%">
	<tbody width="30%">
		<tr>
			<td width="5%"><?php
			echo JText::_( 'VM_PRODUCT_FORM_TITLE' ) ;
			?></td>
			<td align="left" colspan="2"><input type="text"
				name="attributeX[0][name]" value="" size="60" /></td>
			<td colspan="3" align="left"><a href="javascript: newAttribute(1)"><?php
			echo JText::_( 'VM_PRODUCT_FORM_ATTRIBUTE_NEW' ) ;
			?></a>
			| <a href="javascript: newProperty(0)"><?php
			echo JText::_( 'VM_PRODUCT_FORM_PROPERTY_NEW' ) ;
			?></a>
			</td>
		</tr>
		<tr id="attributeX_tr_0_0">
			<td width="5%">&nbsp;</td>
			<td width="10%" align="left"><?php
			echo JText::_( 'VM_PRODUCT_FORM_PROPERTY' ) ;
			?></td>
			<td align="left" width="20%"><input type="text"
				name="attributeX[0][value][]" value="" size="40" /></td>
			<td align="left" width="5%"><?php
			echo JText::_( 'VM_PRODUCT_PRICE_TITLE' ) ;
			?></td>
			<td align="left" width="60%"><input type="text"
				name="attributeX[0][price][]" size="10" value="" /></td>
		</tr>
	</tbody>
</table>
<?php
			return ;
		}
		
		// split multiple attributes
		$dropdownlists = explode( ';', $attribute_string ) ;
		
		for( $i = 0, $n = count( $dropdownlists ) ; $i < $n ; $i ++ ) {
			$dropdownlist = $dropdownlists[$i] ;
			$options = explode( ',', $dropdownlist ) ;
			$dropdown_name = $options[0] ;
			
			// display each attribute in the first loop...
			?>
<table id="attributeX_table_<?php
			echo $i ;
			?>" cellpadding="0"
	cellspacing="0" border="0" class="adminform" width="30%">
	<tbody width="30%">
		<tr>
			<td width="5%"><?php
			echo JText::_( 'VM_PRODUCT_FORM_TITLE' ) ;
			?></td>
			<td align="left" colspan="2"><input type="text"
				name="attributeX[<?php
			echo $i ;
			?>][name]"
				value="<?php
			echo $dropdown_name ;
			?>" size="60" /></td>
			<td colspan="3" align="left"><a
				href="javascript:newAttribute(<?php
			echo ($i + 1) ;
			?>)"><?php
			echo JText::_( 'VM_PRODUCT_FORM_ATTRIBUTE_NEW' ) ;
			?></a> | 
			    <?php
			if( $i != 0 ) {
				?><a
				href="javascript:deleteAttribute(<?php
				echo ($i) ;
				?>)"><?php
				echo JText::_( 'VM_PRODUCT_FORM_ATTRIBUTE_DELETE' ) ;
				?></a> | <?php
			}
			?>
			    <a href="javascript:newProperty(<?php
			echo ($i) ;
			?>)"><?php
			echo JText::_( 'VM_PRODUCT_FORM_PROPERTY_NEW' ) ;
			?></a>
			</td>
		</tr>
			  <?php
			// ... and the properties and prices in the second
			for( $i2 = 1, $n2 = count( $options ) ; $i2 < $n2 ; $i2 ++ ) {
				$value = $options[$i2] ;
				
				if( explode( '[', $value ) ) {
					$value_price = explode( '[', $value ) ;
					?>
			  	    <tr id="attributeX_tr_<?php
					echo $i . "_" . $i2 ;
					?>">
			<td width="5%">&nbsp;</td>
			<td width="10%" align="left"><?php
					echo JText::_( 'VM_PRODUCT_FORM_PROPERTY' ) ;
					?></td>
			<td align="left" width="20%"><input type="text"
				name="attributeX[<?php
					echo $i ;
					?>][value][]"
				value="<?php
					echo $value_price[0] ;
					?>" size="40" /></td>
			<td align="left" width="5%"><?php
					echo JText::_( 'VM_CART_PRICE' ) ;
					?></td>
			<td align="left" width="60%"><input type="text"
				name="attributeX[<?php
					echo $i ;
					?>][price][]" size="5"
				value="<?php
					echo str_replace( ']', '', @$value_price[1] ) ;
					?>" /><a
				href="javascript:deleteProperty(<?php
					echo ($i) ;
					?>,'<?php
					echo $i . "_" . $i2 ;
					?>');">X</a></td>
		</tr>
			  	  <?php
				} else {
					?>
			  	  <tr id="attributeX_tr_<?php
					echo $i . "_" . $i2 ;
					?>">
			<td width="5%">&nbsp;</td>
			<td width="10%" align="left"><?php
					echo JText::_( 'VM_PRODUCT_FORM_PROPERTY' ) ;
					?></td>
			<td align="left" width="20%"><input type="text"
				name="attributeX[<?php
					echo $i ;
					?>][value][]"
				value="<?php
					echo $value ;
					?>" size="40" /></td>
			<td align="left" width="5%"><?php
					echo JText::_( 'VM_CART_PRICE' ) ;
					?></td>
			<td align="left" width="60%"><input type="text"
				name="attributeX[<?php
					echo $i ;
					?>][price][]" size="10" /><a
				href="javascript:deleteProperty(<?php
					echo ($i) ;
					?>,'<?php
					echo $i . "_" . $i2 ;
					?>');">X</a></td>
		</tr>
			  	  <?php
				}
			}
			?>
			  </tbody>
</table>
<?php
		}
	}
	
	function formatAttributeX() {
		// request attribute pieces
		$attributeX = JRequest::getVar( 'attributeX', array( 0 ) ) ;
		$attribute_string = '' ;
		
		// no pieces given? then return 
		if( empty( $attributeX ) ) {
			return $attribute_string ;
		}
		
		// put the pieces together again
		foreach( $attributeX as $attributes ) {
			$attribute_string .= ';' ;
			// continue only if the attribute has a name
			if( empty( $attributes['name'] ) ) {
				continue ;
			}
			$attribute_string .= trim( $attributes['name'] ) ;
			$n2 = count( $attributes['value'] ) ;
			for( $i2 = 0 ; $i2 < $n2 ; $i2 ++ ) {
				$value = $attributes['value'][$i2] ;
				$price = $attributes['price'][$i2] ;
				
				if( ! empty( $value ) ) {
					$attribute_string .= ',' . trim( $value ) ;
					
					if( ! empty( $price ) ) {
						
						// add the price only if there is an operand
						if( strstr( $price, '+' ) or (strstr( $price, '-' )) or (strstr( $price, '=' )) ) {
							$attribute_string .= '[' . trim( $price ) . ']' ;
						}
					}
				}
			}
		
		}
		
		// cut off the first attribute separators on the beginning of the string
		// otherwise you would get an empty first attribute
		$attribute_string = substr( $attribute_string, 1 ) ;
		return trim( $attribute_string ) ;
	}
}
?>
