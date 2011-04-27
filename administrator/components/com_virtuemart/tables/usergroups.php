<?php
/**
*
* Usergroup table
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
class TableUsergroups extends JTable {

	/** @var Primary Key*/
	var $group_id = 0;
	/** @var Authentification Groupname*/
	var $group_name='';
	/** @var Authentification level standard is set to demo*/
	var $group_level = 750;
        /** @var boolean */
	var $checked_out	= 0;
	/** @var time */
	var $checked_out_time	= 0;
// 	var $published = 1;

	function __construct(&$db)
	{
		parent::__construct('#__vm_perm_groups', 'group_id', $db);
	}

	/**
	 * Validates the userfields record fields.
	 *
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check($nrOfValues)
	{
		if (!$this->group_name) {
			$this->setError(JText::_('COM_VIRTUEMART_PERMISSION_GROUP_MUST_HAVE_NAME'));
			return false;
		}

		if (preg_match('/[^a-z0-9\._\-]/i', $this->name) > 0) {
			$this->setError(JText::_('COM_VIRTUEMART_PERMISSION_GROUP_NAME_INVALID_CHARACTERS'));
			return false;
		}

		return true;
	}


}

//No CLosing Tag
