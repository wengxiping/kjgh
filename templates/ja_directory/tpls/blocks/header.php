<?php
/*
 * ------------------------------------------------------------------------
 * JA Directory Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

// get params
$sitename  = $this->params->get('sitename');
$slogan    = $this->params->get('slogan', '');
$logotype  = $this->params->get('logotype', 'text');
$logoimage = $logotype == 'image' ? $this->params->get('logoimage', T3Path::getUrl('images/logo.png', '', true)) : '';
$logoimgsm = ($logotype == 'image' && $this->params->get('enable_logoimage_sm', 0)) ? $this->params->get('logoimage_sm', T3Path::getUrl('images/logo-sm.png', '', true)) : false;

if (!$sitename) {
	$sitename = JFactory::getConfig()->get('sitename');
}

$mainnav = 'col-md-10';
if ($headright = $this->countModules('languageswitcherload')) {
	$mainnav = 'col-md-9';
}
$user = JFactory::getUser();
?>

<script type="text/javascript">
    jQuery(document).ready(function($){
        $('.xp-login-sign>.menu>li').each(function(index,item){
            if($(item).text() == '登录' && <?php if(isset($user) && $user->username){ echo 1;}else{echo 0;}?>){
                $(item).html("<a>"+"<?php echo $user->username;?>"+"</a>");
            }
        });
    })
</script>
<div class="body-content">
    <div class="xp-new-header">
        <div class="xp-new-header-container">
            <div class="xp-nav-left">
                <div class="xp-wellcome">- 欢迎来到话事 -</div>

                <div class="xp-login-sign">
                    <?php if ($this->countModules('login-register')) : ?>
                        <jdoc:include type="modules" name="<?php $this->_p('login-register') ?>" />
                    <?php endif ?>
                </div>

                <div class="xp-login-sign" style="display: none;">
                    <?php if(empty($user->username)){?>
                    <div class="xp-login"><a href="<?php echo JRoute::_('index.php?option=com_users&view=login',false);?>">请登录</a></div>
                    <div class="xp-sign"><a href="<?php echo JRoute::_('index.php?option=com_jblance&view=guest&layout=showfront',false);?>">「免费注册」</a></div>
                    <?php }else{
                    ?>
                        <div class="xp-login"><a href="javascript:void(0);"><?php echo $user->username;?></a></div>
<!--                        <div class="xp-sign"><a href="javascript:void(0);">&nbsp;&nbsp;退出</a></div>-->
                        <?php
                    }?>
                </div>

            </div>
            <div class="xp-nav-right">
                <div class="xp-nav-module-content">
                    <jdoc:include type="<?php echo $this->getParam('navigation_type', 'megamenu') ?>" name="<?php echo $this->getParam('mm_type', 'mainmenu') ?>" />
                </div>
            </div>
        </div>
    </div>
</div>
<div class="xp-header-line"></div>
<div class="body-content2">
    <div class="content-box">
        <div class="xp-search-left">
            <a href="<?php echo JURI::base(true) ?>" title="<?php echo strip_tags($sitename) ?>">
                <div class="logo">
                    <div class="logo-<?php echo $logotype, ($logoimgsm ? ' logo-control' : '') ?>">
    <!--                    <a href="--><?php //echo JURI::base(true) ?><!--" title="--><?php //echo strip_tags($sitename) ?><!--">-->
                            <?php if($logotype == 'image'): ?>
                                <img class="logo-img" src="<?php echo JURI::base(true) . '/' . $logoimage ?>" alt="<?php echo strip_tags($sitename) ?>" />
                            <?php endif ?>
                            <?php if($logoimgsm) : ?>
                                <img class="logo-img-sm" src="<?php echo JURI::base(true) . '/' . $logoimgsm ?>" alt="<?php echo strip_tags($sitename) ?>" />
                            <?php endif ?>
                            <span><?php echo $sitename ?></span>

                        <small class="site-slogan"><?php echo $slogan ?></small>
                    </div>
                </div>
<!--                <div class="txt">-->
<!--                    <div class="txt-top">话事</div>-->
<!--                    <div class="txt-bottom">跨境电商外包服务平台</div>-->
<!--                </div>-->
            </a>
        </div>

        <?php if ($this->countModules('subnav')) : ?>
            <!-- SUB NAV -->
                <jdoc:include type="modules" name="<?php $this->_p('subnav') ?>" style="raw"/>
            <!-- //SUB NAV -->
        <?php endif ?>
    </div>

</div>

<div class="clear-header"></div>



<!-- HEADER -->
<header id="t3-header" class="wrap t3-header <?php if ($headright): ?>ja-lang<?php endif ?>" style="display: none;">
<div class="container">
	<div class="row">
		<!-- LANGUAGE SWITCHER -->
		<?php if ($headright): ?>
  			<div class="col-xs-2 col-md-1 pull-left col-sm-6 ja-language">
  				<?php if ($this->countModules('languageswitcherload')) : ?>
  					<!-- LANGUAGE SWITCHER -->
  					<div class="languageswitcherload">
  						<jdoc:include type="modules" name="<?php $this->_p('languageswitcherload') ?>" style="raw" />
  					</div>
  					<!-- //LANGUAGE SWITCHER -->
  				<?php endif ?>
  			</div>
  		<?php endif ?>
		<!-- // LANGUAGE SWITCHER -->
	</div>
</div>
</header>
<!-- //HEADER -->
