<?php
/**
*
* Order status table
*
* @package	VirtueMart
* @subpackage Order status
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
 * Order status table class
 * The class is is used to manage the order statuses in the shop.
 *
 * @package	VirtueMart
 * @author Oscar van Eijk
 */
class TableOrder_status extends JTable {

	/** @var int Primary key */
	var $order_status_id			= 0;
	/** @var char Order status Code */
	var $order_status_code			= '';
	/** @var string Order status name*/
	var $order_status_name			= null;
	/** @var string Order status description */
	var $order_status_description	= null;
	/** @var int Order in which the order status is listed */
	var $ordering					= 0;
	/** @var int Vendor ID if the status is vendor specific */
	var $vendor_id					= null;
        /** @var boolean */
	var $checked_out	= 0;
	/** @var time */
	var $checked_out_time	= 0;

	/**
	 * @param $db Class constructor; connect to the database
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_order_status', 'order_status_id', $db);
	}

	/**
	 * Validates the order status record fields.
	 *
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check()
	{
        if (empty($this->order_status_code)) {
			$this->setError(JText::_('COM_VIRTUEMART_ORDER_TABLE_ERROR_CODE'));
			return false;
		}
		if (empty($this->order_status_name)) {
			$this->setError(JText::_('COM_VIRTUEMART_ORDER_TABLE_ERROR_NAME'));
			return false;
		}

		$db =& JFactory::getDBO();
		$q = 'SELECT count(*),order_status_id FROM `#__vm_order_status` ';
		$q .= 'WHERE `order_status_code`="' .  $this->order_status_code . '"';
		$db->setQuery($q); 

		$row = $db->loadRow();
		if(is_array($row)){
			if($row[0]>0){
				if($row[1] != $this->order_status_id){
					$this->setError(JText::_('COM_VIRTUEMART_ORDER_TABLE_ERROR_EXISTS'));
					return false;
				}
			}					
		}

		return true;
	}
}

//No CLosing Tag
