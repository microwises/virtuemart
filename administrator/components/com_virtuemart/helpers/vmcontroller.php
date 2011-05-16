<?php
/**
 * abstract controller class containing get,store,delete,publish and pagination
 *
 *
 * This class provides the functions for the calculatoins
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

class VmController extends JController{



	public function setMainLangKey($langkey){
		$this->mainLangKey = $langkey;
	}

	/**
	 * Handle the remove task
	 *
	 * @author Max Milbers
	 */
	function remove(){
		$data = JRequest::get( 'post' );
		$model = $this->getModel();
		if (!$model->delete()) {
			$msg = JText::_($this->mainLangKey.'_COULD_NOT_BE_DELETED');
		}
		else {
			$msg = JText::_($this->mainLangKey.'_DELETED');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=state&virtuemart_country_id='.$data["virtuemart_country_id"], $msg);
	}

	/**
	 * Handle the cancel task
	 *
	 * @author Max Milbers
	 */
	public function cancel(){
		$msg = JText::_($this->mainLangKey.'_CANCELLED'); //'COM_VIRTUEMART_OPERATION_CANCELED'
		$this->setRedirect('index.php?option=com_virtuemart&view=calc', $msg);
	}

	/**
	 * Handle the publish task
	 *
	 * @author Jseros, Max Milbers
	 */
	public function publish(){
		// Check token
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$model = $this->getModel();
		if (!$model->publish(true)) {
			$msg = JText::_($this->mainLangKey.'_PUBLISHED_ERROR');
		}
		else{
			$msg = JText::_($this->mainLangKey.'_PUBLISHED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=calc', $msg);
	}


	/**
	 * Handle the publish task
	 *
	 * @author Max Milbers, Jseros
	 */
	function unpublish(){
		// Check token
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$model = $this->getModel();
		if (!$model->publish(true)) {
			$msg = JText::_($this->mainLangKey.'_UNPUBLISHED_ERROR');
		}
		else{
			$msg = JText::_($this->mainLangKey.'_UNPUBLISHED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=calc', $msg);
	}
}