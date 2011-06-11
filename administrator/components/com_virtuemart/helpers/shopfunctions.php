<?php
/**
 * General helper class
 *
 * This class provides some shop functions that are used throughout the VirtueMart shop.
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author RolandD
 * @copyright Copyright (c) 2004-2008 Soeren Eberhardt-Biermann, 2009 VirtueMart Team. All rights reserved.
 */

class ShopFunctions {

	/**
	 * @var global database object
	 */
	private $_db = null;


	/**
	 * Contructor
	 */
	public function __construct(){

		$this->_db = JFactory::getDBO();
	}

	/*
	* set all commands and options for BE default.php views
	* return $list filter_order and 
	*/
	function addStandardDefaultViewCommands ($showNew=true,$showDelete=true){

		JToolBarHelper::divider();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::editListX();
		if($showNew){
			JToolBarHelper::addNewX();
		}
		if($showDelete){
			JToolBarHelper::deleteList();
		}
	}
	/*
	* set pagination and filters 
	* return Array() $list( filter_order and dir )
	*/
	function addStandardDefaultViewLists ($model,$default_order = 'ordering',$default_dir = 'ASC'){

		$pagination = $model->getPagination();
		$this->assignRef('pagination',	$pagination);
		/* set list filters*/
		$option = JRequest::getCmd( 'option');
		$view = JRequest::getCmd( 'view',JRequest::getCmd( 'controller'));
		$mainframe = JFactory::getApplication() ;
		$lists['search'] = $mainframe->getUserStateFromRequest( $option.'.'.$view.'.search', 'search', '', 'string' );
		$lists['filter_order']     = $mainframe->getUserStateFromRequest( $option.'.'.$view.'.filter_order', 'filter_order', $default_order, 'cmd' );
		$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest( $option.'.'.$view.'.filter_order_Dir', 'filter_order_Dir', $default_dir, 'word' );
		return $lists ;
	}
	/*
	* Add simple search to form
	* @param $searchLabel text to display before searchbox
	* @param $name 		 lists and id name
	*/
	function displayDefaultViewSearch ($searchLabel = 'COM_VIRTUEMART_NAME',$name ='search') {
		return JText::_('COM_VIRTUEMART_FILTER').' '.JText::_($searchLabel).':
		<input type="text" name="'.$name.'" id="'.$name.'" value="'.$this->lists[$name].'" class="text_area" onchange="document.adminForm.submit();" />
		<button onclick="this.form.submit();">'.JText::_('COM_VIRTUEMART_GO').'</button>
		<button onclick="document.getElementById(\''.$name.'\').value=\'\';this.form.submit();">'.JText::_('COM_VIRTUEMART_RESET').'</button>' ;
	}

	function addStandardEditViewCommands (){

		JToolBarHelper::divider();
		JToolBarHelper::save();
        JToolBarHelper::apply();
		JToolBarHelper::cancel();
	}
	 
    function SetViewTitle($cssIcon,$view=null,$msg ='') {

            if (!$view) $view = JRequest::getVar('view',JRequest::getVar('controller'));
            //if ($msg) { $msg = ' <span style="color: #666666;float: right;font-size: large;">'.$msg.'</span>';}
             if ($msg) { $msg = ' <span style="color: #666666; font-size: large;">'.$msg.'</span>';}
             $text = strtoupper('COM_VIRTUEMART_'.$view );
            $viewName = JText::_($text);
			if (!$task = JRequest::getVar('task')) $task='list';
			
            $taskName = ' <small><small>[ '.JText::_('COM_VIRTUEMART_'.$task).' ]</small></small>';
            JToolBarHelper::title( JText::sprintf( 'COM_VIRTUEMART_STRING1_STRING2' ,$viewName, $taskName).$msg , $cssIcon);
            return $viewName;

    }
	
