<?php
/**
* @version $Id: mod_mbt_transmenu.php
* @copyright (C) 2005 MamboTheme.com
*/
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

class Shop_Menu{
	var $menuObj; 
	var $_params = null;
	var $children = null;
	var $open = null;
	
	function Shop_Menu( $params ){
		$this->_params = $params;

		$this->loadMenu();
		$this->createmenuObj();
	}
	
	function createmenuObj (){
			switch ($this->_params->get( 'menutype' )){
				default:
					include_once("transmenu.php");
					$this->menuObj = new TransMenu($this);
				break;
			}
	}
	
	function  loadMenu(){
		$db = JFactory::getDBO();
		$query  = "SELECT virtuemart_category_id as id, category_parent_id as parent, category_name as name, '' as type,
							CONCAT('index.php?option=com_virtuemart&view=category&virtuemart_category_id=', id ) AS link,
							'-1' as browserNav, ordering as list_order
								FROM #__virtuemart_categories, #__virtuemart_category_categories 
								WHERE #__virtuemart_categories.enabled='1' 
									AND #__virtuemart_categories.virtuemart_category_id=#__virtuemart_category_categories.category_child_id 
								ORDER BY name ASC";
		
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		// establish the hierarchy of the menu
		$this->children = array();
		// first pass - collect children
		foreach ($rows as $v ) {
			$pt = $v->parent;
			$list = @$this->children[$pt] ? $this->children[$pt] : array();
			array_push( $list, $v );
			$this->children[$pt] = $list;
		}
		
		// second pass - collect 'open' menus
		$this->open = array( JRequest::getInt('category_id'));
	}
		
	function genMenu(){
		$this->beginMenu();
		$this->menuObj->beginMenu();
		$this->genMenuItems (0, 0);
		$this->menuObj->endMenu();
		$this->endMenu();
	}
	
	/*
	$pid: parent id
	$level: menu level
	$pos: position of parent
	*/
	function genMenuItems($pid, $level) {
		if (@$this->children[$pid]) {
			$i = 0;
			foreach ($this->children[$pid] as $row) {
				
				$this->menuObj->genMenuItem( $row, $level, $i);

				// show menu with menu expanded - submenus visible
				$this->genMenuItems( $row->id, $level+1 );
				$i++;
			}
		}
		
	}

	function beginMenu(){
		echo "<!-- Begin menu -->\n";
	}
	function endMenu(){
		echo "<!-- End menu -->\n";
	}
	function hasSubItems($id){
		if (@$this->children[$id]) return true;
		return false;
	}
}
?>
