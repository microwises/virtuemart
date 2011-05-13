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
 * Shopper group table.
 *
 * This class is a template.
 *
 * @author Markus �hler
 * @package	VirtueMart
 */
class TableShoppergroups extends JTable
{
	/** @var int primary key */
	var $virtuemart_shoppergroup_id	 = 0;

	/** @var int Vendor id */
	var $virtuemart_vendor_id = 0;

	/** @var string Shopper group name; no more than 32 characters */
	var $shopper_group_name  = '';

	/** @var string Shopper group description */
	var $shopper_group_desc  = '';

    /** @var int default group that new customers are associated with. There can only be one
     * default group per vendor. */
	var $default = 0;
               /** @var boolean */
	var $locked_on	= 0;
	/** @var time */
	var $locked_by	= 0;

	/**
	 * @author Markus �hler
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__virtuemart_shoppergroups', 'virtuemart_shoppergroup_id', $db);
	}


	/**
	 * Validates the shopper group record fields.
	 *
	 * @author Markus �hler
	 * @return boolean True if the table buffer contains valid data, false otherwise.
	 */
	function check()
	{
    if (!$this->shopper_group_name) {
			$this->setError(JText::_('COM_VIRTUEMART_SHOPPER_GROUPS_RECORDS_MUST_HAVE_NAME'));
			return false;
		} else if (mb_strlen($this->shopper_group_name) > 32) {
			$this->setError(JText::_('COM_VIRTUEMART_SHOPPER_GROUPS_NAMES_LESS_THAN_32_CHARACTERS'));
      return false;
		}

		if (($this->country_name) && ($this->virtuemart_shoppergroup_id == 0)) {

			$db =& JFactory::getDBO();
			$query = 'SELECT count(*) FROM '
			  . $db->nameQuote('#__virtuemart_shoppergroups')
			  . ' WHERE '
			  . $db->nameQuote('shopper_group_name')
			  . ' = '
			  . $db->Quote($this->shopper_group_name)
			  . ' AND '
			  . $db->nameQuote('virtuemart_vendor_id')
			  . ' = ' . $this->virtuemart_vendor_id;

			$db->setQuery($query);
		  $rowCount = $db->loadResult();

			if ($rowCount > 0) {
				$this->setError(JText::_('COM_VIRTUEMART_SHOPPER_GROUP_NAME_ALREADY_EXISTS_FOR_GIVEN_VENDOR'));
				return false;
			}

			// TODO-MOE:
			//  * Add check for "default" - so that only one shopper group can have the "default" flag set
			//    per vendor id.
		}

		return true;
	}

}
// pure php no closing tag
