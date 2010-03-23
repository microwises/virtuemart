<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* This file contains functions and classes for common html tasks
*
* @version $Id$
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

/**
* This is the class for creating administration lists
*
* Usage:
* require_once( CLASSPATH . "pageNavigation.class.php" );
* require_once( CLASSPATH . "htmlTools.class.php" );
* // Create the Page Navigation
* $pageNav = new vmPageNav( $num_rows, $limitstart, $limit );
*
* // Create the List Object with page navigation
* $listObj = new listFactory( $pageNav );
*
* // print out the search field and a list heading
* $listObj->writeSearchHeader(JText::_('VM_PRODUCT_LIST_LBL'), IMAGEURL."ps_image/product_code.png", $modulename, "product_list");
*
* // start the list table
* $listObj->startTable();
*
* // these are the columns in the table
* $columns = Array(  "#" => "width=\"20\"",
* 					"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".$num_rows.")\" />" => "width=\"20\"",*
* 					JText::_('VM_PRODUCT_LIST_NAME') => '',
* 					JText::_('VM_PRODUCT_LIST_SKU') => '',
* 					_E_REMOVE => "width=\"5%\""
* 				);
* $listObj->writeTableHeader( $columns );
*
* 	###BEGIN LOOPING THROUGH RECORDS ##########
*
* 	$listObj->newRow();
*
* 	// The row number
* 	$listObj->addCell( $pageNav->rowNumber( $i ) );
*
* 	// The Checkbox
* 	$listObj->addCell( mosHTML::idBox( $i, $db->f("product_id"), false, "product_id" ) );
* 	...
* 	###FINISH THE RECENT LOOP########
* 	$listObj->addCell( $ps_html->deleteButton( "product_id", $db->f("product_id"), "productDelete", $keyword, $limitstart ) );
*
* 	$i++;
*
* 	####
* $listObj->writeTable();
* $listObj->endTable();
* $listObj->writeFooter( $keyword );
*
* @package VirtueMart
* @subpackage Classes
* @author soeren
*/
class listFactory {

	/** @var int the number of columns in the table */
	var $columnCount = 0;
	/** @var array css classes for alternating rows (row0 and row1 ) */
	var $alternateColors;
	/** @var int The column number */
	var $x = -1;
	/** @var int The row number */
	var $y = -1;
	/** @var array The table cells */
	var $cells = Array();
	/** @var vmPageNavigation The Page Navigation Object */
	var $pageNav;
	/** @var int The smallest number of results that shows the page navigation */
	var $_resultsToShowPageNav = 6;

	function listFactory( $pageNav=null ) {
		if( defined('_VM_IS_BACKEND')) {
			$this->alternateColors = array( 0 => 'row0', 1 => 'row1' );
		}
		else {
			$this->alternateColors = array( 0 => 'sectiontableentry1', 1 => 'sectiontableentry2' );
		}
		$this->pageNav = $pageNav;
	}

	/**
	* Writes the start of the button bar table
	*/
	function startTable() {
		?><script type="text/javascript"><!--
		function MM_swapImgRestore() { //v3.0
			var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
		} //-->
		</script>
		<table class="adminlist" width="100%">
		<?php
	}
	/**
	* writes the table header cells
	* Array $columnNames["Product Name"] = "class=\"small\" id=\"bla\"
	*/
	function writeTableHeader( $columnNames ) {
		if( !is_array( $columnNames ))
			$this->columnCount = intval( $columnNames );
		else {
			$this->columnCount = count( $columnNames );
			echo '<tr>';
			foreach( $columnNames as $name => $attributes ) {
				$name = html_entity_decode( $name );
				echo "<th class=\"title\" $attributes>$name</th>\n";
			}
			echo "</tr>\n";
		}
	}
	/**
	 * Adds a new row to the list
	 *
	 * @param string $class The additional CSS class name
	 * @param string $id The ID of the HTML tr element
	 * @param string $attributes Additional HTML attributes for the tr element
	 */
	function newRow( $class='', $id='', $attributes='') {
		$this->y++;
		$this->x = 0;
		if( $class != '') {
			$this->cells[$this->y]['class'] = $class;
		}
		if( $id != '') {
			$this->cells[$this->y]['id'] = $id;
		}
		if( $attributes != '' ) {
			$this->cells[$this->y]['attributes'] = $attributes;
		}

	}

	function addCell( $data, $attributes="" ) {

		$this->cells[$this->y][$this->x]["data"] = $data;
		$this->cells[$this->y][$this->x]["attributes"] = $attributes;

		$this->x++;
	}

	/**
	* Writes a table row with data
	* Array
	* $row[0]["data"] = "Cell Value";
	* $row[0]["attributes"] = "align=\"center\"";
	*/
	function writeTable() {
		if( !is_array( $this->cells ))
			return false;

		else {
			$i = 0;
			foreach( $this->cells as $row ) {
				echo "<tr class=\"".$this->alternateColors[$i];
				if( !empty($row['class'])) {
					echo ' '.$row['class'];
				}
				echo '"';
				if( !empty($row['id'])) {
					echo ' id="'.$row['id'].'" ';
				}
				if( !empty($row['attributes'])) {
					echo $row['attributes'];
				}
				echo ">\n";
				foreach( $row as $cell ) {
					if( $cell["data"] == 'i' || !isset( $cell["data"] ) || !is_array($cell)) continue;
					$value = $cell["data"];
					$attributes = $cell["attributes"];
					echo "<td  $attributes>$value</td>\n";
				}
				echo "</tr>\n";
				$i == 0 ? $i++ : $i--;
			}
		}
	}

	function endTable() {
		echo "</table>\n";
	}

