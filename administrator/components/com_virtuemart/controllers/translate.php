<?php
/**
*
* Translate controller
*
* @package	VirtueMart
* @subpackage Translate
* @author Patrick Kohl
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL 2, see COPYRIGHT.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: translate.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

if(!class_exists('VmController'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmcontroller.php');


/**
 * Translate Controller
 *
 * @package    VirtueMart
 * @subpackage Translate
 * @author Patrick Kohl
 */
class VirtuemartControllerTranslate extends VmController {

	var $check 	= null;
	var $fields = null;


	function __construct() {
		parent::__construct();

	}

	public function Translate() {


		JRequest::checkToken( 'get' ) or jexit( '{"check":"Invalid Token"}' );

		$lang = JRequest::get('lang');
		$view = JRequest::getWord('editView');
		$tables = array ('category' =>'categories','product' =>'products','manufacturer' =>'manufacturers','vendor' =>'vendors');
		if ( !array_key_exists($view, $tables) ) jExit('{"check":"Invalid view"}');
		$id = JRequest::getInt('id');
		$db =& JFactory::getDBO();
		$db->setQuery($q);
		jExit();
		$document = JFactory::getDocument();
		$viewType	= $document->getType();
		$view = $this->getView($this->_cname, $viewType);

		// Pushing default model
		$model = $this->getModel();
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}

		$model1 = $this->getModel('Worldzones');
		if (!JError::isError($model1)) {
			$view->setModel($model1, false);
		}

		parent::display();
	}
	/**
	 * Paste the table  in json format
	 *
	 */
	public function paste() {

		// TODO Test user ?
		$json= array();
		$json['fields'] = 'error' ;
		$json['msg'] = 'Invalid Token';
		if (!JRequest::checkToken( 'get' )) {
			echo json_encode($json) ;
			jexit(  );
		}

		$lang = JRequest::getvar('lang');
		$language=& JFactory::getLanguage();
		if ( !$language->exists($lang, JPATH_SITE)){
			$json['msg'] = 'Invalid language ! '.$lang;
			echo json_encode($json) ;
			jexit(  );
		}

		// Remove tag if defaut or
		if ($language->getDefault() == $lang ) $dblang ='';
		else $dblang= substr($lang,0,2).'_';
		$id = JRequest::getInt('id',0);

		$viewKey = JRequest::getWord('editView');
		// TODO temp trick for vendor
		if ($viewKey == 'vendor') $id = 1 ;

		$tables = array ('category' =>'categories','product' =>'products','manufacturer' =>'manufacturers','vendor' =>'vendors');

		if ( !array_key_exists($viewKey, $tables) ) {
			$json['msg'] ="Invalid view ". $viewKey;
			echo json_encode($json);
			jExit();
		}
		$tableName = $tables[$viewKey];


		$db =& JFactory::getDBO();

		$q='select * from #__'.$dblang.'virtuemart_'.$tableName.' where virtuemart_'.$viewKey.'_id ='.$id;
		$db->setQuery($q);
		if ($json['fields'] = $db->loadAssoc()) {
			$json['msg'] = jText::_('COM_VIRTUEMART_SELECTED_LANG').':'.$lang;

		} else {
			$json['fields'] = 'error' ;
			$json['msg'] = jText::_('COM_VIRTUEMART_LANG_IS EMPTY') .$q ;
		}
		echo json_encode($json);
		jExit();


		// $document = JFactory::getDocument();
		// $viewType	= $document->getType();
		// $view = $this->getView($this->_cname, $viewType);

		// //Pushing default model
		// $model = $this->getModel();
		// if (!JError::isError($model)) {
			// $view->setModel($model, true);
		// }

		// $model1 = $this->getModel('Worldzones');
		// if (!JError::isError($model1)) {
			// $view->setModel($model1, false);
		// }

		// parent::display();
	}


}

//pure php no tag