	/**
	 * Creates a Drop Down list of available Creditcards
	 *
	 * @author Max Milbers
	 */
	public function renderCreditCardList($ccId, $multiple = false) {

		$model = self::getModel('creditcard');
		$creditcards = $model->getCreditCards();

		$attrs = '';
		$name = 'creditcard_name';
		$idA = $id = 'virtuemart_creditcard_id';

		if ($multiple){
			$attrs = 'multiple="multiple"';
			$idA .= '[]';
		} else {
			$emptyOption = JHTML::_('select.option','', JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'), $id, $name);
			array_unshift($creditcards, $emptyOption);
		}
		$listHTML = JHTML::_('select.genericlist', $creditcards, $idA, $attrs, $id, $name, $ccId );
		return $listHTML;
	}

	/**
	* Creates a Drop Down list of available Vendors
	*
	* @author Max Milbers, RolandD
	* @access public
	* @param int $virtuemart_shoppergroup_id the shopper group to pre-select
	* @param bool $multiple if the select list should allow multiple selections
	* @return string HTML select option list
	*/
	public function renderVendorList($vendorId, $multiple = false) {

		$db = JFactory::getDBO();
		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		if( !Permissions::getInstance()->check('admin') ){
			if(empty($vendorId)){
				$vendorId = 1;
				//Dont delete this message, we need it later for multivendor
				//JError::raiseWarning(1,'renderVendorList $vendorId is empty, please correct your used model to automatically set the virtuemart_vendor_id to the logged Vendor');
			}
			$q = 'SELECT `vendor_name` FROM #__virtuemart_vendors WHERE `virtuemart_vendor_id` = "'.$vendorId.'" ';
			$db->setQuery($q);
			$vendor = $db->loadResult();
			$html = '<input type="text" size="14" name="vendor_name" class="inputbox" value="'.$vendor.'" readonly="">';
//			$html .='<input type="hidden" value="'.$vendorId.'" name="virtuemart_vendor_id">';
			return $html;
		} else {

			$q = 'SELECT `virtuemart_vendor_id`,`vendor_name` FROM #__virtuemart_vendors';
			$db->setQuery($q);
			$vendors = $db->loadAssocList();

			$attrs = '';
			$name = 'vendor_name';
			$idA = $id = 'virtuemart_vendor_id';


			if ($multiple){
				$attrs = 'multiple="multiple"';
				$idA .= '[]';
			} else {
				$emptyOption = JHTML::_('select.option','', JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'), $id, $name);
				array_unshift($vendors, $emptyOption);
			}
			$listHTML = JHTML::_('select.genericlist', $vendors, $idA, $attrs, $id, $name, $vendorId );
			return $listHTML;
		}
	}

	/**
	* Creates a Drop Down list of available Shopper Groups
	*
	* @author Max Milbers, RolandD
	* @access public
	* @param int $virtuemart_shoppergroup_id the shopper group to pre-select
	* @param bool $multiple if the select list should allow multiple selections
	* @return string HTML select option list
	*/
	public function renderShopperGroupList($shopperGroupId=0, $multiple = false) {
		$shopperModel = self::getModel('shoppergroup');
		$shoppergrps = $shopperModel->getShopperGroups(false,true);
		$attrs = '';
		$name = 'shopper_group_name';
		$idA = $id = 'virtuemart_shoppergroup_id';

		if ($multiple){
			$attrs = 'multiple="multiple"';
			$idA .= '[]';
		} else {
			$emptyOption = JHTML::_('select.option','', JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'), $id, $name);
			array_unshift($shoppergrps, $emptyOption);
		}
		$listHTML = JHTML::_('select.genericlist', $shoppergrps, $idA, $attrs, $id, $name, $shopperGroupId );
		return $listHTML;
	}

	/**
	* Render a simple country list
	* @author jseros, Max Milbers
	*
	* @param int $countryId Selected country id
	* @param boolean $multiple True if multiple selecions are allowed (default: false)
	* @param mixed $_attrib string or array with additional attibutes,
	* e.g. 'onchange=somefunction()' or array('onchange'=>'somefunction()')
	* @param string $_prefix Optional prefix for the formtag name attribute
	* @return string HTML containing the <select />
	*/
	public function renderCountryList( $countryId = 0 , $multiple = false, $_attrib = array(), $_prefix = ''){
		$countryModel = self::getModel('country');
		$countries = $countryModel->getCountries(true, true);
		$attrs = array();
		$name = 'country_name';
		$id = 'virtuemart_country_id';
		$idA = $_prefix . 'virtuemart_country_id';

		if($multiple){
			$attrs['multiple'] = 'multiple';
			$attrs['size'] = '12';
			$idA .= '[]';
		} else {
			$emptyOption = JHTML::_('select.option','', JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'), $id, $name);
			array_unshift($countries, $emptyOption);
		}

		if (is_array($_attrib)) {
			$attrs = array_merge ($attrs, $_attrib);
		} else {
			$_a = explode ('=', $_attrib, 2);
			$attrs[$_a[0]] = $_a[1];
		}
		
		return JHTML::_('select.genericlist', $countries, $idA, $attrs, $id, $name, $countryId );
	}


	/**
	* Render a simple state list
	* @author jseros
	*
	* @param int $stateID Selected state id
	* @param int $countryID Selected country id
	* @param string $dependentField Parent <select /> ID attribute
	* @param string $_prefix Optional prefix for the formtag name attribute
	* @return string HTML containing the <select />
	*/
	public function renderStateList( $stateId = 0, $countryId = 0, $dependentField = '', $multiple = false, $_prefix = ''){
		$document = JFactory::getDocument();
		$stateModel = self::getModel('state');
		// Must be done here also (despite the AJAX selector) to make the current dbselection visible
		//$states = $stateModel->getStates($countryId,true);dump($countryId,'$countryId');
		$attrs = array();
		$name = 'state_name';
		$idA = $id = $_prefix.'virtuemart_state_id';
		
		if($multiple){
			$attrs['multiple'] = 'multiple';
			$idA .= '[]';
			$attrs['size'] = '12';
		} else {
			$attrs['size'] = 1;
	//		$emptyOption = JHTML::_('select.option','', JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'), $id, $name);
	//		array_unshift($states, $emptyOption);
		}

		VmConfig::JcountryStateList() ;
		$attrs['class'] = 'dependent['. $dependentField .']';

		$emptyOption = array(JHTML::_('select.option','', JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'), $id, $name));
		$listHTML = JHTML::_('select.genericlist', $emptyOption, $idA,  $attrs, $id, $name, $stateId, $id);

		if(!is_array($stateId)) $stateId = array($stateId);
		foreach($stateId as $state){
			$listHTML .= '<input type="hidden" name="prs_virtuemart_state_id[]" value="'.$state.'" />' ;
		}
		dump($listHTML,'dump');
		return $listHTML;
	}

	/**
	 * Creates the chooseable template list
	 *
	 * @author Max Milbers, impleri
	 *
	 * @param string defaultText Text for the empty option
	 * @param boolean defaultOption you can supress the empty otion setting this to false
	 * return array of Template objects
	 */
	public function renderTemplateList($defaultText = 0,$defaultOption=true){

		if(empty($defaultText)) $defaultText = JText::_('COM_VIRTUEMART_TEMPLATE_DEFAULT');

		$templateList = array();

		$defaulttemplate = array();
		if($defaultOption){
			$defaulttemplate[0] = new stdClass;
			$defaulttemplate[0] -> name = $defaultText;
			$defaulttemplate[0] -> directory = 0;
			$defaulttemplate[0] -> value = 'default';
		}

		if (VmConfig::isJ15()) {
			if(!class_exists('TemplatesHelper')) require (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_templates'.DS.'helpers'.DS.'template.php');
			$jtemplates = TemplatesHelper::parseXMLTemplateFiles(JPATH_SITE.DS.'templates');
		} else {
			require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_templates'.DS.'helpers'.DS.'templates.php');
			require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_templates'.DS.'models'.DS.'templates.php');
			$templatesModel = new TemplatesModelTemplates();
			$jtemplates = $templatesModel->getItems();
			//@TODO remove templates for admin panel.
		}
		foreach($jtemplates as $template){
			$template->value = $template->name;
		}

		return array_merge($defaulttemplate,$jtemplates);
	}

	/**
	 * Creates structured option fields for all categories
	 *
	 * @todo: Connect to vendor data
	 * @author RolandD, Max Milbers, jseros
	 * @param array 	$selectedCategories All category IDs that will be pre-selected
	 * @param int 		$cid 		Internally used for recursion
	 * @param int 		$level 		Internally used for recursion
	 * @return string 	$category_tree HTML: Category tree list
	 */
	public function categoryListTree($selectedCategories = array(), $cid = 0, $level = 0, $disabledFields=array()) {

		static $categoryTree = '';

		//We have every where multi selection? so we dont need this anylonger
//		if($level==0){
//			$categoryTree .= '<option value="">'.JText::_('COM_VIRTUEMART_SEL_CATEGORY').'</option>';
//		}
		$virtuemart_vendor_id = 1;

		$categoryModel = self::getModel('category');
		$level++;

		$records = $categoryModel->getCategoryTree(true, true, $cid);
		$selected="";
		if(!empty($records)){
			foreach ($records as $key => $category) {

				$childId = $category->category_child_id;

				if ($childId != $cid) {
					if(in_array($childId, $selectedCategories)) $selected = 'selected=\"selected\"'; else $selected='';

					$disabled = '';
					if( in_array( $childId, $disabledFields )) {
						$disabled = 'disabled="disabled"';
					}

					if( $disabled != '' && stristr($_SERVER['HTTP_USER_AGENT'], 'msie') ) {
						//IE7 suffers from a bug, which makes disabled option fields selectable
					}
					else{
						$categoryTree .= '<option '. $selected .' '. $disabled .' value="'. $childId .'">'."\n";
						$categoryTree .= str_repeat(' - ', ($level-1) );

						$categoryTree .= $category->category_name .'</option>';
					}
				}

				self::categoryListTree($selectedCategories, $childId, $level, $disabledFields);
			}
		}


		return $categoryTree;
	}


   	/**
	 * Gets the total number of product for category
	 *
     * @author jseros
     * @param int $categoryId Own category id
	 * @return int Total number of products
	 */
	public function countProductsByCategory( $categoryId = 0 )
	{
		$categoryModel = self::getModel('category');
        return $categoryModel->countProducts($categoryId);
    }

	/**
	* Return the countryname or code of a given countryID
	*
	* @author Oscar van Eijk
	* @access public
	* @param int $_id Country ID
	* @param char $_fld Field to return: country_name (default), country_2_code or country_3_code.
	* @return string Country name or code
	*/
	public function getCountryByID ($_id, $_fld = 'country_name')
	{
		if (empty($_id) && $_id !== 0) { //It must not be empty and it must be not 0 ??
//		if (empty($_id)){
		return ""; // Nothing to do
		}
		$_db = JFactory::getDBO();

		$_q = 'SELECT ' . $_fld . ' AS fld FROM `#__virtuemart_countries` WHERE virtuemart_country_id = ' . $_id;
		$_db->setQuery($_q);
		$_r = $_db->loadResult();
		return $_r;
	}

	/**
	* Return the countryID of a given country name
	*
	* @author Oscar van Eijk
	* @access public
	* @param string $_name Country name
	* @return int Country ID
	*/
	public function getCountryIDByName ($_name = '')
	{
		if ($_name == '') {
			return 0;
		}
		$_db = JFactory::getDBO();

		$_q = "SELECT `virtuemart_country_id` FROM `#__virtuemart_countries` WHERE `country_name` = '$_name'";
		$_db->setQuery($_q);
		$_r = $_db->loadResult();
		return $_r;
	}

	/**
	* Return the statename or code of a given countryID
	*
	* @author Oscar van Eijk
	* @access public
	* @param int $_id State ID
	* @param char $_fld Field to return: state_name (default), state_2_code or state_3_code.
	* @return string state name or code
	*/
	public function getStateByID ($_id, $_fld = 'state_name')
	{
		if (empty($_id) && $_id !== 0) {
//		if (empty($_id)){
			return ""; // Nothing to do
		}
		$_db = JFactory::getDBO();

		$_q = 'SELECT ' . $_fld . ' AS fld FROM `#__virtuemart_states` WHERE virtuemart_state_id = ' . $_id;
		$_db->setQuery($_q);
		$_r = $_db->loadObject();
		return $_r->fld;
	}

	public function getShippingRateDetails($_id)
	{
		$_db = JFactory::getDBO();

		$_q = 'SELECT c.shipping_carrier_name AS carrier '
			. ', s.shipping_rate_name AS name '
			. 'FROM `#__virtuemart_shippingrates` AS s '
			. ', `#__virtuemart_shippingcarriers` AS c '
			. 'WHERE s.virtuemart_shippingrate_id = ' . $_id . ' '
			. 'AND s.shipping_rate_carrier_id = c.virtuemart_shippingcarrier_id '
		;
		$_db->setQuery($_q);
		return $_db->loadObject();
	}

	/**
	 * Print a select-list with enumerated categories
	 *
     * @author jseros
     *
	 * @param boolean $onlyPublished Show only published categories?
	 * @param boolean $withParentId Keep in mind $parentId param?
	 * @param integer $parentId Show only its childs
	 * @param string $attribs HTML attributes for the list
	 * @return string <Select /> HTML
	 */
	public function getEnumeratedCategories( $onlyPublished = true, $withParentId = false, $parentId = 0, $name = '', $attribs = '', $key = '', $text = '', $selected = null )
	{
		$categoryModel = self::getModel('category');

		$categories = $categoryModel->getCategoryTree($onlyPublished, $withParentId, (int)$parentId);

		foreach($categories as $index => $cat){
			$cat->category_name = $cat->ordering .'. '. $cat->category_name;
			$categories[$index] = $cat;
		}

		return JHTML::_('Select.genericlist', $categories, $name, $attribs, $key, $text, $selected, $name);
    }

	/**
	* Return model instance. This is a DRY solution!
	* This is only called within this class
	*
	* @author jseros
	* @access private
	*
	* @param string $name Model name
	* @return JModel Instance any model
	*/
	public function getModel($name = ''){

		$name = strtolower($name);
		$className = ucfirst($name);

		//retrieving model
		if( !class_exists('VirtueMartModel'.$className) ){

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

		$className = 'VirtueMartModel'.$className;
		//instancing the object
		$model = new $className();

		if(empty($model)){
			JError::raiseWarning( 0, 'Model '. $name .' not created.' );
			echo 'Model '. $name .' not created.';
		}else {
			return $model;
		}

	}

	/**
	* Return the order status name for a given code
	*
	* @author Oscar van Eijk
	* @access public
	*
	* @param char $_code Order status code
	* @return string The name of the order status
	*/
	public function getOrderStatusName ($_code)
	{
		$_db = JFactory::getDBO();

		$_q = 'SELECT order_status_name FROM `#__virtuemart_orderstates`'
			. " WHERE order_status_code = '$_code' ";
		$_db->setQuery($_q);
		$_r = $_db->loadObject();
		return $_r->order_status_name;
	}

	/**
	 * TODO this should work with userfields
	 * Lists titles for people
	 *
	 * @param string $t The selected title value
	 * @param string $extra More attributes when needed
	 * @param string $_prefix Optional prefix for the formtag name attribute
	 */
/*	public function listUserTitle($t, $extra="", $_prefix = '') {
		$vmConfig = VmConfig::getInstance();
		$titles = $vmConfig->get('titles');
		$options = array();
		foreach ($titles as $title) { 
			$option = JText::_($title);
			$options[] = JHTML::_('select.option',$option ,$option);
		}
		return JHTML::_('select.genericlist', $options, $_prefix . 'title', $extra, 'value', 'text', $t);
	}*/

	/**
	 * Creates an drop-down list with numbers from 1 to 31 or of the selected range,
	 * dont use within virtuemart. It is just meant for paymentmethods
	 *
	 * @param string $list_name The name of the select element
	 * @param string $selected_item The pre-selected value
	 */
	function listDays($list_name,$selected=false, $start=null, $end=null) {
		$options = array();
		if (!$selected) $selected = date('d');
		$start = $start ? $start : 1;
		$end = $end ? $end : $start + 30;
		$options[] = JHTML::_('select.option', 0, JText::_('DAY'));
		for ($i=$start; $i<=$end; $i++) {
			$options[] = JHTML::_('select.option', $i, $i);
		}
		return JHTML::_('select.genericlist', $options, $list_name, '', 'value', 'text', $selected);
	}



	/**
	 * Creates a Drop-Down List for the 12 months in a year
	 *
	 * @param string $list_name The name for the select element
	 * @param string $selected_item The pre-selected value
	 *
	 */
	function listMonths($list_name, $selected=false) {
		$options = array();
		if (!$selected) $selected = date('m');

		$options[] = JHTML::_('select.option', 0, JText::_('MONTH'));
		$options[] = JHTML::_('select.option', "01", JText::_('JAN'));
		$options[] = JHTML::_('select.option', "02", JText::_('FEB'));
		$options[] = JHTML::_('select.option', "03", JText::_('MAR'));
		$options[] = JHTML::_('select.option', "04", JText::_('APR'));
		$options[] = JHTML::_('select.option', "05", JText::_('MAY'));
		$options[] = JHTML::_('select.option', "06", JText::_('JUN'));
		$options[] = JHTML::_('select.option', "07", JText::_('JUL'));
		$options[] = JHTML::_('select.option', "08", JText::_('AUG'));
		$options[] = JHTML::_('select.option', "09", JText::_('SEP'));
		$options[] = JHTML::_('select.option', "10", JText::_('OCT'));
		$options[] = JHTML::_('select.option', "11", JText::_('NOV'));
		$options[] = JHTML::_('select.option', "12", JText::_('DEC'));
		return JHTML::_('select.genericlist', $options, $list_name, '', 'value', 'text', $selected);
	}

	/**
	 * Creates an drop-down list with years of the selected range or of the next 7 years
	 *
	 * @param string $list_name The name of the select element
	 * @param string $selected_item The pre-selected value
	 */
	function listYears($list_name, $selected=false, $start=null, $end=null) {
		$options = array();
		if (!$selected) $selected = date('Y');
		$start = $start ? $start : date('Y');
		$end = $end ? $end : $start + 7;
		$options[] = JHTML::_('select.option', 0, JText::_('YEAR'));
		for ($i=$start; $i<=$end; $i++) {
			$options[] = JHTML::_('select.option', $i, $i);
		}
		return JHTML::_('select.genericlist', $options, $list_name, '', 'value', 'text', $selected);
	}

	function checkboxListArr( $arr, $tag_name, $tag_attribs,  $key='value', $text='text',$selected=null, $required=0  ) {
		reset( $arr );
		$html = array();
		$n=count( $arr );
		for ($i=0; $i < $n; $i++ ) {
				$k = $arr[$i]->$key;
				$t = $arr[$i]->$text;
				$id = isset($arr[$i]->id) ? $arr[$i]->id : null;

				$extra = '';
				$extra .= $id ? " id=\"" . $arr[$i]->id . "\"" : '';
				if (is_array( $selected )) {
						foreach ($selected as $obj) {
								$k2 = $obj->$key;
								if ($k == $k2) {
										$extra .= " checked=\"checked\"";
										break;
								}
						}
				} else {
						$extra .= ($k == $selected ? " checked=\"checked\"" : '');
				}
				$tmp = "<input type=\"checkbox\" name=\"$tag_name\" id=\"".str_replace('[]', '', $tag_name)."_field$i\" value=\"".$k."\"$extra $tag_attribs />" . "<label for=\"".str_replace('[]', '', $tag_name)."_field$i\">";
				$tmp .= JText::_($t);
				$tmp .= "</label>";
				$html[] = $tmp;
		}
		return $html;
	}

	function checkboxList( $arr, $tag_name, $tag_attribs,  $key='value', $text='text',$selected=null, $required=0 ) {
			return "\n\t".implode("\n\t", vmCommonHTML::checkboxListArr( $arr, $tag_name, $tag_attribs,  $key, $text,$selected, $required ))."\n";
	}
	function checkboxListTable( $arr, $tag_name, $tag_attribs,  $key='value', $text='text',$selected=null, $cols=0, $rows=0, $size=0, $required=0 ) {
			$cellsHtml = self::checkboxListArr( $arr, $tag_name, $tag_attribs,  $key, $text,$selected, $required );
			return self::list2Table( $cellsHtml, $cols, $rows, $size );
	}
	// private methods:
	private function list2Table( $cellsHtml, $cols, $rows, $size ) {
		$cells = count($cellsHtml);
		if ($size == 0) {
				$localstyle = ""; //" style='width:100%'";
		} else {
				$size = (($size-($size % 3)) / 3  ) * 2; // int div  3 * 2 width/heigh ratio
				$localstyle = " style='width:".$size."em;'";
		}
		$return="";
		if ($cells) {
				if ($rows) {
						$return = "\n\t<table class='vmMulti'".$localstyle.">";
						$cols = ($cells-($cells % $rows)) / $rows;      // int div
						if ($cells % $rows) $cols++;
						$lineIdx=0;
						for ($lineIdx=0 ; $lineIdx < min($rows, $cells) ; $lineIdx++) {
								$return .= "\n\t\t<tr>";
								for ($i=$lineIdx ; $i < $cells; $i += $rows) {
										$return .= "<td>".$cellsHtml[$i]."</td>";
								}
								$return .= "</tr>\n";
						}
						$return .= "\t</table>\n";
				} else if ($cols) {
						$return = "\n\t<table class='vmMulti'".$localstyle.">";
						$idx=0;
						while ($cells) {
								$return .= "\n\t\t<tr>";
								for ($i=0, $n=min($cells,$cols); $i < $n; $i++, $cells-- ) {
										$return .= "<td>".$cellsHtml[$idx++]."</td>";
								}
								$return .= "</tr>\n";
						}
						$return .= "\t</table>\n";
				} else {
						$return = "\n\t".implode("\n\t ", $cellsHtml)."\n";
				}
		}
		return $return;
	}

	/**
	 * Prints a JS function to validate all fields
	 * given in the array $required_fields
	 * Does only test if non-empty (or if no options are selected)
	 * Includes a check for a valid email-address
	 *
	 * @param array $required_fields The list of form elements that are to be validated
	 * @param string $formname The name for the form element
	 * @param string $div_id_postfix The ID postfix to identify the label for the field
	 */
	function printJsFormValidation( $required_fields, $allfields, $formname = 'adminForm', $functioname='submitregistration', $div_id_postfix = '_div' ) {
        $field_list = implode( "','", array_keys( $required_fields ) );
        $field_list = str_replace( "'email',", '', $field_list );
        $field_list = str_replace( "'username',", '', $field_list );
        $field_list = str_replace( "'password',", '', $field_list );
        $field_list = str_replace( "'password2',", '', $field_list );

        echo '
            <script language="javascript" type="text/javascript">//<![CDATA[
            function '.$functioname.'() {
                var form = document.'.$formname.';
                var r = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", "i");
                var isvalid = true;
                var required_fields = new Array(\''. $field_list.'\');
            	for (var i=0; i < required_fields.length; i++) {
                    formelement = eval( \'form.\' + required_fields[i] );
                    ';
       	echo "
                    if( !formelement ) {
                            formelement = document.getElementById( required_fields[i]+'_field0' );
                            var loopIds = true;
                    }
                    if( !formelement ) { continue; }
                    if (formelement.type == 'radio' || formelement.type == 'checkbox') {
                        if( loopIds ) {
                                var rOptions = new Array();
                                for(var j=0; j<30; j++ ) {
                                        rOptions[j] = document.getElementById( required_fields[i] + '_field' + j );
                                        if( !rOptions[j] ) { break; }
                                }
                        } else {
                                var rOptions = form[formelement.getAttribute('name')];
                        }
                        var rChecked = 0;
                        if(rOptions.length > 1) {
                                for (var r=0; r < rOptions.length; r++) {
                                        if( !rOptions[r] ) { continue; }
                                        if (rOptions[r].checked) {      rChecked=1; }
                                }
                        } else {
                                if (formelement.checked) {
                                        rChecked=1;
                                }
                        }
                        if(rChecked==0) {
                        	document.getElementById(required_fields[i]+'$div_id_postfix').className += ' missing';
                            isvalid = false;
                    	}
                    	else if (document.getElementById(required_fields[i]+'$div_id_postfix').className == 'formLabel missing') {
                            document.getElementById(required_fields[i]+'$div_id_postfix').className = 'formLabel';
                        }
                    }
                    else if( formelement.options ) {
                        if(formelement.selectedIndex.value == '') {
                                document.getElementById(required_fields[i]+'$div_id_postfix').className += ' missing';
                                isvalid = false;
                        }
                        else if (document.getElementById(required_fields[i]+'$div_id_postfix').className == 'formLabel missing') {
                                document.getElementById(required_fields[i]+'$div_id_postfix').className = 'formLabel';
                        }
                    }
                    else {
                        if (formelement.value == '') {
                            document.getElementById(required_fields[i]+'$div_id_postfix').className += ' missing';
                            isvalid = false;
                        }
                        else if (document.getElementById(required_fields[i]+'$div_id_postfix').className == 'formLabel missing') {
                            document.getElementById(required_fields[i]+'$div_id_postfix').className = 'formLabel';
	                    }
    	        	}
	            }
            ";
       	$optional_check = '';
		if (VmConfig::get('vm_registration_type') == 'OPTIONAL_REGISTRATION') {
			$optional_check = '&& form.register_account.checked';
		}
	    // We have skipped email in the first loop above!
	    // Now let's handle email address validation
	    if( isset( $required_fields['email'] )) {

	   		echo '
			if( !(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(form.email.value))) {
				alert( \''. str_replace("'","\\'",JText::_('REGWARN_MAIL')) .'\');
				return false;
			}';

		}
		if( isset( $required_fields['username'] )) {

			echo '
			if ((r.exec(form.username.value) || form.username.value.length < 3)'.$optional_check.') {
				alert( "'. JText::sprintf('VALID_AZ09', JText::_('USERNAME'), 2) .'" );
				return false;
            }';
        }
        if( isset($required_fields['password']) ) {
			if( JRequest::getVar('view') == 'checkout.index') {
                echo '
                if (form.password.value.length < 6 '.$optional_check.') {
                    alert( "'.JText::_('REGWARN_PASS',false) .'" );
					return false;
                } else if (form.password2.value == ""'.$optional_check.') {
                    alert( "'. JText::_('REGWARN_VPASS1',false) .'" );
                    return false;
                } else if (r.exec(form.password.value)'.$optional_check.') {
                    alert( "'. JText::sprintf('VALID_AZ09', JText::_('PASSWORD'), 6 ) .'" );
                    return false;
                }';
        	}
            echo '
                if ((form.password.value != "") && (form.password.value != form.password2.value)'.$optional_check.'){
                    alert( "'. JText::_('REGWARN_VPASS2',false) .'" );
                    return false;
                }';
        }
        if( isset( $required_fields['agreed'] )) {
			echo '
            if (!form.agreed.checked) {
				alert( "'. JText::_('COM_VIRTUEMART_AGREE_TO_TOS',false) .'" );
				return false;
			}';
		}
		foreach( $allfields as $field ) {
			if(  $field->type == 'euvatid' ) {
				$euvatid = $field->name;
				break;
			}
		}
		if (!empty($euvatid) ) {
			$document = JFactory::getDocument();
			$document->addScript(JURI::root().'components/com_virtuemart/js/euvat_check.js');
			echo '
			if( form.'.$euvatid.'.value != \'\' ) {
				if( !isValidVATID( form.'.$euvatid.'.value )) {
					alert( \''.addslashes(JText::_('VALID_EUVATID',false)).'\' );
					return false;
				}
			}';
		}
		// Finish the validation function
		echo '
			if( !isvalid) {
				alert("'.addslashes( JText::_('CONTACT_FORM_NC',false) ) .'" );
			}
			return isvalid;
		}
	            //]]>
	    </script>';
	}

	/**
	* Validates an EU-vat number, What is this?
	* @author RolandD
	* @param string $euvat EU-vat number to validate
	* @return boolean The result of the validation
	*/
	public function validateEUVat($euvat) {
		if(!class_exists('VmEUVatCheck')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'euvatcheck.php');
		$vatcheck = new VmEUVatCheck($euvat);
		return $vatcheck->validvatid;
	}

	/**
	* Validates an email address by using regular expressions
	* Does not resolve the domain name!
	*
	* Joomla has it's own e-mail checker but is no good JMailHelper::isEmailAddress()
	* maybe in the future it will be better
	*
	* @param string $email
	* @return boolean The result of the validation
	*/
	function validateEmail($email) {
		$valid = preg_match( '/^[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}$/', $email );
		return $valid;
	}

	/**
	 * Creates the Quantity Input Boxes/Radio Buttons/Lists for Products
	 *
	 * @param object $product The product details
	 * @param string $child
	 * @param string $use_parent
	 * @return string
	 */
	function getQuantityBoxOptions($product, $child = false, $use_parent = 'N') {
		$session = JFactory::getSession();
		$cart = $session->get("cart", null);

		if ($child == 'Y') {
			//We have a child list so get the current quantity;
			$quantity = 0 ;
			foreach ($cart->products as $productCart){
				if ($productCart["virtuemart_product_id"] == $product->virtuemart_product_id) {
					$quantity = $productCart["quantity"];
				}
			}
		}
		else {
			$quantity = JRequest::getInt('quantity', 1);
		}

		// Detremine which style to use
		if ($use_parent == 'Y' && $product->parent_virtuemart_product_id !=0) $id = $product->parent_virtuemart_product_id;
		else $id = $product->virtuemart_product_id ;

		//Get style to use
		extract($product->quantity_options);

		//Start output of quantity
		//Check for incompatabilities and reset to normal
		$display_type = null;
		if (VmConfig::get('check_stock') == '1' && ! $product->product_in_stock ) {
			$display_type = 'hide' ;
		}
		if (empty($display_type)
			|| ($display_type == "hide" && $child == 'Y')
			|| ($display_type == "radio" && $child == 'YM')
			|| ($display_type == "radio" && !$child) ) {
				$display_type = "none" ;
		}

		//todo what is this?
		echo '<pre>'.print_r($quantity_options,1).'</pre>';
		exit;

		$tpl->set( 'prod_id', $prod_id ) ;
		$tpl->set( 'quantity', $quantity ) ;
		$tpl->set( 'display_type', $display_type ) ;
		$tpl->set( 'child', $child ) ;
		$tpl->set( 'quantity_options', $quantity_options ) ;

		//Determine if label to be used
		$html = $tpl->fetch( 'product_details/includes/quantity_box_general.tpl.php' ) ;

		return $html ;

	}

	/**
	* Return $str with all but $display_length at the end as asterisks.
	* @author gday
	*
	* @access public
	* @param string $str The string to mask
	* @param int $display_length The length at the end of the string that is NOT masked
	* @param boolean $reversed When true, masks the end. Masks from the beginning at default
	* @return string The string masked by asteriks
	*/
	public function asteriskPad($str, $display_length, $reversed = false) {

		$total_length = strlen($str);

		if($total_length > $display_length) {
			if( !$reversed) {
				for($i = 0; $i < $total_length - $display_length; $i++) {
					$str[$i] = "*";
				}
			}
			else {
				for($i = $total_length-1; $i >= $total_length - $display_length; $i--) {
					$str[$i] = "*";
				}
			}
		}

		return($str);
	}
	/**
	 * Return the icon to move an item UP
	 *
	 * @access	public
	 * @param	int		$i The row index
	 * @param	boolean	$condition True to show the icon
	 * @param	string	$task The task to fire
	 * @param	string	$alt The image alternate text string
	 * @return	string	Either the icon to move an item up or a space
	 * @since	1.0
	 */
	function orderUpIcon($i, $condition = true, $task = 'orderup', $alt = 'Move Up', $enabled = true)
	{
		$alt = JText::_($alt);

		$html = '&nbsp;';
		if ($i > 0 )
		{
			if($enabled) {
				$html	= '<a href="#reorder"  class="orderUp" title="'.$alt.'">';
				$html	.= '   <img src="images/uparrow.png" width="16" height="16" border="0" alt="'.$alt.'" />';
				$html	.= '</a>';
			} else {
				$html	= '<img src="images/uparrow0.png" width="16" height="16" border="0" alt="'.$alt.'" />';
			}
		}

		return $html;
	}

	/**
	 * Return the icon to move an item DOWN
	 *
	 * @access	public
	 * @param	int		$i The row index
	 * @param	int		$n The number of items in the list
	 * @param	boolean	$condition True to show the icon
	 * @param	string	$task The task to fire
	 * @param	string	$alt The image alternate text string
	 * @return	string	Either the icon to move an item down or a space
	 * @since	1.0
	 */
	function orderDownIcon($i, $n, $condition = true, $task = 'orderdown', $alt = 'Move Down', $enabled = true)
	{
		$alt = JText::_($alt);

		$html = '&nbsp;';
		if ($i < $n -1 )
		{
			if($enabled) {
				$html	= '<a href="#reorder" class="orderDown" title="'.$alt.'">';
				$html	.= '  <img src="images/downarrow.png" width="16" height="16" border="0" alt="'.$alt.'" />';
				$html	.= '</a>';
			} else {
				$html	= '<img src="images/downarrow0.png" width="16" height="16" border="0" alt="'.$alt.'" />';
			}
		}

		return $html;
	}

}

//pure php no tag
