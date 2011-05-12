<?php
/**
*
* Media table
*
* @package	VirtueMart
* @subpackage Media
* @author Max Milbers
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
 * Media table class
 * The class is is used to manage the countries in the shop.
 *
 * @author Max Milbers
 * @package		VirtueMart
 */
class TableMedia extends JTable {

	/** @var int Primary key */
	var $file_id				= 0;
	var $vendor_id				= 0;

	/** @var string File title */
	var $file_title				= '';
    /** @var string File description */
	var $file_description		= '';
    /** @var string File Meta or alt  */
	var $file_meta		= '';

	/** @var string File mime type */
	var $file_mimetype			= '';
	/** @var string File URL */
	var $file_url				= '';
	var $file_url_thumb			= '';

	/** @var int File enabled or not */
	var $enabled			= 0;
	/** @var int File is an image or other */
	var $file_is_downloadable	= 0;
	var $file_is_forSale		= 0;
	var $file_is_product_image 	= 0;


	var $shared = 0;
	var $file_params	= '';
                /** @var boolean */
	var $locked_on	= 0;
	/** @var time */
	var $locked_by	= 0;
	/**
	 * @author Max Milbers
	 * @param $db A database connector object
	 */
	function __construct(&$db) {
		parent::__construct('#__virtuemart_medias', 'file_id', $db);
	}

	/**
	 *
	 * @author Max Milbers
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check(){

		$ok = true;
		$notice = true;
	    if (!$this->vendor_id) {
			$this->vendor_id = 1; //default to mainvendor
		}
		if(empty($this->file_title)) $this->file_title = $this->file_name ;

		if(!empty($this->file_title)){
			if(strlen($this->file_title)>126){
				$this->setError('Title too long '.strlen($this->file_title).' for database field, allowed 126');
			}
		} else{
			$this->setError(JText::_('COM_VIRTUEMART_MEDIA_FILES_MUST_HAVE_TITLE'));
			$ok = false;
		}

		if(!empty($this->file_description)){
			if(strlen($this->file_description)>254){
				$this->setError('Description too long '.strlen($this->file_title).' for database field, allowed 254');
			}
		} else{
//			$this->setError(JText::_('COM_VIRTUEMART_MEDIA_FILES_MUST_HAVE_DESCRIPTION'));
//			$ok = false;
		}

		if(!empty($this->file_mimetype)){
//			if(strlen($this->file_title)>254){
//				$this->setError('Url to long '.strlen($this->file_title).' for database field, allowed 254');
//			}
		} else{
			$this->setError(JText::_('COM_VIRTUEMART_MEDIA_FILES_SHOULD_HAVE_MIMETYPE'));
			$notice = true;
		}

		if(!empty($this->file_url)){
			if(strlen($this->file_title)>254){
				$this->setError('Url too long '.strlen($this->file_title).' for database field, allowed 254');
			}
		} else{
			$this->setError(JText::_('COM_VIRTUEMART_MEDIA_FILES_MUST_HAVE_URL'));
			$ok = false;
		}

		$date = JFactory::getDate();
		$today = $date->toMySQL();
		if(empty($this->created_on)){
			$this->created_on = $today;
		}
     	$this->modified_on = $today;

		return $ok;
	}

}
// pure php no closing tag
