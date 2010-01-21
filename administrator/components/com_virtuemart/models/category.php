<?php
/**
*
* Category Model
*
* @package	VirtueMart
* @subpackage Category
* @author jseros, RickG
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

// Load the model framework
jimport( 'joomla.application.component.model');
require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_virtuemart".DS.'helpers'.DS.'vendorhelper.php');
require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_virtuemart".DS.'helpers'.DS.'permissions.php');

/**
 * Model for product categories
 * @author jseros
 */
class VirtueMartModelCategory extends JModel {

	/**
	 * @var integer Primary key
	 * @access private
	 */
    private $_id;

	/**
	 * @var objectlist Category data
	 * @access private
	 */
    private $_data;

	/**
	 * @var integer Total number of categories in the database
	 * @access private
	 */
	private $_total;

	/**
	 * @var pagination Pagination for country list
	 * @access private
	 */
	private $_pagination;


    /**
     * Constructor for the country model.
     *
     * The category id is read and determined if it is an array of ids or just one single id.
     *
     * @author RickG
     */

    public function __construct() {
        parent::__construct();

        // Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int');

		// Set the state pagination variables
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

        // Get the category id or array of ids.
		$idArray = JRequest::getVar('cid',  0, '', 'array');
    	$this->setId( (int)$idArray[0] );
    }



	 /**
     * Resets the category id and data
     *
     * @author RickG
     */
    public function setId($id)
    {
        $this->_id = $id;
        $this->_data = null;
    }


    /**
	 * Get the list of child categories for a given category
	 *
	 * @param int $category_id Category id to check for child categories
	 * @return object List of objects containing the child categories
	 */
	public function getChildCategoryList($vendorId, $category_id) {
		$db = JFactory::getDBO();

		$query = 'SELECT `category_id`, `category_thumb_image`, `category_child_id`, `category_name` ';
		$query .= 'FROM `#__vm_category`, `#__vm_category_xref` ';
		$query .= 'WHERE `#__vm_category_xref`.`category_parent_id` = ' . $category_id . ' ';
		$query .= 'AND `#__vm_category`.`category_id` = `#__vm_category_xref`.`category_child_id` ';
		$query .= 'AND `#__vm_category`.`vendor_id` = ' . $vendorId . ' ';
		$query .= 'AND `#__vm_category`.`published` = "1" ';
		$query .= 'ORDER BY `#__vm_category`.`ordering`, `#__vm_category`.`category_name` ASC';

		$childList = $this->_getList( $query );
		return $childList;
	}


	/**
	* Return an array containing category information
	*
	* @author
	*
	* @param boolean $onlyPublished Show only published categories?
	* @param boolean $withParentId Keep in mind $parentId param?
	* @param integer $parentId Show only its childs
	* @param string $keyword the keyword to filter categories
	* @return array Categories list
	*/
	public function getCategoryTree($onlyPublished = true, $withParentId = false, $parentId = 0, $keyword = "") {

		//$vendorId = Vendor::getLoggedVendor();
		$vendorId = 1;
		$categories = Array();

		$query = "SELECT c.category_id, c.category_description, c.category_name, c.ordering, c.published, cx.category_child_id, cx.category_parent_id, cx.category_shared
				  FROM #__vm_category c
				  LEFT JOIN #__vm_category_xref cx
				  ON c.category_id = cx.category_child_id
				  WHERE 1 ";

		// Get only published categories
		if( $onlyPublished ) {
			$query .= "AND c.published = 1 ";
		}

		if( !empty( $keyword ) ) {
			$query .= "AND ( c.category_name LIKE '%".$keyword."%'
					   OR c.category_description LIKE '%".$keyword."%') ";
		}

		if( $withParentId ){
			$query .= " AND cx.category_parent_id = ". $this->_db->Quote($parentId);
		}

		/*if( !Permissions::check('admin') ){
			$query .= " AND (#__vm_category.vendor_id = ". $this->_db->Quote($vendorId) . " OR #__vm_category_xref.category_shared = '1') ";
		}*/

		$filterOrder = JRequest::getCmd('filter_order', 'c.ordering');
		$filterOrderDir = JRequest::getCmd('filter_order_Dir', 'ASC');

		$query .= " ORDER BY ". $filterOrder ." ". $filterOrderDir;


		// Set the query in the database connector
		$this->_db->setQuery($query);

		// Transfer the Result into a searchable Array
		$categories = $this->_db->loadObjectList();

