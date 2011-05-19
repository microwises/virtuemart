<?php
/**
*
* Media table
*
* @package	VirtueMart
* @subpackage Media
* @author  Patrick Kohl
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: media.php 3057 2011-04-19 12:59:22Z Electrocity $
*/

// Check to ensure this custom is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

/**
 * Custom table class
 * The class manage table to store custom user fields .
 *
 * @author Patrick Kohl
 * @package		VirtueMart
 */
class TableCustomfields extends VmTable {

	/** @var int Primary key */
	var $virtuemart_customfield_id		= 0;

	/** @var int group key */
	var $virtuemart_custom_id		= 0;

    /** @var string custom value */
	var $custom_value	= '';
    /** @var string price  */
	var $custom_price	= '';

	/** @var int custom published or not */
	var $published		= 0;


	/**
	 * @author  Patrick Kohl, Max Milbers
	 * @param $db A database connector object
	 */
	function __construct(&$db) {
		parent::__construct('#__virtuemart_customfields', 'virtuemart_custom_id', $db);

		$this->setObligatoryKeys('virtuemart_custom_id');
//		$this->setObligatoryKeys('creditcard_code');

		$this->setLoggable();


	}


}
// pure php no closing tag
