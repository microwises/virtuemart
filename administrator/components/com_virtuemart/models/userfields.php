<?php
/**
*
* Data module for user fields
*
* @package	VirtueMart
* @subpackage Userfields
* @author RolandD
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