		return $categories;
		/*}
		else {
			return $GLOBALS['category_info']['category_tree'];
		}*/
	}


	/**
	 * Sorts an array with categories so the order of the categories is the same as in a tree.
	 *
	 * @author jseros
	 *
	 * @param array $categoryArr
	 * @return associative array ordering categories
	 */
	public function sortCategoryTree($categoryArr){

		/** FIRST STEP
	    * Order the Category Array and build a Tree of it
	    **/
		$idList = array();
		$rowList = array();
		$depthList = array();

		$children = array();
		$parentIds = array();
		$parentIdsHash = array();
		$parentId = 0;

		for( $i = 0, $nrows = count($categoryArr); $i < $nrows; $i++ ) {
			$parentIds[$i] = $categoryArr[$i]->category_parent_id;

			if($categoryArr[$i]->category_parent_id == 0){
				array_push($idList, $categoryArr[$i]->category_child_id);
				array_push($rowList, $i);
				array_push($depthList, 0);
			}

			$parentId = $parentIds[$i];

			if( isset($parentIdsHash[$parentId] )){
				$parentIdsHash[$parentId][$categoryArr[$i]->category_child_id] = $i;
			}
			else{
				$parentIdsHash[$parentId] = array($categoryArr[$i]->category_child_id => $i);
			}

		}

		$loopCount = 0;
		$watch = array(); // Hash to store children

		while( count($idList) < $nrows ){
			if( $loopCount > $nrows ) break;

			$idTemp = array();
			$rowTemp = array();
			$depthTemp = array();

			for($i = 0, $cIdlist = count($idList); $i < $cIdlist ; $i++) {
				$id = $idList[$i];
				$row = $rowList[$i];
				$depth = $depthList[$i];

				array_push($idTemp, $id);
				array_push($rowTemp, $row);
				array_push($depthTemp, $depth);

				$children = @$parentIdsHash[$id];

				if( !empty($children) ){
					foreach($children as $key => $value) {

						if( !isset($watch[$id][$key]) ){
							$watch[$id][$key] = 1;
							array_push($idTemp, $key);
							array_push($rowTemp, $value);
							array_push($depthTemp, $depth + 1);
						}
					}
				}
			}
			$idList = $idTemp;
			$rowList = $rowTemp;
			$depthList = $depthTemp;
			$loopCount++;
		}

		return array('id_list' => $idList,
					 'row_list' => $rowList,
					 'depth_list' => $depthList,
					 'categories' => $categoryArr
		);
	}


	/**
	 * Gets the total number of product for category
	 *
     * @author jseros
	 * @return int Total number of products
	 */
	public function countProducts( $categoryId = 0 ){
		$categoryId = intval($categoryId);
		$query = 'SELECT COUNT(category_id) as total FROM #__vm_product_category_xref
				  WHERE category_id = '. $this->_db->Quote($categoryId);

    	$this->_db->setQuery($query);
    	$result = $this->_db->loadObject();

        return $result->total;
    }


	/**
	 * Loads the pagination for the category table
	 *
     * @author RickG, jseros
     * @return JPagination Pagination for the current list of categories
	 */
    public function getPagination(){
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;
	}


	/**
	 * Gets the total number of categories
	 *
     * @author RickG, jseros
	 * @return int Total number of categories in the database
	 */
	public function _getTotal(){
    	if (empty($this->_total)) {
			$query = 'SELECT `category_id` FROM `#__vm_category`';
			$this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }


    /**
	 * Order any category
	 *
     * @author jseros
     * @param  int $id category id
     * @param  int $movement movement number
	 * @return bool
	 */
	public function orderCategory($id, $movement){
		//retrieving the category table object
		//and loading data
		$row = $this->getTable();
		$row->load($id);

		$query = 'SELECT category_parent_id FROM #__vm_category_xref WHERE category_child_id = '. $this->_db->Quote( $row->category_id );
		$this->_db->setQuery($query);
		$parent = $this->_db->loadObject();

		if (!$row->move( $movement, $parent->category_parent_id)) {
			$this->setError($row->getError());
			return false;
		}

		return true;
	}


	/**
	 * Order category group
	 *
     * @author jseros
     * @param  array $cats categories to order
	 * @return bool
	 */
	public function setOrder($cats){
		$total		= count( $cats );
		$groupings	= array();
		$row = $this->gettable();

		$order		= JRequest::getVar('order', array(), 'post', 'array');
		JArrayHelper::toInteger($order);

		$query = 'SELECT category_parent_id FROM #__vm_category
				  LEFT JOIN #__vm_category_xref cx
				  ON c.category_id = cx.category_child_id
			      WHERE c.category_id = %s';

		// update ordering values
		for( $i=0; $i < $total; $i++ ) {

			$row->load( $cats[$i] );
			$this->_db->setQuery( sprintf($query, $this->_db->Quote( $cats[$i] )), 0 ,1 );
			$parent = $this->_db->loadObject();

			$groupings[] = $parent->category_parent_id;
			if ($row->ordering != $order[$i]) {
				$row->ordering = $order[$i];
				if (!$row->store()) {
					$this->setError($row->getError());
					return false;
				}
			}
		}

		// execute reorder for each parent group
		$groupings = array_unique( $groupings );
		foreach ($groupings as $group){
			$row->reorder($group);
		}

		return true;
	}


	/**
	 * Publish/Unpublish all the ids selected
     *
     * @author RickG, jseros
     * @param boolean $publishId True is the ids should be published, false otherwise.
     * @return boolean True is the publishing was successful, false otherwise.
     */
	public function publish($publishId = false){
		$table = $this->getTable();
		$categoryIds = JRequest::getVar( 'cid', array(0), 'post', 'array' );

        if (!$table->publish($categoryIds, $publishId)) {
			$this->setError($table->getError());
			return false;
        }

		return true;
	}



	/**
	 * Shared/Unsared all the ids selected
     *
     * @author jseros
     *
     * @param boolean $share True is the ids should be shared, false otherwise
     * @return int 1 is the sharing action was successful, -1 is the unsharing action was successfully, 0 otherwise.
     */
	public function share($categories){

		foreach ($categories as $id){

			$quotedId = $this->_db->Quote($id);
			$query = 'SELECT category_shared
					  FROM #__vm_category_xref
					  WHERE category_child_id = '. $quotedId;

			$this->_db->setQuery($query);
			$categoryXref = $this->_db->loadObject();

			$share = ($categoryXref->category_shared > 0) ? 0 : 1;

			$query = 'UPDATE #__vm_category_xref
					  SET category_shared = '.$share.'
					  WHERE category_child_id = '.$quotedId;

			$this->_db->setQuery($query);

			if( !$this->_db->query() ){
				$this->setError( $this->_db->getErrorMsg() );
				return false;
			}

		}

		return ($share ? 1 : -1);
	}


    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author RickG, jseros
     */
	public function getCategory(){

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
     * Retrieve the detail record for the parent category of $categoryd
     *
     * @author jseros
     *
     * @param int $categoryId Child category id
     * @return JTable parent category data
     */
	public function getParentCategory( $categoryId = 0 ){
		$data = $this->getRelationInfo( $categoryId );
		$parentId = isset($data->category_parent_id) ? $data->category_parent_id : 0;

     	$parent = $this->getTable();
  		$parent->load((int) $parentId);

  		return $parent;
	}


	/**
     * Retrieve a list of pages from the templates directory.
     *
     * @author RickG, jseros
     * @return object List of flypage objects
     */
    public function getTemplateList( $section = 'browse' ) {
		$dir = JPATH_ROOT.DS.'components'.DS.'com_virtuemart'.DS.'themes';
		$dir .= DS.VmConfig::get('theme').DS.'templates'.DS.$section;
		$result = '';

		if ($handle = opendir($dir)) {
		    while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != '.svn' && $file != 'index.html') {
				    if (filetype($dir.DS.$file) != 'dir') {
				    	$file = str_replace('.php', '', $file);
						$result[] = JHTML::_('select.option', $file, JText::_($file));
				    }
				}
		    }
		}

		return $result;
    }


    /**
     * Retrieve category child-parent relation record
     *
     * @author jseros
     *
     * @param int $category_id
     * @return object Record of parent relation
     */
    public function getRelationInfo( $category_id = 0 ){
    	$category_id = (int) $category_id;

    	$query = 'SELECT category_parent_id, category_shared, category_list
    			  FROM #__vm_category_xref
    			  WHERE category_child_id = '. $this->_db->Quote($category_id);
    	$this->_db->setQuery($query);

    	return $this->_db->loadObject();
    }


    /**
	 * Bind the post data to the category table and save it
     *
     * @author jseros
     * @return int category id stored
	 */
    public function store()
	{
		jimport('joomla.filesystem.file');

		$table = $this->getTable();
		$data = JRequest::get('post');

		//normalize data
		$data['category_flypage'] = 'shop.'.$data['category_flypage'];
		$data['category_flypage'] = str_replace('.tpl', '', $data['category_flypage']);

		//uploading images and creating thumbnails
		$fullImage = JRequest::getVar('category_full_image', array(), 'files');
		if($fullImage['error'] == UPLOAD_ERR_OK) {
			move_uploaded_file( $fullImage['tmp_name'], JPATH_COMPONENT_SITE.DS.'shop_image'.DS.'category'.DS.$fullImage['name']);
			$data['category_full_image'] = $fullImage['name'];
		}
		elseif($data['category_full_image_url']){
			$data['category_full_image'] = $data['category_full_image_url'];
		}
		else{
			$data['category_full_image'] = $data['category_full_image_current'];
		}

		//creating the thumbnail image
		if( $data['image_action_full'] == 1 ){
			$data['category_thumb_image'] = basename( ImageHelper::createResizedImage($data['category_full_image'], 'category', PSHOP_IMG_WIDTH, PSHOP_IMG_HEIGHT));
		}
		//deleting image
		elseif( $data['image_action_full'] == 2 ){
			JFile::delete( JPATH_COMPONENT_SITE.DS.'shop_image'.DS.'category'.DS.$data['category_full_image_current'] );
			$data['category_full_image'] = '';
		}

		//uploading explicit thumbnail image
		$thumbImage = JRequest::getVar('category_thumb_image', array(), 'files');
		if( $thumbImage['error'] == UPLOAD_ERR_OK ){
			move_uploaded_file( $thumbImage['tmp_name'], JPATH_COMPONENT_SITE.DS.'shop_image'.DS.'category'.DS.'resized'.DS.$thumbImage['name']);
			$data['category_thumb_image'] = $thumbImage['name'];
		}
		elseif( empty($data['category_thumb_image']) ){
			if( !empty($data['category_thumb_image_url']) ){ //storing the URL if is it necessary
				$data['category_thumb_image'] = $data['category_thumb_image_url'];
			}
			else{
				$data['category_thumb_image'] = $data['category_thumb_image_current'];
			}
		}

		//deleting thumbnail image
		if( $data['image_action_thumb'] == 2 ){
			JFile::delete( JPATH_COMPONENT_SITE.DS.'shop_image'.DS.'category'.DS.'resized'.DS.$data['category_thumb_image_current'] );
			$data['category_thumb_image'] = '';
		}

		// Bind the form fields to the country table
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Make sure the category record is valid
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Save the country record to the database
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		//store category relation
		if( !$data['category_id'] ){ //is new

			$id = $this->_db->insertid();

			$query = 'INSERT INTO #__vm_category_xref(category_parent_id, category_child_id, category_shared)
					  VALUES(
					  	'. $this->_db->Quote( (int)$data['category_parent_id'] ) .',
					  	'. $this->_db->Quote( (int)$id ) .',
					  	'. $this->_db->Quote( (int)$data['shared'] ) .'
					  )';

		}
		else{
			$id = $data['category_id'];

			$query = 'UPDATE #__vm_category_xref
					  SET category_parent_id = '. $this->_db->Quote( (int)$data['category_parent_id'] ) .',
					  category_shared = '. $this->_db->Quote( (int)$data['shared'] ) .'
					  WHERE category_child_id = '. $this->_db->Quote( (int)$data['category_id'] );
		}

		$this->_db->setQuery($query);


		if(!$this->_db->query()){
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		}

		return $id;
	}

	/**
     * Delete all categories selected
     *
     * @author jseros
     * @param  array $cids categories to delete
     * @return boolean if the item delete was successful
     */
    public function delete($cids) {
		$table = $this->getTable();

		foreach($cids as $cid) {
		    if( $this->clearProducts($cid) ) {
				if (!$table->delete($cid)) {
				    $this->setError($table->getError());
				    return false;
				}

				//deleting relations
				$query = "DELETE FROM #__vm_product_category_xref WHERE category_child_id = ". $this->_db->Quote($cid);
		    	$this->_db->setQuery($query);

		    	if(!$this->_db->query()){
		    		$this->setError( $this->_db->getErrorMsg() );
		    	}

		    	//updating parent relations
				$query = "UPDATE #__vm_product_category_xref SET category_parent_id = 0 WHERE category_parent_id = ". $this->_db->Quote($cid);
		    	$this->_db->setQuery($query);

		    	if(!$this->_db->query()){
		    		$this->setError( $this->_db->getErrorMsg() );
		    	}
		    }
		    else {
				$this->setError('Could not clear category products');
				return false;
		    }
		}
		return true;
    }


	/**
     * Delete all relations between categories and products
     *
     * @author jseros
     *
     * @param  int $cid categories to delete
     * @return boolean if the item delete was successful
     */
    public function clearProducts($cid) {

    	$query = "UPDATE #__vm_product_category_xref SET category_id = 0 WHERE category_id =" . $this->_db->Quote($cid);
		$this->_db->setQuery($query);

		if( !$this->_db->query() ){
			return false;
		}

		return true;
    }
}