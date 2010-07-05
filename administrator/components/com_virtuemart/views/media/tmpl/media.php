<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage 
* @author
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/
 
/**
 * @todo Edit link like: http://csvi/administrator/index3.php?page=product.file_form&product_id=1&file_id=7&option=com_virtuemart&no_menu=1
 */
AdminMenuHelper::startAdminArea();

/* Load some behaviors we need */
JHTML::_('behavior.modal');
JHTML::_('behavior.tooltip');
jimport('joomla.filesystem.file');

/* Get the component name */
$option = JRequest::getWord('option');

/* Load some variables */
$keyword = JRequest::getVar('keyword', null);
?>
<div id="header">
	<div style="float: left;">
	<?php
	if (JRequest::getInt('product_id', false)) echo JHTML::_('link', JRoute::_('index.php?view=media&option='.$option), JText::_('VM_PRODUCT_FILES_LIST_RETURN'));
	?>
	</div>
	<div style="float: right;">
		<form action="index.php" method="post" name="adminForm" id="adminForm">
		<?php echo JText::_('VM_PRODUCT_FILES_LIST_SEARCH_BY_NAME') ?>&nbsp;
			<input type="text" value="" name="keyword" size="25" class="inputbox" />
			<input type="hidden" name="option" value="<?php echo $option; ?>" />
			<input type="hidden" name="page" value="product.file_list" />
			<input class="button" type="submit" name="search" value="<?php echo JText::_('VM_SEARCH_TITLE')?>" />
	</div>
</div>
<?php 
$productfileslist = $this->productfileslist;
$roles = $this->productfilesroles;
$pagination = $this->pagination;
?>
	<table class="adminlist">
	<thead>
	<tr>
		<th><input type="checkbox" name="toggle" value="" onclick="checkAll('<?php echo count($productlist); ?>')" /></th>
		<th><?php echo JText::_('VM_PRODUCT_LIST_NAME'); ?></th>
		<th><?php echo JText::_('VM_FILES_LIST_FILENAME'); ?></th>
		<th><?php echo JText::_('VM_FILES_LIST_ROLE'); ?></th>
		<th><?php echo JText::_('VM_VIEW'); ?></th>
		<th><?php echo JText::_('VM_FILES_LIST_FILETITLE'); ?></th>
		<th><?php echo JText::_('VM_FILES_LIST_FILETYPE'); ?></th>
		<th><?php echo JText::_('PUBLISH'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	if (count($productfileslist) > 0) {
		$i = 0;
		$k = 0;
		foreach ($productfileslist as $key => $productfile) {
			/* Create the filename but check if it is a URL first */
			if (strtolower(substr($productfile->file_name, 0, 4)) == 'http') {
				$filename = $productfile->file_name;
			}
			else{
				$imageRootFolderExp = explode('/', VmConfig::get('media_product_path'));
				$imageProductFolder = implode(DS, $imageRootFolderExp);
				$filename = JPATH_SITE.DS.$imageProductFolder.str_replace(JPATH_SITE, '', $productfile->file_name);
			}
			$checked = JHTML::_('grid.id', $i , $productfile->file_id);
			if (!is_null($productfile->file_id)) $published = JHTML::_('grid.published', $productfile, $i );
			else $published = '';
			?>
			<tr>
				<!-- Checkbox -->
				<td><?php echo $checked; ?></td>
				<!-- Product name -->
				<?php 
				$link = "index.php?view=media&limitstart=".$pagination->limitstart."&keyword=".urlencode($keyword)."&product_id=".$productfile->file_product_id."&option=".$option;
				?>
				<td><?php echo JHTML::_('link', JRoute::_($link), $productfile->product_name); ?></td>
				<!-- File name -->
				<?php 
				$link = "index.php?view=media&task=edit&limitstart=".$pagination->limitstart."&keyword=".urlencode($keyword)."&product_id=".$productfile->file_product_id."&file_id=".$productfile->file_id."&file_role=".$productfile->file_role."&option=".$option;
				?>
				<td><?php echo JHTML::_('link', JRoute::_($link), $productfile->file_name, array('title' => JText::_('EDIT').' '.$productfile->file_name)); ?></td>
				<!-- File role -->
				<td><?php
					if ($productfile->isdownloadable) {
						$role = 'isDownloadable';
					}
					else if (substr($productfile->file_name, 0, 4) == 'http') {
						$role = 'isRemoteFile';
					}
					else {
						$role = $productfile->file_role;
					}
					echo JHTML::_('image', $roles[$role], JTEXT::_($role), array('title' => JText::_($role)));
					?>
				</td>
				<!-- Preview -->
				<td>
				<?php
					if ($productfile->file_is_image) {
						$fullimg = $filename;
						$info = pathinfo( $fullimg );
						/* Full image */
						if (JFile::exists($fullimg) || (strtolower(substr($productfile->file_name, 0, 4)) == 'http')) {
							$imgsize = getimagesize($fullimg);
							if ($imgsize[0] > 800) $imgsize[0] = 800;
							if ($imgsize[1] > 600) $imgsize[1] = 600;
							echo JText::_('VM_FILES_LIST_FULL_IMG').": ";
							echo JHTML::_('link', $productfile->file_url, JText::_('VM_VIEW'), array('class' => 'modal', 'rel' => '{handler: \'iframe\', size: {x: '.$imgsize[0].', y: '.$imgsize[1].'}}'));
						}
						echo '<br />';
						/* Create the thumbnail file, this should be in the resized folder */
						if (is_null($productfile->product_thumb_image)) $basename = $info['basename'];
						else $basename = basename($productfile->product_thumb_image);
						$thumbimg = $info['dirname'].DS.'resized'.DS.$basename;
						
						/* Thumbnail image */
						if (JFile::exists($thumbimg)) {
							$imgsize = getimagesize($thumbimg);
							echo JText::_('VM_FILES_LIST_THUMBNAIL_IMG').": ";
							echo JHTML::_('link', JURI::root().str_ireplace(array(JPATH_SITE, '\\'), array('', '/'), $info['dirname']).'/resized/'.$basename, JText::_('VM_VIEW'), array('class' => 'modal', 'rel' => '{handler: \'iframe\', size: {x: '.($imgsize[0]+20).', y: '.($imgsize[1]+20).'}}'));
						}
						else echo JText::_('VM_THUMB_NOT_FOUND').': '.$thumbimg;
					}
				?>
				</td>
				<!-- File title -->
				<td><?php echo $productfile->file_title; ?></td>
				<!-- File extension -->
				<td><?php echo $productfile->file_extension; ?></td>
				<!-- Published -->
				<td><?php echo $published; ?></td>
			</tr>
		<?php 
			$k = 1 - $k;
			$i++;
		} 
	}	
	?>
	</tbody>
	<tfoot>
	<tr>
	<td colspan="15">
		<?php echo $this->pagination->getListFooter(); ?>
	</td>
	</tr>
	</tfoot>
	</table>
<!-- Hidden Fields -->
<input type="hidden" name="task" value="" />
<?php if (JRequest::getInt('product_id', false)) { ?>
	<input type="hidden" name="product_id" value="<?php echo JRequest::getInt('product_id'); ?>" />
<?php } ?>
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="pshop_mode" value="admin" />
<input type="hidden" name="view" value="media" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?> 