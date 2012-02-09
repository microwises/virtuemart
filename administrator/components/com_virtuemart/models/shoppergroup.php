<?php
/**
*
* Data model for shopper group
*
* @package	VirtueMart
* @subpackage ShopperGroup
* @author Markus Öhler
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

	    if (empty($this->_data)) {
	      $this->_data = $this->getTable('shoppergroups');
	      $this->_data->load((int) $this->_id);
	      if(!empty($this->_data->price_display)){
	      	$this->_data->price_display = unserialize($this->_data->price_display);
	      } else{
	      	if(!class_exists('JParameter')) require(JPATH_VM_LIBRARIES.DS.'joomla'.DS.'html'.DS.'parameter.php' );
	      	$this->_data->price_display = new JParameter('');
	      }
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

	    $query = 'SELECT * FROM `#__virtuemart_shoppergroups` ORDER BY `virtuemart_vendor_id`,`shopper_group_name` ';

		if ($noLimit) {
			$this->_data = $this->_getList($query);
		}
		else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

	    return $this->_data;
    }

   function store($data){

   	$myfields = array('basePrice','variantModification','basePriceVariant',
   										'basePriceWithTax','basePriceWithTax','discountedPriceWithoutTax',
   										'salesPriceWithDiscount','salesPrice','priceWithoutTax',
   										'discountAmount','taxAmount');

   	$param ='show_prices='.$data['show_prices']."\n";
   	foreach($myfields as $fields){
   		$param .= $fields.'='.$data[$fields]."\n";		//attention there must be doublequotes
   		$param .= $fields.'Text='.$data[$fields.'Text']."\n";
   		$param .= $fields.'Rounding='.$data[$fields.'Rounding']."\n";
   	}

   	if(!class_exists('JParameter')) require(JPATH_VM_LIBRARIES.DS.'joomla'.DS.'html'.DS.'parameter.php' );
		$jparam = new JParameter($param);
   	$data['price_display'] = serialize(new JParameter($param));

   	return parent::store($data);
   }

	function makeDefault($id,$kind = 1) {
		$this->_db->setQuery('UPDATE  `#__virtuemart_shoppergroups`  SET `default` = 0');
		if (!$this->_db->query()) return ;
		$this->_db->setQuery('UPDATE  `#__virtuemart_shoppergroups`  SET `default` = "'.$kind.'" WHERE virtuemart_shoppergroup_id='.(int)$id);
		if (!$this->_db->query()) return ;
		return true;
	}

	/**
	 *
	 * Get default shoppergroup for anonymous and non anonymous
	 * @param unknown_type $kind
	 */
	function getDefault($kind = 1){

		$kind = $kind + 1;
		$this->_db->setQuery('SELECT * FROM `#__virtuemart_shoppergroups` WHERE `default` = "'.$kind.'" AND `virtuemart_vendor_id` = "1" ');

		if(!$res = $this->_db->loadObject()){
			$app = JFactory::getApplication();
			$app->enqueueMessage('Attention no standard shopper group set '.$this->_db->getErrorMsg());
		} else {
			return $res;
		}

	}

	function remove($ids){

		jimport( 'joomla.utilities.arrayhelper' );
		JArrayHelper::toInteger($ids);

		$table = $this->getTable($this->_maintablename);

		$defaultSgId = $this->getDefault(0);
		$anonymSgId = $this->getDefault(1);

		foreach($ids as $id){

			//Test if shoppergroup is default
			if($id == $defaultId->virtuemart_shoppergroup_id){
				$this->_db->setQuery('SELECT shopper_group_name FROM `#__virtuemart_shoppergroups`  WHERE `virtuemart_shoppergroup_id` = "'.(int)$id.'"');
				$name = $this->_db->loadResult();
				vmError(JText::sprintf('COM_VIRTUEMART_SHOPPERGROUP_DELETE_CANT_DEFAULT',$name,$id));
				continue;
			}

			//Test if shoppergroup is default
			if($id == $anonymSgId->virtuemart_shoppergroup_id){
				$this->_db->setQuery('SELECT shopper_group_name FROM `#__virtuemart_shoppergroups`  WHERE `virtuemart_shoppergroup_id` = "'.(int)$id.'"');
				$name = $this->_db->loadResult();
				vmError(JText::sprintf('COM_VIRTUEMART_SHOPPERGROUP_DELETE_CANT_DEFAULT',$name,$id));
				continue;
			}

			//Test if shoppergroup has members
			$this->_db->setQuery('SELECT * FROM `#__virtuemart_vmuser_shoppergroups`  WHERE `virtuemart_shoppergroup_id` = "'.(int)$id.'"');
			if($this->_db->loadResult()){
				$this->_db->setQuery('SELECT shopper_group_name FROM `#__virtuemart_shoppergroups`  WHERE `virtuemart_shoppergroup_id` = "'.(int)$id.'"');
				$name = $this->_db->loadResult();
				vmError(JText::sprintf('COM_VIRTUEMART_SHOPPERGROUP_DELETE_CANT_WITH_MEMBERS',$name,$id));
				continue;
			}

			if (!$table->delete($id)) {
				vmError(get_class( $this ).'::remove '.$table->getError());
				return false;
		    }
		}

		return true;
	}

	/**
	 * Retrieves the Shopper Group Info of the SG specified by $id
	 *
	 * @todo Vendor ID
	 * @param int $id
	 * @param boolean $default_group
	 * @return array
	 */
  	function getShoppergroupById($id, $default_group = false) {
    	$virtuemart_vendor_id = 1;
    	$db = JFactory::getDBO();

    	$q =  'SELECT `#__virtuemart_shoppergroups`.`virtuemart_shoppergroup_id`, `#__virtuemart_shoppergroups`.`shopper_group_name`, `default` AS default_shopper_group FROM `#__virtuemart_shoppergroups`';

    	if (!empty($id) && !$default_group) {
      		$q .= ', `#__virtuemart_vmuser_shoppergroups`';
      		$q .= ' WHERE `#__virtuemart_vmuser_shoppergroups`.`virtuemart_user_id`="'.(int)$id.'" AND ';
      		$q .= '`#__virtuemart_shoppergroups`.`virtuemart_shoppergroup_id`=`#__virtuemart_vmuser_shoppergroups`.`virtuemart_shoppergroup_id`';
    	}
    	else {
    		$q .= ' WHERE `#__virtuemart_shoppergroups`.`virtuemart_vendor_id`="'.(int)$virtuemart_vendor_id.'" AND `default`="2"';
    	}

    	$db->setQuery($q);
    	return $db->loadAssoc();
  	}

}
// pure php no closing tag