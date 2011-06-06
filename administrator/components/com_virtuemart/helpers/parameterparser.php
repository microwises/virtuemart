<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage core
* @copyright Copyright (c) 2006 Open Source Matters
* @copyright Copyright (C) 2006-2008 soeren - All rights reserved.
* @copyright Copyright (c) 2010 The virtuemart team
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org

/**
* Parameters handler
* @package VirtueMart
*/
class FileUtilities{

	/** TODO obsolete?
	 * Lists all available payment classes in the payment directory
	 *
	 * @param string $name
	 * @param string $preselected
	 * @return string
	 */
	function list_available_classes( $name, $preselected='payment' ) {

		$files = self::vmReadDirectory( JPATH_PLUGINS.DS.'vmpayment', ".php$", true, true);
		$list = array();
        foreach ($files as $file) {
            $file_info = pathinfo($file);
            $filename = $file_info['basename'];
            if( stristr($filename, '.cfg')) { continue; }
            $list[] = array('file' => basename($filename, '.php' ), 'fileName'=>$filename);
        }
		return JHTML::_('select.genericlist', $list, 'file', '', 'file', 'fileName', $preselected );
	}

	/**
	* Function to strip additional / or \ in a path name
	* @param string The path
	* @param boolean Add trailing slash
	*/
	function vmPathName($p_path,$p_addtrailingslash = true) {
		$retval = "";

		$isWin = (substr(PHP_OS, 0, 3) == 'WIN');

		if ($isWin)	{
			$retval = str_replace( '/', '\\', $p_path );
			if ($p_addtrailingslash) {
				if (substr( $retval, -1 ) != '\\') {
					$retval .= '\\';
				}
			}

			// Check if UNC path
			$unc = substr($retval,0,2) == '\\\\' ? 1 : 0;

			// Remove double \\
			$retval = str_replace( '\\\\', '\\', $retval );

			// If UNC path, we have to add one \ in front or everything breaks!
			if ( $unc == 1 ) {
				$retval = '\\'.$retval;
			}
		} else {
			$retval = str_replace( '\\', '/', $p_path );
			if ($p_addtrailingslash) {
				if (substr( $retval, -1 ) != '/') {
					$retval .= '/';
				}
			}

			// Check if UNC path
			$unc = substr($retval,0,2) == '//' ? 1 : 0;

			// Remove double //
			$retval = str_replace('//','/',$retval);

			// If UNC path, we have to add one / in front or everything breaks!
			if ( $unc == 1 ) {
				$retval = '/'.$retval;
			}
		}

		return $retval;
	}

	/**
	* Utility function to read the files in a directory
	* @param string The file system path
	* @param string A filter for the names
	* @param boolean Recurse search into sub-directories
	* @param boolean True if to prepend the full path to the file name
	*/
	function vmReadDirectory( $path, $filter='.', $recurse=false, $fullpath=false  ) {

		$arr = array();
		if (!@is_dir( $path )) {
			return $arr;
		}
		$handle = opendir( $path );

		while ($file = readdir($handle)) {
			$dir = self::vmPathName( $path.'/'.$file, false );
			$isDir = is_dir( $dir );
			if (($file != ".") && ($file != "..")) {
				if (preg_match( "/$filter/", $file )) {
					if ($fullpath) {
						$arr[] = trim( self::vmPathName( $path.'/'.$file, false ) );
					} else {
						$arr[] = trim( $file );
					}
				}
				if ($recurse && $isDir) {
					$arr2 = self::vmReadDirectory( $dir, $filter, $recurse, $fullpath );
					$arr = array_merge( $arr, $arr2 );
				}
			}
		}
		closedir($handle);
		asort($arr);
		return $arr;
	}

}

class vmParameters extends JParameter{

//	/** @var string Path to the xml setup file */
	var $_path 		= null;
//	/** @var string The type of setup file */
	var $_type 		= null;

	var $_group ='_default';
	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	string The raw parms text
	 * @param	string paym_element payment element name
	 * @since	1.5
	 */
	function __construct($data, $paym_element = '', $type='component')
	{
                JPlugin::loadLanguage('plg_vmpayment_'.$paym_element );
                $path=JPATH_PLUGINS.DS.'vmpayment'.DS.basename($paym_element).'.xml';
		parent::__construct($data,$path);
		$this->_type = $type;
		$this->_raw = $data;

	}



