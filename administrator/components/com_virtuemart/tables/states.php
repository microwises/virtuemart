<?php
/**
*
* State table
*
* @package	VirtueMart
* @subpackage Country
* @author RickG
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

if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

/**
 * State table class
 * The class is is used to manage the states in a country
 *
 * @package		VirtueMart
 * @author RickG
 */
class TableStates extends VmTable {

	/** @var int Primary key */
	var $virtuemart_state_id				= 0;
	/** @var integer Country id */
	var $virtuemart_country_id           	= 0;
	/** @var integer Zone id */
	var $virtuemart_worldzone_id           	= 0;
	/** @var string State name */
	var $state_name           	= '';
	/** @var char 3 character state code */
	var $state_3_code         	= '';
    /** @var char 2 character state code */
	var $state_2_code         	= '';
	/** @var int published or unpublished */
	var $published         		= 1;


	/**
	 * @author RickG
	 * @author Max Milbers
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__virtuemart_states', 'virtuemart_state_id', $db);

		$this->setUniqueName('state_name');
		$this->setObligatoryKeys('state_2_code');
		$this->setObligatoryKeys('state_3_code');

		$this->setLoggable();
	}


	/**
	 * Validates the state record fields.
	 *
	 * @author RickG
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
//	function check()
//	{
//        if (!$this->state_name) {
//			$this->setError(JText::_('COM_VIRTUEMART_STATE_RECORDS_MUST_CONTAIN_STATE_NAME'));
//			return false;
//		}
//		if (!$this->state_2_code) {
//			$this->setError(JText::_('COM_VIRTUEMART_STATE_RECORDS_MUST_CONTAIN_2_SYMBOL_CODE'));
//			return false;
//		}
//		if (!$this->state_3_code) {
//			$this->setError(JText::_('COM_VIRTUEMART_STATE_RECORDS_MUST_CONTAIN_3_SYMBOL_CODE'));
//			return false;
//		}
//
//		if (($this->state_name) && ($this->virtuemart_state_id == 0)) {
//		    $db = JFactory::getDBO();
//
//			$q = 'SELECT count(*) FROM `#__virtuemart_states` ';
//			$q .= 'WHERE `state_name`="' .  $this->state_name . '"';
//            $db->setQuery($q);
//		    $rowCount = $db->loadResult();
//			if ($rowCount > 0) {
//				$this->setError(JText::_('COM_VIRTUEMART_STATE_NAME_ALREADY_EXISTS'));
//				return false;
//			}
//		}
//
//		return true;
//	}




}
// pure php no closing tag
