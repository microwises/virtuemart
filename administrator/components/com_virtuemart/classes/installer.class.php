<?php
if (! defined ( '_VALID_MOS' ) && ! defined ( '_JEXEC' ))
	die ( 'Direct Access to ' . basename ( __FILE__ ) . ' is not allowed.' );
/**
*
* @version $Id: installer.class.php 27/09/2008
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

class vmInstaller {
	/**
	 * General handler to install an extension
	 *
	 * @param array $d
	 */
	function install(&$d) {
		global $vmLogger;
		$package = vmInstaller::install_package();
		if( isset($package ['type'])) {
			
			if( file_exists(CLASSPATH.'installer/'.basename($package ['type']).'.vmextension.php')) {
				require_once(CLASSPATH.'installer/'.basename($package ['type']).'.vmextension.php');
				$classname = 'vmInstaller'.basename($package ['type']);
				if( !class_exists($classname)){
					$vmLogger->err('Failed to to install this package: Installation Handler not found!');
					return false;
				}
				$vminstaller_instance = new $classname();
				
				if( $vminstaller_instance->install($package) ) {
					return true;
				}
		
			}
		}
		
		$vmLogger->err('Failed to to install this package: Installation Handler not found!');
		return false;
		
	}
	/**
	 * Method to validate the installation package of an extension
	 *
	 * @static
	 * @param $infos the infomation of method $files list of file need to install
	 * @return 
	 * @since 1.2.0
	 */
	function is_valid_installpackage($infos, $files, $xml) {
		global  $vmLogger;
		
		$element = $infos ['element'];
		if ($element == '') {
			$vmLogger->err ( "The name of the Extension was not found in the package!" );
			return false;
		}
		$xml_name = $element . ".xml";
		$file_name = $element . ".php";
		if (JFile::getName ( $xml ) != $xml_name) {
			$vmLogger->err ( "The file $element.xml was not found!!" );
			return false;
		}
		
		foreach ( $files as $file ) {
			if (JFile::getName ( $file ) == $file_name) {
				$name = true;
			}
		}
		if ($name == true) {
			return true;
		} else {
			$vmLogger->err ( "The file $file_name was not found!!" );
			return false;
		}
	}
	/**
	 * Inserts a new plugin record
	 *
	 * @param array $info
	 * @param string $type
	 */
	function insert_plugin($info, $type) {
		if( !empty($type)) {
			$db = new ps_db;
			$fields = array('name' => $info['name'],
									'element' => $info['element'],
									'folder' => $type,
									'ordering' => '1',
									'published' => '0',
									'iscore' => '0',
									'vendor_id' => $_SESSION['ps_vendor_id'],
									'shopper_group_id' => $_SESSION['auth']['default_shopper_group']			
			);
			$db->buildQuery('INSERT', '#__{vm}_plugins', $fields );
		}
	}
	/**
	 * Deletes a plugin record
	 *
	 * @param string $plugin
	 * @param string $type
	 */
	function delete_plugin( $plugin, $type ) {
		$db = new ps_db;
		$query = 'DELETE FROM `#__{vm}_plugins` 
						WHERE element=\''.$db->getEscaped($plugin).'\' 
							AND folder=\''.$db->getEscaped($type).'\' 
						LIMIT 1';
		return $db->query( $query ) !== false;
		
	}
	/**
	 * Retrieves a list of all available extension types/handlers
	 *
	 * @return array
	 */
	function get_extension_types() {
		$extensions = array();
		$extension_files = vmReadDirectory ( CLASSPATH . "installer/", "vmextension.php$", false, true );
		foreach( $extension_files as $extension ) { 
			$extensions[basename($extension, '.vmextension.php')] = $extension;
		}
		return $extensions;
	}
	/**
	 * Method to read the XML file 
	 *
	 * @static
	 * @param string $xml_path dir Path to xml file
	 * @return array information about the install file
	 * @since 1.2.0
	 */
	function getInfo($xml_path) {
		global  $vmLogger;
		$info = array ("name" => '', "author" => '', "creationdate" => '', "copyright" => '', "license" => '', "authoremail" => '', "authorurl" => '', "version" => '', "description" => '' );
		require_once( CLASSPATH.'simplexml.php');
		$xml = new vmSimpleXML ( );
		if( $xml->loadFile ( $xml_path ) === false ) {
			$vmLogger->err( "Failed to parse the XML file ".basename($xml_path));
			return false;
		}
		
		$count = count ( $xml->document->_children );
		
		for($i = 0; $i < $count; $i ++) {
			switch($xml->document->_children [$i]->_name ) {
			case "name":
				$info ["name"] = $xml->document->_children [$i]->_data; break;
			case "author":
				$info ["author"] = $xml->document->_children [$i]->_data; break;
			case  "creationdate":
				$info ["creationdate"] = $xml->document->_children [$i]->_data; break;
			case  "copyright":
				$info ["copyright"] = $xml->document->_children [$i]->_data; break;
			case  "license":
				$info ["license"] = $xml->document->_children [$i]->_data; break;
			case  "authoremail":
				$info ["authoremail"] = $xml->document->_children [$i]->_data; break;
			case  "authorurl":
				$info ["authorurl"] = $xml->document->_children [$i]->_data; break;
			case  "version":
				$info ["version"] = $xml->document->_children [$i]->_data; break;
			case  "description":
				$info ["description"] = $xml->document->_children [$i]->_data; break;
			case  "element":
				$info ["element"] = $xml->document->_children [$i]->_data; break;
			case  "is_creditcard":
				$info ["is_creditcard"] = $xml->document->_children [$i]->_data; break;
			case  "type": // used for: payment method type
				$info ["type"] = $xml->document->_children [$i]->_data; break;
			}
		}
		return $info;
	}
	
	/**
	 * Method to read the XML file 
	 *
	 * @static
	 * @param string $xml_path dir Path to xml file
	 * @return file, query,...
	 * @since 1.2.0
	 */
	
	function getFile($xml_path) {
		global  $vmLogger;
		$files = array ( );
		require_once( CLASSPATH.'simplexml.php');
		$xml = new vmSimpleXML ( );
		if( $xml->loadFile ( $xml_path ) === false ) {
			$vmLogger->err( "Failed to parse the XML file ".basename($xml_path));
			return false;
		}
		
		$file_install = array ("query" => '', "file" => '', "administrator_file" => '' );
		$count = count ( $xml->document->_children );
		
		$count_data = array(); //The value to count the _data of a note to store in array
		$queries = array();
		$list_file = array();
		$list_admin_file = array();
		$list_languages = array();
		
		for($i = 0; $i < $count; $i ++) {
			//Check the note in the install _children name
			if ($xml->document->_children [$i]->_name == "install") {
				$count_type = count ( $xml->document->_children [$i]->_children );
				
				if ($count_type > 3 || $count_type <= 0) {
					$vmLogger->err ( 'XML_ERROR' );
					return false;
				}
				
				for($j = 0; $j < $count_type; $j ++) {
					
					switch($xml->document->_children [$i]->_children [$j]->_name ) {
					case 'queries':
						$count_query = count ( $xml->document->_children [$i]->_children [$j]->_children );
						$count_data = 0;
						for($k = 0; $k < $count_query; $k ++) {
							if ($xml->document->_children [$i]->_children [$j]->_children [$k]->_name == 'query') {
								
								$queries [$count_data++] = $xml->document->_children [$i]->_children [$j]->_children [$k]->_data;
							
							}
						}
						break;
					case 'files':
						$count_file = count ( $xml->document->_children [$i]->_children [$j]->_children );
						$count_data = 0;
						for($k = 0; $k < $count_file; $k ++) {
							if ($xml->document->_children [$i]->_children [$j]->_children [$k]->_name == 'filename') {
								
								$list_file[$count_data++] = $xml->document->_children [$i]->_children [$j]->_children [$k]->_data;
							}
						}
						
						break;
					case 'administrator_files':
						$count_admin_file = count ( $xml->document->_children [$i]->_children [$j]->_children );
						$count_data = 0;
						for($k = 0; $k < $count_admin_file; $k ++) {
							if ($xml->document->_children [$i]->_children [$j]->_children [$k]->_name == 'filename') {
								
								$list_admin_file [$count_data++] = $xml->document->_children [$i]->_children [$j]->_children [$k]->_data;
							}
						}
						break;
					case 'languages':
						$count_languages = count ( $xml->document->_children [$i]->_children [$j]->_children );
						$count_data = 0;
						for($k = 0; $k < $count_languages; $k ++) {
							if ($xml->document->_children [$i]->_children [$j]->_children [$k]->_name == 'language') {
								
								$list_languages[$count_data++] = $xml->document->_children [$i]->_children [$j]->_children [$k]->_data;
							}
						}
						
					}
				}
			}
		}
		//Store the data from the XML file
		$file_install ["query"] = $queries;
		$file_install ["languages"] = $list_languages;
		$file_install ["administrator_file"] = $list_admin_file;
		$file_install ["file"] = $list_file;
		
		return $file_install;
	}

	
	/**
	 * Method create packege for installer
	 *
	 * @static
	 * @param 
	 * @return package for installer
	 * @since 1.2.0
	 */
	function install_package() {
		global  $vmLogger;
		$userfile = vmRequest::getVar ( 'install_package', null, 'files', 'array' );
		
		// Make sure that zlib is loaded so that the package can be unpacked
		if (! extension_loaded ( 'zlib' )) {
			$vmLogger->err ( 'NO_ZLIB' );
			return false;
		}
		
		// If there is no uploaded file, we have a problem...
		if (! is_array ( $userfile )) {
			$vmLogger->err ( 'NO_FILE_SELECT' );
			//JError::raiseWarning('SOME_ERROR_CODE', JText::_('No file selected'));
			return false;
		}
		
		// Check if there was a problem uploading the file.
		if ($userfile ['error'] || $userfile ['size'] < 1) {
			$vmLogger->err ( 'WARNING_UPLOAD_ERROR' );
			return false;
		}
		
		// Build the appropriate paths
		$config = & JFactory::getConfig ();
		//print_r($config);
		//die();
		$tmp_dest = $config->getValue ( 'config.tmp_path' ) . DS . $userfile ['name'];
		$tmp_src = $userfile ['tmp_name'];
		//print_r($tmp_dest);echo " Desct and: Src :";
		//print_r($tmp_src);
		//die();
		

		// Move uploaded file
		jimport ( 'joomla.filesystem.file' );
		$uploaded = JFile::upload ( $tmp_src, $tmp_dest );
		
		// Unpack the downloaded package file
		$package = vmInstaller::unpack ( $tmp_dest );
		
		return $package;
	}
	
	/**
	 * Unpacks a file and verifies it as a Joomla element package
	 * Supports .gz .tar .tar.gz and .zip
	 *
	 * @static
	 * @param string $p_filename The uploaded package filename or install directory
	 * @return boolean True on success, False on error
	 * @since 1.2.0
	 */
	function unpack($p_filename) {
		global  $vmLogger;
		// Path to the archive
		$archivename = $p_filename;
		
		// Temporary folder to extract the archive into
		$tmpdir = uniqid ( 'install_' );
		
		// Clean the paths to use for archive extraction
		$extractdir = JPath::clean ( dirname ( $p_filename ) . DS . $tmpdir );
		$archivename = JPath::clean ( $archivename );
		
		// do the unpacking of the archive
		jimport ( 'joomla.filesystem.archive' );
		$result = JArchive::extract ( $archivename, $extractdir );
		
		if ($result === false) {
			return false;
		}
		
		/*
		 * Lets set the extraction directory and package file in the result array so we can
		 * cleanup everything properly later on.
		 */
		$retval ['extractdir'] = $extractdir;
		$retval ['packagefile'] = $archivename;
		
		/*
		 * Try to find the correct install directory.  In case the package is inside a
		 * subdirectory detect this and set the install directory to the correct path.
		 *
		 * List all the items in the installation directory.  If there is only one, and
		 * it is a folder, then we will set that folder to be the installation folder.
		 */
		$dirList = array_merge ( JFolder::files ( $extractdir, '' ), JFolder::folders ( $extractdir, '' ) );
		
		if (count ( $dirList ) == 1) {
			if (JFolder::exists ( $extractdir . DS . $dirList [0] )) {
				$extractdir = JPath::clean ( $extractdir . DS . $dirList [0] );
			}
		}
		
		/*
		 * We have found the install directory so lets set it and then move on
		 * to detecting the extension type.
		 */
		$retval ['dir'] = $extractdir;
		
		/*
		 * Get the extension type and return the directory/type array on success or
		 * false on fail.
		 */
		if ($retval ['type'] = vmInstaller::detectType ( $extractdir )) {
			return $retval;
		} else {
			return false;
		}
	}
	
	/**
	 * Method to detect the extension type from a package directory
	 *
	 * @static
	 * @param string $p_dir Path to package directory
	 * @return mixed Extension type string or boolean false on fail
	 * @since 1.2.0
	 */
	function detectType($p_dir) {
		global  $vmLogger;
		// Search the install dir for an xml file
		$files = JFolder::files ( $p_dir, '\.xml$', 1, true );
		if (count ( $files ) > 0) {
			
			foreach ( $files as $file ) {
				$xmlDoc = & JFactory::getXMLParser ();
				$xmlDoc->resolveErrors ( true );
				
				if (! $xmlDoc->loadXML ( $file, false, true )) {
					// Free up memory from DOMIT parser
					unset ( $xmlDoc );
					continue;
				}
				$root = & $xmlDoc->documentElement;
				
				if (! is_object ( $root ) || ($root->getTagName () != "install" && $root->getTagName () != 'mosinstall')) {
					unset ( $xmlDoc );
					continue;
				}
				
				$type = $root->getAttribute ( 'type' );
				// Free up memory from DOMIT parser
				unset ( $xmlDoc );
				return $type;
			}
		
		} else {
			$vmLogger->err ( 'CANT_FIND_XML' );
			return false;
		}
	}
	
	/**
	 * Method to detect the extension type from a package directory
	 *
	 * @static
	 * @param array $queries list query need to install
	 * @return return boolean value
	 * @since 1.2.0
	 */
	function install_query($queries = '') {
		global  $vmLogger;
		
		$db_insert = new ps_DB ( );
		if ($queries != '') {
			foreach ( $queries as $query ) {
				if ($query != '') {
					if($db_insert->query($query) === false ) {
						$vmLogger->err ( 'QUERY_ERROR' );
						return false;
					}
				}
			}
		}
		return true;
	}
	
	/**
	 * Method to detect the extension type from a package directory
	 *
	 * @static
	 * @param $query the query
	 * @return table's name  or boolean false on fail
	 * @since 1.2.0
	 */
	function findTableName($query) {
		trim ( $query );
		$table = '';
		$query = strtolower ( $query );
		$query = str_replace ( 'if ', '', $query );
		$query = str_replace ( 'not ', '', $query );
		$query = str_replace ( 'exists ', '', $query );
		
		$a = strpos ( $query, 'table' );
		
		if ($a == false) {
			$table = '';
		} else {
			$table = substr ( $query, $a );
		}
		
		$table = str_replace ( 'table', ' ', $table );
		trim ( $table );
		
		$tab = explode ( '(', $table );
		
		if (! $tab [1]) {
			$tab = explode ( ' ', $table );
		}
		
		return $tab [0];
	}
	
	
	function rollback($files = '', $queries = '', $path = '') {
		$db_rollback = new ps_DB ( );
		//echo "patch = ". $path."<br>";
		//print_r($files);
		

		foreach ( $files as $file ) {
			$path_del = $path . DS . $file;
			JFile::delete ( $path_del );
			//echo "<br>".$path_del."<br>";
		}
		if ($queries != '') {
			foreach ( $queries as $query ) {
				
				$table = vmInstaller::findTableName ( $query );
				$query = "DROP TABLE IF EXISTS " . $table;
				
				if ($table != '') {
					$db_rollback->setQuery ( $query );
					$db_rollback->query ();
				}
			}
		}
	}
	/**
	 * Method to copy a file from an installation package
	 *
	 * @static
	 * @param $dir source parth folder ,$list_file list file copy ,$path_folder 
	 * @return true if have no error
	 * @since 1.2.0
	 */	
	function install_file($dir, &$list_file, $path_folder) {
		global $vmLogger;
		$count = count ( $list_file );
		
		if ($count > 0) {
			
			foreach ( $list_file as $file ) {
				$src = $dir . DS . $file;
				$path = $path_folder . DS . $file;
				
				if (! file_exists ( $src )) {
					/*
					 * The source file does not exist.  Nothing to copy so set an error
					 * and return false.
					 */
					$vmLogger->err( 'Required File missing in Installation Package: ' . $src );
					
					return false;
				} elseif (file_exists ( $path )) {
					/*
						 * The destination file already exists and the overwrite flag is false.
						 * Set an error and return false.
						 */
					$vmLogger->err( 'The File ' . $path . ' already exists! ' );
					
					return 'exists';
				}
				
				//Create the sub folder if not exists
				$check_folder = explode ( "\\", $file );
				$count_folder = count ( $check_folder );
				if ($count_folder > 1) {
					$folder_pos = $path_folder;
					for($i = 0; $i < $count_folder - 1; $i ++) {
						if (! JFolder::exists ( $folder_pos . DS . $check_folder [$i] )) {
							
							JFolder::create ( $folder_pos . DS . $check_folder [$i] );
						}
						$folder_pos = $folder_pos . DS . $check_folder [$i];
					}
				}
				//copy file
				JFile::copy ( $src, $path );
				
			}
			
			return true;
		}
		return false;
	}
	/**
	 * Handler to uninstall an extension
	 *
	 * @param array $d
	 */
	function uninstall(&$d) {
		global $vmLogger;
		
//		$extension_type = vmGet( $d, 'extension_type');
		//Lets use JoomlaNative method, it may not work, caused by the $d
		$extension_type = JRequest::getVar( 'extension_type');
		
		if( file_exists(CLASSPATH.'installer/'.$extension_type.'.vmextension.php')) {
			require_once(CLASSPATH.'installer/'.basename($extension_type).'.vmextension.php');
			$classname = 'vmInstaller'.$extension_type;
			$extension_name = JRequest::getVar($extension_type.'name');
			$vminstaller_instance = new $classname();
			if( is_array($extension_name)) {
				$result = true;
				foreach( $extension_name as $extension) {
					if( !$vminstaller_instance->remove($extension, $extension_type)) {
						$result = false;
						$vmLogger->err( 'Failed to uninstall the Extension '.$extension);
					}
				}
				return $result;
			} else {
				if( !$vminstaller_instance->remove($extension_name, $extension_type)) {
					$vmLogger->err( 'Failed to uninstall the Extension '.$extension);
					return false;
				}
				return true;
			}
		} else { 
			
			$vmLogger->err( 'Unknown Extension Type!' );
			return false;
		}
		
	}
	function remove($plugin_name, $folder) {
		global $vmLogger;
		
		$plugin_name = $GLOBALS['vmInputFilter']->clean($plugin_name, 'WORD');
		$folder = $GLOBALS['vmInputFilter']->clean($folder, 'WORD');
		
		$xml_path = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'plugins' . DS . $folder .  DS . $plugin_name . '.xml';
		jimport ( 'joomla.filesystem.file' );
		$path = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'. DS . 'plugins' . DS . $folder;
		if (JFile::exists ( $xml_path )) {
			$file_install = vmInstaller::getFile ( $xml_path );
			vmInstaller::rollback ( $file_install ['file'], $file_install ['query'], $path );
			
			$vmLogger->info('Uninstall successful!');
			JFile::delete ( $xml_path );
			$this->delete_plugin($plugin_name, $folder );
			return true;
		} else {
			
			$vmLogger->err( 'Can not uninstall this extension ('.$folder.')! The XML file was not found!');
		}
		return false;
	}
}
?>