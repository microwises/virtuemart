<?php
/**
 *
 * Product controller
 *
 * @package	VirtueMart
 * @subpackage
 * @author RolandD
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

if(!class_exists('VmController'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmcontroller.php');


/**
 * Product Controller
 *
 * @package    VirtueMart
 * @author
 */
class VirtuemartControllerProduct extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */
	function __construct() {
		parent::__construct('virtuemart_product_id');
		$this->addViewPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart' . DS . 'views');
	}


	/**
	 * Shows the product add/edit screen
	 */
	public function edit() {
		parent::edit('product_edit');
	}

	/**
	 * We want to allow html so we need to overwrite some request data
	 *
	 * @author Max Milbers
	 */
	function save(){

		$data = JRequest::get('post');

		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		if(Permissions::getInstance()->check('admin')){
			$data['product_desc'] = JRequest::getVar('product_desc','','post','STRING',2);
			$data['product_s_desc'] = JRequest::getVar('product_s_desc','','post','STRING',2);
		}

		parent::save($data);
	}

	function saveJS(){
		$data = JRequest::get('get');
		JRequest::setVar($data['token'], '1', 'post');

		JRequest::checkToken() or jexit( 'Invalid Token save' );
		$model = VmModel::getModel($this->_cname);
		$id = $model->store($data);

		$errors = $model->getErrors();
		if(empty($errors)) {
			$msg = JText::sprintf('COM_VIRTUEMART_STRING_SAVED',$this->mainLangKey);
			$type = 'save';
		}
		else $type = 'error';
		foreach($errors as $error){
			$msg = ($error).'<br />';
		}
		$json['msg'] = $msg;
		if ($id) {
			$json['product_id'] = $id;

			$json['ok'] = 1 ;
		} else {
			$json['ok'] = 0 ;

		}
		echo json_encode($json);
		jExit();

	}

	/**
	 * This task creates a child by a given product id
	 *
	 * @author Max Milbers
	 */
	public function createChild(){
		$app = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('product', 'html');

		$model = VmModel::getModel('product');

		//$cids = JRequest::getVar('cid');
		$cids = JRequest::getVar($this->_cidName, JRequest::getVar('virtuemart_product_id',array(),'', 'ARRAY'), '', 'ARRAY');
		//jimport( 'joomla.utilities.arrayhelper' );
		JArrayHelper::toInteger($cids);

		foreach($cids as $cid){
			if ($id=$model->createChild($cid)){
				$msg = JText::_('COM_VIRTUEMART_PRODUCT_CHILD_CREATED_SUCCESSFULLY');
				$redirect = 'index.php?option=com_virtuemart&view=product&task=edit&product_parent_id='.$cids[0].'&virtuemart_product_id='.$id;
			} else {
				$msg = JText::_('COM_VIRTUEMART_PRODUCT_NO_CHILD_CREATED_SUCCESSFULLY');
				$msgtype = 'error';
				$redirect = 'index.php?option=com_virtuemart&view=product';
			}
		}
		$app->redirect($redirect, $msg, $msgtype);

	}

	/**
	* This task creates a child by a given product id
	*
	* @author Max Milbers
	*/
	public function createVariant(){

		$data = JRequest::get('get');
		JRequest::setVar($data['token'], '1', 'post');
		JRequest::checkToken() or jexit('Invalid Token, in ' . JRequest::getWord('task'));

		$app = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('product', 'html');

		$model = VmModel::getModel('product');

		//$cids = JRequest::getVar('cid');
		$cid = JRequest::getInt('virtuemart_product_id',0);

		if(empty($cid)){
			$msg = JText::_('COM_VIRTUEMART_PRODUCT_NO_CHILD_CREATED_SUCCESSFULLY');
// 			$redirect = 'index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id='.$cid;
		} else {
			if ($id=$model->createChild($cid)){
				$msg = JText::_('COM_VIRTUEMART_PRODUCT_CHILD_CREATED_SUCCESSFULLY');
				$redirect = 'index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id='.$cid;
			} else {
				$msg = JText::_('COM_VIRTUEMART_PRODUCT_NO_CHILD_CREATED_SUCCESSFULLY');
				$msgtype = 'error';
				$redirect = 'index.php?option=com_virtuemart&view=product';
			}
			vmdebug('$redirect '.$redirect);
			$app->redirect($redirect, $msg, $msgtype);
		}

	}

	/**
	 * Clone a product
	 *
	 * @author RolandD, Max Milbers
	 */
	public function CloneProduct() {
		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('product', 'html');

		$model = VmModel::getModel('product');
		$msgtype = '';
		//$cids = JRequest::getInt('virtuemart_product_id',0);
		$cids = JRequest::getVar($this->_cidName, JRequest::getVar('virtuemart_product_id',array(),'', 'ARRAY'), '', 'ARRAY');
		//jimport( 'joomla.utilities.arrayhelper' );
		JArrayHelper::toInteger($cids);

		foreach($cids as $cid){
			if ($model->createClone($cid)) $msg = JText::_('COM_VIRTUEMART_PRODUCT_CLONED_SUCCESSFULLY');
			else {
				$msg = JText::_('COM_VIRTUEMART_PRODUCT_NOT_CLONED_SUCCESSFULLY');
				$msgtype = 'error';
			}
		}

		$mainframe->redirect('index.php?option=com_virtuemart&view=product', $msg, $msgtype);
	}


	/**
	 * Get a list of related products, categories
	 * or customfields
	 * @author RolandD
	 * Kohl Patrick
	 */
	public function getData() {

		/* Create the view object. */
		$view = $this->getView('product', 'json');

		/* Now display the view. */
		$view->display(null);
	}

	/**
	 * Add a product rating
	 * @author RolandD
	 */
	public function addRating() {
		$mainframe = Jfactory::getApplication();

		/* Get the product ID */
		// 		$cids = array();
		$cids = JRequest::getVar($this->_cidName, JRequest::getVar('virtuemart_product_id',array(),'', 'ARRAY'), '', 'ARRAY');
		jimport( 'joomla.utilities.arrayhelper' );
		JArrayHelper::toInteger($cids);
		// 		if (!is_array($cids)) $cids = array($cids);

		$mainframe->redirect('index.php?option=com_virtuemart&view=ratings&task=add&virtuemart_product_id='.$cids[0]);
	}

	public function sentProductEmailToCustomer(){


		$cids = JRequest::getVar('virtuemart_product_id', JRequest::getVar('virtuemart_product_id',array(),'', 'ARRAY'), '', 'ARRAY');
		jimport( 'joomla.utilities.arrayhelper' );
		JArrayHelper::toInteger($cids);
		vmdebug('sentProductEmailToCustomer',$cids);
		$db = JFactory::getDbo();
		foreach($cids as $cid){
			$q = 'SELECT `ou`.`title`,`ou`.`company`,`ou`.`last_name`,`ou`.`first_name`,`ou`.`email`
					FROM `#__virtuemart_order_items` as `oi`
					LEFT JOIN `#__virtuemart_orders` as `o` ON `o`.`virtuemart_order_id` = `oi`.`virtuemart_order_id`
					LEFT JOIN `#__virtuemart_order_userinfos` as `ou` ON `o`.`virtuemart_order_id` = `ou`.`virtuemart_order_id`
					WHERE `oi`.`virtuemart_product_id` = "'.(int)$cid.'" AND `address_type` = "BT" GROUP BY `ou`.email';

			$db->setQuery($q);

			$addresses = $db->loadAssocList();
			$err = $db->getErrorMsg();
			if(!empty($err)){
				vmError('sentProductEmailToCustomer '.$err);
			}
			vmdebug('$addresses '.$q,$addresses);

			$vars = array();
			$productModel = VmModel::getModel('product');
			$vars['product'] = $productModel->getProduct($virtuemart_product_id);

			$vendorModel = VmModel::getModel('vendor');
			$VendorEmail = $vendorModel->getVendorEmail($vars['product']->virtuemart_vendor_id);
			$vars['vendor'] = array('vendor_store_name' => $fromName );

			foreach($addresses as $address){

				$user = JFactory::getUser();
				$fromMail = $user->email;
				$fromName = $user->name;
				$vars['user'] = array('name' => $fromName, 'email' => $fromMail);

				$TOMail = JRequest::getVar('email');	//is sanitized then
				$TOMail = str_replace(array('\'','"',',','%','*','/','\\','?','^','`','{','}','|','~'),array(''),$TOMail);

				//recommend and productdetails should be changed, we need a link to the order there.
				if (shopFunctionsF::renderMail('recommend', $TOMail, $vars,'productdetails',true)) {
					$string = 'COM_VIRTUEMART_MAIL_SEND_SUCCESSFULLY';
				}
				else {
					$string = 'COM_VIRTUEMART_MAIL_NOT_SEND_SUCCESSFULLY';
				}
				$mainframe->enqueueMessage(JText::_($string));

				// vmdebug('my email vars ',$vars,$TOMail);
				// Display it all
// 				$view = $this->getView('recommend', 'html');

// 				$view->setLayout('mail_confirmed');
// 				$view->display();
			}
		}

		$app = Jfactory::getApplication();
		$app->redirect('index.php?option=com_virtuemart&view=product');
	}

}
// pure php no closing tag
