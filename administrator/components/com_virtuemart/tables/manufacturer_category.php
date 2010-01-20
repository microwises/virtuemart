<?php
/**
*
* Manufacturer Category table
*
* @package	VirtueMart
* @subpackage Manufacturer category
* @author vhv_alex
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
 * Manufacturer category table class
 * The class is used to manage the manufacturer category in the shop.
 *
 * @package		VirtueMart
 * @author vhv_alex
 */
class TableManufacturer_Category extends JTable {

	/** @var int Primary key */
	var $mf_category_id = 0;
	/** @var string manufacturer category name */
	var $mf_category_name = '';
	/** @var string manufacturer category description */
	var $mf_category_desc = '';
	/** @var int Published or unpublished */
	var $published = 1;


	/**
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_manufacturer_category', 'mf_category_id', $db);
	}


	/**
	 * Validates the manufacturer category record fields before saving to db.
	 *
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check()
	{
        if (!$this->mf_category_name) {
			$this->setError(JText::_('Manufacturer category name is empty.'));
			return false;
		}

		if (($this->mf_category_name) && ($this->mf_category_id == 0)) {
		    $db =& JFactory::getDBO();

			$q = 'SELECT count(*) FROM #__vm_manufacturer_category ';
			$q .= 'WHERE mf_category_name="' .  $this->mf_category_name . '"';
            $db->setQuery($q);
		    $rowCount = $db->loadResult();
			if ($rowCount > 0) {
				$this->setError(JText::_('The given manufacturer category name already exists.'));
				return false;
			}
		}

		return true;
	}
	/*
	 * Verify that user have to delete all manufacturers of a particular category before that category can be removed
	 *
	 * @return boolean True if category is ready to be removed, otherwise False
	 */
	function checkManufacturer($categoryId = 0)
	{
		if($categoryId > 0) {
			$db = JFactory::getDBO();

			$q = 'SELECT count(*)'
				.' FROM #__vm_manufacturer'
				.' WHERE mf_category_id = '.$categoryId;
			$db->setQuery($q);
			$mCount = $db->loadResult();

			if($mCount > 0) {
				return false;
			}

		}
		return true;
	}




}
?>
