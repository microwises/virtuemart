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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
class VirtuemartViewMedia extends JView {

	function display($tpl = null) {

		/* Load the menu */
		$this->loadHelper('adminMenu');

		/* Get the task */
		$task = JRequest::getCmd('task');

		/*  */
		switch ($task) {
			case 'save':
				$this->get('SaveMedia');
				break;
			case 'remove':
				$this->get('DeleteMedia');
				break;
			case 'publish':
			case 'unpublish':
				$this->get('PublishMedia');
				break;
		}

		/* Load the page data */
		switch ($task) {
			case 'add':
			case 'edit':

				/* Get the file details */
				$productfile = $this->get('ImageDetails');
				$this->assignRef('productfile', $productfile);

				/* Get the list of files from the downloadroot */
				$this->assignRef('filesselect', $this->get('FilesSelect'));

				/* Set selected file type */
				/* Add the dropdown options */
				$file_type_options=array();
				$file_type_options[] = JHTML::_('select.option','product_images', 'VM_FILES_FORM_PRODUCT_IMAGE');
				$file_type_options[] = JHTML::_('select.option','product_full_image', 'VM_PRODUCT_FORM_FULL_IMAGE');
				$file_type_options[] = JHTML::_('select.option','product_thumb_image', 'VM_PRODUCT_FORM_THUMB_IMAGE');
				$file_type_options[] = JHTML::_('select.option','downloadable_file', 'VM_FILES_FORM_DOWNLOADABLE');
				$file_type_options[] = JHTML::_('select.option','image', 'VM_FILES_FORM_IMAGE');
				$file_type_options[] = JHTML::_('select.option','file', 'VM_FILES_FORM_FILE');

				/* Find out which type the image is */
				$file_type_selected = $this->get('SelectedFileType');
				$this->assignRef('file_type_selected', $file_type_selected);

				$file_types = JHTML::_('select.genericlist', $file_type_options, 'file_type', 'onchange="checkThumbnailing();" class="inputbox"', 'value', 'text', $file_type_selected);
				$this->assignRef('file_types', $file_types);

				/* Set up the toolbar */
				JToolBarHelper::title(JText::_( 'VM_FILES_FORM' ).' '.$productfile->product_name, 'vm_media_48');
				JToolBarHelper::save();
				JToolBarHelper::cancel();
				break;
			case 'save':
			default:
				$this->assignRef('productfileslist', $this->get('ProductFilesList'));
				$this->assignRef('productfilesroles', $this->get('ProductFilesRoles'));

				/* Get the pagination */
				$pagination = $this->get('Pagination');
				$this->assignRef('pagination', $pagination);

				/* Set up the toolbar */
				/* Create the toolbar */
				if (JRequest::getInt('product_id', false)) {
					JToolBarHelper::title(JText::_('MEDIA_LIST').' :: '.$this->productfileslist[0]->product_name, 'vm_media_48');
				}
				else JToolBarHelper::title(JText::_( 'MEDIA_LIST' ), 'vm_media_48');
				JToolBarHelper::deleteList();
				if (JRequest::getInt('product_id', false)) JToolBarHelper::addNew();
				break;
		}

		parent::display($tpl);
	}

}
// pure php no closing tag