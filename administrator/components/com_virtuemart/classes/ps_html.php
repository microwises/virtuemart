<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* This Class provides some utility functions
* to easily create drop-down lists
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

class ps_html {


	function dropdown_display($name, $value, &$arr, $size=1, $multiple="", $extra="") {
		echo ps_html::selectList($name, $value, $arr, $size, $multiple, $extra );
	}

	/**
	 * Prints an HTML dropdown box named $name using $arr to
	 * load the drop down.  If $value is in $arr, then $value
	 * will be the selected option in the dropdown.
	 * @author gday
	 * @author soeren
	 * 
	 * @param string $name The name of the select element
	 * @param string $value The pre-selected value
	 * @param array $arr The array containting $key and $val
	 * @param int $size The size of the select element
	 * @param string $multiple use "multiple=\"multiple\" to have a multiple choice select list
	 * @param string $extra More attributes when needed
	 * @return string HTML drop-down list
	 */	
	function selectList($name, $value, &$arr, $size=1, $multiple="", $extra="") {
		$html = '';
		if( empty( $arr ) ) {
			$arr = array();
		}
		$html = "<select class=\"inputbox\" name=\"$name\" size=\"$size\" $multiple $extra>\n";

		
		while (list($key, $val) = each($arr)) {
			$selected = "";
			if( is_array( $value )) {
				if( in_array( $key, $value )) {
					$selected = "selected=\"selected\"";
				}
			}
			else {
				if(strtolower($value) == strtolower($key) ) {
					$selected = "selected=\"selected\"";
				}
			}
			$html .= "<option value=\"$key\" $selected>".shopMakeHtmlSafe($val);
			$html .= "</option>\n";
		}

		$html .= "</select>\n";
		
		return $html;
	}
	function yesNoSelectList( $fieldname, $value, $yesValue=1, $noValue=0 ) {
		
		$values = array($yesValue => JText::_('VM_ADMIN_CFG_YES'),
								$noValue => JText::_('VM_ADMIN_CFG_NO'));
		return ps_html::selectList($fieldname, $value, $values );
	}
	/**
	 * Creates a Radio Input List
	 *
	 * @param string $name
	 * @param string $value
	 * @param string $arr
	 * @param string $extra
	 * @return string
	 */
	function radioList($name, $value, &$arr, $extra="") {
		$html = '';
		if( empty( $arr ) ) {
			$arr = array();
		}
		$html = '';
		$i = 0;
		while (list($key, $val) = each($arr)) {
			$checked = '';
			if( is_array( $value )) {
				if( in_array( $key, $value )) {
					$checked = 'checked="checked"';
				}
			}
			else {
				if(strtolower($value) == strtolower($key) ) {
					$checked = 'checked="checked"';
				}
			}
			$html .= '<input type="radio" name="'.$name.'" id="'.$name.$i.'" value="'.htmlspecialchars($key, ENT_QUOTES).'" '.$checked.' '.$extra." />\n";
			$html .= '<label for="'.$name.$i++.'">'.$val."</label>\n";
		}
		
		return $html;
	}

	/**
	 * Lists titles for people
	 *
	 * @param string $t The selected title value
	 * @param string $extra More attributes when needed
	 */
	function list_user_title($t, $extra="") {
		

		$title = array(JText::_('VM_REGISTRATION_FORM_MR'),
						JText::_('VM_REGISTRATION_FORM_MRS'),
						JText::_('VM_REGISTRATION_FORM_DR'),
						JText::_('VM_REGISTRATION_FORM_PROF'));
		echo "<select class=\"inputbox\" name=\"title\" $extra>\n";
		echo "<option value=\"\">".JText::_('VM_REGISTRATION_FORM_NONE')."</option>\n";
		for ($i=0;$i<count($title);$i++) {
			echo "<option value=\"" . $title[$i]."\"";
			if ($title[$i] == $t)
			echo " selected=\"selected\" ";
			echo ">" . $title[$i] . "</option>\n";
		}
		echo "</select>\n";

	}

