<?php
/**
*
* List/add/edit/remove Users
*
* @package	VirtueMart
* @subpackage User
* @author Oscar van Eijk
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
 * HTML View class for maintaining the list of users
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
 */
class VirtuemartViewUser extends JView {

	function display($tpl = null) {

		global $mainframe, $option;

		// Load the helper(s)
		$this->loadHelper('adminMenu');

		$layoutName = JRequest::getVar('layout', 'default');
		$model = $this->getModel();

		if ($layoutName == 'edit') {
			jimport('joomla.html.pane');
			$pane = JPane::getInstance();
			$editor = JFactory::getEditor();

			$_currentUser =& JFactory::getUser();
			$this->loadHelper('vendorhelper');
			$vendor =& new Vendor;

			$userDetails = $model->getUser();
			if ($userDetails->JUser->get('id') < 1) { // Insert new user
				JToolBarHelper::title(  JText::_('VM_USER_FORM_LBL' ).': <small><small>[ New ]</small></small>', 'vm_user_48.png');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel();
			} else { // Update existing user
				JToolBarHelper::title( JText::_('VM_USER_FORM_LBL' ).': <small><small>[ Edit ]</small></small>', 'vm_user_48.png');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel('cancel', 'Close');
			}

			// User details
			$_contactDetails = $model->getContactDetails();
			$_groupList = $model->getGroupList();
			if (!is_array($_groupList)) {
				$lists['gid'] = '<input type="hidden" name="gid" value="'. $userDetails->JUser->get('gid') .'" /><strong>'. JText::_($_groupList) .'</strong>';
			} else {
				$lists['gid'] 	= JHTML::_('select.genericlist', $_groupList, 'gid', 'size="10"', 'value', 'text', $userDetails->JUser->get('gid'));
			}

			$lists['canBlock']      = ($_currentUser->authorize('com_users', 'block user')
							&& ($userDetails->JUser->get('id') != $_currentUser->get('id'))); // Can't block myself
			$lists['canSetMailopt'] = $_currentUser->authorize('workflow', 'email_events');
			$lists['block']     = JHTML::_('select.booleanlist', 'block',     0, $userDetails->JUser->get('block'),     'VM_ADMIN_CFG_YES', 'VM_ADMIN_CFG_NO');
			$lists['sendEmail'] = JHTML::_('select.booleanlist', 'sendEmail', 0, $userDetails->JUser->get('sendEmail'), 'VM_ADMIN_CFG_YES', 'VM_ADMIN_CFG_NO');

			$lists['params'] = $userDetails->JUser->getParameters(true);

			$this->assignRef('lists', $lists);
			$this->assignRef('userDetails', $userDetails);
			$this->assignRef('vendor', $vendor);
			$this->assignRef('contactDetails', $_contactDetails);
			$this->assignRef('editor', $editor);
			$this->assignRef('pane', $pane);
		} else {
			JToolBarHelper::title( JText::_('VM_USER_LIST_LBL'), 'vm_user_48.png');
			JToolBarHelper::addNewX();
			JToolBarHelper::editListX();
			JToolBarHelper::divider();
			JToolBarHelper::custom('enable_vendor', 'publish','','VM_USER_ISVENDOR');
			JToolBarHelper::custom('disable_vendor', 'unpublish','','VM_USER_ISNOTVENDOR');
			JToolBarHelper::divider();
			JToolBarHelper::deleteList('', 'remove', 'Delete');

			$userList = $model->getUserList();
			$this->assignRef('userList', $userList);

			$pagination = $model->getPagination();
			$this->assignRef('pagination', $pagination);

			// search filter
			$search = $mainframe->getUserStateFromRequest( $option.'search', 'search', '', 'string');
			$search = JString::strtolower( $search );
			$lists['search']= $search;

			// Get the ordering
			$lists['order']     = $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'id', 'cmd' );
			$lists['order_Dir'] = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', '', 'word' );
			$this->assignRef('lists', $lists);
		}

		parent::display($tpl);
	}

	/**
	 * Additional grid function for custom toggles
	 *
	 * @return string HTML code to write the toggle button 
	 */
	function toggle( $field, $i, $toggle, $imgY = 'tick.png', $imgX = 'publish_x.png', $prefix='' )
	{

		$img 	= $field ? $imgY : $imgX;
		if ($toggle == 'published') { // Stay compatible with grid.published
			$task 	= $field ? 'unpublish' : 'publish';
			$alt 	= $field ? JText::_( 'Published' ) : JText::_( 'Unpublished' );
			$action = $field ? JText::_( 'Unpublish Item' ) : JText::_( 'Publish item' );
		} else {
			$task 	= $field ? 'disable_'.$toggle : 'enable_'.$toggle;
			$alt 	= $field ? JText::_( 'Enabled' ) : JText::_( 'Disabled' );
			$action = $field ? JText::_( 'Disable Item' ) : JText::_( 'Enable item' );
		}

		return ('<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">'
			.'<img src="images/'. $img .'" border="0" alt="'. $alt .'" /></a>');
	}


}

//No Closing Tag
