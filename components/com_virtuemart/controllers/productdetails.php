<?php
/**
*
* Description
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

// Load the controller framework
jimport('joomla.application.component.controller');

/**
* VirtueMart Component Controller
*
* @package VirtueMart
* @author RolandD
*/
class VirtueMartControllerProductdetails extends JController {

	public function __construct() {
		parent::__construct();
		$this->registerTask( 'recommend','MailForm' );
		$this->registerTask( 'askquestion','MailForm' );
	}

	public function display() {

		$format = JRequest::getWord('format','html');
		if ($format=='pdf') {
			$viewName='Pdf';
		}
		else $viewName='Productdetails';

		$view = $this->getView($viewName, $format);

		$view->display();
	}

	/**
	 * Send the ask question email.
	 * @author Kohl Patrick, Christopher Roussel
	 */
	public function mailAskquestion () {

		JRequest::checkToken() or jexit( 'Invalid Token' );
		if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		$mainframe = JFactory::getApplication();
		$vars = array();
		$min = VmConfig::get('vm_asks_minimum_comment_length', 50)+1;
		$max = VmConfig::get('vm_asks_maximum_comment_length', 2000)-1 ;
		$commentSize = mb_strlen( JRequest::getString('comment') );
		$validMail = filter_var(JRequest::getVar('email'), FILTER_VALIDATE_EMAIL);
		if ( $commentSize<$min || $commentSize>$max || !$validMail ) {
				$this->setRedirect(JRoute::_ ( 'index.php?option=com_virtuemart&tmpl=component&view=productdetails&task=askquestion&virtuemart_product_id='.JRequest::getInt('virtuemart_product_id',0) ),JText::_('COM_VIRTUEMART_COMMENT_NOT_VALID_JS'));
				return ;
		}

		$virtuemart_product_idArray = JRequest::getInt('virtuemart_product_id',0);
		if(is_array($virtuemart_product_idArray)){
			$virtuemart_product_id=(int)$virtuemart_product_idArray[0];
		} else {
			$virtuemart_product_id=(int)$virtuemart_product_idArray;
		}
		$productModel = VmModel::getModel('product');

		$vars['product'] = $productModel->getProduct($virtuemart_product_id);

		$user = JFactory::getUser();
		if (empty($user->id)) {
			$fromMail = JRequest::getVar('email');	//is sanitized then
			$fromName = JRequest::getVar('name','');//is sanitized then
			$fromMail = str_replace(array('\'','"',',','%','*','/','\\','?','^','`','{','}','|','~'),array(''),$fromMail);
			$fromName = str_replace(array('\'','"',',','%','*','/','\\','?','^','`','{','}','|','~'),array(''),$fromName);
		}
		else {
			$fromMail = $user->email;
			$fromName = $user->name;
	 	}
	 	$vars['user'] = array('name' => $fromName, 'email' => $fromMail);

	 	$vendorModel = VmModel::getModel('vendor');
		$VendorEmail = $vendorModel->getVendorEmail($vars['product']->virtuemart_vendor_id);
		$vars['vendor'] = array('vendor_store_name' => $fromName );

		if (shopFunctionsF::renderMail('askquestion', $VendorEmail, $vars,'productdetails')) {
			$string = 'COM_VIRTUEMART_MAIL_SEND_SUCCESSFULLY';
		}
		else {
			$string = 'COM_VIRTUEMART_MAIL_NOT_SEND_SUCCESSFULLY';
		}
		$mainframe->enqueueMessage(JText::_($string));

		// Display it all
		$view = $this->getView('askquestion', 'html');
		$view->setLayout('mail_confirmed');
		$view->display();
	}

