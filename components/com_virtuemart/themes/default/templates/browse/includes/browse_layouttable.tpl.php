<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
mm_showMyFileName(__FILE__); ?>

<?php echo $buttons_header // The PDF, Email and Print buttons ?>
<?php echo $browsepage_header // The heading, the category description ?>
<?php echo $parameter_form // The Parameter search form ?>
<?php echo $orderby_form // The sort-by, order-by form PLUS top page navigation ?>

<?php
$data =array(); // Holds the rows of products
$i = 1; $row = 0; // Counters
foreach( $products as $product ) {
		
		foreach( $product as $attr => $val ) {
			// Using this we make all the variables available in the template
			// translated example: $this->set( 'product_name', $product_name );
			$this->set( $attr, $val );
		}
		
		// Parse the product template (usually 'browse_x') for each product
		// and store it in our $data array 
		$data[$row][] = $this->fetch( 'browse/'.$templatefile .'.php' );
		
		// Start a new row ?
		if ( ($i % $products_per_row) == 0) {
			$row++;
		}
		$i++;
		
}
// Creates a new HTML_Table object that will help us
// to build a table holding all the products
$table =& new HTML_Table('width="100%"');

// Loop through each row and build the table
foreach($data as $key => $value ) {
	$table->addRow($data[$key] );
}
// Display the table
echo $table->toHtml();
?>
<br class="clr" />
<?php echo $browsepage_footer ?>
<?php 
// Show Featured Products
if( $this->get_cfg( 'showFeatured', 1 )) {
    /* featuredproducts(random, no_of_products,category_based) no_of_products 0 = all else numeric amount
    edit featuredproduct.tpl.php to edit layout */
    echo $ps_product->featuredProducts(true,10,true);
} ?>
<?php echo $recent_products ?>
