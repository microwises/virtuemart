<?php
/**
*
* Data module for user fields
*
* @package	VirtueMart
* @subpackage Userfields
* @author RolandD
* @author Oscar van Eijk
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
if(!class_exists('ParamHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'paramhelper.php');

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model class for user fields
 *
 * @package	VirtueMart
 * @subpackage Userfields
 * @author RolandD
 */
class VirtueMartModelUserfields extends VmModel {

	/** @var object paramater parsers */
	var $_params;
	/** @var array type=>fieldname with formfields that are saved as parameters */
	var $reqParam;

	// *** code for htmlpurifier ***
	// var $htmlpurifier = '';

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct('virtuemart_userfield_id');
		$this->setMainTable('userfields');

		$this->setToggleName('required');
		$this->setToggleName('registration');
		$this->setToggleName('shipment');
		$this->setToggleName('account');
		// Instantiate the Helper class
		$this->_params = new ParamHelper();

		// Form fields that must be translated to parameters
		$this->reqParam = array (
			 'age_verification' => 'minimum_age'
			,'euvatid'          => 'virtuemart_shoppergroup_id'
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
			case 'euvatid':
				vmdebug ($fieldType, $fieldName, $value ,$post);
				if ($value = shopFunctionsF::validateEUVat($value)) {
					$query= 'SELECT params FROM `#__virtuemart_userfields` where `type`="euvatid" AND name="'.$fieldName.'"' ;
					$this->_db->setQuery( $query );
					$params = $this->_db->loadResult();
					$shoppergroup_id = explode("=", $params);
					if (isset($shoppergroup_id[1])) {
						$post['virtuemart_shoppergroup_id'] = (int)$shoppergroup_id[1];
						$shoppergroupData = array('virtuemart_user_id'=>$post['virtuemart_user_id'],'virtuemart_shoppergroup_id'=>$post['virtuemart_shoppergroup_id']);
						$user_shoppergroups_table = $this->getTable('vmuser_shoppergroups');
						$shoppergroupData = $user_shoppergroups_table -> bindChecknStore($shoppergroupData);
					}
				}
				break;
			case 'age_verification':
				$value = JRequest::getInt('birthday_selector_year')
							.'-'.JRequest::getInt('birthday_selector_month')
							.'-'.JRequest::getInt('birthday_selector_day');
				break;
			default:

			// //*** code for htmlpurifier ***
			// //SEE http://htmlpurifier.org/
			// // must only add all htmlpurifier in library/htmlpurifier/
			// if (!$this->htmlpurifier) {
			// require(JPATH_VM_ADMINISTRATOR.DS.'library'.DS.'htmlpurifier'.DS.'HTMLPurifier.auto.php');
			// $config = HTMLPurifier_Config::createDefault();
			// $this->htmlpurifier = new HTMLPurifier($config);
			// }
			// $value = $this->htmlpurifier->purify($value);
			// vmdebug( "purified filter" , $value);

			//$config->set('URI.HostBlacklist', array('google.com'));// set eg .add google.com in black list


 				// no HTML TAGS but permit all alphabet
				$value =	preg_replace('@<[\/\!]*?[^<>]*?>@si','',$value);//remove all html tags
				$value =	(string)preg_replace('#on[a-z](.+?)\)#si','',$value);//replace start of script onclick() onload()...
				$value = trim(str_replace('"', ' ', $value),"'") ;
				$value =	(string)preg_replace('#^\'#si','',$value);//replace ' at start
				//vmdebug( "my filter" , $value);
			break;
		}
		return $value;
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
		$this->_data = $this->getTable('userfield_values');
		if ($this->_id > 0) {
			$query = 'SELECT * FROM `#__virtuemart_userfield_values` WHERE `virtuemart_userfield_id` = ' . (int)$this->_id
				. ' ORDER BY `ordering`';
			$_userFieldValues = $this->_getList($query);
			return $_userFieldValues;
		} else {
			return array();
		}
	}

	function getCoreFields(){
		 return array( 'name','username', 'email', 'password', 'password2' , 'agreed');
	}

	/**
	 * Bind the post data to the userfields table and save it
	 *
	 * @return boolean True is the save was successful, false otherwise.
	 */
	function store()
	{
		$field      = $this->getTable('userfields');
		$userinfo   = $this->getTable('userinfos');
		$orderinfo  = $this->getTable('order_userinfos');

		//TODO move this to controller
		$data = JRequest::get('post');

		$isNew = ($data['virtuemart_userfield_id'] < 1) ? true : false;

		$coreFields = $this->getCoreFields();
		if(in_array($data['name'],$coreFields)){
			//vmError('Cant store/update core field. They belong to joomla');
			//return false;
		} else {
			if ($isNew) {
				$reorderRequired = false;
				$_action = 'ADD';
			} else {
				$field->load($data['virtuemart_userfield_id']);
				$_action = 'CHANGE';

				if ($field->ordering == $data['ordering']) {
					$reorderRequired = false;
				} else {
					$reorderRequired = true;
				}
			}
		}

		// Put the parameters, if any, in the correct format
		if (array_key_exists($data['type'], $this->reqParam)) {
			$this->_params->set($this->reqParam[$data['type']], $data[$this->reqParam[$data['type']]]);
			$data['params'] = $this->_params->paramString();
		}

		// Store the fieldvalues, if any, in a correct array
		$fieldValues = $this->postData2FieldValues($data['vNames'], $data['vValues'], $data['virtuemart_userfield_id']);

		if (!$field->bind($data)) { // Bind data
			vmError($field->getError());
			return false;
		}

		if (!$field->check(count($fieldValues))) { // Perform data checks
			vmError($field->getError());
			return false;
		}

		// Get the fieldtype for the database
		$_fieldType = $field->formatFieldType($data);


		if(!in_array($data['name'],$coreFields) && $field->type != 'delimiter'){

			// Alter the user_info table
			if (!$userinfo->_modifyColumn ($_action, $data['name'], $_fieldType)) {
				vmError($userinfo->getError());
				return false;
			}

			// Alter the order_userinfo table
			if (!$orderinfo->_modifyColumn ($_action, $data['name'], $_fieldType)) {
				vmError($orderinfo->getError());
				return false;
			}
		}

		// if new item, order last in appropriate group
		if ($isNew) {
			$field->ordering = $field->getNextOrder();
		}

		$_id = $field->store();
		if ($_id === false) { // Write data to the DB
			vmError($field->getError());
			return false;
		}

		if (!$this->storeFieldValues($fieldValues, $_id)) {
			return false;
		}

		if ($reorderRequired) {
			$field->reorder();
		}

		vmdebug('storing userfield',$_id);
		// Alter the user_info database to hold the values

		return $_id;
	}

	/**
	 * Bind and write all value records
	 *
	 * @param array $_values
	 * @param mixed $_id If a new record is being inserted, it contains the virtuemart_userfield_id, otherwise the value true
	 * @return boolean
	 */
	private function storeFieldValues($_values, $_id)
	{
		if (count($_values) == 0) {
			return true; //Nothing to do
		}
		$fieldvalue = $this->getTable('userfield_values');

		for ($i = 0; $i < count($_values); $i++) {
			if (!($_id === true)) { // If $_id is true, it was not a new record
				$_values[$i]['virtuemart_userfield_id'] = $_id;
			}

			if (!$fieldvalue->bind($_values[$i])) { // Bind data
				vmError($fieldvalue->getError());
				return false;
			}

			if (!$fieldvalue->check()) { // Perform data checks
				vmError($fieldvalue->getError());
				return false;
			}

			if (!$fieldvalue->store()) { // Write data to the DB
				vmError($fieldvalue->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 *
	 * @author Max Milbers
	 */
	public function getUserFieldsFor($layoutName, $type,$userId = -1){

		$register = false;

		if(VmConfig::get('oncheckout_show_register',1)){
			$user = JFactory::getUser();
			if(!empty($user)){
				if(empty($user->id)){
					$register = true;
				}
			} else {
				$register = true;
			}
		} else {
			$register = false;
		}



		//Here we define the fields to skip
// 		if($layoutName=='edit' || ){
// 			$skips = array('delimiter_userinfo', 'delimiter_billto', 'username', 'password', 'password2'
// 						, 'address_type', 'bank', 'email','user_is_vendor');
// 		} else
		if ($layoutName=='cart' && $register){
			$skips = array('delimiter_userinfo', 'delimiter_billto', 'address_type', 'bank','user_is_vendor');

		} else if ($layoutName=='cart' && !$register){
			$skips = array('delimiter_userinfo', 'delimiter_billto', 'username', 'name', 'password', 'password2', 'address_type', 'bank','user_is_vendor');
		} else if ($layoutName=='edit' && $type='BT'){
			$skips = array('delimiter_userinfo', 'delimiter_billto', 'address_type', 'bank','user_is_vendor');
		} else {
			$skips = array('delimiter_userinfo', 'delimiter_billto', 'name','username', 'password', 'password2'
						, 'address_type', 'bank','email','user_is_vendor','agreed');
		}

		//Here we get the fields
		if ($type == 'BT') {
			$userFields = $this->getUserFields(
					 'account'
					,	array() // Default toggles
					,	$skips// Skips
			);
		} else {
			$userFields = $this->getUserFields(
				 'shipment'
				, array() // Default toggles
				, $skips
			);
		}


		//Small ugly hack to make registering optional //do we still need that? YES !  notice by Max Milbers
		if($register && $type == 'BT'  && VmConfig::get('oncheckout_show_register',1) ){

			$corefields = $this->getCoreFields();
			unset($corefields[2]); //the 2 is for the email field, it is necessary in almost anycase.
			foreach($userFields as $field){
				if(in_array($field->name,$corefields)){
					$field->required = 0;
					$field->value = '';
					$field->default = '';

				}
			}
		}

		return $userFields;
	}
	/**
	 * Retrieve an array with userfield objects
	 *
	 * @param string $section The section the fields belong to (e.g. 'registration' or 'account')
	 * @param array $_switches Array to toggle these options:
	 *                         * published    published fields only (default: true)
	 *                         * required     Required fields only (default: false)
	 *                         * delimiters   Exclude delimiters (default: false)
	 *                         * captcha      Exclude Captcha type (default: false)
	 *                         * system       System fields filter (no default; true: only system fields, false: exclude system fields)
	 * @param array $_skip Array with fieldsnames to exclude. Default: array('username', 'password', 'password2', 'agreed'),
	 *                     specify array() to skip nothing.
	 * @see getUserFieldsFilled()
	 * @author Oscar van Eijk
	 * @return array
	 */
	public function getUserFields ($_sec = 'registration', $_switches=array(), $_skip = array('username', 'password', 'password2'))
	{
		$_q = 'SELECT * FROM `#__virtuemart_userfields` WHERE 1 = 1 ';

		if( $_sec != 'bank' && $_sec != '') {
			$_q .= 'AND `'.$_sec.'`=1 ';
		} elseif ($_sec == 'bank' ) {
			$_q .= "AND name LIKE '%bank%' ";
		}

/*		if (($_skipBank = array_search('bank', $_skip)) !== false ) {
			$_q .= "AND name NOT LIKE '%bank%' ";
			unset ($_skip[$_skipBank]);
		}*/

		if(array_key_exists('published',$_switches)){
			if ($_switches['published'] !== false ) {
				$_q .= 'AND published = 1 ';
			}
		} else {
			$_q .= 'AND published = 1 ';
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
			$_q .= "AND FIND_IN_SET(name, '".implode(',', $_skip)."') = 0 ";
		}
		$_q .= ' ORDER BY ordering ';
		$_fields = $this->_getList($_q);

		// We need some extra fields that are not in the userfields table. They will be hidden on the details form
		if (!in_array('address_type', $_skip)) {
			$_address_type = new stdClass();
			$_address_type->virtuemart_userfield_id = 0;
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
			$_address_type->shipment = 0;
			$_address_type->account = 1;
			$_address_type->readonly = 0;
			$_address_type->calculated = 0; // what is this???
			$_address_type->sys = 0;
			$_address_type->virtuemart_vendor_id = 1;
			$_address_type->params = '';
			$_fields[] = $_address_type;
		}

// 		if (!in_array('user_is_vendor', $_skip)) {
// 			$_user_is_vendor = new stdClass();
// 			$_user_is_vendor->virtuemart_userfield_id = 0;
// 			$_user_is_vendor->name = 'user_is_vendor';
// 			$_user_is_vendor->title = '';
// 			$_user_is_vendor->description = '' ;
// 			$_user_is_vendor->type = 'hidden';
// 			$_user_is_vendor->maxlength = 0;
// 			$_user_is_vendor->size = 0;
// 			$_user_is_vendor->required = 0;
// 			$_user_is_vendor->ordering = 0;
// 			$_user_is_vendor->cols = 0;
// 			$_user_is_vendor->rows = 0;
// 			$_user_is_vendor->value = 0;
// 			$_user_is_vendor->default = '0';
// 			$_user_is_vendor->published = 1;
// 			$_user_is_vendor->registration = 1;
// 			$_user_is_vendor->shipment = 0;
// 			$_user_is_vendor->account = 1;
// 			$_user_is_vendor->readonly = 0;
// 			$_user_is_vendor->calculated = 0;
// 			$_user_is_vendor->sys = 0;
// 			$_user_is_vendor->virtuemart_vendor_id = 1;
// 			$_user_is_vendor->params = '';
// 			$_fields[] = $_user_is_vendor;
// 		}
		return $_fields;
	}

	/**
	 * Format a userfield, e.g. translate or add JavaScript
	 * Note by Max Milbers, This should be in the helper afaik
	 * @access private
	 * @param string $_f Field type
	 * @param string $_v Input value
	 * @author Oscar van Eijk
	 * @return string Formatted value
	 */
	private function _userFieldFormat($_f, $_v)
	{
// 		vmdebug('What is happening here?',$_f, $_v);
// 		switch ($_f) {
// 			case 'agreed':
// 			case 'title':
// 				if (substr($_v, 0, 1) == '_') {
// 					$_v = substr($_v, 1);
// 				}
				$_r = (JText::_($_v)?JText::_($_v):$_v);
// 				if( $_f == 'title') {
// 					break;
// 				}
				// TODO Handling Agreed field
/*				$_r->title = '<script type="text/javascript">//<![CDATA[
						document.write(\'<label for="agreed_field">'. str_replace("'","\\'",JText::_('COM_VIRTUEMART_I_AGREE_TO_TOS')) .'</label><a href="javascript:void window.open(\\\''. $mosConfig_live_site .'/index2.php?option=com_virtuemart&page=shop.tos&pop=1\\\', \\\'win2\\\', \\\'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no\\\');">\');
						document.write(\' ('.JText::_('COM_VIRTUEMART_STORE_FORM_TOS') .')</a>\');
					//]]></script>
					<noscript>
					<label for="agreed_field">'. JText::_('COM_VIRTUEMART_I_AGREE_TO_TOS') .'</label>
					<a target="_blank" href="/index.php?option=com_virtuemart&amp;page=shop.tos" title="'. JText::_('COM_VIRTUEMART_I_AGREE_TO_TOS') .'">
					 ('.JText::_('COM_VIRTUEMART_STORE_FORM_TOS').')
					</a></noscript>';*/
// 				break;
// 		}
// 		vmdebug('return _userFieldFormat',$_r);
		return $_r;
	}

	/**
	 * Return an array with userFields in several formats.
	 *
	 * @access public
	 * @param $_selection An array, as returned by getuserFields(), with fields that should be returned.
	 * @param $_userData Array with userdata holding the values for the fields
	 * @param $_prefix string Optional prefix for the formtag name attribute
	 * @author Oscar van Eijk
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
	 * <pre>
	 *   // In the controller, make sure this model is loaded.
	 *   // In view.html.php, make the following calls:
	 *   $_usrDetails = getUserDetailsFromSomeModel(); // retrieve an user_info record, eg from the usermodel or ordermodel
	 *   $_usrFieldList = $userFieldsModel->getUserFields(
	 *                    'registration'
	 *                  , array() // Default switches
	 *                  , array('delimiter_userinfo', 'username', 'email', 'password', 'password2', 'agreed', 'address_type') // Skips
	 *    );
	 *   $usrFieldValues = $userFieldsModel->getUserFieldsFilled(
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
	 *            <?php echo JText::_('COM_VIRTUEMART_TABLE_HEADER') ?>
	 *         </td>
	 *       </tr>
	 *     </thead>
	 *      <?php
	 *        foreach ($this->shipmentfields['fields'] as $_field ) {
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
	 * </pre>
	 */
	public function getUserFieldsFilled($_selection, $_userData = null, $_prefix = ''){

		if(!class_exists('ShopFunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');
		$_return = array(
				 'fields' => array()
				,'functions' => array()
				,'scripts' => array()
				,'links' => array()
		);

// 		vmdebug('my user data in getUserFieldsFilled',$_selection,$_userData);
 		$_userData=(array)($_userData);
		if (is_array($_selection)) {
		    foreach ($_selection as $_fld) {
			    $_return['fields'][$_fld->name] = array(
					     'name' => $_prefix . $_fld->name
					    ,'value' => (($_userData == null || !array_key_exists($_fld->name, $_userData))
						    ? $_fld->default
						    : @$_userData[$_fld->name])
					    ,'title' => JText::_($_fld->title)
					    ,'type' => $_fld->type
					    ,'required' => $_fld->required
					    ,'hidden' => false
			    );

    // 			if($_fld->name==='email') vmdebug('user data email getuserfieldbyuser',$_userData);
			    // First, see if there are predefined fields by checking the name
			    switch( $_fld->name ) {

    // 				case 'email':
    // 					$_return['fields'][$_fld->name]['formcode'] = $_userData->email;
    // 					break;
				    case 'virtuemart_country_id':
					    $_return['fields'][$_fld->name]['formcode'] =
							    ShopFunctions::renderCountryList($_return['fields'][$_fld->name]['value'], false, array(), $_prefix);

					    // Translate the value from ID to name
					    $_return['fields'][$_fld->name]['value'] = shopFunctions::getCountryByID($_return['fields'][$_fld->name]['value']);
					    break;

				    case 'virtuemart_state_id':

					    $_return['fields'][$_fld->name]['formcode'] =
					    shopFunctions::renderStateList(	$_return['fields'][$_fld->name]['value'],
													    // ShopFunctions::getCountryIDByName($_return['fields']['virtuemart_country_id']['value']),
													    $_prefix,
													    false
													    // $_prefix
													    );

					    $_return['fields'][$_fld->name]['value'] = shopFunctions::getStateByID($_return['fields'][$_fld->name]['value']);
					    break;
				    //case 'agreed':
				    //	$_return['fields'][$_fld->name]['formcode'] = '<input type="checkbox" id="'.$_prefix.'agreed_field" name="'.$_prefix.'agreed" value="1" '
				    //		. ($_fld->required ? ' class="required"' : '') . ' />';
				    //	break;
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
							    //echo JHTML::_('behavior.calendar');
							    /*
							     * TODO We must add the joomla.javascript here that contains the calendar,
							     * since Joomla does not load it when there's no user logged in.
							     * Gotta find out why... some security issue or a bug???
							     * Note by Oscar
							     */
							    // if ($_userData === null) { // Not logged in
								    // $_doc = JFactory::getDocument();
								    // $_doc->addScript( JURI::root(true).'/includes/js/joomla.javascript.js');
							    // }
								$currentYear= date('Y');
							    $calendar = vmJsApi::jDate($_return['fields'][$_fld->name]['value'],  $_prefix.$_fld->name,  $_prefix.$_fld->name . '_field',false,($currentYear-100).':'.$currentYear);
								$_return['fields'][$_fld->name]['formcode'] = $calendar ;
							    break;
						    case 'emailaddress':
							    if(empty($_return['fields'][$_fld->name]['value'])) $_return['fields'][$_fld->name]['value'] = JFactory::getUser()->email;
    // 							vmdebug('emailaddress',$_fld);
						    case 'text':
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
								    . $_return['fields'][$_fld->name]['value'] .'></textarea>';
							    break;
						    case 'editorta':
							    jimport( 'joomla.html.editor' );
							    $editor = JFactory::getEditor();
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
								    . 'FROM #__virtuemart_userfield_values '
								    . 'WHERE virtuemart_userfield_id = ' . $_fld->virtuemart_userfield_id
								    . ' ORDER BY ordering ';
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
									    $rows = $_fld->rows;
									    $row = 1;
									    foreach ($_values as $_val) {
										    if 	($row > $rows) { $row = 1;
											    $br = '<br />';
										    } else {
											    $row ++ ;
											    $br = '';
										    }
										    $_return['fields'][$_fld->name]['formcode'] .= '<input type="checkbox" name="'
											    . $_prefix.$_fld->name . '[]" id="' . $_prefix.$_fld->name . '_field' . $_idx . '" value="'. $_val->fieldvalue . '" '
											    . (in_array($_val->fieldvalue, $_selected) ? 'checked="checked"' : '') .'/> ' . JText::_($_val->fieldtitle) . $br;

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
	    }
		return $_return;
	}

	/**
	 * Checks if a single field is required, used in the cart
	 *
	 * @author Max Milbers
	 * @param string $fieldname
	 */
	function getIfRequired($fieldname) {

		$q = 'SELECT `required` FROM #__virtuemart_userfields WHERE `name` = "'.$fieldname.'" ';

		$this->_db->setQuery($q);
		$result = $this->_db->loadResult();
		$error = $this->_db->getErrorMsg();
		if(!empty($error)){
			vmError('userfields getIfRequired '.$error,'Programmer used an unknown userfield '.$fieldname);
		}

		return $result;

	}

	/**
	 * Translate arrays form userfield_values to the format expected by the table class.
	 *
	 * @param array $titles List of titles from the formdata
	 * @param array $values List of values from the formdata
	 * @param int $virtuemart_userfield_id ID of the userfield to relate
	 * @return array Data to bind to the userfield_values table
	 */
	private function postData2FieldValues($titles, $values, $virtuemart_userfield_id){

		$_values = array();
		if (is_array($titles) && is_array($values)) {
			for ($i=0; $i < count($titles) ;$i++) {
				if (empty($titles[$i])) {
					continue; // Ignore empty fields
				}
				$_values[] = array(
					 'virtuemart_userfield_id'    => $virtuemart_userfield_id
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
		$_sql = 'SELECT `name`
				FROM `#__virtuemart_userfields`
				WHERE virtuemart_userfield_id = "'.$_id.'" ';

		$_v = $this->_getList($_sql);
		return ($_v[0]->name);
	}

	/**
	 * Delete all record ids selected
	 *
	 * @return boolean True is the remove was successful, false otherwise.
	 */
	function remove($fieldIds){

		$field      = $this->getTable('userfields');
		$value      = $this->getTable('userfield_values');
		$userinfo   = $this->getTable('userinfos');
		$orderinfo  = $this->getTable('order_userinfos');

		foreach($fieldIds as $fieldId) {
			$_fieldName = $this->getNameByID($fieldId);
			$field->load($fieldId);

			if ($field->type != 'delimiter') {
				// Alter the user_info table
				if (!$userinfo->_modifyColumn ('DROP', $_fieldName)) {
					vmError($userinfo->getError());
					return false;
				}

				// Alter the order_userinfo table
				if (!$orderinfo->_modifyColumn ('DROP', $_fieldName)) {
					vmError($orderinfo->getError());
					return false;
				}
			}

			if (!$field->delete($fieldId)) {
				vmError($field->getError());
				return false;
			}
			if (!$value->delete($fieldId)) {
				vmError($field->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Retrieve a list of userfields from the database.
	 *
	 * @deprecated
	 * @return object List of userfield objects
	 */
	function getUserfieldsList()
	{
		if (!$this->_data) {
			$query = $this->_getListQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
			$this->_total = $this->_getListCount($query);
		}
		return $this->_data;
	}

	/**
	* Gets the total number of userfields
	*
	* @return int Total number of userfields in the database
	* @deprecated
	*
	function _getTotal()
	{
		if (empty($this->_total)) {
			$query = $this->_getListQuery();
			$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}

	/**
	 * If a filter was set, get the SQL WHERE clase
	 *
	 *@deprecated
	 * @return string text to add to the SQL statement
	 */
	function _getFilter()
	{
		$db = JFactory::getDBO();
		if ($search = JRequest::getWord('search', false)) {
			$search = '"%' . $this->_db->getEscaped( $search, true ) . '%"' ;
			//$search = $this->_db->Quote($search, false);
			return (' WHERE `name` LIKE ' .$search);
		}
		return ('');
	}

	/**
	 * Build the query to list all Userfields
	 *
	 *@deprecated
	 * @return string SQL query statement
	 */
	function _getListQuery ()
	{
		$query = 'SELECT * FROM `#__virtuemart_userfields` ';
		$query .= $this->_getFilter();
		$query .= $this->_getOrdering();
		return ($query);
	}
 //*/
}

// No closing tag