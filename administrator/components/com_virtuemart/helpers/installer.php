<?php
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

// no direct access
defined('_JEXEC') or die('Restricted access');

class VmInstaller {
    /** @var string Last error generated from this installer class */
    var $_error = '';
    var $_remotePackageName = array();
    var $_localPackageName = '';
    var $_extractDir = '';
    var $_files;

    /**
     * General handler to install an extension
     *
     * 1-04-09 RickG Prepared for VM 1.5
     *
     * @param array $d
     */
    function getInstaller($installerHandler='') {
	$vminstallerInstance = new VmInstaller();

	// Check for a custom installer handler
	if ($installerHandler) {
	    $helperPath = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS;
	    if (file_exists($helperPath.'installer_'.$installerHandler.'.php')) {
		require_once($helperPath.'installer_'.$installerHandler.'.php');
		$classname = 'VmInstaller'.$installerHandler;
		if (!class_exists($classname)) {
		    return false;
		}
		$vminstallerInstance = new $classname();
	    }
	}
	else {
	    $vminstallerInstance = new VmInstaller();
	}

	return $vminstallerInstance;
    }


    /**
     * Upload and install a given package name
     *
     * @param array $packageName Package file to upload
     */
    function uploadAndInstall($packageName='') {
	jimport('joomla.installer.installer');

	if (!$packageName) {
	    $this->_error = 'No package name provided!';
	    return false;
	}

	$this->_remotePackageName = $packageName;
	if (!$this->uploadPackage()) {
	    return false;
	}

	$jinstaller = JInstaller::getInstance();
	$jinstaller->install($this->_localPackageName);

/*
	if (!$this->unpackPackage()) {
	    return false;
	}

	if (!$this->validatePackage()) {
	    return false;
	}

	if (!$this->installPackage()) {
	    return false;
	}

	$this->cleanupAfterInstall();
*/
	return true;
    }


    /**
     * Upload the package to the Joomla temp directory
     *
     * return boolean True if package could be uploaded, false otherwise
     */
    function uploadPackage() {
	// Make sure that zlib is loaded so that the package can be unpacked
	if (!extension_loaded('zlib')) {
	    $this->_error = JText::_('NO_ZLIB');
	    return false;
	}

	// If there is no uploaded file, we have a problem...
	if (!is_array($this->_remotePackageName)) {
	    $this->_error = JText::_('NO_FILE_SELECT');
	    return false;
	}

	// Check if there was a problem uploading the file.
	if ($this->_remotePackageName['error'] || $this->_remotePackageName['size'] < 1) {
	    $this->_error = JText::_('WARNING_UPLOAD_ERROR');
	    return false;
	}

	// Build the appropriate paths
	$config = JFactory::getConfig ();
	$tmp_dest = $config->getValue('config.tmp_path') . DS . $this->_remotePackageName['name'];
	$tmp_src = $this->_remotePackageName['tmp_name'];

	// Move uploaded file
	jimport('joomla.filesystem.file');
	if (!JFile::upload($tmp_src, $tmp_dest)) {
	    $this->_error = JText::_('JFile error uploading package!');
	    return false;
	}
	else {
	    $this->_localPackageName = $tmp_dest;
	}

	return true;
    }


    /**
     * Unpacks the package into a temporary directory
     * Supports .gz .tar .tar.gz and .zip
     *
     * return boolean True if package could be uploaded, false otherwise
     */
    function unpackPackage() {
	// Temporary folder to extract the archive into
	$tmpdir = uniqid('install_');
	$this->_extractDir = dirname($this->_localPackageName) . DS . $tmpdir;

	// Clean the paths to use for archive extraction
	$extractdir = JPath::clean($this->_extractDir);
	$archivename = JPath::clean($this->_localPackageName);

	// do the unpacking of the archive
	jimport('joomla.filesystem.archive');
	if (!JArchive::extract($archivename, $extractdir)) {
	    $this->_error = JText::_('Falied to upack package!');
	    return false;
	}

	return true;
    }


