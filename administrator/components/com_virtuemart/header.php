<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* Header file for the shop administration.
* shows all modules that are available to the user in a dropdown menu
*
* @version $Id$
* @package VirtueMart
* @subpackage core
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* http://virtuemart.org
*/
mm_showMyFileName( __FILE__ );
include_once( ADMINPATH . "version.php" );

global $error, $page, $ps_product, $ps_product_category;
$product_id = JRequest::getInt('product_id');
$product_parent_id = JRequest::getInt('product_parent_id');
$module_id = JRequest::getInt('module_id', 0 );

if( is_array( $product_id ) || $page == 'product.product_list' ) {
    $recent_product_id = "";
}
else {
    $recent_product_id = $product_id;
}
        
$mod = array();


$q = "SELECT module_name,module_perms from #__{vm}_module WHERE published='1' ";
$q .= "AND module_name <> 'checkout' ORDER BY list_order ASC";

//$db = &JFactory::getDBO();
//$db->setQuery($q);
//$db->loadResult();
//$permsArray = $db->loadRowList(0);
//foreach($permsArray as $permrow){
//	if ($perm->check($permrow("module_perms"))) {
//        $mod[] = $permrow("module_name");
//	}
//}
$db = new ps_DB;
$db->query($q);
while ($db->next_record()) {
    if ($perm->check($db->f("module_perms"))) {
        $mod[] = $db->f("module_name");
	}
}

$vm_mainframe->addStyleSheet( $mosConfig_live_site.'/components/com_virtuemart/js/admin_menu/css/menu.css');
$vm_mainframe->addScript($mosConfig_live_site.'/components/com_virtuemart/js/admin_menu/js/virtuemart_menu.js');
$vm_mainframe->addScript($mosConfig_live_site.'/components/com_virtuemart/js/admin_menu/js/nifty.js');
$vm_mainframe->addScript($mosConfig_live_site.'/components/com_virtuemart/js/admin_menu/js/fat.js');
$vm_mainframe->addScript($mosConfig_live_site.'/components/com_virtuemart/js/functions.js');

$menu_items = getAdminMenu($module_id);
?>
<div id="vmMenu">
<div id="content-box2">
<div id="content-pad">
  <div class="sidemenu-box">
    <div class="sidemenu-pad">
		<center>
		<?php
		echo JHTML::_('link', 'http://virtuemart.org', JHTML::_('image', VM_ADMIN_ICON_URL.'vm_menulogo.png', 'Virtuemart'), array('target' => '_blank'));
		?>
			<h2><?php echo JText::_('VM_ADMIN')	?></h2>
		</center>
		<div class="status-divider">
		</div>
		<div class="sidemenu" id="masterdiv2">
		<?php
		$modCount = 1;
		foreach( $menu_items as $item ) { ?> 
			<h3 class="title-smenu" title="<?php echo JText::_($item['title']); ?> admin" ><?php echo JText::_($item['title']) ?></h3>
			<div class="section-smenu">
			<ul><?php 			
			foreach( $item['items'] as $link ) {
				if( $link['name'] == '-' ) {?>
					<li>
					<hr>
					</li><?php 
				} else { 
					// rjg - 7/14/09 Use new code to look for view and task
					//$url = strncmp($link['link'], 'http', 4 ) === 0 ? $link['link'] : $sess->url('index2.php?pshop_mode=admin&'.$link['link'], false, false );
					
                    if (strncmp($link['link'], 'http', 4 ) === 0) {
                    	/* Check for CSVI VirtueMart */
                    	if ($link['name'] == 'CSVIMPROVED_TITLE') {
                    		$q = "SELECT id FROM #__components WHERE link = 'option=com_csvivirtuemart'";
                    		$db->setQuery($q);
                    		$id = $db->loadResult();
                    		if ($id) $url = 'index.php?option=com_csvivirtuemart';
                    		else $url = $link['link'];
                    	}
                        else $url = $link['link'];
                    }
                    else {
                    	if ($link['view']) {			                       				                      
                        	$url = 'index.php?option=com_virtuemart&view='.$link['view'];
	                    	$url .= $link['task'] ? "&task=".$link['task'] : '';
	                    	// $url .= $link['extra'] ? $link['extra'] : '';
	                    	$url = strncmp($link['view'], 'http', 4 ) === 0 ? $link['view'] : $url;
	                	}
	                    else {
                        	$url = 'index2.php?option=com_virtuemart&pshop_mode=admin&'.$link['link'];
                    	}
                    }			
                    // end rjg
                    		
					?>
					<li class="item-smenu vmicon <?php echo $link['icon_class']; ?>">
					<a href="<?php echo $url; ?>"><?php echo JText::_($link['name']) ? JText::_($link['name']) : JText::_($link['name']); ?></a>
					</li><?php 
				}
			} ?>
			</ul></div>
			<?php $modCount++;
		} ?></div>
	<div style="text-align:center;">
	<h5><?php echo JText::_('VM_YOUR_VERSION') ?></h5>
	<a href="http://virtuemart.org/index2.php?option=com_versions&amp;catid=1&amp;myVersion=<?php echo @$VMVERSION->RELEASE ?>" onclick="javascript:void window.open(this.href, 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=580,directories=no,location=no'); return false;" title="<?php echo JText::_('VM_VERSIONCHECK_TITLE') ?>" target="_blank">
	<?php echo $VMVERSION->PRODUCT .'&nbsp;' . $VMVERSION->RELEASE .'&nbsp;'. $VMVERSION->DEV_STATUS
	?>
	</a>
	 </div>
      </div>
    </div>
  </div>
