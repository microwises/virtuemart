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
* @version $Id: countries.php 3454 2011-06-07 16:33:49Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

/**
 * Country table class
 * The class is is used to manage the languages in the shop.
 *
 * @package		VirtueMart
 * @author Max Milbers
 */
class TableLanguages extends VmTable {

	/** @var int Primary key */
	var $virtuemart_language_id				= 0;

	var $language_name           		= 0;

	var $language_code           = '';

	var $installed         = '';

	var $default         = '';

    /** @var int published or unpublished */
	var $published 		        = 1;


	/**
	 * @author Max Milbers
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__virtuemart_languages', 'virtuemart_language_id', $db);

		$this->setUniqueName('language_name');
		$this->setObligatoryKeys('language_code');

		$this->setLoggable();
	}

}
// pure php no closing tag