	/**
	* @param string The name of the control, or the default text area if a setup file is not found
	* @author Sören, Max Milbers
	* @return string HTML
	*/
	function render( $name='params' ) {

		if (is_object( $this->_xml[$this->_group] )) {

			$params = $this->getParams($name);
			$html = array();
			$html[] = '<table width="100%" class="adminform">';

			$element = $this->_xml[$this->_group];

			if ($description = @$element->attributes( 'description')) {
				// add the params description to the display
				$html[] = '<tr><td colspan="2">' . $description . '</td></tr>';
			}

			$this->_methods = get_class_methods( get_class( $this ) );


			$i=0;
			foreach ($element->_children as $param) {
				$result = $this->renderParam( $param, $name );
				$html[] = '<tr>';

				$html[] = '<td width="40%" class="labelcell"><span class="editlinktip">' .Jtext::_( $result[0])  . '</span></td>';
				$html[] = '<td>' . $result[1] . $result[2].'</td>';

				$html[] = '</tr>';
			}
			$html[] = '</table>';

			if (count( $element->_children ) < 1) {
				$html[] = "<tr><td colspan=\"2\"><i>" . _NO_PARAMS . "</i></td></tr>";
			}
			return implode( "\n", $html );
		} else {
			return '<textarea name="$name" cols="40" rows="10" class="text_area">'.$this->_raw.'</textarea>';
		}
	}

/**
 *
 * @author Sören, Max Milbers
* @param object A param tag node
* @param string The control name
* @return array Any array of the label, the form element and the tooltip
*/
	function renderParam( &$param, $control_name='params' ) {

		$result = array();

		$name = $param->attributes( 'name');
		$type = $param->attributes( 'type');
		if( $param->attributes( 'label') != '') {
			$label = JText::_($param->attributes( 'label'));
		} else {
			$label = '';
		}

		if( $param->attributes( 'description') ) {
			$description = JText::_($param->attributes( 'description'));
		} else {
			$description = JText::_('COM_VIRTUEMART_NO_DESCRIPTION_FOUND');
		}

		$result[0] = $label ? $label : $name;

		if ($type == 'spacer' || $type == 'checkbox') {
			$result[0] = '&nbsp;';
		} else {
//			$result[0] = JHTML::tooltip( addslashes( $description ), addslashes( $result[0] ), '', '', $result[0], '#', 0 );
			//$result[0] = $description;
		}

		dump($this->_methods,'_form_' . $type);
		if (in_array( '_form_' . $type, $this->_methods )) {

			$value = $this->get($name);
//			$value = $this->get($param->attributes('name'), $param->attributes('default'));
			$result[1] =  call_user_func( array( $this, '_form_' . $type ), $name, $value, $param, $control_name, $label );


		} 
		//else {
		//	$result[1] = _HANDLER . ' = ' . $type;
		//}

		if ( $description ) {
//			$result[2] = JHTML::tooltip( $description, $result[0] );
			$result[2] = JHTML::tooltip( $description);
//			$result[2] =  $description;
		} else {
			$result[2] = '';
		}

		return $result;
	}

	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_text( $name, $value, &$node, $control_name ) {
		$size = $node->attributes( 'size');
		if( (int)$size == 0 ) {
			$size = 25;
		}
		return '<input type="text" name="'. $control_name .'['. $name .']" value="'. htmlspecialchars($value) .'" class="text_area" size="'. $size .'" />';
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_password( $name, $value, &$node, $control_name ) {
		$size = $node->attributes( 'size');
		if( (int)$size == 0 ) {
			$size = 25;
		}
		return '<input type="password" name="'. $control_name .'['. $name .']" value="'. htmlspecialchars($value) .'" class="text_area" size="'. $size .'" />';
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_checkbox( $name, $value, &$node, $control_name, $label='' ) {
		$default = $node->attributes('default');
		$checked = '';
		if( $value == $default ) {
			$checked = ' checked="checked"';
		}
		$id = uniqid($name);
		return '<input type="checkbox" name="'. $control_name .'['. $name .']" value="'. htmlspecialchars($value) .'"'.$checked.' class="text_area" id="'.$id.'" />
		<label for="'.$id.'">'.$label.'</label>';
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_list( $name, $value, &$node, $control_name ) {

		$size = $node->attributes('size');
		$multiselect = $node->attributes('multiselect');
		if( $multiselect ) {
			$multiselect = 'multiple="multiple"';
			$size = 5;
			$name .= ']['; // well, if it's multi-select, this must be an array, right?
			if( strstr($value,',')) {
				$value = explode(',', $value );
			}
		}
		if( $size == 0 ) $size = 1;
		$options = array();
		foreach ($node->_children as $option) {
			$val = $option->attributes( 'value');
			$text = trim($option->data());
			$options[$val] = JText::_($text);
		}

		return VmHTML::selectList( $control_name .'['. $name .']', $value, $options, $size, $multiselect );
	}

	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_radio( $name, $value, &$node, $control_name ) {

		$options = array();
		foreach ($node->_children as $option) {
			$val 	= $option->attributes( 'value');
			$text 	= trim($option->data());
			$options[$val] = JText::_($text);
		}

		return VmHTML::radioList( $control_name .'['. $name .']', $value, $options );
	}

	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_table_data_list( $name, $value, &$node, $control_name ) {

		$db = JFactory::getDBO();

		$table = $node->attributes('table');
		$condition = $node->attributes('sql_condition');
		$valuefield = $node->attributes('valuefield');
		$textfield = $node->attributes('textfield');
		$orderfield = $node->attributes('orderfield');
		$sorting = strtoupper($node->attributes('sorting')) == 'DESC' ? 'DESC' : 'ASC';
		$multiselect = $node->attributes('multiselect');

		$query = "SELECT `".$db->getEscaped($valuefield).'`, `'.$db->getEscaped($textfield)."`"
		. "\n FROM `".$db->getEscaped($table)."`";
		if( $condition != '' ) {
			$query .= "\n WHERE ".$condition;
		}
		if( $orderfield ) {
			$query .= "\n ORDER BY `".$db->getEscaped($orderfield)."` ".$sorting;
		}
		$db->setQuery($query);
		$array = $db->loadResultArray();

		if( $multiselect == '1' ) {
			$multiple = 'multiple="multiple"';
			$size = 5;
		} else {
			$multiple = '';
			$size = 1;
		}
		$name = $control_name .'['. $name .']';
		return  VmHTML::selectList( $name, $value, $array, $size, $multiple, 'class="inputbox"'  );
	}


	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
//	function _form_vm_category( $name, $value, &$node, $control_name ) {
//		global $database;
//
//		$multiselect = $node->_attributes( 'multiselect' );
//		if( $multiselect == '1' ) {
//			$multiple = true;
//			$size = 5;
//		} else {
//			$multiple = false;
//			$size = 1;
//		}
//		require( CLASSPATH.'ps_product_category.php');
//		$ps_product_category = new ps_product_category();
//
//		ob_start();
//		$ps_product_category->list_all(''. $control_name .'['. $name .']', 0, array(), $size, true, $multiple );
//		$category_dropdown = ob_get_clean();
//		return $category_dropdown;
//	}

	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_filelist( $name, $value, &$node, $control_name ) {

		// path to images directory
		$path 	= JPATH_SITE . $node->attributes( 'directory');
		$filter = $node->attributes( 'filter');
		$files 	= vmReadDirectory( $path, $filter );

		$options = array();
		foreach ($files as $file) {
			$options[$file] = $file;
		}
		if ( !$node->attributes( 'hide_none' ) ) {
			array_unshift( $options, array( '-1', '- '. 'Do Not Use' .' -' ) );
		}
		if ( !$node->attributes( 'hide_default' ) ) {
			array_unshift( $options, array( '', '- '. 'Use Default' .' -' ) );
		}

		return VmHTML::selectList( ''. $control_name .'['. $name .']', $value, $options, 1, '', 'class="inputbox"' );
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_imagelist( $name, $value, &$node, $control_name ) {
		$node->addAttribute( 'filter', '\.png$|\.gif$|\.jpg$|\.bmp$|\.ico$' );
		return $this->_form_filelist( $name, $value, $node, $control_name );
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_textarea( $name, $value, &$node, $control_name ) {
 		$rows 	= $node->attributes( 'rows');
 		$cols 	= $node->attributes( 'cols');
 		// convert <br /> tags so they are not visible when editing
 		$value 	= str_replace( '<br />', "\n", $value );

 		return '<textarea name="' .$control_name.'['. $name .']" cols="'. $cols .'" rows="'. $rows .'" class="text_area">'. htmlspecialchars($value) .'</textarea>';
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_hidden( $name, $value, &$node, $control_name ) {

 		return '<input name="' .$control_name.'['. $name .']" value="'. htmlspecialchars($value) .'" type="hidden" />';
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_spacer( $name, $value, &$node, $control_name ) {

		if ( $value ) {
			return '<h3>'.JText::_($value).'</h3>';
		} else {
			return '<hr />';
		}
	}
	function _form_secret_key( $name, $value, &$node, $control_name ) {

			return '<a class="button" id="changekey" href="'
			. JRoute::_($_SERVER['SCRIPT_NAME']."?page=store.payment_method_keychange&pshop_mode=admin&element=$name") .'" >'
			. JText::_('COM_VIRTUEMART_CHANGE_TRANSACTION_KEY')
			.'<a/>';

	}
	/**
	* special handling for textarea param
	*/
	function textareaHandling( &$txt ) {
		$total = count( $txt );
		for( $i=0; $i < $total; $i++ ) {
			if ( strstr( $txt[$i], "\n" ) ) {
				$txt[$i] = str_replace( "\n", '<br />', $txt[$i] );
			}
		}
		$txt = implode( "\n", $txt );

		return $txt;
	}
	
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'SQL';

	function _form_sql($name, $value, &$node, $control_name)
	{
		$db			= & JFactory::getDBO();
		$db->setQuery($node->attributes('query'));
		$key = ($node->attributes('key_field') ? $node->attributes('key_field') : 'value');
		$val = ($node->attributes('value_field') ? $node->attributes('value_field') : $name);
		return JHTML::_('select.genericlist',  $db->loadObjectList(), ''.$control_name.'['.$name.']', 'class="inputbox"', $key, $val, $value, $control_name.$name);
	}

	function fetchElement($name, $value, &$xmlElement, $control_name) {
		
		return;
	}
}



/**
* @param string
* @return string
*/
function vmParseParams( $txt ) {
	return vmParameters::parse( $txt );
}
// pure php no closing tag
