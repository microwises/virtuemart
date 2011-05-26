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

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model for product categories
 * @author jseros
 */
class VirtueMartModelCategory extends VmModel {

	private $_category_tree;

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('categories');
	}


    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author RickG, jseros, RolandD, Max Milbers
     */
	public function getCategory($virtuemart_category_id=0,$childs=TRUE){

		if(!empty($virtuemart_category_id)) $this->setId((int)$virtuemart_category_id);

  		if (empty($this->_data)) {
   			$this->_data = $this->getTable('categories');
   			$this->_data->load((int)$this->_id);
  		}

		$xrefTable = $this->getTable('category_medias');
		$this->_data->virtuemart_media_id = $xrefTable->load((int)$this->_id);

		if($xrefTable->getError()) $this->setError($xrefTable->getError());

  		if($childs){
  			$this->_data->haschildren = $this->hasChildren($this->_id);

  			/* Get children if they exist */
			if ($this->_data->haschildren) $this->_data->children = $this->getChildrenList($this->_id);
			else $this->_data->children = null;

			/* Get the product count */
			$this->_data->productcount = $this->countProducts($this->_id);

			/* Get parent for breatcrumb */
			$this->_data->parents = $this->getparentsList($this->_id);

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
	 * Get the list of child categories for a given category
	 *
	 * @param int $virtuemart_category_id Category id to check for child categories
	 * @return object List of objects containing the child categories
	 */
	public function getChildCategoryList($vendorId, $virtuemart_category_id) {

		$query = 'SELECT `#__virtuemart_categories`.`virtuemart_category_id`,`#__virtuemart_categories`.`category_name` ';
		$query .= 'FROM `#__virtuemart_categories`, `#__virtuemart_category_categories` ';
		$query .= 'WHERE `#__virtuemart_category_categories`.`category_parent_id` = ' . $virtuemart_category_id . ' ';
		$query .= 'AND `#__virtuemart_categories`.`virtuemart_category_id` = `#__virtuemart_category_categories`.`category_child_id` ';
		$query .= 'AND `#__virtuemart_categories`.`virtuemart_vendor_id` = ' . $vendorId . ' ';
		$query .= 'AND `#__virtuemart_categories`.`published` = "1" ';
		$query .= 'ORDER BY `#__virtuemart_categories`.`ordering`, `#__virtuemart_categories`.`category_name` ASC';
		$childList = $this->_getList( $query );

		if(!empty($childList)){
			foreach($childList as $child){
				$xrefTable = $this->getTable('category_medias');
				$child->virtuemart_media_id = $xrefTable->load($child->virtuemart_category_id);
			}
		}
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

		$vendorId = 1;

		$query = "SELECT c.`virtuemart_category_id`, c.`category_description`, c.`category_name`, c.`ordering`, c.`published`, cx.`category_child_id`, cx.`category_parent_id`, c.`shared`
				  FROM `#__virtuemart_categories` c
				  LEFT JOIN `#__virtuemart_category_categories` cx
				  ON c.`virtuemart_category_id` = cx.`category_child_id`
				  WHERE 1 ";

		// Get only published categories
		if( $onlyPublished ) {
			$query .= "AND c.`published` = 1 ";
		}

		if( !empty( $keyword ) ) {
			$query .= "AND ( c.`category_name` LIKE '%".$keyword."%'
					   OR c.`category_description` LIKE '%".$keyword."%') ";
		}

		if( $withParentId ){
			$query .= " AND cx.`category_parent_id` = ". $this->_db->Quote($parentId);
		}
		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		if( !Permissions::getInstance()->check('admin') ){
			$query .= " AND (c.`virtuemart_vendor_id` = ". $this->_db->Quote($vendorId) . " OR c.`shared` = '1') ";
		}

		$filterOrder = JRequest::getCmd('filter_order', 'c.ordering');
		$filterOrderDir = JRequest::getCmd('filter_order_Dir', 'ASC');
		// $filterOrder can still be empty at this point!
		if( empty( $filterOrder )) {
			$filterOrder = 'c.`ordering`';
		}
		if( empty( $filterOrderDir )) {
			$filterOrderDir = 'ASC';
		}
		$query .= " ORDER BY ". $filterOrder ." ". $filterOrderDir;

		// Set the query in the database connector
		$this->_db->setQuery($query);

		// Transfer the Result into a searchable Array
		$this->_category_tree = $this->_db->loadObjectList();

		return $this->_category_tree;

	}


	/**
	 * Sorts an array with categories so the order of the categories is the same as in a tree.
	 *
	 * @author jseros
	 *
	 * @param array $this->_category_tree
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
	* count the products in a category
	*
	* @author RolandD, Max Milbers
	* @return array list of categories product is in
	*/
	public function countProducts($cat_id=0) {

		if(!empty($this->_db))$this->_db = JFactory::getDBO();
		$vendorId = 1;
		if ($cat_id > 0) {
			$q = 'SELECT count(#__virtuemart_products.virtuemart_product_id) AS total
			FROM `#__virtuemart_products`, `#__virtuemart_product_categories`
			WHERE `#__virtuemart_products`.`virtuemart_vendor_id` = "'.$vendorId.'"
			AND `#__virtuemart_product_categories`.`virtuemart_category_id` = '.$this->_db->Quote($cat_id).'
			AND `#__virtuemart_products`.`virtuemart_product_id` = `#__virtuemart_product_categories`.`virtuemart_product_id`
			AND `#__virtuemart_products`.`published` = "1" ';
			$this->_db->setQuery($q);
			$count = $this->_db->loadResult();
		} else $count=0 ;

		return $count;
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
		$row = $this->getTable('categories');
		$row->load($id);

		$query = 'SELECT `category_parent_id` FROM `#__virtuemart_category_categories` WHERE `category_child_id` = '. $this->_db->Quote( $row->virtuemart_category_id );
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
		$row = $this->getTable('categories');

		$order		= JRequest::getVar('order', array(), 'post', 'array');
		JArrayHelper::toInteger($order);

		$query = 'SELECT `category_parent_id` FROM `#__virtuemart_categories`
				  LEFT JOIN `#__virtuemart_category_categories` cx
				  ON c.`virtuemart_category_id` = cx.`category_child_id`
			      WHERE c.`virtuemart_category_id` = %s';

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
	 * Shared/Unsared all the ids selected
     * TODO replace by toggle
     * @author jseros
     *
     * @param boolean $share True is the ids should be shared, false otherwise
     * @return int 1 is the sharing action was successful, -1 is the unsharing action was successfully, 0 otherwise.
     */
//	public function share($categories){
//
//		foreach ($categories as $id){
//
//			$quotedId = $this->_db->Quote($id);
//			$query = 'SELECT `category_shared`
//					  FROM `#__virtuemart_category_categories`
//					  WHERE `category_child_id` = '. $quotedId;
//
//			$this->_db->setQuery($query);
//			$categoryXref = $this->_db->loadObject();
//
//			$share = ($categoryXref->category_shared > 0) ? 0 : 1;
//
//			$query = 'UPDATE `#__virtuemart_category_categories`
//					  SET `category_shared` = '.$share.'
//					  WHERE `category_child_id` = '.$quotedId;
//
//			$this->_db->setQuery($query);
//
//			if( !$this->_db->query() ){
//				$this->setError( $this->_db->getErrorMsg() );
//				return false;
//			}
//
//		}

//		return ($share ? 1 : -1);
//	}


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

     	$parent = $this->getTable('categories');
  		$parent->load((int) $parentId);

  		return $parent;
	}


    /**
     * Retrieve category child-parent relation record
     *
     * @author jseros
     *
     * @param int $virtuemart_category_id
     * @return object Record of parent relation
     */
    public function getRelationInfo( $virtuemart_category_id = 0 ){
    	$virtuemart_category_id = (int) $virtuemart_category_id;

    	$query = 'SELECT `category_parent_id`, `ordering`
    			  FROM `#__virtuemart_category_categories`
    			  WHERE `category_child_id` = '. $this->_db->Quote($virtuemart_category_id);
    	$this->_db->setQuery($query);

    	return $this->_db->loadObject();
    }


    /**
	 * Bind the post data to the category table and save it
     *
     * @author jseros, RolandD, Max Milbers
     * @return int category id stored
	 */
    public function store($data) {

    	JRequest::checkToken() or jexit( 'Invalid Token, in store category');

		$table = $this->getTable('categories');

		$data = $table->bindChecknStore($data);
    	$errors = $table->getErrors();
		foreach($errors as $error){
			$this->setError($error);
		}

		if(!empty($data['virtuemart_category_id'])){
			$xdata['category_child_id'] = $data['virtuemart_category_id'];
			$xdata['category_parent_id'] = empty($data['category_parent_id'])? 0:$data['category_parent_id'];
			$xdata['ordering'] = empty($data['ordering'])? 0: $data['ordering'];

    		$table = $this->getTable('category_categories');

			$xdata = $table->bindChecknStore($xdata);
	    	$errors = $table->getErrors();
			foreach($errors as $error){
				$this->setError($error);
			}
		}

		// Process the images
		if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
		$mediaModel = new VirtueMartModelMedia();
		$file_id = $mediaModel->storeMedia($data,'category');
        $errors = $mediaModel->getErrors();
		foreach($errors as $error){
			$this->setError($error);
		}
		return $data['virtuemart_category_id'] ;
	}

	/**
     * Delete all categories selected
     *
     * @author jseros
     * @param  array $cids categories to remove
     * @return boolean if the item remove was successful
     */
    public function remove($cids) {

    	JRequest::checkToken() or jexit( 'Invalid Token, in remove category');

		$table = $this->getTable('categories');

		foreach($cids as $cid) {
		    if( $this->clearProducts($cid) ) {
				if (!$table->delete($cid)) {
				    $this->setError($table->getError());
				    return false;
				}

				//deleting relations
				$query = "DELETE FROM `#__virtuemart_product_categories` WHERE `category_child_id` = ". $this->_db->Quote($cid);
		    	$this->_db->setQuery($query);

		    	if(!$this->_db->query()){
		    		$this->setError( $this->_db->getErrorMsg() );
		    	}

		    	//updating parent relations
				$query = "UPDATE `#__virtuemart_product_categories` SET `category_parent_id` = 0 WHERE `category_parent_id` = ". $this->_db->Quote($cid);
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
     * @param  int $cid categories to remove
     * @return boolean if the item remove was successful
     */
    public function clearProducts($cid) {

    	$query = "UPDATE `#__virtuemart_product_categories` SET `virtuemart_category_id` = 0 WHERE `virtuemart_category_id` =" . $this->_db->Quote($cid);
		$this->_db->setQuery($query);

		if( !$this->_db->query() ){
			return false;
		}

		return true;
    }


	/**
	 * Stuff of categorydetails
	 */

	/* array container for category tree ID*/
	var $container = array();


	/**
	* Checks for children of the category $virtuemart_category_id
	*
	* @author RolandD
	* @param int $virtuemart_category_id the category ID to check
	* @return boolean true when the category has childs, false when not
	*/
	public function hasChildren($virtuemart_category_id) {
		$db = JFactory::getDBO();
		$q = "SELECT `category_child_id`
			FROM `#__virtuemart_category_categories`
			WHERE `category_parent_id` = ".$virtuemart_category_id;
		$db->setQuery($q);
		$db->query();
		if ($db->getAffectedRows() > 0) return true;
		else return false;
	}

	/**
	 * Creates a bulleted of the childen of this category if they exist
	 *
	 * @author RolandD
	 * @todo Add vendor ID
	 * @param int $virtuemart_category_id the category ID to create the list of
	 * @return array containing the child categories
	 */
	public function getChildrenList($virtuemart_category_id,$limit=false) {
		$db = JFactory::getDBO();
		$childs = array();

		$q = "SELECT `virtuemart_category_id`, `category_child_id`, `category_name`
			FROM `#__virtuemart_categories`, `#__virtuemart_category_categories`
			WHERE `#__virtuemart_category_categories`.`category_parent_id` = ".$virtuemart_category_id."
			AND `#__virtuemart_categories`.`virtuemart_category_id`=`#__virtuemart_category_categories`.`category_child_id`
			AND `#__virtuemart_categories`.`virtuemart_vendor_id` = 1
			AND `#__virtuemart_categories`.`published` = 1
			ORDER BY `#__virtuemart_categories`.`ordering`, `#__virtuemart_categories`.`category_name` ASC";
		if ($limit) $q .=' limit 0,'.$limit;
		$db->setQuery($q);
		$childs = $db->loadObjectList();
		/* Get the products in the category */
		if(!empty($childs)){
			foreach ($childs as $ckey => $child) {
				$childs[$ckey]->number_of_products = $this->countProducts($child->category_child_id);
			}
		}


		return $childs;
	}

	/**
	 * Creates a bulleted of the childen of this category if they exist
	 *
	 * @author RolandD
	 * @todo Add vendor ID
	 * @param int $virtuemart_category_id the category ID to create the list of
	 * @return array containing the child categories
	 */
	public function getparentsList($virtuemart_category_id) {

		$db = & JFactory::getDBO();
		$menu = &JSite::getMenu();
		$parents = array();
		if (empty($query['Itemid'])) {
			$menuItem = &$menu->getActive();
		} else {
			$menuItem = &$menu->getItem($query['Itemid']);
		}
		$menuCatid = (empty($menuItem->query['virtuemart_category_id'])) ? 0 : $menuItem->query['virtuemart_category_id'];
		if ($menuCatid == $virtuemart_category_id) return ;
		$parents_id = array_reverse($this->getCategoryRecurse($virtuemart_category_id,$menuCatid));
		foreach ($parents_id as $id ) {
			$q = "SELECT `category_name`,`virtuemart_category_id`
				FROM  `#__virtuemart_categories`
				WHERE  `virtuemart_category_id`=".$id;

			$db->setQuery($q);

			$parents[] = $db->loadObject();
		}
		return $parents;
	}

	function getCategoryRecurse($virtuemart_category_id,$catMenuId,$first=true ) {
		static $idsArr = array();
		if($first) {
			$idsArr = array();
		}

		$db = & JFactory::getDBO();
		$q  = "SELECT `category_child_id` AS `child`, `category_parent_id` AS `parent`
			FROM  `#__virtuemart_category_categories` AS `xref`
			WHERE `xref`.`category_child_id`= ".$virtuemart_category_id;
		$db->setQuery($q);
		if (!$ids = $db->loadObject()) {
			return $idsArr;
		}
		if ($ids->child) $idsArr[] = $ids->child;
		if($ids->child != 0 and $catMenuId != $virtuemart_category_id and $catMenuId != $ids->parent) {
			$this->getCategoryRecurse($ids->parent,$catMenuId,false);
		}
		return $idsArr;
	}

	/*
	* Returns an array of the categories recursively for a given category
	* @author Kohl Patrick
	* @param int $id
	* @param int $maxLevel
	 * @Object $this->container
	*/
	function treeCat($id=0,$maxLevel =1000) {
		static $level = 0;
		static $num = -1 ;
		$db = & JFactory::getDBO();
		$q = 'SELECT `category_child_id`,`category_name` FROM `#__virtuemart_category_categories`
		LEFT JOIN `#__virtuemart_categories` on `#__virtuemart_categories`.`virtuemart_category_id`=`#__virtuemart_category_categories`.`category_child_id`
		WHERE `category_parent_id`='.$id;
		$db->setQuery($q);
		$num ++;
		// if it is a leaf (no data underneath it) then return
		$childs = $db->loadObjectList();
		if ($level==$maxLevel) return;
		if ($childs) {
			$level++;
			foreach ($childs as $child) {
				$this->container[$num]->id = $child->category_child_id;
				$this->container[$num]->name = $child->category_name;
				$this->container[$num]->level = $level;
				self::treeCat($child->category_child_id,$maxLevel );
			}
			$level--;
		}
	}
	/**
	 * @author Kohl Patrick
	 * @param  $maxlevel the number of level
	 * @param  $id the root category id
 	 * @Object $this->container
	 * @ return categories id, name and level in container
	 * if you set Maxlevel to 0, then you see nothing
	 * max level =1 for simple category,2 for category and child cat ....
	 * don't set it for all (1000 levels)
	 */
	function GetTreeCat($id=0,$maxLevel = 1000) {
		self::treeCat($id ,$maxLevel) ;
		return $this->container ;
	}

	/* old fuction to do work virtuemart categorie tree */
	function get_category_tree( $virtuemart_category_id=0,
				$links_css_class="mainlevel",
				$list_css_class="mm123",
				$highlighted_style="font-style:italic;" ) {

		self::getCategoryTreeArray(); // Set array of category objects
		$result = self::sortCategoryTreeArray(); // Sort array of category objects
		$row_list = $result['row_list'];
		$depth_list = $result['depth_list'];
		$category_tmp = $result['category_tmp'];
		$nrows = sizeof($category_tmp);

		// Copy the Array into an Array with auto_incrementing Indexes
		$key = array_keys($this->_category_tree); // Array of category table primary keys

		$nrows = $size = sizeOf($key); // Category count

		$html = "";

		// Find out if we have subcategories to display
		$allowed_subcategories = Array();
		if( !empty( $this->_category_tree[$virtuemart_category_id]["category_parent_id"] ) ) {
			// Find the Root Category of this category
			$root = $this->_category_tree[$virtuemart_category_id];
			$allowed_subcategories[] = $this->_category_tree[$virtuemart_category_id]["category_parent_id"];
			// Loop through the Tree up to the root
			while( !empty( $root["category_parent_id"] )) {
				$allowed_subcategories[] = $this->_category_tree[$root["category_child_id"]]["category_child_id"];
				$root = $this->_category_tree[$root["category_parent_id"]];
			}
		}
		// Fix the empty Array Fields
		if( $nrows < count( $row_list ) ) {
			$nrows = count( $row_list );
		}

		// Now show the categories
		for($n = 0 ; $n < $nrows ; $n++) {

			if( !isset( $row_list[$n] ) || !isset( $category_tmp[$row_list[$n]]["category_child_id"] ) )
			continue;
			if( $virtuemart_category_id == $category_tmp[$row_list[$n]]["category_child_id"] )
			$style = $highlighted_style;
			else
			$style = "";

			$allowed = false;
			if( $depth_list[$n] > 0 and $virtuemart_category_id>0 ) {
				// Subcategory!
				if( isset( $root ) && in_array( $category_tmp[$row_list[$n]]["category_child_id"], $allowed_subcategories )
				|| $category_tmp[$row_list[$n]]["category_parent_id"] == $virtuemart_category_id
				|| $category_tmp[$row_list[$n]]["category_parent_id"] == $this->_category_tree[$virtuemart_category_id]["category_parent_id"]) {
					$allowed = true;

				}
			}
			else
			$allowed = true;
			$append = "";
			if( $allowed ) {
				if( $style == $highlighted_style ) {
					$append = 'id="active_menu"';
				}
				if( $depth_list[$n] > 0 ) {
					$css_class = "sublevel";
				}
				else {
					$css_class = $links_css_class;
				}
				$catname =htmlentities($category_tmp[$row_list[$n]]["category_name"], ENT_NOQUOTES, 'UTF-8');

				$html .= '
          <a title="'.$catname.'" style="display:block;'.$style.'" class="'. $css_class .'" href="'. JRoute::_ ( 'index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$category_tmp[$row_list[$n]]["category_child_id"]) .'" '.$append.'>'
				. str_repeat("&nbsp;&nbsp;&nbsp;",$depth_list[$n]) . $catname
				. self::countProducts( $category_tmp[$row_list[$n]]["category_child_id"] )
				.'</a>';
			}
		}

		return $html;
	}
	/**
	* This function is repsonsible for returning an array containing category information
	* @param boolean Show only published products?
	* @param string the keyword to filter categories
	*/
	function getCategoryTreeArray( $only_published=true, $keyword = "" ) {

		$db = JFactory::getDBO();
		if( empty( $this->_category_tree)) {

			// Get only published categories
			$query  = "SELECT `virtuemart_category_id`, `category_description`, `category_name`,`category_child_id`, `category_parent_id`,`#__virtuemart_categories`.`ordering`, `published` as category_publish
						FROM `#__virtuemart_categories`, `#__virtuemart_category_categories` WHERE ";
			if( $only_published ) {
				$query .= "`#__virtuemart_categories`.`published`=1 AND ";
			}
			$query .= "`#__virtuemart_categories`.`virtuemart_category_id`=`#__virtuemart_category_categories`.`category_child_id` ";
			if( !empty( $keyword )) {
				$query .= "AND ( `category_name` LIKE '%$keyword%' ";
				$query .= "OR `category_description` LIKE '%$keyword%' ";
				$query .= ") ";
			}
			$query .= "ORDER BY `#__virtuemart_categories`.`ordering` ASC, `#__virtuemart_categories`.`category_name` ASC";

			// initialise the query in the $database connector
			$db->setQuery($query);

			// Transfer the Result into a searchable Array
			$dbCategories = $db->loadAssocList();

		//if (!$ids = $db->loadObject())
			foreach( $dbCategories as $Cat ) {
				$this->_category_tree[$Cat['category_child_id']] = $Cat;
			}
		}
	}
		/**
	 * Sorts an array with categories so the order of the categories is the same as in a tree, just as a flat list.
	 * The Tree Depth is
	 *
	 * @param array $categoryArr
	 */
	function sortCategoryTreeArray() {
		// Copy the Array into an Array with auto_incrementing Indexes
		$key = array_keys($this->_category_tree); // Array of category table primary keys

		$nrows = $size = sizeOf($key); // Category count

		/** FIRST STEP
	    * Order the Category Array and build a Tree of it
	    **/

		$id_list = array();
		$row_list = array();
		$depth_list = array();

		$children = array();
		$parent_ids = array();
		$parent_ids_hash = array();

		//Build an array of category references
		$category_tmp = Array();
		for ($i=0; $i<$size; $i++)
		{
			$category_tmp[$i] = $this->_category_tree[$key[$i]];
			$parent_ids[$i] = $category_tmp[$i]['category_parent_id'];
			if($category_tmp[$i]["category_parent_id"] == 0)
			{
				array_push($id_list,$category_tmp[$i]["category_child_id"]);
				array_push($row_list,$i);
				array_push($depth_list,0);
			}

			$parent_id = $parent_ids[$i];

			if (isset($parent_ids_hash[$parent_id]))
			{
				$parent_ids_hash[$parent_id][$i] = $parent_id;

			}
			else
			{
				$parent_ids_hash[$parent_id] = array($i => $parent_id);
			}

		}

		$loop_count = 0;
		$watch = array(); // Hash to store children
		while(count($id_list) < $nrows) {
			if( $loop_count > $nrows )
			break;
			$id_temp = array();
			$row_temp = array();
			$depth_temp = array();
			for($i = 0 ; $i < count($id_list) ; $i++) {
				$id = $id_list[$i];
				$row = $row_list[$i];
				$depth = $depth_list[$i];
				array_push($id_temp,$id);
				array_push($row_temp,$row);
				array_push($depth_temp,$depth);

				$children = @$parent_ids_hash[$id];

				if (!empty($children))
				{
					foreach($children as $key => $value) {
						if( !isset($watch[$id][$category_tmp[$key]["category_child_id"]])) {
							$watch[$id][$category_tmp[$key]["category_child_id"]] = 1;
							array_push($id_temp,$category_tmp[$key]["category_child_id"]);
							array_push($row_temp,$key);
							array_push($depth_temp,$depth + 1);
						}
					}
				}
			}
			$id_list = $id_temp;
			$row_list = $row_temp;
			$depth_list = $depth_temp;
			$loop_count++;
		}
		return array('id_list' => $id_list,
								'row_list' => $row_list,
								'depth_list' => $depth_list,
								'category_tmp' => $category_tmp);
	}

}