<?php
/**
*
* List/add/edit/remove Userfields
*
* @package	VirtueMart
* @subpackage Userfields
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
jimport('joomla.version');

/**
 * HTML View class for maintaining the list of order types
 *
 * @package	VirtueMart
 * @subpackage Userfields
 * @author Oscar van Eijk
 */
class VirtuemartViewUserfields extends JView {

	function display($tpl = null) {

		$option = JRequest::getCmd( 'option');
		$mainframe = JFactory::getApplication() ;

		// Load the helper(s)
		$this->loadHelper('adminMenu');

		$layoutName = JRequest::getVar('layout', 'default');
		$model = $this->getModel();

		// The list of fields which can't be toggled
		$lists['coreFields']= array( 'username', 'email', 'password', 'password2' );

		if ($layoutName == 'edit') {
			$editor = JFactory::getEditor();

			$userField = $model->getUserfield();

			if ($userField->virtuemart_userfield_id < 1) { // Insert new userfield
				JToolBarHelper::title(  JText::_('COM_VIRTUEMART_USERFIELD_FORM_LBL').JText::_('COM_VIRTUEMART_FORM_NEW'), 'vm_orderstatus_48');

				$this->assignRef('ordering', JText::_('COM_VIRTUEMART_NEW_ITEMS_PLACE'));
				$userFieldValues = array();
				$attribs = 'onchange="toggleType(this.options[this.selectedIndex].value);"';
				$lists['type'] = JHTML::_('select.genericlist', $this->_getTypes(), 'type', $attribs, 'type', 'text', $userField->type);
			} else { // Update existing userfield
				// Ordering dropdown
				$qry = 'SELECT ordering AS value, name AS text'
					. ' FROM #__virtuemart_userfields'
					. ' ORDER BY ordering';
				$ordering = JHTML::_('list.specificordering',  $userField, $userField->virtuemart_userfield_id, $qry);
				$this->assignRef('ordering', $ordering);

				JToolBarHelper::title( JText::_('COM_VIRTUEMART_USERFIELD_FORM_LBL').JText::_('COM_VIRTUEMART_FORM_EDIT'));

				$userFieldValues = $model->getUserfieldValues();
				$lists['type'] = $this->_getTypes($userField->type)
					. '<input type="hidden" name="type" value="'.$userField->type.'" />';
			}
			JToolBarHelper::divider();
			JToolBarHelper::save();
                        JToolBarHelper::apply();
			JToolBarHelper::cancel();
			
			$notoggle = (in_array($userField->name, $lists['coreFields']) ? 'readonly="readonly"' : '');

			// Vendor selection
			$vendor_model = $this->getModel('vendor');
			$vendor_list = $vendor_model->getVendors();
			$lists['vendors'] = JHTML::_('select.genericlist', $vendor_list, 'virtuemart_vendor_id', '', 'virtuemart_vendor_id', 'vendor_name', $userField->virtuemart_vendor_id);

			// Shopper groups for EU VAT Id
			$shoppergroup_model = $this->getModel('shoppergroup');
			$shoppergroup_list = $shoppergroup_model->getShopperGroups(true);
			$lists['shoppergroups'] = JHTML::_('select.genericlist', $shoppergroup_list, 'virtuemart_shoppergroup_id', '', 'virtuemart_shoppergroup_id', 'shopper_group_name', $model->_params->get('virtuemart_shoppergroup_id'));

			// Minimum age select
			$ages = array();
			for ($i = 13; $i <= 25; $i++) {
				$ages[] = array('key' => $i, 'value' => $i.' '.JText::_('COM_VIRTUEMART_CMN_YEARS'));
			}
			$lists['minimum_age'] = JHTML::_('select.genericlist', $ages, 'minimum_age', '', 'key', 'value', $model->_params->get('minimum_age', 18));

			// Web address types
			$webaddress_types = array(
				 array('key' => 0, 'value' => JText::_('COM_VIRTUEMART_USERFIELDS_URL_ONLY'))
				,array('key' => 2, 'value' => JText::_('COM_VIRTUEMART_USERFIELDS_HYPERTEXT_URL'))
			);
			$lists['webaddresstypes'] = JHTML::_('select.genericlist', $webaddress_types, 'webaddresstype', '', 'key', 'value', $model->_params->get('webaddresstype'));

			// Userfield values
			if (($n = count($userFieldValues)) < 1) {
				$lists['userfield_values'] =
					 '<tr>'
					.'<td><input type="text" value="" name="vNames[0]" /></td>'
					.'<td><input type="text" value="" name="vValues[0]" /></td>'
					.'</tr>';
				$i = 1;
			} else {
				$lists['userfield_values'] = '';
				for ($i = 0; $i < $n; $i++) {
					$lists['userfield_values'] .=
						 '<tr>'
						.'<td><input type="text" value="'.$userFieldValues[$i]->fieldtitle.'" name="vNames['.$i.']" readonly="readonly" /></td>'
						.'<td><input type="text" value="'.$userFieldValues[$i]->fieldvalue.'" name="vValues['.$i.']" /></td>'
						.'</tr>';
				}
			}
			$this->assignRef('valueCount', --$i);

			// Toggles
			$lists['required']     = JHTML::_('select.booleanlist', 'required',     $notoggle, $userField->required,     'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');
			$lists['published']    = JHTML::_('select.booleanlist', 'published',    $notoggle, $userField->published,    'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');
			$lists['registration'] = JHTML::_('select.booleanlist', 'registration', $notoggle, $userField->registration, 'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');
			$lists['shipping']     = JHTML::_('select.booleanlist', 'shipping',     $notoggle, $userField->shipping,     'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');
			$lists['account']      = JHTML::_('select.booleanlist', 'account',      $notoggle, $userField->account,      'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');
			$lists['readonly']     = JHTML::_('select.booleanlist', 'readonly',     $notoggle, $userField->readonly,     'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');

			$this->assignRef('lists', $lists);
			$this->assignRef('userField', $userField);
			$this->assignRef('userFieldValues', $userFieldValues);
			$this->assignRef('editor', $editor);
		} else {
			JToolBarHelper::title( JText::_('COM_VIRTUEMART_MANAGE_USER_FIELDS'));
			JToolBarHelper::addNewX();
			JToolBarHelper::editListX();
			JToolBarHelper::divider();
			JToolBarHelper::custom('enable_required', 'publish','','COM_VIRTUEMART_FIELDMANAGER_REQUIRE');
			JToolBarHelper::custom('disable_required', 'unpublish','','COM_VIRTUEMART_FIELDMANAGER_UNREQUIRE');
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::divider();
			JToolBarHelper::custom('enable_registration', 'publish','','COM_VIRTUEMART_FIELDMANAGER_SHOW_REGISTRATION');
			JToolBarHelper::custom('disable_registration', 'unpublish','','COM_VIRTUEMART_FIELDMANAGER_HIDE_REGISTRATION');
			JToolBarHelper::custom('enable_shipping', 'publish','','COM_VIRTUEMART_FIELDMANAGER_SHOW_SHIPPING');
			JToolBarHelper::custom('disable_shipping', 'unpublish','','COM_VIRTUEMART_FIELDMANAGER_HIDE_SHIPPING');
			JToolBarHelper::custom('enable_account', 'publish','','COM_VIRTUEMART_FIELDMANAGER_SHOW_ACCOUNT');
			JToolBarHelper::custom('disable_account', 'unpublish','','COM_VIRTUEMART_FIELDMANAGER_HIDE_ACCOUNT');
			JToolBarHelper::divider();
			JToolBarHelper::deleteList('', 'remove', 'Delete');

			$userfieldsList = $model->getUserfieldsList();
			$this->assignRef('userfieldsList', $userfieldsList);

			$pagination = $model->getPagination();
			$this->assignRef('pagination', $pagination);

			// search filter
			$search = $mainframe->getUserStateFromRequest( $option.'search', 'search', '', 'string');
			$search = JString::strtolower( $search );
			$lists['search']= $search;

			// Get the ordering
			$lists['order']     = $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'ordering', 'cmd' );
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
	function toggle( $field, $i, $toggle, $untoggleable = false, $imgY = 'tick.png', $imgX = 'publish_x.png', $prefix='' )
	{

		$img 	= $field ? $imgY : $imgX;
		if ($toggle == 'published') { // Stay compatible with grid.published
			$task 	= $field ? 'unpublish' : 'publish';
			$alt 	= $field ? JText::_('COM_VIRTUEMART_PUBLISHED') : JText::_('COM_VIRTUEMART_UNPUBLISHED');
			$action = $field ? JText::_('COM_VIRTUEMART_UNPUBLISH_ITEM') : JText::_('COM_VIRTUEMART_PUBLISH_ITEM');
		} else {
			$task 	= $field ? 'disable_'.$toggle : 'enable_'.$toggle;
			$alt 	= $field ? JText::_('COM_VIRTUEMART_published') : JText::_('COM_VIRTUEMART_DISABLED');
			$action = $field ? JText::_('COM_VIRTUEMART_DISABLE_ITEM') : JText::_('COM_VIRTUEMART_ENABLE_ITEM');
		}

		if (VmConfig::isAtLeastVersion('1.6.0')) {
			$img = 'admin/' . $img;
		}
		$retImgSrc =  JHTML::_('image.administrator', $img, '/images/', null, null, $alt);

		if ($untoggleable) {
			return ($retImgSrc);
		} else {
			return ('<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">'
				.$retImgSrc. '</a>');
		}
	}

	/**
	 * Create an array with userfield types and the visible text in the format expected by the Joomla select class
	 *
	 * @param string $value If not null, the type of which the text should be returned
	 * @return mixed array or string
	 */
	function _getTypes ($value = null)
	{
		$types = array(
			 array('type' => 'text'             , 'text' => JText::_('COM_VIRTUEMART_FIELDS_TEXTFIELD'))
			,array('type' => 'checkbox'         , 'text' => JText::_('COM_VIRTUEMART_FIELDS_CHECKBOX_SINGLE'))
			,array('type' => 'multicheckbox'    , 'text' => JText::_('COM_VIRTUEMART_FIELDS_CHECKBOX_MULTIPLE'))
			,array('type' => 'date'             , 'text' => JText::_('COM_VIRTUEMART_FIELDS_DATE'))
			,array('type' => 'age_verification' , 'text' => JText::_('COM_VIRTUEMART_FIELDS_AGEVERIFICATION'))
			,array('type' => 'select'           , 'text' => JText::_('COM_VIRTUEMART_FIELDS_DROPDOWN_SINGLE'))
			,array('type' => 'multiselect'      , 'text' => JText::_('COM_VIRTUEMART_FIELDS_DROPDOWN_MULTIPLE'))
			,array('type' => 'emailaddress'     , 'text' => JText::_('COM_VIRTUEMART_FIELDS_EMAIL'))
			,array('type' => 'euvatid'          , 'text' => JText::_('COM_VIRTUEMART_FIELDS_EUVATID'))
			,array('type' => 'editorta'         , 'text' => JText::_('COM_VIRTUEMART_FIELDS_EDITORAREA'))
			,array('type' => 'textarea'         , 'text' => JText::_('COM_VIRTUEMART_FIELDS_TEXTAREA'))
			,array('type' => 'radio'            , 'text' => JText::_('COM_VIRTUEMART_FIELDS_RADIOBUTTON'))
			,array('type' => 'webaddress'       , 'text' => JText::_('COM_VIRTUEMART_FIELDS_WEBADDRESS'))
		);

		if (file_exists(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_securityimages'.DS.'client.php')) {
			$types[] = array('type' => 'captcha', 'text' => JText::_('COM_VIRTUEMART_FIELDS_CAPTCHA'));
		}
		if (file_exists(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_securityimages'.DS.'class'.DS.'SecurityImagesHelper.php')) {
			$types[] = array('type' => 'captcha', 'text' => JText::_('COM_VIRTUEMART_FIELDS_CAPTCHA'));
		}
		if (file_exists(JPATH_ROOT.DS.'components'.DS.'com_yanc'.DS.'yanc.php')) {
			$types[] = array('type' => 'yanc_subscription', 'text' => JText::_('COM_VIRTUEMART_FIELDS_NEWSLETTER').' (YaNC)');
		}
		if (file_exists(JPATH_ROOT.DS.'components'.DS.'com_anjel'.DS.'anjel.php')) {
			$types[] = array('type' => 'anjel_subscription', 'text' => JText::_('COM_VIRTUEMART_FIELDS_NEWSLETTER').' (ANJEL)');
		}
		if (file_exists(JPATH_ROOT.DS.'components'.DS.'com_letterman'.DS.'letterman.php')) {
			$types[] = array('type' => 'letterman_subscription', 'text' => JText::_('COM_VIRTUEMART_FIELDS_NEWSLETTER').' (Letterman)');
		}
		if (file_exists(JPATH_ROOT.DS.'components'.DS.'com_ccnewsletter'.DS.'ccnewsletter.php')) {
			$types[] = array('type' => 'ccnewsletter_subscription', 'text' => JText::_('COM_VIRTUEMART_FIELDS_NEWSLETTER').' (ccNewsletter)');
		}
		$types[] = array('type' => 'delimiter', 'text' => JText::_('COM_VIRTUEMART_FIELDS_DELIMITER'));

		if ($value === null) {
			return $types;
		} else {
			foreach ($types as $type) {
				if ($type['type'] == $value) {
					return $type['text'];
				}
				return $value;
			}
		}
	}

}

//No Closing Tag
