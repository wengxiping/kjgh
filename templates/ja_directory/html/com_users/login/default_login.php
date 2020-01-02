<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
?>
<script type="text/javascript">

    jQuery(document).ready(function($){
        $("#xp-checkbox-remember").click(function () {
              if($(this).find('div').hasClass('img')){
                  $(this).find('.img').removeClass('img').addClass('select-img');
                  $("#remember").val('yes');
              }else{
                  $(this).find('.select-img').removeClass('select-img').addClass('img');
                  $("#remember").val('no');
              }
        });

        $("#group-line-middle").click(function(){
            if($("#group-list").hasClass("group-hidden")){//如果存在的话
                $("#group-list").removeClass('group-hidden');
                $(this).find('.down-img').removeClass('down-img').addClass('up-img');
            }else{
                $("#group-list").addClass('group-hidden');
                $(this).find('.up-img').removeClass('up-img').addClass('down-img')
            }
        });
        $("#username").attr('placeholder','请输入用户名/邮箱');
        // $('#username').addClass('xp-login-placeholder');
        $("#password").attr('placeholder','请输入密码');
        // $('#password').addClass('xp-login-placeholder');
    });

</script>
<div class="xp-login-container">
 <div class="login-left">
     <div class="login-txt-content">
         <div class="login-txt-top">有话事，没难事</div>
         <div class="login-txt-bottom">跨境电商众包服务平台</div>
     </div>
 </div>
<div class="login-wrap xp-login-container">

  <div class="login <?php echo $this->pageclass_sfx?>">
  	<?php if ($this->params->get('show_page_heading')) : ?>
  	<div class="page-header">
  		<h1>
  			<?php echo $this->escape($this->params->get('page_heading')); ?>
  		</h1>
  	</div>
  	<?php endif; ?>

  	<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
  	<div class="login-description">
  	<?php endif; ?>

  		<?php if($this->params->get('logindescription_show') == 1) : ?>
  			<?php echo $this->params->get('login_description'); ?>
  		<?php endif; ?>

  		<?php if (($this->params->get('login_image')!='')) :?>
  			<img src="<?php echo $this->escape($this->params->get('login_image')); ?>" class="login-image" alt="<?php echo JTEXT::_('COM_USER_LOGIN_IMAGE_ALT')?>"/>
  		<?php endif; ?>

  	<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
  	</div>
  	<?php endif; ?>

  	<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post" class="form-horizontal" autocomplete="off">
        <div  class="form-txt">
            <div class="form-head">登录</div>
            <div class="form-subhead">欢迎来到话事</div>
        </div>
      <div class="xp-fieldset">
  			<?php foreach ($this->form->getFieldset('credentials') as $field): ?>
  				<?php if (!$field->hidden): ?>
  					<div class="xp-form-group">
  						<div class="xp-ipt">

  							<?php echo $field->input; ?>
  						</div>
  					</div>
  				<?php endif; ?>
  			<?php endforeach; ?>

			<?php $tfa = JPluginHelper::getPlugin('twofactorauth'); ?>

			<?php if (!is_null($tfa) && $tfa != array()): ?>
				<div class="form-group">
					<div class="xp-ipt">
						<?php echo $this->form->getField('secretkey')->input; ?>
					</div>
				</div>
			<?php endif; ?>

  			<div class="xp-form-group xp-submit">
  				<div class="xp-group">
  					<button type="submit" class=""><?php echo JText::_('JLOGIN'); ?></button>
  				</div>
  			</div>

          <?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
              <div class="xp-new-form-group">
                  <div class="xp-new-group">
                      <div class="xp-checkbox" >
                          <label id="xp-checkbox-remember" >
                              <div class="img"></div>
                              <input id="remember" type="hidden" name="remember" value="yes"/>
                              <?php echo JText::_(version_compare(JVERSION, '3.0', 'ge') ? 'COM_USERS_LOGIN_REMEMBER_ME' : 'JGLOBAL_REMEMBER_ME') ?>
                          </label>
                            <div class="line"></div>
                          <a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
                              <?php echo JText::_('COM_USERS_LOGIN_RESET'); ?>?</a>
                      </div>
                      <div class="xp-other-links">
                          <div class="xp-left">还没有账号？</div>
                          <div class="xp-right"><a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>"><?php echo JText::_('COM_USERS_LOGIN_REGISTER'); ?></a>
                          </div>
                      </div>
                  </div>
              </div>
          <?php endif; ?>

  			<?php if ($this->params->get('login_redirect_url')) : ?>
          <input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('login_redirect_url', $this->form->getValue('return'))); ?>" />
        <?php else : ?>
          <input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('login_redirect_menuitem', $this->form->getValue('return'))); ?>" />
        <?php endif; ?>
  			<?php echo JHtml::_('form.token'); ?>
  		</div>
<!--        <div class="xp-other-login-group">-->
<!--           <div class="group-line">-->
<!--               <div class="group-line-left"></div>-->
<!--               <div class="group-line-middle" id="group-line-middle">-->
<!--                   <div class="left">其它服务登录</div>-->
<!--                   <div class="right"><div class="down-img"></div>-->
<!--                   </div>-->
<!--               </div>-->
<!--               <div class="group-line-right"></div>-->
<!--           </div>-->
<!--            <div class="group-content group-hidden" id="group-list">-->
<!--                <div class="item"><div class="left">阿米猪合伙人</div><div class="right"><a href="http://www.baidu.com">爱帮客官网</a></div></div>-->
<!--                <div class="item"><div class="left">阿米猪亚马逊运营管理</div><div class="right"><a href="http://www.baidu.com">话事外包服务</a></div></div>-->
<!--                <div class="item"><div class="left">DigDeals优惠券平台</div><div class="right"><a href="http://www.baidu.com">阿瓦力邮件营销</a></div></div>-->
<!--                <div class="item"><div class="left">哇宝独立商城</div><div class="right"><a href="http://www.baidu.com">大数粉测</a></div></div>-->
<!--            </div>-->
<!--        </div>-->

<!--      <div class="other-links form-group">-->
<!--        <div class="col-sm-12">-->
<!--        <ul>-->
<!--          <li><a href="--><?php //echo JRoute::_('index.php?option=com_users&view=reset'); ?><!--">-->
<!--            --><?php //echo JText::_('COM_USERS_LOGIN_RESET'); ?><!--</a></li>-->
<!--          <li><a href="--><?php //echo JRoute::_('index.php?option=com_users&view=remind'); ?><!--">-->
<!--            --><?php //echo JText::_('COM_USERS_LOGIN_REMIND'); ?><!--</a></li>-->
<!--          --><?php
//          $usersConfig = JComponentHelper::getParams('com_users');
//          if ($usersConfig->get('allowUserRegistration')) : ?>
<!--          <li><a href="--><?php //echo JRoute::_('index.php?option=com_users&view=registration'); ?><!--">-->
<!--              --><?php //echo JText::_('COM_USERS_LOGIN_REGISTER'); ?><!--</a></li>-->
<!--          --><?php //endif; ?>
<!--        </ul>-->
<!--        </div>-->
<!--      </div>-->

  	</form>

  </div>

</div>
</div>
