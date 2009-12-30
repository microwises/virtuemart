<?php
/**
 * updatesMigration controller
 *
 * @package	VirtueMart
 * @subpackage updatesMigration
 * @author Max Milbers
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */
 
 defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
 
 class updatesMigrationHelper {
	
//	private $db;
   	public	$storeOwnerId = "62";
	public	$userUserName = "not found";
	public	$userName = "not found";
	public	$oldVersion = "fresh";


    function __construct(){
//		$this->db = &JFactory::getDBO();
	}
		
	function determineAlreadyInstalledVersion(){
		$this -> oldVersion = "fresh";
		$db = JFactory::getDBO();
		$db->setQuery( 'SELECT * FROM #__vm_country WHERE `country_id`="1" ');
		if($db->query() == true ) {
			$country1 = $db->loadResult();
			if(isset($country1)){
				$this -> oldVersion = "1.0";
				$db->setQuery( 'SELECT * FROM #__vm_auth_user_group WHERE `user_id`="'.$this -> storeOwnerId.'" ');
				if($db->query() == true ) {
					$authUser = $db->loadResult();
					if(isset($authUser)){
						$this -> oldVersion = "1.1";
						$db->setQuery( 'SELECT * FROM #__vm_menu_admin WHERE `id`= "10" ');
						if($db->query() == true ) {
							$menuAdmin = $db->loadResult();
							if(isset($menuAdmin)){
								$this -> oldVersion = "1.5";
							}
						}
					}
				}
			}
		}
		JError::raiseNotice(1, 'Installed Version '.$this -> oldVersion);
		return;
	}


	/**
	 * This is a general function to safely open a connection to a server,
	 * post data when needed and read the result.
	 * Tries using cURL and switches to fopen/fsockopen if cURL is not available
	 * @since VirtueMart 1.1.0
	 * @static 
	 * @param string $url
	 * @param string $postData
	 * @param array $headers
	 * @param resource $fileToSaveData
	 * @return mixed
	 */
	function handleCommunication( $url, $postData='', $headers=array(), $fileToSaveData=null ) {
		global $vmLogger;

		$urlParts = parse_url( $url );
		if ( !isset( $urlParts['port'] )) $urlParts['port'] = 80;
		if ( !isset( $urlParts['scheme'] )) $urlParts['scheme'] = 'http';

		if ( isset( $urlParts['query'] )) $urlParts['query'] = '?'.$urlParts['query'];
		if ( isset( $urlParts['path'] )) $urlParts['path'] = $urlParts['path'].vmGet($urlParts,'query');

		// Check proxy
		$proxyURL = VmConfgi::getVar(proxy_url);
		if ( trim( $proxyUrl ) != '') {
			if ( !stristr($proxyUrl, 'http')) {
				$proxyURL['host'] = $proxyUrl;
				$proxyURL['scheme'] = 'http';
			} 
			else {
				$proxyURL = parse_url($proxyUrl);
			}
		}
		else {
			$proxyURL = '';
		}

		if( function_exists( "curl_init" ) && function_exists( 'curl_exec' ) ) {

			$vmLogger->debug( 'Using the cURL library for communicating with '.$urlParts['host'] );

			$CR = curl_init();
			curl_setopt($CR, CURLOPT_URL, $url);

			// just to get sure the script doesn't die
			curl_setopt($CR, CURLOPT_TIMEOUT, 30 );
			if( !empty( $headers )) {
				// Add additional headers if provided
				curl_setopt($CR, CURLOPT_HTTPHEADER, $headers);
			}
			curl_setopt($CR, CURLOPT_FAILONERROR, true);
			if( $postData ) {
				curl_setopt($CR, CURLOPT_POSTFIELDS, $postData );
				curl_setopt($CR, CURLOPT_POST, 1);
			}
			if( is_resource($fileToSaveData)) {
				curl_setopt($CR, CURLOPT_FILE, $fileToSaveData );
			} else {
				curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);
			}
			// Do we need to set up the proxy?
			if( !empty($proxyURL) ) {
				$vmLogger->debug( 'Setting up proxy: '.$proxyURL['host'].':'.VmConfig::getVar('proxy_port') );
				//curl_setopt($CR, CURLOPT_HTTPPROXYTUNNEL, true);
				curl_setopt($CR, CURLOPT_PROXY, $proxyURL['host'] );
				curl_setopt($CR, CURLOPT_PROXYPORT, VmConfig::getVar('proxy_port') );
				// Check if the proxy needs authentication
				if ( trim(VmConfig::getVar('proxy_user')) != '') {
					$vmLogger->debug( 'Using proxy authentication!' );
					curl_setopt($CR, CURLOPT_PROXYUSERPWD, VmConfig::getVar('proxy_user').':'.VmConfig::getVar('proxy_pass') );
				}
			}

			if( $urlParts['scheme'] == 'https') {
				// No PEER certificate validation...as we don't have
				// a certificate file for it to authenticate the host www.ups.com against!
				curl_setopt($CR, CURLOPT_SSL_VERIFYPEER, 0);
				//curl_setopt($CR, CURLOPT_SSLCERT , "/usr/locale/xxxx/clientcertificate.pem");
			}
			$result = curl_exec( $CR );
			$error = curl_error( $CR );
			if( !empty( $error ) && stristr( $error, '502') && !empty( $proxyURL )) {
				$vmLogger->debug( 'Switching to NTLM authenticaton.');
				curl_setopt( $CR, CURLOPT_PROXYAUTH, CURLAUTH_NTLM );
				$result = curl_exec( $CR );
				$error = curl_error( $CR );
			}
			curl_close( $CR );

			if( !empty( $error )) {
				$vmLogger->err( $error );
				return false;
			}
			else {
				return $result;
			}
		}
		else {
			if( $postData ) {
				if( !empty( $proxyURL )) {
					// If we have something to post we need to write into a socket
					if( $proxyURL['scheme'] == 'https'){
						$protocol = 'ssl';
					}
					else {
						$protocol = 'http';
					}
					$fp = fsockopen("$protocol://".$proxyURL['host'], VmConfig::getVar('proxy_port'), $errno, $errstr, $timeout = 30);
				}
				else {
					// If we have something to post we need to write into a socket
					if( $urlParts['scheme'] == 'https'){
						$protocol = 'ssl';
					}
					else {
						$protocol = $urlParts['scheme'];
					}
					$fp = fsockopen("$protocol://".$urlParts['host'], $urlParts['port'], $errno, $errstr, $timeout = 30);
				}
			}
			else {
				if( !empty( $proxyURL )) {
					// Do a read-only fopen transaction
					$fp = fopen( $proxyURL['scheme'].'://'.$proxyURL['host'].':'.VmConfig::getVar('proxy_port'), 'rb' );
				}
				else {
					// Do a read-only fopen transaction
					$fp = fopen( $urlParts['scheme'].'://'.$urlParts['host'].':'.$urlParts['port'].$urlParts['path'], 'rb' );
				}
			}
			if(!$fp){
				//error tell us
				$vmLogger->err( "Possible server error! - $errstr ($errno)\n" );
				return false;
			}
			else {
				$vmLogger->debug( 'Connection opened to '.$urlParts['host']);
			}
			if( $postData ) {
				$vmLogger->debug('Now posting the variables.' );
				//send the server request
				if( !empty( $proxyURL )) {
					fputs($fp, "POST ".$urlParts['host'].':'.$urlParts['port'].$urlParts['path']." HTTP/1.0\r\n");
					fputs($fp, "Host: ".$proxyURL['host']."\r\n");

					if( trim( VmConfig::getVar('proxy_user') )!= '') {
						fputs($fp, "Proxy-Authorization: Basic " . base64_encode (VmConfig::getVar('proxy_user').':'.VmConfig::getVar('proxy_pass') ) . "\r\n\r\n");
					}
				}
				else {
					fputs($fp, 'POST '.$urlParts['path']." HTTP/1.0\r\n");
					fputs($fp, 'Host:'. $urlParts['host']."\r\n");
				}
				fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
				fputs($fp, "Content-length: ".strlen($postData)."\r\n");
				fputs($fp, "Connection: close\r\n\r\n");
				fputs($fp, $postData . "\r\n\r\n");
			}
			else {
				if( !empty( $proxyURL )) {
					fputs($fp, "GET ".$urlParts['host'].':'.$urlParts['port'].$urlParts['path']." HTTP/1.0\r\n");
					fputs($fp, "Host: ".$proxyURL['host']."\r\n");
					if( trim( VmConfig::getVar('proxy_user') )!= '') {
						fputs($fp, "Proxy-Authorization: Basic " . base64_encode (VmConfig::getVar('proxy_user').':'.VmConfig::getVar('proxy_pass')) . "\r\n\r\n");
					}
				}
				else {
					fputs($fp, 'GET '.$urlParts['path']." HTTP/1.0\r\n");
					fputs($fp, 'Host:'. $urlParts['host']."\r\n");
				}
			}
			// Add additional headers if provided
			foreach( $headers as $header ) {
				fputs($fp, $header."\r\n");
			}
			$data = "";
			while (!feof($fp)) {
				$data .= @fgets ($fp, 4096);
			}
			fclose( $fp );

			// If didnt get content-lenght, something is wrong, return false.
			if ( trim($data) == '' ) {
				$vmLogger->err('An error occured while communicating with the server '.$urlParts['host'].'. It didn\'t reply (correctly). Please try again later, thank you.' );
				return false;
			}
			$result = trim( $data );
			if( is_resource($fileToSaveData )) {
				fwrite($fileToSaveData, $result );
				return true;
			} else {
				return $result;
			}
		}
	}

}