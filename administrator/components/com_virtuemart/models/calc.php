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
    public function __construct()
    {
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
    public function setId($id)
    {
        $this->_id = $id;
        $this->_data = null;
    }


	/**
	 * Loads the pagination for the country table
	 *
     * @author RickG
     * @return JPagination Pagination for the current list of countries
	 */
    public function getPagination()
    {
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
	public function _getTotal() 
	{
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
	public function getCalc()
	{	
		$db = JFactory::getDBO();
     
  		if (empty($this->_data)) {
   			$this->_data = $this->getTable();
   			$this->_data->load((int)$this->_id);
  		}
  
  		if (!$this->_data) {
   			$this->_data = new stdClass();
   			$this->_id = 0;
   			$this->_data = null;
  		}
		/* Add the calculation rule categories */
		$q = 'SELECT `calc_category` FROM #__vm_calc_category_xref WHERE `calc_rule_id` = "'.$this->_id.'"';
		$db->setQuery($q);
		$this->_data->calc_categories = $db->loadResultArray();

		/* Add the calculation rule shoppergroups */
		$q = 'SELECT `calc_shopper_group` FROM #__vm_calc_shoppergroup_xref WHERE `calc_rule_id` = "'.$this->_id.'"';
		$db->setQuery($q);
		$this->_data->calc_shopper_groups = $db->loadResultArray();

		/* Add the calculation rule countries */
		$q = 'SELECT `calc_countries` FROM #__vm_calc_countries_xref WHERE `calc_rule_id` = "'.$this->_id.'"';
		$db->setQuery($q);
		$this->_data->calc_countries = $db->loadResultArray();
		
		/* Add the calculation rule states */
		$q = 'SELECT `calc_states` FROM #__vm_calc_states_xref WHERE `calc_rule_id`= "'.$this->_id.'"';
		$db->setQuery($q);
		$this->_data->calc_states = $db->loadResultArray();
				
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
	public function getCalcs($onlyPublished=false, $noLimit=false)
	{		
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

		$db = JFactory::getDBO();
		$config =& JFactory::getConfig();
		$tzoffset = $config->getValue('config.offset');
		foreach ($this->_data as $data){

			/* Write the first 5 categories in the list */
			$q = 'SELECT `calc_category` FROM #__vm_calc_category_xref WHERE `calc_rule_id` = "'.$data->calc_id.'"';
			$db->setQuery($q);
			$calcCategories = $db->loadResultArray();
			if(isset($calcCategories)){
				$calcCategoriesList='';
				$i=0;
				foreach ($calcCategories as $value) {
					$q = 'SELECT category_name FROM #__vm_category WHERE category_id = "'.$value.'"';
					$db->setQuery($q);
					$categoryName = $db->loadResult();
					$calcCategoriesList .= $categoryName. ', ';
					$i++;
					if($i>4) break;
				}
				$data->calcCategoriesList = substr($calcCategoriesList,0,-2);
			}
			
			/* Write the first 5 shoppergroups in the list */
			$q = 'SELECT `calc_shopper_group` FROM #__vm_calc_shoppergroup_xref WHERE `calc_rule_id` = "'.$data->calc_id.'"';
			$db->setQuery($q);
			$calcShoppers = $db->loadResultArray();
			if(isset($calcShoppers)){
				$calcShoppersList='';
				$i=0;
				foreach ($calcShoppers as $value) {
					$q = 'SELECT shopper_group_name FROM #__vm_shopper_group WHERE shopper_group_id = "'.$value.'"';
					$db->setQuery($q);
					$shopperName = $db->loadResult();
					$calcShoppersList .= $shopperName. ', ';
					$i++;
					if($i>4) break;
				}
				$data->calcShoppersList = substr($calcShoppersList,0,-2);
			}
			
			/* Write the first 5 shoppergroups in the list */
			$q = 'SELECT `calc_shopper_group` FROM #__vm_calc_shoppergroup_xref WHERE `calc_rule_id` = "'.$data->calc_id.'"';
			$db->setQuery($q);
			$calcShoppers = $db->loadResultArray();
			if(isset($calcShoppers)){
				$calcShoppersList='';
				$i=0;
				foreach ($calcShoppers as $value) {
					$q = 'SELECT shopper_group_name FROM #__vm_shopper_group WHERE shopper_group_id = "'.$value.'"';
					$db->setQuery($q);
					$shopperName = $db->loadResult();
					$calcShoppersList .= $shopperName. ', ';
					$i++;
					if($i>4) break;
				}
				$data->calcShoppersList = substr($calcShoppersList,0,-2);
			}
			
			/* Write the first 5 countries in the list */
			$q = 'SELECT `calc_shopper_group` FROM #__vm_calc_shoppergroup_xref WHERE `calc_rule_id` = "'.$data->calc_id.'"';
			$db->setQuery($q);
			$calcShoppers = $db->loadResultArray();
			if(isset($calcShoppers)){
				$calcShoppersList='';
				$i=0;
				foreach ($calcShoppers as $value) {
					$q = 'SELECT shopper_group_name FROM #__vm_shopper_group WHERE shopper_group_id = "'.$value.'"';
					$db->setQuery($q);
					$shopperName = $db->loadResult();
					$calcShoppersList .= $shopperName. ', ';
					$i++;
					if($i>4) break;
				}
				$data->calcShoppersList = substr($calcShoppersList,0,-2);
			}
			
			/* Write the first 5 states in the list */
			$q = 'SELECT `calc_shopper_group` FROM #__vm_calc_shoppergroup_xref WHERE `calc_rule_id` = "'.$data->calc_id.'"';
			$db->setQuery($q);
			$calcShoppers = $db->loadResultArray();
			if(isset($calcShoppers)){
				$calcShoppersList='';
				$i=0;
				foreach ($calcShoppers as $value) {
					$q = 'SELECT shopper_group_name FROM #__vm_shopper_group WHERE shopper_group_id = "'.$value.'"';
					$db->setQuery($q);
					$shopperName = $db->loadResult();
					$calcShoppersList .= $shopperName. ', ';
					$i++;
					if($i>4) break;
				}
				$data->calcShoppersList = substr($calcShoppersList,0,-2);
			}
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
	public function published( $row, $i, $variable = 'published' )
	{
		$imgY = 'tick.png';
		$imgX = 'publish_x.png';
		$img 	= $row->$variable ? $imgY : $imgX;
		$task 	= $row->$variable ? 'unpublish' : 'publish';
		$alt 	= $row->$variable ? JText::_( 'Published' ) : JText::_( 'Unpublished' );
		$action = $row->$variable ? JText::_( 'Unpublish Item' ) : JText::_( 'Publish item' );

		$href = '
		<a title="'. $action .'">
		<img src="images/'. $img .'" border="0" alt="'. $alt .'" /></a>'
		;
		return $href;
	}
	
        
	/**
	 * Bind the post data to the calculation table and save it
     *
     * @author RickG, Max Milbers
     * @return boolean True is the save was successful, false otherwise. 
	 */
    public function store() 
	{
		$table = $this->getTable('calc');

		$data = JRequest::get('post');		
		
		// Convert selected dates to MySQL format for storing.
		$startDate = JFactory::getDate($data['publish_up']);
		$data['publish_up'] = $startDate->toMySQL();
		$expireDate = JFactory::getDate($data['publish_down']);
		$data['publish_down'] = $expireDate->toMySQL();
		
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
		
		// Save the country record to the database
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}
		
		$db = JFactory::getDBO();
		
		/* Store categories */
		/* Delete old category links */
		$q  = 'DELETE FROM `#__vm_calc_category_xref` ';
		$q .= 'WHERE `calc_rule_id` = "'.$data["calc_id"].'" ';
		$db->setQuery($q);
		$db->Query();

		/* Store the new categories */
		foreach( $data["calc_categories"] as $category_id ) {
			$q  = 'INSERT INTO `#__vm_calc_category_xref` ';
			$q .= '(calc_rule_id,calc_category) ';
			$q .= 'VALUES ("'.$data["calc_id"].'","'. $category_id . '")';
			$db->setQuery($q); 
			$db->query();
		}
		
		/* Store Shoppergroups */
		/* Delete old shoppergroup links */
		$q  = 'DELETE FROM `#__vm_calc_shoppergroup_xref` ';
		$q .= 'WHERE `calc_rule_id` = "'.$data["calc_id"].'" ';
		$db->setQuery($q);
		$db->Query();

		/* Store the new categories */
		foreach( $data["shopper_group_id"] as $shoppergrp_id ) {
			$q  = 'INSERT INTO `#__vm_calc_shoppergroup_xref` ';
			$q .= '(calc_rule_id,calc_shopper_group) ';
			$q .= 'VALUES ("'.$data['calc_id'].'","'. $shoppergrp_id . '")';
			$db->setQuery($q); 
			$db->query();
		}
		
		return true;
	}	


	/**
	 * Delete all record ids selected
     *
     * @author Max Milbers
     * @return boolean True is the delete was successful, false otherwise.      
     */ 	 
	public function delete() 
	{
		$calcIds = JRequest::getVar('cid',  0, '', 'array');
    	$table = $this->getTable('calc');
 
    	foreach($calcIds as $calcId) {

    		if (!$table->delete($calcId)) {
        		$this->setError($table->getError());
        		return false;
    		}	
        	else {
        		//$this->setError('Could not remove country states!');
        		return true;
        	}
    	}
 
    	return true;	
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
		$table = $this->getTable('calc');
		$calcIds = JRequest::getVar( 'cid', array(0), 'post', 'array' );				
		
        if (!$table->publish($calcIds, $publishId)) {
			$this->setError($table->getError());
			return false;        		
        }		
        
		return true;		
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
	
}