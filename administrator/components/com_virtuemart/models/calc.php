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

class VirtueMartModelCalc extends JModel
{
	/** @var array Array of Primary keys */
    private $_cid;
	/** @var integer Primary key */
    private $_id;
	/** @var objectlist Calculation rule  data */
    private $_data;
	/** @var integer Total number of calculation rules in the database */
	private $_total;
	/** @var pagination Pagination for calculation rules list */
	private $_pagination;


    /**
     * Constructor for the calc model.
     *
     * The calc id is read and detmimined if it is an array of ids or just one single id.
     *
     * @author RickG
     */
    public function __construct(){
        parent::__construct();

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int');

		// Set the state pagination variables
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

        // Get the calc id or array of ids.
		$idArray = JRequest::getVar('cid',  0, '', 'array');
    	$this->setId((int)$idArray[0]);

    }


    /**
     * Resets the calc id and data
     *
     * @author RickG
     */
    public function setId($id){
        $this->_id = $id;
        $this->_data = null;
    }


	/**
	 * Loads the pagination for the country table
	 *
     * @author RickG
     * @return JPagination Pagination for the current list of countries
	 */
    public function getPagination(){
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;
	}


	/**
	 * Gets the total number of countries
	 *
     * @author RickG
	 * @return int Total number of countries in the database
	 */
	public function _getTotal() {
    	if (empty($this->_total)) {
			$query = 'SELECT `calc_id` FROM `#__vm_calc`';
			$this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }


    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author Max Milbers
     */
	public function getCalc(){
		if(empty($this->_db)) $this->_db = JFactory::getDBO();

  		if (empty($this->_data)) {
   			$this->_data = $this->getTable();
   			$this->_data->load((int)$this->_id);
  		}

  		if (!$this->_data) {
   			$this->_data = new stdClass();
   			$this->_id = 0;
  		}
		/* Add the calculation rule categories */
		$q = 'SELECT `calc_category` FROM #__vm_calc_category_xref WHERE `calc_rule_id` = "'.$this->_id.'"';
		$this->_db->setQuery($q);
		$this->_data->calc_categories = $this->_db->loadResultArray();

		/* Add the calculation rule shoppergroups */
		$q = 'SELECT `calc_shopper_group` FROM #__vm_calc_shoppergroup_xref WHERE `calc_rule_id` = "'.$this->_id.'"';
		$this->_db->setQuery($q);
		$this->_data->calc_shopper_groups = $this->_db->loadResultArray();

		/* Add the calculation rule countries */
		$q = 'SELECT `calc_country` FROM #__vm_calc_country_xref WHERE `calc_rule_id` = "'.$this->_id.'"';
		$this->_db->setQuery($q);
		$this->_data->calc_countries = $this->_db->loadResultArray();

		/* Add the calculation rule states */
		$q = 'SELECT `calc_state` FROM #__vm_calc_state_xref WHERE `calc_rule_id`= "'.$this->_id.'"';
		$this->_db->setQuery($q);
		$this->_data->calc_states = $this->_db->loadResultArray();

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
	public function getCalcs($onlyPublished=false, $noLimit=false){
		if(empty($this->_db)) $this->_db = JFactory::getDBO();

		$query = 'SELECT * FROM `#__vm_calc` ';
		if ($onlyPublished) {
			$query .= 'WHERE `#__vm_calc`.`published` = 1';
		}
		$query .= ' ORDER BY `#__vm_calc`.`calc_name`';
		if ($noLimit) {
			$this->_data = $this->_getList($query);
		}
	 	else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
		foreach ($this->_data as $data){

			/* Write the first 5 categories in the list */
			$data->calcCategoriesList = modelfunctions::buildGuiList('calc_category','#__vm_calc_category_xref','calc_rule_id',$data->calc_id,'category_name','#__vm_category','category_id');

			/* Write the first 5 shoppergroups in the list */
			$data->calcShoppersList = modelfunctions::buildGuiList('calc_shopper_group','#__vm_calc_shoppergroup_xref','calc_rule_id',$data->calc_id,'shopper_group_name','#__vm_shopper_group','shopper_group_id');

			/* Write the first 5 countries in the list */
			$data->calcCountriesList = modelfunctions::buildGuiList('calc_country','#__vm_calc_country_xref','calc_rule_id',$data->calc_id,'country_name','#__vm_country','country_id');

			/* Write the first 5 states in the list */
			$data->calcStatesList = modelfunctions::buildGuiList('calc_state','#__vm_calc_state_xref','calc_rule_id',$data->calc_id,'state_name','#__vm_state','state_id');

			$query = 'SELECT `currency_name` FROM `#__vm_currency` WHERE `currency_id` = "'.$data->calc_currency.'" ';
			$this->_db->setQuery($query);
			$data->currencyName = $this->_db->loadResult();

		}

//		echo (print_r($this->_data).'<br /><br />');
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
		$alt 	= $row->$variable ? JText::_('VM_PUBLISHED' ) : JText::_('VM_UNPUBLISHED' );
		$action = $row->$variable ? JText::_('VM_UNPUBLISH_ITEM' ) : JText::_('VM_PUBLISH_ITEM' );

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
		$table = $this->getTable('calc');

		$data = JRequest::get('post');

		// Convert selected dates to MySQL format for storing.
		$startDate = JFactory::getDate($data['publish_up']);
		$data['publish_up'] = $startDate->toMySQL();
//		if ($data['publish_down'] == '' or $data['publish_down']==0){
		if (empty($data['publish_down']) || trim($data['publish_down']) == JText::_('VM_NEVER')){
			$this->_db = JFactory::getDBO();
			$data['publish_down']	= $this->_db->getNullDate();
		} else {
			$expireDate = JFactory::getDate($data['publish_down']);
			$data['publish_down']	= $expireDate->toMySQL();
		}

		$modified = JFactory::getDate();
		$data['modified']=$modified->toMySQL();

		// Bind the form fields to the calculation table
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Make sure the calculation record is valid
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Save the record to the database
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
		modelfunctions::storeArrayData('#__vm_calc_category_xref','calc_rule_id','calc_category', $table->calc_id,$data["calc_categories"]);
		modelfunctions::storeArrayData('#__vm_calc_shoppergroup_xref','calc_rule_id','calc_shopper_group', $table->calc_id,$data["shopper_group_id"]);
		modelfunctions::storeArrayData('#__vm_calc_country_xref','calc_rule_id','calc_country', $table->calc_id,$data["country_id"]);
		modelfunctions::storeArrayData('#__vm_calc_state_xref','calc_rule_id','calc_state', $table->calc_id,$data["state_id"]);

		return $table->calc_id;
	}


	/**
	 * Delete all record ids selected
     *
     * @author Max Milbers
     * @return boolean True is the delete was successful, false otherwise.
     */
	public function delete() {
		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
		return modelfunctions::delete('cid','calc');

	}


	/**
	 * Publish/Unpublish all the ids selected
     *
     * @author Max Milbers
     * @param boolean $publishId True is the ids should be published, false otherwise.
     * @return boolean True is the delete was successful, false otherwise.
     */
	public function publish($publishId = false)
	{
		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
		return modelfunctions::publish('cid','calc',$publishId);

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
			$query = 'SELECT calc_shopper_published
					  FROM #__vm_calc
					  WHERE calc_id = '. $quotedId;

			$this->_db->setQuery($query);
			$calc = $this->_db->loadObject();

			$publish = ($calc->calc_shopper_published > 0) ? 0 : 1;

			$query = 'UPDATE #__vm_calc
					  SET calc_shopper_published = '.$publish.'
					  WHERE calc_id = '.$quotedId;

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
			$query = 'SELECT calc_vendor_published
					  FROM #__vm_calc
					  WHERE calc_id = '. $quotedId;

			$this->_db->setQuery($query);
			$calc = $this->_db->loadObject();

			$publish = ($calc->calc_vendor_published > 0) ? 0 : 1;

			$query = 'UPDATE #__vm_calc
					  SET calc_vendor_published = '.$publish.'
					  WHERE calc_id = '.$quotedId;

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


		$q = 'SELECT * FROM `#__vm_calc` WHERE ';
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

	function getDBDiscounts() {

		return self::getRule(array('DBTax','DBTaxBill'));;
	}

	function getDADiscounts() {

		return self::getRule(array('DATax','DATaxBill'));;
	}
}