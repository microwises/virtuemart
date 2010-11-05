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
				$db->setQuery( 'SELECT * FROM #__vm_users WHERE `user_id`="'.$this -> storeOwnerId.'" ');
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

}