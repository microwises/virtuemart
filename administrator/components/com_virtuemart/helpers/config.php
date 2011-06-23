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
 *   $vmConfig = VmConfig::getInstance(); // load the config and create an instance
 *  $vmConfig -> jQuery(); // for use of jQuery
 *  Then always use the defined paths below to ensure future stability
 */
define( 'JPATH_VM_SITE', JPATH_ROOT.DS.'components'.DS.'com_virtuemart' );
define( 'JPATH_VM_ADMINISTRATOR', JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart' );

require(JPATH_VM_ADMINISTRATOR.DS.'version.php');


class VmConfig
{
	/* instance of class */
	private static $_instance = null;	

	private function __construct() {
	self::loadConfig();
	}

	/**
	 * Load the configuration values from the database into a session variable.
	 * This step is done to prevent accessing the database for every configuration variable lookup.
	 *
	 * @author RickG
	 */
	private function loadConfig() {
		$db = JFactory::getDBO();
		$query = 'SELECT `config` FROM `#__virtuemart_configs` WHERE `virtuemart_config_id` = "1"';
		$db->setQuery($query);
		$config = $db->loadResult();

		if(empty($config)){
			$config = self::installVMconfig();
			$config = $db->loadResult();
		}
		$session = JFactory::getSession();
		$session->clear('vmconfig');
		$session->set('vmconfig', $config,'vm');
	}
	
	// Get always the same VmConfig 
	public static function getInstance() {
		if(is_null(self::$_instance)) {
			self::$_instance = new VmConfig();
		}
		return self::$_instance;
	}


	/**
	 * Find the configuration value for a given key
	 *
	 * @author RickG
	 * @param string $key Key name to lookup
	 * @return Value for the given key name
	 */
	function get($key = '', $default='')
	{
		$value = '';
		if ($key) {
			jimport('joomla.html.parameter');
			$session = JFactory::getSession();
			$config = $session->get('vmconfig', '','vm');
			if (!$config) {
				VmConfig::loadConfig();
				$config = $session->get('vmconfig', '','vm');
			}

			if ($config) {
				$params = new JParameter($config);
				$value = $params->get($key);
			}
			else {
			    $params = new JParameter('');
			    $value = '';
			}

			if ($value == '') {
			    $params->set($key, $default);
			    $value = $default;
			}
		}

		return $value;
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
		JHTML::script('jquery.js', 'components/com_virtuemart/assets/js/', false);
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
		$jsVars  = "vmCartText = '". JText::_('COM_VIRTUEMART_MINICART_ADDED') ."' ;\n" ;
		$jsVars .= "vmCartError = '". JText::_('COM_VIRTUEMART_MINICART_ERROR') ."' ;\n" ;
		$jsVars .= "loadingImage = '".JURI::root(true) ."/components/com_virtuemart/assets/images/facebox/loading.gif'  ;\n" ;
		$jsVars .= "closeImage = '".$closeimage."' ; \n";
		$jsVars .= "faceboxHtml = \"<div id='facebox' style='display:none;'><div class='popup'><div class='content'></div> <a href='#' class='close'><img src='".$closeimage."' title='close' class='close_image' /></a></div></div>\" ;\n";
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($jsVars);
		$colorbox='colorbox5.css';
		JHTML::script('facebox.js', 'components/com_virtuemart/assets/js/', false);
		JHTML::script('jquery.colorbox.js', 'components/com_virtuemart/assets/js/', false);
		JHTML::script('vmprices.js', 'components/com_virtuemart/assets/js/', false);
		JHTML::stylesheet('facebox.css', 'components/com_virtuemart/assets/css/', false);
		JHTML::stylesheet($colorbox, 'components/com_virtuemart/assets/css/', false);
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

	function JcountryStateList() {
		static $JcountryStateList;
		// If exist exit
		if ($JcountryStateList) return;
		$document = JFactory::getDocument();
		$document->addScriptDeclaration('jQuery(function(){VM.countryStateList();});');
		$JcountryStateList = true;
		return;
	}

	function JimageSelectlist() {
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
	}
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
		$_datafile = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'virtuemart_defaults.cfg';
		if (!file_exists($_datafile)) {
			JError::raiseWarning(500, 'The data file with the default configuration could not be found. You must configure the shop manually.');
			return false;
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
			if (preg_match_all('/\{(\w+?)\}/', $_line, $_matches)) {
				foreach ($_matches[1] as $_match) {
					if (defined($_match)) {
						$_line = preg_replace("/\{$_match\}/", constant($_match), $_line);
					}
				}
			}
			if (strpos($_line, '=') === false) {
				$_line .= '=';
			}
			$_configData[] = $_line;
		}

		fclose ($_data);

		$_value = join('\n', $_configData);
		if (!$_value) {
			return false; // Nothing to do
		}

		if ($_section == '[CONFIG]') {
			$_qry = "INSERT INTO `#__virtuemart_configs` (`virtuemart_config_id`, `config`) VALUES (null, '$_value')";
		}
		// Other sections can be implemented here

		// Write to the DB
		$_db = JFactory::getDBO();
		$_db->setQuery($_qry);
		if (!$_db->query()) {
			JError::raiseWarning(1, 'JInstaller::install: '.JText::_('COM_VIRTUEMART_SQL_ERROR').' '.$_db->stderr(true));
			return false;
		}
		return true;
	}
	
}
// pure php no closing tag
