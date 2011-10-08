<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author
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

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
class VirtuemartViewProduct extends JView {

	function display($tpl = null) {

		//$this->loadHelper('customhandler');

		$filter = JRequest::getVar('q', false);
		$type = JRequest::getWord('type', false);
		$id = JRequest::getInt('id', false);
		$row = JRequest::getInt('row', false);
		$product_id = JRequest::getInt('virtuemart_product_id', 0);
		$model = $this->getModel('customfields');
		//$customfield = $model->getcustomfield();
		$db = JFactory::getDBO();
		/* Get the task */
		if ($type=='relatedproducts') {
			$query = "SELECT virtuemart_product_id AS id, CONCAT(product_name, '::', product_sku) AS value
				FROM #__virtuemart_products";
			if ($filter) $query .= " WHERE product_name LIKE '%". $db->getEscaped( $filter, true ) ."%' or product_sku LIKE '%". $db->getEscaped( $filter, true ) ."%' limit 0,50";
				$db->setQuery($query);
				echo json_encode($db->loadObjectList());
				return;
		} else if ($type=='products') {

			$query = 'SELECT * FROM `#__virtuemart_customs` WHERE field_type ="R" ';
			$db->setQuery($query);
			$customs = $db->loadObject();
			$customs->custom_value = $id;
			// $query = "SELECT virtuemart_product_id AS id, CONCAT(product_name, '::', product_sku) AS value
				// FROM #__virtuemart_products WHERE virtuemart_product_id =".$id;
			// $db->setQuery($query);
			// $field = $db->loadObject();
			$html = array ();
			$display = $model->inputType($customs,$id,$row);
			$html[] = '<div class="vm_thumb_image">
				<span>'.$display.'</span>
				<input type="hidden" value="R" name="field['.$row.'][field_type]" />
				<input type="hidden" value="'.$customs->virtuemart_custom_id.'" name="field['.$row.'][virtuemart_custom_id]" />

				<input type="hidden" value="0" name="field['.$row.'][admin_only]" />
				<span class="vmicon vmicon-16-default-off"></span>
				<div class="trash"></div></div>';

				$json['table'] = 'products';
			$json['value'] = $html;
			$json['ok'] = $id ;
		}else if ($type=='relatedcategories') {
			$query = "SELECT virtuemart_category_id AS id, CONCAT(category_name, '::', virtuemart_category_id) AS value
				FROM #__virtuemart_categories ";
			if ($filter) $query .= " WHERE category_name LIKE '%". $db->getEscaped( $filter, true ) ."%' limit 0,50";
			$db->setQuery($query);
			if ($result = $db->loadObjectList() ) echo json_encode($result);
			else echo $db->_sql;
			return;

		} else if ($type=='categories') {

			$query = 'SELECT * FROM `#__virtuemart_customs` WHERE field_type = "Z" ';
			$db->setQuery($query);
			$customs = $db->loadObject();
			$customs->custom_value = $id;

			// $query = "SELECT virtuemart_category_id AS id, category_name AS value
				// FROM #__virtuemart_categories WHERE virtuemart_category_id =".$id;
			// $db->setQuery($query);
			// $field = $db->loadObject();
			$html = array ();
			$display = $model->inputType($customs,$id,$row);
			$html[] = '<div class="vm_thumb_image">
				<span>'.$display.'</span>
				<input type="hidden" value="Z" name="field['.$row.'][field_type]" />
				<input type="hidden" value="'.$customs->virtuemart_custom_id.'" name="field['.$row.'][virtuemart_custom_id]" />
				<input type="hidden" value="0" name="field['.$row.'][admin_only]" />
				<div class="trash"></div></div>';
			$json['table'] = '#categories';
			$json['value'] = $html;
			$json['ok'] = $id ;
		} else if ($type=='custom') {
			$query = "SELECT CONCAT(virtuemart_custom_id, '|', custom_value, '|', field_type) AS id, CONCAT(custom_title, '::', custom_tip) AS value
				FROM #__virtuemart_customs";
			if ($filter) $query .= " WHERE custom_title LIKE '%".$filter."%' limit 0,50";
			$db->setQuery($query);
			$json['value'] = $db->loadObjectList();
			$json['ok'] = 1 ;
		} else if ($type=='fields') {
			$fieldTypes= $model->getField_types() ;

			$query = "SELECT *,custom_value as value FROM #__virtuemart_customs
			WHERE (`virtuemart_custom_id`=".$id." or `custom_parent_id`=".$id.")";
			$query .=" order by custom_parent_id asc";
			$db->setQuery($query);
			$rows = $db->loadObjectlist();
			
			$html = array ();
			foreach ($rows as $field) {
				if ($field->field_type =='C' ){
					$json['table'] = 'childs';
					$q='SELECT `virtuemart_product_id` FROM `#__virtuemart_products` WHERE `published`=1
					AND `product_parent_id`= '.JRequest::getInt('virtuemart_product_id');
					//$db->setQuery(' SELECT virtuemart_product_id, product_name FROM `#__virtuemart_products` WHERE `product_parent_id` ='.(int)$product_id);
					$db->setQuery($q);
					if ($childIds = $db->loadResultArray()) {
					// Get childs
						foreach ($childIds as $childId) {
							$field->custom_value = $childId;
							$display = $model->inputType($field,$childId,$row);
							 if ($field->is_cart_attribute) $cartIcone=  'default';
							 else  $cartIcone= 'default-off';
							 $html[] = '<div class="removable">
								<td>'.$field->custom_title.'</td>
								 <td>'.$display.$field->custom_tip.'
								 </td>
								 <td>'.JText::_($fieldTypes[$field->field_type]).'
									<input type="hidden" value="'.$field->field_type .'" name="field['.$row.'][field_type]" />
									<input type="hidden" value="'.$field->virtuemart_custom_id.'" name="field['.$row.'][virtuemart_custom_id]" />
									<input type="hidden" value="'.$field->admin_only.'" name="field['.$row.'][admin_only]" />
								 </td>
								 <td><span class="vmicon vmicon-16-'.$cartIcone.'"></span></td>
								 <td></td>
								</div>';
							$row++;
						}
					}
				} elseif ($field->field_type =='E') {
					$json['table'] = 'childs';
					$display = $model->inputType($field,$product_id,$row);
					 if ($field->is_cart_attribute) $cartIcone=  'default';
					 else  $cartIcone= 'default-off';
					 if (!empty ($field->custom_tip) ) $field->custom_tip = '<span> ('.$field->custom_tip.')</span>';
					 else $field->custom_tip ='';
					 $html[] = '<div class="removable">
						<div>'.$field->custom_title.$field->custom_tip.'</span></div>				
						<span>'.$display.'</span>
						<input type="hidden" value="'.$field->field_type .'" name="field['.$row.'][field_type]" />
						<input type="hidden" value="'.$field->virtuemart_custom_id.'" name="field['.$row.'][virtuemart_custom_id]" />
						<input type="hidden" value="'.$field->admin_only.'" name="field['.$row.'][admin_only]" />
						 <span class="vmicon vmicon-16-'.$cartIcone.'"></span>
						<span class="trash"></span>'.JTEXT::_('COM_VIRTUEMART_CUSTOM_ACTIVATE_JAVASCRIPT').'</div>';
					$row++;

				} else {
				$json['table'] = 'fields';
				$display = $model->inputType($field,$product_id,$row);
				 if ($field->is_cart_attribute) $cartIcone=  'default';
				 else  $cartIcone= 'default-off';
				 $html[] = '<tr class="removable">
					<td>'.$field->custom_title.'</td>
					<td>'.$field->custom_tip.'</td>
					 <td>'.$display.'</td>
					 <td>'.JText::_($fieldTypes[$field->field_type]).'
						<input type="hidden" value="'.$field->field_type .'" name="field['.$row.'][field_type]" />
						<input type="hidden" value="'.$field->virtuemart_custom_id.'" name="field['.$row.'][virtuemart_custom_id]" />
						<input type="hidden" value="'.$field->admin_only.'" name="field['.$row.'][admin_only]" />
					 </td>
					 <td><span class="vmicon vmicon-16-'.$cartIcone.'"></span></td>
					 <td><span class="trash"></span><input class="ordering" type="hidden" value="'.$row.'" name="field['.$row .'][ordering]" /></td>
					</tr>';
				$row++;
				}
			}

			$json['value'] = $html;
			$json['ok'] = 1 ;
		} else $json['ok'] = 0 ;
		if ( empty($json)) {
			$json['value'] = null;
			$json['ok'] = 1 ;
		}
		echo json_encode($json);


	}

}
// pure php no closing tag
