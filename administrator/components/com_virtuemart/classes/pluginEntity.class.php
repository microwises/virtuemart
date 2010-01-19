<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
 * @version		$Id$
 * @package		classes
 * @copyright	Copyright (C) 2008 soeren - All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

class vmPluginEntity extends vmAbstractObject {
	
	/** @var string The name of the database table for this entity */
	var $table_name = '#__{vm}_plugins';
	/** @var string The key which is used to identify this object (example: product_id) */
	var $_key = 'id';
	var $_required_fields = array('element');

	function update( &$d ) {
		global $perm;
		if( !$this->validate_update($d)) {
			return false;
		}
		$id		= vmRequest::getInt('id');
		$params		= vmRequest::getVar( 'params', null, 'post', 'array' );

		// Build parameter INI string
		if (is_array($params)) {
			$txt = array ();
			foreach ($params as $k => $v) {
				if( is_array($v)) {
					$v = implode(',', $v );
				}
				$txt[] = "$k=$v";
			}
			$params = implode("\n", $txt);
		}
		$fields = array( 'name' => vmGet($d,'name'),
								'element' => vmGet($d, 'element'),
								'published' => vmRequest::getInt('published'),
								'params' => $params
					);
		$db = new ps_DB();
		$db->buildQuery('UPDATE', $this->table_name, $fields, 'WHERE id='.$id .($perm->check('admin')?' AND vendor_id='.$_SESSION['ps_vendor_id']:''));
		if( $db->query() === false ) {
			$GLOBALS['vmLogger']->err('Failed to update the Plugin');
			return false;
		}
		$GLOBALS['vmLogger']->info('The Plugin has been updated');
		return true;
	}

	function get_plugin_list($folder='') {
		global $perm;
		
		$dbp = new ps_DB();
		$q = 'SELECT * FROM #__{vm}_plugins WHERE 1=1';
		if( $folder != '') {
			$q .= ' AND folder=\''.$dbp->getEscaped($folder).'\'';
		}
	    if( !$perm->check('admin')) {
	    	$q.= ' AND vendor_id='.$_SESSION['ps_vendor_id'];
	    }
		$q .= ' ORDER BY folder, ordering';
		$dbp->query( $q );
		
		$plugins = array();
		while( $dbp->next_record()) {
			$result = array( 'id' => $dbp->f('id'),
						'element' => $dbp->f('element'),
						'shopper_group_id' => $dbp->f('shopper_group_id'),
						'name' => $dbp->f('name'),
						'iscore' => $dbp->f('iscore'),
						'ordering' => $dbp->f('ordering'),
						'params' => $dbp->f('params')
				);
			if( $folder != '') {
				$plugins[] = $result; 
			} else {
				$plugins[$dbp->f('folder')][] = $result; 
			}
		}
			
		return $plugins;
	}
	
	function get_plugin_folders() {
		$dbf = new ps_DB();
		$q = 'SELECT DISTINCT folder FROM #__{vm}_plugins ';
		$q .= 'ORDER BY folder';
		$dbf->query( $q );
		$folders = array();
		while( $dbf->next_record()) {
			$folders[] = $dbf->f('folder');
		}
		return $folders;
	}
	
	function get_folder_dropdown($name, $value) {
		$folders = vmPluginEntity::get_plugin_folders();
		$array = array();
		foreach( $folders as $folder ) {
			$array[$folder] = ucfirst($folder);
		}
		$array = array_merge(array('' => JText::_('VM_SELECT')), $array);
		
		return ps_html::selectList($name, $value, $array, 1, '', 'onchange="adminForm.submit()"');
	}
}
?>