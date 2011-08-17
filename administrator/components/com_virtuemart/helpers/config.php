<?php
/**
 * Configuration helper class
 *
 * This class provides some functions that are used throughout the VirtueMart shop to access confgiuration values.
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author RickG
 * @author Max Milbers
 * @copyright Copyright (c) 2004-2008 Soeren Eberhardt-Biermann, 2009 VirtueMart Team. All rights reserved.
 */
defined('_JEXEC') or die('Restricted access');

/**
 *
 * We need this extra paths to have always the correct path undependent by loaded application, module or plugin
 * Plugin, module developers must always include this config at start of their application
 *   $vmConfig = VmConfig::loadConfig(); // load the config and create an instance
 *  $vmConfig -> jQuery(); // for use of jQuery
 *  Then always use the defined paths below to ensure future stability
 */
define( 'JPATH_VM_SITE', JPATH_ROOT.DS.'components'.DS.'com_virtuemart' );
define( 'JPATH_VM_ADMINISTRATOR', JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart' );

if(VmConfig::isJ15()){
	define ('JPATH_VM_LIBRARIES', JPATH_LIBRARIES);
} else {
	define ('JPATH_VM_LIBRARIES', JPATH_PLATFORM);
}

require(JPATH_VM_ADMINISTRATOR.DS.'version.php');


/**
 * This function shows an info message, the messages gets translated with JText::,
 * you can overload the function, so that automatically sprintf is taken, when needed.
 * So this works vmInfo('COM_VIRTUEMART_MEDIA_NO_PATH_TYPE',$type,$link )
 * and also vmInfo('COM_VIRTUEMART_MEDIA_NO_PATH_TYPE');
 *
 * @author Max Milbers
 * @param unknown_type $publicdescr
 * @param unknown_type $value
 */

function vmInfo($publicdescr,$value=null){

	$app = JFactory::getApplication();
	$lang = JFactory::getLanguage();
	if($value!==null){

		$args = func_get_args();
		if (count($args) > 0) {
			$args[0] = $lang->_($args[0]);
			$app ->enqueueMessage(call_user_func_array('sprintf', $args),'info');
		}
	}	else {
// 		$app ->enqueueMessage('Info: '.JText::_($publicdescr));
		$publicdescr = $lang->_($publicdescr);
		$app ->enqueueMessage('Info: '.JText::_($publicdescr),'info');
// 		debug_print_backtrace();
	}
}

function vmWarn($publicdescr,$value=null){

	$app = JFactory::getApplication();
	$lang = JFactory::getLanguage();
	if($value!==null){

		$args = func_get_args();
		if (count($args) > 0) {
			$args[0] = $lang->_($args[0]);
			$app ->enqueueMessage(call_user_func_array('sprintf', $args),'warning');
		}
	}	else {
		// 		$app ->enqueueMessage('Info: '.JText::_($publicdescr));
		$publicdescr = $lang->_($publicdescr);
		$app ->enqueueMessage('Info: '.$publicdescr,'warning');
		// 		debug_print_backtrace();
	}
}

/**
 * Shows an error message, sensible information should be only in the first one, the second one is for non BE users
 * @author Max Milbers
 */
function vmError($descr,$publicdescr=''){
	$lang = JFactory::getLanguage();
	if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
	if(Permissions::getInstance()->check('admin')){
		$app = JFactory::getApplication();
		$descr = $lang->_($descr);
		$app ->enqueueMessage($descr,'error');
	} else {
		if(!empty($publicdescr)){
			$app = JFactory::getApplication();

			$publicdescr = $lang->_($publicdescr);
			$app ->enqueueMessage($publicdescr,'error');
		}
	}

}

/**
 * A debug dumper for VM, it is only shown to backend users.
 *
 * @author Max Milbers
 * @param unknown_type $descr
 * @param unknown_type $values
 */
function vmdebug($debugdescr,$debugvalues=null){

	if(VMConfig::showDebug()  ){

		if($debugvalues!==null){
// 			$debugdescr .=' <pre>'.print_r($debugvalues,1).'<br />'.print_r(get_class_methods($debugvalues),1).'</pre>';

			$args = func_get_args();
			if (count($args) > 1) {
// 				foreach($args as $debugvalue){
				for($i=1;$i<count($args);$i++){
					if(isset($args[$i])){
						$debugdescr .=' Var'.$i.': <pre>'.print_r($args[$i],1).'<br />'.print_r(get_class_methods($args[$i]),1).'</pre>';
					}
				}

			}
		}

		$app = JFactory::getApplication();
		$app ->enqueueMessage('vmdebug '.$debugdescr);
	}

}

function vmTrace($notice,$force=false){

	if($force || (VMConfig::showDebug() ) ){
		//$app = JFactory::getApplication();
		//$app ->enqueueMessage($notice.' '.debug_print_backtrace());
		debug_print_backtrace();
	}

}

function vmRam($notice,$value=null){
	vmdebug($notice.' used Ram '.round(memory_get_usage(true)/(1024*1024),2).'M ',$value);
}

function vmRamPeak($notice,$value=null){
	vmdebug($notice.' memory peak '.round(memory_get_peak_usage(true)/(1024*1024),2).'M ',$value);
}


function vmSetStartTime($name='current'){

	VmConfig::setStartTime($name, microtime(true));
}

function vmTime($descr,$name='current'){

	$starttime = VmConfig::$_starttime ;
	if(empty($starttime[$name])){
		vmdebug('vmTime: starting at general runtime '.microtime(true));
		VmConfig::$_starttime[$name] = microtime(true);
	} else if($name=='current'){
		vmdebug('vmTime: time consumed '.microtime(true) - $starttime[$name]);
		VmConfig::$_starttime[$name] = microtime(true);
	} else {
		if(empty($descr)) $descr = $name;
		$tmp = 'vmTime: '.$descr.': '.(microtime(true) - $starttime[$name]);
		vmdebug($tmp);
	}

}

/**
 * We use this Class STATIC not dynamically !
 */
class VmConfig{

	// instance of class
	private static $_jpConfig = null;
	private static $_debug = null;
	public static $_starttime = array();
	public static $loaded = false;

	var $_params = array();
	var $_raw = array();

	private function __construct() {

	}

	function getStartTime(){
		return self::$_starttime;
	}

	function setStartTime($name,$value){
		self::$_starttime[$name] = $value;
	}

	function showDebug(){

		//return self::$_debug = true;	//this is only needed, when you want to debug THIS file
		if(self::$_debug===null){

			$debug = VmConfig::get('debug_enable','none');
// 			$app = JFactory::getApplication();
// 			$app ->enqueueMessage($debug);

			// 1 show debug only to admins
			if($debug === 'admin' ){
				if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
				if(Permissions::getInstance()->check('admin')){
					self::$_debug = true;
				} else {
					self::$_debug = false;
				}
			}
			// 2 show debug to anyone
			else if($debug === 'all' ){
				self::$_debug = true;
			}
			// else dont show debug
			else {
				self::$_debug = false;
			}

		}

		return self::$_debug;
	}

	/**
	 * Loads the configuration and works as singleton therefore called static. The call using the program cache
	 * is 10 times faster then taking from the session. The session is still approx. 30 times faster then using the file.
	 * The db is 10 times slower then the session.
	 *
	 * Performance:
	 *
	 * Fastest is
	 * Program Cache: 1.5974044799805E-5
	 * Session Cache: 0.00016094612121582
	 *
	 * First config db load: 0.00052118301391602
	 * Parsed and in session: 0.001554012298584
	 *
	 * After install from file: 0.0040450096130371
	 * Parsed and in session: 0.0051419734954834
	 *
	 *
	 * Functions tests if alread loaded in program cache, session cache, database and at last the file.
	 *
	 * Load the configuration values from the database into a session variable.
	 * This step is done to prevent accessing the database for every configuration variable lookup.
	 *
	 * @author Max Milbers
	 * @param $force boolean Forces the function to load the config from the db
	 */
	public function loadConfig($force = false) {


// 		vmSetStartTime('loadConfig');
		if(!$force && self::$loaded){
			if(!empty(self::$_jpConfig) && !empty(self::$_jpConfig->_params)){
// 				vmTime('Program Cache','loadConfig');
				return self::$_jpConfig;
			} else {
				$session = JFactory::getSession();
				$vmConfig = $session->get('vmconfig','','vm');
				if(!empty($vmConfig)){
					$test = unserialize(base64_decode($vmConfig));
					if(!empty($test) && !empty($test->_params)) {
						self::$_jpConfig = $test;
// 						$app = JFactory::getApplication();
// 						$app ->enqueueMessage('loadConfig session cache');
// 						vmTime('Session Cache','loadConfig');
						return self::$_jpConfig;
					}
				}

			}
		}

		self::$_jpConfig = new VmConfig();

		$db = JFactory::getDBO();
		$query = 'SELECT `config` FROM `#__virtuemart_configs` WHERE `virtuemart_config_id` = "1"';
		$db->setQuery($query);
		self::$_jpConfig->_raw = $db->loadResult();
// 		vmTime('First config db load','loadConfig');
		if(empty(self::$_jpConfig->_raw)){
			self::$_jpConfig->_raw = self::installVMconfig();
			$db->setQuery($query);
			self::$_jpConfig->_raw = $db->loadResult();
			$this->_params = null;
// 			vmTime('After install from file','loadConfig');
		}

		$i = 0;
		$pair = array();
		if (!empty(self::$_jpConfig->_raw)) {
			$config = explode('|', self::$_jpConfig->_raw);
			foreach($config as $item){
				$item = explode('=',$item);
				if(!empty($item[1])){
					$pair[$item[0]] = unserialize(base64_decode($item[1]) );
				} else {
					$pair[$item[0]] ='';
				}

			}

			self::$_jpConfig->_params = $pair;


			self::$_jpConfig->setSession();

// 			vmTime('Parsed and in session','loadConfig');
			return self::$_jpConfig;
		}
		$app = JFactory::getApplication();
		$app ->enqueueMessage('Attention config is empty');
		return 'Was not able to create config';
	}

	function setSession(){
		$session = JFactory::getSession();
		$session->clear('vmconfig');
// 		$app = JFactory::getApplication();
// 		$app ->enqueueMessage('setSession session cache <pre>'.print_r(self::$_jpConfig->_params,1).'</pre>');
		$session->set('vmconfig', base64_encode(serialize(self::$_jpConfig)),'vm');
		self::$loaded = true;
	}

	/**
	 * Find the configuration value for a given key
	 *
	 * @author Max Milbers
	 * @param string $key Key name to lookup
	 * @return Value for the given key name
	 */
	function get($key, $default='',$allow_load=true)
	{

		$value = '';
		if ($key) {

			if (empty(self::$_jpConfig->_params) && $allow_load) {
				self::loadConfig();
			}

			if (!empty(self::$_jpConfig->_params)) {
				if(array_key_exists($key,self::$_jpConfig->_params) && isset(self::$_jpConfig->_params[$key])){
					$value = self::$_jpConfig->_params[$key];
				} else {
					$value = $default;
				}

			} else {
				$value = $default;
			}

		} else {
			$app = JFactory::getApplication();
			$app -> enqueueMessage('VmConfig get, empty key given');
		}

		return $value;
	}

	function set($key, $value){

		if (empty(self::$_jpConfig->_params)) {
			self::loadConfig();
		}

		if (!empty(self::$_jpConfig->_params)) {
			self::$_jpConfig->_params[$key] = $value;
			self::$_jpConfig->setSession();
		}
	}

	/**
	 * For setting params, needs assoc array
	 * @author Max Milbers
	 */
	function setParams($params){
		self::$_jpConfig->_params = array_merge($this->_params,$params);
	}

	/**
	 * Writes the params as string and escape them before
	 * @author Max Milbers
	 */
	function toString(){
		$raw = '';
		$db = JFactory::getDBO();

		jimport( 'joomla.utilities.arrayhelper' );
		foreach(self::$_jpConfig->_params as $paramkey => $value){
// 			if(is_array($value)){
// 				JArrayHelper::toInteger($value);
// 			} else {
// 				$value = $db->getEscaped($value);
// 			}

			$raw .= $paramkey.'='.base64_encode(serialize($value)).'|';
// 			$raw .= $paramkey.'='.$value.'|';
		}
		self::$_jpConfig->_raw = substr($raw,0,-1);
		return self::$_jpConfig->_raw;
	}

	/**
	 * Find the currenlty installed version
	 *
	 * @author RickG
	 * @param boolean $includeDevStatus True to include the development status
	 * @return String of the currently installed version
	 */
	function getInstalledVersion($includeDevStatus=false)
	{
		// Get the installed version from the wmVersion class.

		return vmVersion::$RELEASE;
	}

	/**
	 * Compares two "A PHP standardized" version number against the current Joomla! version
	 * This function needs at least 3 digits, like 1.5.0,
	 * We can use it like isAtLeastVersion('1.6.0')
	 *
	 * This function returns a true if the version is equal or higher
	 * @return boolean
	 * @see http://www.php.net/version_compare
	 */
	function isAtLeastVersion ( $minimum ) {
		return (version_compare( JVERSION, $minimum, 'ge' ));
	}

	/**
	 * Return if the used joomla function is j15
	 */
	function isJ15(){
		return (strpos(JVERSION,'1.5') === 0);
	}
	/**
	 * ADD some javascript if needed
	 * Prevent duplicate load of script
	 * @ Author KOHL Patrick
	 */
		function jQuery()
	{
		static $jquery;
		// If exist exit
		if ($jquery) return;
		$document = JFactory::getDocument();
		$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js');
		$document->addScript('//ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js');
		//JHTML::script('jquery.min.js', '//ajax.googleapis.com/ajax/libs/jquery/1.6.1/', false);
		/*$document = JFactory::getDocument();
		$document->addScriptDeclaration('jQuery.noConflict();');*/

		$jquery = true;
		return;
	}
	// Virtuemart product and price script
	function jPrice()
	{
		static $jPrice;
		// If exist exit
		if ($jPrice) return;
                        JPlugin::loadLanguage('com_virtuemart');

		$closeimage = JURI::root(true) .'/components/com_virtuemart/assets/images/facebox/closelabel.png';
		$jsVars  = "siteurl = '". JURI::root(true) .'/' ."' ;\n" ;
		$jsVars .= "vmCartText = '". JText::_('COM_VIRTUEMART_MINICART_ADDED') ."' ;\n" ;
		$jsVars .= "vmCartError = '". JText::_('COM_VIRTUEMART_MINICART_ERROR') ."' ;\n" ;
		$jsVars .= "loadingImage = '".JURI::root(true) ."/components/com_virtuemart/assets/images/facebox/loading.gif'  ;\n" ;
		$jsVars .= "closeImage = '".$closeimage."' ; \n";
		$jsVars .= "faceboxHtml = \"<div id='facebox' style='display:none;'><div class='popup'><div class='content'></div> <a href='#' class='close'><img src='".$closeimage."' title='close' class='close_image' /></a></div></div>\" ;\n";
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($jsVars);
		JHTML::script('facebox.js', 'components/com_virtuemart/assets/js/', false);
		JHTML::script('vmprices.js', 'components/com_virtuemart/assets/js/', false);
		JHTML::stylesheet('facebox.css', 'components/com_virtuemart/assets/css/', false);
		$jPrice = true;
		return;
	}

	// Virtuemart Site Js script
	function jSite()
	{
		static $jSite;
		// If exist exit
		if ($jSite) return;
		JHTML::script('vmsite.js', 'components/com_virtuemart/assets/js/', false);
		$jSite = true;
		return;
	}
	// Virtuemart Site Js script VmConfig::jDate();
	function jDate($value='',$name="date",$class='class="datepicker"')
	{
		static $jDate;
		// If exist exit
		$display= '<input '.$class.' type="date" name="'.$name.'" value="'.$value.'" />';
		if ($jDate) return $display;
		$front = JURI::root(true).'/components/com_virtuemart/assets/';
		$document = JFactory::getDocument();
		//$document->addScript($front.'js/jquery.ui.core.min.js');
		//$document->addScript($front.'js/jquery.ui.datepicker.min.js');
		$document->addStyleSheet($front.'css/ui/jquery.ui.all.css');
				$lg = &JFactory::getLanguage();
		$lang = substr($lg->getTag(), 0, 2);
		$existingLang = array("af","ar","ar-DZ","az","bg","bs","ca","cs","da","de","el","en-AU","en-GB","en-NZ","eo","es","et","eu","fa","fi","fo","fr","fr-CH","gl","he","hr","hu","hy","id","is","it","ja","ko","kz","lt","lv","ml","ms","nl","no","pl","pt","pt-BR","rm","ro","ru","sk","sl","sq","sr","sr-SR","sv","ta","th","tj","tr","uk","vi","zh-CN","zh-HK","zh-TW");
		if (!in_array($lang, $existingLang)) $lang ="en-GB";
		$document->addScript($front.'js/i18n/jquery.ui.datepicker-'.$lang.'.js');
		$document->addScriptDeclaration('
		jQuery(document).ready( function() {
			jQuery( ".datepicker" ).datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat:"yy-mm-dd"
			});

		});
		');
		$jDate = true;
		return $display;
	}

	function JcountryStateList($stateIds) {
		static $JcountryStateList;
		// If exist exit
		if ($JcountryStateList) return;
		$document = JFactory::getDocument();
		$document->addScript(JURI::root(true).'/components/com_virtuemart/assets/js/vmsite.js');
		$document->addScriptDeclaration(' jQuery( function($) {
			$(".virtuemart_country_id").vm2front("list",{dest : "#virtuemart_state_id",ids : "'.$stateIds.'"});
		});');
		$JcountryStateList = true;
		return;
	}

