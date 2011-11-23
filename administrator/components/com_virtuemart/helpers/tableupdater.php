<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @version $Id: tableupdater.php 4657 2011-11-10 12:06:03Z Milbo $
 * @package VirtueMart
 * @subpackage core
 * @author Max Milbers
 * @copyright Copyright (C) 2011 by the virtuemart team - All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL 2, see COPYRIGHT.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 * http://virtuemart.net
 */


/**
 * Class to update the tables according to the install.sql db file
 *
 * @author Milbo
 *
 */
class GenericTableUpdater extends JModel{

	public function __construct(){

		JTable::addIncludePath(JPATH_VM_ADMINISTRATOR . DS . 'tables');

		$this->_app = JFactory::getApplication();
		$this->_db = JFactory::getDBO();
		// 		$this->_oldToNew = new stdClass();
		$this->starttime = microtime(true);

		$max_execution_time = ini_get('max_execution_time');
		$jrmax_execution_time= JRequest::getInt('max_execution_time',120);

		if(!empty($jrmax_execution_time)){
			// 			vmdebug('$jrmax_execution_time',$jrmax_execution_time);
			if($max_execution_time!==$jrmax_execution_time) @ini_set( 'max_execution_time', $jrmax_execution_time );
		}

		$this->maxScriptTime = ini_get('max_execution_time')*0.90-1;	//Lets use 5% of the execution time as reserve to store the progress

		$memory_limit = ini_get('memory_limit');
		if($memory_limit<128)  @ini_set( 'memory_limit', '128M' );

		$this->maxMemoryLimit = $this->return_bytes(ini_get('memory_limit')) * 0.85;

		$config = JFactory::getConfig();
		$this->_prefix = $config->getValue('config.dbprefix');

	}

	public function portOldLanguageToNewTables($langs){

		//create language tables
		$this->createLanguageTables($langs);
		$this->portLanguageFields();
	}

	var $tables = array('categories'=>'virtuemart_category_id',
										'manufacturers'=>'virtuemart_manufacturer_id',
										'manufacturercategories'=>'virtuemart_manufacturercategories_id',
										'products'=>'virtuemart_product_id',
										'vendors'=>'virtuemart_vendor_id',
										'paymentmethods'=>'virtuemart_paymentmethod_id',
										'shipmentmethods'=>'virtuemart_shipmentmethod_id',
	);

