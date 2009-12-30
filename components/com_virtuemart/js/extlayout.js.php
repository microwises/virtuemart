<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* This file provides the Ext Layout for VirtueMart Administration
* It is located here, because this provides an easy way to include it using the standard VirtueMart Call
* and allows to keep the current Session.
*
* @version $Id: compat.joomla1.5.php 1133 2008-01-08 20:40:56Z gregdev $
* @package VirtueMart
* @subpackage core
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/

while(@ob_end_clean());

header( 'Content-Type: application/x-javascript');
JText::setDebug(0);


$module_id = JRequest::getVar( 'module_id');


$menu_items = getAdminMenu($module_id);

//$menu_items = assembleMenuItems($mod);
include( ADMINPATH.'version.php');

echo '
if( typeof Ext == "undefined" ) {
	document.location="index2.php?option='.VM_COMPONENT_NAME.'&usefetchscript=0";
}
// Check if this Window is a duplicate and opens in an iframe
if( typeof parent.Ext !== "undefined" && typeof parent.loadPage == "function" ) {
	// then load the pure page, not again the whole VirtueMart Admin interface
	parent.loadPage();
}
function showButtonMenu( btn, e ) {
	btn.showMenu();
}
function hideButtonMenu( btn, e ) {
	btn.hideMenu();
}';
echo '
function vmLayoutInit() {
	if(!Ext.get("vmPage") ) {
		return;
	}
    try{ Ext.get("header-box").hide(); } catch(e) {} // Hide the Admin Menu under Joomla! 1.5
    try{ Ext.get("wrapper").hide(); } catch(e) {} // Hide the Admin Menu under Joomla! 1.0
            
    // initialize state manager, we will use cookies
	Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
	';
	
	$admin_menu = 'var adminMenuItems = [';
	$i = 0;
	$itemCount = count( $menu_items );
	foreach( $menu_items as $item ) {
		$admin_menu .= '{ 
	                    title:"<span style=\"font-weight: bold;\">'.$item['title'].'</span>",
			           html: "<ul>';
		$j = 0;
		$linkCount = count( $item['items'] );
		foreach( $item['items'] as $link ) {
			if( $link['name'] == '-' ) {
				$admin_menu .= "<li>&nbsp;</li>";
			} else {
				$url = strncmp($link['link'], 'http', 4 ) === 0 ? $link['link'] : $sess->url('index2.php?pshop_mode=admin&'.$link['link'], false, false );
				$title = isset( $link['title'] ) ? ' title="'.$link['title'].'"' : '';
				$admin_menu .= '<li><a href=\"'.$url.'\" class=\"'.$link['icon_class'].'\" style=\"font-weight: bold;background-repeat: no-repeat;\" onclick=\"loadPage( \''.$url.'\' );return false;\""
									+ ">'.(JText::_($link['name']) ? JText::_($link['name']) : $link['name']) .'</a></li>';
			}
		}
		$admin_menu .= '</ul>"
					}';
		if( ++$i < $itemCount ) $admin_menu .= ',';
	}
	$admin_menu .= '];';
	echo $admin_menu;
	
	echo '
    var viewport = new Ext.Viewport({
			layout:"border",
			items:[{
			    region:"center",
			    layout:"fit",
			    items:[{
			        layout:"fit",
			        items:[{
							xtype:"tabpanel",
					        deferredRender:false,
					        activeTab:0,
					        id: "center-panel",
					    	listeners: {
							    "tabchange" : {
							        fn: function(tabpanel, panel) { parent.document.title=panel.title },
							        scope: this
							    }
							 },
					        items:[{
					        	xtype: "panel",
								layout: "fit",
								id: "vmpage-panel",
								title: "'.addslashes(JText::_('VM_ADMIN_PANELTITLE')).'",
								tbar: [{ xtype: "tbspacer" }],
								closable:false,
								contentEl: "vmPage"
							}]
			              
			          }]
			      }]
			  },
			  {
				region: "west",
				autoScroll: true,
				width: 220,
				layout: "fit",
				items: [{
					xtype: "panel",
					autoHeight: true,
					html:"<div style=\"margin-bottom: 5px;\">" +
			    		 "<img src=\"'.VM_THEMEURL.'images/administration/header_logo.png\" alt=\"VirtueMart Logo\" /> " +
						"<a href=\"'.(!empty($_REQUEST['frontend'])&&vmisjoomla('1.5')?'index.php':'index2.php').'\" title=\"'.JText::_('VM_ADMIN_BACKTOJOOMLA').'\" class=\"vmicon vmicon-16-back\" style=\"vertical-align: middle;font-weight:bold;margin-top: 3px;\">'.JText::_('VM_ADMIN_BACKTOJOOMLA').'</a>" +
						"<br />" +
						"<a href=\"index2.php?option=com_virtuemart&amp;vmLayout=standard\" class=\"vmicon vmicon-16-back\" style=\"vertical-align: middle;font-weight:bold;margin-top: 3px;\">'. JText::_('VM_ADMIN_SIMPLE_LAYOUT') .'</a>" +
						"</div>"
					},
					{
					xtype: "panel",
		        	layout:"accordion",
					layoutConfig:{animate:true},
					
					defaults: {
						stateEvents: ["collapse","expand"],
						getState:function() { return {collapsed:this.collapsed}; },
						bodyStyle:"padding:8px",
						border:false,
						autoScroll: true,
						autoHeight: true
					},
					items: adminMenuItems
					}]
			}]
			}
    );
 }';
 //Only joomla 1.5 is interesting