	/**
	 * Creates an drop-down list with numbers from 1 to 31 or of the selected range
	 *
	 * @param string $list_name The name of the select element
	 * @param string $selected_item The pre-selected value
	 */
	function list_days($list_name,$selected_item='', $start=null, $end=null) {
		if( $selected_item == '') {
			$selected_item = date('d');
		}
		$start = $start ? $start : 1;
		$end = $end ? $end : $start + 30;
		$list = array('Day');
		for ($i=$start; $i<=$end; $i++) {
			$list[$i] = $i;
		}
		ps_html::dropdown_display($list_name, $selected_item, $list);
	}
	/**
	 * Creates a Drop-Down List for the 12 months in a year
	 *
	 * @param string $list_name The name for the select element
	 * @param string $selected_item The pre-selected value
	 * 
	 */
	function list_month($list_name, $selected_item="") {
		
		if( $selected_item == '') {
			$selected_item = date('m');
		}
		$list = array("Month",
		"01" => JText::_('JAN'),
		"02" => JText::_('FEB'),
		"03" => JText::_('MAR'),
		"04" => JText::_('APR'),
		"05" => JText::_('MAY'),
		"06" => JText::_('JUN'),
		"07" => JText::_('JUL'),
		"08" => JText::_('AUG'),
		"09" => JText::_('SEP'),
		"10" => JText::_('OCT'),
		"11" => JText::_('NOV'),
		"12" => JText::_('DEC'));
		ps_html::dropdown_display($list_name, $selected_item, $list);
	}

	/**
	 * Creates an drop-down list with years of the selected range or of the next 7 years
	 *
	 * @param string $list_name The name of the select element
	 * @param string $selected_item The pre-selected value
	 */
	function list_year($list_name,$selected_item='', $start=null, $end=null) {
		$start = $start ? $start : date('Y');
		$end = $end ? $end : $start + 7;
		for ($i=$start; $i<=$end; $i++) {
			$list[$i] = $i;
		}
		ps_html::dropdown_display($list_name, $selected_item, $list);
		
	}


	function list_country($list_name, $value="", $extra="") {
		echo ps_html::getCountryList($list_name, $value, $extra);
	}

	/**
	 * Creates a drop-down list for all countries
	 *
	 * @param string $list_name The name of the select element
	 * @param string $value The value of the pre-selected option
	 * @param string $extra More attributes for the select element when needed
	 * @return string The HTML code for the select list
	 */	
	function getCountryList($list_name, $value="", $extra="") {
		

		$db = new ps_DB;

		$q = "SELECT * from #__{vm}_country ORDER BY country_name ASC";
		$db->query($q);
		$countries[''] = JText::_('VM_SELECT');
		
		while ($db->next_record()) {
//			$countries[$db->f("country_3_code")] = $db->f("country_name");
			$countries[$db->f("country_id")] = $db->f("country_name");
		}
		
		return ps_html::selectList( $list_name, $value, $countries, 1, '', $extra );
	}
	
