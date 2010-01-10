<?php 
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