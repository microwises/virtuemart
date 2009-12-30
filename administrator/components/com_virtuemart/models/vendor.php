<?php
/**
* @package		VirtueMart
* @license		GNU/GPL, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');


/**
* Model for VirtueMart Vendors
*
* @package		VirtueMart
*/
class VirtueMartModelVendor extends JModel
{
    /**
    * Vendor Id
    *
    * @var $_id;
    */
    var $_id;
    
    /**
    * Vendor detail record
    *
    * @var object;
    */
    var $_vendor;    
    
    
    /**
    * Constructor for the Vendor model.
    */
    function __construct()
    {
        parent::__construct();
        
        $cid = JRequest::getVar('cid', false, 'DEFAULT', 'array');
        if ($cid) {
            $id = $cid[0];
        }
        else {
            $id = JRequest::getInt('id', 1);
        }
        
        $this->setId($id);
    }
    
    
    /**
    * Resets the Vendor ID and data
    */        
    function setId($id=1) 
    {
        $this->_id = $id;
        $this->_vendor = null;
    }
    
    
    /**
	* Retrieve the vendor details from the database.
	* 
	* @return object Vendor details
	*/
	function getVendor($vendId=1) 
	{
        if (!$this->_vendor) {
        	//The DB should get with the ps_vendor.php
        	//and the functions in this class must be rewritten OR I port the ps_vendor class in this class
        	// by Max Milbers
            $db =& $this->getDBO();
            $query = 'SELECT * FROM `#__vm_vendor` ';
            $query .=  'WHERE `vendor_id`=' . $vendId;
            $db->setQuery($query);
            
            $this->_vendor = $db->loadObject();
        }
        return $this->_vendor;   
	}
	
	/**
	* Retrieve a list of vendors
	* 
	* @author: RolandD
	* @return object List of vendors
	*/
	public function getVendors() {
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__vm_vendor`';
		$db->setQuery($q);
		return $db->loadObjectList();
	}
	
	/**
	* Retrieve the user ID by vendor ID 
	* 
	* @author jseros
	* @param $vendId The vendor ID
	* @return user ID by vendor
	*/
	public function getUserId($vendId = 0) {
		$sql = "SELECT user_id
   				FROM #__vm_auth_user_vendor
  				WHERE vendor_id = ". $this->_db->Quote((int)$vendId) ."";
   				
   		$this->_db->setQuery($sql);
   		$result = $this->_db->loadObject();
   		
   		return (isset($result->user_id) ? $result->user_id : 0);
	}
	
	
	/**
	 * Bind the post data to the vendor table and save it
     *
     * @author RickG	
     * @return boolean True is the save was successful, false otherwise. 
	 */
    function store($data) 
	{
		$table = $this->getTable('vendor');	
	
		// Bind the form fields to the unser info table
		if (!$table->bind($data)) {		    
			$this->setError($table->getError());
			return false;	
		}

		// Make sure the user info record is valid
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;	
		}
		
		// Save the user info record to the database
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;	
		}		
		
		return true;
	}	
}
?>