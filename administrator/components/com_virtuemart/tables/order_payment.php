<?php
/**
 * Order table holding payment log
 *
 * @package	VirtueMart
 * @subpackage Orders
 * @author 	Oscar van Eijk
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

class TableOrder_payment extends JTable
{
	/**
	 * @var Array holding all table fields
	 * @access private
	 */
	private $fieldList;

	/**
	 * Constructor
	 */
	function __construct(&$_db)
	{
		self::loadFields($_db);
		parent::__construct('#__vm_order_payment', 'order_payment_id', $_db);
	}

	/**
	 * Load the fieldlist
	 */
	private function loadFields(&$_db)
	{
		$_q = 'SHOW COLUMNS FROM `#__vm_order_payment`';
		$_db->setQuery($_q);
		$_fields = $_db->loadObjectList();
		if (count($_fields) > 0) {
			foreach ($_fields as $_k => $_f) {
				$this->fieldList[$_f->Field] = $_f->Default;
			}
			$this->setProperties($this->fieldList);
		}
	}

	/**
	 * Get an array containing all properties from this class as keys. The properties from
	 * the parent class are excluded.
	 * Values are all set to the default value.
	 * 
	 * @access public
	 * @author Oscar van Eijk
	 * @return array
	 */
	public function getTableFields ()
	{
		return $this->fieldList;
// TODO This construction uses actual values i.s.o. default; preferable but doesn't work with __set properties :-(
//		$_rClass = new ReflectionClass(__CLASS__);
//		$_properties = $_rClass->getProperties();
//		$_fields = array();
//		foreach ($_properties as $_obj) {
//			if ($_obj->class == __CLASS__) {
//				$_p = $_obj->name;
//				$_fields[$_p] = $this->$_p;
//			}
//		}
//		return $_fields;
	}
}
// No closing tag