	/**
	* This creates a header above the list table, containing a search box
	* @param The Label for the list (will be used as list heading!)
	* @param The core module name (e.g. "product")
	* @param The page name (e.g. "product_list" )
	* @param Additional varaibles to include as hidden input fields
	*/
	function writeSearchHeader( $title, $image="", $modulename, $pagename) {

		global $sess, $keyword;

		if( !empty( $keyword )) {
			$keyword = urldecode( $keyword );
		}
		else {
			$keyword = "";
		}
		$search_date = JRequest::getVar('search_date', null);
		$show = JRequest::getVar('show', "" );

		$header = '<a name="listheader"></a>';
		$header .= '<form name="adminForm" action="'.$_SERVER['PHP_SELF'].'" method="post">

					<input type="hidden" name="option" value="'.VM_COMPONENT_NAME.'" />
					<input type="hidden" name="page" value="'. $modulename . '.' . $pagename . '" />
					<input type="hidden" name="task" value="" />
					<input type="hidden" name="func" value="" />
					<input type="hidden" name="vmtoken" value="'.vmSpoofValue($sess->getSessionId()).'" />
					<input type="hidden" name="no_menu" value="'.vmRequest::getInt( 'no_menu' ).'" />
					<input type="hidden" name="no_toolbar" value="'.vmRequest::getInt('no_toolbar').'" />
					<input type="hidden" name="only_page" value="'.vmRequest::getInt('only_page').'" />
					<input type="hidden" name="boxchecked" />';
		if( defined( "_VM_IS_BACKEND") || @$_REQUEST['pshop_mode'] == "admin"  ) {
			$header .= "<input type=\"hidden\" name=\"pshop_mode\" value=\"admin\" />\n";
		}
        if(( $title != "" ) || !empty( $pagename )) {
			$header .= '<table><tr>';
			if( $title != "" ) {
				$style = ($image != '') ? 'style="background:url('.$image.') no-repeat;text-indent: 30px;line-height: 50px;"' : '';
				$header .= '<td><div class="header" '.$style.'><h2 style="margin: 0px;">'.$title.'</h2></div></td>'."\n";
				$GLOBALS['vm_mainframe']->setPageTitle( $title );
			}

			if( !empty( $pagename ))
				$header .= '<td width="20%">
				<input class="inputbox" type="text" size="25" name="keyword" value="'.shopMakeHtmlSafe($keyword).'" />
				<input class="button" type="submit" name="search" value="'.JText::_('VM_SEARCH_TITLE').'" />
				</td>';

			$header .= "\n</tr></table><br style=\"clear:both;\" />\n";
		}

		if ( !empty($search_date) ) // Changed search by date
			$header .= '<input type="hidden" name="search_date" value="'.$search_date.'" />';

		if( !empty($show) ) {
			$header .= "<input type=\"hidden\" name=\"show\" value=\"$show\" />\n";
		}

		echo $header;
	}

	/**
	* This creates a list footer (page navigation)
	* @param The core module name (e.g. "product")
	* @param The page name (e.g. "product_list" )
	* @param The Keyword from a search by keyword
	* @param Additional varaibles to include as hidden input fields
	*/
	function writeFooter($keyword, $extra="") {
		$footer= "";
		if( $this->pageNav !== null ) {
			if( $this->_resultsToShowPageNav <= $this->pageNav->total ) {

				$footer = $this->pageNav->getListFooter();
			}
		}
		else {
			$footer = "";
		}

		if(!empty( $extra )) {
			$extrafields = explode("&", $extra);
			array_shift($extrafields);
			foreach( $extrafields as $key => $value) {
				$field = explode("=", $value);
				$footer .= '<input type="hidden" name="'.$field[0].'" value="'.@shopMakeHtmlSafe($field[1]).'" />'."\n";
			}
		}
		$footer .= '</form>';

		echo $footer;
	}
}
/**
* This is the class for creating regular forms used in VirtueMart
*
* Usage:
* //First create the object and let it print a form heading
* $formObj = &new formFactory( "My Form" );
* //Then Start the form
* $formObj->startForm();
* // Add necessary hidden fields
* $formObj->hiddenField( 'country_id', $country_id );
*
* // Write your form with mixed tags and text fields
* // and finally close the form:
* $formObj->finishForm( $funcname, $modulename.'.country_list' );
*
* @package VirtueMart
* @subpackage Core
* @author soeren
*/
class formFactory {
	/**
	* Constructor
	* Prints  the Form Heading if provided
	*/
	function formFactory( $title = '' ) {
		if( $title != "" ) {
			echo '<div class="header"><h2 style="margin: 0px;">'.$title."</h2></div>\n";
			$GLOBALS['vm_mainframe']->setPageTitle( $title );
		}
	}
	/**
	* Writes the form start tag
	*/
	function startForm( $formname = 'adminForm', $extra = "" ) {
		$action = (!defined('_VM_IS_BACKEND' ) && !empty($_REQUEST['next_page'])) ? 'index.php' : $_SERVER['PHP_SELF'];
		echo '<form method="post" action="'. $action .'" name="'.$formname.'" '.$extra.' target="_self">';
	}

	function hiddenField( $name, $value ) {
		echo ' <input type="hidden" name="'.$name.'" value="'.shopMakeHtmlSafe($value).'" />
		';
	}
	/**
	* Writes necessary hidden input fields
	* and closes the form
	*/
	function finishForm( $func, $page='' ) {
		$no_menu = vmRequest::getInt('no_menu');

		$html = '
		<input type="hidden" name="vmtoken" value="'.vmSpoofValue($GLOBALS['sess']->getSessionId()).'" />
		<input type="hidden" name="func" value="'.$func.'" />
        <input type="hidden" name="page" value="'.$page.'" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="option" value="'.VM_COMPONENT_NAME.'" />';
		if( $no_menu ) {
			$html .= '<input type="hidden" name="ajax_request" value="1" />';
		}
		$html .= '<input type="hidden" name="no_menu" value="'.$no_menu.'" />';
		$html .= '<input type="hidden" name="no_toolbar" value="'.JRequest::getVar('no_toolbar',0).'" />';
		$html .= '<input type="hidden" name="only_page" value="'.JRequest::getVar('only_page',0).'" />';

        if( defined( "_VM_IS_BACKEND") || @$_REQUEST['pshop_mode'] == "admin"  ) {
        	$html .= '<input type="hidden" name="pshop_admin" value="admin" />';
        }
        $html .= '
		</form>
		';

		echo $html;
	}
}

/**
* Tab Creation handler
* @package VirtueMart
* @subpackage core
* @author soeren
* Modified to use Panel-in-Panel functionality
*/
class vmTabPanel {
	/** @var int Use cookies */
	var $useCookies = 0;

    /** @var string Panel ID */
    var $panel_id;
    var $tabs;

	/**
	* Constructor
	* Includes files needed for displaying tabs and sets cookie options
	* @param int useCookies, if set to 1 cookie will hold last used tab between page refreshes
	* @param int show_js, if set to 1 the Javascript Link and Stylesheet will not be printed
	*/
	function vmTabPanel($useCookies, $show_js, $panel_id) {
		vmCommonHTML::loadExtjs();
        $this->useCookies = $useCookies;
        $this->panel_id = $panel_id;
        $this->tabs = array();
	}

	/**
	* creates a tab pane and creates JS obj
	* @param string The Tab Pane Name
	*/
	function startPane($id) {
//		if($id=="debug-pane"){
//			echo '<div class="tab-page" id="'.$id.'" width="80%">';
//		} else {
			echo '<div class="tab-page" id="'.$id.'">';
//		}
		
		$this->pane_id = $id;
	}

