<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<div class="buttons_heading">
<?php 
$pdf_link = "index2.php?option=$option&amp;page=shop.pdf_output&amp;showpage=$page&amp;pop=1&amp;output=pdf&amp;product_id=$product_id&amp;category_id=$category_id";
?>
<?php echo vmCommonHTML::PdfIcon( $pdf_link ); ?>
<?php echo vmCommonHTML::PrintIcon(); ?>
<?php echo vmCommonHTML::EmailIcon($product_id); ?>

</div>