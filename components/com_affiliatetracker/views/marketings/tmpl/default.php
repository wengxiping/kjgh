<?php // no direct access

/*------------------------------------------------------------------------
# com_invoices - Invoices for Joomla
# ------------------------------------------------------------------------
# author				Germinal Camps
# copyright 			Copyright (C) 2012 JoomlaFinances.com. All Rights Reserved.
# @license				http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: 			http://www.JoomlaFinances.com
# Technical Support:	Forum - http://www.JoomlaFinances.com/forum
-------------------------------------------------------------------------*/

//no direct access
defined('_JEXEC') or die('Restricted access.');
JHtml::_('jquery.framework');

jimport('joomla.filesystem.file');
require_once(JPATH_COMPONENT . '/phpqrcode.php');


$doc = JFactory::getDocument();
$doc->addScript("components/com_affiliatetracker/js/jquery.qrcode.min.js");
$doc->addStyleSheet("components/com_jblance/css/customer/recommendedList.css");
$itemid = $this->params->get('itemid');
if($itemid != "") $itemid = "&Itemid=" . $itemid;

$affiliate_link = AffiliateHelper::get_account_link();
$atid = AffiliateHelper::getCurrentUserAtid();

?>
<script type="text/javascript">
    //jQuery(function($){
    //    var object={width:"<?php //echo $this->permalink->width?>//",height:"<?php //echo $this->permalink->height?>//",text:"<?php //echo $this->permalink->text?>//"};
    //    $('#qrcode').qrcode(object);
    //});

</script>
<div class="page-header">
    <h1><?php echo JText::_('AFFILIATE_TITLE'); ?></h1>
</div>

<?php echo AffiliateHelper::nav_tabs(); ?>

<?php
$intro = new stdClass();

$intro->text = $this->params->get('textmarketings');

$dispatcher = JDispatcher::getInstance();
$plug_params = new JRegistry('');

JPluginHelper::importPlugin('content');
$results = $dispatcher->trigger('onContentPrepare', array ('com_affiliatetracker.marketings', &$intro, &$plug_params, 0));

echo $intro->text;
?>

<div class="row-fluid at_module_wrapper" >
    <?php
    $modules = JModuleHelper::getModules("at_marketing");
    $document	=JFactory::getDocument();
    $renderer	= $document->loadRenderer('module');
    $attribs 	= array();
    $attribs['style'] = 'xhtml';
    foreach ( @$modules as $mod )
    {
        echo $renderer->render($mod, $attribs);
    }
    ?>
</div>

<div class="card-at-columns">
<div class="card-at-header">邀请您的朋友和客户参与“话事”活动</div>

<?php
$k = 0;
$total_comission = 0 ;
$total = 0 ;

for ($i=0, $n=count( $this->items ); $i < $n; $i++)	{
    $row =$this->items[$i];
//    dump($row);
//    die;
    $html = str_replace(array('{affiliate_link}', '{atid}'), array($affiliate_link, $atid), $row->html_code);
    ?>
        <div class="card-at">
            <div class="card-at-img">
                <img src="" style="width: 100%;height: 100%">
            </div>
            <div class="card-at-des">
                <p class="card-at-des-title"><?php echo $row->title;?></p>
                <p class="card-at-des-herf"><span><?php echo $row->html_code;?></span>  <span>复制链接</span></p>
                <button>查看详情</button>
            </div>
            <div>
                <div class="card-at-img-top card-at-code">
                     <img src="<?php echo QRcode::png_object_clean($html);?>" style="width: 100%;height: 100%">
                </div>
                <p style="font-size: 14px;font-family: MicrosoftYaHei;color: rgba(102,102,102,1);text-align: center;">活动二维码</p>
            </div>


        </div>
    <?php
    $k = 1 - $k;
}
?>

</div>

<script type="text/javascript">
    function showHTML(index) {
        jQuery("#html" + index).removeClass("hidden").select();
    }
</script>

<?php
$modules = JModuleHelper::getModules("at_bottom");
$document	=JFactory::getDocument();
$renderer	= $document->loadRenderer('module');
$attribs 	= array();
$attribs['style'] = 'xhtml';
foreach ( @$modules as $mod )
{
    echo $renderer->render($mod, $attribs);
}
?>
<div align="center"><?php echo AffiliateHelper::showATFooter(); ?></div>
