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
 * @copyright  2010 Mickael Cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    $Id:$
 */

 /** loading framework **/
include_once('VM_Commons.php');

	/**
    * This function Method1
	* (expose as WS)
    * @param 
    * @return 
   */
	function Method1($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		
		//Auth OK
		if ($result == "true"){
		
			$commonReturn['returnCode'] = "Your";//$conf['version'];
			$commonReturn['message'] = "Customized";
			$commonReturn['returnData'] = "Service";
				
			return $commonReturn;

		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}		
	}
	
	/**
    * This function Method2
	* (expose as WS)
    * @param string
    * @return 
   */
	function Method2($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password);
		
		//Auth OK
		if ($result == "true"){
		
			$commonReturn['returnCode'] = "Your";//$conf['version'];
			$commonReturn['message'] = "Customized";
			$commonReturn['returnData'] = "Service 2";
				
			return $commonReturn;

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}		
	}

	
	
	
	
	
	/* SOAP SETTINGS */
	if ($vmConfig->get('soap_ws_custom_on')==1){

		/* SOAP SETTINGS */
		$cache = "0";
		if ($conf['users_cache'] == "on")$cache = "1";
		ini_set("soap.wsdl_cache_enabled", $cache); // wsdl cache settings
		
		if ($conf['soap_version'] == "SOAP_1_1"){
			$options = array('soap_version' => SOAP_1_1);
		}else {
			$options = array('soap_version' => SOAP_1_2);
		}
		
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
		echo "This Web Service (Custom) is disabled";
	}
?> 