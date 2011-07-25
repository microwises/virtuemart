<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author  Patrick Kohl
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.html.php 3006 2011-04-08 13:16:08Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * Json View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author  Patrick Kohl
 */
class VirtuemartViewMedia extends JView {

	/* json object */
	private $json = null;
	
	function display($tpl = null) {

		$virtuemart_media_id = JRequest::getInt('virtuemart_media_id');
		$db = JFactory::getDBO();
		$query='SELECT `file_url`,`file_title` FROM `#__virtuemart_medias` where `virtuemart_media_id`='.$virtuemart_media_id;
		$db->setQuery( $query );
		$json = $db->loadObject();
		if (isset($json->file_url)) { 
			$json->file_url = JURI::root().$json->file_url;
			$json->msg =  'OK';
			echo json_encode($json);
		} else {
			$json->msg =  '<b>'.JText::_('COM_VIRTUEMART_NO_IMAGE_SET').'</b>';
			echo json_encode($json);
		}
	}

}
// pure php no closing tag
