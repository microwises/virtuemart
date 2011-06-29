<?php
/**
*
* Product details view
*
* @package VirtueMart
* @subpackage
* @author RolandD
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport( 'joomla.application.component.view' );

/**
* Product details
*
* @package VirtueMart
* @author RolandD
* @author Max Milbers
* @author Christopher Roussel
*/
class VirtueMartViewAskquestion extends JView {

	function renderMail() {
		$this->setLayout('mail_raw_question');
		$this->comment = JRequest::getString('comment');
	 	$this->subject = JText::_('COM_VIRTUEMART_QUESTION_ABOUT').$this->product->product_name;
	 	parent::display();
	}

}

// pure php no closing tag