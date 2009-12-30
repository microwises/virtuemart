<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* This file contains the mainframe class for VirtueMart
*
* @version $Id: mainframe.class.php 1786 2009-05-13 13:21:59Z macallf $
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2007-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

class vmMainFrame {
	/**
	 * Array of linked scripts
	 * @var		array
	 * @access   private
	 */
	var $_scripts = array();

	/**
	 * Array of scripts placed in the header
	 * @var  array
	 * @access   private
	 */
	var $_script = array();
	/**
	 * Array of scripts meant as a response for an ajax call
	 * @var  array
	 * @access   private
	 */
	var $_response_scripts = array();
	 /**
	 * Array of linked style sheets
	 * @var	 array
	 * @access  private
	 */
	var $_styleSheets = array();

	/**
	 * Array of included style declarations
	 * @var	 array
	 * @access  private
	 */
	var $_style = array();
	
	function vmMainFrame() {
		if( empty($_SESSION['userstate'])) {
			$_SESSION['userstate'] = array();
		}
	}
	/**
	 * Gets a user state.
	 *
	 * @access	public
	 * @param	string	The path of the state.
	 * @return	mixed	The user state.
	 */
	function getUserState( $key ) 	{
		if( isset($_SESSION['userstate'][$key]) && !is_null($_SESSION['userstate'][$key])) {
			return $_SESSION['userstate'][$key];
		}
		return null;
	}

	/**
	* Sets the value of a user state variable.
	*
	* @access	public
	* @param	string	The path of the state.
	* @param	string	The value of the variable.
	* @return	mixed	The previous state, if one existed.
	*/
	function setUserState( $key, $value ) {
		 $_SESSION['userstate'][$key] = $value;
	}

	/**
	 * We use now the joomla native function
	 * Gets the value of a user state variable.
	 *
	 * @access	public
	 * @param	string	The key of the user state variable.
	 * @param	string	The name of the variable passed in a request.
	 * @param	string	The default value for the variable if not found. Optional.
	 * @param	string	Filter for the variable, for valid values see {@link JFilterInput::clean()}. Optional.
	 * @return	The request user state.
	 */
//	function getUserStateFromRequest( $key, $request, $default = null, $type = 'none' ) {
//
//		$old_state = $this->getUserState( $key );
//		$cur_state = (!is_null($old_state)) ? $old_state : $default;
//		$new_state = vmRequest::getVar($request, null, 'default', $type);
//
//		// Save the new value only if it was set in this request
//		if ($new_state !== null) {
//			$this->setUserState($key, $new_state);
//		} else {
//			$new_state = $cur_state;
//		}
//
//		return $new_state;
//	}
	
	/**
	 * Registers a handler to a particular event group.
	 *
	 * @static
	 * @param	string	The event name.
	 * @param	mixed	The handler, a function or an instance of a event object.
	 * @return	void
	 * @since 1.2.0
	 */
	function registerEvent($event, $handler) {
		$dispatcher =& vmDispatcher::getInstance();
		$dispatcher->register($event, $handler);
	}
	/**
	 * Calls all handlers associated with an event group.
	 *
	 * @static
	 * @param	string	The event name.
	 * @param	array		An array of arguments.
	 * @return	array		An array of results from each function call.
	 * @since		1.2.0
	 */
	function triggerEvent($event, $args=null) {
		$dispatcher =& vmDispatcher::getInstance();
		return $dispatcher->trigger($event, $args);
	}
	 /**
	 * Adds a linked script to the page
	 *
	 * @param	string  $url		URL to the linked script
	 * @param	string  $type		Type of script. Defaults to 'text/javascript'
	 * @access   public
	 */
	function addScript($url, $position = 'middle', $type="text/javascript") {
		static $included_scripts = array();
//		if( vmIsJoomla('1.0') && strstr($_SERVER['PHP_SELF'],'index3.php') || 
//			!vmIsJoomla() && defined('_VM_IS_BACKEND')) {
//			echo vmCommonHTML::scriptTag($url);
//			return;
//		}
		if( isset($included_scripts[$url])) return;
		else $included_scripts[$url] = 1;
		$this->_scripts[] = array( 'url' => $url,
												'content' => '',
												'type' => $type,
												'position' => strtolower($position));
	}