	/**
	* Ends Tab Pane
	*/
	function endPane() {
		echo "</div>";
		$scripttag = "
	tabinit_{$this->panel_id} = function() {
	Ext.QuickTips.init();
	var state = Ext.state.Manager;
	var tabs_{$this->panel_id} = new Ext.TabPanel({
		renderTo: '{$this->pane_id}',
		activeTab: 0,
		deferredRender: false,
		enableTabScroll: true,
		autoScroll: true,
		autoWidth: true,
		items: [";

		$num = 0;
		$numTabs = count( $this->tabs );
		foreach ( $this->tabs as $id => $title ) {
			$scripttag .= "{ autoHeight: true, contentEl: '$id', title: '".addslashes($title)."' , tabTip: '".addslashes(strip_tags($title))."' }";
			$num++;
			if( $num < $numTabs ) {
				$scripttag .= ",\n";
			}
		}
		$scripttag .= "]
		});";
		reset($this->tabs);
		if( $this->useCookies ) {
			$scripttag .= "tabs_{$this->panel_id}.activate(state.get('{$this->panel_id}-active', '".key($this->tabs)."'));";
		} else {
			$scripttag .= "tabs_{$this->panel_id}.activate( '".key($this->tabs)."'); ";
		}

		if( $this->useCookies ) {
			$scripttag .= "
	Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
	tabs_{$this->panel_id}.on('tabchange', function(tp, tab){
     state.set('{$this->panel_id}-active', tab.id);
     });
	";

		}

		$scripttag .= "};
	if( Ext.isIE ) {
	Ext.EventManager.addListener( window, 'load', tabinit_{$this->panel_id} );
}
else {
	Ext.onReady( tabinit_{$this->panel_id} );
}

		";

		echo vmCommonHTML::scriptTag('', $scripttag );

	}

	/*
	* Creates a tab with title text and starts that tabs page
	* @param tabText - This is what is displayed on the tab
	* @param paneid - This is the parent pane to build this tab on
	*/
	function startTab( $tabText, $paneid ) {
		echo "<div class=\"tab-page\" id=\"".$paneid."\">";
		$this->tabs[$paneid] = $tabText;
	}

	/*
	* Ends a tab page
	*/
	function endTab() {
		echo "</div>";
	}
}
class mShopTabs extends vmTabPanel { }

class vmMooAjax {

	/**
	 * This is used to print out Javascript code for the moo.ajax script
	 *
	 * @param string $url
	 * @param string $updateId
	 * @param string $onComplete A JS function name to be called after the HTTP transport has been finished
	 * @param array VM_COMPONENT_NAMEs
	 * @param string $varName The name of a variable the ajax object is assigned to
	 */
	function writeAjaxUpdater( $url, $updateId, $onComplete, $method='post', $vmDirs=array(), $varName='' ) {
		echo vmMooAjax::getAjaxUpdater($url, $updateId, $onComplete, $methods, $vmDirs, $varName);
	}

	function getAjaxUpdater( $url, $updateId, $onComplete, $method='post', $vmDirs=array(), $varName='' ) {
		global $mosConfig_live_site;

		vmCommonHTML::loadMooTools();

		$path = defined('_VM_IS_BACKEND' ) ? '/administrator/' : '/';
		$vmDirs['method'] = $method;
		$html = '';
		if( $varName ) {
			$html .= 'var '.$varName.' = ';
		}
		if( !strstr( $url, $mosConfig_live_site) && !strstr($url, 'http' )) {
			$url = $mosConfig_live_site.$path.$url;
		}
		$html .= "new ajax('$url', {\n";
		foreach (VM_COMPONENT_NAMEs as $key => $val) {
			if( strstr( $val, '.')) {
				$html .= "$key: $val,\n";
			}
			else {
				$html .= "$key: '$val',\n";
			}
		}
		if( $updateId != '' ) {
			$html .= "update: '$updateId'";
			if( $onComplete ) { $html .= ",\n"; }
		}
		if( $onComplete ) {
			$html .= "onComplete: $onComplete";
		}
		$html .= '
		});';

		return $html;
	}
}


/**
 * This is the class offering functions for common HTML tasks
 *
 */
class vmCommonHTML {
	/**
	 * function to create a hyperlink
	 *
	 * @param string $link
	 * @param string $text
	 * @param string $target
	 * @param string $title
	 * @param string $attributes
	 * @return string
	 */
	function hyperLink( $link, $text, $target='', $title='', $attributes='' ) {

		if( $target ) {
			$target = ' target="'.$target.'"';
		}
		if( $title ) {
			$title = ' title="'.$title.'"';
		}
		if( $attributes ) {
			$attributes = ' ' . $attributes;
		}
		return '<a href="'.vmAmpReplace($link).'"'.$target.$title.$attributes.'>'.$text.'</a>';
	}
	/**
	 * Function to create an image tag
	 *
	 * @param string $src
	 * @param string $alt
	 * @param int $height
	 * @param int $width
	 * @param string $title
	 * @param int $border
	 * @param string $attributes
	 * @return string
	 */
	function imageTag( $src, $alt='', $align='', $height='', $width='', $title='', $border='0', $attributes='' ) {

		if( $align ) { $align = ' align="'.$align.'"'; }
		if( $height ) { $height = ' height="'.$height.'"'; }
		if( $width ) { $width = ' width="'.$width.'"'; }
		if( $title ) { $title = ' title="'.$title.'"'; }
		if( $attributes ) {	$attributes = ' ' . $attributes; }

		if( strpos($attributes, 'border=')===false) {
			$border = ' border="'.$border.'"';
		} // Prevent doubled attributes
		if( strpos($attributes, 'alt=')===false) {
			$alt = ' alt="'.$alt.'"';
		}

		return '<img src="'.$src.'"'.$alt.$align.$title.$height.$width.$border.$attributes.' />';
	}
	/**
	 * Returns a properly formatted XHTML List
	 *
	 * @param array $listitems
	 * @param string $type Can be ul, ol, ...
	 * @param string $style
	 * @return string
	 */
	function getList( $listitems, $type = 'ul', $style='' ) {
		if( $style ) {
			$style = 'style="'.$style.'"';
		}
		$html  = '<' . $type ." $style>\n";
		foreach( $listitems as $item ) {
			$html .= '<li>' . $item . "</li>\n";
		}
		$html  .= '</' . $type .">\n";

		return $html;
	}
	/**
	 * Returns a script tag. The referenced script will be fetched by a
	 * PHP script called "fetchscript.php"
	 * That allows use gzip compression, so bigger Javascripts don't steal our bandwith
	 *
	 * @param string $src The script src reference
	 * @param string $content A Javascript Text to include in a script tag
	 * @return string
	 */
	function scriptTag( $src='', $content = '' ) {
		global $mosConfig_gzip, $mosConfig_live_site;
		if( $src == '' && $content == '' ) return;

		if( $src ) {
			if( isset( $_REQUEST['usefetchscript'])) {
				$use_fetchscript = vmRequest::getBool( 'usefetchscript', 1 );
				vmRequest::setVar( 'usefetchscript', $use_fetchscript, 'session' );
			} else {
				$use_fetchscript = vmRequest::getBool( 'usefetchscript', 1, 'session' );
			}

			$url_params = '';

			if( stristr( $src, 'com_virtuemart' ) && !stristr( $src, '.php' ) && $use_fetchscript ) {
				$urlpos = strpos( $src, '?' );
				if( $urlpos ) {
					$url_params = '&amp;'.substr( $src, $urlpos );
					$src = substr( $src, 0, $urlpos);
				}
				$base_source = str_replace( URL, '', $src );
				$base_source = str_replace( SECUREURL, '', $base_source );
				$base_source = str_replace( '/components/com_virtuemart', '', $base_source);
				$base_source = str_replace( 'components/com_virtuemart', '', $base_source);
				$src = $mosConfig_live_site.'/components/com_virtuemart/fetchscript.php?gzip='.$mosConfig_gzip.'&amp;subdir[0]='.dirname( $base_source ) . '&amp;file[0]=' . basename( $src );
			}

			return '<script src="'.$src.@$url_params.'" type="text/javascript"></script>'."\n";
		}

		if( $content ) {
			return "<script type=\"text/javascript\">\n".$content."\n</script>\n";
		}

	}
	/**
	 * Returns a link tag
	 *
	 * @param string $href
	 * @param string $type
	 * @param string $rel
	 * @return string
	 */
	function linkTag( $href, $type='text/css', $rel = 'stylesheet', $media="screen, projection" ) {
		global $mosConfig_gzip, $mosConfig_live_site;
		if( isset( $_REQUEST['usefetchscript'])) {
			$use_fetchscript = vmRequest::getBool( 'usefetchscript', 1 );
			vmRequest::setVar( 'usefetchscript', $use_fetchscript, 'session' );
		} else {
			$use_fetchscript = vmRequest::getBool( 'usefetchscript', 1, 'session' );
		}
		if( stristr( $href, 'com_virtuemart' ) && $use_fetchscript) {
			$base_href = str_replace( URL, '', $href );
			$base_href = str_replace( SECUREURL, '', $base_href );
			$base_href = str_replace( 'components/com_virtuemart/', '', $base_href);
			$href = $mosConfig_live_site.'/components/com_virtuemart/fetchscript.php?gzip='.$mosConfig_gzip.'&amp;subdir[0]='.dirname( $base_href ) . '&amp;file[0]=' . basename( $href );
		}
		return '<link type="'.$type.'" href="'.$href.'" rel="'.$rel.'"'.(empty($media)?'':' media="'.$media.'"').' />'."\n";

	}
	/**
	* Writes a "Save Ordering" Button
	* @param int the number of rows
	*/
	function getSaveOrderButton( $num_rows, $funcname='reorder') {
		global $mosConfig_live_site;
		$n = $num_rows-1;
		$html = '<a href="javascript: document.adminForm.func.value = \''.$funcname.'\'; saveorder( '.$n.' );">
				<img src="'.$mosConfig_live_site.'/administrator/images/filesave.png" border="0" width="16" height="16" alt="'.JText::_('VM_SORT_SAVE_ORDER').'" /></a>';
		$html .= '<a href="javascript: if( confirm( \''.addslashes(JText::_('VM_SORT_ALPHA_CONFIRM')).'\')) { document.adminForm.func.value = \''.$funcname.'\'; document.adminForm.task.value=\'sort_alphabetically\'; document.adminForm.submit(); }">
				<img src="'.IMAGEURL.'/ps_image/sort_a-z.gif" border="0" width="16" height="16" alt="'.JText::_('VM_SORT_ALPHA').'" /></a>';

		return $html;
	}
	function getOrderingField( $ordering ) {

		return '<input type="text" name="order[]" size="5" value="'. $ordering .'" class="text_area" style="text-align: center" />';

	}

