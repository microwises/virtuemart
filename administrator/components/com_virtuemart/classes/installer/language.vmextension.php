<?php
if (! defined ( '_VALID_MOS' ) && ! defined ( '_JEXEC' ))
	die ( 'Direct Access to ' . basename ( __FILE__ ) . ' is not allowed.' );

/**
*
* @version $Id: language.class.php 27/09/2008
* @package VirtueMart
* @subpackage classes
* @copyright Copyright 2008 HoaNT-Vsmarttech for this class
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/ 

class vmInstallerLanguage {
	function getTitle() {
		return 'Languages';
	}
	/**
	 * Method to show the language list
	 *
	 * @static
	 * @param 
	 * @return 
	 * @since 1.2.0
	 */
	function get_extension_list(){
		
		$rows= array();
		$path=JPATH_ADMINISTRATOR.'/components/com_virtuemart/languages/';
		$files = vmReadDirectory( $path, '.xml$' );
		if(count($files)==0)
		{
			return $rows;
		}
		foreach ($files as  $file)
		{
			$rows[] = vmInstaller::getInfo($path.DS.$file);
		}
		
		return $rows;	
	}
	
	/**
	 * Method to valid the Language xml file install
	 *
	 * @static
	 * @param $infos the infomation of method $files list of file need to install
	 * @return 
	 * @since 1.2.0
	 */
	function valid_lang($infos, $files, $xml) {
		global $vmLogger;
		
		$name = $infos ['name'];
		if ($name == '') {
			$vmLogger->err ( "The name of language not found!" );
			return false;
		}
		$xml_name = $name . ".xml";
		$file_name = $name . ".php";
		if (JFile::getName ( $xml ) != $xml_name) {
			$vmLogger->err ( "The $name.xml not found!!" );
			return false;
		}
		foreach ($files as $file)
		{
			if ($file_name != JFile::getName ( $file )) {
				$vmLogger->err ( "The name of language file error!!" );
				return false;
			}
		}
		
		$name = true;
		
		foreach ( $files as $file ) {
			$c=explode("\\",$file);
			if (count($c)!=2) {
				$name = false;
			}
		}
		if ($name == true) {
			return true;
		} else {
			$vmLogger->err ( "XML file error!!" );
			return false;
		}
	}
	
	/**
	 * Method to detect the extension type from a package directory
	 *
	 * @static
	 * @param array $package all about the installation
	 * @return 
	 * @since 1.2.0
	 */
	function install($package) {
		global $vmLogger;
		
		//print_r($package);
		$files = JFolder::files ( $package ['dir'], '\.xml$', 1, true );
		foreach ( $files as $file ) {
			//Get the information of the XML 
			$info = vmInstaller::getInfo ( $file );
			
			//Get the data of the installation
			$file_install = vmInstaller::getFile ( $file );
			
			//valid the Payment method
			$valid = vmInstallerLanguage::valid_lang($info, $file_install ['file'], $file );
			
			if (! $valid) {
				return false;
			}
			
			$path = JPATH_ADMINISTRATOR . DS . "components" . DS . "com_virtuemart".DS."languages".DS.basename($info ['name']);
			$check_file = vmInstallerLanguage::install_file ( $package ['dir'], $file_install ['file'], $path );
			
			if ($check_file === 'exists') {
				return false;
			} else {
				$check_query = vmInstaller::install_query ( $file_install ['query'] );
				if ($check_file && $check_query) {
					$src = $package ['dir'] . DS . JFile::getName ( $file );
					$path = JPATH_ADMINISTRATOR . DS . "components" . DS . "com_virtuemart" . DS . "languages" . DS . $info ['name'] . ".xml";
					
					JFile::copy ( $src, $path );
				}
				if ($check_file && $check_query) {
					echo 'SUCCESSFUL_INSTALL';
					echo "<br>" . $info ['description'];
				} else {
					vmInstaller::rollback ( $file_install ['file'], $file_install ['query'], $path );
				}
			}
		}
		return true;
	}
	/**
	 * Method uninstaller Language
	 *
	 * @static
	 * @param $paymentname is Language name want to remove
	 * @return 
	 * @since 1.2.0
	 */
	function uninstall($languagename) {
		$xml_path = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'languages' . DS . $languagename . '.xml';
		jimport ( 'joomla.filesystem.file' );
		$path = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS . 'languages'.DS.basename($languagename);
		if (JFile::exists ( $xml_path )) {
			$file_install = vminstaller::getFile ( $xml_path );
			vminstaller::rollback ( $file_install ['file'], $file_install ['query'], $path );
			$url = 'index.php?pshop_mode=admin&page=installer.extension_list&option=com_virtuemart';
			$msg = 'Uninstall successfully!';
			JFile::delete ( $xml_path );
		} else {
			$url = 'index.php?pshop_mode=admin&page=installer.extension_list&option=com_virtuemart';
			$msg = 'Can not uninstall this Language! The XML file was not found!';
		}
		vmRedirect($url, $msg);
	}
}

?>