<?php
/**
 * Shopper group data access object.
 *
 * @package	VirtueMart
 * @subpackage ShopperGroup
 * @author Markus �hler 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Shopper group to user xref table. 
 * 
 *
 * @author Max Milbers
 * @package	VirtueMart
 */
class TableUser_perm_group extends JTable
{
	/** @var int primary key, same as user (user can only be in one group */
	var $user_id	= 0;
	
	/** @var int Group id */
	var $group_id	= 0;
	

	/**
	 * @author Max Milbers
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_user_perm_group_xref', 'user_id', $db);
	}


	/**
	 * Validates the shopper group record fields.
	 *
	 * @author Max Milbers
	 * @return boolean True if the table buffer contains valid data, false otherwise.
	 */
	function check() 
	{
    	if (!$this->user_id) {
			$this->setError(JText::_('Shopper must have an user id to set permission.'));
			dump($this->user_id,'table check no user id');
			return false;
		} else if (!$this->group_id) {
			$this->setError(JText::_('Shopper must have a group id to set permissin'));
			dump($this->group_id,'table check no group id');
      		return false;
		}

		return true;
	}

	/**
	 * This override is necessary, because the table handles the user_id as primary.
	 * Therefore the table often tries an update instead of an insert.
	 * 
	 * @author Max Milbers
	 * 
	 */
	function store(){
		/* Check if a record exists */
		$q = 'SELECT `user_id`
			FROM `#__vm_auth_user_group`
			WHERE `user_id` = "'.$this->user_id.'"';
		$this->_db->setQuery($q);
		$total = $this->_db->loadResult();
		
		if( $total)
		{
			$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key );
		}
		else
		{
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		}
		if( !$ret )
		{
			$this->setError(get_class( $this ).'::store failed - '.$this->_db->getErrorMsg());
			return false;
		}
		else
		{
			return true;
		}
	}
}
?>
