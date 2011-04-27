<?php
/**
*
* Users table
*
* @package	VirtueMart
* @subpackage User
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: user_shoppergroup.php 2420 2010-06-01 21:12:57Z oscar $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * user_shoppergroup_xref table class
 * The class is used to link users to shoppergroups.
 *
 * @package	VirtueMart
 * @author Max Milbers
 */
 
 class TableVm_users extends JTable {

	/** @var int Vendor ID */
	var $user_id			= 0;
	var $user_is_vendor 	= 0;
	var $vendor_id 			= 0;
	var $customer_number 	= 0;
	var $perms				= 0;
        /** @var boolean */
	var $checked_out	= 0;
	/** @var time */
	var $checked_out_time	= 0;

	/**
	 * @param $db Class constructor; connect to the database
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_users', 'user_id', $db);
	}
 
 	/**
 	 * Every entry must contain a user_id
 	 * @author Max Milbers
 	 */
 	public function check(){
		if (!$this->user_id) {
			$this->setError(JText::_('COM_VIRTUEMART_USERS_MUST_HAVE_USER_ID'));
			return false;
		}
		return true;
 	}
 	/**
	 * Records in this table do not need to exist, so we might need to create a record even
	 * if the primary key is set. Therefore we need to overload the store() function.
	 * 
	 * @author Oscar van Eijk
	 * @see libraries/joomla/database/JTable#store($updateNulls)
	 */
	public function store()
	{
		$_qry = 'SELECT user_id '
				. 'FROM #__vm_users '
				. 'WHERE user_id = ' . $this->user_id
		;
		$this->_db->setQuery($_qry);
		$_count = $this->_db->loadResultArray();

		if (count($_count) > 0) {
			$returnCode = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, false );
		} else {
			$returnCode = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key);
		}
		
		if (!$returnCode){
			$this->setError(get_class( $this ).'::store failed - '.$this->_db->getErrorMsg());
			return false;
		}
		else return true;
	}
	
 }