//if( vmisjoomla('1.0') && empty( $_REQUEST['frontend'])) {
//	$script = 'index3.php';
//} elseif( vmisjoomla('1.0') ) {
//	$script = 'index2.php';
//} else {
	$script = 'index.php';
//}
echo '
function loadPage(page){
	
	if( !page || page == "" ) {
        defaultpage = "'.$script.'&option=com_virtuemart&page=store.index";
        page = Ext.state.Manager.get( "vmlastpage", defaultpage );
	}
	if( page.indexOf( "virtuemart.net" ) == -1 ) {
        php_self = page.replace(/index2.php/, "'.$script.'");
        php_self = php_self.replace(/index.php/, "'.$script.'");
        if( Ext.get("vmPage") ) {
            Ext.get("vmPage").dom.src = php_self + "&only_page=1&no_menu=1&tmpl=component";
        }
	} else {
		if( Ext.get("vmPage") ) {
        	Ext.get("vmPage").dom.src = page;
       	}
	}         
    Ext.state.Manager.set("vmlastpage", page );
}
if( Ext.isIE ) {
	Ext.EventManager.addListener( window, "load", vmLayoutInit );
}
else {
	Ext.onReady( vmLayoutInit );
}
';


	function getAdminMenu($filter_by_module_id=0) {
		global $page, $db, $auth;
		
		$menuArr = array();
		        
		$filter[] = "vmmod.module_publish='Y'";
		$filter[] = "item.published='1'";
		$filter[] = "vmmod.is_admin='1'";
		$filter[] = "FIND_IN_SET('".$auth['perms']."', module_perms )>0";
		if( !empty($filter_by_module_id)) {
			$filter[] = 'vmmod.module_id='.(int)$filter_by_module_id; 
		}
		
		$q = "SELECT vmmod.module_id,module_name,module_perms,id,name,link,depends,icon_class 
					FROM #__{vm}_module vmmod 
					LEFT JOIN #__{vm}_menu_admin item ON vmmod.module_id=item.module_id 
					WHERE  ".implode(' AND ', $filter )."   
					ORDER BY vmmod.list_order,item.ordering";
		$db->query($q);
		
		while( $db->next_record() ) {
		    $menuArr[$db->f('module_name')]['title'] = $GLOBALS['VM_LANG']->_('VM_'.strtoupper($db->f('module_name')).'_MOD');
			$menuArr[$db->f('module_name')]['items'][] = array('name' => $db->f('name'),
																		'link' => $db->f('link'),
																		'depends' => $db->f('depends'),
																		'icon_class' => $db->f('icon_class'));
		}
		return $menuArr;
	}
?>