<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: ps_state.php 1 2008-02-12 14:21:45Z Danny and Max Milbers $
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

/**
 * The class is is used to manage the countries in your store.
 *
 */
class ps_state extends vmAbstractObject  {
	var $key = 'state_id';
	var $_table_name = '#__{vm}_state';
	
	/**
	 * Returns the Sate Name and ID of the state specified by $code
	 * 
	 * @author Max Milbers 
	 * @param string $code
	 * @return ps_DB
	 */
	function &get_state_by_code( $code ) {
		$db = new ps_DB();
		$state_code_type = strlen( $code );
		switch ($state_code_type) {
			case 2:
				$state_code_type_field = 'state_2_code';
				break;
			case 3:
				$state_code_type_field = 'state_3_code';
				break;
			default:
				return false;
		}
		$db->query('SELECT `state_id`, `state_name`, `state_2_code`, `state_3_code` 
							FROM `'.$this->getTable().'` WHERE `'.$state_code_type_field.'` = \''.$db->getEscaped($code).'\'' );
		$db->next_record();
		return $db;
	}
}
?>