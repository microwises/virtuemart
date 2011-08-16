<?php

define( '_VALID_MOS', 1 );
define( '_JEXEC', 1 );

/**
 * Virtuemart User SOA Connector
 *
 * Virtuemart User SOA Connector (Here you can define your own services)
 *
 * @package    com_vm_soa
 * @subpackage modules
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  2011 Mickael Cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    $Id:$
 */

 /** loading framework **/
include_once('VM_Commons.php');


/**
 * Class CommonReturn
 *
 * Class "CommonReturn" with attribute : returnCode, message, $returnData, 
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class CommonReturn {
		public $returnCode="";
		public $message="";
		public $returnData="";

		//constructeur
		/**
		 *
		 * @param String $returnCode
		 * @param String $message
		 */
		function __construct($returnCode, $message, $returnData) {
			$this->returnCode = $returnCode;
			$this->message = $message;	
			$this->returnData = $returnData;				
		}
	}	

	/**
    * This function Method1
	* (expose as WS)
    * @param 
    * @return 
   */
	function Method1($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		//Auth OK
		if ($result == "true"){
		
			$commonReturn = new CommonReturn('your customized',
											 'service',
											 'Hello '.$params->loginInfo->login);				
			return $commonReturn;

		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}		
	}
	
	/**
    * This function Method2
	* (expose as WS)
    * @param
    * @return 
   */
	function Method2($params) {
			
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		//Auth OK
		if ($result == "true"){
		
			$commonReturn = new CommonReturn('your', 'customized','service2');				
			return $commonReturn;
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}		
	}

	
	
	
	
	
	/* SOAP SETTINGS */
	if ($vmConfig->get('soap_ws_custom_on')==1){

		ini_set("soap.wsdl_cache_enabled", $vmConfig->get('soap_ws_custom_cache_on')); // wsdl cache settings
		$options = array('soap_version' => SOAP_1_2);
		
		/** SOAP SERVER **/
		if (empty($conf['BASESITE']) && empty($conf['URL'])){
			$server = new SoapServer(JURI::root(false).'/VM_CustomizedWSDL.php');
		}else if (!empty($conf['BASESITE'])){
			$server = new SoapServer('http://'.$conf['URL'].'/'.$conf['BASESITE'].'/administrator/components/com_virtuemart/services/VM_CustomizedWSDL.php');
		}else {
			$server = new SoapServer('http://'.$conf['URL'].'/administrator/components/com_virtuemart/services/VM_CustomizedWSDL.php');
		}
				
		/* Add Functions */
		$server->addFunction("Method1");
		$server->addFunction("Method2");

		$server->handle();
		
	}else{
		echoXmlMessageWSDisabled('Custom');
	}
?> 