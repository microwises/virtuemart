<?php
/**
 * HTML helper class
 *
 * This class was developed to provide some standard HTML functions.
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author RickG
 * @copyright Copyright (c) 2004-2008 Soeren Eberhardt-Biermann, 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * HTML Helper
 *
 * @package VirtueMart
 * @subpackage Helpers
 * @author RickG
 */
class VmHTML{
	/**
	 * Converts all special chars to html entities
	 *
	 * @param string $string
	 * @param string $quote_style
	 * @param boolean $only_special_chars Only Convert Some Special Chars ? ( <, >, &, ... )
	 * @return string
	 */
	function shopMakeHtmlSafe( $string, $quote_style='ENT_QUOTES', $use_entities=false ) {
		if( defined( $quote_style )) {
			$quote_style = constant($quote_style);
		}
		if( $use_entities ) {
			$string = @htmlentities( $string, constant($quote_style), vmGetCharset() );
		} else {
			$string = @htmlspecialchars( $string, $quote_style, vmGetCharset() );
		}
		return $string;
	}

    /**
     * Generate HTML code for a checkbox
     *
     * @param string Name for the chekcbox
     * @param mixed Current value of the checkbox
     * @param mixed Value to assign when checkbox is checked
     * @param mixed Value to assign when checkbox is not checked
     * @return string HTML code for checkbox
     */
    function checkbox($name, $value, $checkedValue=1, $uncheckedValue=0) {
	if ($value == $checkedValue) {
	    $checked = 'checked="checked"';
	}
	else {
	    $checked = '';
	}
	$htmlcode = '<input type="hidden" name="' . $name . '" value="' . $uncheckedValue . '">';
	$htmlcode .= '<input type="checkbox" name="' . $name . '" value="' . $checkedValue . '" ' . $checked . ' />';
	return $htmlcode;
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
			$html .= "<option value=\"$key\" $selected>".self::shopMakeHtmlSafe($val);
			$html .= "</option>\n";
		}

		$html .= "</select>\n";
		
		return $html;
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
}