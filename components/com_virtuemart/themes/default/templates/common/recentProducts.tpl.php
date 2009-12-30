<?php 
defined('_JEXEC') or die('Restricted access');

if ($this->recentProducts) {
    ?>
    <br>
    <h4>Recent Products</h4>
    <?php
	for ($i=0, $n=count($this->recentProducts); $i < $n; $i++) {       
	    $product =& $this->recentProducts[$i];
		$productURL = JRoute::_( 'index.php?option=com_virtuemart&view=browse&catid=' . $category->category_id ); 
		?>
		
		product here 
        
    <?php
	}
}

?>