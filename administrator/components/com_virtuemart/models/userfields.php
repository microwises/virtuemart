<?php
/**
 * Data module for user fields
 *
 * @package	VirtueMart
 * @subpackage Userfields
 * @author RolandD 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');

/**
 * Model class for user fields
 *
 * @package	VirtueMart
 * @subpackage Userfields 
 * @author RolandD  
 */
class VirtueMartModelUserfields extends JModel {
	
	/**
	* Prepare a user field for database update
	*/
	public function prepareFieldDataSave($fieldType, $fieldName, $value=null) {
		$post = JRequest::get('post');
		switch(strtolower($fieldType)) {
			case 'webaddress':
				if (isset($post[$fieldName."Text"]) && ($post[$fieldName."Text"])) {
					$oValuesArr = array();
					$oValuesArr[0] = str_replace(array('mailto:','http://','https://'),'', $value);
					$oValuesArr[1] = str_replace(array('mailto:','http://','https://'),'', $post[$fieldName."Text"]);
					$value = implode("|*|",$oValuesArr);
				} 
				else {
					$value = str_replace(array('mailto:','http://','https://'),'', $value);
				}
				break;
			case 'email': 
				$value = str_replace(array('mailto:','http://','https://'),'', $value);
				break;
			case 'multiselect':
			case 'multicheckbox':
			case 'select':
				if (is_array($value)) $value = implode("|*|",$value);
				break;
			case 'age_verification':
				$value = JRequest::getInt('birthday_selector_year')
							.'-'.JRequest::getInt('birthday_selector_month')
							.'-'.JRequest::getInt('birthday_selector_day');
				break;
			default:
				break;
		}
		return $value;
	}
}
?>