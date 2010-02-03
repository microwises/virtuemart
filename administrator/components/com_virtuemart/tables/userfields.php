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
* @version $Id: order_status.php 2227 2010-01-20 23:03:48Z SimonHodgkiss $
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
	/** @var mediumtex */
	var $param			= 0;
	/**
	 * @param $db Class constructor; connect to the database
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_userfield', 'fieldid', $db);
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
}

//No CLosing Tag