	/**
	 * Adds a script to the page
	 *
	 * @access   public
	 * @param	string  $content   Script
	 * @param	string  $type		Scripting mime (defaults to 'text/javascript')
	 * @param	string  $position	Set to when the script should be loaded. Positions are top, middle, bottom
	 * @return   void
	 */
	function addScriptDeclaration($content, $position = 'middle', $type = 'text/javascript') {
//		if( vmIsJoomla('1.0') && strstr($_SERVER['PHP_SELF'],'index3.php') || 
//			!vmIsJoomla() && defined('_VM_IS_BACKEND')) {
//			echo vmCommonHTML::scriptTag('', $content);
//			return;
//		}
		$this->_scripts[] = array( 'url' => '',
												'content' => $content,
												'type' => strtolower($type),
												'position' => strtolower($position));
	}

	/**
	 * Adds a linked stylesheet to the page
	 *
	 * @param	string  $url	URL to the linked style sheet
	 * @param	string  $type   Mime encoding type
	 * @param	string  $media  Media type that this stylesheet applies to
	 * @access   public
	 */
	function addStyleSheet($url, $type = 'text/css', $media = null, $attribs = array())
	{
//		if( vmIsJoomla('1.0') && (strstr($_SERVER['PHP_SELF'],'index3.php') || strstr($_SERVER['PHP_SELF'],'index2.php')) || 
//			!vmIsJoomla() && defined('_VM_IS_BACKEND') ) {
//			echo vmCommonHTML::linkTag($url, $type, 'stylesheet', $media );
//			return;
//		}
		if( isset($this->_styleSheets[$url])) return;
		$this->_styleSheets[$url]['url']		= $url;
		$this->_styleSheets[$url]['mime']		= $type;
		$this->_styleSheets[$url]['media']		= $media;
		$this->_styleSheets[$url]['attribs']	= $attribs;
	}

	 /**
	 * Adds a stylesheet declaration to the page
	 *
	 * @param	string  $content   Style declarations
	 * @param	string  $type		Type of stylesheet (defaults to 'text/css')
	 * @access   public
	 * @return   void
	 */
	function addStyleDeclaration($content, $type = 'text/css') {
//		if( vmIsJoomla('1.0') && strstr($_SERVER['PHP_SELF'],'index3.php') || 
//			!vmIsJoomla() && defined('_VM_IS_BACKEND')) {
//			echo '<style type="'.$type.'">'.$content.'</style>';
//			return;
//		}
		$this->_style[][strtolower($type)] = $content;
	}
	
	function addResponseScript($content) {
		$this->_response_scripts[] = $content;
	}
	
