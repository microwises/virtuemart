<?php
/**
 * virtuemart table class, with some additional behaviours.
 *
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

jimport( 'joomla.user.user' );

/**
 * Replaces JTable with some more advanced functions and fitting to the nooku conventions
 *
 * checked_out = locked_by,checked_time = locked_on
 *
 * Enter description here ...
 * @author Milbo
 *
 */
class VmTable extends JTable {


	/** @var int Primary key */
//	private $_id		= 0;

//	private $_pkey 		= 0;
	private $_obkeys	= array();
	private $_unique	= false;
	private $_unique_name = array();

	var $orderingKey = 'ordering';

    function setPrimaryKey($key,$keyForm=0,$langkey=0){
    	$this->setObligatoryKeys('_pkey',$langkey);
    	$this->_pkey = $key;
    	$this->_pkeyForm = empty($keyForm)? $key:$keyForm;
    }

	public function setObligatoryKeys($key,$langkey=0){

		$this->_obkeys[$key] = $langkey;
	}

	public function setUniqueName($name,$langkey){
		$this->_unique = true;
		$this->_obkeys[$name] = $langkey;
		$this->_unique_name[$name] = $langkey;
	}

	public function setLoggable(){
	    $this->created_on = '';
        $this->created_by = 0;
        $this->modified_on = '';
        $this->modified_by = 0;
	}

	public function setLockable(){
		$this->locked_on = '';
		$this->locked_by = 0;
	}

    function setOrderable($key='ordering', $auto=true){
    	$this->orderingKey = $key;
    	$this->orderable = 1;
    }

    /**
     * @author Max Milbers
     * @param
     */
    function check($obligatory=false) {

    	foreach($this->_obkeys as $obkeys => $error){
    		if (empty($this->$obkeys)) {
    			if(empty($error)){
    				$this->setError('Serious error cant save '.$this->_tbl.' without '.$obkeys);
    			} else {
    				$this->setError(JText::_($error));
    			}
            	return false;
        	}
    	}

    	if ($this->_unique) {
		    $db = JFactory::getDBO();
		    foreach($this->_unique_name as $obkeys => $error){

		   		$q = 'SELECT `'.$this->_tbl_key.'`,`'.$obkeys.'` FROM `'.$this->_tbl.'` ';
				$q .= 'WHERE `'.$obkeys.'`="' .  $this->$obkeys . '"';
	            $db->setQuery($q);
			    $unique_id = $db->loadResultArray();

			    $tblKey = $this->_tbl_key;

				if (!empty($unique_id) && $unique_id[0]!=$this->$tblKey) {
					if(empty($error)){
						$this->setError(JText::_($error));
					} else {
						$this->setError('Error cant save '.$this->_tbl.' without a non unique '.$obkeys);
					}

					return false;
				}
		    }

		}

       	$date = JFactory::getDate();
		$today = $date->toMySQL();
		$user = JFactory::getUser();

        if(isset($this->created_on) && empty($this->created_on) ){
        	$this->created_on = $today;
        	$this->created_by = $user->id;
        }

        if(isset($this->modified_on) ){
        	$this->modified_on = $today;
        	$this->modified_by = $user->id;
        }

        if(isset($this->locked_on) ){
        	$this->locked_on = 0;
        }

        //This is a hack for single, shouldnt be used, when we write multivendor there should be message
        if(isset($this->virtuemart_vendor_id)){
        	if(empty($this->virtuemart_vendor_id)) $this->virtuemart_vendor_id = 1;
        }

        return true;
    }

    /**
     * As shortcat
     *
     * @author Max Milbers
     * @param unknown_type $model
     * @param unknown_type $data
     * @param unknown_type $obligatory
     */
    public function bindChecknStore($model, $data, $obligatory=false) {

    	if (!$this->bind($data)) {
			$model->setError($this->getError());
			return false;
		}

		// Make sure the table record is valid
		if (!$this->check($obligatory)) {
			$model->setError($this->getError());
			return false;
		}

		// Save the record to the database
		if (!$this->store()) {
			$model->setError($this->getError());
			return false;
		}
		$tblKey = $this->_tbl_key;
		$data[$this->_tbl_key] = $this->$tblKey;

		return $data;
    }