</div>
</div>
   
   
<?php 
echo '<script type="text/javascript">
	window.onload=function(){
		Fat.fade_all();
		NiftyCheck();
		Rounded("div.sidemenu-box","all","#fff","#f7f7f7","border #ccc");
		Rounded("div.element-box","all","#fff","#fff","border #ccc");
		Rounded("div.toolbar-box","all","#fff","#fbfbfb","border #ccc");
		Rounded("div.submenu-box","all","#fff","#f2f2f2","border #ccc");

	}
</script>';

if (!empty($error) && ($page != ERRORPAGE)) {
     echo '<br /><div class="message">'. $error.'</div><br />';
}
 

function getAdminMenu($filter_by_module_id=0) {
		global $page, $auth;
		
		$menuArr = array();
		        
		$filter[] = "jmmod.published='1'";
		$filter[] = "item.published='1'";
		$filter[] = "jmmod.is_admin='1'";
		$filter[] = "FIND_IN_SET('".$auth['perms']."', module_perms )>0";
		if( !empty($filter_by_module_id)) {
			$filter[] = 'vmmod.module_id='.(int)$filter_by_module_id; 
		}
		
		// rjg - 7/14/09 Change to read view and task
		$q = "SELECT jmmod.module_id,module_name,module_perms,id,name,link,depends,icon_class,view,task 
					FROM #__vm_module jmmod 
					LEFT JOIN #__vm_menu_admin item ON jmmod.module_id=item.module_id 
					WHERE  ".implode(' AND ', $filter )."   
					ORDER BY jmmod.list_order,item.ordering";
		$db = new ps_DB();
		$db->query($q);
		
		while( $db->next_record() ) {
		    $menuArr[$db->f('module_name')]['title'] = 'VM_'.strtoupper($db->f('module_name')).'_MOD';
		    // rjg - 7/14/09 Change to read view and task
			$menuArr[$db->f('module_name')]['items'][] = array('name' => $db->f('name'),
																		'link' => $db->f('link'),
																		'depends' => $db->f('depends'),
																		'icon_class' => $db->f('icon_class'),
														   				'view' => $db->f('view'),
														   				'task' => $db->f('task'));																			
		}
		return $menuArr;
	}
	
	?>
