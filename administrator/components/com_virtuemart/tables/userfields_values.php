<?php
/**
*
* Userfield Values table
*
* @package	VirtueMart
* @subpackage Userfields
* @author Oscar van Eijk
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: order_status.php 2227 2010-01-20 23:03:48Z SimonHodgkiss $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Userfields table class
 * The class is used to manage the values for select-type userfields in the shop.
 *
 * @package	VirtueMart
 * @author Oscar van Eijk
 */
class TableUserfields_values extends JTable {

	/** @var int Primary key */
	var $fieldvalueid	= 0;
	/** @var int Reference to the userfield */
	var $fieldid		= 0;
	/** @var string Label of the value */
	var $fieldtitle	= null;
	/** @var string Selectable value */
	var $fieldvalue	= null;
	/** @var int Value ordering */
	var $ordering		= 0;
	/** @var boolean True if part of the VirtueMart installation; False for User specified*/
	var $sys			= 0;

	/**
	 * @param $db Class constructor; connect to the database
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_userfield_values', 'fieldvalueid', $db);
	}

	/**
	 * Validates the userfields record fields.
	 *
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check()
	{
        if (!$this->order_status_code) {
			$this->setError(JText::_('Order status records must contain an order status code.'));
			return false;
		}
		if (!$this->order_status_name) {
			$this->setError(JText::_('Order status records must contain an order status name.'));
			return false;
		}

		if ($this->order_status_id == 0) {
			$db =& JFactory::getDBO();

			$q = 'SELECT count(*) FROM `#__vm_order_status` ';
			$q .= 'WHERE `order_status_code`="' .  $this->order_status_code . '"';
			$db->setQuery($q);
			$rowCount = $db->loadResult();
			if ($rowCount > 0) {
				$this->setError(JText::_('The given status code already exists.'));
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Reimplement delete() to get a list if value IDs based on the field id
	 * @var Field id
	 * @return boolean True on success
	 */
	function delete($fieldid)
	{
		if ($fieldvalueids = $this->_getList('SELECT `fieldvalueid` FROM `#__vm_userfield_value` WHERE `fieldid` = ' . $fieldid)) {
			foreach ($fieldvalueids as $fieldvalueid) {
				if (!parent::delete($fieldvalueid)) {
					return false;
				}
			}
		}
		return true;
	}
}

//No CLosing Tag
