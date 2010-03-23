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
			$string = @htmlentities( $string, constant($quote_style), self::vmGetCharset() );
		} else {
			$string = @htmlspecialchars( $string, $quote_style, self::vmGetCharset() );
		}
		return $string;
	}


	/**
	 * Returns the charset string from the global _ISO constant
	 *
	 * @return string UTF-8 by default
	 * @since 1.0.5
	 */
	function vmGetCharset() {
		$iso = explode( '=', @constant('_ISO') );
		if( !empty( $iso[1] )) {
			return $iso[1];
		}
		else {
			return 'UTF-8';
		}
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
	function selectList($name, $value, $arrIn, $size=1, $multiple="", $extra="") {

		$html = '';
		if( empty( $arrIn ) ) {
			$arr = array();
		} else {
			if(!is_array($arrIn)){
	        	 $arr=array($arrIn);
	        } else {
	        	 $arr=$arrIn;
	        }			
		}

        
		$html = '<select class="inputbox" name="'.$name.'" size="'.$size.'" '.$multiple.' '.$extra.'>';

		while (list($key, $val) = each($arr)) {
//		foreach ($arr as $key=>$val){
			$selected = "";
			if( is_array( $value )) {
				if( in_array( $key, $value )) {
					$selected = 'selected="selected"';
				}
			}
			else {
				if(strtolower($value) == strtolower($key) ) {
					$selected = 'selected="selected"';
				}
			}

			$html .= '<option value="'.$key.'" '.$selected.'>'.self::shopMakeHtmlSafe($val);
			$html .= '</option>';

		}

		$html .= '</select>';

		return $html;
	}
	
	
	/**
	 * 
	 */
    function selectListParamParser( $arrIn, $tag_name, $tag_attribs, $key, $text, $selected, $required=0 ) {
//    function selectListParamParser($tag_name ,$tag_attribs ,$arrIn , $key, $text, $selected, $required=0 ) {
        
        echo '<br />$tag_name '.$tag_name;
        echo '<br />$tag_attribs '.$tag_attribs;
        echo '<br />$key '.$key;
        echo '<br />$text '.$text;
        echo '<br />$selected '.$selected;
        if(empty($arrIn)){
        	 return 'Error selectListParamParser no first argument given';
        }
        if(!is_array($arrIn)){
        	 $arr=array($arrIn);
        } else {
        	 $arr=$arrIn;
        }
        reset( $arr );
        $html = "\n<select name=\"$tag_name\" id=\"".str_replace('[]', '', $tag_name)."\" $tag_attribs>";
        if(!$required) $html .= "\n\t<option value=\"\">".JText::_('VM_SELECT')."</option>";
        $n=count( $arr );
        for ($i=0; $i < $n; $i++ ) {
        	echo '<br />Watn isn hier loas $arr[$i] '.$arr[$i].' und $text '.$text.'<br />';
                $k = stripslashes($arr[$i]->$key);
                $t = stripslashes($arr[$i]->$text);
                $id = isset($arr[$i]->id) ? $arr[$i]->id : null;

                $extra = '';
                $extra .= $id ? " id=\"" . $arr[$i]->id . "\"" : '';
                if (is_array( $selected )) {
                        foreach ($selected as $obj) {
                                $k2 = stripslashes($obj->$key);
                                if ($k == $k2) {
                                        $extra .= " selected=\"selected\"";
                                        break;
                                }
                        }
                } else {
                        $extra .= ($k == stripslashes($selected) ? " selected=\"selected\"" : '');
                }
                $html .= "\n\t<option value=\"".$k."\"$extra>";
				if( $t[0] == '_' ) $t = substr( $t, 1 );
				$html .= JText::_($t);
                $html .= "</option>";
        }
        $html .= "\n</select>\n";
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