	/**
	 * Creates a drop-down list for states [filtered by country_id]
	 *
	 * @param string $list_name The name of the select element
	 * @param string $selected_item The value of the pre-selected option
	 * @param int $country_id The ID of a country to filter states from
	 * @param string $extra More attributes for the select element when needed
	 * @return HTML code with the drop-down list
	 */
	function list_states($list_name,$selected_item="", $country_id="", $extra="") {
		
		$db =& new ps_DB;
		$q = 'SELECT country_name, state_name, state_3_code , state_2_code 
				FROM #__{vm}_state s, #__{vm}_country c 
				WHERE s.country_id = c.country_id';
		if( !empty( $country_id )) {
			$q .= ' AND c.country_id='.(int)$country_id;
		}
		$q .= "\nORDER BY country_name, state_name";
		$db->query( $q );
		$list = Array();
		$list["0"] = JText::_('VM_SELECT');
		$list["NONE"] = "not listed";
		$country = "";

		while( $db->next_record() ) {
			if( $country != $db->f("country_name")) {
				$list[] = "------- ".$db->f("country_name")." -------";
				$country = $db->f("country_name");
			}
//			$list[$db->f("state_2_code")] = $db->f("state_name");
			$list[$db->f("state_id")] = $db->f("state_name");
		}

		$this->dropdown_display($list_name, $selected_item, $list,"","",$extra);
		return 1;
	}
	/**
	 * Creates a Javascript based dynamic state list, depending of the selected
	 * country of a country drop-down list (specified by $country_list_name)
	 *
	 * @param string $country_list_name The name of the country select list element
	 * @param string $state_list_name The name for this states drop-down list
	 * @param string $selected_country_code The 3-digit country code that is pre-selected
	 * @param string $selected_state_code The state code of a pre-selected state
	 * @return string HTML code containing the dynamic state list
	 */
	function dynamic_state_lists( $country_list_name, $state_list_name, $selected_country_code="", $selected_state_code="" ) {
		global $vendor_country_3_code, $vm_mainframe, $mm_action_url, $page;
		
		/*$GLOBALS['vmLogger'] -> info ('dynamic_state_lists  $country_list_name     ' .$country_list_name);
		$GLOBALS['vmLogger'] -> info ('dynamic_state_lists  $state_list_name       ' .$state_list_name);
		$GLOBALS['vmLogger'] -> info ('dynamic_state_lists  $selected_country_code ' .$selected_country_code);
		$GLOBALS['vmLogger'] -> info ('dynamic_state_lists  $selected_state_code   ' .$selected_state_code);
		$GLOBALS['vmLogger'] -> info ('dynamic_state_lists  $vendor_country_3_code ' .$vendor_country_3_code);*/

		$db = new ps_DB;
		
		//note the unexisting & before the = PHP5 issues!
		//requesting singleton-like JDocument object
		$document = JFactory::getDocument();
		
		if( empty( $selected_country_code )) {
			$selected_country_code = $vendor_country_3_code;
		}else{
			$db->query( 'SELECT `country_3_code` FROM `#__{vm}_country` WHERE `country_id`="' . $selected_country_code. '" ' );
	//		if($db -> num_rows >0){
				$selected_country_code = $db->f('country_id');
//			}else{
//				$GLOBALS['vmLogger'] -> info ('dynamic_state_lists Country Code 3 not found for id ' .$selected_country_code. ' and code '. $db->f('country_3_code'));
//			}
		}

		if( empty( $selected_state_code )) {
			$selected_state_code = "originalPos";
		} 
//		else {
//			$db->query( 'SELECT `state_3_code` FROM `#__{vm}_state` WHERE `state_id`="' . $selected_state_code .'" ' );				
////			if($db -> num_rows >0){
//				$selected_state_code = $db->f('state_3_code');
////			}else{
////				$GLOBALS['vmLogger'] -> info ('dynamic_state_lists state Code 3 not found for id ' .$selected_state_code);
////			}
//		}
//		$selected_country_code ="GBR";
//		$selected_state_code = "ENG";
		
//		$db->query( "SELECT c.country_id, c.country_3_code, s.state_name, s.state_2_code
//						FROM #__{vm}_country c
//						LEFT JOIN #__{vm}_state s 
//						ON c.country_id=s.country_id OR s.country_id IS NULL
//						ORDER BY c.country_id, s.state_name" );
		//Maybe better
		//select only some fields from state table to avoid conflict
		$db->query( "SELECT c.*, s.state_id, s.state_3_code, s.state_2_code, s.state_name, s.published
						FROM #__{vm}_country c
						LEFT JOIN #__{vm}_state s 
						ON c.country_id=s.country_id
						ORDER BY c.country_id, s.state_name" );

//		$GLOBALS['vmLogger'] -> info ('dynamic_state_lists  $selected_country_code ' .$selected_country_code);
//		$GLOBALS['vmLogger'] -> info ('dynamic_state_lists  $selected_state_code   ' .$selected_state_code);
						
		if( $db->num_rows() > 0 ) {
			if( !vmIsAdminMode() ) {
				$vm_mainframe->addScript( $mm_action_url.'includes/js/mambojavascript.js');
				$vm_mainframe->addScript( $mm_action_url.'includes/js/joomla.javascript.js');
			}
			// Build the State lists for each Country
			//$script = "<script language=\"javascript\" type=\"text/javascript\">\n";
			$script = "//<![CDATA[ 
						<!--\n";
			$script .= "var originalOrder = 1,\n";
			$script .= "originalPos = '".$selected_country_code."',\n";
			$script .= "states = [];	// array in the format [key,value,text]\n";

			$i = 0;
			$prev_country = '';
			
			
			while( $db->next_record() ) {
				//$country_3_code = $db->f("country_3_code");
				
				$country_id = $db->f("country_id");
				
				//better verifing only the id
				if( $db->f('state_id') ) {
					
					// Add 'none' to the list of countries that have states:
					if( $prev_country != $country_id && $page == 'tax.tax_form' ) {
						$script .= "states[".$i++."] = [ '".$country_id."',' - ','".JText::_('VM_NONE')."' ];\n";
					}
					elseif( $prev_country != $country_id ) {
						$script .= "states[".$i++."] = [ '".$country_id."','',' -= ".JText::_('VM_SELECT')." =-' ];\n";
					}
					
					$prev_country = $country_id;
					
					// array in the format [key,value,text]
					$script .= "states[".$i++."] = [ '".$country_id."','".$db->f("state_id")."','".addslashes($db->f("state_name"))."' ];\n";
				}
				else{
					$script .= "states[".$i++."] = [ '".$country_id."',' - ','".JText::_('VM_NONE')."' ];\n";
				}
				
				
			}
//			$GLOBALS['vmLogger'] -> info ('$selected_country_code '.$selected_country_code.' ...$selected_state_code ' .$selected_state_code);
			
			$script .= "
			function changeStateList() { 
			  var selected_country = null,
			   stateList = document.adminForm.".$country_list_name.";
			  for (var i=0, l=stateList.length; i < l ; i++){
				 if (stateList[i].selected){
					selected_country = stateList[i].value;
				}
			  }

			  //joomla.javascript.js
			  changeDynaList('".$state_list_name."',states,selected_country, originalPos, originalOrder);
			  
			}";
			
			$script_write = "<script type=\"text/javascript\">writeDynaList( 'class=\"inputbox\" name=\"".$state_list_name."\" size=\"1\" id=\"state\"', states, originalPos, originalPos, ".$selected_state_code." );</script>";
			$script .= "
				//-->	
				//]]>";

			//embeding dynamic list declaration
			$vm_mainframe->addScriptDeclaration($script);
			
			//just returning the dynamic script to write combobox
			return $script_write;
		}else{
			$GLOBALS['vmLogger'] -> info ('No rows');
		}
	}


	/**
	 * Creates a drop-down list for weight units-of-measure
	 *
	 * @param string $list_name The name for the select element
	 * @return string The HTML code for the select list
	 */
	function list_weight_uom($list_name) {
		

		$list = array(JText::_('VM_SELECT'),
		"LBS" => "Pounds",
		"KGS" => "Kilograms",
		"G" => "Grams");
		$this->dropdown_display($list_name, "", $list);
		return 1;
	}

	function list_currency($list_name, $value="") {
		echo ps_html::getCurrencyList($list_name, $value, 'currency_code');
	}
	/**
	 * Creates a drop-down list for currencies. The currency ID is used as option value
	 *
	 * @param string $list_name The name of the select element
	 * @param string $value The value of the pre-selected option
	 * @return HTML code with the drop-down list
	 */
	function list_currency_id($list_name, $value="") {
		echo ps_html::getCurrencyList($list_name, $value, 'currency_id');
	}
	
	/**
	 * Creates a drop-down list for currencies.
	 *
	 * @param string $list_name The name of the select element
	 * @param string $value The value of the pre-selected option
	 * @param string $key The name of the field that will be the array index [curreny_code|currency_id]
	 * @return HTML code with the drop-down list
	 */	
	function getCurrencyList($list_name, $value="", $key='currency_code', $extra='', $size=1, $multiple='') {
		
		$db = new ps_DB;

		$q = "SELECT `currency_id`, `currency_code`, `currency_name` FROM `#__{vm}_currency` ORDER BY `currency_name` ASC";
		$db->query($q);
		
		if( $size == 1 ) {
			$currencies[''] = JText::_('VM_SELECT');
		}
		while ($db->next_record()) {
			$currencies[$db->f($key)] = $db->f("currency_name");
		}
		
		return ps_html::selectList( $list_name, $value, $currencies, $size, $multiple, $extra );
	}


	/**
	 * This is the equivalent to mosCommonHTML::idBox
	 * 
	 * @param int The row index
	 * @param int The record id
	 * @param string The name of the form element
	 * @param string The name of the checkbox element
	 * @return string
	 */
	function idBox( $rowNum, $recId, $frmName="adminForm", $name='cid' ) {

		return '<input type="checkbox" id="cb'.$rowNum.'" name="'.$name.'[]" value="'.$recId.'" onclick="ms_isChecked(this.checked, \''.$frmName.'\');" />';

	}
	/**
	 * Creates a multi-select list with all products except the given $product_id
	 *
	 * @param string $list_name The name of the select element
	 * @param array $values Contains the IDs of all products which are pre-selected
	 * @param int $product_id The product id that is excluded from the list
	 * @param boolean $show_items Wether to show child products as well
	 */
	function list_products($list_name, $values=array(), $product_id, $show_items=false ) {

		$db =& new ps_DB;

		$q = "SELECT #__{vm}_product.product_id,category_name,product_name
			FROM #__{vm}_product,#__{vm}_product_category_xref,#__{vm}_category ";
		if( !$show_items ) {
			$q .= "WHERE product_parent_id='0'
					AND #__{vm}_product.product_id <> '$product_id' 
					AND #__{vm}_product.product_id=#__{vm}_product_category_xref.product_id
					AND #__{vm}_product_category_xref.category_id=#__{vm}_category.category_id";
		}
		else {
			$q .= "WHERE #__{vm}_product.product_id <> '$product_id' 
					AND  #__{vm}_product.product_id=#__{vm}_product_category_xref.product_id 
					AND #__{vm}_product_category_xref.category_id=#__{vm}_category.category_id";;
		}
		$q .= ' ORDER BY category_name,#__{vm}_category.category_id,product_name';
		// This is necessary, because so much products are difficult to handle!
		$q .= ' LIMIT 0, 2000';
		
		$db->query( $q );
		$products = Array();
		while( $db->next_record() ) {
			$products[$db->f("product_id")] = $db->f("category_name")." =&gt; ".$db->f("product_name");
		}
		$this->dropdown_display($list_name, $values, $products, 20, "multiple=\"multiple\"");
	}

	/**
	 * Creates a drop-down list for Extra fields
	 * @deprecated 
	 * @param string $t The pre-selected value
	 * @param string $extra Additional attributes for the select element
	 */
	function list_extra_field_4($t, $extra="") {
		global  $vmLogger;

		$vmLogger->debug( 'The function '.__CLASS__.'::'.__FUNCTION__.' is deprecated. Use the userfield manager instead please.' );
		
		$title = array(array('Y',JText::_('VM_SHOPPER_FORM_EXTRA_FIELD_4_1')),
		array('N',JText::_('VM_SHOPPER_FORM_EXTRA_FIELD_4_2')));

		echo "<select class=\"inputbox\" name=\"extra_field_4\" $extra>\n";
		for ($i=0;$i<count($title);$i++) {
			echo "<option value=\"" . $title[$i][0]."\"";
			if ($title[$i][0] == $t)
			echo " selected=\"selected\" ";
			echo ">" . $title[$i][1] . "</option>\n";
		}
		echo "</select>\n";
	}
	/**
	 * Creates a drop-down list for Extra fields
	 * @deprecated 
	 * @param string $t The pre-selected value
	 * @param string $extra Additional attributes for the select element
	 */
	function list_extra_field_5($t, $extra="") {
		global  $vmLogger;
		
		$vmLogger->debug( 'The function '.__CLASS__.'::'.__FUNCTION__.' is deprecated. Use the userfield manager instead please.' );
		
		$title = array(array('A',JText::_('VM_SHOPPER_FORM_EXTRA_FIELD_5_1')),
		array('B',JText::_('VM_SHOPPER_FORM_EXTRA_FIELD_5_2')),
		array('C',JText::_('VM_SHOPPER_FORM_EXTRA_FIELD_5_3')));

		echo "<select class=\"inputbox\" name=\"extra_field_5\" $extra>\n";
		for ($i=0;$i<count($title);$i++) {
			echo "<option value=\"" . $title[$i][0]."\"";
			if ($title[$i][0] == $t)
			echo " selected=\"selected\" ";
			echo ">" . $title[$i][1] . "</option>\n";
		}
		echo "</select>\n";
	}
	/**
	 * Lists all available themes for this VirtueMart installation
	 *
	 * @param string $name
	 * @param string $preselected
	 * @return string
	 */
	function list_themes( $name, $preselected='default' ) {
		global $mosConfig_absolute_path;
		$themes = vmReadDirectory( $mosConfig_absolute_path . "/components/com_virtuemart/themes", "", false, true );
		$array = array();
		foreach ($themes as $theme ) {
			if( file_exists($theme.'/theme.php' ) ) {
				$array[basename($theme)] = basename( $theme );
			}
		}
		return ps_html::selectList( $name, $preselected, $array );
	}
	
	/**
	 * Funtion to create a select list holding all files for a special template section (e.g. order_emails)
	 *
	 * @param string $name
	 * @param string $section
	 * @param string $preselected
	 * @return string
	 */
	function list_template_files( $name, $section='browse', $preselected='' ) {
		
		$files = vmReadDirectory( VM_THEMEPATH . "templates/$section/" );
		$array = array();
        foreach ($files as $file) {
        	if( is_dir( $file ) ) continue;
            $file_info = pathinfo($file);
            $filename = $file_info['basename'];
            if( $filename == 'index.html' ) { continue; }
            $array[basename($filename, '.'.$file_info['extension'] )] = basename($filename, '.'.$file_info['extension'] );
        }
        if( $section == 'browse') {
        	$array = array_merge( array('managed' => 'managed'), $array );
        }
        return ps_html::selectList( $name, $preselected, $array );
	} 

	/**
	* Writes a box containing an information about the write access to a file
	* A green colored "Writable" box when the file is writeable
	* A red colored "Unwritable" box when the file is NOT writeable
	* 
	* @param string A path to a file or directory
	* @return string Prints a div element
	*/
	function writableIndicator( $folder, $style='text-align:left;margin-left:20px;' ) {
                
		if( !is_array( $folder)) {
			$folder = array($folder);
		}
		echo '<div class="vmquote" style="'.$style.'">';
        foreach( $folder as $dir ) {
            echo $dir . ' :: ';
            echo is_writable( $dir )
                 ? '<span style="font-weight:bold;color:green;">'.JText::_('VM_WRITABLE').'</span>'
                 : '<span style="font-weight:bold;color:red;">'.JText::_('VM_UNWRITABLE').'</span>';
            echo '<br/>';
        }
        echo '</div>';
	}
	/**
	 * This is used by lists to show a "Delete this item" button in each row
	 *
	 * @param string $id_fieldname The name of the identifying field [example: product_id]
	 * @param mixed $id The unique ID identifying the item that is to be deleted
	 * @param string $func The name of the function that is used to delete the item [e.g. productDelete]
	 * @param string $keyword The recent keyword [deprecated]
	 * @param int $limitstart The recent limitstart value [deprecated]
	 * @param string $extra Additional URL parameters to be appended to the link
	 * @return A link with the delete button in it
	 */
	function deleteButton( $id_fieldname, $id, $func, $keyword="", $limitstart=0, $extra="" ) {
		global $page, $sess;
		$no_menu = vmRequest::getInt('no_menu');
		$href = $sess->url($_SERVER['PHP_SELF']. "?page=$page&func=$func&$id_fieldname=$id&keyword=". urlencode($keyword)."&limitstart=$limitstart&no_menu=$no_menu" . $extra );
		$code = "<a class=\"toolbar\" href=\"$href\" onclick=\"return confirm('".JText::_('VM_DELETE_MSG') ."');\" onmouseout=\"MM_swapImgRestore();\"  onmouseover=\"MM_swapImage('delete$id','','". IMAGEURL ."ps_image/delete_f2.gif',1);\">";
		$code .= "<img src=\"". IMAGEURL ."ps_image/delete.gif\" alt=\"Delete this record\" name=\"delete$id\" align=\"middle\" border=\"0\" />";
		$code .= "</a>";

		return $code;
	}
	/**
	 * Used to create the Control Panel links with icons in it
	 *
	 * @param string $image The complete icon URL
	 * @param string $link The URL that is linked to
	 * @param string $text The text / label for the link
	 */
	function writePanelIcon( $image, $link, $text ) {
		echo '<div style="float:left;"><div class="icon">
			<a title="'.$text.'" href="'.$link.'">
					<img src="'.$image.'" alt="'.$text.'" align="middle" name="image" border="0" /><br />
			'.$text.'</a></div></div>
			';

	}
}

?>