	/**
	 *
	 *
	 * @author Max Milbers
	 * @param unknown_type $config
	 */
	public function createLanguageTables($langs=0){

		if(empty($langs)){
			$langs = VmConfig::get('active_languages');
			if(empty($langs)){
				$params = JComponentHelper::getParams('com_languages');
				$langs = (array)$params->get('site', 'en-GB');
			}
		}

		//Todo add the mb_ stuff here
		// 		vmTime('my langs <pre>'.print_r($langs,1).'</pre>');
		foreach($this->tables as $table=>$tblKey){

			$className = 'Table'.ucfirst ($table);
			if(!class_exists($className)) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.$table.'.php');
			$tableName = '#__virtuemart_'.$table;

			$langTable = $this->getTable($table);
			$translatableFields = $langTable->getTranslatableFields();
			if(empty($translatableFields)) continue;

			foreach($langs as $lang){
				// 				$lang = strtr($lang,'-','_');
				$lang = strtolower(strtr($lang,'-','_'));
				$tbl_lang = $tableName.'_'.$lang;
				$q = 'CREATE TABLE IF NOT EXISTS '.$tbl_lang.' (';
				$q .= '`'.$tblKey.'` SERIAL ,';
				foreach($translatableFields as $name){
					if(strpos($name,'name') !==false ){
						$fieldstructure = 'char(128) NOT NULL DEFAULT "" ';
					} else if(strpos($name,'meta')!==false ){
						$fieldstructure = 'char(128) NOT NULL DEFAULT "" ';
					} else if(strpos($name,'slug')!==false ){
						$fieldstructure = 'char(144) NOT NULL DEFAULT "" ';
						$slug = true;
					} else if(strpos($name,'desc')!==false || $name == 'vendor_terms_of_service'){
						$fieldstructure = 'varchar(2024) NOT NULL DEFAULT "" ';
					} else if(strpos($name,'phone')!==false) {
						$fieldstructure = 'char(24) NOT NULL DEFAULT "" ';
					} else{
						$fieldstructure = 'char(255) NOT NULL DEFAULT "" ';
					}

					$q .= '`'.$name.'` '.$fieldstructure.',';
				}
				// 				$q = substr($q,0,-1);
				$q .= 'PRIMARY KEY (`'.$tblKey.'`)';
				if($slug){
					$q .= ', UNIQUE KEY `slug` (`slug`) )';
				} else {
					$q .= ')';
				}
				$q .= ' ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT="Language '.$lang.' for '.$table.'" AUTO_INCREMENT=1 ;';
				$this->_db->setQuery($q);
				$this->_db->query();
				// 				vmdebug('checkLanguageTables',$this->_db);
			}
		}
		// 		vmTime('done creation of lang tables');
	}

	private function portLanguageFields(){

		$config = &JFactory::getConfig();
		$lang = $config->getValue('language');

		$ok = false;
		foreach($this->tables as $table=>$tblKey){
			if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
				vmWarn('language fields not copied, please rise execution time and do again');
				return false;
			}
			vmTime('$portLanguageFields $table '.$table);
			$db = JFactory::getDBO();
			$tableName = '#__virtuemart_'.$table;
			$className = 'Table'.ucfirst ($table);
			// 			if(!class_exists($className)) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.$table.'.php');
			$langTable = $this->getTable($table);
			// 			$langTable = new $className($tableName,$tblKey,$db) ;

			$query = 'SHOW COLUMNS FROM `'.$tableName.'` ';
			$this->_db->setQuery($query);
			$columns = $this->_db->loadResultArray(0);
			// 			vmdebug('$portLanguageFields contains language fields ',$columns);

			$translatableFields = $langTable->getTranslatableFields();
			$translatableFields = array_intersect($translatableFields,$columns);
			// 			if(in_array($translatableFields[0],$columns)){
			if(count($translatableFields)>1){

				$ok = true;
				//approximatly 100 products take a 1 MB
				$maxItems = $this->_getMaxItems('Language '.$table);

				$startLimit = 0;
				$i = 0;
				$continue=true;
				while($continue){

					$q = 'SELECT * FROM '.$tableName. ' LIMIT '.$startLimit.','.$maxItems;
					$this->_db->setQuery($q);
					$res = self::loadCountListContinue($q,$startLimit,$maxItems,'port Language '.$table);
					$resultList = $res[0];
					$startLimit = $res[1];
					$continue = $res[2];

					foreach($resultList as $row){

						if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
							vmWarn('language fields not copied, please rise execution time and do again');
							return false;
						}

						$db = JFactory::getDBO();
						// 						$dummy = array($tblKey=>$row[$tblKey]);
						// 						$langTable = new $className($tableName,$tblKey,$db) ;
						$langTable = $this->getTable($table);
						$langTable->bindChecknStore($row);
						$errors = $langTable->getErrors();
						if(!empty($errors)){
							foreach($errors as $error){
								$this->setError($error);
								vmError('portLanguageFields'.$error);
								vmdebug('portLanguageFields table',$langTable);
							}
							$ok = false;
							break;
						}
					}
				}

				//Okey stuff copied, now lets remove the old fields
				if($ok){
					vmdebug('I delete the columns ');
					foreach($translatableFields as $fieldname){
						if(in_array($fieldname,$columns)){
							vmdebug('I delete the column '.$tableName.' '.$fieldname);
							$this->_db->setQuery('ALTER TABLE `'.$tableName.'` DROP COLUMN `'.$fieldname.'` ');
							if(!$this->_db->query()){
								VmError('Deleting of '.$tableName.' '.$fieldname.' failed. '.$this->_db->getQuery());
							} else {
								vmdebug('I deleted the column '.$this->_db->getQuery());
							}

						}
					}
				}

			}
			vmTime('$portLanguageFields $table '.$table);
		}

	}

	public function updateMyVmTables(){

		$file = JPATH_VM_ADMINISTRATOR.DS.'install'.DS.'install.sql';
		$data = fopen($file, 'r');

		$tables = array();
		$tableDefStarted = false;
		while ($line = fgets ($data)) {
			$line = trim($line);
			if (empty($line)) continue; // Empty line

			if (strpos($line, '#') === 0) continue; // Commentline
			if (strpos($line, '--') === 0) continue; // Commentline

			if(strpos($line,'CREATE TABLE IF NOT EXISTS')!==false){
				$tableDefStarted = true;
				$fieldLines = array();
				$tableKeys = array();
				$start = strpos($line,'`');

				$tablename = trim(substr($line,$start+1,-3));
				// 				vmdebug('my $tablename ',$start,$end,$line);
			} else if($tableDefStarted && strpos($line,'KEY')!==false){

				$start = strpos($line,"`");
				$temp = substr($line,$start+1);
				$end = strpos($temp,"`");
				$keyName = substr($temp,0,$end);

				if(strrpos($line,',')==strlen($line)-1){
					$line = substr($line,0,-1);
				}
				$tableKeys[$keyName] = $line;

			} else if(strpos($line,'ENGINE')!==false){
				$tableDefStarted = false;

				$start = strpos($line,"COMMENT='");
				$temp = substr($line,$start+9);
				$end = strpos($temp,"'");
				$comment = substr($temp,0,$end);

				$tables[$tablename] = array($fieldLines, $tableKeys,$comment);
			} else if($tableDefStarted){

				$start = strpos($line,"`");
				$temp = substr($line,$start+1);
				$end = strpos($temp,"`");
				$keyName = substr($temp,0,$end);

				$line = trim(substr($line,$end+2));
				if(strrpos($line,',')==strlen($line)-1){
					$line = substr($line,0,-1);
				}

				$fieldLines[$keyName] = $line;
			}
		}

		// 	vmdebug('Parsed tables',$tables); //return;
		$this->_db->setQuery('SHOW TABLES LIKE "%_virtuemart_%"');
		if (!$existingtables = $this->_db->loadResultArray()) {
			$this->setError = $this->_db->getErrorMsg();
			return false;
		}


		$demandedTables = array();
		//TODO ignore admin menu table
		foreach ($tables as $tablename => $table){
			$tablename = str_replace('#__',$this->_prefix,$tablename);
			$demandedTables[] = $tablename;
			if(in_array($tablename,$existingtables)){
				$this -> compareUpdateTable($tablename,$table);
				// 				unset($todelete[$tablename]);
			} else {
				$this->createTable($tablename,$table);
			}
		}

		$tablesWithLang = array_keys($this->tables); //('categories','manufacturercategories','manufacturers','paymentmethods','shipmentmethods','products','vendors');

		$alangs = VmConfig::get('active_languages');
		if(empty($alangs)) $alangs = array(VmConfig::setdbLanguageTag());
		foreach($alangs as $lang){
			foreach($tablesWithLang as $tablewithlang){
				$demandedTables[] = $this->_prefix.'virtuemart_'.$tablewithlang.'_'.$lang;
			}
		}
		$demandedTables[] = $this->_prefix.'virtuemart_configs';

		$todelete = array();
		foreach ($existingtables as $tablename){
			if(!in_array($tablename,$demandedTables) and strpos($tablename,'_plg_')===false){
				$todelete[] = $tablename;
			}
		}
		$this->dropTables($todelete);

	}


	public function createTable($tablename,$table){

		$q = 'CREATE TABLE IF NOT EXISTS `'.$tablename.'` (
				';
		foreach($table[0] as $fieldname => $alterCommand){
			$q .= '`'.$fieldname.'` '.$alterCommand.'
			';
		}

		foreach($table[1] as $name => $value){
			$q .= '`'.$name.'` '.$value.',
						';
		}
		$q = substr($q,0,-1);

		$q = ") ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='".$table[3]."' AUTO_INCREMENT=1 ;";

		$this->_db->setQuery($query);
		if(!$this->_db->query()){
			$this->_app->enqueueMessage('createTable ERROR :'.$this->_db->getErrorMsg() );
		}
		$this->_app->enqueueMessage($q);
	}

	public function dropTables($todelete){
		if(empty($todelete)) return;
		$q = 'DROP ';// .implode(',',$todelete);
		foreach($todelete as $tablename){
			$tablename = str_replace('#__',$this->_prefix,$tablename);
			$q .= $tablename.', ';
		}
		$q = substr($q,0,-1);

		// 		$this->_db->setQuery($q);
		// 		if(!$this->_db->query()){
		// 			$this->_app->enqueueMessage('dropTables ERROR :'.$this->_db->getErrorMsg() );
		// 		}
		$this->_app->enqueueMessage($q);
	}

	public function compareUpdateTable($tablename,$table){

		if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
			vmWarn('compareUpdateTable not finished, please rise execution time and update tables again');
			return false;
		}
		$this->alterColumns($tablename,$table[0]);
		$this->alterKey($tablename,$table[1]);

	}

	private function alterKey($tablename,$keys){

		if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
			vmWarn('compareUpdateTable alterKey not finished, please rise execution time and update tables again');
			return false;
		}
		$demandFieldNames = array();
		foreach($keys as $i=>$line){
			$demandedFieldNames[] = $i;
		}
		// 		vmdebug('                $demandedFieldNames ' ,$demandedFieldNames);

		$query = "SHOW INDEXES  FROM `".$tablename."` ";	//SHOW {INDEX | INDEXES | KEYS}
		$this->_db->setQuery($query);
		if(!$eKeys = $this->_db->loadObjectList() ){
			$this->_app->enqueueMessage('alterKey show index:'.$this->_db->getErrorMsg() );
		} else {
			$eKeyNames= $this->_db->loadResultArray(2);
		}

		$dropped = 0;
		foreach($eKeyNames as $i => $name){
			$query = '';
			if(!in_array($name, $demandedFieldNames)){

				if(strpos($eKeys[$i]->Key_name,'PRIMARY')!==false ||  $name==='virtuemart_order_userinfo_id'){
					// 					$query = 'ALTER TABLE `'.$tablename.'` DROP INDEX `'.$name.'` ';
				} else {
					$query = 'ALTER TABLE `'.$tablename.'` DROP INDEX `'.$name.'` ';
					vmdebug('DROP $eKeyNames '.$name);
				}

				if(!empty($query)){
					$this->_db->setQuery($query);
					if(!$this->_db->query()){
						$this->_app->enqueueMessage('alterTable DROP '.$tablename.'.'.$name.' :'.$this->_db->getErrorMsg() );
					} else {
						$dropped++;
					}
				}

			}
		}

		$query = "SHOW INDEXES  FROM `".$tablename."` ";	//SHOW {INDEX | INDEXES | KEYS}
		$this->_db->setQuery($query);
		if(!$eKeys = $this->_db->loadObjectList() ){
			$this->_app->enqueueMessage('alterKey show index:'.$this->_db->getErrorMsg() );
		} else {
			$eKeyNames= $this->_db->loadResultArray(2);
		}

		$showThem = false;
		foreach($keys as $name =>$value){

			$query = '';
			$action = '';

			if(in_array($name, $eKeyNames)){

				$key=array_search($name, $eKeyNames);
				// 				vmdebug('hm key id '.$key);
				$oldColumn = $this->reCreateKeyByTableAttributes($eKeys[$key]);

				$compare = strcasecmp( $oldColumn, $value);

				if (!empty($compare)) {
					$showThem = true;
					vmdebug('$oldColumn '.$oldColumn.' $value '.$value);
					if(strpos($value,'PRIMARY')!==false){
						$dropit = "DROP PRIMARY KEY , ";
						$query = "ALTER TABLE `".$tablename."` ".$dropit." ADD PRIMARY KEY (`".$name."`);" ;
					} else {
						if(strpos($value,'KEY')) $type = 'KEY'; else $type = 'INDEX';
						$query = "ALTER TABLE `".$tablename."` DROP  ".$type." `".$name."` , ADD ".$value ;
						$action = 'ALTER';
					}
				}
			} else {
				if(strpos($value,'PRIMARY')===false){
					// 					vmdebug('ADD $eKeyNames '.$name ,$eKeyNames);
					$query = "ALTER TABLE `".$tablename."` ADD ".$value ;
					$action = 'ADD';
				}

			}

			if(!empty($query)){
				$this->_db->setQuery($query);
				if(!$this->_db->query()){
					$this->_app = JFactory::getApplication();
					$this->_app->enqueueMessage('alterKey '.$action.' INDEX '.$name.': '.$this->_db->getErrorMsg() );
				}
			}
		}

		// 		if($showThem)vmdebug('$eKeys  ',$eKeys);
	}

	function reCreateKeyByTableAttributes($keyAttribs){

		$oldkey ='';


		if(strpos($keyAttribs->Key_name,'PRIMARY')!==false){
			$oldkey = 'PRIMARY KEY (`'.$keyAttribs->Column_name.'`)';
		} else {
			$oldkey = 'KEY `'.$keyAttribs->Key_name.'` (`'.$keyAttribs->Column_name.'`)';
		}

		// 		if(empty($keyAttribs->Cardinality)){
		// 			vmdebug('Cardinality : '.$keyAttribs->Cardinality.' '.$oldkey);
		// 		}

		return $oldkey;
	}

	/**
	 * @author Max Milbers
	 * @param unknown_type $tablename
	 * @param unknown_type $fields
	 * @param unknown_type $command
	 */
	private function alterColumns($tablename,$fields){

		$after ='';
		$dropped = 0;
		$altered = 0;
		$added = 0;
		$this->_app = JFactory::getApplication();

		$demandFieldNames = array();
		foreach($fields as $i=>$line){
			$demandFieldNames[] = $i;
		}

		$query = 'SHOW FULL COLUMNS  FROM `'.$tablename.'` ';
		$this->_db->setQuery($query);
		$fullColumns = $this->_db->loadObjectList();
		$columns = $this->_db->loadResultArray(0);

		foreach($columns as $fieldname){

			if(!in_array($fieldname, $demandFieldNames)){
				$query = 'ALTER TABLE `'.$tablename.'` DROP COLUMN `'.$fieldname.'` ';
				$action = 'DROP';
				$dropped++;

				$this->_db->setQuery($query);
				if(!$this->_db->query()){
					$this->_app->enqueueMessage('alterTable '.$action.' '.$tablename.'.'.$fieldname.' :'.$this->_db->getErrorMsg() );
				}
			}
		}

		foreach($fields as $fieldname => $alterCommand){
			$query='';
			$action = '';
			// 			vmdebug('$fieldname',$fieldname,$alterCommand);
			if(empty($alterCommand)){
				vmdebug('empty alter command '.$fieldname);
				continue;
			}
			else if(in_array($fieldname,$columns)){

				$key=array_search($fieldname, $columns);
				$oldColumn = $this->reCreateColumnByTableAttributes($fullColumns[$key]);

				$compare = strcasecmp( $oldColumn, $alterCommand);

				if (!empty($compare)) {
					$query = 'ALTER TABLE `'.$tablename.'` CHANGE COLUMN `'.$fieldname.'` `'.$fieldname.'` '.$alterCommand;
					$action = 'CHANGE';
					$altered++;
					// 				    vmdebug('$fullColumns',$fullColumns[$key]);
					// 				    vmdebug('Alter field ',$oldColumn,$alterCommand,$compare);
				}
			}
			else {
				$query = 'ALTER TABLE `'.$tablename.'` ADD '.$fieldname.' '.$alterCommand.' '.$after;
				$action = 'ADD';
				$added++;
			}
			if (!empty($query)) {
				$this->_db->setQuery($query);
				if(!$this->_db->query()){
					$this->_app->enqueueMessage('alterTable '.$action.' '.$tablename.'.'.$fieldname.' :'.$this->_db->getErrorMsg() );
				}
				$after = 'AFTER '.$fieldname;
			}
		}

		$this->_app->enqueueMessage('Tablename '.$tablename.' dropped: '.$dropped.' altered: '.$altered.' added: '.$added);

		return true;

	}

	private function reCreateColumnByTableAttributes($fullColumn){

		$oldColumn = $fullColumn->Type;

		if($this->notnull($fullColumn->Null)){

			// 			if(empty($fullColumn->Default)){
			// 				$default = $fullColumn->Extra;
			// 			} else {
			// 				$default = $fullColumn->Default;
			// 			}
			$oldColumn .= $this->notnull($fullColumn->Null).$this->getdefault($fullColumn->Default);
		}
		$oldColumn .= $this->primarykey($fullColumn->Key).$this->formatComment($fullColumn->Comment);

		return $oldColumn;
	}
	// 	$oldColumn=$fullColumns[$key]->Type.  .$this->primarykey($fullColumns[$key]->Key).$this->formatComment($fullColumns[$key]->Comment);

	private function formatComment($comment){
		if(!empty($comment)){
			return ' COMMENT \''.$comment.'\'';
		} else {
			return '';
		}

	}

	private function notnull($string){
		if ($string=='NO') {
			return  ' NOT NULL';
		} else {
			return '';
		}
	}

	private function primarykey($string){
		if ($string=='PRI') {
			return  ' AUTO_INCREMENT';
		} else {
			return '';
		}
	}

	private function getdefault($string){
		if (isset($string)) {
			return  " DEFAULT '".$string."'";
		} else {
			return '';
		}
	}

	private function return_bytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}

	private function _getMaxItems($name){

		$maxItems = 50;
		$freeRam =  ($this->maxMemoryLimit - memory_get_usage(true))/(1024 * 1024) ;
		$maxItems = (int)$freeRam * 100;
		if($maxItems<=0){
			$maxItems = 50;
			vmWarn('Your system is low on RAM! Limit set: '.$this->maxMemoryLimit.' used '.memory_get_usage(true)/(1024 * 1024).' MB and php.ini '.ini_get('memory_limit'));
		}
		vmdebug('Migrating '.$name.', free ram left '.$freeRam.' so limit chunk to '.$maxItems);
		return $maxItems;
	}

	function loadCountListContinue($q,$startLimit,$maxItems,$msg){

		$continue = true;
		$this->_db->setQuery($q);
		if(!$this->_db->query()){
			vmError($msg.' db error '. $this->_db->getErrorMsg());
			vmError($msg.' db error '. $this->_db->getQuery());
			$entries = array();
			$continue = false;
		} else {
			$entries = $this->_db->loadAssocList();
			$count = count($entries);
			vmInfo($msg. ' found '.$count.' vm1 entries for migration ');
			$startLimit += $maxItems;
			if($count<$maxItems){
				$continue = false;
			}
		}

		return array($entries,$startLimit,$continue);
	}
}


