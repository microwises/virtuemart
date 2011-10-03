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

define('USE_SQL_CALC_FOUND_ROWS' , true);

class VmModel extends JModel {

	var $_id 			= 0;
	var $_data			= null;
	var $_total			= null;
	var $_query 		= null;
	var $_pagination 	= 0;

	var $_maintable 	= '';	// something like #__virtuemart_calcs
	var $_maintablename = '';
	var $_idName		= '';
	var $_cidName		= 'cid';
	var $_togglesName	= null;
	private $_withCount = true;
	var $_noLimit = false;

	public function __construct($cidName='cid'){
		parent::__construct();

		$this->_cidName = $cidName;

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getWord('option').JRequest::getWord('view').'.limitstart', 'limitstart', 0, 'int');

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

		$this->setDefaultValidOrderingFields($defaultTable);
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

		if(is_array($id) && count($id)!==0) $id = $id[0];
		if($this->_id!=$id){
			$this->_id = (int)$id;
			//			$idName = $this->_idName;
			//			$this->$idName = $this->_id;
			$this->_data = null;
		}
		return $this->_id;
	}

	var $_tablePreFix = '';
	/**
	 *
	 * This function sets the valid ordering fields for this model with the default table attributes
	 * @param unknown_type $defaultTable
	 */
	function setDefaultValidOrderingFields($defaultTable=null){

		if($defaultTable===null){
			$defaultTable = $this->getTable($this->_maintablename);
		}

		$this->_tablePreFix = $defaultTable->_tablePreFix;
		$dTableArray = (array)($defaultTable);
		// Iterate over the object variables to build the query fields and values.
		foreach ($dTableArray as $k => $v){

			// Ignore any internal fields.
			if ($k[0] == '_') {
				continue;
			}
			$this->_validOrderingFieldName[] = $this->_tablePreFix.$k;
		}

	}

	function addvalidOrderingFieldName($add){
		$this->_validOrderingFieldName = array_merge($this->_validOrderingFieldName,$add);
	}

	var $_validFilterDir = array('ASC','DESC');
	function getValidFilterDir($default = 'ASC'){

		$view = JRequest::getWord('view');
		$mainframe = JFactory::getApplication() ;
		$filter_order_Dir = strtoupper($mainframe->getUserStateFromRequest( 'com_virtuemart'.$view.'.filter_order_Dir', 'filter_order_Dir', $default, 'word' ));
		if(!in_array($filter_order_Dir, $this->_validFilterDir)){
			$filter_order_Dir = $default;
			$mainframe->setUserState( 'com_virtuemart'.$view.'.filter_order_Dir',$filter_order_Dir);
			vmdebug('checkValidOrderingField: programmer choosed invalid ordering direction, model _validDefaultOrderingFieldName used');
		}
		return $filter_order_Dir;
	}

	// 	var $_validDefaultOrderingFieldName = 'ordering';
	var $_validOrderingFieldName = null;

	function getValidFilterOrdering($overwrite=null,$overWriteDefault=null){

		$mainframe = JFactory::getApplication() ;
		$view = JRequest::getWord('view');

		$defaultValue = $this->_validOrderingFieldName[0];
		if($overWriteDefault!==null){
			$defaultValue = $overWriteDefault;
		}

		if($overwrite!==null){
			$filter_order = $overwrite;
		} else {
			$filter_order = strtolower($mainframe->getUserStateFromRequest( 'com_virtuemart'.$view.'.filter_order', 'filter_order',$defaultValue , 'cmd' ));
		}

		$dotps = strrpos($filter_order, '.');
		if($dotps===false && !empty($this->_tablePreFix) ){
			$filter_order = $this->_tablePreFix . $filter_order;
		}

		if(!in_array($filter_order, $this->_validOrderingFieldName)){

			vmdebug('checkValidOrderingField: programmer choosed invalid ordering '.$filter_order.', use '.$defaultValue);
			$filter_order = $defaultValue;

			$mainframe->setUserState( 'com_virtuemart.'.$view.'.filter_order',$filter_order);

		}

		return $filter_order;
	}

	/**
	 * Get the SQL Ordering statement
	 *
	 * @return string text to add to the SQL statement
	 */
	function _getOrdering($default='ordering',$order_dir = 'ASC') {
		if ($default == '') return '';
		// 		$option = JRequest::getCmd( 'option');
		// 		$view = JRequest::getWord('view');
		// 		$mainframe = JFactory::getApplication() ;


		//		$filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'.'.$view.'.filter_order_Dir', 'filter_order_Dir', $order_dir, 'word' );
		$filter_order_Dir = $this->getValidFilterDir($order_dir);

		// 			$filter_order     = $mainframe->getUserStateFromRequest( $option.'.'.$view.'.filter_order', 'filter_order', $default, 'cmd' );
		$filter_order     = $this->getValidFilterOrdering($default);

		return ' ORDER BY '.$filter_order.' '.$filter_order_Dir ;
	}



	/**
	 * Loads the pagination
	 *
	 * @author Max Milbers
	 */
	public function getPagination($total=0,$limitStart=0,$limit=0) {
		if ($this->_pagination == null) {


			if(empty($limit) ){
				$limits = $this->setPaginationLimits();
			} else {
				$limits[0] = $limitStart;
				$limits[1] = $limit;
			}

			// TODO, this give result when result = 0 >>> if(empty($total)) $total = $this->getTotal();

			jimport('joomla.html.pagination');
			// 			$this->_pagination = new JPagination($total , $this->getState('limitstart'), $this->getState('limit') );
			$this->_pagination = new JPagination($total , $limits[0], $limits[1] );
			// 			vmdebug('created Pagination',$total, $limits[0], $limits[1] );
		}
		// 		vmdebug('my pagination',$this->_pagination);
		return $this->_pagination;
	}

	/**
	 * Gets the total number of entries
	 *TODO filters and search ar not set
	 * @author Max Milbers
	 * @return int Total number of entries in the database
	 */
	public function getTotal() {

		if (empty($this->_total)) {
			$query = 'SELECT `'.$this->_db->getEscaped($this->_idName).'` FROM `'.$this->_db->getEscaped($this->_maintable).'`';;
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


	public function setPaginationLimits(){

		$mainframe = JFactory::getApplication();

		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit',  VmConfig::get('list_limit',10), 'int');
		$this->setState('limit', $limit);

		// 		$this->setState('limitstart', JRequest::getVar('limitstart', 0, '', 'int'));
		// In case limit has been changed, adjust limitstart accordingly
		// 		$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));
		$limitstart = ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0);
		$this->setState('limitstart', $limitstart);

		return array($limitstart,$limit);
	}

	public function setGetCount($withCount){

		$this->_withCount = $withCount;
	}

	/**
	 *
	 * exeSortSearchListQuery
	 *
	 * @author Max Milbers
	 * @author Patrick Kohl
	 * @param boolean $object use single result array = 2, assoc. array = 1 or object list = 0 as return value
	 * @param string $select the fields to select
	 * @param string $joinedTables the string of the joined tables or the table
	 * @param string $whereString for the where condition
	 * @param string $groupBy
	 * @param string $orderBy
	 * @param string $filter_order_Dir
	 */

	public function exeSortSearchListQuery($object, $select, $joinedTables, $whereString = '', $groupBy = '', $orderBy = '', $filter_order_Dir = '', $nbrReturnProducts = false){

		// 		vmSetStartTime('exe');
		// 		if(USE_SQL_CALC_FOUND_ROWS){

		//and the where conditions
		$joinedTables .= $whereString .$groupBy .$orderBy .$filter_order_Dir ;
		// 			$joinedTables .= $whereString .$groupBy .$orderBy;

		if($nbrReturnProducts){
			$limitStart = 0;
			$limit = $nbrReturnProducts;
			$this->_withCount = false;
		} else if($this->_noLimit){
			$this->_withCount = false;
			$limitStart = 0;
			$limit = 0;
		} else {
			$limits = $this->setPaginationLimits();
			$limitStart = $limits[0];
			$limit = $limits[1];
		}

		if($this->_withCount){
			$q = 'SELECT SQL_CALC_FOUND_ROWS '.$select.$joinedTables;
		} else {
			$q = 'SELECT '.$select.$joinedTables;
		}

		$this->_db->setQuery($q,$limitStart,$limit);
		// 			vmdebug('my q',$this->_db->getQuery() );
		if($object == 2){
			$list = $this->_db->loadResultArray();
		} else if($object == 1 ){
			$list = $this->_db->loadAssocList();
		} else {
			$list = $this->_db->loadObjectList();
		}
		// 			vmdebug('my $list',$list);
		if(empty($list)){
			$errors = $this->_db->getErrorMsg();
			if( !empty( $errors)){
				vmdebug('exeSortSearchListQuery error in db ',$this->_db->getErrorMsg());
				VmTrace('productsortseachr',true);
			}
			if($object == 2 or $object == 1){
				$list = array();
			}
		}

		if($this->_withCount){

			$this->_db->setQuery('SELECT FOUND_ROWS()');
			$count = $this->_db->loadResult();

			if($count == false){
				$count = 0;
			}
			$this->_total = $count;
			$this->getPagination($count,$limitStart,$limit);

		} else {
			$this->_withCount = true;
		}
		// 			vmTime('exeSortSearchListQuery SQL_CALC_FOUND_ROWS','exe');
		return $list;

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
			if(isset($this->_data->virtuemart_vendor_id) && empty($this->_data->virtuemart_vendor_id)){
				if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
				$this->_data->virtuemart_vendor_id = VirtueMartModelVendor::getLoggedVendor();;
			}
		}

		return $this->_data;
	}


	public function store(&$data){

		$table = $this->getTable($this->_maintablename);

		$data = $table->bindChecknStore($data);

		$errors = $table->getErrors();
		foreach($errors as $error){
			$this->setError( get_class( $this ).'::store '.$error);
		}
		if(is_object($data)){
			$_idName = $this->_idName;
			return $data->$_idName;
		} else {
			return $data[$this->_idName];
		}

	}

	/**
	 * Delete all record ids selected
	 *
	 * @author Max Milbers
	 * @return boolean True is the delete was successful, false otherwise.
	 */
	public function remove($ids) {

		$table = $this->getTable($this->_maintablename);
		foreach($ids as $id) {
			if (!$table->delete((int)$id)) {
				$this->setError(get_class( $this ).'::remove '.$id.' '.$table->getError());
				return false;
			}
		}

		return true;
	}

	public function setToggleName($togglesName){
		$this->_togglesName[] = $togglesName ;
	}
	/**
	 * toggle (0/1) a field
	 * or invert by $val for multi IDS;
	 * @author Patrick Kohl
	 * @param string $field the field to toggle
	 * @param string $postName the name of id Post  (Primary Key in table Class constructor)
	 */

	function toggle($field,$val = NULL, $cidName = 0 ) {
		$ok = true;
		$this->setToggleName('published');
		if (!in_array($field, $this->_togglesName)) {
			return false ;
		}
		$table = $this->getTable($this->_maintablename);
		//		if(empty($cidName)) $cidName = $this->_cidName;

		$ids = JRequest::getVar( $this->_cidName, JRequest::getVar('cid',array(0)), 'post', 'array' );

		foreach($ids as $id){
			$table->load( (int)$id );
			if (!$table->toggle($field, $val)) {
				//			if (!$table->store()) {
				JError::raiseError(500, get_class( $this ).'::toggle '.$table->getError() );
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
		$table = $this->getTable($this->_maintablename);
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
		$table = $this->getTable($this->_maintablename);
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


	/**
	 * Since an object like product, category dont need always an image, we can attach them to the object with this function
	 * The parameter takes a single product or arrays of products, look for BE/views/product/view.html.php
	 * for an exampel using it
	 *
	 * @author Max Milbers
	 * @param object $obj some object with a _medias xref table
	 */

	public function addImages($obj){

		if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
		if(empty($this->mediaModel))$this->mediaModel = new VirtueMartModelMedia();

		$this->mediaModel->attachImages($obj,$this->_maintablename,'image');

	}

	public function resetErrors(){

		$this->_errors = array();
	}
}