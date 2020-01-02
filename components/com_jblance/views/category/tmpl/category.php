<?php
/**
 * @company        :    BriTech Solutions
 * @created by    :    JoomBri Team
 * @contact        :    www.joombri.in, support@joombri.in
 * @created on    :    28 March 2012
 * @file name    :    modules/mod_jblancecategory/tmpl/default.php
 * @copyright   :    Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :    GNU General Public License version 2 or later
 * @author      :    Faisel
 * @description    :    Entry point for the component (jblance)
 */
defined('_JEXEC') or die('Restricted access');

$Itemid = ($this->setItemId > 0) ? '&amp;Itemid=' . $this->setItemId : '';

$document = JFactory::getDocument();
$direction = $document->getDirection();
$document->addStyleSheet("components/com_jblance/css/style.css");
$document->addStyleSheet("modules/mod_jblancecategory/css/style.css");

if ($direction === 'rtl')
    $document->addStyleSheet("modules/mod_jblancecategory/css/style-rtl.css");

$config = JblanceHelper::getConfig();

if ($config->loadBootstrap) {
    JHtml::_('bootstrap.loadCss', true, $direction);
}
//var_dump(ModJblanceCategoryHelper::getSubCategories(1,1,'',''));
//calculate span with
$spanCount = 12 / $this->total_column;
$span = 'span' . $spanCount;

if (count($this->rows) > 0) { ?>
    <?php
    foreach ($this->rows as $row) { ?>
        <div class="new-row-fluid">
            <div class="fluid-background">
            <?php if (is_array($this->selectChooseIdArray) && in_array($row->id, $this->selectChooseIdArray)) { ?>
                <div class="head"><img src="/templates/ja_directory/acm/gallery/images/left-arrow.png"><strong><?php echo $row->category; ?></strong></div>
                <!-- <div class="img-banner">banner</div> -->
                <div class="xp-new-span">
                    <?php
                    $subs = ModJblanceCategoryHelperNew::getSubCategories($row->id, $this->show_empty_count, '', '');
                    foreach ($subs as $sub) {
                        $link_proj_categ = JRoute::_('index.php?option=com_jblance&amp;view=project&amp;layout=searchproject&amp;id_categ=' . $sub->id . '&amp;type=category' . $Itemid); ?>
                        <?php if (in_array($sub->id, $this->selectChooseIdArray)) { ?>
                            <div class="test">
                                <div class="xp-category-list">
                                    <a href="<?php echo $link_proj_categ; ?>">
                                        <?php echo $sub->category; ?>
                                        <?php
                                        if ($this->show_count) {
                                            if($sub->thecount){
                                                echo '<span class="label label-info xp-label">' . $sub->thecount . '</span>';//' '('.$sub->thecount.')';
                                            }else{
                                                echo '<span class="label label-info xp-zero-label">' . $sub->thecount . '</span>';//' '('.$sub->thecount.')';
                                            }

                                        }
                                        ?>
                                    </a>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            <?php } ?>
            </div>
        </div>
        <?php
    }
}
?>
<div style="clear: both;"></div>
<style>
    #t3-mainbody{
        padding: 0!important;width: 100% !important;margin:16px auto 0!important;display: flex;justify-content: center;align-items: center;
    }
    #t3-mainbody .row{margin: 0!important;padding: 0!important;width: 1200px!important;}
    #t3-mainbody .row #t3-content{
        padding: 0!important;margin: 0!important;    position: relative;
        z-index: 99;
    }
    .new-jb-bs{
         width: 1200px;
         column-count: 4; //多列的列数
         column-gap: 8px;//列间距
    }
    .new-jb-bs .new-row-fluid{
        width: 290px;break-inside:avoid;
    }
    .new-jb-bs .new-row-fluid .fluid-background{
        background: #FFFFFF;display: flex;flex-direction: column;margin-bottom: 8px;
    }
    .new-jb-bs .new-row-fluid:nth-child(4n-3){
        margin-left: 0!important;
    }
    .new-jb-bs .new-row-fluid .xp-new-span{
        width: 290px;
        margin: 0!important;padding: 8px 18px 10px!important;
        display: flex;
        justify-content:space-between;
        align-items: center;
        flex-direction: row;
        flex-wrap: wrap;
    }
    .new-jb-bs .new-row-fluid .xp-new-span .test{
        min-width: 126px;height: 30px;display: flex;justify-content: flex-start;align-items: center;margin-bottom: 8px;
    }
    .new-jb-bs .new-row-fluid .xp-new-span .test a{
        opacity: 1;
        font-size: 14px;
        font-family: MicrosoftYaHei;
        color: rgba(102,102,102,1);
        letter-spacing: 0px;
    }
    .new-jb-bs .new-row-fluid .xp-new-span .test a:hover{
        text-decoration: underline;
        color: rgba(255,96,16,1) !important;
    }
    .new-jb-bs .new-row-fluid .head{
        display: flex;justify-content: flex-start;align-items: center;padding: 16px 0 6px 0;
    }
    .new-jb-bs .new-row-fluid .head strong{
        opacity: 1;
        font-size: 16px;
        font-family: MicrosoftYaHei;
        font-weight: 700;
        color: rgba(255,96,16,1);
        letter-spacing: 0px;
    }
    .new-jb-bs .new-row-fluid .img-banner{
        width: 262px;
        height: 28px;
        margin: auto;
        background: #FFF2B3;
        text-align: center;
        line-height: 28px;
        color:#FF8F1F;
    }
    .new-jb-bs .new-row-fluid .head img{
        margin-right: 8px;
    }
    .xp-label{
        background: #FF8F1F!important;
    }
    .xp-zero-label{
        background: #CCCCCC!important;
    }
    /* .fluid-background .img-banner {
    display: none;
    } */
    .t3-section-2>.fist-merchant {
      display: none
    }
    body .t3-section-2 {
        background-color: transparent;
    }
    .xp-content {
        margin-top: 8px;
    }
    .xp-content .xp-gallery-content>.isotope-layout>.xp-content-border>.list-content {
        border: none;
        border-left: 1px #f9fafa solid;
        padding: 0 10px;
        margin: 10px 0;
    }
    .xp-content .xp-gallery-content>.isotope-layout>.xp-content-border>.list-content:first-child {
        border-color: transparent;
    }
    .xp-content .xp-gallery-content>.isotope-layout>.xp-content-border>.list-content .item-image {
        width: 100%;
        max-height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .xp-row .new-client-img {
    padding: 0 10px;
    }
</style>