	/**
	 * Description
	 *
	 * @author Joomla Team, Max Milbers
	 * @access public
	 * @param $dirn
	 * @param $where
	 */
	function move( $dirn, $where='', $orderingkey = 0 ){

		if(!empty($orderingkey)) $this->orderingKey = $orderingkey;

		if (!in_array( $this->orderingKey,  array_keys($this->getProperties())))
		{
			$this->setError( get_class( $this ).' does not support ordering' );
			return false;
		}

		$k = $this->_tbl_key;

		$orderingKey = $this->orderingKey;

		$sql = "SELECT $this->_tbl_key, `'.$this->orderingKey.'` FROM $this->_tbl";

		if ($dirn < 0)
		{
			$sql .= ' WHERE `'.$this->orderingKey.'` < '.(int) $this->$orderingKey;
			$sql .= ($where ? ' AND '.$where : '');
			$sql .= ' ORDER BY `'.$this->orderingKey.'` DESC';
		}
		else if ($dirn > 0)
		{
			$sql .= ' WHERE `'.$this->orderingKey.'` > '.(int) $this->$orderingKey;
			$sql .= ($where ? ' AND '. $where : '');
			$sql .= ' ORDER BY `'.$this->orderingKey.'`';
		}
		else
		{
			$sql .= ' WHERE `'.$this->orderingKey.'` = '.(int) $this->$orderingKey;
			$sql .= ($where ? ' AND '.$where : '');
			$sql .= ' ORDER BY `'.$this->orderingKey.'`';
		}

		$this->_db->setQuery( $sql, 0, 1 );


		$row = null;
		$row = $this->_db->loadObject();
		if (isset($row))
		{
			$query = 'UPDATE '. $this->_tbl
			. ' SET `'.$this->orderingKey.'` = '. (int) $row->$orderingKey
			. ' WHERE '. $this->_tbl_key .' = '. $this->_db->Quote($this->$k)
			;
			$this->_db->setQuery( $query );

			if (!$this->_db->query())
			{
				$err = $this->_db->getErrorMsg();
				JError::raiseError( 500, $err );
			}

			$query = 'UPDATE '.$this->_tbl
			. ' SET `'.$this->orderingKey.'` = '.(int) $this->$orderingKey
			. ' WHERE '.$this->_tbl_key.' = '.$this->_db->Quote($row->$k)
			;
			$this->_db->setQuery( $query );

			if (!$this->_db->query())
			{
				$err = $this->_db->getErrorMsg();
				JError::raiseError( 500, $err );
			}

			$this->ordering = $row->ordering;
		}
		else
		{
			$query = 'UPDATE '. $this->_tbl
			. ' SET `'.$this->orderingKey.'` = '.(int) $this->$orderingKey
			. ' WHERE '. $this->_tbl_key .' = '. $this->_db->Quote($this->$k)
			;
			$this->_db->setQuery( $query );

			if (!$this->_db->query())
			{
				$err = $this->_db->getErrorMsg();
				JError::raiseError( 500, $err );
			}
		}
		return true;
	}

	/**
	 * Returns the ordering value to place a new item last in its group
	 *
	 * @access public
	 * @param string query WHERE clause for selecting MAX(ordering).
	 */
	function getNextOrder ( $where='', $orderingkey = 0 ){

		if(!empty($orderingkey)) $this->orderingKey = $orderingkey;
		if (!in_array( $this->orderingKey, array_keys($this->getProperties()) ))
		{
			$this->setError( get_class( $this ).' does not support ordering' );
			return false;
		}

		$query = 'SELECT MAX(`'.$this->orderingKey.'`)' .
				' FROM ' . $this->_tbl .
				($where ? ' WHERE '.$where : '');

		$this->_db->setQuery( $query );
		$maxord = $this->_db->loadResult();

		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $maxord + 1;
	}

	/**
	 * Compacts the ordering sequence of the selected records
	 *
	 * @access public
	 * @param string Additional where query to limit ordering to a particular subset of records
	 */
	function reorder( $where='', $orderingkey = 0 ){

		if(!empty($orderingkey)) $this->orderingKey = $orderingkey;
		$k = $this->_tbl_key;

		if (!in_array( $this->orderingKey, array_keys($this->getProperties() ) ))
		{
			$this->setError( get_class( $this ).' does not support ordering');
			return false;
		}

		if ($this->_tbl == '#__content_frontpage')
		{
			$order2 = ", content_id DESC";
		}
		else
		{
			$order2 = "";
		}

		$query = 'SELECT '.$this->_tbl_key.', '.$this->orderingKey
		. ' FROM '. $this->_tbl
		. ' WHERE `'.$this->orderingKey.'` >= 0' . ( $where ? ' AND '. $where : '' )
		. ' ORDER BY `'.$this->orderingKey.'` '.$order2
		;
		$this->_db->setQuery( $query );
		if (!($orders = $this->_db->loadObjectList()))
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		$orderingKey = $this->orderingKey;
		// compact the ordering numbers
		for ($i=0, $n=count( $orders ); $i < $n; $i++)
		{
			if ($orders[$i]->$orderingKey >= 0)
			{
				if ($orders[$i]->$orderingKey != $i+1)
				{
					$orders[$i]->$orderingKey = $i+1;
					$query = 'UPDATE '.$this->_tbl
					. ' SET `'.$this->orderingKey.'` = '. (int) $orders[$i]->$orderingKey
					. ' WHERE '. $k .' = '. $this->_db->Quote($orders[$i]->$k)
					;
					$this->_db->setQuery( $query);
					$this->_db->query();
				}
			}
		}

	return true;
	}

