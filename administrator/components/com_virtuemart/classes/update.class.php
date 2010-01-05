<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: update.class.php 1755 2009-05-01 22:45:17Z rolandd $
* @package VirtueMart
* @subpackage core
* @copyright Copyright (C) 2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*
*/
require_once( CLASSPATH . 'connectionTools.class.php');

/**
 * Updater Class, handles Update Checks, Patch Package Downloads+Extraction and Update Installation 
 * @author soeren
 * @since 1.1.0
 */
class vmUpdate {
	/**
	 * Checks the VirtueMart Server for the latest available Version of VirtueMart
	 *
	 * @return string Example: 1.1.2
	 */
	function checkLatestVersion() {
		if( !empty($_SESSION['vmLatestVersion'])) {
			return $_SESSION['vmLatestVersion'];
		}
		$VMVERSION =& new vmVersion();
		$url = "http://virtuemart.orgindex2.php?option=com_versions&catid=1&myVersion={$VMVERSION->RELEASE}&task=latestversionastext&j=".(vmIsJoomla('1.5')?'1.5':'1.0');
		$result = VmConnection::handleCommunication($url);
		if( $result !== false ) {
			// Cache the result for later use
			$_SESSION['vmLatestVersion'] = $result;
		}
		return $result; 
	}
	/**
	 * Function to store the matching patch package for the currently installed VM version to the cache path
	 *
	 * @param array $d
	 * @return boolean
	 */
	function getPatchPackage( &$d) {
		global $vm_mainframe, $vmLogger, $mosConfig_cachepath;
		
		$allowed_extensions = array('gz', 'zip');
		
		if( empty($_FILES['uploaded_package']['tmp_name'])) {
			// retrieve the latest version number from virtuemart.net
			require_once( ADMINPATH.'version.php');
			$VMVERSION =& new vmVersion();
			// This URL should return a string - the direct URL to the matching patch package
			$url = "http://virtuemart.org/index2.php?option=com_versions&catid=1&myVersion={$VMVERSION->RELEASE}&task=listpatchpackages&j=".(vmIsJoomla('1.5')?'1.5':'1.0');
			$result = VmConnection::handleCommunication($url);
			if( !empty( $result )
			 	&& (strncmp('http://dev.virtuemart.net', $result, 25)===0 || strncmp('http://virtuemart.org', $result, 21)===0)
			 ) {
				$filename = basename( $result );
				$doc_id_pos = strpos($filename,'?');
				if( $doc_id_pos > 0 ) {
					$filename = substr($filename, 0, $doc_id_pos);
				}
				// Was the package already downloaded?
				if( file_exists( $mosConfig_cachepath.'/'.$filename)) {
					$vmLogger->info( JText::_('VM_UPDATE_PACKAGE_EXISTS').' '.$mosConfig_cachepath.'/'.$filename);
	
				} else {
					// If not, store it on this server
					$patch_package = VmConnection::handleCommunication($result);
					if( !file_put_contents($mosConfig_cachepath.'/'.$filename, $patch_package )) {
						$vmLogger->err( JText::_('VM_UPDATE_ERR_STORE_FAILED') );
						return false;
					}
				}
				// cache the location of the stored package file
				$_SESSION['vm_updatepackage'] = $mosConfig_cachepath.'/'.$filename;
			} else {
				$vmLogger->err( JText::_('VM_UPDATE_ERR_RETRIEVE_FAILED') );
				return false;
			}
			if( vmIsXHR() ) {
				$vm_mainframe->addResponseScript('parent.loadPage("'.$GLOBALS['sess']->url($_SERVER['PHP_SELF'].'?page=admin.update_preview', false, false).'");');
			}
			return true;
		} else {
			// make sure the file name is safe for storage.
			$filename = vmSafeFileName($_FILES['uploaded_package']['name']);
			$fileinfo = pathinfo( $filename );
			if( !in_array( strtolower($fileinfo['extension']), $allowed_extensions )) {
				$vmLogger->err( 'An invalid patch package extension was detected. Allowed Types: '.implode(', ', $allowed_extensions ));
				return false;
			}
			// Handle the uploaded package file- the integrity validation is done in another function
			if( move_uploaded_file( $_FILES['uploaded_package']['tmp_name'], $mosConfig_cachepath.'/'.$filename )) {
				$_SESSION['vm_updatepackage'] = $mosConfig_cachepath.'/'.$filename;
				if( vmIsXHR() ) {
					$vm_mainframe->addResponseScript('parent.loadPage("'.$GLOBALS['sess']->url($_SERVER['PHP_SELF'].'?page=admin.update_preview', false, false).'");');
				}
			} else {
				$vmLogger->err( 'Failed to store the uploaded patch package file.');
				return false;
			}
		}
	}
	function &getPatchContents( $updatepackage ) {
		global $vmLogger, $mosConfig_absolute_path;
		
		
		$extractdir = vmUpdate::getPackageDir( $updatepackage);
		$update_manifest = $extractdir.'/update.xml';
		
		$result = true;
		if( !file_exists($update_manifest)) {
			jimport('joomla.filesystem.archive');
			if( !JArchive::extract($updatepackage, $extractdir )) {
				JError::raiseNotice(JText::_('VM_UPDATE_ERR_EXTRACT_FAILED')." ".$extractdir,1);
				$result= false;return $result;
			}

		}
		
		$fileArr = array();		
		$queryArr = array();
		$result = true;
		
		// Can we use the PHP5 SimpleXML Extension ?
		if( function_exists('simplexml_load_file')) {
			$xml = @simplexml_load_file($update_manifest);
			if( $xml === false ) {
				$vmLogger->err( JText::_('VM_UPDATE_ERR_PARSE_FAILED') );
				return false;
			}
			
 			$toversion = (string)$xml->toversion;
 			$forversion = (string)$xml->forversion;
 			$description = (string)$xml->description;
 			$releasedate = (string)$xml->releasedate;
 			
			foreach( $xml->files->file as $file ) {
				if( file_exists($extractdir.'/'.$file )) {
					$fileArr[] = array('filename' => (string)$file,
												'copy_policy' => (string)@$file['copy']
										);
				} else {
					$vmLogger->err( sprintf(JText::_('VM_UPDATE_ERR_FILE_MISSING'),$file) );
					$result = false;
				}
			}
			if( $result === false ) {
				return $result;
			}
			if( !empty( $xml->queries->query ) && is_array($xml->queries->query) )
			foreach( $xml->queries->query as $query ) {
				$queryArr[] = (string)$query;
			}
		} else {
			// Use the SimpleXML Equivalent
			require_once( CLASSPATH. 'simplexml.php' );
			$xml = new vmSimpleXML();
 			$result = $xml->loadFile($update_manifest);
		
			if( $result === false ) {
				$vmLogger->err( JText::_('VM_UPDATE_ERR_PARSE_FAILED') );
				return false;
			}
			$result = true;
 			$xml = $xml->document;
 			
 			$toversion = $xml->toversion[0]->data();
 			$forversion = $xml->forversion[0]->data();
 			$description = $xml->description[0]->data();
 			$releasedate = $xml->releasedate[0]->data();
 			
			foreach( $xml->files[0]->file as $file ) {
				if( file_exists($extractdir.'/'.$file->data() )) {
					$fileArr[] = array('filename' => $file->data(),
													'copy_policy' => $file->attributes('copy')
												);
				} else {
					$vmLogger->err( sprintf(JText::_('VM_UPDATE_ERR_FILE_MISSING'),$file) );
					$result = false;
				}
			}
			if( $result === false ) {
				return $result;
			}
			if( !empty( $xml->queries[0]->query ) && is_object($xml->queries[0]->query) ) {
				foreach( $xml->queries[0]->query as $query ) {
					$queryArr[] = $query->data();
				}
			}
		}
		$returnArr['toversion'] = $toversion;
		$returnArr['forversion'] = $forversion;
		$returnArr['description'] = $description;
		$returnArr['releasedate'] = $releasedate;
		$returnArr['fileArr'] =& $fileArr;
		$returnArr['queryArr'] =& $queryArr;
		return $returnArr;
	}
	/**
	 * Applies the Patch Package
	 *
	 * @param array $d
	 * @return boolean
	 */
	function applyPatch( &$d ) {
		global $vm_mainframe, $vmLogger, $mosConfig_absolute_path, $db, $sess;
		
		$updatepackage = vmget($_SESSION,'vm_updatepackage');
		if( empty( $updatepackage ) ) {
			$vmLogger->err( JText::_('VM_UPDATE_ERR_DOWNLOAD') );
			return false;
		}
		$patchdir = vmUpdate::getPackageDir($updatepackage);
		$packageContents = vmUpdate::getPatchContents($updatepackage);
		
		if( !vmUpdate::verifyPackage( $packageContents ) ) {
			return false;
		}
		$errors = 0;
		foreach( $packageContents['fileArr'] as $fileentry ) {
			$file = $fileentry['filename'];
			$patch_file = $patchdir.'/'.$file;
			$orig_file = $mosConfig_absolute_path.'/'.$file;
			
		  	if( file_exists($orig_file)) {
		  		if( !is_writable($orig_file ) && !@chmod($orig_file, 0644 ) ) {
		  			$vmLogger->err( sprintf(JText::_('VM_UPDATE_ERR_FILE_UNWRITABLE'),$mosConfig_absolute_path.'/'.$file) );
		  			$errors++;
		  		}
		  	} else {
		  		if( $fileentry['copy_policy'] == 'only_if_exists') {
		  			continue;
		  		}
		  		$dirname =  is_dir($patch_file) ? $orig_file : dirname($orig_file);
		  		if( (is_dir($patch_file) || !file_exists($dirname)) ) {  					
		  			if( !vmUpdate::mkdirR($dirname, 0755 )) {
		  				$vmLogger->err( sprintf(JText::_('VM_UPDATE_ERR_DIR_UNWRITABLE'),$dirname) );
		  				$errors++;
		  			}
		  		} elseif( !is_writable($mosConfig_absolute_path.'/'.dirname($file) ) && !@chmod($mosConfig_absolute_path.'/'.dirname($file), 0755) ) {
		  			$vmLogger->err( sprintf(JText::_('VM_UPDATE_ERR_DIR_UNWRITABLE'),$mosConfig_absolute_path.'/'.$file) );
		  			$errors++;
		  		}
		  	}
		}
	  	if( $errors > 0 ) {
	  		return false;
  		}
  		foreach( $packageContents['fileArr'] as $fileentry ) {
  			$file = $fileentry['filename'];
  			$patch_file = $patchdir.'/'.$file;
  			$orig_file = $mosConfig_absolute_path.'/'.$file;
  			
			if( !file_exists($orig_file) && $fileentry['copy_policy'] == 'only_if_exists') {
		  		continue;
		  	}
		  		
  			if( (is_dir($patch_file) || !file_exists(dirname($orig_file))) ) {
  				$dirname =  is_dir($patch_file) ? $orig_file : dirname($orig_file);
  				if( !vmUpdate::mkdirR($dirname, 755 )) {
  					$vmLogger->crit( 'Failed to create a necessary directory' );
  				}
  			}
  			elseif( !@copy( $patch_file, $orig_file ) ) {
  				$vmLogger->crit( sprintf(JText::_('VM_UPDATE_ERR_OVERWRITE_FAILED'),$file) );
  				return false;  				
  			} else {
  				$vmLogger->debug( sprintf(JText::_('VM_UPDATE_FILE_OVERWROTE'),$file) );
  			}
  		}
  		foreach( $packageContents['queryArr'] as $query ) {
  			if( $db->query($query) === false ) {
  				$vmLogger->crit( sprintf(JText::_('VM_UPDATE_ERR_QUERY_FAILED'),$query) );
  			} else {
  				$vmLogger->debug( sprintf(JText::_('VM_UPDATE_QUERY_EXECUTED'),$query) );
  			}
  		}
  		
  		$db->query('UPDATE `#__components` SET `params` = \'RELEASE='.$packageContents['toversion'].'\nDEV_STATUS=stable\' WHERE `name` = \'virtuemart_version\'');
  		
  		$_SESSION['vmupdatemessage'] = sprintf(JText::_('VM_UPDATE_SUCCESS'),$packageContents['forversion'],$packageContents['toversion']);
  		
  		// Delete the patch package file
  		vmUpdate::removePackageFile($d);
  		
		if( vmIsXHR() ) {
			$vm_mainframe->addResponseScript('parent.loadPage("'.$GLOBALS['sess']->url($_SERVER['PHP_SELF'].'?page=admin.update_result', false, false).'");');
		} else {
	  		// Redirect to the Result Page and display the Update Message there
			vmRedirect($sess->url($_SERVER['PHP_SELF'].'?page=admin.update_result', false, false) );
		}
	}
	/**
	 * Verifies the integrity of the Patch Package.
	 *
	 * @param array $packageContents
	 * @return boolean
	 */
	function verifyPackage( &$packageContents ) {
		global  $vmLogger;
		
		if( $packageContents === false ) {
			return false;
		}
		require_once( ADMINPATH.'version.php');
		$VMVERSION = new vmVersion();
		
		if( $VMVERSION->RELEASE != $packageContents['forversion'] ) {
			$vmLogger->err( JText::_('VM_UPDATE_ERR_NOTMATCHING') );
			return false;
		}
		
		return true;
	}
	/**
	 * Deletes the Patch Package File and its extracted contents
	 *
	 * @param array $d
	 * @return boolean
	 */
	function removePackageFile( &$d ) {
		global $vm_mainframe, $vmLogger;
		$packageFile = vmGet( $_SESSION,'vm_updatepackage');
		if( empty( $packageFile ) || !file_exists($packageFile)) {
			return true;
		}
		$packageDir = vmUpdate::getPackageDir($packageFile);
		if( !empty( $packageDir )) {
			$result = vmRemoveDirectoryR( $packageDir );
			if( !$result ) {
				$vmLogger->err( 'Failed to remove the Directory of the Patch Package');
			}
			$result = @unlink( $packageFile );
			if( !$result ) {
				$vmLogger->err( 'Failed to remove the Patch Package File');
				return false;
			}
			unset( $_SESSION['vm_updatepackage']);
			unset( $_SESSION['vmLatestVersion']);
		}
		if( vmIsXHR() ) {
			$vm_mainframe->addResponseScript('parent.loadPage("'.$GLOBALS['sess']->url($_SERVER['PHP_SELF'].'?page=admin.update_check', false, false).'");');
		}
		return true;
	}
	/**
	 * Creates the directory name where the patch package will be extracted to
	 *
	 * @param string $updatepackage
	 * @return string
	 */
	function getPackageDir( $updatepackage ) {
		$fileinfo = pathinfo($updatepackage);
		$extension = strtolower($fileinfo['extension']);
		if( $extension == 'gz' ) $extension = 'tar.gz';
		return dirname($updatepackage).'/'. str_replace('.' . $extension, '', basename($updatepackage) );
	}
	/**
	 * Shows the Step-1-2-3 Bar at the Top of the Updater
	 *
	 * @param int $step
	 */
	function stepBar( $step ) {
		
		
		$steps = array( 1 => JText::_('VM_UPDATE_STEP_1'),
									2 => JText::_('VM_UPDATE_STEP_2'),
									3 => JText::_('VM_UPDATE_STEP_3') );
		$num_of_steps = count( $steps );
		$cellwidth = intval(100 / $num_of_steps);
		
		echo '<table width="60%" align="center" border="0" cellspacing="10" cellpadding="7"><tr>';
		
		foreach( $steps as $num => $label ) {
			if( $step == $num ) {
				$style='background-color:#3333FF;color:white;font-weight:bold;';
			} elseif( $num > $step ) {
				$style='background-color:#E6E6FA;';
			} else {
				$style='background-color:#00CC33;';
			}
			echo '<td width="'.$cellwidth.'%" style="'.$style.'border:1px solid gray;">'.$num.'<br />'.$label.'</td>';			
		}
		echo '</tr></table>';
	}
	/**
	 * Recursively creates a new directory
	 *
	 * @param string $path
	 * @param octal $rights
	 * @return boolean
	 */
	function mkdirR($path, $rights = 0777) {
		
		$folder_path = array(strstr($path, '.') ? dirname($path) : $path);
	
		while(!@is_dir(dirname(end($folder_path)))
			&& dirname(end($folder_path)) != '/'
			&& dirname(end($folder_path)) != '.'
			&& dirname(end($folder_path)) != '') {
			array_push($folder_path, dirname(end($folder_path)));
		}
	
		while($parent_folder_path = array_pop($folder_path)) {
			@mkdir($parent_folder_path, $rights);
		}
		@mkdir( $path );
		return is_dir( $path );
	}
}
?>
