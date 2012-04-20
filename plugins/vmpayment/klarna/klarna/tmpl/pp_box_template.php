<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$js = '<script type="text/javascript">jQuery(document).find(".product_price").width("25%");</script>';
	$js .= '<style>';
	$js .= 'div.klarna_PPBox{z-index: 200 !important;}';
	$js .= 'div.cbContainer{z-index: 10000 !important;}';
	$js .= 'div.klarna_PPBox_bottomMid{overflow: visible !important;}';
	$js .= '</style>';
	//$html .= '<br>';
	if ($viewData['country'] == 'nl') {
	    $js .= '<style>.klarna_PPBox_topMid{width: 81%;}</style>';
	}
	$document = &JFactory::getDocument();
?>

<tr>
    <td style='text-align: left'>
        <?php echo $viewData['pp_title'] ?>
    </td>
    <td class='klarna_PPBox_pricetag'>
        <?php echo   $viewData['pp_price']   ?>
    </td>
</tr>