	/**
	 * Checks out a row
	 *
	 * @access public
	 * @param	integer	The id of the user
	 * @param 	mixed	The primary key value for the row
	 * @return	boolean	True if successful, or if checkout is not supported
	 */
	function checkout( $who, $oid = null )
	{
		if (!in_array( 'locked_by', array_keys($this->getProperties()) )) {
			return true;
		}

		$k = $this->_tbl_key;
		if ($oid !== null) {
			$this->$k = $oid;
		}

		$date =& JFactory::getDate();
		$time = $date->toMysql();

		$query = 'UPDATE '.$this->_db->nameQuote( $this->_tbl ) .
			' SET locked_by = '.(int)$who.', locked_on = '.$this->_db->Quote($time) .
			' WHERE '.$this->_tbl_key.' = '. $this->_db->Quote($this->$k);
		$this->_db->setQuery( $query );

		$this->locked_by = $who;
		$this->locked_on = $time;

		return $this->_db->query();
	}

	/**
	 * Checks in a row
	 *
	 * @access	public
	 * @param	mixed	The primary key value for the row
	 * @return	boolean	True if successful, or if checkout is not supported
	 */
	function checkin( $oid=null )
	{
		if (!(
			in_array( 'locked_by', array_keys($this->getProperties()) ) ||
	 		in_array( 'locked_on', array_keys($this->getProperties()) )
		)) {
			return true;
		}

		$k = $this->_tbl_key;

		if ($oid !== null) {
			$this->$k = $oid;
		}

		if ($this->$k == NULL) {
			return false;
		}

		$query = 'UPDATE '.$this->_db->nameQuote( $this->_tbl ).
				' SET locked_by = 0, locked_on = '.$this->_db->Quote($this->_db->getNullDate()) .
				' WHERE '.$this->_tbl_key.' = '. $this->_db->Quote($this->$k);
		$this->_db->setQuery( $query );

		$this->locked_by = 0;
		$this->locked_on = '';

		return $this->_db->query();
	}

	/**
	 * Check if an item is checked out
	 *
	 * This function can be used as a static function too, when you do so you need to also provide the
	 * a value for the $against parameter.
	 *
	 * @static
	 * @access public
	 * @param integer  $with  	The userid to preform the match with, if an item is checked out
	 * 				  			by this user the function will return false
	 * @param integer  $against 	The userid to perform the match against when the function is used as
	 * 							a static function.
	 * @return boolean
	 */
	function isCheckedOut( $with = 0, $against = null)
	{
		if(isset($this) && is_a($this, 'JTable') && is_null($against)) {
			$against = $this->get( 'locked_by' );
		}

		//item is not checked out, or being checked out by the same user
		if (!$against || $against == $with) {
			return  false;
		}

		$session =& JTable::getInstance('session');
		return $session->exists($against);
	}


	/**
	 * Generic Publish/Unpublish function
	 *
	 * @access public
	 * @param array An array of id numbers
	 * @param integer 0 if unpublishing, 1 if publishing
	 * @param integer The id of the user performnig the operation
	 * @since 1.0.4
	 */
	function publish( $cid=null, $publish=1, $user_id=0 ){

		JArrayHelper::toInteger( $cid );
		$user_id	= (int) $user_id;
		$publish	= (int) $publish;
		$k			= $this->_tbl_key;

		if (count( $cid ) < 1)
		{
			if ($this->$k) {
				$cid = array( $this->$k );
			} else {
				$this->setError("No items selected.");
				return false;
			}
		}

		$cids = $k . '=' . implode( ' OR ' . $k . '=', $cid );

		$query = 'UPDATE '. $this->_tbl
		. ' SET published = ' . (int) $publish
		. ' WHERE ('.$cids.')'
		;

		$checkin = in_array( 'locked_by', array_keys($this->getProperties()) );
		if ($checkin)
		{
			$query .= ' AND (locked_by = 0 OR locked_by = '.(int) $user_id.')';
		}

		$this->_db->setQuery( $query );
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (count( $cid ) == 1 && $checkin)
		{
			if ($this->_db->getAffectedRows() == 1) {
				$this->checkin( $cid[0] );
				if ($this->$k == $cid[0]) {
					$this->published = $publish;
				}
			}
		}
		$this->setError('');
		return true;
	}

}