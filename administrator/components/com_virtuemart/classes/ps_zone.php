<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/


class ps_zone {
  
  /*
  ** VALIDATION FUNCTIONS
  **
  */
  function validate_add(&$d) {
    
    $db = new ps_DB;
   
    $q = "SELECT * from #__{vm}_zone_shipping WHERE zone_name='" . $d["zone_name"] . "'";
    $db->query($q);
    if ($db->next_record()) {
      $d["error"] = "ERROR:  This Zone Name already exists. Please select another name.";
      return False;
    } 
    if (!$d["zone_cost"]) {
      $d["error"] = "ERROR:  You must enter a per item zone cost. For Free shipping enter 0.00";
      return False;
    }
    if (!$d["zone_limit"]) {
      $d["error"] = "ERROR:  You must either enter a zone limit OR a 0.00 for no limit ";
      return False;
    }

    if ($d["zone_limit"] > "0") {
      if($d["zone_cost"] > $d["zone_limit"]) {
      $d["error"] = "ERROR:  The cost can not be higher than the limit.\n";
      $d["error"] .= "If you wish to have no limit, enter 0.00 as your limit.";
      return False;
    }
   }
    if (!$d["zone_name"]) {
      $d["error"] = "ERROR:  You must enter a Zone Name.";
      return False;
    }
    return True;    
  }
  
  function validate_delete($d) {
    
    if (!$d["zone_id"]) {
      $d["error"] = "ERROR:  Please select a zone to delete.";
      return False;
    }
    else {
      return True;
    }
  }
  
  function validate_update(&$d) {
    $db = new ps_DB;

    if (!$d["zone_id"]) {
      $d["error"] = "ERROR:  You must select a zone to update.";
      return False;
    }
    if (!$d["zone_cost"]) {
      $d["error"] = "ERROR:  You must enter a per item zone cost.\n";
      $d["error"] .= "For free shipping, enter 0.00.";
      return False;
    }

    if (!$d["zone_limit"]) {
      $d["error"] = "ERROR:  You must either enter a zone limit OR a 0.00 for no limit ";
      return False;
    }

    if ($d["zone_limit"] > "0") {
      if($d["zone_cost"] > $d["zone_limit"]) {
      $d["error"] = "ERROR:  The cost can not be higher than the limit.\n";
      $d["error"] .= "If you wish to have no limit, enter 0 as your limit.";
      return False;
    }
   }

    if (!$d["zone_name"]) {
      $d["error"] = "ERROR:  You must enter a Zone Name.";
      return False;
    }
    return True;
  }
    function validate_assign(&$d) {

    if (!$d["zone_id"]) {
      $d["error"] = "ERROR:  You must select a zone.";
      return False;
    }
    if (!$d["country_id"]) {
      $d["error"] = "ERROR:  You must select a country.";
      return False;
    }
    return True;
  }
  
  /**************************************************************************
   * name: add()
   * created by: mike
   * description: creates a new zone rate record
   * parameters:
   * returns:
   **************************************************************************/
  function add(&$d) {
    $db = new ps_DB;
    
    $timestamp = time();
    
    if (!$this->validate_add($d)) {
      return False;
    }
    
    foreach ($d as $key => $value)
        $d[$key] = addslashes($value);
        
    $q = "INSERT INTO #__{vm}_zone_shipping (zone_name, zone_cost, ";
    $q .= "zone_limit, zone_description, zone_tax_rate) VALUES ('";
    $q .= $d["zone_name"] . "','";
    $q .= $d["zone_cost"] . "','";
    $q .= $d["zone_limit"] . "','";
    $q .= $d["zone_description"] . "','";
    $q .= $d["zone_tax_rate"] . "')";
    $db->query($q);
    $db->next_record();
    $_REQUEST['zone_id'] = $db->last_insert_id();
    return True;

  }
  
  /**************************************************************************
   * name: update()
   * created by: mike
   * description: updates function information
   * parameters:
   * returns:
   **************************************************************************/
  function update(&$d) {
    $db = new ps_DB; 

    $timestamp = time();

    if (!$this->validate_update($d)) {
      return False;	
    }
    
    foreach ($d as $key => $value)
        if( !is_array($value))
          $d[$key] = addslashes($value);
        
        
    $q = "UPDATE #__{vm}_zone_shipping SET ";
    $q .= "zone_name='" . $d["zone_name"];
    $q .= "',zone_cost='" . $d["zone_cost"];
    $q .= "',zone_limit='" . $d["zone_limit"];
    $q .= "',zone_description='" . $d["zone_description"];
    $q .= "',zone_tax_rate='" . $d["zone_tax_rate"];
    $q .= "' WHERE zone_id='" . $d["zone_id"] . "'";
    $db->query($q);
    $db->next_record();
    return True;
  }