    /**
     * Method to validate the installation package of an extension
     *
     * @static
     * @param $infos the infomation of method $files list of file need to install
     * @return
     * @since 1.2.0
     */
    function validatePackage() {
	$info = pathinfo($this->_localPackageName);
	$fileNameNoExt = basename($this->_localPackageName,'.'.$info['extension']);

	$xmlParser = JFactory::getXMLParser('Simple');
	if (!$xmlParser->loadFile($this->_extractDir.DS.'update.xml')) {
	    $this->_error = JText::_('Failed to parse XML file!');
	    return false;
	}
	// a type tag could be added later to handle other updates
	$attr = $xmlParser->document->data();
	$installType = $attr['type'];

	// access children
	foreach ($xmlParser->document->children() as $child) {
	    //print $child->name().'<br />';
	    switch ($child->name()) {
		case "files":
		    $this->_files = $child->children();
		    break;
		case "forversion":
		    //if (version_compare($child->data(), VmConfig::getInstalledVersion(), '>') != 1) {
		//	$this->_error = JText::_('Your version already includes this update!');
	//		return false;
		//    }
		    break;
	    }
	}

	return true;
    }


    /**
     * Install the package
     */
    function installPackage() {
	$this->installFiles($this->_files);

	return true;
    }


    /**
     * Install files from the install package
     *
     * @param Array $fileArray Array of file objects from the XML file.
     */
    function installFiles($fileArray) {
	foreach ($fileArray as $file) {
	    $attrib = $file->attributes();

	    switch ($attrib["copy"]) {
		case "overwrite_create":
		    break;
		case "only_if_exists":
		    break;
	    }
	    
	    $filePath = $file->data();
	    $fileName = basename($filePath);
	    print $attrib["copy"].$filePath.$fileName.'<br />';
	}

	return true;
    }


    /**
     * Remove the temporary extract directory and uploaded package file.
     */
    function cleanupAfterInstall() {
	JFolder::delete($this->_extractDir);
	JFile::delete($this->_localPackageName);
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
die($xml->document->toString());
	$count = count ( $xml->document->_children );

	for($i = 0; $i < $count; $i ++) {
	    switch($xml->document->_children [$i]->_name ) {
		case "name":
		    $info ["name"] = $xml->document->_children [$i]->_data;
		    break;
		case "author":
		    $info ["author"] = $xml->document->_children [$i]->_data;
		    break;
		case  "creationdate":
		    $info ["creationdate"] = $xml->document->_children [$i]->_data;
		    break;
		case  "copyright":
		    $info ["copyright"] = $xml->document->_children [$i]->_data;
		    break;
		case  "license":
		    $info ["license"] = $xml->document->_children [$i]->_data;
		    break;
		case  "authoremail":
		    $info ["authoremail"] = $xml->document->_children [$i]->_data;
		    break;
		case  "authorurl":
		    $info ["authorurl"] = $xml->document->_children [$i]->_data;
		    break;
		case  "version":
		    $info ["version"] = $xml->document->_children [$i]->_data;
		    break;
		case  "description":
		    $info ["description"] = $xml->document->_children [$i]->_data;
		    break;
		case  "element":
		    $info ["element"] = $xml->document->_children [$i]->_data;
		    break;
		case  "is_creditcard":
		    $info ["is_creditcard"] = $xml->document->_children [$i]->_data;
		    break;
		case  "type": // used for: payment method type
		    $info ["type"] = $xml->document->_children [$i]->_data;
		    break;
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
     * Method to detect the extension type from a package directory
     *
     * @static
     * @param string $p_dir Path to package directory
     * @return mixed Extension type string or boolean false on fail
     * @since 1.2.0
     */
    function detectType($p_dir) {
	// Search the install dir for an xml file
	$files = JFolder::files( $p_dir, '\.xml$', 1, true);
	if (count($files) > 0) {
	    foreach ($files as $file) {
		$xmlDoc = JFactory::getXMLParser();
		$xmlDoc->resolveErrors(true);

		if (!$xmlDoc->loadXML($file, false, true)) {
		    // Free up memory from DOMIT parser
		    unset($xmlDoc);
		    continue;
		}
		$root = $xmlDoc->documentElement;

		if (!is_object($root) || ($root->getTagName() != "install" && $root->getTagName() != 'mosinstall')) {
		    unset($xmlDoc);
		    continue;
		}

		$type = $root->getAttribute('type');
		// Free up memory from DOMIT parser
		unset($xmlDoc);
		return $type;
	    }

	}
	else {
	    $this->setError('CANT_FIND_XML');
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
	jimport( 'joomla.filesystem.file' );
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


    /**
     * Set the last error message to the message provided
     *
     * @param string $newError New error message
     */
    function setError($newError) {
	$this->_error = $newError;
    }


    /**
     * Return the last error generated in this install class
     *
     * @return String The last error message
     */
    function getError() {
	return $this->_error;
    }
}
?>