	/**
	 * Send the Recommend to a friend email.
	 * @author Kohl Patrick,
	 */
	public function mailRecommend () {

		JRequest::checkToken() or jexit( 'Invalid Token' );

		if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		$mainframe = JFactory::getApplication();
		$vars = array();

		$virtuemart_product_idArray = JRequest::getInt('virtuemart_product_id',0);
		if(is_array($virtuemart_product_idArray)){
			$virtuemart_product_id=(int)$virtuemart_product_idArray[0];
		} else {
			$virtuemart_product_id=(int)$virtuemart_product_idArray;
		}
		$productModel = VmModel::getModel('product');

		$vars['product'] = $productModel->getProduct($virtuemart_product_id);

		$user = JFactory::getUser();
		$fromMail = $user->email;
		$fromName = $user->name;
		$vars['user'] = array('name' => $fromName, 'email' => $fromMail);

	 	$vendorModel = VmModel::getModel('vendor');
		$VendorEmail = $vendorModel->getVendorEmail($vars['product']->virtuemart_vendor_id);
		$vars['vendor'] = array('vendor_store_name' => $fromName );

		$TOMail = JRequest::getVar('email');	//is sanitized then
		$TOMail = str_replace(array('\'','"',',','%','*','/','\\','?','^','`','{','}','|','~'),array(''),$TOMail);
		if (shopFunctionsF::renderMail('recommend', $TOMail, $vars,'productdetails',true)) {
			$string = 'COM_VIRTUEMART_MAIL_SEND_SUCCESSFULLY';
		}
		else {
			$string = 'COM_VIRTUEMART_MAIL_NOT_SEND_SUCCESSFULLY';
		}
		$mainframe->enqueueMessage(JText::_($string));

// 		vmdebug('my email vars ',$vars,$TOMail);
		// Display it all
		$view = $this->getView('recommend', 'html');

		$view->setLayout('mail_confirmed');
		$view->display();
	}

	/**
	 *  Ask Question form
	 * Recommend form for Mail
	 */
	public function MailForm(){

		if (JRequest::getCmd('task') == 'recommend' ) {
			$user = JFactory::getUser();
			//Todo, maybe allow ask a question also for anonymous users?
			if (empty($user->id)) {
				VmInfo(JText::_('YOU MUST LOGIN FIRST'));
				return ;
			}
			$view = $this->getView('recommend', 'html');
		} else {
			$view = $this->getView('askquestion', 'html');
		}

		/* Set the layout */
		$view->setLayout('form');

		// Display it all
		$view->display();
	}

	/* Add or edit a review
	 TODO  control and update in database the review */
	public function review(){

		$data = JRequest::get('post');

		$model = VmModel::getModel('ratings');
		$model->saveRating($data);
		$errors = $model->getErrors();
		if(empty($errors)) $msg = JText::sprintf('COM_VIRTUEMART_STRING_SAVED',JText::_('COM_VIRTUEMART_REVIEW') );
		foreach($errors as $error){
			$msg = ($error).'<br />';
		}

		$this->setRedirect(JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$data['virtuemart_product_id']), $msg);

	}

	/**
	 * Json task for recalculation of prices
	 *
	 * @author Max Milbers
	 * @author Patrick Kohl
	 *
	 */
	public function recalculate(){

		//$post = JRequest::get('request');

//		echo '<pre>'.print_r($post,1).'</pre>';
		jimport( 'joomla.utilities.arrayhelper' );
		$virtuemart_product_idArray = JRequest::getVar('virtuemart_product_id',array());	//is sanitized then
		JArrayHelper::toInteger($virtuemart_product_idArray);
		$virtuemart_product_id = $virtuemart_product_idArray[0];
		$customPrices = array();
		$customVariants = JRequest::getVar('customPrice',array());	//is sanitized then
		foreach($customVariants as $customVariant){
			foreach($customVariant as $priceVariant=>$selected){
				//Important! sanitize array to int
				//JArrayHelper::toInteger($priceVariant);
				$customPrices[$priceVariant]=$selected;
			}
		}
		jimport( 'joomla.utilities.arrayhelper' );
		$quantityArray = JRequest::getVar('quantity',array());	//is sanitized then
		JArrayHelper::toInteger($quantityArray);

		$quantity = 1;
		if(!empty($quantityArray[0])){
			$quantity = $quantityArray[0];
		}

		$product_model = VmModel::getModel('product');

		$prices = $product_model->getPrice($virtuemart_product_id,$customPrices,$quantity);
		$priceFormated = array();
		if (!class_exists('CurrencyDisplay')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');
		$currency = CurrencyDisplay::getInstance();
		foreach ( $prices as $name => $product_price  ){
			$priceFormated[$name] = $currency->createPriceDiv($name,'',$prices,true);
		}

		// Get the document object.
		$document = JFactory::getDocument();
		$document->setName('recalculate');
		JResponse::setHeader('Cache-Control','no-cache, must-revalidate');
		JResponse::setHeader('Expires','Mon, 6 Jul 2000 10:00:00 GMT');
		// Set the MIME type for JSON output.
		$document->setMimeEncoding( 'application/json' );
				JResponse::setHeader('Content-Disposition','attachment;filename="recalculate.json"', true);
				JResponse::sendHeaders();
		echo json_encode ($priceFormated);

	}

	public function getJsonChild() {

	$view = $this->getView('productdetails', 'json');

		$view->display(null);
	}
}
// pure php no closing tag
