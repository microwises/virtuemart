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
* @version $Id$
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
	var $fieldtitle		= null;
	/** @var string Selectable value */
	var $fieldvalue		= null;
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
	 * Validates the userfields record fields, and checks if the given value already exists.
	 * If so, the primary key is set.
	 *
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check()
	{
		if (preg_match('/[^a-z0-9\._\-]/i', $this->fieldtitle) > 0) {
			$this->setError(JText::_('Title in fieldvalues contains invalid characters'));
			return false;
		}

		$db =& JFactory::getDBO();
		$q = 'SELECT `fieldvalueid` FROM `#__vm_userfield_values` '
			. 'WHERE `fieldtitle`="' . $this->fieldtitle . '" '
			. 'AND   `fieldid`=' . $this->fieldid;
		$db->setQuery($q);
		$_id = $db->loadResult();
		if ($_id === null) {
			$this->fieldvalueid = null;
		} else {
			$this->fieldvalueid = $_id;
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
		$db =& JFactory::getDBO();
		$db->setQuery('DELETE from `#__vm_userfield_values` WHERE `fieldid` = ' . $fieldid);
		if ($db->query() === false) {
			$this->setError($db->getError());
			return false;
		}
		return true;
	}
}

//No CLosing Tag