	function scriptRedirect($location) {
		$this->addResponseScript('document.location=\''.$location.'\'');
	}
	function errorAlert( $text, $action='window.history.go(-1);', $mode=1 ) {
		global $mainframe, $mosConfig_live_site;
	
		$text = nl2br( $text );
		$text = str_replace( '\'', "\'", $text );
		$text = strip_tags( $text );
	
		switch ( $mode ) {
			case 2:
				echo '<script type="text/javascript">'.$action."</script> \n";
				break;
	
			case 1:
			default:
				echo '<script type="text/javascript">alert(\''.$text.'\');'. $action."</script> \n";
				echo '<noscript>';
				echo "<h2>$text</h2>\n<br /><a href=\"$mosConfig_live_site\">$mosConfig_live_site</a>";
				echo '</noscript>';
				break;
		}
	
		$this->close(true);
	}
	function render( $print = false ) {
		global $mainframe, $mosConfig_gzip, $mosConfig_live_site;
		
		foreach ( $this->_style as $style ) {
			$tag = '<style type="'.key($style).'">'.current($style).'</style>';
			if( $print ) {
				echo $tag;
			} else {
				$mainframe->addCustomHeadTag( $tag );
			}
		}
		if( isset( $_REQUEST['usefetchscript'])) {
			$use_fetchscript = vmRequest::getBool( 'usefetchscript', true );
			vmRequest::setVar( 'usefetchscript', $use_fetchscript, 'session' );
		} else {
			$use_fetchscript = vmRequest::getBool( 'usefetchscript', true, 'session' );
		}
		// Gather all the linked Scripts into ONE link
		$i = 0;
		$appendix = '';
		$loadorder = array();
		
		foreach( $this->_scripts as $script ) {
			$src = $script['url'];
			$type = $script['type'];
			$content = $script['content'];
			$position = $script['position'];
			$urlpos = strpos( $src, '?' );			
			$url_params = '';
			$js_file = false;
			$js_statement = false;
			
			if( $urlpos && (stristr( $src, VM_COMPONENT_NAME ) && !stristr( $src, '.php' ) && $use_fetchscript) ) {
				$url_params = '&amp;'.substr( $src, $urlpos );
				$src = substr( $src, 0, $urlpos);
			}
			
			/* Group the JS files together */
			if( stristr( $src, VM_COMPONENT_NAME ) && !stristr( $src, '.php' ) && $use_fetchscript) {
				$base_source = str_replace( $GLOBALS['real_mosConfig_live_site'], '', $src );
				$base_source = str_replace( $GLOBALS['mosConfig_live_site'], '', $base_source );
				$base_source = str_replace( '/components/'.VM_COMPONENT_NAME, '', $base_source);
				$base_source = str_replace( 'components/'.VM_COMPONENT_NAME, '', $base_source);
				$js_file = '&amp;subdir['.$i.']='.dirname( $base_source ) . '&amp;file['.$i.']=' . basename( $src );
				$i++;
			} 
			/* Group the JS statements together */
			else {
				$js_statement = array('type'=>$type, 'src'=>$src, 'content' => $content);
			}
			
			/* Group the statements according to their position */
			/* JS files */
			if ($js_file) {
				if (!isset($loadorder[$position]['js_file']['appendix'])) $loadorder[$position]['js_file']['appendix'] = '';
				$loadorder[$position]['js_file']['appendix'] .= $js_file;
			}
			$js_file = false;
			
			/* JS statements */
			if ($js_statement) {
				$loadorder[$position]['js_statement'][] = $js_statement;
			}
			$js_statement = false;
		}
		
		/* Add the JS to the output */
		$processorder = array('top', 'middle', 'bottom');
		foreach ($processorder as $key => $pos) {
			if (isset($loadorder[$pos])) {
				if (isset($loadorder[$pos]['js_file'])) {
					/* JS files */
			$src = $mosConfig_live_site.'/components/'.VM_COMPONENT_NAME.'/fetchscript.php?gzip='.$mosConfig_gzip;
					$src .= $loadorder[$pos]['js_file']['appendix'];
			$tag = '<script src="'.$src.@$url_params.'" type="text/javascript"></script>';
			if( $print ) {
				echo $tag;
			} else {
				$mainframe->addCustomHeadTag( $tag );
			}
		}
				if (isset($loadorder[$pos]['js_statement'])) {
					/* JS statements */
					foreach ($loadorder[$pos]['js_statement'] AS $statement_key => $otherscript) {
						if( !empty($otherscript['src'])) {
							$tag = '<script type="'.$otherscript['type'].'" src="'.$otherscript['src'].'"></script>';
						} else {
							$tag = '<script type="'.$otherscript['type'].'">'.$otherscript['content'].'</script>';
						}
						if( $print ) {
							echo $tag;
						} else {
							$mainframe->addCustomHeadTag( $tag );
						}
					}
				}
			}
		}

		// Gather all the linked Stylesheets into ONE link
		$i = 0;
		$appendix = '';			
		$url_params = '';
		foreach( $this->_styleSheets as $stylesheet ) {
			$urlpos = strpos( $stylesheet['url'], '?' );			
			
			if( $urlpos ) {
				$url_params .= '&amp;'.substr( $stylesheet['url'], $urlpos );
				$stylesheet['url'] = substr( $stylesheet['url'], 0, $urlpos);
			}
			if( stristr( $stylesheet['url'], VM_COMPONENT_NAME ) && !stristr( $stylesheet['url'], '.php' ) && $stylesheet['media'] == null && $use_fetchscript ) {
				$base_source = str_replace( $GLOBALS['real_mosConfig_live_site'], '', $stylesheet['url'] );
				$base_source = str_replace( $GLOBALS['mosConfig_live_site'], '', $base_source );
				$base_source = str_replace( '/components/'.VM_COMPONENT_NAME, '', $base_source);
				$base_source = str_replace( 'components/'.VM_COMPONENT_NAME, '', $base_source);
				$appendix .= '&amp;subdir['.$i.']='.dirname( $base_source ) . '&amp;file['.$i.']=' . basename( $stylesheet['url'] );
				$i++;
			} else {
				$tag = '<link type="'.$stylesheet['mime'].'" href="'.$stylesheet['url'].'" rel="stylesheet"'.(!empty($stylesheet['media'])?' media="'.$stylesheet['media'].'"':'').' />';
				if( $print ) {
					echo $tag;
				} else {
					$mainframe->addCustomHeadTag( $tag );
				}
			}
		}
		if( $i> 0 ) {
			$src = $mosConfig_live_site.'/components/com_virtuemart/fetchscript.php?gzip='.$mosConfig_gzip;
			$src .= $appendix;
			$tag = '<link href="'.$src.@$url_params.'" type="text/css" rel="stylesheet" />';
			if( $print ) {
				echo $tag;
			} else {
				$mainframe->addCustomHeadTag( $tag );
			}
		}
	}
	
