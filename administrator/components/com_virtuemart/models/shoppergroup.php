<?php
/**
*
* Data model for shopper group
*
* @package	VirtueMart
* @subpackage ShopperGroup
* @author Markus Öhler
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

// Load the model framework
jimport( 'joomla.application.component.model');

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model class for shopper group
 *
 * @package	VirtueMart
 * @subpackage ShopperGroup
 * @author Markus Öhler
 */
class VirtueMartModelShopperGroup extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct('virtuemart_shoppergroup_id');
		$this->setMainTable('shoppergroups');
	}

    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author Markus Öhler
     */
    function getShopperGroup() {
	    $db = JFactory::getDBO();

	    if (empty($_data)) {
	      $this->_data = $this->getTable('shoppergroups');
	      $this->_data->load((int) $this->_id);
	    }

	    return $this->_data;
    }


    /**
     * Retireve a list of shopper groups from the database.
     *
     * @author Markus Öhler
     * @param string $noLimit True if no record count limit is used, false otherwise
     * @return object List of shopper group objects
     */
    function getShopperGroups($onlyPublished=false, $noLimit = false) {
    	$db = JFactory::getDBO();

	    $query = 'SELECT * FROM '
	      . $db->nameQuote('#__virtuemart_shoppergroups')
	      . 'ORDER BY '
	      . $db->nameQuote('virtuemart_vendor_id')
	      . ','
	      . $db->nameQuote('shopper_group_name')
		;
		if ($noLimit) {
			$this->_data = $this->_getList($query);
		}
		else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

	    return $this->_data;
    }
	
	function makeDefault($id) {
		$this->_db->setQuery('UPDATE  `#__virtuemart_shoppergroups`  SET `default` = 0');
		if (!$this->_db->query()) return ;
		$this->_db->setQuery('UPDATE  `#__virtuemart_shoppergroups`  SET `default` = 1 WHERE virtuemart_shoppergroup_id='.$id);
		if (!$this->_db->query()) return ;
		return true;
	}
	
	function getDefault(){
		$this->_db->setQuery('SELECT * FROM `#__virtuemart_shoppergroups`  WHERE `default` = "1"');
		
		if(!$res = $this->_db->loadObject()){
			$app = JFactory::getApplication();
			$app->enqueueMessage('Attention no standard shopper group set '.$this->_db->getErrorMsg());
		} else {
			return $res;
		}
			
	}
	
	
}
// pure php no closing tag