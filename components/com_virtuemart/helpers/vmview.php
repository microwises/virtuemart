<?php
defined('_JEXEC') or die('');
/**
 * abstract controller class containing get,store,delete,publish and pagination
 *
 *
 * This class provides the functions for the calculatoins
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
// Load the view framework
jimport( 'joomla.application.component.view');
// Load default helpers

class VmView extends JView{

	/**
	 * Sets automatically the shortcut for the language and the redirect path
	 *
	 * @author Max Milbers
	 */
	// public function __construct() {
		// parent::construct();
	// }
	function getModel($name=null){

// 		if(!class_exists('VmView'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmview.php');
// 		return VmView::getModel($name);
		if (!$name) $name = JRequest::getCmd('view');
		$name = strtolower($name);
		$className = 'VirtueMartModel'.ucfirst($name);

// 		retrieving model
		if( !class_exists($className) ){

			$modelPath = JPATH_VM_ADMINISTRATOR.DS."models".DS.$name.".php";

			if( file_exists($modelPath) ){
				require( $modelPath );
			}
			else{
				JError::raiseWarning( 0, 'Model '. $name .' not found.' );
				echo 'Model '. $name .' not found.';die;
				return false;
			}
		}
		$model = new $className();
		if(empty($name)){
			JError::raiseWarning( 0, 'Model '. $name .' not created.' );
			echo 'Model '. $name .' not created.';
		}else {
			return $model;
		}

	}


}