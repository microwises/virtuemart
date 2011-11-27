<?php
/**
*
* Data module for shop calculation rules
*
* @package	VirtueMart
* @subpackage  Calculation tool
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

jimport( 'joomla.application.component.model');

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

class VirtueMartModelPaymentmethod extends VmModel{

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('paymentmethods');
	}

	/**
	 * Gets the virtuemart_paymentmethod_id with a plugin and vendorId
	 *
	 * @author Max Milbers
	 */
	 public function getIdbyCodeAndVendorId($jpluginId,$vendorId=1){
	 	if(!$jpluginId) return 0;
	 	$q = 'SELECT `virtuemart_paymentmethod_id` FROM #__virtuemart_paymentmethods WHERE `payment_jplugin_id` = "'.$jpluginId.'" AND `virtuemart_vendor_id` = "'.$vendorId.'" ';
		$this->_db->setQuery($q);
		return $this->_db->loadResult();
	 }

    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author Max Milbers
     */
	public function getPayment(){

  		if (empty($this->_data)) {
   			$this->_data = $this->getTable('paymentmethods');
   			$this->_data->load((int)$this->_id);
  		}

  		if(empty($this->_data->virtuemart_vendor_id)){
  		   	if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
   			$this->_data->virtuemart_vendor_id = VirtueMartModelVendor::getLoggedVendor();
  		}

  		if(!empty($this->_id)){
			/* Add the paymentmethod shoppergroups */
			$q = 'SELECT `virtuemart_shoppergroup_id` FROM #__virtuemart_paymentmethod_shoppergroups WHERE `virtuemart_paymentmethod_id` = "'.$this->_id.'"';
			$this->_db->setQuery($q);
			$this->_data->virtuemart_shoppergroup_ids = $this->_db->loadResultArray();

			if (VmConfig::isJ15()) {
				$table = '#__plugins';
				$ext_id = 'id';
			} else {
				$table = '#__extensions';
				$ext_id = 'extension_id';
			}
			$q = 'SELECT `params` FROM `' . $table . '` WHERE `' . $ext_id . '` = "'.$this->_data->payment_jplugin_id.'"';
			$this->_db->setQuery($q);

			$this->_data->param = $this->_db->loadResult();

  		} else {
  			$this->_data->virtuemart_shoppergroup_ids = '';
  			$this->_data->param = '';
  		}


  		return $this->_data;
	}

	/**
	 * Retireve a list of calculation rules from the database.
	 *
     * @author Max Milbers
     * @param string $onlyPuiblished True to only retreive the publish Calculation rules, false otherwise
     * @param string $noLimit True if no record count limit is used, false otherwise
	 * @return object List of calculation rule objects
	 */
	public function getPayments($onlyPublished=false, $noLimit=false){


		$where = array();
		if ($onlyPublished) {
			$where[] = ' `#__virtuemart_paymentmethods`.`published` = 1';
		}

		$whereString = '';
		if (count($where) > 0) $whereString = ' WHERE '.implode(' AND ', $where) ;

		$select = ' * FROM `#__virtuemart_paymentmethods_'.VMLANG.'` as l ';
		$joinedTables = ' JOIN `#__virtuemart_paymentmethods`   USING (`virtuemart_paymentmethod_id`) ';
		$this->_data =$this->exeSortSearchListQuery(0,$select,$joinedTables,$whereString,' ',$this->_getOrdering('ordering') );

			//$this->exeSortSearchListQuery(0,'*',' FROM `#__virtuemart_paymentmethods`',$whereString,'',$this->_getOrdering('ordering'));

		if(isset($this->_data)){

			if(!class_exists('shopfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');
			foreach ($this->_data as $data){
				/* Add the paymentmethod shoppergroups */
				$q = 'SELECT `virtuemart_shoppergroup_id` FROM #__virtuemart_paymentmethod_shoppergroups WHERE `virtuemart_paymentmethod_id` = "'.$data->virtuemart_paymentmethod_id.'"';
				$this->_db->setQuery($q);
				$data->virtuemart_shoppergroup_ids = $this->_db->loadResultArray();


				/* Write the first 5 shoppergroups in the list */
				$data->paymShoppersList = shopfunctions::renderGuiList('virtuemart_shoppergroup_id','#__virtuemart_paymentmethod_shoppergroups','virtuemart_paymentmethod_id',$data->virtuemart_paymentmethod_id,'shopper_group_name','#__virtuemart_shoppergroups','virtuemart_shoppergroup_id','shoppergroup',4,0);

			}

		}
		return $this->_data;
	}


	/**
	 * Bind the post data to the paymentmethod tables and save it
     *
     * @author Max Milbers
     * @return boolean True is the save was successful, false otherwise.
	 */
    public function store($data)
	{

		if(!empty($data['params'])){
			foreach($data['params'] as $k=>$v){
				$data[$k] = $v;
			}
		}

	  	if(empty($data['virtuemart_vendor_id'])){
	  	   	if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
	   		$data['virtuemart_vendor_id'] = VirtueMartModelVendor::getLoggedVendor();
	  	}
		// missing string FIX, Bad way ?
		if (VmConfig::isJ15()) {
			$tb = '#__plugins';
			$ext_id = 'id';
		} else {
			$tb = '#__extensions';
			$ext_id = 'extension_id';
		}
		$q = 'SELECT `element` FROM `' . $tb . '` WHERE `' . $ext_id . '` = "'.$data['payment_jplugin_id'].'"';
		$this->_db->setQuery($q);
		$data['payment_element'] = $this->_db->loadResult();

		$table = $this->getTable('paymentmethods');


		if(isset($data['payment_jplugin_id'])){

			JPluginHelper::importPlugin('vmpayment');
			$dispatcher = JDispatcher::getInstance();
			$varsToPushParam = $dispatcher->trigger('plgVmGetDeclaredPluginParams',array('payment',0,$data['payment_jplugin_id']));

			if(!empty($varsToPushParam)){
				vmdebug('hm ',$varsToPushParam);
				foreach($varsToPushParam as $push){
					if($push[0]!==0 and $push[1]!==0){
						$table->setParameterable($push[0],$push[1]);
					}
				}
			}
		}

		$table->bindChecknStore($data);
		$errors = $table->getErrors();
		foreach($errors as $error){
				$this->setError($error);
			}

		$xrefTable = $this->getTable('paymentmethod_shoppergroups');
		$xrefTable->bindChecknStore($data);
		$errors = $xrefTable->getErrors();
		foreach($errors as $error){
			$this->setError($error);
		}

		if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
			JPluginHelper::importPlugin('vmpayment');
			//Add a hook here for other shipment methods, checking the data of the choosed plugin
			$dispatcher = JDispatcher::getInstance();
			$retValues = $dispatcher->trigger('plgVmOnStoreInstallPluginTable', array('payment' , $data['payment_jplugin_id']));

		return $table->virtuemart_paymentmethod_id;
	}


	/**
     * Publish a field
     *
     * @author Max Milbers
     *
     */
/*	public function published( $row, $i, $variable = 'published' )
	{
		$imgY = 'tick.png';
		$imgX = 'publish_x.png';
		$img 	= $row->$variable ? $imgY : $imgX;
		$task 	= $row->$variable ? 'unpublish' : 'publish';
		$alt 	= $row->$variable ? JText::_('COM_VIRTUEMART_PUBLISHED') : JText::_('COM_VIRTUEMART_UNPUBLISHED');
		$action = $row->$variable ? JText::_('COM_VIRTUEMART_UNPUBLISH_ITEM') : JText::_('COM_VIRTUEMART_PUBLISH_ITEM');

		$href = '
		<a title="'. $action .'">
		<img src="images/'. $img .'" border="0" alt="'. $alt .'" /></a>'
		;
		return $href;
	}*/

	/**
	 * Publish/Unpublish all the ids selected
     *
     * @author jseros
     *
     * @return int 1 is the publishing action was successful, -1 is the unsharing action was successfully, 0 otherwise.
     	* @deprecated
     */
	public function changeIsPercentagePublish($quotedId){

//		foreach ($categories as $id){

//			$quotedId = $this->_db->Quote($id);
			$query = 'SELECT discount_is_percentage
					  FROM #__virtuemart_paymentmethods
					  WHERE virtuemart_paymentmethod_id = '. (int)$quotedId;

			$this->_db->setQuery($query);
			$calc = $this->_db->loadObject();

			$publish = ($calc->calc_shopper_published > 0) ? 0 : 1;

			$query = 'UPDATE #__virtuemart_paymentmethods
					  SET discount_is_percentage = '.$publish.'
					  WHERE virtuemart_paymentmethod_id = '.(int)$quotedId;

			$this->_db->setQuery($query);

			if( !$this->_db->query() ){
				$this->setError( $this->_db->getErrorMsg() );
				return false;
			}

//		}

		return ($publish ? 1 : -1);
	}


	/**
	 * Due the new plugin system this should be obsolete
	 * function to render the payment plugin list
	 *
	 * @author Max Milbers
	 *
	 * @param radio list of creditcards
	 * @return html
	 */
	public function renderPaymentList($selectedPaym=0,$selecedCC=0){

		$payms = self::getPayments(false,true);
		$listHTML='';
		foreach($payms as $item){
			$checked='';
			if($item->virtuemart_paymentmethod_id==$selectedPaym){
				$checked='"checked"';
			}
			$listHTML .= '<input type="radio" name="virtuemart_paymentmethod_id" value="'.$item->virtuemart_paymentmethod_id.'" '.$checked.'>'.$item->payment_name.' <br />';
			$listHTML .= ' <br />';
		}

		return $listHTML;

	}

	/**
	 * function to render the creditcardlist
	 *
	 * @author Max Milbers
	 *
	 * @param radio list of creditcards
	 * @return html
	 * @deprecated
	 */

	public function renderCreditCardRadioList($selected,$creditcardIds=0){
		$creditcardIds=0;
		if(!$creditcardIds){
			$creditcardIds = self::getPaymentAcceptedCreditCards();
		}

		if(!class_exists('VirtueMartModelCreditcard')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'creditcard.php');
		$creditcardModel = new VirtueMartModelCreditcard();

		$listHTML='';

		if($creditcardIds){
			foreach($creditcardIds as $ccId){
				$item = $creditcardModel->getCreditCard($ccId);
				$checked='';
	//			foreach($selected as $select){
					if($item->virtuemart_creditcard_id==$selected){
						$checked='"checked"';
					}
	//			}
				$listHTML .= '<input type="radio" name="creditcard" value="'.$item->virtuemart_creditcard_id.'" '.$checked.'>'.$item->creditcard_name.' <br />';
			}
		}
		return $listHTML;
	}

	/**
	 * Gets the ids of accepted creditcards
	 *
	 * @return ids of Creditcards
	 * @deprecated
	 */
	function getPaymentAcceptedCreditCards(){

		$data = array(0);
		if(empty($this->_db))  $this->_db = JFactory::getDBO();

		$query = 'SELECT `virtuemart_creditcard_id` FROM `#__virtuemart_paymentmethod_creditcards` ';
		$query .= 'WHERE `virtuemart_paymentmethod_id` = "'.$this->_id.'"';

		$this->_db->setQuery($query);
		$data = $this->_db->loadResultArray();

		return $data;
	}

	/**
	 * Check if the current payment method accepts credit cards
	 *
	 * @author Max Milbers
	 * @author Oscar van Eijk
	 *
	 * @param integer $virtuemart_paymentmethod_id Payment method ID
	 * @return boolean
	 */
	function hasCreditCard($virtuemart_paymentmethod_id)
	{
		$query = 'SELECT COUNT(*) AS c FROM `#__virtuemart_paymentmethod_creditcards` WHERE `virtuemart_paymentmethod_id`="'.(int)$virtuemart_paymentmethod_id.'"';
		if(empty($this->_db))  $this->_db = JFactory::getDBO();
		$this->_db->setQuery($query);
		$_r = $this->_db->loadAssoc();
		return ($_r['c'] > 0);
	}
}
