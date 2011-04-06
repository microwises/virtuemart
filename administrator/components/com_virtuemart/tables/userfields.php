<?php
/**
*
* Userfield table
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
 * The class is used to manage the userfields in the shop.
 *
 * @package	VirtueMart
 * @author Oscar van Eijk
 */
class TableUserfields extends JTable {

	/** @var var Primary Key*/
	var $fieldid		= 0;
	/** @var string Internal fielname*/
	var $name			= null;
	/** @var string Visible title*/
	var $title			= null;
	/** @var string Description*/
	var $description	= null;
	/** @var string Input type*/
	var $type			= null;
	/** @var int Max size of string inputs*/
	var $maxlength		= 0;
	/** @var int Fieldsize*/
	var $size			= 0;
	/** @var boolean True if required*/
	var $required		= 0;
	/** @var int Field ordering*/
	var $ordering		= 0;
	/** @var int Nr of columns for textarea*/
	var $cols			= 0;
	/** @var int Nr of rows for textarea*/
	var $rows			= 0;
	/** @var string */
	var $value			= null;
	/** @var int */
	var $default		= 0;
	/** @var boolean True if publised*/
	var $published		= 1;
	/** @var boolean True to display in registration form*/
	var $registration	= 0;
	/** @var boolean True to display in shipping form*/
	var $shipping		= 0;
	/** @var boolean True to display in account maintenance*/
	var $account		= 1;
	/** @var boolean True if readonly*/
	var $readonly		= 0;
	/** @var boolean */
	var $calculated		= 0;
	/** @var boolean True if part of the VirtueMart installation; False for User specified*/
	var $sys			= 0;
	/** @var int The Vendor ID, if vendor specific*/
	var $vendor_id		= 0;
	/** @var mediumtex Additional type-specific parameters */
	var $params			= null;
	/**
	 * @param $db Class constructor; connect to the database
	 */
	function __construct(&$db)
	{
		self::loadFields($db);
		parent::__construct('#__vm_userfield', 'fieldid', $db);
	}

	/**
	 * Load the fieldlist
	 */
	private function loadFields(&$_db)
	{
		$_fieldlist = array();
		$_q = "SHOW COLUMNS FROM `#__vm_userfield`";
		$_db->setQuery($_q);
		$_fields = $_db->loadObjectList();
		if (count($_fields) > 0) {
			foreach ($_fields as $key => $_f) {
				$_fieldlist[$_f->Field] = $_f->Default;
			}
			$this->setProperties($_fieldlist);
		}
	}

	/**
	 * Validates the userfields record fields.
	 *
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check($nrOfValues)
	{
		if (!$this->name) {
			$this->setError(JText::_('COM_VIRTUEMART_USERFIELD_MUST_HAVE_NAME'));
			return false;
		}
		if (!$this->title) {
			$this->setError(JText::_('COM_VIRTUEMART_USERFIELD_MUST_HAVE_TITLE'));
			return false;
		}
		if (preg_match('/[^a-z0-9\._\-]/i', $this->name) > 0) {
			$this->setError(JText::_('COM_VIRTUEMART_NAME_OF_USERFIELD_CONTAINS_INVALID_CHARACTERS'));
			return false;
		}
		$reqValues = array('select', 'multiselect', 'radio', 'multicheckbox');
		if (in_array($this->type, $reqValues) && $nrOfValues == 0) {
			$this->setError(JText::_('COM_VIRTUEMART_VALUES_ARE_REQUIRED_FOR_THIS_TYPE'));
			return false;
		}
		if ($this->fieldid == 0) {
			$_sql = 'SELECT COUNT(*) AS c '
					. 'FROM `#__COM_VIRTUEMART_userfield`'
					. "WHERE name = '" . $this->_db->getEscaped($this->name) . "' ";

			$this->_db->setQuery($_sql);
			$_c = $this->_db->loadResultArray();

			if ($_c[0] > 0) {
				$this->setError(JText::_('COM_VIRTUEMART_USERFIELD_ERR_ALREADY', $this->name));
				return false;
			}
		}
		return true;
	}

	/**
	 * Format the field type
	 * @param $_data array array with additional data written to other tables
	 * @return string Field type in SQL syntax
	 */
	function formatFieldType(&$_data = array())
	{
		$_fieldType = $this->type;
		switch($this->type) {
			case 'date':
				$_fieldType = 'DATE';
				break;
			case 'editorta':
			case 'textarea':
			case 'multiselect':
			case 'multicheckbox':
				$_fieldType = 'MEDIUMTEXT';
				break;
			case 'letterman_subscription':
			case 'yanc_subscription':
			case 'anjel_subscription':
			case 'ccnewsletter_subscription':
				$this->params = 'newsletter='.substr($this->type,0,strpos($this->type, '_') )."\n";
				$this->type = 'checkbox';
			case 'checkbox':
				$_fieldType = 'TINYINT';
				break;
			case 'euvatid':
				$this->params = 'shopper_group_id='.$_data['shopper_group_id']."\n";
				$_fieldType = 'VARCHAR(255)';
				break;
			case 'age_verification':
				$this->params = 'minimum_age='.(int)$_data['minimum_age']."\n";
			default:
				$_fieldType = 'VARCHAR(255)';
				break;
		}
		return $_fieldType;
	}

	/**
	 * Reimplement the store method to return the last inserted ID
	 *
	 * @return mixed When a new record was succesfully inserted, return the ID, otherwise the status
	 */
	function store()
	{
		$isNew = ($this->fieldid == 0);
		if (!parent::store()) { // Write data to the DB
			$this->setError($this->getError());
			return false;
		} else {
			return ($isNew ? $this->_db->insertid() : true);
		}
	}

}

//No CLosing Tag
