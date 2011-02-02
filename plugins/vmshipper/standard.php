<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* Shipper plugin for standard shippers, like regular postal services
*
* @version $Id$
* @package VirtueMart
* @subpackage Plugins - shippper
* @copyright Copyright (C) 2004-2011 VirtueMart Team - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
// This is required in order to call the plugins from the backend as well!
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'vmpaymentplugin.php');

class plgVmShipperStandard extends vmShipperPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 */
	function plgVmShipperStandard(&$subject, $config)
	{
		$this->_selement = basename(__FILE__, '.php');
		$this->_createTable();
		parent::__construct($subject, $config);
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * @author Oscar van Eijk
	 */
	protected function _createTable()
	{
		$_scheme = DbScheme::get_instance();
		$_scheme->create_scheme('#__vm_order_shipper_'.$this->_selement);
		$_schemeCols = array(
			 'id' => array (
					 'type' => 'int'
					,'length' => 11
					,'auto_inc' => true
					,'null' => false
			)
			,'order_id' => array (
					 'type' => 'int'
					,'length' => 11
					,'null' => false
			)
			,'shipper_id' => array (
					 'type' => 'text'
					,'null' => false
			)
		);
		$_schemeIdx = array(
			 'idx_order_payment' => array(
					 'columns' => array ('order_id')
					,'primary' => false
					,'unique' => false
					,'type' => null
			)
		);
		$_scheme->define_scheme($_schemeCols);
		$_scheme->define_index($_schemeIdx);
		if (!$_scheme->scheme(true)) {
			JError::raiseWarning(500, $_scheme->get_db_error());
		}
		$_scheme->reset();
	}
}
// No closing tag
