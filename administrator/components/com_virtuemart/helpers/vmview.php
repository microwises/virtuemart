<?php
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
		if(!class_exists('ShopFunctions'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');
		return ShopFunctions::getModel($name);
	}


	/*
	 * set all commands and options for BE default.php views
	* return $list filter_order and
	*/
	function addStandardDefaultViewCommands($showNew=true, $showDelete=true) {

		JToolBarHelper::divider();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::editListX();
		if ($showNew) {
			JToolBarHelper::addNewX();
		}
		if ($showDelete) {
			JToolBarHelper::deleteList();
		}
	}

	/*
	 * set pagination and filters
	* return Array() $list( filter_order and dir )
	*/

	function addStandardDefaultViewLists($model, $default_order = null, $default_dir = null,$name = 'search') {

		$pagination = $model->getPagination();

		$this->assignRef('pagination', $pagination);

		/* set list filters */
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view', JRequest::getCmd('controller'));
		$mainframe = JFactory::getApplication();

		$lists[$name] = $mainframe->getUserStateFromRequest($option . '.' . $view . '.'.$name, $name, '', 'string');

		//$lists['filter_order'] = $model->getValidFilterOrdering($default_order); // break the list header column orderby choice
		$lists['filter_order'] = $mainframe->getUserStateFromRequest( 'com_virtuemart'.$view.'filter_order', 'filter_order', $default_order, 'cmd' );

		$lists['filter_order_Dir'] = $model->getValidFilterDir($default_dir);

		$this->assignRef('lists', $lists);

	}

	/*
	 * Add simple search to form
	* @param $searchLabel text to display before searchbox
	* @param $name 		 lists and id name
	* ??JText::_('COM_VIRTUEMART_NAME')
	*/

	function displayDefaultViewSearch($searchLabel, $value, $name ='search') {
		return JText::_('COM_VIRTUEMART_FILTER') . ' ' . JText::_($searchLabel) . ':
		<input type="text" name="' . $name . '" id="' . $name . '" value="' .$value . '" class="text_area" />
		<button onclick="this.form.submit();">' . JText::_('COM_VIRTUEMART_GO') . '</button>
		<button onclick="document.getElementById(\'' . $name . '\').value=\'\';this.form.submit();">' . JText::_('COM_VIRTUEMART_RESET') . '</button>';
	}
	function addStandardEditViewCommands($id = 0) {

		JToolBarHelper::divider();
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		// javascript for cookies setting in case of press "APPLY"
		$document = JFactory::getDocument();
				$isJ15 = VmConfig::isJ15();
		if ($isJ15) {
			$j = "
	function submitbutton(pressbutton) {

		jQuery( '#media-dialog' ).remove();
		var options = { path: '/', expires: 2}
		if (pressbutton == 'apply') {
			var idx = jQuery('#tabs li.current').index();
			jQuery.cookie('vmapply', idx, options);
		} else {
			jQuery.cookie('vmapply', '0', options);
		}
		 submitform(pressbutton);
	};" ;
		}
		else $j = "
	Joomla.submitbutton=function(a){
		var options = { path: '/', expires: 2}
		if (a == 'apply') {
			var idx = jQuery('#tabs li.current').index();
			jQuery.cookie('vmapply', idx, options);
		} else {
			jQuery.cookie('vmapply', '0', options);
		}
		jQuery( '#media-dialog' ).remove();
		Joomla.submitform(a);
	};" ;
		$document->addScriptDeclaration ( $j);

		// LANGUAGE setting

		$editView = JRequest::getWord('view',JRequest::getWord('controller','' ) );

		$params = JComponentHelper::getParams('com_languages');
		//$config =& JFactory::getConfig();$config->getValue('language');
		$selectedLangue = $params->get('site', 'en-GB');

		$lang = strtolower(strtr($selectedLangue,'-','_'));
		// only add if ID and view not null
		if ($editView and $id and (count(vmconfig::get('active_languages'))>1) ) {

			if ($editView =='user') $editView ='vendor';
			//$params = JComponentHelper::getParams('com_languages');
			jimport('joomla.language.helper');
			$lang = JRequest::getVar('vmlang', $lang);
			$languages = JLanguageHelper::createLanguageList($selectedLangue, constant('JPATH_SITE'), true);
			$activeVmLangs = (vmconfig::get('active_languages') );

			foreach ($languages as $k => &$joomlaLang) {
				if (!in_array($joomlaLang['value'], $activeVmLangs) )  unset($languages[$k] );
			}
			$langList = JHTML::_('select.genericlist',  $languages, 'vmlang', 'class="inputbox"', 'value', 'text', $selectedLangue , 'vmlang');
			$this->assignRef('langList',$langList);
			$this->assignRef('lang',$lang);



			$token = JUtility::getToken();
			$j = '
			jQuery(function($) {
				var oldflag = "";
				$("select#vmlang").chosen().change(function() {
					langCode = $(this).find("option:selected").val();
					flagClass = "flag-"+langCode.substr(0,2) ;
					$.getJSON( "index.php?option=com_virtuemart&view=translate&task=paste&format=json&lg="+langCode+"&id='.$id.'&editView='.$editView.'&'.$token.'=1" ,
						function(data) {
							var items = [];

							if (data.fields !== "error" ) {
								if (data.structure == "empty") alert(data.msg);
								$.each(data.fields , function(key, val) {
									cible = jQuery("#"+key);
									if (oldflag !== "") cible.parent().removeClass(oldflag)
									if (cible.parent().addClass(flagClass).children().hasClass("mce_editable") && data.structure !== "empty" ) tinyMCE.execInstanceCommand(key,"mceSetContent",false,val);
									else if (data.structure !== "empty") cible.val(val);
									});
								oldflag = flagClass ;
							} else alert(data.msg);
						}
					)
				});
			})';
			$document->addScriptDeclaration ( $j);
		} else {
			// $params = JComponentHelper::getParams('com_languages');
			// $lang = $params->get('site', 'en-GB');
			$jlang = JFactory::getLanguage();
			$langs = $jlang->getKnownLanguages();
			$defautName = $langs[$selectedLangue]['name'];
			$flagImg =JURI::root( true ).'/administrator/components/com_virtuemart/assets/images/flag/'.substr($lang,0,2).'.png';
			$langList = '<input name ="vmlang" type="hidden" value="'.$selectedLangue.'" ><img style="vertical-align: middle;" alt="'.$defautName.'" src="'.$flagImg.'"> <b> '.$defautName.'</b>';
			$this->assignRef('langList',$langList);
			$this->assignRef('lang',$lang);
		}

	}


	function SetViewTitle($name ='', $msg ='') {
		$view = JRequest::getWord('view', JRequest::getWord('controller'));
		if ($name == '')
		$name = $view;
		if ($msg) {
			$msg = ' <span style="color: #666666; font-size: large;">' . $msg . '</span>';
		}
		//$text = strtoupper('COM_VIRTUEMART_'.$name );
		$viewText = JText::_('COM_VIRTUEMART_' . $name);
		if (!$task = JRequest::getWord('task'))
		$task = 'list';

		$taskName = ' <small><small>[ ' . JText::_('COM_VIRTUEMART_' . $task) . ' ]</small></small>';
		JToolBarHelper::title($viewText . ' ' . $taskName . $msg, 'head vm_' . $view . '_48');
		$this->assignRef('viewName',$viewName);
	}
	function sort($orderby ,$name=null ){
		if (!$name) $name= 'COM_VIRTUEMART_'.strtoupper ($orderby);
		return JHTML::_('grid.sort' , JText::_($name) , $orderby , $this->lists['filter_order_Dir'] , $this->lists['filter_order']);

	}
}