<?php
/**
*
* Manufacturer table
*
* @package	VirtueMart
* @subpackage Manufacturer
* @author Patrick Kohl
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
 * Manufacturer table class
 * The class is used to manage the manufacturer table in the shop.
 *
 * @package		VirtueMart
 * @author
 */
class TableManufacturer extends JTable {

	/** @var int Primary key */
	var $manufacturer_id = 0;
	/** @var string manufacturer name */
	var $mf_name = '';
	/** @var string manufacturer email */
	var $mf_email = '';
	/** @var string manufacturer description */
	var $mf_desc = '';
    /** @var int Manufacturer category id */
	var $mf_category_id  = 0;
    /** @var string manufacturer URL */
	var $mf_url = '';

	/** @var int enabled or unpublished */
	var $enabled = 1;
              /** @var boolean */
	var $locked_on	= 0;
	/** @var time */
	var $locked_by	= 0;

	/**
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__virtuemart_manufacturers', 'manufacturer_id', $db);
	}


	/**
	 * Validates the manufacturer record before saving to db.
	 *
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check()
	{
        if (!$this->mf_name) {
			$this->setError(JText::_('COM_VIRTUEMART_MANUFACTURER_RECORDS_MUST_CONTAIN_NAME'));
			return false;
		}

		if (($this->mf_name) && ($this->manufacturer_id == 0)) {
		    $db =& JFactory::getDBO();

			$q = 'SELECT `manufacturer_id` FROM `#__virtuemart_manufacturers` ';
			$q .= 'WHERE `mf_name`="' .  $this->mf_name . '"';
            $db->setQuery($q);
		    $manufacturer_id = $db->loadResult();
		    if (!empty($manufacturer_id) && $manufacturer_id!=$this->manufacturer_id) {
				$this->setError(JText::_('COM_VIRTUEMART_MANUFACTURER_NAME_ALREADY_EXISTS'));
				return false;
			}
		}

		return true;
	}

}
// pure php no closing tag
