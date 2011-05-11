<?php
/**
*
* calc_category_xref table ( to map calc rules to shoppergroups)
*
* @package	VirtueMart
* @subpackage Calculation tool
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: calc.php 3002 2011-04-08 12:35:45Z alatak $
*/

defined('_JEXEC') or die();

/**
 *
 * The class is an xref table
 *
 * @author Max Milbers
 * @package		VirtueMart
 */

if(!class_exists('VmXrefTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmxreftable.php');

class Tablecalc_category_xref extends VmXrefTable {

	var $_pkey 		= 'calc_rule_id';
	var $pkeyForm	= 'calc_id';

	var $_skey 		= 'calc_category';
	var $skeyForm	= 'calc_categories';

	/**
	 * @author Max Milbers
	 * @param $db A database connector object
	 */
	function __construct(&$db){
		parent::__construct('#__vm_calc_category_xref', 'id', $db);
	}


}
