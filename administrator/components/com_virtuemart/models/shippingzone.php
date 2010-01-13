<?php
/**
 * Data module for the shipping zones
 *
 * @package	VirtueMart
 * @subpackage Shipping
 * @author RickG 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');

/**
 * Model class for shipping zone
 *
 * @package	VirtueMart
 * @subpackage Shipping 
 * @author RickG  
 */
class VirtueMartModelShippingZone extends JModel
{
   /**
    * Shipping Zone Id
    * 
    * @var $_id;
    */
    var $_id;
      
    /**
     * Zone data record
     *
     * @var object;
     */
    var $_data;    
    
    
    /**
     * Constructor for the shpping zone model.
     *
     * The zone id is read and detmimined if it is an array of ids or just one single id.
     *
     * @author RickG 
     */
    function __construct()
    {
        parent::__construct();
        
        $cid = JRequest::getVar('zone_id', false, 'DEFAULT', 'array');
        if ($cid) {
            $id = $cid[0];
        }
        else {
            $id = JRequest::getInt('zone_id', 1);
        }
  
        $this->setId($id);
    }
    
    
    /**
     * Resets the zone id and data
     *
     * @author RickG
     */        
    function setId($id) 
    {
        $this->_id = $id;
        $this->_data = null;
    }	
    
    
    /** 
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author RickG
     */ 
	function getShippingZone()
	{		
		$db =& JFactory::getDBO();		
					
		if (empty($this->_data)) {					
			$query = 'SELECT * ';
			$query .= 'FROM `#__vm_zone_shipping` ';
			$query .= 'WHERE `zone_id` = ' . (int)$this->_id;
			$db->setQuery($query);
			$this->_data = $db->loadObject();
		}
		
		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_id = 0;
			$this->_data = null;
		}

		return $this->_data;
	}    
    
    
    /** 
     * Retrieve a list of zone ids and zone names for use in a HTML select list.
     *
     * @author RickG
     */ 
    function getShippingZoneSelectList()
    {
    	$db =& JFactory::getDBO();
    				
    	$query = 'SELECT `zone_id`, `zone_name` ';
		$query .= 'FROM `#__vm_zone_shipping`';
		$db->setQuery($query);
		
		return $db->loadObjectList();
	}
}
?>