<?php
/**
*
* View class for the product
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
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * RAW text view class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author RolandD,Max Milbers, Christopher Roussel
 */
class VirtuemartViewProduct extends JView {
	function renderMail() {
		$this->setLayout('mail_raw_waitlist');
		$this->subject = JText::sprintf('COM_VIRTUEMART_PRODUCT_WAITING_LIST_EMAIL_SUBJECT', $this->productName);
		$notice_body = JText::sprintf('COM_VIRTUEMART_PRODUCT_WAITING_LIST_EMAIL_TEXT', $this->productName, $this->url);

		parent::display();
	}
}

//pure php no closing tag
