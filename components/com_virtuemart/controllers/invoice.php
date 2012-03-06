<?php
/**
 *
 * Controller for the front end Orderviews
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
 * @version $Id: orders.php 5432 2012-02-14 02:20:35Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

/**
 * VirtueMart Component Controller
 *
 * @package		VirtueMart
 */
class VirtueMartControllerInvoice extends JController
{

	public function display() {

		$format = JRequest::getWord('format','html');
		if ($format=='pdf') {
			$viewName='Pdf';
		}
		else $viewName= 'Invoice';

		$view = $this->getView($viewName, $format);

		$view->display();
	}

	function checkStoreInvoice($orderDetails = 0){

		vmdebug('checkStoreInvoice start');

		$force = true;

		//	@ini_set( 'max_execution_time', 5 );

		$path = VmConfig::get('forSale_path',0);
		if($path===0 ){
			vmError('No path set to store invoices');
			return false;
		} else {
			$path .= 'invoices'.DS;
			if(!file_exists($path)){
				vmError('Path wrong to store invoices, folder invoices does not exist '.$path);
				return false;
			} else if(!is_writable( $path )){
				vmError('Cannot store pdf, directory not writeable '.$path);
				return false;
			}
		}

		$orderModel = VmModel::getModel('orders');

		$invoiceNumber = $orderModel->createInvoiceNumber($orderDetails['details']['BT']);

		if(!$invoiceNumber or empty($invoiceNumber)){
			vmError('Cant create pdf, createInvoiceNumber failed');;
			return 0;
		}
		$path .= 'vminvoice_'.$invoiceNumber.'.pdf';


		if(file_exists($path) and !$force){
			return $path;
		}

		// 			$app = JFactory::getApplication('site');

		//We come from the be, so we need to load the FE langauge
		$jlang =& JFactory::getLanguage();
		$jlang->load('com_virtuemart', JPATH_SITE, 'en-GB', true);
		$jlang->load('com_virtuemart', JPATH_SITE, $jlang->getDefault(), true);
		$jlang->load('com_virtuemart', JPATH_SITE, null, true);

		$this->addViewPath( JPATH_VM_SITE.DS.'views' );
		$format = 'html';
		$viewName= 'invoice';
		$view = $this->getView($viewName, $format);

		$view->addTemplatePath( JPATH_VM_SITE.DS.'views'.DS.'invoice'.DS.'tmpl' );

		$view->invoiceNumber = $invoiceNumber;
		$view->orderDetails = $orderDetails;
		$view->uselayout = 'invoice';

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();


		if(!class_exists('vmPdf'))	require(JPATH_VM_SITE.DS.'helpers'.DS.'vmpdf.php');
		$vmPdf =  new vmPdf();
		$path = $vmPdf->createVmPdf($view);
		return $path;
	}
}







// No closing tag