	function close( $exit=false ) {
		global $mosConfig_live_site, $mainframe;
		if( !$exit ) {
			if( defined( 'vmToolTipCalled')) {
				echo vmCommonHTML::scriptTag( $mosConfig_live_site.'/components/'.VM_COMPONENT_NAME.'/js/wz_tooltip.js' );
			}
			if( defined( '_LITEBOX_LOADED')) {
				echo vmCommonHTML::scriptTag( '', 'var prev_onload = document.body.onload; 
													window.onload = function() { if( prev_onload ) prev_onload(); initLightbox(); }' );
			}
			$this->render();
		} else {
			
			if( !empty( $this->_response_scripts )) {
				echo vmCommonHTML::scriptTag('', implode("\n", $this->_response_scripts ));
			}
			if( is_callable( array( $mainframe, 'close' ) ) ) {				
				$mainframe->close();
			} else {
				session_write_close();
				exit;
			}
		}
	}

	/**
	 * Appends items to the CMS pathway
	 *
	 * @param	string  $pathway_items	Array of pathway objects ($name, $link)
	 * @access   public
	 */
	function vmAppendPathway( $pathway ) {
		global $mainframe;
		
		// Remove the link on the last pathway item
		$pathway[ count($pathway) - 1 ]->link = '';

//		if( vmIsJoomla('1.5') ) {
			$cmsPathway =& $mainframe->getPathway();
			foreach( $pathway AS $item) {
				$cmsPathway->addItem( $item->name, str_replace('&amp;', '&', $item->link) );
			}
//		} else {
//			$tpl = vmTemplate::getInstance();
//			$tpl->set( 'pathway', $pathway );
//			$vmPathway = $tpl->fetch( 'common/pathway.tpl.php' );
//			$mainframe->appendPathWay( $vmPathway );
//		}
	}
	function setPageTitle( $title ) {
		global $mainframe;
		$title = strip_tags(str_replace('&nbsp;',' ', $title));
		$title = trim($title);
		if( defined( '_VM_IS_BACKEND')) {
			echo vmCommonHTML::scriptTag('', "//<![CDATA[
			var vm_page_title=\"".str_replace('"', '\"', $title )."\";
			try{ parent.document.title = vm_page_title; } catch(e) { document.title =vm_page_title; } 
			//]]>
			");
			
		}
		else{
//		elseif( vmIsJoomla('1.5') ) {
			$document=& JFactory::getDocument();
			$document->setTitle($title);
//		} else {
//			$mainframe->setPageTitle( $title );
		}
	}
	
	
	/**
	 * Returns a pathway item
	 *
	 * @param	string	$name
	 * @param	string	$link
	 * @access   public
	 * @return	object	A pathway item object
	 */
	function vmPathwayItem( $name, $link = '' ) {
		$item = new stdClass();
		$item->name = vmHtmlEntityDecode( $name );
		$item->link = $link;
		
		return $item;
	}
	
}
?>
