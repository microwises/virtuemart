<?php

define( '_VALID_MOS', 1 );
define( '_JEXEC', 1 );

/**
 * Virtuemart Category SOA Connector
 *
 * Virtuemart SQLQueries SOA Connector (Provide functions execute generic SQL queries, INSERT, UPDATE, SELECT queries)
 * The return classe is a "SQLResult" 
 * 
 *
 * @package    com_vm_soa
 * @subpackage component
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  2010 Mickael Cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    $Id:$
 */

 /** loading framework **/
include_once('VM_Commons.php');

/**
 * Class SQLResult
 *
 * Class "SQLResult" 
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class SQLResult {
		public $columnsAndValues;

		//constructeur
		function __construct($columnsAndValues) {
			$this->columnsAndValues = $columnsAndValues;
		}
	}
 
 /**
 * Class SQLResult
 *
 * Class "SQLResult" 
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class columnAndValue {
		public $idx="";
		public $column="";
		public $value="";

		//constructeur
		function __construct($idx,$column,$value) {
			$this->idx = $idx;
			$this->column = $column;
			$this->value = $value;
		}
	}
 /**
 * Class SQLResult
 *
 * Class "SQLResult" with attribute : id, name, description,  image, fulliamage , parent category
 * attributes, parent produit, child id)
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	
 
	/**
    * This function get Childs of a category for a category ID
	* (expose as WS)
    * @param string The id of the category
    * @return array of Categories
   */
	function ExecuteSQLSelectQuery($params) {
	
		$SQLSelectRequest= $params;
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_execsql_select')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
	
			$SQLSelectRequest= $_SQLSelectRequest;
			
			$query = "SELECT " ;//FROM #__{vm}_category WHERE 1 ";
			$strTmp;
						
			if (is_array($params->columns->column)){
				$count = count($params->columns->column);
				for ($i = 0; $i < $count; $i++) {
					if ($i==$count-1){
						$strTmp .= $params->columns->column[$i]." ";
					}else{
						$strTmp .= $params->columns->column[$i].", ";
					}
				}
			}else {
					$count = 1;
					$strTmp .= $params->columns->column." ";
			}
			
			/*$q .= $strTmp;
			$q .= " FROM $params->table ";
			$q .= $params->whereClause;

			/*$db = new ps_DB;
			$db->setQuery($q);
			$db->query();*/
			
			$db = JFactory::getDBO();
			$query .= $strTmp;
			$query .= " FROM $params->table ";
			$query .= $params->whereClause;
			$db->setQuery($query);
			
			$rows = $db->loadAssocList();
			
			foreach ($rows as $row) {
			
				$strResult=null;
				$arrayCol;
				$strResult;
				
				if ($count == 1){
					$columnAndValue = new columnAndValue(0,$params->columns->column,$row[$params->columns->column]);
					$columnAndValueArray[] = $columnAndValue;
				
				} else {
					for ($i = 0; $i < $count; $i++) {
						$columnAndValue = new columnAndValue($i,$params->columns->column[$i],$row[$params->columns->column[$i]]);
						$columnAndValueArray[] = $columnAndValue;
						/*$arrayCol=  array( $params->columns->column[$i] =>$db->f($params->columns->column[$i]));
						$strResult .=  $params->columns->column[$i]." : ".$db->f($params->columns->column[$i])." | ";*/
					}
				}
				$SQLResult= new SQLResult($columnAndValueArray);
				$resultArray[] = $SQLResult;
				$columnAndValueArray=null;
			
			}
			
			$errMsg=  $db->getErrorMsg();
			
			if ($errMsg==null){
				return $resultArray;
			} else {
				return new SoapFault("JoomlaExecuteSQLSelectQueryFault", "cannot execute SQL Select Query  ".$q." | ERRLOG : ".$errMsg);				
			}

			
			
		}else if ($result== "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}		
	
	}
	
	/**
    * This function execute a SQL querie
	* (expose as WS)
    * @param string the SQL request
    * @return array resultSet
   */
	function ExecuteSQLQuery($params) {
	
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_execsql')==0){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){	
		
			/*$q = $params->sqlRequest;
			$db = new ps_DB;
			$db->setQuery($q);
			$db->query();*/
			
			$db = JFactory::getDBO();
			$query = $params->sqlRequest;
			$db->setQuery($query);
			
			$rows = $db->loadAssocList();
			
			
			foreach ($rows as $row) {
				$strResult=null;
				$arrayCol;
				$strResult;
				
				//$row=  $db->get_row();
				
				$i=0;
				foreach($row as $cle=>$valeur)
				{
					$columnAndValue = new columnAndValue($i,$cle,$valeur);
					$columnAndValueArray[] = $columnAndValue;
					$i++;
					//$strResult .= $cle.' : '.$valeur.' | ';
				} 
				$SQLResult= new SQLResult($columnAndValueArray);
				$resultArray[] =$SQLResult;
				$columnAndValueArray=null;
			}
			
			$errMsg=  $db->getErrorMsg();
			
			if ($errMsg==null){
				return $resultArray;
			} else {
				return new SoapFault("JoomlaExecuteSQLQueryFault", "cannot execute SQL Query  ".$params->sqlRequest." | ERRLOG : ".$errMsg);				
			}

			
		
		}else if ($result== "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}		
		
	}
	
		/**
    * This function execute a SQL insert Queries
	* (expose as WS)
    * @param string The id of the category
    * @return array of Categories
   */
	function ExecuteSQLInsertQuery($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_execsql_insert')==0){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
	
			//$SQLSelectRequest= $_SQLSelectRequest;
			
			$db = JFactory::getDBO();
			
			$type='INSERT INTO ';
			$cols=' ( ';
			$values=' VALUES ( ';
			if (is_array($params->columns->column)){
				$count = count($params->columns->column);
				for ($i = 0; $i < $count; $i++) {
					if ($i == $count-1){
						$cols   .= $db->nameQuote($params->columns->column[$i]) . '';
						$values .= $db->quote($params->values->value[$i]) . '';
					}else {
						$cols   .= $db->nameQuote($params->columns->column[$i]) . ',';
						$values .= $db->quote($params->values->value[$i]) . ',';
					}
				}
			}else {
					$cols   .= $db->nameQuote($params->columns->column) . '';
					$values .= $db->quote($params->values->value) . '';
			}
			$cols   .=' ) ';
			$values .=' ) ';
			/*
			$db = new ps_DB;
			$db->buildQuery($type,$params->table,$values,$params->whereClause);
			$result = $db->query();
			$errMsg=  $db->getErrorMsg();
			*/
			
			$query = $type;
			$query .= $db->nameQuote($params->table);;
			$query .= $cols;
			$query .= $values;
			$query .= $params->whereClause;
			$db->setQuery($query);
			$result = $db->query();
			$errMsg=  $db->getErrorMsg();
			
			$insert_id=$db->insertid();
			
			
			//return new SoapFault("JoomlaSQLInsertFault", "cannot execute INSERT into ".$params->table." | ERRLOG : ".$errMsg."INSERT OK in table : ".$params->table.' row id : '.$insert_id.' result '.$result.' query : '.$query);	
			
			if ($errMsg==null){
				$columnAndValue = new columnAndValue($insert_id,"OK","INSERT OK in table : ".$params->table.' row id : '.$insert_id);
				$columnAndValueArray[] = $columnAndValue;
				$SQLResult= new SQLResult($columnAndValueArray);
				$resultArray[] =$SQLResult;
				return $resultArray;
			}else{
				return new SoapFault("JoomlaSQLInsertFault", "cannot execute INSERT into ".$params->table." | ERRLOG : ".$errMsg);				
			}
			//$resultArray[] =new SQLResult($q);
			

		}else if ($result== "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}		
	
	}
	
	/**
    *  function ExecuteSQLUpdateQuery
	* ( expose as WS)
    * @param login/pass
    * @return true/false
    */
	function ExecuteSQLUpdateQuery($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_execsql_update')==0){
			$result = "true";
		}	
		
		//Auth OK
		if ($result == "true"){
	
			$db = JFactory::getDBO();
			
			$type='UPDATE ';
			
			$colsAndvalues=' SET  ';
			
			if (is_array($params->columns->column)){
				$count = count($params->columns->column);
				for ($i = 0; $i < $count; $i++) {
					if ($i == $count-1){
						$colsAndvalues .= $db->nameQuote($params->columns->column[$i]) . '';
						$colsAndvalues .= " = ".$db->quote($params->values->value[$i]) . '';
					}else {
						$colsAndvalues  .= $db->nameQuote($params->columns->column[$i]) . ',';
						$colsAndvalues .= " = ".$db->quote($params->values->value[$i]) . ',';
					}
				}
			}else {
					$colsAndvalues   .= $db->nameQuote($params->columns->column) . '';
					$colsAndvalues   .= " = ".$db->quote($params->values->value) . '';
			}
			$colsAndvalues   .='  ';
			
			
			$query = $type;
			$query .= $db->nameQuote($params->table);;
			$query .= $colsAndvalues;
			$query .= $params->whereClause;
			
			$db->setQuery($query);
			$result = $db->query();
			$errMsg=  $db->getErrorMsg();
			
			
			if ($errMsg==null){
				$columnAndValue = new columnAndValue($insert_id,"OK","UPDATE OK in table : ".$params->table);
				$columnAndValueArray[] = $columnAndValue;
				$SQLResult= new SQLResult($columnAndValueArray);
				$resultArray[] =$SQLResult;
				return $resultArray;
			}else{
				return new SoapFault("JoomlaSQLUpdateFault", "cannot execute UPDATE into ".$params->table." | ERRLOG : ".$errMsg);				
			}
			//$resultArray[] =new SQLResult($q);
			

		}else if ($result== "false"){
			return new SoapFault("JoomlaServerAuthFault", "Autification KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}		
	
	}
	
		
	/* SOAP SETTINGS */
	
	if ($vmConfig->get('soap_ws_sql_on')==1){

		/* SOAP SETTINGS */
		ini_set("soap.wsdl_cache_enabled", $vmConfig->get('soap_ws_sql_cache_on')); // wsdl cache settings
		$options = array('soap_version' => SOAP_1_2);
		
		
		if (empty($conf['BASESITE']) && empty($conf['URL'])){
			$server = new SoapServer(JURI::root(false).'/VM_SQLQueriesWSDL.php');
		}else if (!empty($conf['BASESITE'])){
			$server = new SoapServer('http://'.$conf['URL'].'/'.$conf['BASESITE'].'/administrator/components/com_virtuemart/services/VM_SQLQueriesWSDL.php');
		}else {
			$server = new SoapServer('http://'.$conf['URL'].'/administrator/components/com_virtuemart/services/VM_SQLQueriesWSDL.php');
		}
		

		/* Add Functions */
		$server->addFunction("ExecuteSQLQuery");
		$server->addFunction("ExecuteSQLSelectQuery");
		$server->addFunction("ExecuteSQLInsertQuery");
		$server->addFunction("ExecuteSQLUpdateQuery");
		$server->handle();
	}else{
		echoXmlMessageWSDisabled('SQL queries');
	}
?> 