	function getYesNoIcon( $condition, $pos_alt = "Published", $neg_alt = "Unpublished" ) {
		global $mosConfig_live_site;
		if( $condition===true || strtoupper( $condition ) == "Y" || $condition == '1' ) {
			return '<img src="'.$mosConfig_live_site.'/administrator/images/tick.png" border="0" alt="'.$pos_alt.'" />';
		}
		else {
			return '<img src="'.$mosConfig_live_site.'/administrator/images/publish_x.png" border="0" alt="'.$neg_alt.'" />';
		}
	}
	/**
	* @param int The row index
	* @param int The record id
	* @param boolean
	* @param string The name of the form element
	* @return string
	*/
	function idBox( $rowNum, $recId, $checkedOut=false, $name='cid' ) {
		if ( $checkedOut ) {
			return '';
		} else {
			return '<input type="checkbox" id="cb'.$rowNum.'" name="'.$name.'[]" value="'.$recId.'" onclick="isChecked(this.checked);" />';
		}
	}

	/**
	 * Manipulates an array and fills the $index with selected="selected"
	 * Indexes within $disableArr will be filled with disabled="disabled"
	 *
	 * @param array $arr
	 * @param int $index
	 * @param string $att
	 * @param array $disableArr
	 */
	function setSelectedArray( &$arr, $index, $att='selected', $disableArr=array() ) {
		if( !isset($arr[$index])) {
			return;
		}
		foreach( $arr as $key => $val ) {
			$arr[$key] = '';
			if( $key == $index ) {
				$arr[$key] = $att.'="'.$att.'"';
			}
			elseif( in_array( $key, $disableArr )) {
				$arr[$key] = 'disabled="disabled"';
			}
		}
	}

	/**
	 * tests for template/default pathway arrow separator
	 * @author FTW Stroker
	 * @static
	 * @return string The separator for the pathway breadcrumbs
	 */
	function pathway_separator() {
		global $vm_mainframe,$mainframe, $mosConfig_absolute_path, $mosConfig_live_site;
		$imgPath =  'templates/' . $mainframe->getTemplate() . '/images/arrow.png';
		if (file_exists( "$mosConfig_absolute_path/$imgPath" )){
			$img = '<img src="' . $mosConfig_live_site . '/' . $imgPath . '" border="0" alt="arrow" />';
		} else {
			$imgPath = '/images/M_images/arrow.png';
			if (file_exists( $mosConfig_absolute_path . $imgPath )){
				$img = '<img src="' . $mosConfig_live_site . '/images/M_images/arrow.png" alt="arrow" />';
			} else {
				$img = '&gt;';
			}
		}
		return $img;
	}

