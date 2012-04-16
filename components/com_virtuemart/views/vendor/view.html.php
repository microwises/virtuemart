<?php

/**
 *
 * List/add/edit/remove Vendors
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Max Milbers
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 5133 2011-12-19 12:02:41Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmView'))require(JPATH_VM_SITE.DS.'helpers'.DS.'vmview.php');

// Set to '0' to use tabs i.s.o. sliders
// Might be a config option later on, now just here for testing.
define('__VM_USER_USE_SLIDERS', 0);

/**
 * HTML View class for maintaining the list of users
 *
 * @package	VirtueMart
 * @subpackage Vendor
 * @author Max Milbers
 */
class VirtuemartViewVendor extends VmView {

	/**
	 * Displays the view, collects needed data for the different layouts
	 *
	 * Okey I try now a completly new idea.
	 * We make a function for every tab and the display is getting the right tabs by an own function
	 * putting that in an array and after that we call the preparedataforlayoutBlub
	 *
	 * @author Max Milbers
	 */
	function display($tpl = null) {

		$document = JFactory::getDocument();
		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();
		$layoutName = $this->getLayout();

		$model = VmModel::getModel();

		$virtuemart_vendor_id = JRequest::getInt('virtuemart_vendor_id');

// 		if ($layoutName=='default') {
		if (empty($virtuemart_vendor_id)) {
			$document->setTitle( JText::_('COM_VIRTUEMART_VENDOR_LIST') );
			$pathway->addItem(JText::_('COM_VIRTUEMART_VENDOR_LIST'));

			$vendors = $model->getVendors();
			$model->addImages($vendors);

			$this->assignRef('vendors', $vendors);

		} else {

			$vendor = $model->getVendor($virtuemart_vendor_id);
			$model->addImages($vendor);

			$this->assignRef('vendor', $vendor);

			$userId = $model->getUserIdByVendorId($virtuemart_vendor_id);

			$usermodel = VmModel::getModel('user');

			$virtuemart_userinfo_id = $usermodel->getBTuserinfo_id($userId);
			$userFields = $usermodel->getUserInfoInUserFields($layoutName, 'BT', $virtuemart_userinfo_id,true,true);
			$this->assignRef('userFields', $userFields);

			if ($layoutName=='tos') {
				$document->setTitle( JText::_('COM_VIRTUEMART_VENDOR_TOS') );
				$pathway->addItem(JText::_('COM_VIRTUEMART_VENDOR_TOS'));
			}
			elseif ($layoutName=='contact') {
				$user = JFactory::getUser();
				$document->setTitle( JText::_('COM_VIRTUEMART_VENDOR_CONTACT') );
				$pathway->addItem(JText::_('COM_VIRTUEMART_VENDOR_CONTACT'));
				$this->assignRef('user', $user);

			} else {
				$document->setTitle( JText::_('COM_VIRTUEMART_VENDOR_DETAILS') );
				$pathway->addItem(JText::_('COM_VIRTUEMART_VENDOR_DETAILS'));
				$this->setLayout('details');
			}

			$linkdetails = '<a href="'.JRoute::_('index.php?option=com_virtuemart&view=vendor&virtuemart_vendor_id=' . $this->vendor->virtuemart_vendor_id).'">'.JText::_('COM_VIRTUEMART_VENDOR_DETAILS').'</a>';
			$linkcontact = '<a href="'.JRoute::_('index.php?option=com_virtuemart&view=vendor&layout=contact&virtuemart_vendor_id=' . $this->vendor->virtuemart_vendor_id).'">'.JText::_('COM_VIRTUEMART_VENDOR_CONTACT').'</a>';
			$linktos = '<a href="'.JRoute::_('index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=' . $this->vendor->virtuemart_vendor_id).'">'.JText::_('COM_VIRTUEMART_VENDOR_TOS').'</a>';

			$this->assignRef('linkdetails', $linkdetails);
			$this->assignRef('linkcontact', $linkcontact);
			$this->assignRef('linktos', $linktos);
		}

		parent::display($tpl);

	}


	function renderMailLayout() {
		$this->setLayout('mail_html_question');
		$this->comment = JRequest::getString('comment');
		$virtuemart_vendor_id = JRequest::getInt('virtuemart_vendor_id');

		$vendorModel = VmModel::getModel('vendor');
		$this->vendor = $vendorModel->getVendor($virtuemart_vendor_id);

		$this->subject = JText::_('COM_VIRTUEMART_VENDOR_CONTACT') .' '.$this->user['name'];
		$this->vendorEmail= $this->user['email'];
		//$this->vendorName= $this->user['email'];
		if (VmConfig::get('order_mail_html')) {
			$tpl = 'mail_html_question';
		} else {
			$tpl = 'mail_raw_question';
		}
		$this->setLayout($tpl);
		parent::display( );
	}

}

//No Closing Tag
