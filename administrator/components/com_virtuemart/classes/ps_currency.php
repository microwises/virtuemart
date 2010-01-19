<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
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
 * The class is is used to manage the currencies in your store.
 *
 */
class ps_currency {

	function validate_add($d) {
		
		$db = new ps_DB;

		if (!$d["currency_name"]) {
			$GLOBALS['vmLogger']->err( JText::_('VM_CURRENCY_ERR_NAME') );
			return False;
		}
		if (!$d["currency_code"]) {
			$GLOBALS['vmLogger']->err( JText::_('VM_CURRENCY_ERR_CODE') );
			return False;
		}

		if ($d["currency_name"]) {
			$q = "SELECT count(*) as rowcnt from #__{vm}_currency where";
			$q .= " currency_name='" .  $d["currency_name"] . "'";
			$db->setQuery($q);
			$db->query();
			$db->next_record();
			if ($db->f("rowcnt") > 0) {
				$GLOBALS['vmLogger']->err( JText::_('VM_CURRENCY_ERR_EXISTS') );
				return False;
			}
		}
		return True;
	}

	/**************************************************************************
	** name: validate_delete()
	** created by: soeren
	** description:
	** parameters:
	** returns:
	***************************************************************************/
	function validate_delete($d) {
		
		if (!$d["currency_id"]) {
			$GLOBALS['vmLogger']->err( JText::_('VM_CURRENCY_ERR_DELETE_SELECT') );
			return False;
		}
		else {
			return True;
		}
	}

	/**************************************************************************
	** name: validate_update
	** created by:
	** description:
	** parameters:
	** returns:
	***************************************************************************/
	function validate_update($d) {
		
		if (!$d["currency_name"]) {
			$GLOBALS['vmLogger']->err( JText::_('VM_CURRENCY_ERR_NAME') );
			return False;
		}
		if (!$d["currency_code"]) {
			$GLOBALS['vmLogger']->err( JText::_('VM_CURRENCY_ERR_CODE') );
			return False;
		}

		return true;
	}

	/**
	 * creates a new currency record
	 *
	 * @param array $d
	 * @return boolean
	 */
	function add(&$d) {
		
		
		$db = new ps_DB;

		if (!$this->validate_add($d)) {
			return False;
		}
		$fields = array( 'currency_name' => vmGet($d, 'currency_name' ),
					'currency_code' => vmGet($d, 'currency_code' )
		);
		$db->buildQuery('INSERT', '#__{vm}_currency', $fields );
		if( $db->query() ) {
			$GLOBALS['vmLogger']->info( JText::_('VM_CURRENCY_ADDED') );
			$_REQUEST['currency_id'] = $db->last_insert_id();
			return true;	
		}

		return false;

	}

	/**
	 * Updates a Currency Record
	 *
	 * @param array $d
	 * @return boolean
	 */
	function update(&$d) {
		
		
		$db = new ps_DB;

		if (!$this->validate_update($d)) {
			return False;
		}
		$fields = array( 'currency_name' => vmGet($d, 'currency_name' ),
					'currency_code' => vmGet($d, 'currency_code' )
		);
		$db->buildQuery('UPDATE', '#__{vm}_currency', $fields, 'WHERE currency_id='.(int)$d["currency_id"] );
		if( $db->query() ) {
			$GLOBALS['vmLogger']->info( JText::_('VM_CURRENCY_UPDATED') );
			return true;	
		}

		return false;
	}

	/**
	* Controller for Deleting Records.
	*/
	function delete(&$d) {

		if (!$this->validate_delete($d)) {
			$d["error"]=$this->error;
			return False;
		}
		$record_id = $d["currency_id"];

		if( is_array( $record_id)) {
			foreach( $record_id as $record) {
				if( !$this->delete_record( $record, $d ))
				return false;
			}
			return true;
		}
		else {
			return $this->delete_record( $record_id, $d );
		}
	}
	/**
	* Deletes one Record.
	*/
	function delete_record( $record_id, &$d ) {
		global $db;

		$q = 'DELETE from #__{vm}_currency where currency_id='.(int)$record_id;
		$db->query($q);
		return True;
	}

}

?>
