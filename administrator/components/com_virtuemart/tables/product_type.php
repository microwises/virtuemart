<?php
/**
*
* Virtuemart Product Type table
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
class TableProduct_type extends JTable {

	/** @var int Primary key */
	var $product_type_id = 0;
	/** @var string Product type name */
	var $product_type_name = null;
	/** @var string Description */
	var $product_type_description = null;
	/** @var int Published */
	var $published = null;
	/** @var string Name of the browsepage */
	var $product_type_browsepage = null;
	/** @var string Name of the flypage to use */
	var $product_type_flypage = null;
	/** @var int The order to list the product types in */
	var $product_type_list_order = null;

	/**
	* @param database A database connector object
	 */
	function __construct($db) {
		parent::__construct('#__vm_product_type', 'product_type_id', $db );
	}
}
// pure php no closing tag