	/**
	* Controller for Deleting Records.
	*/
	function delete(&$d) {
	
		if (!$this->validate_delete($d)) {
		  return False;
		}
		$record_id = $d["zone_id"];
		
		if( is_array( $record_id)) {
			foreach( $record_id as $record) {
				if( !$this->delete_record( $record, $d ))
					return false;
			}
			return true;
		}
		else {
			return $this->delete_record( $record_id, $d );
		}
	}
	/**
	* Deletes one Record.
	*/
	function delete_record( $record_id, &$d ) {
		global $db;
		
		$q = "DELETE FROM #__{vm}_zone_shipping WHERE zone_id='$record_id'";
		$db->query($q);
		$db->next_record();
		return True;
	}
  /**************************************************************************
   * name: assign()
   * created by: mike
   * description: Assigns a zone to a country
   * parameters:
   * returns:
   **************************************************************************/
  function assign(&$d) {
    $db = new ps_DB; 
    $timestamp = time();

    if (!$this->validate_assign($d)) {
      return False;	
    }
	if( is_array( $d["country_id"] )) {
		$i = 0;
		foreach( $d["country_id"] as $country ) {
			$q = "UPDATE #__{vm}_country SET zone_id='".$d["zone_id"][$i]."'";
			$q .= " WHERE country_id='".$country."'";
			$db->query($q);
			$i++;
		}
	}
    return True;
  }
  /**************************************************************************
  ** name: list_zones($list_name,$value)
  ** created by: pfmartin/mwattier
  ** description:  Returns an HTML dropdown box for the countries
  ** parameters: $name - name of the HTML dropdown element
  **             $value - Drop down item to make selected
  **             $arr - array used to build the HTML drop down element
  ** returns: prints HTML drop down element to standard output
  ***************************************************************************/
   function list_zones($list_name,$value) {
     $db = new ps_DB;


	$q = "SELECT * from #__{vm}_zone_shipping ORDER BY zone_name ASC";
	$db->query($q);

	$html = "<select class=\"inputbox\" name=\"$list_name\">\n";
	while ($db->next_record()) {
       $html .= "<option value=\"" . $db->f("zone_id");
       if ($value == $db->f("zone_id")) {
			$html.= "\" selected=\"selected\"";
       }
       $html .= "\">" . $db->f("zone_name") . "</option>\n";
     }
     $html .= "</select>\n";
     return $html;
   }
 /**************************************************************************
  ** name: per_item($zone_id)
  ** created by: mwattier <geek@devcompany.com>
  ** description:  get the per item limit
  ** parameters: 
  **             
  **             
  ** returns: the cost limit for this zone
  ***************************************************************************/
   function per_item($zone_id) {
      $db = new ps_DB;

      $q = "SELECT zone_cost FROM #__{vm}_zone_shipping WHERE zone_id ='$zone_id' ";
      $db->query($q);
      $db->next_record(); 

      return $db->f("zone_cost");
        
   }
   /**
    * Returns the Name of a Shipping Zone
    * @static 
    * @param int $zone_id
    * @return string
    */
   function zone_name($zone_id) {
       $db = new ps_DB;

     	$q = "SELECT zone_name FROM #__{vm}_zone_shipping WHERE zone_id =".(int)$zone_id;
      	$db->query($q);
      	$db->next_record(); 
        return $db->f("zone_name");
        
   }
   
  /**************************************************************************
  ** name: zone_limit($zone_id)
  ** created by: mwattier <geek@devcompany.com>
  ** description:  get the per item limit
  ** parameters: 
  **             
  **             
  ** returns: the cost limit for this zone
  ***************************************************************************/
   function zone_limit($zone_id) {
       $db = new ps_DB;

     $q = "SELECT zone_limit FROM #__{vm}_zone_shipping WHERE zone_id ='$zone_id' ";
      $db->query($q);
      $db->next_record(); 

         return $db->f("zone_limit");
        
   }
}
?>
