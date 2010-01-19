<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
mm_showMyFileName( __FILE__ );
require_once( CLASSPATH.'pluginEntity.class.php');

$id = vmRequest::getInt('id');
$description = '';
if ($id > 0 ) {
    $q = 'SELECT * FROM #__{vm}_plugins WHERE id='.$id;
    if( !$perm->check('admin')) {
    	$q.= ' AND vendor_id='.$hVendor_id;
    }
    $db->query($q);
    if( $db->next_record() ) {
    	
    	require_once( CLASSPATH.'simplexml.php');
    	$xml = new vmSimpleXML();
    	$xml->loadFile(ADMINPATH.'plugins/'.$db->f('folder').'/'.$db->f('element').'.xml');
    	
    	$rootEl = @$xml->document;
    	
    	if( is_object($rootEl)) {
    		$description = $rootEl->getElementByPath('/description');
    		$description = is_callable(array($description,'data')) ? $description->data() : 'missing';
    	}
    }
}

//First create the object and let it print a form heading
$formObj = &new formFactory( JText::_('Add/Edit Plugin') );
//Then Start the form
$formObj->startForm();
?>
<table class="adminform">
<tr><td width="40%" valign="top">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'Details' ); ?></legend>
	<table class="admintable">
		<tr>
			<td width="100" class="key">
				<label for="name">
					<?php echo JText::_( 'Name' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="name" id="name" size="35" value="<?php echo $db->f('name'); ?>" />
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<?php echo JText::_( 'Published' ); ?>:
			</td>
			<td>
				<?php 
				$published = array('0' => 'No','1' => 'Yes');
				echo ps_html::radioList('published',$db->f('published'),$published); 
				?>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<label for="folder">
					<?php echo JText::_( 'Type' ); ?>:
				</label>
			</td>
			<td>
				<?php $db->p('folder'); ?>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<label for="element">
					<?php echo JText::_( 'Plugin file' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="element" id="element" size="20" value="<?php echo $db->f('element'); ?>" />.php
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<label for="access">
					<?php echo JText::_( 'Shopper Group' ); ?>:
				</label>
			</td>
			<td>
				<?php echo ps_shopper_group::list_shopper_groups('shopper_group_id', $db->f('shopper_group_id')); ?>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<?php echo JText::_( 'Order' ); ?>:
			</td>
			<td>
				<?php 
				$plugins = vmPluginEntity::get_plugin_list($db->f('folder'));
				$orderinglist = array('0' => 'First Item');
				$i = 1;
				foreach( $plugins as $plugin ) {
					//if($plugin['element'] == $db->f('element')) continue;
					$orderinglist[$i++] = $plugin['name'];
				}
				echo ps_html::selectList('ordering', $db->f('ordering'), $orderinglist );
				?>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<?php echo JText::_( 'Description' ); ?>:
			</td>
			<td style="font-style: italic;">
				<?php echo $description; ?>
			</td>
		</tr>
		</table>
	</fieldset>
</td>
<td width="60%" valign="top">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'Parameters' ); ?></legend>
	<?php
        $parameters = new vmParameters($db->f('params'), ADMINPATH.'plugins/'.$db->f('folder').'/'.basename($db->f('element')).'.xml', $db->f('folder') );
        echo $parameters->render();
	?>
	</fieldset>
</tr>
</table>
<?php
// Add necessary hidden fields
$formObj->hiddenField( 'id', $id );

// Write your form with mixed tags and text fields
// and finally close the form:
$formObj->finishForm( 'pluginUpdate', $modulename, str_replace('_form', '_list', $pagename), $option );
?>