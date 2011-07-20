<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Category
* @author RickG, jseros
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

AdminUIHelper::startAdminArea();
$pane = JPane::getInstance('tabs', array('startOffset' => 0));
$editor = JFactory::getEditor();

$mainframe = JFactory::getApplication();

//making images URL
//$imagesURL = $mainframe->getSiteURL().VmConfig::get('media_category_path');
?>

<form action="index.php" method="post" id="adminForm" name="adminForm" enctype="multipart/form-data">

<?php // Loading Templates in Tabs
AdminUIHelper::buildTabs ( array (	'categoryform' 	=> 	'COM_VIRTUEMART_CATEGORY_FORM_LBL',
									'images' 	=> 	'COM_VIRTUEMART_IMAGE_S'
									 ),$this->_models['category']->_id );
?>
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="virtuemart_category_id" value="<?php echo $this->category->virtuemart_category_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="category" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

<?php AdminUIHelper::endAdminArea(); ?>