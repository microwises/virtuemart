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

class VirtueMartModelCalc extends VmModel {


    /**
     * Constructor for the calc model.
     *
     * The calc id is read and detmimined if it is an array of ids or just one single id.
     *
     * @author RickG
     */
    public function __construct(){
        parent::__construct();
		$this->setMainTable('calcs');

    }


    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author Max Milbers
     */
	public function getCalc(){
		if(empty($this->_db)) $this->_db = JFactory::getDBO();

  		if (empty($this->_data)) {
   			$this->_data = $this->getTable('calcs');
   			$this->_data->load((int)$this->_id);
  		}

		$xrefTable = $this->getTable('calc_categories');
		if (!$this->_data->calc_categories = $xrefTable->load($this->_id)) {
			$this->setError($xrefTable->getError());
		}

		$xrefTable = $this->getTable('calc_shoppergroups');
		if (!$this->_data->virtuemart_shoppergroup_ids = $xrefTable->load($this->_id)) {
			$this->setError($xrefTable->getError());
		}

		$xrefTable = $this->getTable('calc_countries');
		if (!$this->_data->calc_countries = $xrefTable->load($this->_id)) {
			$this->setError($xrefTable->getError());
		}

		$xrefTable = $this->getTable('calc_states');
		if (!$this->_data->virtuemart_state_ids = $xrefTable->load($this->_id)) {
			$this->setError($xrefTable->getError());
		}

		if($errs = $this->getErrors()){
			$app = JFactory::getApplication();
			foreach($errs as $err){
				$app->enqueueMessage($err);
			}
		}

  		return $this->_data;
	}

	/**
	 * Retireve a list of calculation rules from the database.
	 *
     * @author Max Milbers
     * @param string $onlyPuiblished True to only retreive the published Calculation rules, false otherwise
     * @param string $noLimit True if no record count limit is used, false otherwise
	 * @return object List of calculation rule objects
	 */
	public function getCalcs($onlyPublished=false, $noLimit=false){
		if(empty($this->_db)) $this->_db = JFactory::getDBO();

		$query = 'SELECT * FROM `#__virtuemart_calcs` ';
		if ($onlyPublished) {
			$query .= 'WHERE `#__virtuemart_calcs`.`published` = 1';
		}
		$query .= ' ORDER BY `#__virtuemart_calcs`.`calc_name`';
		if ($noLimit) {
			$this->_data = $this->_getList($query);
		}
	 	else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
		foreach ($this->_data as $data){

			/* Write the first 5 categories in the list */
			$data->calcCategoriesList = modelfunctions::buildGuiList('virtuemart_category_id','#__virtuemart_calc_categories','virtuemart_calc_id',$data->virtuemart_calc_id,'category_name','#__virtuemart_categories','virtuemart_category_id');

			/* Write the first 5 shoppergroups in the list */
			$data->calcShoppersList = modelfunctions::buildGuiList('virtuemart_shoppergroup_id','#__virtuemart_calc_shoppergroups','virtuemart_calc_id',$data->virtuemart_calc_id,'shopper_group_name','#__virtuemart_shoppergroups','virtuemart_shoppergroup_id');

			/* Write the first 5 countries in the list */
			$data->calcCountriesList = modelfunctions::buildGuiList('virtuemart_country_id','#__virtuemart_calc_countries','virtuemart_calc_id',$data->virtuemart_calc_id,'country_name','#__virtuemart_countries','virtuemart_country_id');

			/* Write the first 5 states in the list */
			$data->calcStatesList = modelfunctions::buildGuiList('virtuemart_state_id','#__virtuemart_calc_states','virtuemart_calc_id',$data->virtuemart_calc_id,'state_name','#__virtuemart_states','virtuemart_state_id');

			$query = 'SELECT `currency_name` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id` = "'.$data->calc_currency.'" ';
			$this->_db->setQuery($query);
			$data->currencyName = $this->_db->loadResult();

		}

		return $this->_data;
	}

    /**
     * Publish a field
     *
     * @author Max Milbers
     *
     */
	public function published( $row, $i, $variable = 'published' ){
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
	}


