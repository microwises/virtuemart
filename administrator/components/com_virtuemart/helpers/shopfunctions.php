<?php
defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * General helper class
 *
 * This class provides some shop functions that are used throughout the VirtueMart shop.
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author RolandD
 * @author Max Milbers
 * @author Patrick Kohl
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
	public function __construct() {

		$this->_db = JFactory::getDBO();
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

	/**
	 * Builds an enlist for information (not chooseable)
	 *
	 * //TODO check for misuse by code injection
	 * @author Max Milbers
	 *
	 * @param $fieldnameXref datafield for the xreftable, where the name is stored
	 * @param $tableXref xref table
	 * @param $fieldIdXref datafield for the xreftable, where the id is stored
	 * @param $idXref The id to query in the xref table
	 * @param $fieldname the name of the datafield in the main table
	 * @param $table main table
	 * @param $fieldId the name of the field where the id is stored
	 * @param $quantity The number of items in the list
	 * @return List as String
	 */
	function renderGuiList($fieldnameXref, $tableXref, $fieldIdXref, $idXref, $fieldname, $table, $fieldId, $view, $quantity=4,$translate = 1) {
// 		'virtuemart_category_id','#__virtuemart_calc_categories','virtuemart_calc_id',$data->virtuemart_calc_id,'category_name','#__virtuemart_categories','virtuemart_category_id','category'
		//Sanitize input
		$quantity = (int) $quantity;

// 		if (!class_exists('TablePaymentmethods'))
// 			require(JPATH_VM_ADMINISTRATOR . DS . 'tables' . DS . 'paymentmethods.php');

// 		$table = new TablePaymentmethods($this->_db); /// we need that?
// 		$table->load($payment_id);

		$db = JFactory::getDBO();
		$q = 'SELECT ' . $db->getEscaped($fieldnameXref) . ' FROM ' . $db->getEscaped($tableXref) . ' WHERE ' . $db->getEscaped($fieldIdXref) . ' = "' . (int) $idXref . '"';
		$db->setQuery($q);
		$tempArray = $db->loadResultArray();
		//echo $db->_sql;
		if (isset($tempArray)) {
			$links = '';
			$ttip = '';
			$i = 0;
			foreach ($tempArray as $value) {
				if($translate){
					$mainTable = $table.'_'.VMLANG ;
					$q = 'SELECT ' . $db->getEscaped($fieldname) . ' FROM ' . $db->getEscaped($mainTable) . ' JOIN '.$table.' using (`'.$fieldnameXref.'`) WHERE ' . $db->getEscaped($fieldId) . ' = "' . (int) $value . '"';
				} else {
					$q = 'SELECT ' . $db->getEscaped($fieldname) . ' FROM ' . $db->getEscaped($table) . ' WHERE ' . $db->getEscaped($fieldId) . ' = "' . (int) $value . '"';
				}
				$db->setQuery($q);
				$tmp = $db->loadResult();
				if ($i < $quantity) {
					$links .= JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=' . $view . '&task=edit&cid[]=' . $value), $tmp) . ', ';
				}
				//$ttip .= JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view='.$view.'&task=edit&cid[]='.$value), $tmp). ', ';
				$ttip .= $tmp . ', ';

				//				$list .= $tmp. ', ';
				$i++;
				//if($i==$quantity) break;
			}
			$links = substr($links, 0, -2);
			$ttip = substr($ttip, 0, -2);

			$list = '<span class="hasTip" title="' . $ttip . '" >' . $links . '</span>';

			return $list;
		} else {
			return '';
		}
	}

	/**
	 * Creates a Drop Down list of available Creditcards
	 *
	 * @author Max Milbers
	 * @deprecated
	 */
	public function renderCreditCardList($ccId, $multiple = false) {

		$model = self::getModel('creditcard');
		$creditcards = $model->getCreditCards();

		$attrs = '';
		$name = 'creditcard_name';
		$idA = $id = 'virtuemart_creditcard_id';

		if ($multiple) {
			$attrs = 'multiple="multiple"';
			$idA .= '[]';
		} else {
			$emptyOption = JHTML::_('select.option', '', JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'), $id, $name);
			array_unshift($creditcards, $emptyOption);
		}
		$listHTML = JHTML::_('select.genericlist', $creditcards, $idA, $attrs, $id, $name, $ccId);
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

		if(Vmconfig::get('multix','none')=='none'){

			$vendorId = 1;

			$q = 'SELECT `vendor_name` FROM #__virtuemart_vendors WHERE `virtuemart_vendor_id` = "' . (int) $vendorId . '" ';
			$db->setQuery($q);
			$vendor = $db->loadResult();
			$html = '<input type="text" size="14" name="vendor_name" class="inputbox" value="' . $vendor . '" readonly="">';
		} else {
			if (!class_exists('Permissions'))
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'permissions.php');
			if (!Permissions::getInstance()->check('admin')) {
				if (empty($vendorId)) {
					$vendorId = 1;
					//Dont delete this message, we need it later for multivendor
					//JError::raiseWarning(1,'renderVendorList $vendorId is empty, please correct your used model to automatically set the virtuemart_vendor_id to the logged Vendor');
				}
				$q = 'SELECT `vendor_name` FROM #__virtuemart_vendors WHERE `virtuemart_vendor_id` = "' . (int) $vendorId . '" ';
				$db->setQuery($q);
				$vendor = $db->loadResult();
				$html = '<input type="text" size="14" name="vendor_name" class="inputbox" value="' . $vendor . '" readonly="">';
				//			$html .='<input type="hidden" value="'.$vendorId.'" name="virtuemart_vendor_id">';
				return $html;
			} else {

				$q = 'SELECT `virtuemart_vendor_id`,`vendor_name` FROM #__virtuemart_vendors';
				$db->setQuery($q);
				$vendors = $db->loadAssocList();

				$attrs = '';
				$name = 'vendor_name';
				$idA = $id = 'virtuemart_vendor_id';


				if ($multiple) {
					$attrs = 'multiple="multiple"';
					$idA .= '[]';
				} else {
					$emptyOption = JHTML::_('select.option', '', JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'), $id, $name);
					array_unshift($vendors, $emptyOption);
				}
				$listHTML = JHTML::_('select.genericlist', $vendors, $idA, $attrs, $id, $name, $vendorId);
				return $listHTML;
			}
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
		$shoppergrps = $shopperModel->getShopperGroups(false, true);
		$attrs = '';
		$name = 'shopper_group_name';
		$idA = $id = 'virtuemart_shoppergroup_id';

		if ($multiple) {
			$attrs = 'multiple="multiple"';
			$idA .= '[]';
		} else {
			$emptyOption = JHTML::_('select.option', '', JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'), $id, $name);
			array_unshift($shoppergrps, $emptyOption);
		}

		$listHTML = JHTML::_('select.genericlist', $shoppergrps, $idA, $attrs, $id, $name, $shopperGroupId);
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
	public function renderCountryList($countryId = 0, $multiple = false, $_attrib = array(), $_prefix = '') {
		$countryModel = self::getModel('country');
		$countries = $countryModel->getCountries(true, true, false);
		$attrs = array();
		$name = 'country_name';
		$id = 'virtuemart_country_id';
		$idA = $_prefix . 'virtuemart_country_id';
		$attrs['class'] = 'virtuemart_country_id';

		if ($multiple) {
			$attrs['multiple'] = 'multiple';
			$attrs['size'] = '12';
			$idA .= '[]';
		} else {
			$emptyOption = JHTML::_('select.option', '', JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'), $id, $name);
			array_unshift($countries, $emptyOption);
		}

		if (is_array($_attrib)) {
			$attrs = array_merge($attrs, $_attrib);
		} else {
			$_a = explode('=', $_attrib, 2);
			$attrs[$_a[0]] = $_a[1];
		}

		return JHTML::_('select.genericlist', $countries, $idA, $attrs, $id, $name, $countryId);
	}

	/**
	 * Render a simple state list
	 * @author jseros, Patrick Kohl
	 *
	 * @param int $stateID Selected state id
	 * @param int $countryID Selected country id
	 * @param string $dependentField Parent <select /> ID attribute
	 * @param string $_prefix Optional prefix for the formtag name attribute
	 * @return string HTML containing the <select />
	 */
	public function renderStateList($stateId = '0', $_prefix = '', $multiple = false) {

		if (is_array($stateId))
		$stateId = implode(",", $stateId);
		vmJsApi::JcountryStateList($stateId);
		$attrs = array();
		if ($multiple) {
			$attrs = 'multiple="multiple" size="12" name="' . $_prefix . 'virtuemart_state_id[]" ';
		} else {
			$attrs = 'size="1"  name="' . $_prefix . 'virtuemart_state_id" ';
		}

		$listHTML = '<select class="inputbox multiple" id="virtuemart_state_id" ' . $attrs . '>
						<OPTION value="">' . JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION') . '</OPTION>
						</select>';

		return $listHTML;
	}

	/**
	 * Renders the list for the tax rules
	 *
	 * @author Max Milbers
	 */
	function renderTaxList($selected, $name='product_tax_id', $class='multiple="multiple"') {

		if (!class_exists('VirtueMartModelCalc'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'calc.php');
		$taxes = VirtueMartModelCalc::getTaxes();

		$taxrates = array();
		$taxrates[] = JHTML::_('select.option', '-1', JText::_('COM_VIRTUEMART_PRODUCT_TAX_NONE'), $name);
		$taxrates[] = JHTML::_('select.option', '0', JText::_('COM_VIRTUEMART_PRODUCT_TAX_NO_SPECIAL'), $name);
		foreach ($taxes as $tax) {
			$taxrates[] = JHTML::_('select.option', $tax->virtuemart_calc_id, $tax->calc_name, $name);
		}
		$listHTML = JHTML::_('Select.genericlist', $taxrates, $name, $class, $name, 'text', $selected);
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
	public function renderTemplateList($defaultText = 0, $defaultOption=true) {

		if (empty($defaultText))
		$defaultText = JText::_('COM_VIRTUEMART_TEMPLATE_DEFAULT');

		$templateList = array();

		$defaulttemplate = array();
		if ($defaultOption) {
			$defaulttemplate[0] = new stdClass;
			$defaulttemplate[0]->name = $defaultText;
			$defaulttemplate[0]->directory = 0;
			$defaulttemplate[0]->value = 'default';
		}

		if (JVM_VERSION===1) {
			if (!class_exists('TemplatesHelper'))
			require (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_templates' . DS . 'helpers' . DS . 'template.php');
			$jtemplates = TemplatesHelper::parseXMLTemplateFiles(JPATH_SITE . DS . 'templates');
		} else {
			require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_templates' . DS . 'helpers' . DS . 'templates.php');
			require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_templates' . DS . 'models' . DS . 'templates.php');
			$templatesModel = new TemplatesModelTemplates();
			$jtemplates = $templatesModel->getItems();
		}

		foreach ($jtemplates as $key => $template) {
			$template->value = $template->name;
			if (JVM_VERSION===2) {
				if ($template->client_id == '0') {
					$template->directory = $template->element;
				} else {
					unset($jtemplates[$key]);
				}
			}
		}

		return array_merge($defaulttemplate, $jtemplates);
	}
	/**
	 * Returns all the weight unit
	 *
	 * @author Valérie Isaksen
	 */
	function getWeightUnit() {
		return array(
                'KG' => JText::_('COM_VIRTUEMART_WEIGHT_UNIT_NAME_KG')
		, 'GR' => JText::_('COM_VIRTUEMART_WEIGHT_UNIT_NAME_GR')
		, 'MG' => JText::_('COM_VIRTUEMART_WEIGHT_UNIT_NAME_MG')
		, 'LB' => JText::_('COM_VIRTUEMART_WEIGHT_UNIT_NAME_LB')
		, 'OZ' => JText::_('COM_VIRTUEMART_WEIGHT_UNIT_NAME_ONCE')
		);
	}
	/**
	 * Renders the string for the
	 *
	 * @author Valérie Isaksen
	 */
	function renderWeightUnit ($name ) {

		$weigth_unit = self::getWeightUnit();
		if (isset($weigth_unit[$name]))
		return $weigth_unit[$name];
		else
		return '';
	}

	/**
	 * Renders the list for the Weight Unit
	 *
	 * @author Valérie Isaksen
	 */
	function renderWeightUnitList($name, $selected) {

		$weigth_unit_default = self::getWeightUnit();
		foreach ($weigth_unit_default as  $key => $value) {
			$wu_list[] = JHTML::_('select.option', $key, $value, $name);
		}
		$listHTML = JHTML::_('Select.genericlist', $wu_list, $name, '', $name, 'text', $selected);
		return $listHTML;
		/*
		if (!class_exists('VmHTML')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
		return VmHTML::selectList($name, $selected, $weigth_unit_default);
		 *
		 */
	}

	/**
	 * Convert Weigth Unit
	 *
	 * @author Valérie Isaksen
	 */
	function convertWeigthUnit($value, $from, $to) {

		$value = str_replace(',', '.', $value);
		$g = 1 ;

		switch ($from) {
			case 'KG': $g = 1000 * $value;
			break;
			case 'GR': $g = $value;
			break;
			case 'MG': $g = $value/1000;
			break;
			case 'LB': $g = 453.59237 * $value;
			break;
			case 'OZ': $g = 28.3495 * $value;
			break;
		}
		switch ($to) {
			case 'KG' :
				$value = round($g / 1000, 2);
				break;
			case 'GR' :
				$value = round($g, 2);
				break;
			case 'MG' :
				$value = round(1000 * $g, 2);
				break;
			case 'LB' :
				$value = round($g / 453.59237, 2);
				break;
			case 'OZ' :
				$value = round($g / 28.3495, 2);
				break;
		}
		return $value;
	}

	/**
	 * Convert Metric Unit
	 *
	 * @author Florian Voutzinos
	 */
	function convertDimensionUnit($value, $from, $to) {

		$value = (float)str_replace(',', '.', $value);
		$meter = 1 ;

		// transform $value in meters
		switch ($from) {
			case 'CM': $meter = 0.01*$value;
			break;
			case 'MM': $meter = 0.001*$value;
			break;
			case 'YD': $meter = 1.0936*$value;
			break;
			case 'FT': $meter = 3.28083*$value;
			break;
			case 'IN': $meter = 39.37*$value;
			break;
		}
		switch ($to) {
			case 'CM' :
				$value = round($meter*0.01, 2);
				break;
			case 'MM' :
				$value = round($meter*0.001, 2);
				break;
			case 'YD' :
				$value = round($meter*0.9144 , 2);
				break;
			case 'FT' :
				$value = round($meter*0.3048, 2);
				break;
			case 'IN' :
				$value = round($meter*0.0254, 2);
				break;
		}
		return $value;
	}

	/**
	 * Renders the list for the Lenght, Width, Height Unit
	 *
	 * @author Valérie Isaksen
	 */
	function renderLWHUnitList($name, $selected) {

		if (!class_exists('VmHTML')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');

		$lwh_unit_default = array(   'M' =>  JText::_('COM_VIRTUEMART_LWH_UNIT_NAME_M')
		,'CM' =>  JText::_('COM_VIRTUEMART_LWH_UNIT_NAME_CM')
		,'MM' =>  JText::_('COM_VIRTUEMART_LWH_UNIT_NAME_MM')
		,'YD' =>  JText::_('COM_VIRTUEMART_LWH_UNIT_NAME_YARD')
		,'FT' =>  JText::_('COM_VIRTUEMART_LWH_UNIT_NAME_FOOT')
		,   'IN' =>  JText::_('COM_VIRTUEMART_LWH_UNIT_NAME_INCH')
		);
		return VmHTML::selectList($name,$selected, $lwh_unit_default);

	}




	/**
	 * Writes a line  for the price configuration
	 *
	 * @author Max Milberes
	 * @param string $name
	 * @param string $langkey
	 */
	function writePriceConfigLine($obj,$name,$langkey){

		if (!class_exists('VmHTML')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
		$html =
			'<tr>
				<td class="key">
					<span class="editlinktip hasTip" title="'. JText::_($langkey.'_EXPLAIN').'">
						<label>'.JText::_($langkey).
						'</label>
					</span>
				</td>

				<td>'.
		VmHTML::checkbox($name, $obj->get($name)).'
				</td>
				<td align="center">'.
		VmHTML::checkbox($name.'Text', $obj->get($name.'Text',1)).'
				</td>
				<td align="center">
				<input type="text" value="'.$obj->get($name.'Rounding',2).'" class="inputbox" size="4" name="'.$name.'Rounding">
				</td>
			</tr>';
		return $html;
	}

	/**
	 * This generates the list when the user have different ST addresses saved
	 * @author Oscar van Eijk
	 */
	function generateStAddressList ($userModel,$task){

		// Shipment address(es)
		$_addressList = $userModel->getUserAddressList($userModel->getId() , 'ST');
		if (count($_addressList) == 1 && empty($_addressList[0]->address_type_name )) {
			return JText::_('COM_VIRTUEMART_USER_NOSHIPPINGADDR');
		} else {
			$_shipTo = array();
			for ($_i = 0; $_i < count($_addressList); $_i++) {
				if(empty($_addressList[$_i]->virtuemart_user_id)) $_addressList[$_i]->virtuemart_user_id = JFactory::getUser()->id;
				if(empty($_addressList[$_i]->virtuemart_userinfo_id)) $_addressList[$_i]->virtuemart_userinfo_id = 0;
				if(empty($_addressList[$_i]->address_type_name)) $_addressList[$_i]->address_type_name = 0;

				$_shipTo[] = '<li>'.'<a href="index.php'
				.'?option=com_virtuemart'
				.'&view=user'
				.'&task='.$task
				.'&addrtype=ST'
				.'&cid[]='.$_addressList[$_i]->virtuemart_user_id
				.'&virtuemart_userinfo_id='.$_addressList[$_i]->virtuemart_userinfo_id
				. '">'.$_addressList[$_i]->address_type_name.'</a>'.'</li>';

			}
			$useXHTTML = empty($this->useXHTML) ? true:$this->useXHTML;
			$useSSL = empty($this->useSSL) ? false:$this->useSSL;

$addLink = '<a href="'.JRoute::_('index.php?option=com_virtuemart&view=user&task='.$task.'&new=1&addrtype=ST&cid[]='.$userModel->getId(),$useXHTTML,$useSSL) .'"><span class="vmicon vmicon-16-editadd"></span> ';
		$addLink .= JText::_('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL').' </a>';

			return $addLink.'<ul>' . join('', $_shipTo) . '</ul>';
		}
	}

	public static $counter = 0;
	public static $categoryTree = 0;

	public function categoryListTree($selectedCategories = array(), $cid = 0, $level = 0, $disabledFields=array()) {

		if(empty(self::$categoryTree)){
// 			vmTime('Start with categoryListTree');
			$cache = JFactory::getCache('_virtuemart');
			$cache->setCaching( 1 );
			self::$categoryTree = $cache->call( array( 'ShopFunctions', 'categoryListTreeLoop' ),$selectedCategories, $cid, $level, $disabledFields );
			// self::$categoryTree = self::categoryListTreeLoop($selectedCategories, $cid, $level, $disabledFields);
// 			vmTime('end loop categoryListTree '.self::$counter);
		}

		return self::$categoryTree;
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
	public function categoryListTreeLoop($selectedCategories = array(), $cid = 0, $level = 0, $disabledFields=array()) {

		self::$counter++;

		static $categoryTree = '';

		$virtuemart_vendor_id = 1;

// 		vmSetStartTime('getCategories');
		$categoryModel = self::getModel('category');
		$level++;

		$categoryModel->_noLimit = true;
		$app = JFactory::getApplication();
		$records = $categoryModel->getCategories($app->isSite(), $cid);
// 		vmTime('getCategories','getCategories');
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
						$categoryTree .= '<option '. $selected .' '. $disabled .' value="'. $childId .'">';
						$categoryTree .= str_repeat(' - ', ($level-1) );

						$categoryTree .= $category->category_name .'</option>';
					}
				}

				if($categoryModel->hasChildren($childId)){
					self::categoryListTreeLoop($selectedCategories, $childId, $level, $disabledFields);
				}

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
	public function getCountryByID ($id, $fld = 'country_name'){

		if (empty($id)) return '';

		$id = (int) $id;
		$db = JFactory::getDBO();

		$q = 'SELECT ' . $db->getEscaped($fld) . ' AS fld FROM `#__virtuemart_countries` WHERE virtuemart_country_id = ' . (int)$id;
		$db->setQuery($q);
		return $db->loadResult();
	}

	/**
	 * Return the countryID of a given country name
	 *
	 * @author Oscar van Eijk
	 * @author Max Milbers
	 * @access public
	 * @param string $_name Country name
	 * @return int Country ID
	 */
	public function getCountryIDByName ($name)
	{
		if (empty($name)) {
			return 0;
		}
		$db = JFactory::getDBO();

		if(strlen($name)===2){
			$fieldname = 'country_2_code';
		} else if(strlen($name)===3){
			$fieldname = 'country_3_code';
		} else {
			$fieldname = 'country_name';
		}
		$q = 'SELECT `virtuemart_country_id` FROM `#__virtuemart_countries` WHERE `'.$fieldname.'` = "'.$db->getEscaped($name).'"';
		$db->setQuery($q);
		$r = $db->loadResult();
		return $r;
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
	public function getStateByID ($id, $fld = 'state_name'){
		if (empty($id)) return '';
		$db = JFactory::getDBO();
		$q = 'SELECT ' . $db->getEscaped($fld) . ' AS fld FROM `#__virtuemart_states` WHERE virtuemart_state_id = "'.(int)$id.'"';
		$db->setQuery($q);
		$r = $db->loadObject();
		return $r->fld;
	}

	/**
	 * Return the stateID of a given state name
	 *
	 * @author Max Milbers
	 * @access public
	 * @param string $_name Country name
	 * @return int Country ID
	 */
	public function getStateIDByName ($name)
	{
		if (empty($name)) {
			return 0;
		}
		$db = JFactory::getDBO();
		if(strlen($name)===2){
			$fieldname = 'state_2_code';
		} else if(strlen($name)===3){
			$fieldname = 'state_3_code';
		} else {
			$fieldname = 'state_name';
		}
		$q = 'SELECT `virtuemart_state_id` FROM `#__virtuemart_states` WHERE `'.$fieldname.'` = "'.$db->getEscaped($name).'"';
		$db->setQuery($q);
		$r = $db->loadResult();
		return $r;
	}
	/*
	 * Return the Tax or code of a given taxID
	*
	* @author Valérie Isaksen
	* @access public
	* @param int $_d TAx ID
	* @return string Country name or code
	*/
	public function getTaxByID ($id){
		if (empty($id)) return '';

		$id = (int) $id;
		$db = JFactory::getDBO();
		$q = 'SELECT  *   FROM `#__virtuemart_calcs` WHERE virtuemart_calc_id = ' . (int)$id;
		$db->setQuery($q);
		return $db->loadAssoc();

	}
/**
	 * Return the currencyname or code of a given currencyID
	 *
	 * @author Valérie Isaksen
	 * @access public
	 * @param int $_id Currency ID
	 * @param char $_fld Field to return: currency_name (default), currency_2_code or currency_3_code.
	 * @return string Currency name or code
	 */
	public function getCurrencyByID ($id, $fld = 'currency_name'){

		if (empty($id)) return '';

		$id = (int) $id;
		$db = JFactory::getDBO();

		$q = 'SELECT ' . $db->getEscaped($fld) . ' AS fld FROM `#__virtuemart_currencies` WHERE virtuemart_currency_id = ' . (int)$id;
		$db->setQuery($q);
		return $db->loadResult();
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

		$categories = $categoryModel->getCategories($onlyPublished, $parentId);

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
	public function getModel($name = null){

// 		if(!class_exists('VmView'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmview.php');
// 		return VmView::getModel($name);
		if (!$name) $name = JRequest::getCmd('view');
		$name = strtolower($name);
		$className = ucfirst($name);

// 		retrieving model
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
// 		instancing the object
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
		$db = JFactory::getDBO();

		$_q = 'SELECT `order_status_name` FROM `#__virtuemart_orderstates` WHERE `order_status_code` = "'.$db->getEscaped($_code) . '"';
		$db->setQuery($_q);
		$_r = $db->loadObject();
		if(empty($_r->order_status_name)){
			vmError('getOrderStatusName: couldnt find order_status_name for '.$_code);
			return 'current order status broken';
		} else {
			return $_r->order_status_name;
		}

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
		$vmConfig = VmConfig::loadConfig();
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
	function listMonths($list_name, $selected=false, $class='') {
		$options = array();
		if (!$selected) $selected = date('m');

		$options[] = JHTML::_('select.option', 0, JText::_('MONTH'));
		$options[] = JHTML::_('select.option', "01", JText::_('JANUARY'));
		$options[] = JHTML::_('select.option', "02", JText::_('FEBRUARY'));
		$options[] = JHTML::_('select.option', "03", JText::_('MARCH'));
		$options[] = JHTML::_('select.option', "04", JText::_('APRIL'));
		$options[] = JHTML::_('select.option', "05", JText::_('MAY'));
		$options[] = JHTML::_('select.option', "06", JText::_('JUNE'));
		$options[] = JHTML::_('select.option', "07", JText::_('JULY'));
		$options[] = JHTML::_('select.option', "08", JText::_('AUGUST'));
		$options[] = JHTML::_('select.option', "09", JText::_('SEPTEMBER'));
		$options[] = JHTML::_('select.option', "10", JText::_('OCTOBER'));
		$options[] = JHTML::_('select.option', "11", JText::_('NOVEMBER'));
		$options[] = JHTML::_('select.option', "12", JText::_('DECEMBER'));
		return JHTML::_('select.genericlist', $options, $list_name, '', 'value', 'text', $selected);

	}

	/**
	 * Creates an drop-down list with years of the selected range or of the next 7 years
	 *
	 * @param string $list_name The name of the select element
	 * @param string $selected_item The pre-selected value
	 */
	function listYears($list_name, $selected=false, $start=null, $end=null, $attr='') {
		$options = array();
		if (!$selected) $selected = date('Y');
		$start = $start ? $start : date('Y');
		$end = $end ? $end : $start + 7;
		$options[] = JHTML::_('select.option', 0, JText::_('YEAR'));
		for ($i=$start; $i<=$end; $i++) {
			$options[] = JHTML::_('select.option', $i, $i);
		}
		return JHTML::_('select.genericlist', $options, $list_name, $attr, 'value', 'text', $selected);
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
	 *
	 * @author RolandD
	 * @param string $euvat EU-vat number to validate
	 * @return boolean The result of the validation
	 */
	// public function validateEUVat($euvat) {
		// if(!class_exists('VmEUVatCheck')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'euvatcheck.php');
		// $vatcheck = new VmEUVatCheck($euvat);
		// return $vatcheck->validvatid;

	/*
	 *
	 *$return = validateEUVat(array(‘vatnumber’ => ‘BE0123456789′, ‘country’ => ‘BE’));
	 */

	function validateEUVat($args = array()) {
		if ( '' != $args['vatnumber'] ) {
			$vat_number 	= str_replace(array(' ', '.', '-', ',', ', '), '', $args['vatnumber']);
			$countryCode 	= substr($vat_number, 0, 2);
			$vatNumber 		= substr($vat_number, 2);

			if ( strlen($countryCode) != 2 || is_numeric(substr($countryCode, 0, 1)) || is_numeric(substr($countryCode, 1, 2)) ) {
				return false;//format error 'message' => 'Your VAT Number syntax is not correct. You should have something like this: BE805670816B01'
			}

			if ( $args['country'] != $countryCode ) {
				return false;//'message' => 'Your VAT Number is not valid for the selected country.'
			}

			$client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/services/checkVatService.wsdl");
			$params = array('countryCode' => $countryCode, 'vatNumber' => $vatNumber);

			$result = $client->checkVat($params);

			if ( !$result->valid ) {
				return false ;// 'message' => sprintf('Invalid VAT Number. Check the validity on the customer VAT Number via <a href="%s">Europa VAT Number validation webservice</a>', 'http://ec.europa.eu/taxation_customs/vies/lang.do?fromWhichPage=vieshome'));
			} else {
				return true;
			}
		}
		return false;
	}

	/**
	 * Validates an email address by using regular expressions
	 * Does not resolve the domain name!
	 * ATM NOT USED
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
	function orderUpIcon($i, $condition = true, $task = 'orderup', $alt = 'COM_VIRTUEMART_MOVE_UP', $enabled = true)
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

	function getValidProductFilterArray( ) {
		static $filterArray;

		if (!isset( $filterArray )) {
/*
		$filterArray = array('p.virtuemart_product_id', 'p.product_sku','pp.product_price','c.category_name','c.category_description',
		'm.mf_name', 'l.product_s_desc', 'p.product_desc', 'p.product_weight', 'p.product_weight_uom', 'p.product_length', 'p.product_width',
		'p.product_height', 'p.product_lwh_uom', 'p.product_in_stock', 'p.low_stock_notification', 'p.product_available_date',
		'p.product_availability', 'p.product_special', 'p.created_on', 'p.modified_on', 'l.product_name', 'p.product_sales',
		'p.product_unit', 'p.product_packaging', 'p.intnotes', 'l.metadesc', 'l.metakey', 'p.metarobot', 'p.metaauthor');
		}
*/
		$filterArray = array('product_name', 'created_on', 'product_sku',
			'product_s_desc', 'product_desc',
			'category_name', 'category_description','mf_name',
			'product_price', 'product_special', 'product_sales', 'product_availability', 'product_available_date',
			'product_height', 'product_width', 'product_length', 'product_lwh_uom',
			'product_weight', 'product_weight_uom', 'product_in_stock', 'low_stock_notification',
			 'p.modified_on',
			'product_unit', 'product_packaging', 'p.virtuemart_product_id','ordering');
		//other possible fields
		//'p.intnotes',		this is maybe interesting, but then only for admins or special shoppergroups

		// this fields leads to trouble, because we have this fields in product, category and manufacturer,
		// they are anyway making not a lot sense for orderby or search.
		//'l.metadesc', 'l.metakey', 'l.metarobot', 'l.metaauthor'
		}


		return $filterArray ;
	}
}

//pure php no tag
