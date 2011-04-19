<?php
/**
*
* Virtuemart Product Type Parameter table
*
* @package	VirtueMart
* @subpackage
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
 *
 * @package	VirtueMart
 * @author RolandD
 */
class TableProduct_type_parameter extends JTable {

	/** @var int Primary key */
	var $parameter_id = 0;
	/** @var int  */
	var $product_type_id = 0;
	/** @var string Parameter name */
	var $parameter_name = null;
	/** @var string Product type parameter database name */
	var $parameter_label = null;
	/** @var string Description */
	var $parameter_description = null;
	/** @var int The order to list the product types in */
	var $ordering = null;
	/** @var string Type of parameter */
	var $parameter_type = null;
	/** @var string Values for the parameter */
	var $parameter_values = null;
	/** @var string If parameter is multiselect */
	var $parameter_multiselect = null;
	/** @var string Parameter default value */
	var $parameter_default = null;
	/** @var string Unit for parameter */
	var $parameter_unit = null;


	/**
	* @param database A database connector object
	 */
	function __construct($db) {
		parent::__construct('#__vm_product_type_parameter', 'parameter_id', $db );
	}

	/**
	* Store a product type parameter
	* @author RolandD
	*/
	public function store() {
		$db = JFactory::getDBO();

		/* Update or Insert? */
		$insert = $this->check();
		if ($insert) $q = 'INSERT INTO #__vm_product_type_parameter VALUES (';
		else $q = 'UPDATE #__vm_product_type_parameter SET ';

		foreach ($this as $name => $value) {
			if (substr($name, 0, 1) != '_') {
				if ($insert) $q .= $db->Quote($value).',';
				else $q .= $db->nameQuote($name).'='.$db->Quote($value).',';
			}
		}

		if ($insert) $q = substr($q, 0, -1).')';
		else $q = substr($q, 0, -1).' WHERE product_type_id = '.$this->product_type_id." AND parameter_name = ".$db->Quote($this->parameter_name);

		/* Update the database */
		$db->setQuery($q);
		return ($db->query());
	}

	/**
	* Check if the parameter exists or not
	* @author RolandD
	*/
	public function check() {
		$db = JFactory::getDBO();
		$q = "SELECT COUNT(*) AS total FROM #__vm_product_type_parameter WHERE product_type_id = ".$this->product_type_id." AND parameter_name = ".$db->Quote($this->parameter_name);
		$db->setQuery($q);

		$count = $db->loadResult();
		if ($count > 0) return false;
		else return true;
	}
}
// pure php no closing tag
