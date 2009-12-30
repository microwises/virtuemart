<?php
/**
 * Manufacturer Model
 *
 * @package	VirtueMart
 * @subpackage Manufacturer
 * @author vhv_alex 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');

/**
 * Model class for manufacturer
 *
 * @package	VirtueMart
 * @subpackage Manufacturer 
 * @author vhv_alex  
 */
class VirtueMartModelManufacturer extends JModel
{    
	/** @var integer Primary key */
    var $_id;          
	/** @var objectlist Manufacturer data */
    var $_data;        
	/** @var integer Total number of manufacturers in the database */
	var $_total;      
	/** @var pagination Pagination for manufacturer list */
	var $_pagination;    
    
    
    /**
     * Constructor for the manufacturer model.
     *
     * The manufacturer id is read and detmimined if it is an array of ids or just one single id.
     *
     */
    function __construct()
    {
        parent::__construct();
        
		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int');		
		
		// Set the state pagination variables
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);        
        
        // Get the country id or array of ids.
		$idArray = JRequest::getVar('cid',  0, '', 'array');
    	$this->setId((int)$idArray[0]);
    }
    
    
    /**
     * Resets the country id and data
     *
     */        
    function setId($id) 
    {
        $this->_id = $id;
        $this->_data = null;
    }	
    
    
	/**
	 * Loads the pagination for the manufacturer table
	 *
     * @return JPagination Pagination for the current list of manufacturers 
	 */
    function getPagination() 
    {
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;
	}
    
    
	/**
	 * Gets the total number of manufacturers
	 *
	 * @return int Total number of manufacturers in the database
	 */
	function _getTotal() 
	{
    	if (empty($this->_total)) {
			$query = 'SELECT `manufacturer_id` FROM `#__vm_manufacturer`';	  		
			$this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }    
    
    
    /** 
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     */ 
	function getManufacturer()
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

  		return $this->_data;		
	}    
    
    
       
	/**
	 * Bind the post data to the manufacturer table and save it
     *
     * @return boolean True is the save was successful, false otherwise. 
	 */
    function store() 
	{
		$table = $this->getTable('manufacturer');

		$data = JRequest::get('post');		
	
		// Bind the form fields to the country table
		if (!$table->bind($data)) {		    
			$this->setError($table->getError());
			return false;	
		}

		// Make sure the country record is valid
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;	
		}
		
		// Save the country record to the database
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;	
		}		
		
		return true;
	}	


	/**
	 * Delete all record ids selected
     *
     * @return boolean True is the delete was successful, false otherwise.      
     */ 	 
	function delete() 
	{
		$manufacturerIds = JRequest::getVar('cid',  0, '', 'array');
    	$table = $this->getTable('manufacturer');
 
    	foreach($manufacturerIds as $manufacturerId) {
       		if (!$table->delete($manufacturerId)) {
           		$this->setError($table->getError());
           		return false;
       		}
    	}
 
    	return true;	
	}	
	
	/**
	 * Publish/Unpublish all the ids selected
     *
     * @param boolean $publishId True is the ids should be published, false otherwise.
     * @return boolean True is the delete was successful, false otherwise.      
     */ 	 
	function publish($publishId = false) 
	{
		$table = $this->getTable('manufacturer');
		$manufacturerIds = JRequest::getVar( 'cid', array(0), 'post', 'array' );				
								
        if (!$table->publish($manufacturerIds, $publishId)) {
			$this->setError($table->getError());
			return false;        		
        }		
        
		return true;		
	}	
	
	
	/**
	 * Retireve a list of countries from the database.
	 * 
     * @param string $onlyPuiblished True to only retreive the publish countries, false otherwise
     * @param string $noLimit True if no record count limit is used, false otherwise
	 * @return object List of manufacturer objects
	 */
	function getManufacturers($onlyPublished=false, $noLimit=false)
	{		
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$option	= 'com_virtuemart';
		
		
		$mf_category_id	= $mainframe->getUserStateFromRequest( $option.'mf_category_id', 'mf_category_id', 0, 'int' );
		$search = $mainframe->getUserStateFromRequest( $option.'search', 'search', '', 'string' );
		
		$where = array();
		if ($mf_category_id > 0) {
			$where[] .= '`#__vm_manufacturer`.`mf_category_id` = '. $mf_category_id;	
		}
		if ( $search ) {
			$where[] .= 'LOWER( `#__vm_manufacturer`.`mf_name` ) LIKE '.$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
		}
		if ($onlyPublished) { 
			$where[] .= '`#__vm_manufacturer`.`published` = 1';			
		}
		
		$where = (count($where) ? ' WHERE '.implode(' AND ', $where) : '');
		
		$query = 'SELECT * FROM `#__vm_manufacturer` '
				. $where;
		
		$query .= ' ORDER BY `#__vm_manufacturer`.`mf_name`';
		if ($noLimit) {
			$this->_data = $this->_getList($query);
		}
		else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}		

		return $this->_data;
	}
	/**
	 * Build manufacturer filter
	 * 
	 * @return object List of manufacturer to build filter select box
	 */
	function getManufacturerDropDown() 
	{
		$db = JFactory::getDBO();
		$query = 'SELECT manufacturer_id as value, mf_name as text'
				.' FROM #__vm_manufacturer';
		$db->setQuery($query);
		
		$options[] = JHTML::_('select.option',  '0', '- '. JText::_( 'Select manufacturer' ) .' -' );
		
		$options = array_merge($options, $db->loadObjectList());
		
		
		return $options;
		
		
		 
	}
	
}
?>