<?php defined('_JEXEC') or die('Restricted access'); ?>
<tr>
    <td style='text-align: left'>
        <?php echo $params['pp_title'] ?>
    </td>
    <td class='klarna_PPBox_pricetag'>
        <?php echo $this->settings['currency_prefix'] . $params['pp_price'] . $this->settings['currency_suffix'] ?>
    </td>
</tr>
<?php