/* 	function JimageSelectlist() {
		static $JimageSelectlist;
		if ($JimageSelectlist) return;
			$js = "
			jQuery(document).ready(function() {

				jQuery('#addnewselectimage').click(function() {
					jQuery('.selectimage select:first').clone(true).insertAfter('.selectimage select:last');
				});
				jQuery('.detachselectimage').click(function() {
					if (jQuery('.selectimage select:eq(1)').length)
					jQuery('.selectimage select:last').remove();
				});
				jQuery('.selectimage select').change(function() {
					var data = jQuery(this).val();

					jQuery.getJSON('index.php?option=com_virtuemart&view=media&task=viewJson&format=json&virtuemart_media_id='+data ,
					function(datas, textStatus) {
						if (datas.msg =='OK') {
							jQuery('#vm_display_image').attr('src', datas.file_root+datas.file_url);
							jQuery('#vm_display_image').attr('alt', datas.file_title);
							jQuery('#file_title').html(datas.file_title);
							jQuery('.adminform [name=file_title]').val(datas.file_title);
							jQuery('.adminform [name=file_description]').val(datas.file_description);
							jQuery('.adminform [name=file_meta]').val(datas.file_meta);
							jQuery('.adminform [name=file_url]').val(datas.file_url);
							jQuery('.adminform [name=file_url_thumb]').val(datas.file_url_thumb);
							jQuery('[name=active_media_id]').val(datas.virtuemart_media_id);
						if (datas.file_url_thumb !== 'undefined') { jQuery('#vm_thumb_image').attr('src',datas.file_root+datas.file_url_thumb); }
						else { jQuery('#vm_thumb_image').attr('src','');}
						} else jQuery('#file_title').html(datas.msg);
					});
					//if (jQuery('.selectimage select:eq(1)').length)
					//jQuery('.selectimage select:last').remove();
				});
			});";
			$document = JFactory::getDocument();
			$document->addScriptDeclaration($js);
		$JimageSelectlist = true;
		return;
	} */
	function JvalideForm()
	{
		static $jvalideForm;
		// If exist exit
		if ($jvalideForm) return;
		$lg = &JFactory::getLanguage();
		$lang = substr($lg->getTag(), 0, 2);
		$existingLang = array("cz", "da", "de", "en", "es", "fr", "it", "ja", "nl", "pl", "pt", "ro", "ru", "tr");
		if (!in_array($lang, $existingLang)) $lang ="en";
		JHTML::script('jquery.validationEngine.js', 'components/com_virtuemart/assets/js/', false);
		JHTML::script('jquery.validationEngine-'.$lang.'.js', 'components/com_virtuemart/assets/js/languages/', false);
		$document = JFactory::getDocument();
		$document->addScriptDeclaration( "

			jQuery(document).ready(function() {
				jQuery('#adminform').validationEngine();
			});"  );
		JHTML::stylesheet ( 'validationEngine.template.css', 'components/com_virtuemart/assets/css/', false );
		JHTML::stylesheet ( 'validationEngine.jquery.css', 'components/com_virtuemart/assets/css/', false );
		$jvalideForm = true;
		return;
	}
	/*	function cssSite()
	{
		static $jSite;
		// If exist exit
		if ($jSite) return;
		JHTML::script('vmsite.js', 'components/com_virtuemart/assets/js/', false);
		$jSite = true;
		return;
	}*/

	/**
	 * ADD some CSS if needed
	 * Prevent duplicate load of CSS stylesheet
	 * @ Author KOHL Patrick
	 */

	function cssSite() {
		static $cssSite;
		if ($cssSite) return;
		// Get the Page direction for right to left support
		$document = & JFactory::getDocument ();
		$direction = $document->getDirection ();
		$cssFile = 'vmsite-' . $direction . '.css';

		// If exist exit

		JHTML::stylesheet ( $cssFile, 'components/com_virtuemart/assets/css/', false );
		$cssSite = true;
		return;
	}

	/**
	 * Read the file vm_config.dat from the install directory, compose the SQL to write
	 * the config record and store it to the dabase.
	 *
	 * @param $_section Section from the virtuemart_defaults.cfg file to be parsed. Currently, only 'config' is implemented
	 * @return Boolean; true on success, false otherwise
	 * @author Oscar van Eijk
	 */
	public function installVMconfig($_section = 'config')
	{

		$_datafile = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'virtuemart_defaults.cfg';
		if (!file_exists($_datafile)) {
			JError::raiseWarning(500, 'The data file with the default configuration could not be found. You must configure the shop manually.');
			return false;
		} else {
			vmInfo('Taking config from file');
		}

		$_section = '['.strtoupper($_section).']';
		$_data = fopen($_datafile, 'r');
		$_configData = array();
		$_switch = false;
		while ($_line = fgets ($_data)) {
			$_line = trim($_line);

			if (strpos($_line, '#') === 0) {
				continue; // Commentline
			}
			if ($_line == '') {
				continue; // Empty line
			}
			if (strpos($_line, '[') === 0) {
				// New section, check if it's what we want
				if (strtoupper($_line) == $_section) {
					$_switch = true; // Ok, right section
				} else {
					$_switch = false;
				}
				continue;
			}
			if (!$_switch) {
				continue; // Outside a section or inside the wrong one.
			}

			if (strpos($_line, '=') !== false) {

				$pair = explode('=',$_line);
				if(!empty($pair[1])){
					if(strpos($pair[1], 'array:') !== false){
						$pair[1] = substr($pair[1],6);
						$pair[1] = explode('|',$pair[1]);
					}
					$_line = $pair[0].'='.base64_encode(serialize($pair[1]));
				} else {
					$_line = $pair[0].'=';
				}
				$_configData[] = $_line;

			}

		}

		fclose ($_data);

		$_value = join('|', $_configData);
		if (!$_value) {
			return false; // Nothing to do
		}

		if ($_section == '[CONFIG]') {
			$_qry = "CREATE TABLE IF NOT EXISTS `#__virtuemart_configs` (
  `virtuemart_config_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `config` text,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`virtuemart_config_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Holds configuration settings' AUTO_INCREMENT=1 ;";
			$_db = JFactory::getDBO();
			$_db->setQuery($_qry);
			$_db->query();
			$_qry = "INSERT INTO `#__virtuemart_configs` (`virtuemart_config_id`, `config`) VALUES ('1', '$_value')";

		}

		// Other sections can be implemented here

		// Write to the DB
		$_db = JFactory::getDBO();
		$_db->setQuery($_qry);
		if (!$_db->query()) {
			JError::raiseWarning(1, 'VmConfig::installVMConfig: '.JText::_('COM_VIRTUEMART_SQL_ERROR').' '.$_db->stderr(true));
			return false;
		}else {

// 			vmdebug('Config installed file, store values '.$_value);
			return true;
		}

	}

}
// pure php no closing tag