	/**
	 * Function to include the MooTools JS scripts in the HTML document
	 * http://mootools.net
	 * @static
	 * @since VirtueMart 1.1.0
	 *
	 */
	function loadMooTools( $version='' ) {
		global $mosConfig_live_site, $vm_mainframe;
		if( !defined( "_MOOTOOLS_LOADED" )) {
			if( $version  == '' ) {
				$version = 'mootools-release-1.11.js';
			}
			$vm_mainframe->addScriptDeclaration( 'var cart_title = "'.JText::_('VM_CART_TITLE').'";var ok_lbl="'.JText::_('CMN_CONTINUE').'";var cancel_lbl="'.JText::_('CMN_CANCEL').'";var notice_lbl="'.JText::_('PEAR_LOG_NOTICE').'";var live_site="'.$mosConfig_live_site.'";' );
			$vm_mainframe->addScript( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/mootools/'.$version );
			$vm_mainframe->addScript( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/mootools/mooPrompt.js' );
			$vm_mainframe->addStyleSheet( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/mootools/mooPrompt.css' );

			define ( "_MOOTOOLS_LOADED", "1" );
		}

	}
	/**
	 * Function to load the javascript and stylsheet files for Slimbox,
	 * a Lightbox derivate with mootools and prototype.lite
	 * @author http://www.digitalia.be/software/slimbox
	 *
	 * @param boolean $print
	 */
	function loadSlimBox( ) {
		global $mosConfig_live_site, $vm_mainframe;
		if( !defined( '_SLIMBOX_LOADED' )) {

			vmCommonHTML::loadMooTools();

			$vm_mainframe->addScriptDeclaration( 'var slimboxurl = \''.$mosConfig_live_site.'/components/'. VM_COMPONENT_NAME .'/js/slimbox/\';');
			$vm_mainframe->addScript( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/slimbox/js/slimbox.js' );
			$vm_mainframe->addStyleSheet( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/slimbox/css/slimbox.css' );

			define ( '_SLIMBOX_LOADED', '1' );
		}
	}

	/**
	 * Prototype is a Javascript framework
	 * @author http://prototype.conio.net/
	 *
	 */
	function loadPrototype( ) {
		global $vm_mainframe, $mosConfig_live_site;
		if( !defined( "_PROTOTYPE_LOADED" )) {
			$vm_mainframe->addScript( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/prototype/prototype.js' );
			define( '_PROTOTYPE_LOADED', 1 );
		}

	}

	/**
	 * Loads the CSS and Javascripts needed to run the Greybox
	 * Source: http://orangoo.com/labs/?page_id=5
	 *
	 */
	function loadGreybox( ) {
		global $mosConfig_live_site, $vm_mainframe;
		if( !defined( '_GREYBOX_LOADED' )) {

			$vm_mainframe->addScriptDeclaration( 'var GB_ROOT_DIR = \''.$mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/greybox/\';', 'top' );
			$vm_mainframe->addScript( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/greybox/AJS.js' );
			$vm_mainframe->addScript( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/greybox/AJS_fx.js' );
			$vm_mainframe->addScript( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/greybox/gb_scripts.js' );
			$vm_mainframe->addStyleSheet( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/greybox/gb_styles.css' );

			define ( '_GREYBOX_LOADED', '1' );
		}
	}

	/**
	* Loads all necessary script files for Tigra Tree Menu
	* @static
	* @since VirtueMart 1.1.0
	*/
	function loadTigraTree( ) {
		global $mosConfig_live_site, $vm_mainframe;
		if( !defined( "_TIGRATREE_LOADED" )) {
//			if( vmIsJoomla( '1.5' )) {
				$js_src = $mosConfig_live_site.'/modules/mod_virtuemart';
//			} else {
//				$js_src = $mosConfig_live_site.'/modules';
//			}
			$vm_mainframe->addScript( $js_src .'/tigratree/tree_tpl.js.php' );
			$vm_mainframe->addScript( $js_src .'/tigratree/tree.js' );

			define ( "_TIGRATREE_LOADED", "1" );
		}
	}

	function loadYUI( ) {
		global $mosConfig_live_site, $vm_mainframe;
		if( !defined( "_YUI_LOADED" )) {
			$vm_mainframe->addScript( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/extjs2/yui-utilities.js' );
			define ( "_YUI_LOADED", "1" );
		}
	}
	function loadExtjs() {
		global $mosConfig_live_site, $vm_mainframe;
		vmCommonHTML::loadYUI();
		if( !defined( "_EXTJS_LOADED" )) {

			$vm_mainframe->addScript( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/extjs2/ext-yui-adapter.js' );
			$vm_mainframe->addScript( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/extjs2/ext-all.js' );
			$vm_mainframe->addScriptDeclaration( 'Ext.BLANK_IMAGE_URL = "'.$mosConfig_live_site.'/components/'. VM_COMPONENT_NAME .'/js/extjs2/images/default/s.gif";', 'bottom' );
			$vm_mainframe->addStyleSheet( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/extjs2/css/ext-all.css' );
			$vm_mainframe->addStyleSheet( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/extjs2/css/xtheme-gray.css' );
			define ( "_EXTJS_LOADED", "1" );
		}
	}
	/**
	 * Returns a properly formatted image link that opens a LightBox2/Slimbox
	 *
	 * @param string $image_link Can be the image src or a complete image tag
	 * @param string $text The Link Text, e.g. 'Click here!'
	 * @param string $title The Link title, will be used as Image Caption
	 * @param string $image_group The image group name when you want to use the gallery functionality
	 * @param string $mootools Set to 'true' if you're using slimbox or another MooTools based image viewing library
	 * @return string
	 */
	function getLightboxImageLink( $image_link, $text, $title='', $image_group='' ) {

		vmCommonHTML::loadSlimBox();

		if( $image_group ) {
			$image_group = '['.$image_group.']';
		}
		$link = vmCommonHTML::hyperLink( $image_link, $text, '', $title, 'rel="lightbox'.$image_group.'"' );

		return $link;
	}

	function getGreyboxPopUpLink( $url, $text, $target='_blank', $title='', $attributes='', $height=500, $width=600, $no_js_url='' ) {
		vmCommonHTML::loadGreybox();
		if( $no_js_url == '') {
			$no_js_url = $url;
		}
		$link = vmCommonHTML::hyperLink( $no_js_url, $text, $target, $title, $attributes.' onclick="try{ if( !parent.GB ) return GB_showCenter(\''.$title.'\', \''.$url.'\', '.$height.', '.$width.');} catch(e) { }"' );

		return $link;
	}
	/**
	 * Returns a div element of the class "shop_error"
	 * containing $msg to print out an error
	 *
	 * @param string $msg
	 * @return string HTML code
	 */
	function getInfoField( $msg ) {

		$html = '<div class="shop_info">'.$msg.'</div>';
		return $html;
	}
	/**
	 * Returns a div element to indicate success or failure of a function execution after an ajax call
	 * and a div element with all the log messages
	 *
	 * @param boolean $success
	 * @param vmLog_Display $vmLogger
	 */
	function getSuccessIndicator( $success, $vmDisplayLogger ) { /*@MWM1*/

		echo '<div id="successIndicator" style="display:none;">';
		if( $success) {
			echo 'Success';
		}
		else {
			echo 'Failure';
		}
		echo '</div>';
		echo '<div id="vmLogResult">';
		$vmDisplayLogger->printLog(); /*@MWM1: Log/Debug enhancements*/
		echo '</div>';

	}
	/**
	 * Returns a div element of the class "shop_error"
	 * containing $msg to print out an error
	 *
	 * @param string $msg
	 * @return string HTML code
	 */
	function getErrorField( $msg ) {

		$html = '<div class="shop_error">'.$msg.'</div>';
		return $html;
	}
	/**
	 * Writes a PDF icon
	 *
	 * @param string $link
	 * @param boolean $use_icon
	 */
	function PdfIcon( $link, $use_icon=true ) {
		global  $mosConfig_live_site;
		if ( PSHOP_PDF_BUTTON_ENABLE == '1' && !JRequest::getVar( 'pop')  ) {
			$link .= '&amp;pop=1';
			if ( $use_icon ) {
				$text = vmCommonHTML::ImageCheck( 'pdf_button.png', '/images/M_images/', NULL, NULL, JText::_('CMN_PDF'), JText::_('CMN_PDF') );
			} else {
				$text = JText::_('CMN_PDF') .'&nbsp;';
			}
			return vmPopupLink($link, $text, 640, 480, '_blank', JText::_('CMN_PDF'));
		}
	}

	/**
	 * Writes an Email icon
	 *
	 * @param string $link
	 * @param boolean $use_icon
	 */
	function EmailIcon( $product_id, $use_icon=true ) {
		global  $mosConfig_live_site, $sess;
		if ( @VM_SHOW_EMAILFRIEND == '1' && !JRequest::getVar( 'pop' ) && $product_id > 0  ) {
			$link = $sess->url( 'index2.php?page=shop.recommend&amp;product_id='.$product_id.'&amp;pop=1'.(vmIsJoomla('1.5') ? '&amp;tmpl=component' : '') );
			if ( $use_icon ) {
				$text = vmCommonHTML::ImageCheck( 'emailButton.png', '/images/M_images/', NULL, NULL, JText::_('CMN_EMAIL'), JText::_('CMN_EMAIL') );
			} else {
				$text = '&nbsp;'. JText::_('CMN_EMAIL');
			}
			return vmPopupLink($link, $text, 640, 480, '_blank', JText::_('CMN_EMAIL'), 'screenX=100,screenY=200');
		}
	}

	function PrintIcon( $link='', $use_icon=true, $add_text='' ) {
		global  $mosConfig_live_site, $mosConfig_absolute_path, $cur_template, $Itemid;
		if ( @VM_SHOW_PRINTICON == '1' ) {
			if( !$link ) {
				$query_string = str_replace( 'only_page=1', 'only_page=0', vmAmpReplace(JRequest::getVar('QUERY_STRING')) );
				$link = 'index2.php?'.$query_string.'&amp;pop=1&amp;tmpl=component';
			}
			// checks template image directory for image, if non found default are loaded
			if ( $use_icon ) {
				$text = vmCommonHTML::ImageCheck( 'printButton.png', '/images/M_images/', NULL, NULL, JText::_('CMN_PRINT'), JText::_('CMN_PRINT') );
				$text .= shopMakeHtmlSafe($add_text);
			} else {
				$text = '|&nbsp;'. JText::_('CMN_PRINT'). '&nbsp;|';
			}
			$isPopup = JRequest::getVar( 'pop' );
			if ( $isPopup ) {
				// Print Preview button - used when viewing page
				$html = '<span class="vmNoPrint">
				<a href="javascript:void(0)" onclick="javascript:window.print(); return false;" title="'. JText::_('CMN_PRINT').'">
				'. $text .'
				</a></span>';
				return $html;
			} else {
				// Print Button - used in pop-up window
				return vmPopupLink($link, $text, 640, 480, '_blank', JText::_('CMN_PRINT'));
			}
		}

	}
	/**
	* Checks to see if an image exists in the current templates image directory
 	* if it does it loads this image.  Otherwise the default image is loaded.
	* Also can be used in conjunction with the menulist param to create the chosen image
	* load the default or use no image
	*/
	function ImageCheck( $file, $directory='/images/M_images/', $param=NULL, $param_directory='/images/M_images/', $alt=NULL, $name=NULL, $type=1, $align='middle', $title=NULL, $admin=NULL ) {
		global $mosConfig_absolute_path, $mosConfig_live_site, $mainframe;

		$cur_template = $mainframe->getTemplate();

		$name 	= ( $name 	? ' name="'. $name .'"' 	: '' );
		$title 	= ( $title 	? ' title="'. $title .'"' 	: '' );
		$alt 	= ( $alt 	? ' alt="'. $alt .'"' 		: ' alt=""' );
		$align 	= ( $align 	? ' align="'. $align .'"' 	: '' );

		// change directory path from frontend or backend
		if ($admin) {
			$path 	= '/administrator/templates/'. $cur_template .'/images/';
		} else {
			$path 	= '/templates/'. $cur_template .'/images/';
		}

		if ( $param ) {
			$image = $mosConfig_live_site. $param_directory . $param;
			if ( $type ) {
				$image = '<img src="'. $image .'" '. $alt . $name . $align .' border="0" />';
			}
		} else if ( $param == -1 ) {
			$image = '';
		} else {
			if ( file_exists( $mosConfig_absolute_path . $path . $file ) ) {
				$image = $mosConfig_live_site . $path . $file;
			} else {
				// outputs only path to image
				$image = $mosConfig_live_site. $directory . $file;
			}

			// outputs actual html <img> tag
			if ( $type ) {
				$image = '<img src="'. $image .'" '. $alt . $name . $title . $align .' border="0" />';
			}
		}

		return $image;
	}
	/**
	 * this function parses all the text through all content plugins
	 *
	 * @param string $text
	 * @param string $type
	 */
	function ParseContentByPlugins( $text, $type = 'content' ) {
		global $_MAMBOTS;
		if( VM_CONTENT_PLUGINS_ENABLE == '1') {
//			} elseif( vmIsJoomla('1.5')) {
				$params 	   =& $GLOBALS['mainframe']->getParams('com_content');
				$dispatcher	   =& JDispatcher::getInstance();
				JPluginHelper::importPlugin($type);
				$row = new stdClass();
				$row->text = $text;
				$results = $dispatcher->trigger('onPrepareContent', array (&$row, & $params, 0 ));
				$text = $row->text;
//			}
		}
		return $text;

	}

        /**
         * This class allows us to create fieldsets like in Community builder
         * @author Copyright 2004 - 2005 MamboJoe/JoomlaJoe, Beat and CB team
         *
         * @param array $arr
         * @param string $tag_name
         * @param string $tag_attribs
         * @param string $key
         * @param string $text
         * @param mixed $selected
         * @param mixed $required
         * @return string HTML form code
         */
        // begin class vmCommonHTML extends mosHTML {

        function radioListArr( &$arr, $tag_name, $tag_attribs, $key, $text, $selected, $required=0 ) {
                reset( $arr );
                $html = array();
                $n=count( $arr );
                for ($i=0; $i < $n; $i++ ) {
                        $k = stripslashes($arr[$i]->$key);
                        $t = stripslashes($arr[$i]->$text);
                        $id = isset($arr[$i]->id) ? $arr[$i]->id : null;

                        $extra = '';
                        $extra .= $id ? " id=\"" . $arr[$i]->id . "\"" : '';
                        if (is_array( $selected )) {
                                foreach ($selected as $obj) {
                                        $k2 = stripslashes($obj->$key);
                                        if ($k == $k2) {
                                                $extra .= " checked=\"checked\"";
                                                break;
                                        }
                                }
                        } else {
                                $extra .= ($k == stripslashes($selected) ? "  checked=\"checked\"" : '');
                        }
                        $html[] = "<input type=\"radio\" name=\"$tag_name\" id=\"".$tag_name."_field$i\" $tag_attribs value=\"".$k."\"$extra /> " . "<label for=\"".$tag_name."_field$i\">$t</label>";
                }
                return $html;
        }
        function radioList( $arr, $tag_name, $tag_attribs, $key, $text, $selected, $required=0 ) {
                return "\n\t".implode("\n\t ", vmCommonHTML::radioListArr( $arr, $tag_name, $tag_attribs, $key, $text, $selected, $required ))."\n";
        }
        function radioListTable( $arr, $tag_name, $tag_attribs, $key, $text, $selected, $cols=0, $rows=1, $size=0, $required=0 ) {
                $cellsHtml = vmCommonHTML::radioListArr( $arr, $tag_name, $tag_attribs, $key, $text, $selected, $required );
                return vmCommonHTML::list2Table( $cellsHtml, $cols, $rows, $size );
        }

		/**
		* Writes a yes/no radio list
		* @param string The value of the HTML name attribute
		* @param string Additional HTML attributes for the <select> tag
		* @param mixed The key that is selected
		* @returns string HTML for the radio list
		*/
		function yesnoRadioList( $tag_name, $tag_attribs, $key, $text, $selected, $yes='', $no='' ) {
			
			$yes = ( $yes=='' ) ? JText::_('VM_ADMIN_CFG_YES') : $yes;
			$no = ( $no=='' ) ? JText::_('VM_ADMIN_CFG_NO') : $no;
			$arr = array(
				vmCommonHTML::makeOption( '0', $no ),
				vmCommonHTML::makeOption( '1', $yes )
			);

			return vmCommonHTML::radioList( $arr, $tag_name, $tag_attribs, $key, $text, $selected );
		}

		function makeOption( $value, $text='', $value_name='value', $text_name='text' ) {
			$obj = new stdClass;
			$obj->$value_name = $value;
			$obj->$text_name = trim( $text ) ? $text : $value;
			return $obj;
		}

        function selectList( $arr, $tag_name, $tag_attribs, $key, $text, $selected, $required=0 ) {
                
                reset( $arr );
                $html = "\n<select name=\"$tag_name\" id=\"".str_replace('[]', '', $tag_name)."\" $tag_attribs>";
                if(!$required) $html .= "\n\t<option value=\"\">".JText::_('VM_SELECT')."</option>";
                $n=count( $arr );
                for ($i=0; $i < $n; $i++ ) {
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
        
        function checkboxListArr( $arr, $tag_name, $tag_attribs,  $key='value', $text='text',$selected=null, $required=0  ) {
                
                reset( $arr );
                $html = array();
                $n=count( $arr );
                for ($i=0; $i < $n; $i++ ) {
                        $k = $arr[$i]->$key;
                        $t = $arr[$i]->$text;
                        $id = isset($arr[$i]->id) ? $arr[$i]->id : null;

                        $extra = '';
                        $extra .= $id ? " id=\"" . $arr[$i]->id . "\"" : '';
                        if (is_array( $selected )) {
                                foreach ($selected as $obj) {
                                        $k2 = $obj->$key;
                                        if ($k == $k2) {
                                                $extra .= " checked=\"checked\"";
                                                break;
                                        }
                                }
                        } else {
                                $extra .= ($k == $selected ? " checked=\"checked\"" : '');
                        }
                        $tmp = "<input type=\"checkbox\" name=\"$tag_name\" id=\"".str_replace('[]', '', $tag_name)."_field$i\" value=\"".$k."\"$extra $tag_attribs />" . "<label for=\"".str_replace('[]', '', $tag_name)."_field$i\">";
                        $tmp .= JText::_($t);
                        $tmp .= "</label>";
                        $html[] = $tmp;
                }
                return $html;
        }
        function checkboxList( $arr, $tag_name, $tag_attribs,  $key='value', $text='text',$selected=null, $required=0 ) {
                return "\n\t".implode("\n\t", vmCommonHTML::checkboxListArr( $arr, $tag_name, $tag_attribs,  $key, $text,$selected, $required ))."\n";
        }
        function checkboxListTable( $arr, $tag_name, $tag_attribs,  $key='value', $text='text',$selected=null, $cols=0, $rows=0, $size=0, $required=0 ) {
                $cellsHtml = vmCommonHTML::checkboxListArr( $arr, $tag_name, $tag_attribs,  $key, $text,$selected, $required );
                return vmCommonHTML::list2Table( $cellsHtml, $cols, $rows, $size );
        }
        // private methods:
        function list2Table ( $cellsHtml, $cols, $rows, $size ) {
                $cells = count($cellsHtml);
                if ($size == 0) {
                        $localstyle = ""; //" style='width:100%'";
                } else {
                        $size = (($size-($size % 3)) / 3  ) * 2; // int div  3 * 2 width/heigh ratio
                        $localstyle = " style='width:".$size."em;'";
                }
                $return="";
                if ($cells) {
                        if ($rows) {
                                $return = "\n\t<table class='vmMulti'".$localstyle.">";
                                $cols = ($cells-($cells % $rows)) / $rows;      // int div
                                if ($cells % $rows) $cols++;
                                $lineIdx=0;
                                for ($lineIdx=0 ; $lineIdx < min($rows, $cells) ; $lineIdx++) {
                                        $return .= "\n\t\t<tr>";
                                        for ($i=$lineIdx ; $i < $cells; $i += $rows) {
                                                $return .= "<td>".$cellsHtml[$i]."</td>";
                                        }
                                        $return .= "</tr>\n";
                                }
                                $return .= "\t</table>\n";
                        } else if ($cols) {
                                $return = "\n\t<table class='vmMulti'".$localstyle.">";
                                $idx=0;
                                while ($cells) {
                                        $return .= "\n\t\t<tr>";
                                        for ($i=0, $n=min($cells,$cols); $i < $n; $i++, $cells-- ) {
                                                $return .= "<td>".$cellsHtml[$idx++]."</td>";
                                        }
                                        $return .= "</tr>\n";
                                }
                                $return .= "\t</table>\n";
                        } else {
                                $return = "\n\t".implode("\n\t ", $cellsHtml)."\n";
                        }
                }
                return $return;
	}


	// end class vmCommonHTML, thanks folks!
}

/**
 * Utility function to provide ToolTips
 *
 * @param string $tooltip ToolTip text
 * @param string $title The Box title
 * @param string $image
 * @param int $width
 * @param string $text
 * @param string $href
 * @param string $link
 * @return string HTML code for ToolTip
 */
function vmToolTip( $tooltip, $title='Tip!', $image = "{mosConfig_live_site}/images/M_images/con_info.png", $width='350', $text='', $href='#', $link=false ) {
	global $mosConfig_live_site, $database;
	//defined('vmToolTipCalled') or define('vmToolTipCalled',1);
	if(!defined( 'vmJToolTipCalled')) { 
		$tooltipArray = array('className'=>'VMImageTip');
		JHTML::_('behavior.tooltip','.vmToolTip',$tooltipArray);
		define('vmJToolTipCalled', "1");
	}

	$tooltip = str_replace('"','&quot;',$tooltip);
	if($database !=null){
		$tooltip = $database->getEscaped($tooltip);
	}
	
	$tooltip = str_replace("&#039;","\&#039;",$tooltip);
    //$tooltip = str_replace('\n',"",$tooltip);
    $tooltip = stripcslashes($tooltip);
	
	if ( !empty($width) ) {
		$width = ',WIDTH, -'.$width;
	}
	if ( $title ) {
		//$title = ',TITLE,\''.$title .'\'';
	}
	$image = str_replace( "{mosConfig_live_site}", $mosConfig_live_site, $image);
	if( $image != '' ) {
		$text 	= vmCommonHTML::imageTag( $image, '', 'top' ). '&nbsp;'.$text;
	}

	$style = 'style="text-decoration: none; color: #333;"';
	if ( $href ) {
		$style = '';
	}
	if ( $link ) {
		$tip = vmCommonHTML::hyperLink( $href, $text, '','', "onmouseover=\"Tip( '$tooltip' $width $title );\" onmouseout=\"UnTip()\" ". $style );
	} else {
		//$tip = "<span onmouseover=\"Tip( '$tooltip' $width $title );\" onmouseout=\"UnTip()\" ". $style .">". $text ."</span>";
		$tip = "<span class='vmToolTip' title=\"".$title."::".$tooltip."\" ".$style.'>'.$text.'</span>';
	}

	return $tip;
}
/**
 * @deprecated
 */
function mm_ToolTip( $tooltip, $title='Tip!', $image = "{mosConfig_live_site}/images/M_images/con_info.png", $width='', $text='', $href='#', $link=false ) { return vmToolTip( $tooltip, $title, $image, $width, $text, $href, $link ); }

/**
 * Utility function to provide persistant HelpToolTips
 *
 * @param unknown_type $tip
 * @param unknown_type $linktext
 */
function vmHelpToolTip( $tip, $linktext = ' [?] ' ) {
        global $mosConfig_live_site;

        if( !defined( 'vmHelpToolTipCalled')) {
                echo '<script type="text/javascript" src="'.$mosConfig_live_site.'/components/com_virtuemart/js/helptip/helptip.js"></script>
                        <link type="text/css" rel="stylesheet" href="'.$mosConfig_live_site.'/components/com_virtuemart/js/helptip/helptip.css" />';
                define('vmHelpToolTipCalled', 1);
        }
        $tip = str_replace( "\n", "",
                        str_replace( "&lt;", "<",
                        str_replace( "&gt;", ">",
                        str_replace( "&amp;", "&",
                        @htmlentities( $tip, ENT_QUOTES )))));
        $varname = 'a'.md5( $tip );
        echo '<script type="text/javascript">//<![CDATA[
        var '.$varname.' = \''.$tip.'\';
        //]]></script>
        ';
        echo '<a class="helpLink" href="?" onclick="showHelpTip(event, '.$varname.'); return false">'.$linktext.'</a>
';
}

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

function mm_showMyFileName( $filename ) {

    if (vmShouldDebug()) { /*@MWM1: Logging/Debugging Enhancements */
        echo vmToolTip( '<div class=\'inputbox\'>Begin of File: '. wordwrap( $filename, 70, '<br />', true ).'</div>');
    }
}
/**
* Wraps HTML Code or simple Text into Javascript
* and uses the noscript Tag to support browsers with JavaScript disabled
**/
function mm_writeWithJS( $textToWrap, $noscriptText ) {
    $text = "";
    if( !empty( $textToWrap )) {
        $text = "<script type=\"text/javascript\">//<![CDATA[
            document.write('".str_replace("\\\"", "\"", addslashes( $textToWrap ))."');
            //]]></script>\n";
    }
    if( !empty( $noscriptText )) {
        $text .= "<noscript>
            $noscriptText
            </noscript>\n";
    }
    return $text;
}

/**
 * A function to create a XHTML compliant and JS-disabled-safe pop-up link
 *
 * @param string $link The HREF attribute
 * @param string $text The link text
 * @param int $popupWidth
 * @param int $popupHeight
 * @param string $target The value of the target attribute
 * @param string $title
 * @param string $windowAttributes
 * @return string
 */
function vmPopupLink( $link, $text, $popupWidth=640, $popupHeight=480, $target='_blank', $title='', $windowAttributes='' ) {
	if( $windowAttributes ) {
		$windowAttributes = ','.$windowAttributes;
	}
	return vmCommonHTML::hyperLink( $link, $text, '', $title, "onclick=\"void window.open('$link', '$target', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=$popupWidth,height=$popupHeight,directories=no,location=no".$windowAttributes."');return false;\"" );

}

/**
 * Removes the empty lines from a address_detail string.
 *
 * @param string $string
 * @return string
 */
function vmRemoveEmptyLines( $string ) {
	return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);
}

/**
 * Creates a formatted address using the store address format
 *
 * @param array $address_details
 * @return string
 */
function vmFormatAddress( $address_details, $use_html=false, $removeEmptyLines=false ) {
	global $vendor_address_format;

	$store_address = str_replace( '{storename}', @$address_details['name'], $vendor_address_format );
	$store_address = str_replace( '{address_1}', @$address_details['address_1'], $store_address );
	$store_address = str_replace( '{address_2}', @$address_details['address_2'], $store_address );
	$store_address = str_replace( '{state}', @$address_details['state'], $store_address );
	$store_address = str_replace( '{statename}', @$address_details['state_name'], $store_address );
	$store_address = str_replace( '{city}', @$address_details['city'], $store_address );
	$store_address = str_replace( '{zip}', @$address_details['zip'], $store_address );
	$store_address = str_replace( '{country}', @$address_details['country'], $store_address );
	$store_address = str_replace( '{phone}', @$address_details['phone'], $store_address );
	$store_address = str_replace( '{email}', @$address_details['email'], $store_address );
	$store_address = str_replace( '{fax}', @$address_details['fax'], $store_address );
	$store_address = str_replace( '{url}', @$address_details['url'], $store_address );

	if( $removeEmptyLines ) {
		$store_address = vmRemoveEmptyLines( $store_address );
	}

	if( $use_html ) {
		$store_address = nl2br( $store_address );
	} else {
		$store_address = strip_tags( $store_address );
	}
	return $store_address;
}
/**
 * Creates meta information
 *
 * @param array $metadata
 * @ $metadata[] = array('type' => type of action, set, append or prepend,
 * 						 'title' => meta key to set e.g. keyword, author etc,
 * 						 'meta' => actual meta data,
 * 						)
 */
function vmSetMetaData($metadata) {
		$document=& JFactory::getDocument();
		foreach($metadata as $meta) {
			switch($meta['type']) {
				case 'append' :
					$prepend = $document->getMetaData($meta['title']);
					$metaval = shopMakeHtmlSafe((($prepend == "") ? '' : $prepend.",") .$meta['meta']);
					break;
				case 'prepend' :
					$append = $document->getMetaData($meta['title']);
					$metaval = shopMakeHtmlSafe($meta['meta'].(($append == "") ? '' : ",".$append));
					break;
				case 'set' :
				default;
					$metaval = shopMakeHtmlSafe($meta['meta']);
			}
			$document->setMetaData($meta['title'],$metaval);
		}
		
	}


?>
