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
if( !class_exists('mosMenuBar')) {
	require_once( $mosConfig_absolute_path."/administrator/includes/menubar.html.php" );
}

if( !class_exists('JToolbar')) {
	class JToolBar {
		function &getInstance($text) {
			$tb = new JToolBar();
			return $tb;
		}
		function appendButton( $type, $html ) {
			echo $html;
		}
	}
}
/**
 * Utility Class for the Standard Administration Toolbar
 * @author soeren
 * 
 */
class vmMenuBar extends mosMenuBar {

	/**
	* Writes the common 'new' icon for the button bar
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function addNew( $task='new', $page, $alt='', $formName="adminForm" ) {
		global  $limit;
		if( $alt == '') {
			$alt = JText::_('CMN_NEW');
		}
		$bar =& JToolBar::getInstance('toolbar');
		
		$bar->appendButton('Custom', '<td>
			<a class="toolbar" href="javascript:document.adminForm.toggle.onclick();vm_submitButton(\''.$task.'\',\''.$formName.'\',\''.$page.'\');">'
			. '<div class="vmicon-32-'. $task.'" type="Standard"></div>'
			. $alt
		.'</a>
		</td>');

	}
	/**
	* Writes a save button for a given option
	* Save operation leads to a save and then close action
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function save( $task='save', $alt='' ) {
		
		if( $alt == '') {
			$alt = JText::_('CMN_SAVE');
		}
		
		$bar =& JToolBar::getInstance('toolbar');
		
		$bar->appendButton('Custom', '<td class="button">
		<a class="toolbar" href="javascript:submitbutton(\''. $task.'\');">
		<div class="vmicon-32-'. $task.'" type="Standard"></div>'
		. $alt .'
		</a>
		</td>' );
		
	}
	/**
	* Writes a back button
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function back( $task='back', $alt='' ) {
		
		if( $alt == '') {
			$alt = JText::_('BACK');
		}
		
		$bar =& JToolBar::getInstance('toolbar');
		
		$bar->appendButton('Custom', '<td class="button">
		<a class="toolbar" href="#" onclick="window.history.back();return false;">
		<div class="vmicon-32-'. $task.'" type="Standard"></div>'
		. $alt .'
		</a>
		</td>' );
		
	}
	/**
	* Writes a save button for a given option
	* Save operation leads to a save and then close action
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function apply( $task='apply', $alt='' ) {
		global $page;
		if( $alt == '') {
			$alt = JText::_('E_APPLY');
		}
		$bar =& JToolBar::getInstance('toolbar');
		
		$bar->appendButton('Custom', "<td>
		<a class=\"toolbar\" href=\"javascript:vm_submitButton('$task', 'adminForm', '$page');\">
		<div class=\"vmicon-32-$task\" type=\"Standard\"></div>
		$alt</a>
		</td>" );
		
	}
	/**
	* Writes a common 'publish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function publishList( $func, $task='publish', $alt='' ) {
		
		if ($alt=='') {
			$alt=JText::_('CMN_PUBLISH');
		}

		$bar =& JToolBar::getInstance('toolbar');
		
     	$bar->appendButton( 'Custom', '<td>
		<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert(\'' . str_replace("'","\\'",JText::_('CMN_PLEASESELECT_PUBLISH')) . '\'); } else {vm_submitListFunc(\''. $task. '\', \'adminForm\', \''. $func .'\');}" >
		<div class="vmicon-32-'. $task.'" type="Standard"></div>'
		 . $alt .'
		</a>
		</td>' );
     	
	}
	/**
	* Writes a common 'unpublish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function unpublishList( $func, $task='unpublish', $alt='' ) {
		
		if ($alt=='') {
			$alt=JText::_('CMN_UNPUBLISH');
		}

		$bar =& JToolBar::getInstance('toolbar');
		
     	$bar->appendButton( 'Custom', '<td>
		<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert(\'' . str_replace("'","\\'",JText::_('CMN_PLEASESELECT_UNPUBLISH')) . '\'); } else {vm_submitListFunc(\''. $task. '\', \'adminForm\', \''. $func .'\');}" >
		<div class="vmicon-32-'. $task.'" type="Standard"></div>'
		 . $alt .'
		</a>
		</td>' );
	}
	/**
	* Writes a common 'delete' button for a list of records
	* @param string  Postscript for the 'are you sure' message
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function deleteList( $func, $task='remove', $alt='' ) {
		
		if( $alt == '') {
			$alt = JText::_('E_REMOVE');
		}
		$bar =& JToolBar::getInstance('toolbar');
		
		$bar->appendButton( 'Custom', '<td><a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert(\'' . str_replace("'","\\'",JText::_('CMN_PLEASESELECT_DELETE')) . '\'); } else if (confirm(\'' . str_replace("'","\\'",JText::_('CMN_CONFIRM_DELETE_ITEMS')) .'\')){ vm_submitListFunc(\''. $task.'\', \'adminForm\', \''. $func.'\' );}">
			<div class="vmicon-32-'. $task.'" type="Standard"></div>'
			. $alt .'
		</a></td>' );
		
	}
	
	/**
	* Writes a cancel button and invokes a cancel operation (eg a checkin)
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function cancel( $task='cancel', $alt='' ) {
		global $page;
		if( $alt == '') {
			$alt = JText::_('CMN_CANCEL');
		}
		$no_menu = JRequest::getVar( 'no_menu' );
		$bar =& JToolBar::getInstance('toolbar');
		
		if ($page == "store.store_form") { $my_page = "store.index"; }
		elseif ($page == "admin.user_address_form") { $my_page = "admin.user_form"; }
		elseif ($page == "admin.show_cfg") { $my_page = "store.index"; }
		elseif ( $page == 'admin.theme_config_form' ) { $my_page = 'admin.show_cfg'; }
		else { $my_page = str_replace('form','list',$page); }
		
		
		if( $no_menu ) {
			$js = "vm_windowClose();";
		}
		else {
			$js = "vm_submitButton('$task', 'adminForm', '$my_page');";
		}
		$bar->appendButton( 'Custom', "<td>
			<a class=\"toolbar\" href=\"javascript:$js\" >
			 <div class=\"vmicon-32-$task\" type=\"Standard\"></div>
			$alt</a>
		</td>" );
		
	}
	
	/**
	* Writes a custom option and task button for the button bar
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display (FULL URL!)
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	*/
	function custom( $task='', $page, $icon='', $iconOver='', $alt='', $listSelect=true, $formName="adminForm", $func = "" ) {
		

		$bar =& JToolBar::getInstance('toolbar');
		if ($listSelect) {
			if( empty( $func ))
				$href = "javascript:if (document.adminForm.boxchecked.value == 0){ alert('" . str_replace("'","\\'",JText::_('CMN_PLEASESELECT_TO')) . " $alt');}else{vm_submitButton('$task','$formName', '$page')}";
			else
				$href = "javascript:if (document.adminForm.boxchecked.value == 0){ alert('" . str_replace("'","\\'",JText::_('CMN_PLEASESELECT_TO')) . " $alt');}else{vm_submitListFunc('$task','$formName', '$func')}";
                } else {
                        $href = "javascript:vm_submitButton('$task','$formName', '$page')";
                }
                if( empty( $task )) {
                        $image_name = uniqid( "img_" );
                }
                else {
                        $image_name  = $task;
                }
                if ($icon && $iconOver) {
					$bar->appendButton('Custom', "<td>
						<a class=\"toolbar\" href=\"$href\" onmouseout=\"MM_swapImgRestore();\"  onmouseover=\"MM_swapImage('$image_name','','$iconOver',1);\">
						<img name=\"$image_name\" src=\"$icon\" alt=\"$alt\" border=\"0\" align=\"middle\" />
						&nbsp;<br/>$alt</a>
						</td>" );
			
		} 
		else {
			// The button is just a link then!
			$bar->appendButton('Custom', "<td><a class=\"toolbar\" href=\"$href\">&nbsp;$alt</a></td>" );
		}
	}
		/**
	* Writes a link for the button bar
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display (FULL URL!)
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	*/
	function customHref( $href='', $icon='', $iconOver='', $alt='' ) {
		$bar =& JToolBar::getInstance('toolbar');
		if ($icon && $iconOver) {
			$bar->appendButton('Custom', "<td>
			<a class=\"toolbar\" href=\"$href\" onmouseout=\"MM_swapImgRestore();\"  onmouseover=\"MM_swapImage('$alt','','$iconOver',1);\">
			<img name=\"$alt\" src=\"$icon\" alt=\"$alt\" border=\"0\" align=\"middle\" />
			&nbsp;<br/>$alt</a></td>" );
			
		}
		else {
			$bar->appendButton('Custom', "<td><a class=\"toolbar\" href=\"$href\">&nbsp;$alt</a></td>" );
		}
	}
}
/**
 * This class is used for the Ext-based Toolbar (ExtJS), which is only the case in "Extended Layout" mode
 * This toolbar is a custom replacement for the Mambo/Joomla! toolbar
 * @author soeren
 * @since 1.1
 *
 */
class vmToolBar {
	var $buttons = '';
	/**
	 * Returns a reference to a global vmToolBar object, only creating it if it
	 * doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $toolbar = &vmToolBar::getInstance([$name);</pre>
	 *
	 * @access	public
	 * @param	string		$name  The name of the toolbar.
	 * @return	vmToolBar	The vmToolBar object.
	 */
	function & getInstance($name)
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$name])) {
			$instances[$name] = new vmToolBar();
		}

		return $instances[$name];
	}
	
	function appendButton( $text, $action_name, $click_action ) {
		$text = '<div style="float:left;background: url('.VM_THEMEURL.'images/administration/menu/icon-16-'.$action_name.'.png) 50% 0 no-repeat;height:17px;width:17px;" border="0" alt="'.$action_name.'">&nbsp;</div>&nbsp;' . $text;
		$this->buttons .=  "vmTb.addButton({text: '$text', handler: new Function('".addslashes($click_action)."')});\n";
	}
	/**
	 * Renders the Ext Toolbar for VirtueMart
	 * means: it assembles the javascript to add the buttons/separators/links to the toolbar
	 *
	 */
	function render() {
		
		if( $this->buttons != '' ) {
			vmCommonHTML::loadExtjs();
			$toolbarscript = "var renderVMTb = function() {
				var vmTb = new Ext.Toolbar({renderTo: \"vm-toolbar\"});\n"
				. $this->buttons
				//. "\nif( self.history.length > 1 ) { vmTb.addSeparator(); vmTb.addButton({text: '<div style=\"float:left;background: url(".VM_THEMEURL."images/administration/menu/icon-16-back.png) 50% 0 no-repeat;height:17px;width:17px;\" border=\"0\" alt=\"{JText::_BACK}\">&nbsp;</div>{JText::_BACK}', handler: new Function('history.back();') }); }"
				. "\nvmTb.addSeparator();\n vmTb.addButton({text: '<div style=\"float:left;background: url(".VM_THEMEURL."images/administration/menu/icon-16-reload.png) 50% 0 no-repeat;height:17px;width:17px;\" border=\"0\" alt=\"".JText::_('RELOAD')."\">&nbsp;</div>".JText::_('RELOAD')."', handler: new Function('location.reload();') });
				};
				if( Ext.isIE6 || Ext.isIE7 ) {
					Ext.EventManager.addListener( window, 'load', renderVMTb );
				}
				else {
					Ext.onReady( renderVMTb );
				}";	
			
			echo vmCommonHTML::scriptTag('', $toolbarscript );
		}
		//
	}

	/**
	* Writes the common 'new' icon for the button bar
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function addNew( $task='new', $page, $alt='', $formName="adminForm" ) {
		
		if( $alt == '') {
			$alt = JText::_('CMN_NEW');
		}
		
		$this->appendButton($alt, $task, "document.adminForm.toggle.onclick();vm_submitButton('$task','$formName','$page')" );

	}
	/**
	* Writes a save button for a given option
	* Save operation leads to a save and then close action
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function save( $task='save', $alt='' ) {
		
		if( $alt == '') {
			$alt = JText::_('CMN_SAVE');
		}
		$this->appendButton($alt, $task, "submitbutton('$task')" );
		
	}
	
	/**
	* Writes an apply button for a given option
	* Save operation leads to a save and then reopen form action
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function apply( $task='apply', $alt='' ) {
		global $page;
		if( $alt == '') {
			$alt = JText::_('E_APPLY');
		}
		$this->appendButton($alt, $task, "vm_submitButton('$task', 'adminForm', '$page')" );
	}
	function back() {
		
		$this->appendButton(JText::_('BACK'), 'back', "window.history.back();" );
	}
	/**
	* Writes a common 'publish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function publishList( $func, $task='publish', $alt='' ) {
		
		if ($alt=='') {
			$alt=JText::_('CMN_PUBLISH');
		}
     	$this->appendButton($alt, $task, "if (document.adminForm.boxchecked.value == 0){ Ext.Msg.alert('".JText::_('PEAR_LOG_NOTICE')."', '" . JText::_('CMN_PLEASESELECT_PUBLISH',false) . "'); } else {vm_submitListFunc('$task', 'adminForm', '$func');}");
	}
	
	/**
	* Writes a common 'unpublish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function unpublishList( $func, $task='unpublish', $alt='' ) {		
		
		if ($alt=='') {
			$alt=JText::_('CMN_UNPUBLISH');
		}
     	$this->appendButton( $alt, $task, "if (document.adminForm.boxchecked.value == 0){ Ext.Msg.alert('".JText::_('PEAR_LOG_NOTICE')."', '" . JText::_('CMN_PLEASESELECT_UNPUBLISH',false) . "'); } else {vm_submitListFunc('$task', 'adminForm', '$func');}" );
	}
	/**
	* Writes a common 'delete' button for a list of records
	* @param string  Postscript for the 'are you sure' message
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function deleteList( $func, $task='remove', $alt='' ) {
		
		if( $alt == '') {
			$alt = JText::_('E_REMOVE');
		}	
		
		$this->appendButton( $alt, $task, "if (document.adminForm.boxchecked.value == 0){ Ext.Msg.alert('".JText::_('PEAR_LOG_NOTICE')."', '" . JText::_('CMN_PLEASESELECT_DELETE') . "'); } else if (confirm('Are you sure you want to delete selected items?')){ vm_submitListFunc('$task', 'adminForm', '$func' );}" );
		
	}
	
	/**
	* Writes a cancel button and invokes a cancel operation (eg a checkin)
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function cancel( $task='cancel', $alt='' ) {
		global $page;
		if( $alt == '') {
			$alt = JText::_('CMN_CANCEL');
		}
		
		if ($page == "store.store_form") { $my_page = "store.index"; }
		elseif ($page == "admin.user_address_form") { $my_page = "admin.user_form"; }
		elseif ($page == "admin.show_cfg") { $my_page = "store.index"; }
		elseif ( $page == 'admin.theme_config_form' ) { $my_page = 'admin.show_cfg'; }
		else { $my_page = str_replace('form','list',$page); }		
		
		$js = "vm_submitButton('$task', 'adminForm', '$my_page');";
		
		$this->appendButton( $alt, $task, $js );
		
	}
	
	/**
	* Writes a custom option and task button for the button bar
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display (FULL URL!)
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	*/
	function custom( $task='', $page, $action_name='', $alt='', $listSelect=true, $formName="adminForm", $func = "" ) {
		
		
		if ($listSelect) {
			if( empty( $func )) {
				$js = "if (document.adminForm.boxchecked.value == 0){ Ext.Msg.alert('".JText::_('PEAR_LOG_NOTICE')."', '" . JText::_('CMN_PLEASESELECT_TO') . " $alt');}else{vm_submitButton('$task','$formName', '$page')}";
			}
			else {
				$js = "if (document.adminForm.boxchecked.value == 0){ Ext.Msg.alert('".JText::_('PEAR_LOG_NOTICE')."', '" . JText::_('CMN_PLEASESELECT_TO') . " $alt');}else{vm_submitListFunc('$task','$formName', '$func')}";
			}
        } else {
            $js = "vm_submitButton('$task','$formName', '$page')";
        }
        if( empty( $task )) {
            $image_name = uniqid( "img_" );
        }
        else {
            $image_name  = $task;
        }
        if ($action_name) {
			$this->appendButton($alt, $task, $js );			
		} 
		else {
			// The button is just a link then!
			$this->appendButton($alt, $task, "document.location='$js'" );
		}
	}
		/**
	* Writes a link for the button bar
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display (FULL URL!)
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	*/
	function customHref( $href='', $icon='', $alt='' ) {
		
		if ($icon ) {
			$this->appendButton($alt, $icon, "document.location='$href'" );			
		}
		else {
			$this->appendButton($alt, 'none', "document.location='$href'" );
		}
	}
	function divider() {
		$this->buttons .= "vmTb.addSeparator();\n";
	}
}