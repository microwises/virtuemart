<?php
/**
*
* Country table
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

/**
 * Country table class
 * The class is is used to manage the countries in the shop.
 *
 * @package		VirtueMart
 * @author RickG
 */
class TableCountry extends JTable {

	/** @var int Primary key */
	var $virtuemart_country_id				= 0;
	/** @var integer Zone id */
	var $virtuemart_zone_id           		= 0;
	/** @var string Country name */
	var $country_name           = '';
	/** @var char 3 character country code */
	var $country_3_code         = '';
    /** @var char 2 character country code */
	var $country_2_code         = '';
    /** @var int published or unpublished */
	var $published 		        = 1;
           /** @var boolean */
	var $locked_on	= 0;
	/** @var time */
	var $locked_by	= 0;



	/**
	 * @author RickG
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__virtuemart_countries', 'virtuemart_country_id', $db);
	}


	/**
	 * Validates the country record fields.
	 *
	 * @author RickG
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check()
	{
        if (!$this->country_name) {
			$this->setError(JText::_('COM_VIRTUEMART_COUNTRY_RECORDS_MUST_CONTAIN_CONTRY_NAME'));
			return false;
		}
		if (!$this->country_2_code) {
			$this->setError(JText::_('COM_VIRTUEMART_COUNTRY_RECORDS_MUST_CONTAIN_2_SYMBOL_CODE'));
			return false;
		}
		if (!$this->country_3_code) {
			$this->setError(JText::_('COM_VIRTUEMART_COUNTRY_RECORDS_MUST_CONTAIN_3_SYMBOL_CODE'));
			return false;
		}

		if (($this->country_name)) {
		    $db = JFactory::getDBO();

			$q = 'SELECT `virtuemart_country_id` FROM `#__virtuemart_countries` ';
			$q .= 'WHERE `country_name`="' .  $this->country_name . '"';
            $db->setQuery($q);
		    $virtuemart_country_id = $db->loadResult();
		    if (!empty($virtuemart_country_id) && $virtuemart_country_id!=$this->virtuemart_country_id) {
				$this->setError(JText::_('COM_VIRTUEMART_COUNTRY_NAME_ALREADY_EXISTS'));
				return false;
			}
		}

		return true;
	}

}
// pure php no closing tag
