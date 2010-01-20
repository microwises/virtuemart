<?php
/**
*
* Country table
*
* @package	VirtueMart
* @subpackage Media
* @author RolandD
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
 * @author RolandD
 */
class TableMedia extends JTable {

	/** @var int Primary key */
	var $file_id				= 0;
	/** @var integer Product id */
	var $file_product_id		= 0;
	/** @var string File name */
	var $file_name				= '';
	/** @var string File title */
	var $file_title				= '';
    /** @var string File description */
	var $file_description		= '';
    /** @var string File extension */
	var $file_extension			= '';
	/** @var string File mime type */
	var $file_mimetype			= '';
	/** @var string File URL */
	var $file_url				= '';
	/** @var int File published or not */
	var $file_published			= 0;
	/** @var int File is an image or other */
	var $file_is_image			= 0;
	/** @var int File image height */
	var $file_image_height		= 0;
	/** @var int File image width */
	var $file_image_width		= 0;
	/** @var int File thumbnail image height */
	var $file_image_thumb_height = 0;
	/** @var int File thumbnail image width */
	var $file_image_thumb_width	= 0;


	/**
	 * @author RolandD
	 * @param $db A database connector object
	 */
	function __construct(&$db) {
		parent::__construct('#__vm_product_files', 'file_id', $db);
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
			$this->setError(JText::_('Country records must contain a contry name.'));
			return false;
		}
		if (!$this->country_2_code) {
			$this->setError(JText::_('Country records must contain a 2 symbol code.'));
			return false;
		}
		if (!$this->country_3_code) {
			$this->setError(JText::_('Country records must contain a 3 symbol code.'));
			return false;
		}

		if (($this->country_name) && ($this->country_id == 0)) {
		    $db =& JFactory::getDBO();

			$q = 'SELECT count(*) FROM `#__vm_country` ';
			$q .= 'WHERE `country_name`="' .  $this->country_name . '"';
            $db->setQuery($q);
		    $rowCount = $db->loadResult();
			if ($rowCount > 0) {
				$this->setError(JText::_('The given country name already exists.'));
				return false;
			}
		}

		return true;
	}




}
?>
