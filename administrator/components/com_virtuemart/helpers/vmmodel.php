<?php
/**
 * abstract model class containing some standards
 *  get,store,delete,publish and pagination
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

defined('_JEXEC') or die();


class VmModel extends JModel {

	protected $_id 			= 0;
	protected $_data 		= null;
	protected $_pagination 	= 0;

	protected $_maintable 	= '';	// something like #__virtuemart_calcs
	protected $_maintablename = '';
	protected $_idName		= '';
	protected $_cidName		= 'cid';

    public function __construct($cidName='cid'){
        parent::__construct();

        $this->_cidName = $cidName;

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').JRequest::getVar('view').'.limitstart', 'limitstart', 0, 'int');

		// Set the state pagination variables
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

        // Get the id or array of ids.
		$idArray = JRequest::getVar($this->_cidName,  0, '', 'array');
    	$this->setId((int)$idArray[0]);

    }

    public function setMainTable($maintablename,$maintable=0){

    	$this->_maintablename = $maintablename;
    	if(empty($maintable)){
    		$this->_maintable = '#__virtuemart_'.$maintablename;
    	} else {
    		$this->_maintable = $maintable;
    	}
		$defaultTable = $this->getTable($this->_maintablename);
		$this->_idName = $defaultTable->getKeyName();
    }

    public function setIdName($idName){
    	$this->_idName = $idName;
    }

    public function getIdName(){
    	return $this->_idName;
    }

   	public function getId(){
    	return $this->_id;
   	}

	 /**
     * Resets the id and data
     *
     * @author Max Milbers
     */
    function setId($id){
    	if($this->_id!=$id){
			$this->_id = (int)$id;
			$this->_data = null;
    	}
    	return $this->_id;
    }

	/**
	 * Loads the pagination
	 *
	 * @author Max Milbers
	 */
    public function getPagination() {
		if ($this->_pagination == null) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

	/**
	 * Gets the total number of countries
	 *
     * @author Max Milbers
	 * @return int Total number of entries in the database
	 */
	public function getTotal() {

    	if (empty($this->_total)) {

			$query = 'SELECT '.$this->_db->nameQuote($this->_idName).' FROM '.$this->_db->nameQuote($this->_maintable);
			$this->_db->setQuery( $query );
			if(!$this->_db->query()){
				if(empty($this->_maintable)) $this->setError('Model '.get_class( $this ).' has no maintable set');
				$this->_total = 0;
			} else {
				$this->_total = $this->_db->getNumRows();
			}


//			$this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }

    /**
     *
     * @author Max Milberes
     *
     */

    public function getData(){

    	if (empty($this->_data)) {
		    $this->_data = $this->getTable($this->_maintablename);
			$this->_data->load($this->_id);

			//just an idea
//    		if(isset($this->_data->virtuemart_vendor_id && empty($this->_data->virtuemart_vendor_id)){
//		    	if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
//		    	$this->_data->virtuemart_vendor_id = VirtueMartModelVendor::getLoggedVendor();;
//		    }
		}

		return $this->_data;
    }


    public function store($data){

		$table = $this->getTable($this->_maintablename);

		if($data = $table->bindChecknStore($data)){
			if(is_object($data)){
				$_idName = $this->_idName;
				return $data->$_idName;
	    	} else {
	    		return $data[$this->_idName];
	    	}

		} else {
			$app = JFactory::getApplication();
			$app->enqueueMessage($table->getError());
			return false;
		}

	}

	/**
	 * Delete all record ids selected
     *
     * @author Max Milbers
     * @return boolean True is the delete was successful, false otherwise.
     */
	public function remove() {

		$table =& $this->getTable($this->_maintablename);
		$ids = JRequest::getVar($this->_cidName,  0, '', 'array');

		foreach($ids as $id) {
		    if (!$table->delete($id)) {
				$this->setError($table->getError());
				return false;
		    }
		}
		return true;
	}

	/**
	 *
	 * @author Max Milbers
	 * @param unknown_type $idName
	 * @param unknown_type $tablename
	 * @param unknown_type $publishId
	 */
	function publish($publishId = false) {

		$table = $this->getTable($this->_maintablename);

		$ids = JRequest::getVar( $this->_cidName, array(0), 'post', 'array' );
		if (!$table->publish($ids, $publishId)) {
			$this->setError($table->getError());
			return false;
		}

		return true;
    }

	/**
	 * toggle (0/1) a field
	 * or invert by $val for multi IDS;
	 * @author Patrick Kohl
	 * @param string $field the field to toggle
	 * @param string $postName the name of id Post  (Primary Key in table Class constructor)
	 */

	function toggle($field,$val = NULL, $postName='cid' ) {
		$ok = true;
		$table =& $this->getTable($this->_maintablename);

		$ids = JRequest::getVar( $postName, array(0), 'post', 'array' );
		foreach($ids as $id){
			$table->load( $id );
			if ($val == NULL) {
				if ($table->$field ==0) $table->$field = 1 ;
				else $table->$field = 0;
			} else {
				$table->$field = $val;
			}

			if (!$table->store()) {
				JError::raiseError(500, $table->getError() );
				$ok = false;
			}
		}

		return $ok;

    }
	/**
	 * Original From Joomla Method to move a weblink
	 * @ Author Kohl Patrick
	 * @$filter the field to group by
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function move($direction, $filter=null)
	{
		$table =& $this->getTable($this->_maintablename);
		if (!$table->load($this->_id)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		if ($filter) ' '.$filter.' = '.(int) $table->$filter.' AND published >= 0 ';
		if (!$table->move( $direction, $filter )) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}
	/**
	 * Original From Joomla Method to move a weblink
	 * @ Author Kohl Patrick
	 * @$filter the field to group by
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function saveorder($cid = array(), $order, $filter = null)
	{
		$table =& $this->getTable($this->_maintablename);
		$groupings = array();

		// update ordering values
		for( $i=0; $i < count($cid); $i++ )
		{
			$table->load( (int) $cid[$i] );
			// track categories
			if ($filter) $groupings[] = $table->$filter;

			if ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];
				if (!$table->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		// execute updateOrder for each parent group
		if ($filter) {
			$groupings = array_unique( $groupings );
			foreach ($groupings as $group){
				$table->reorder(	$filter.' = '.(int) $group);
			}
		}

		return true;
	} 
    //General toggle could be nice, lets see
//	/**
//	 * Switch a toggleable field on or off
//	 *
//	 * @param $field string Database fieldname to toggle
//	 * @param $id array list of primary keys to toggle
//	 * @param $value boolean Value to set
//	 * @return boolean Result
//	 */
//	function toggle($field, $id = array(), $value = 1)
//	{
//		if (count( $id ))
//		{
//			JArrayHelper::toInteger($id);
//			$ids = implode( ',', $id );
//
//			$query = 'UPDATE `#__virtuemart_userfields`'
//				. ' SET `' . $field . '` = '.(int) $value
//				. ' WHERE virtuemart_userfield_id IN ( '.$ids.' )'
//			;
//			$this->_db->setQuery( $query );
//			if (!$this->_db->query()) {
//				$this->setError($this->_db->getErrorMsg());
//				return false;
//			}
//		}
//		return true;
//	}

}