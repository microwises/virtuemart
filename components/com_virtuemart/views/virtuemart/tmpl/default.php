<?php
/**
*
* Description
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
* @version $Id$
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>

<link rel="stylesheet" href="<?php echo VM_THEMEPATH.DS.VM_THEMENAME.DS.'theme.css'; ?>" type="text/css" />
        
<div id="store">

<?php    
// Display a list of child categories
include(VM_THEMEPATH.DS.VM_THEMENAME.DS.'templates'.DS.'common'.DS.'categoryChildlist.tpl.php');

// Display the featured products
include(VM_THEMEPATH.DS.VM_THEMENAME.DS.'templates'.DS.'common'.DS.'featuredProducts.tpl.php');
?>

</div>