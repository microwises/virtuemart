<?php
/**
*
* Data module for user fields
*
* @package	VirtueMart
* @subpackage Userfields
* @author RolandD
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

// Load the helpers
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'paramhelper.php');


/**
 * Model class for user fields
 *
 * @package	VirtueMart
 * @subpackage Userfields
 * @author RolandD
 */
class VirtueMartModelUserfields extends JModel {

	/** @var integer Primary key */
	var $_id;
	/** @var objectlist userfield data */
	var $_data;
	/** @var object paramater parsers */
	var $_params;
	/** @var array type=>fieldname with formfields that are saved as parameters */
	var $reqParam;
	/** @var integer Total number of userfields in the database */
	var $_total;
	/** @var pagination Pagination for userfieldlist */
	var $_pagination;

	/**
	 * Constructor for the userfields model.
	 *
	 * The userfield ID is read and detmimined if it is an array of ids or just one single id.
	 */
	function __construct()
	{
		parent::__construct();

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int');

		// Set the state pagination variables
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Instantiate the Helper class
		$this->_params = new ParamHelper();

		// Get the (array of) order status ID(s)
		$idArray = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$idArray[0]);
		
		// Form fields that must be translated to parameters
		$this->reqParam = array (
			 'age_verification' => 'minimum_age'
			,'euvatid'          => 'shopper_group_id'
			,'webaddress'       => 'webaddresstype'
		);
	}
	
	/**
	* Prepare a user field for database update
	*/
	public function prepareFieldDataSave($fieldType, $fieldName, $value=null, $post) {
//		$post = JRequest::get('post');

		switch(strtolower($fieldType)) {
			case 'webaddress':
				if (isset($post[$fieldName."Text"]) && ($post[$fieldName."Text"])) {
					$oValuesArr = array();
					$oValuesArr[0] = str_replace(array('mailto:','http://','https://'),'', $value);
					$oValuesArr[1] = str_replace(array('mailto:','http://','https://'),'', $post[$fieldName."Text"]);
					$value = implode("|*|",$oValuesArr);
				}
				else {
					$value = str_replace(array('mailto:','http://','https://'),'', $value);
				}
				break;
			case 'email':
				$value = str_replace(array('mailto:','http://','https://'),'', $value);
				break;
			case 'multiselect':
			case 'multicheckbox':
			case 'select':
				if (is_array($value)) $value = implode("|*|",$value);
				break;
			case 'age_verification':
				$value = JRequest::getInt('birthday_selector_year')
							.'-'.JRequest::getInt('birthday_selector_month')
							.'-'.JRequest::getInt('birthday_selector_day');
				break;
			default:
				break;
		}
		return $value;
	}

	/**
	 * Resets the userfield id and data
	 */
	function setId($id)
	{
		$this->_id = $id;
		$this->_data = null;
	}

	/**
	 * Loads the pagination for the userfields table
	 *
	 * @return JPagination Pagination for the current list of userfields
	 */
	function getPagination()
	{
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;
	}

	/**
	 * Gets the total number of userfields
	 *
	 * @return int Total number of userfields in the database
	 */
	function _getTotal()
	{
		if (empty($this->_total)) {
			$query = $this->_getListQuery();
			$this->_total = $this->_getListCount($query);
	}
		return $this->_total;
	}

	/**
	 * Retrieve the detail record for the current $id if the data has not already been loaded.
	 */
	function getUserfield()
	{
		if (empty($this->_data)) {
			$this->_data = $this->getTable('userfields');
			$this->_data->load((int)$this->_id);
		}

		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_id = 0;
			$this->_data = null;
		}

		// Parse the parameters, if any
		$this->_params->parseParam($this->_data->params);

		return $this->_data;
	}

	/**
	 * Retrieve the value records for the current $id if available for the current type
	 * 
	 * @return array List wil values, or an empty array if none exist
	 */
	function getUserfieldValues()
	{
		$this->_data = $this->getTable('userfields_values');
		if ($this->_id > 0) {
			$query = 'SELECT * FROM `#__vm_userfield_values` WHERE `fieldid` = ' . $this->_id
				. ' ORDER BY `ordering`';
			$_userFieldValues = $this->_getList($query);
			return $_userFieldValues;
		} else {
			return array();
		}
	}
	
	/**
	 * Bind the post data to the userfields table and save it
	 *
	 * @return boolean True is the save was successful, false otherwise.
	 */
	function store()
	{
		$field      =& $this->getTable('userfields');
		$userinfo   =& $this->getTable('user_info');
		$orderinfo  =& $this->getTable('order_user_info');

		$data = JRequest::get('post');

		$isNew = ($data['fieldid'] < 1) ? true : false;
		if ($isNew) {
			$reorderRequired = false;
			$_action = 'ADD';
		} else {
			$field->load($data['fieldid']);
			$_action = 'CHANGE';

			if ($field->ordering == $data['ordering']) {
				$reorderRequired = false;
			} else {
				$reorderRequired = true;
			}
		}

		// Put the parameters, if any, in the correct format
		if (array_key_exists($data['type'], $this->reqParam)) {
			$this->_params->set($this->reqParam[$data['type']], $data[$this->reqParam[$data['type']]]);
			$data['params'] = $this->_params->paramString();
		}

		// Store the fieldvalues, if any, in a correct array
		$fieldValues = $this->postData2FieldValues($data['vNames'], $data['vValues'], $data['fieldid']);

		if (!$field->bind($data)) { // Bind data
			$this->setError($field->getError());
			return false;
		}

		if (!$field->check(count($fieldValues))) { // Perform data checks
			$this->setError($field->getError());
			return false; 
		}

		// Get the fieldtype for the database
		$_fieldType = $field->formatFieldType($data);

		// Alter the user_info table
		if (!$userinfo->_modifyColumn ($_action, $data['name'], $_fieldType)) {
			$this->setError($userinfo->getError());
			return false;
		}

		// Alter the order_user_info table
		if (!$orderinfo->_modifyColumn ($_action, $data['name'], $_fieldType)) {
			$this->setError($orderinfo->getError());
			return false;
		}

		// if new item, order last in appropriate group
		if ($isNew) {
			$field->ordering = $field->getNextOrder();
		}

		if (($_id = $field->store()) === false) { // Write data to the DB
			$this->setError($field->getError());
			return false;
		}

		if (!$this->storeFieldValues($fieldValues, $_id)) {
			return false;
		}
					
		if ($reorderRequired) {
			$field->reorder();
		}

		// Alter the user_info database to hold the values
		
		return true;
	}

	/**
	 * Bind and write all value records
	 * 
	 * @param array $_values
	 * @param mixed $_id If a new record is being inserted, it contains the fieldid, otherwise the value true
	 * @return boolean
	 */
	private function storeFieldValues($_values, $_id)
	{
		if (count($_values) == 0) {
			return true; //Nothing to do
		}
		$fieldvalue =& $this->getTable('userfields_values');

		for ($i = 0; $i < count($_values); $i++) {
			if (!($_id === true)) { // If $_id is true, it was not a new record
				$_values[$i]['fieldid'] = $_id;
			}

			if (!$fieldvalue->bind($_values[$i])) { // Bind data
				$this->setError($fieldvalue->getError());
				return false;
			}

			if (!$fieldvalue->check()) { // Perform data checks
				$this->setError($fieldvalue->getError());
				return false;
			}

			if (!$fieldvalue->store()) { // Write data to the DB
				$this->setError($fieldvalue->getError());
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Retrieve an array with userfield objects
	 *
	 * @param string $section The section the fields belong to (e.g. 'registration' or 'account')
	 * @param array $_switches Array to toggle these options:
	 *                         * published    Published fields only (default: true)
	 *                         * required     Required fields only (default: false)
	 *                         * delimiters   Exclude delimiters (default: false)
	 *                         * captcha      Exclude Captcha type (default: false)
	 *                         * system       System fields filter (no default; true: only system fields, false: exclude system fields)
	 * @param array $_skip Array with fieldsnames to exclude. Default: array('username', 'password', 'password2', 'agreed'),
	 *                     specify array() to skip nothing.
	 * @see getUserFieldsByUser()
	 * @return array	 */
	function getUserFields ($_sec = 'registration', $_switches=array(), $_skip = array('username', 'password', 'password2', 'agreed'))
	{
		$_q = 'SELECT * FROM `#__vm_userfield` WHERE 1 = 1 ';

		if( $_sec != 'bank' && $_sec != '') {
			$_q .= 'AND `'.$_sec.'`=1 ';
		} elseif ($_sec == 'bank' ) {
			$_q .= "AND name LIKE '%bank%' ";
		}

		if (($_skipBank = array_search('bank', $_skip)) !== false ) {
			$_q .= "AND name NOT LIKE '%bank%' ";
			unset ($_skip[$_skipBank]);
		}

		if(array_key_exists('published',$_switches)){
			if ($_switches['published'] !== false ) {
				$_q .= 'AND published = 1 ';
			}
		}
		if(array_key_exists('required',$_switches)){
			if ($_switches['required'] === true ) {
				$_q .= "AND required = 1 ";
			}
		}
		if(array_key_exists('delimiters',$_switches)){
			if ($_switches['delimiters'] === true ) {
				$_q .= "AND type != 'delimiter' ";
			}
		}
		if(array_key_exists('captcha',$_switches)){
			if ($_switches['captcha'] === true ) {
				$_q .= "AND type != 'captcha' ";
			}
		}
		if(array_key_exists('sys',$_switches)){
			if ($_switches['sys'] === true ) {
				$_q .= "AND sys = 1 ";
			} else {
				$_q .= "AND sys = 0 ";
			}
		}
		
		if (count($_skip) > 0) {
			$_q .= "AND FIND_IN_SET (name, '".implode(',', $_skip)."') = 0 ";
		}
		$_q .= 'ORDER BY ordering ';
		$_fields = $this->_getList($_q);

		// We need some extra fields that are not in the userfields table. They will be hidden on the details form
		if (!in_array('address_type', $_skip)) {
			$_address_type = new stdClass();
			$_address_type->fieldid = 0;
			$_address_type->name = 'address_type';
			$_address_type->title = '';
			$_address_type->description = '' ;
			$_address_type->type = 'hidden';
			$_address_type->maxlength = 0;
			$_address_type->size = 0;
			$_address_type->required = 0;
			$_address_type->ordering = 0;
			$_address_type->cols = 0;
			$_address_type->rows = 0;
			$_address_type->value = '';
			$_address_type->default = 'BT';
			$_address_type->published = 1;
			$_address_type->registration = 1;
			$_address_type->shipping = 0;
			$_address_type->account = 1;
			$_address_type->readonly = 0;
			$_address_type->calculated = 0;
			$_address_type->sys = 0;
			$_address_type->vendor_id = 1;
			$_address_type->params = '';
			$_fields[] = $_address_type;
		}

		if (!in_array('user_is_vendor', $_skip)) {
			$_user_is_vendor = new stdClass();
			$_user_is_vendor->fieldid = 0;
			$_user_is_vendor->name = 'user_is_vendor';
			$_user_is_vendor->title = '';
			$_user_is_vendor->description = '' ;
			$_user_is_vendor->type = 'hidden';
			$_user_is_vendor->maxlength = 0;
			$_user_is_vendor->size = 0;
			$_user_is_vendor->required = 0;
			$_user_is_vendor->ordering = 0;
			$_user_is_vendor->cols = 0;
			$_user_is_vendor->rows = 0;
			$_user_is_vendor->value = '';
			$_user_is_vendor->default = 0;
			$_user_is_vendor->published = 1;
			$_user_is_vendor->registration = 1;
			$_user_is_vendor->shipping = 0;
			$_user_is_vendor->account = 1;
			$_user_is_vendor->readonly = 0;
			$_user_is_vendor->calculated = 0;
			$_user_is_vendor->sys = 0;
			$_user_is_vendor->vendor_id = 1;
			$_user_is_vendor->params = '';
			$_fields[] = $_user_is_vendor;
		}
		return $_fields;
	}

	/**
	 * Format a userfield, e.g. translate or add JavaScript
	 * Note by Max Milbers, This should be in the helper afaik
	 * @access private
	 * @param string $_f Field type
	 * @param string $_v Input value
	 * @return string Formatted value
	 */
	private function _userFieldFormat($_f, $_v)
	{
		switch ($_f) {
			case 'agreed':
			case 'title':
				if (substr($_v, 0, 1) == '_') {
					$_v = substr($_v, 1);
				}
				$_r = (JText::_($_v)?JText::_($_v):$_v);
				if( $_f == 'title') {
					break;
				}
				// TODO Handling Agreed field
				$_r->title = '<script type="text/javascript">//<![CDATA[
						document.write(\'<label for="agreed_field">'. str_replace("'","\\'",JText::_('VM_I_AGREE_TO_TOS')) .'</label><a href="javascript:void window.open(\\\''. $mosConfig_live_site .'/index2.php?option=com_virtuemart&page=shop.tos&pop=1\\\', \\\'win2\\\', \\\'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no\\\');">\');
						document.write(\' ('.JText::_('VM_STORE_FORM_TOS') .')</a>\');
					//]]></script>
					<noscript>
					<label for="agreed_field">'. JText::_('VM_I_AGREE_TO_TOS') .'</label>
					<a target="_blank" href="/index.php?option=com_virtuemart&amp;page=shop.tos" title="'. JText::_('VM_I_AGREE_TO_TOS') .'">
					 ('.JText::_('VM_STORE_FORM_TOS').')
					</a></noscript>';
				break;
		}
		return $_r;
	}

	/**
	 * Return an array with userFields in several formats.
	 * 
	 * @access public
	 * @param $_selection An array, as returned by getuserFields(), with fields that should be returned.
	 * @param $_userData Array with userdata holding the values for the fields
	 * @param $_prefix string Optional prefix for the formtag name attribute
	 * @return array List with all userfield data in the format:
	 * array(
	 *    'fields' => array(   // All fields
	 *                   <fieldname> => array(
	 *                                     'name' =>       // Name of the field
	 *                                     'value' =>      // Existing value for the current user, or the default
	 *                                     'title' =>      // Title used for label and such
	 *                                     'type' =>       // Field type as specified in the userfields table
	 *                                     'hidden' =>     // True/False
	 *                                     'required' =>   // True/False. If True, the formcode also has the class "required" for the Joomla formvalidator
	 *                                     'formcode' =>   // Full HTML tag
	 *                                  )
	 *                   [...]
	 *                )
	 *    'functions' => array() // Optional javascript functions without <script> tags.
	 *                           // Possible usage: if (count($ar('functions')>0) echo '<script ...>'.join("\n", $ar('functions')).'</script>;
	 *    'scripts'   => array(  // Array with scriptsources for use with JHTML::script();
	 *                      <name> => <path>
	 *                      [...]
	 *                   )
	 *    'links'     => array(  // Array with stylesheets for use with JHTML::stylesheet();
	 *                      <name> => <path>
	 *                      [...]
	 *                   )
	 * )
	 * @example This example illustrates the use of this function. For additional examples, see the Order view
	 * and the User view in the administrator section.
	 *   // In the controller, make sure this model is loaded.
	 *   // In view.html.php, make the following calls:
	 *   $_usrDetails = getUserDetailsFromSomeModel(); // retrieve an user_info record, eg from the usermodel or ordermodel
	 *   $_usrFieldList = $userFieldsModel->getUserFields(
	 *                    'registration'
	 *                  , array() // Default switches
	 *                  , array('delimiter_userinfo', 'username', 'email', 'password', 'password2', 'agreed', 'address_type') // Skips
	 *    );
	 *   $usrFieldValues = $userFieldsModel->getUserFieldsByUser(
	 *                      $_usrFieldList
	 *                     ,$_usrDetails
	 *   );
	 *   $this->assignRef('userfields', $userfields);
	 *   // In the template, use code below to display the data. For an extended example using
	 *   // delimiters, JavaScripts and StyleSheets, see the edit_shopper.php in the user view
	 *   <table class="admintable" width="100%">
	 *     <thead>
	 *       <tr>
	 *         <td class="key" style="text-align: center;"  colspan="2">
	 *            <?php echo JText::_('Table Header') ?>
	 *         </td>
	 *       </tr>
	 *     </thead>
	 *      <?php 
	 *        foreach ($this->shippingfields['fields'] as $_field ) {
	 *          echo '  <tr>'."\n";
	 *          echo '    <td class="key">'."\n";
	 *          echo '      '.$_field['title']."\n";
	 *          echo '    </td>'."\n";
	 *          echo '    <td>'."\n";
	 *          
	 *          echo '      '.$_field['value']."\n";    // Display only
	 *       Or:
	 *          echo '      '.$_field['formcode']."\n"; // Input form
	 *          
	 *          echo '    </td>'."\n";
	 *          echo '  </tr>'."\n";
	 *        }
	 *      ?>
	 *    </table>
	 */
	function getUserFieldsByUser($_selection, $_userData = null, $_prefix = '')
	{

		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'shopfunctions.php');
		$shopFunctions = new ShopFunctions();
		$_return = array(
				 'fields' => array()
				,'functions' => array()
				,'scripts' => array()
				,'links' => array()
		);
		
		foreach ($_selection as $_fld) {
			$_return['fields'][$_fld->name] = array(
					 'name' => $_prefix . $_fld->name
					,'value' => (($_userData == null)
						? $_fld->default
						: @$_userData->{$_fld->name})
					,'title' => self::_userFieldFormat(
							 ($_fld->name == 'agreed')?'agreed':'title'
							,$_fld->title
						)
					,'type' => $_fld->type
					,'required' => $_fld->required
					,'hidden' => false
			);

			// First, see if there are predefined fields by checking the name
			switch( $_fld->name ) {
				case 'title':
					$_return['fields'][$_fld->name]['formcode'] = $shopFunctions->listUserTitle(
						$_return['fields'][$_fld->name]['value'], '', $_prefix);
					break;
				
				case 'country_id':
					$_return['fields'][$_fld->name]['formcode'] = $shopFunctions->renderCountryList(
						$_return['fields'][$_fld->name]['value'], false
						, array('onchange' => 'changeStateList();'), $_prefix);
					break;
				
				case 'state_id':
					$_return['fields'][$_fld->name]['formcode'] = $shopFunctions->renderStateList(
						$_return['fields']['country_id']['value'], $_return['fields'][$_fld->name]['value']
						, $_prefix.'country_id', false, $_prefix);
// TODO Write a javascript function to reload the statelist. Using jQuery???
// TODO even more.... findout why it *does* work ?!? Legacy code? Where is it included?
					break;
				case 'agreed':
					$_return['fields'][$_fld->name]['formcode'] = '<input type="checkbox" id="'.$_prefix.'agreed_field" name="'.$_prefix.'agreed" value="1" '
						. ($_fld->required ? ' class="required"' : '') . ' />';
					break;
				case 'password':
				case 'password2':
					$_return['fields'][$_fld->name]['formcode'] = '<input type="password" id="' . $_prefix.$_fld->name . '_field" name="' . $_prefix.$_fld->name . '" size="30" class="inputbox" />'."\n";
					break;

				// It's not a predefined field, so handle it by it's fieldtype
				default:
					switch( $_fld->type ) {
						case 'hidden':
							$_return['fields'][$_fld->name]['formcode'] = '<input type="hidden" id="'
								. $_prefix.$_fld->name . '_field" name="' . $_prefix.$_fld->name.'" size="' . $_fld->size
								. '" value="' . $_return['fields'][$_fld->name]['value'] .'" '
								. ($_fld->required ? ' class="required"' : '')
								. ($_fld->maxlength ? ' maxlength="' . $_fld->maxlength . '"' : '')
								. ($_fld->readonly ? ' readonly="readonly"' : '') . ' /> ';
							$_return['fields'][$_fld->name]['hidden'] = true;
							break;
						case 'date':
						case 'age_verification':
							$_calendar_path = 'includes'.DS.'js'.DS.'calendar';
							if (!in_array('calendar.js', $_return['scripts'])) {
								$_return['scripts']['calendar.js'] = $_calendar_path.DS;
							}
//							FIXME The language is all lowercase while the filename is mixed case. Is this a Joomla issue? 
//							$document = JFactory::getDocument();
//							if (!in_array('calendar-' . $document->language . '.js', $_return['scripts'])) {
//								$_return['scripts']['calendar-' . $document->language . '.js'] = $_calendar_path.DS.'lang'.DS;
//							}
							if (!in_array('calendar-en-GB.js', $_return['scripts'])) {
								$_return['scripts']['calendar-en-GB.js'] = $_calendar_path.DS.'lang'.DS;
							}
							if (!in_array('calendar-mos.css', $_return['links'])) {
								$_return['links']['calendar-mos.css'] = $_calendar_path.DS;
							}
							$_return['fields'][$_fld->name]['formcode'] = '<input type="text" id="' . $_prefix.$_fld->name . '_field" name="'
								. $_prefix.$_fld->name.'" size="' . $_fld->size . '" value="'. $_return['fields'][$_fld->name]['value'] . '" '
								. ($_fld->required ? ' class="required"' : '')
								. ($_fld->maxlength ? ' maxlength="' . $_fld->maxlength . '"' : '')
								. ($_fld->readonly ? ' readonly="readonly"' : '') . ' /> '."\n"
								. '<input name="reset" type="reset" class="button" onclick="return showCalendar(\''.$_fld->name.'_field\', \'y-mm-dd\');" value="..." />';
							break;
						case 'text':
						case 'emailaddress':
						case 'webaddress':
						case 'euvatid':
							$_return['fields'][$_fld->name]['formcode'] = '<input type="text" id="'
								. $_prefix.$_fld->name . '_field" name="' . $_prefix.$_fld->name.'" size="' . $_fld->size
								. '" value="' . $_return['fields'][$_fld->name]['value'] .'" '
								. ($_fld->required ? ' class="required"' : '')
								. ($_fld->maxlength ? ' maxlength="' . $_fld->maxlength . '"' : '')
								. ($_fld->readonly ? ' readonly="readonly"' : '') . ' /> ';
							break;
						case 'textarea':
							$_return['fields'][$_fld->name]['formcode'] = '<textarea id="'
								. $_prefix.$_fld->name . '_field" name="' . $_prefix.$_fld->name . '" cols="' . $_fld->cols
								. '" rows="'.$_fld->rows . '" class="inputbox" '
								. ($_fld->readonly ? ' readonly="readonly"' : '')
								. $_return['fields'][$_fld->name]['value'] .'</textarea>';
							break;
						case 'editorta':
							jimport( 'joomla.html.editor' );
							$editor =& JFactory::getEditor();
							$_return['fields'][$_fld->name]['formcode'] = $editor->display($_prefix.$_fld->name, $_return['fields'][$_fld->name]['value'], 300, 150, $_fld->cols, $_fld->rows);
							break;
						case 'checkbox':
							$_return['fields'][$_fld->name]['formcode'] = '<input type="checkbox" name="'
								. $_prefix.$_fld->name . '" id="' . $_prefix.$_fld->name . '_field" value="1" '
								. ($_return['fields'][$_fld->name]['value'] ? 'checked="checked"' : '') .'/>';
							break;
						case 'captcha':
							// FIXME Implement the new securityimages component
//							if (file_exists($mosConfig_absolute_path.'/administrator/components/com_securityimages/client.php')) {
//								include ($mosConfig_absolute_path.'/administrator/components/com_securityimages/client.php');
//								// Note that this package name must be used on the validation site too! If both are not equal, validation will fail
//								$packageName = 'securityVMRegistrationCheck';
//								echo insertSecurityImage($packageName);
//								echo getSecurityImageText($packageName);
//							}
//							break;
						case 'multicheckbox':
						case 'select':
						case 'multiselect':
						case 'radio':
							$_qry = 'SELECT fieldtitle, fieldvalue '
								. 'FROM #__vm_userfield_values '
								. 'WHERE fieldid = ' . $_fld->fieldid . ' '
								. 'ORDER BY ordering ';
							$_values = $this->_getList($_qry); 
							// We need an extra lok here, especially for the Bank info; the values
							// must be translated.
							// Don't check on the field name though, since others might be added in the future :-(
							foreach ($_values as $_v) {
								$_v->fieldtitle = JText::_($_v->fieldtitle);
							}
							$_attribs = array();
							if ($_fld->readonly) {
								$_attribs['readonly'] = 'readonly';
							}
							if ($_fld->required) {
								$_attribs['class'] = 'required';
							}
							
							if ($_fld->type == 'radio') {
								$_selected = $_return['fields'][$_fld->name]['value'];
							} else {
								$_attribs['size'] = $_fld->size; // Use for all but radioselects
								$_selected = explode("|*|", $_return['fields'][$_fld->name]['value']);
							}

							// Nested switch...
							switch($_fld->type) {
								case 'multicheckbox':
									$_return['fields'][$_fld->name]['formcode'] = '';
									$_idx = 0;
									foreach ($_values as $_val) {
										
										$_return['fields'][$_fld->name]['formcode'] .= '<input type="checkbox" name="'
											. $_prefix.$_fld->name . '[]" id="' . $_prefix.$_fld->name . '_field' . $_idx . '" value="'. $_val->fieldvalue . '" '
											. (in_array($_val->fieldvalue, $_selected) ? 'checked="checked"' : '') .'/> ' . JText::_($_val->fieldtitle) . '<br />';
										$_idx++;
									}
									break;
								case 'select':
									$_return['fields'][$_fld->name]['formcode'] = JHTML::_('select.genericlist', $_values, $_prefix.$_fld->name, $_attribs, 'fieldvalue', 'fieldtitle', $_selected[0]);
									break;
								case 'multiselect':
									$_attribs['multiple'] = 'multiple';
									$_attribs['rows'] = $_fld->rows;
									$_attribs['cols'] = $_fld->cols;
									$_return['fields'][$_fld->name]['formcode'] = JHTML::_('select.genericlist', $_values, $_prefix.$_fld->name.'[]', $_attribs, 'fieldvalue', 'fieldtitle', $_selected);
									break;
								case 'radio':
									$_return['fields'][$_fld->name]['formcode'] =  JHTML::_('select.radiolist', $_values, $_prefix.$_fld->name, $_attribs, $_selected, 'fieldvalue', 'fieldtitle');
									break;
							}
							break;
					}
					break;
			}
		}
		return $_return;
	}

	/**
	 * Translate arrays form userfield_values to the format expected by the table class.
	 * 
	 * @param array $titles List of titles from the formdata
	 * @param array $values List of values from the formdata
	 * @param int $fieldid ID of the userfield to relate
	 * @return array Data to bind to the userfield_values table
	 */
	private function postData2FieldValues($titles, $values, $fieldid)
	{
		$_values = array();
		if (is_array($titles) && is_array($values)) {
			for ($i=0; $i < count($titles) ;$i++) {
				if (empty($titles[$i])) {
					continue; // Ignore empty fields
				}
				$_values[] = array(
					 'fieldid'    => $fieldid
					,'fieldtitle' => $titles[$i]
					,'fieldvalue' => $values[$i]
					,'ordering'   => $i
				);
			}
		}
		return $_values;
	}

	/**
	 * Get the column name of a given fieldID
	 * @param $_id integer Field ID
	 * @return string Fieldname
	 */
	function getNameByID($_id)
	{
		$_sql = 'SELECT name '
				. 'FROM `#__vm_userfield`'
				. "WHERE fieldid = $_id";

		$_v = $this->_getList($_sql);
		return ($_v[0]->name);
	}

	/**
	 * Delete all record ids selected
	 *
	 * @return boolean True is the delete was successful, false otherwise.
	 */
	function delete()
	{
		$fieldIds   = JRequest::getVar('cid',  0, '', 'array');
		$field      =& $this->getTable('userfields');
		$value      =& $this->getTable('userfields_values');
		$userinfo   =& $this->getTable('user_info');
		$orderinfo  =& $this->getTable('order_user_info');
		
		foreach($fieldIds as $fieldId) {
			$_fieldName = $this->getNameByID($fieldId);

			// Alter the user_info table
			if (!$userinfo->_modifyColumn ('DROP', $_fieldName)) {
				$this->setError($userinfo->getError());
				return false;
			}

			// Alter the order_user_info table
			if (!$orderinfo->_modifyColumn ('DROP', $_fieldName)) {
				$this->setError($orderinfo->getError());
				return false;
			}
		
			if (!$field->delete($fieldId)) {
				$this->setError($field->getError());
				return false;
			}
			if (!$value->delete($fieldId)) {
				$this->setError($field->getError());
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Retrieve a list of userfields from the database.
	 *
	 * @return object List of userfield objects
	 */
	function getUserfieldsList()
	{
		if (!$this->_data) {
			$query = $this->_getListQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_data;
	}

	/**
	 * Get the SQL Ordering statement
	 *
	 * @return string text to add to the SQL statement
	 */
	function _getOrdering()
	{
		global $mainframe, $option;

		$filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
		$filter_order     = $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'ordering', 'cmd' );

		return (' ORDER BY '.$filter_order.' '.$filter_order_Dir);
	}

	/**
	 * If a filter was set, get the SQL WHERE clase
	 *
	 * @return string text to add to the SQL statement
	 */
	function _getFilter()
	{
		$db = JFactory::getDBO();
		if (JRequest::getVar('search', false)) {
			return (' WHERE `name` LIKE ' .$db->Quote('%'.JRequest::getVar('search').'%'));
		}
		return ('');
	}

	/**
	 * Build the query to list all Userfields
	 *
	 * @return string SQL query statement
	 */
	function _getListQuery ()
	{
		$query = 'SELECT * FROM `#__vm_userfield` ';
		$query .= $this->_getFilter();
		$query .= $this->_getOrdering();
		return ($query);
	}

	/**
	 * Change the ordering of an Userfield
	 *
	 * @return boolean True on success
	 */
	function move($direction)
	{
		$table =& $this->getTable('userfields');
		if (!$table->load($this->_id)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		if (!$table->move($direction)){
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Reorder the Userfields
	 *
	 * @return boolean True on success
	 */
	function saveorder($cid = array(), $order)
	{
		$table =& $this->getTable('userfields');

		// update ordering values
		for( $i=0; $i < count($cid); $i++ )
		{
			$table->load( (int) $cid[$i] );
			if ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];
				if (!$table->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Switch a toggleable field on or off
	 * 
	 * @param $field string Database fieldname to toggle
	 * @param $id array list of primary keys to toggle
	 * @param $value boolean Value to set
	 * @return boolean Result
	 */
	function toggle($field, $id = array(), $value = 1)
	{
		if (count( $id ))
		{
			JArrayHelper::toInteger($id);
			$ids = implode( ',', $id );

			$query = 'UPDATE `#__vm_userfield`'
				. ' SET `' . $field . '` = '.(int) $value
				. ' WHERE fieldid IN ( '.$ids.' )'
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}
}

// No closing tag