	/**
	 * Bind the post data to the calculation table and save it
     *
     * @author Max Milbers
     * @return boolean True is the save was successful, false otherwise.
	 */
    public function store() {

		$table = $this->getTable('calcs');
		$data = JRequest::get('post');

		// Convert selected dates to MySQL format for storing.
		$startDate = JFactory::getDate($data['publish_up']);
		$data['publish_up'] = $startDate->toMySQL();
//		if ($data['publish_down'] == '' or $data['publish_down']==0){
		if (empty($data['publish_down']) || trim($data['publish_down']) == JText::_('COM_VIRTUEMART_NEVER')){
			$this->_db = JFactory::getDBO();
			$data['publish_down']	= $this->_db->getNullDate();
		} else {
			$expireDate = JFactory::getDate($data['publish_down']);
			$data['publish_down']	= $expireDate->toMySQL();
		}

		if (!$table->bindChecknStore($data)) {
			$this->setError($table->getError());
			return false;
		}

		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');

    	$xrefTable = $this->getTable('calc_categories');
    	if (!$xrefTable->bindChecknStore($data)) {
			$this->setError($xrefTable->getError());
		}

		$xrefTable = $this->getTable('calc_shoppergroups');
    	if (!$xrefTable->bindChecknStore($data)) {
			$this->setError($xrefTable->getError());
		}

		$xrefTable = $this->getTable('calc_countries');
    	if (!$xrefTable->bindChecknStore($data)) {
			$this->setError($xrefTable->getError());
		}

		$xrefTable = $this->getTable('calc_states');
    	if (!$xrefTable->bindChecknStore($data)) {
			$this->setError($xrefTable->getError());
		}

    	$errMsg = $this->_db->getErrorMsg();
		$errs = $this->_db->getErrors();

		if(!empty($errMsg)){

			$errNum = $this->_db->getErrorNum();
			$this->setError('SQL-Error: '.$errNum.' '.$errMsg.' <br /> used query '.$query);
		}

		if(!empty($errs)){
			foreach($errs as $err){
				if(!empty($err)) $this->setError($err);
			}
		}

		if($errs = $this->getErrors()){
			$app = JFactory::getApplication();
			foreach($errs as $err){
				$app->enqueueMessage($err);
			}
		}

		return $table->virtuemart_calc_id;
	}


	/**
	 * Publish/Unpublish all the ids selected
     *
     * @author jseros
     *
     * @return int 1 is the publishing action was successful, -1 is the unsharing action was successfully, 0 otherwise.
     */
	public function shopperPublish($categories){

		foreach ($categories as $id){

			$quotedId = $this->_db->Quote($id);
			$query = 'SELECT `calc_shopper_published`
					  FROM `#__virtuemart_calcs`
					  WHERE virtuemart_calc_id = '. $quotedId;

			$this->_db->setQuery($query);
			$calc = $this->_db->loadObject();

			$publish = ($calc->calc_shopper_published > 0) ? 0 : 1;

			$query = 'UPDATE `#__virtuemart_calcs`
					  SET `calc_shopper_published` = '.$publish.'
					  WHERE virtuemart_calc_id = '.$quotedId;

			$this->_db->setQuery($query);

			if( !$this->_db->query() ){
				$this->setError( $this->_db->getErrorMsg() );
				return false;
			}

		}

		return ($publish ? 1 : -1);
	}



	/**
	 * Publish/Unpublish all the ids selected
     *
     * @author jseros
     *
     * @return int 1 is the publishing action was successful, -1 is the unsharing action was successfully, 0 otherwise.
     */
	public function vendorPublish($categories){

		foreach ($categories as $id){

			$quotedId = $this->_db->Quote($id);
			$query = 'SELECT `calc_vendor_published`
					  FROM `#__virtuemart_calcs`
					  WHERE `virtuemart_calc_id` = '. $quotedId;

			$this->_db->setQuery($query);
			$calc = $this->_db->loadObject();

			$publish = ($calc->calc_vendor_published > 0) ? 0 : 1;

			$query = 'UPDATE `#__virtuemart_calcs`
					  SET `calc_vendor_published` = '.$publish.'
					  WHERE `virtuemart_calc_id` = '.$quotedId;

			$this->_db->setQuery($query);

			if( !$this->_db->query() ){
				$this->setError( $this->_db->getErrorMsg() );
				return false;
			}

		}

		return ($publish ? 1 : -1);
	}


	function getRule($kind){

		if (!is_array($kind)) $kind = array($kind);
		if(empty($this->_db)) $this->_db = JFactory::getDBO();

		$nullDate		= $this->_db->getNullDate();
		$now			=& JFactory::getDate()->toMySQL();


		$q = 'SELECT * FROM `#__virtuemart_calcs` WHERE ';
		foreach ($kind as $field){
			$q .= '`calc_kind`="'.$field.'" OR ';
		}
		$q=substr($q,0,-3);

		$q .= ' AND ( publish_up = '.$this->_db->Quote($nullDate).' OR publish_up <= '.$this->_db->Quote($now).' )' .
			' AND ( publish_down = '.$this->_db->Quote($nullDate).' OR publish_down >= '.$this->_db->Quote($now).' ) ';

		$this->_db->setQuery($q);
		$data = $this->_db->loadObjectList();

		if (!$data) {
   			$data = new stdClass();
  		}
  		return $data;
	}

	function getTaxes() {

		return self::getRule(array('TAX','TaxBill'));
	}

	function getDiscounts(){
		return  self::getRule(array('DATax','DATaxBill','DBTax','DBTaxBill'));
	}

	function getDBDiscounts() {

		return self::getRule(array('DBTax','DBTaxBill'));
	}

	function getDADiscounts() {

		return self::getRule(array('DATax','DATaxBill'));;
	}
}