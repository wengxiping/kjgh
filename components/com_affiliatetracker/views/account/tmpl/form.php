<?php

/*------------------------------------------------------------------------
# com_invoices - Invoices for Joomla
# ------------------------------------------------------------------------
# author        Germinal Camps
# copyright       Copyright (C) 2012 JoomlaFinances.com. All Rights Reserved.
# @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites:       http://www.JoomlaFinances.com
# Technical Support:  Forum - http://www.JoomlaFinances.com/forum
-------------------------------------------------------------------------*/

//no direct access
defined('_JEXEC') or die('Restricted access.');
$doc    = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/utility.js");
$doc->addStyleSheet("components/com_jblance/css/customer/account.css");

$params = JComponentHelper::getParams( 'com_affiliatetracker' );

$itemid = $params->get('itemid', '');
if($itemid != "") $itemid = "&Itemid=" . $itemid;

$user = JFactory::getUser();
$hasaccount = AffiliateHelper::hasAccounts($user->id);
$select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
$return = base64_encode('index.php?option=com_affiliatetracker&view=account&layout=form&id=0'.$itemid);
JblanceHelper::setJoomBriToken();
$userParams = JComponentHelper::getParams( 'com_users' );
$allowUserRegistration = $userParams->get('allowUserRegistration', 0);

?>
<div class="page-header">
  <p class='account-top-title'>加入联盟立即赚取佣金</p>
</div>

<?php
      $intro = new stdClass();

      $intro->text = $params->get('textnewaccount');

      $dispatcher = JDispatcher::getInstance();
      $plug_params = new JRegistry('');

      JPluginHelper::importPlugin('content');
      $results = $dispatcher->trigger('onContentPrepare', array ('com_affiliatetracker.accounts', &$intro, &$plug_params, 0));

      echo $intro->text;
      ?>

<?php if (empty($user->id)) { ?>
<fieldset class="adminform" id="notLoggedUserSection" style="display: none;">
    <div class="control-group">
        <?php if ($allowUserRegistration) { ?>
        <div class="span6">
            <legend class='legend-title'><?php echo JText::_( 'NEW_JOOMLA_USER' ); ?></legend>
            <p><?php echo JText::_( 'NEW_JOOMLA_ACCOUNT_EXPLANATION' ); ?></p>
            <button class="btn btn-primary" id="registerAccountBtn"><?php echo JText::_( 'REGISTER_ACCOUNT' ); ?></button>
        </div>
        <?php } ?>
        <div class="<?php if ($allowUserRegistration) echo 'span6'; else echo 'span12'; ?>">
            <legend><?php echo JText::_( 'RETURNING_JOOMLA_USER' ); ?></legend>
            <form action="<?php echo JRoute::_('index.php'); ?>" method="post" class="adminForm">
                <fieldset>
                    <label><?php echo JText::_( 'ACCOUNT_USERNAME' ); ?></label>
                    <input type="text" name="username">
                    <label><strong><?php echo JText::_( 'ACCOUNT_PASSWORD' ); ?></strong></label>
                    <input type="password" name="password">
                    <div class="control-group"><button type="submit" class="btn btn-primary"><?php echo JText::_( 'LOGIN' ); ?></button></div>
                    <input type="hidden" name="option" value="com_users">
                    <input type="hidden" name="task" value="user.login">
                    <?php echo JHtml::_('form.token'); ?>
                    <input type="hidden" name="return" value="<?php echo $return ?>" />
                </fieldset>
            </form>
        </div>
    </div>
</fieldset>
<?php } ?>

<div id="formNewAffiliateAccount" class="<?php if (empty($user->id)) echo "hidden__"; ?>">
    <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-horizontal">

      <fieldset class="adminform">
          <legend><?php echo JText::_( 'ACCOUNT_DETAILS' ); ?></legend>

          <div class="control-group">
              <label class="control-label left-label-title" for="account_name"><span style='color:red'>*</span><?php echo JText::_( 'ACCOUNT_NAME' ); ?></label>
              <div class="controls">
                <input class="inputbox detail-info" type="text" name="account_name" id="account_name" size="80" maxlength="250" value="<?php echo $this->account->account_name;?>" <?php if(!$this->account->id) echo "autofocus='autofocus'"; ?> />
              </div>
          </div>
          <div class="control-group newJoomlaAccountField hidden" id="acc_email_group">
              <label class="control-label left-label-title" for="account_email"><span style='color:red'>*</span><?php echo JText::_( 'ACCOUNT_EMAIL' ); ?></label>
              <div class="controls">
                  <input class="inputbox detail-info" type="text" name="account_email" id="account_email" size="80" maxlength="250"/>
                  <span class="help-inline hidden" id="account_email_error"><?php echo JText::_( 'REQUIRED' ); ?></span>
              </div>
          </div>
          <div class="control-group newJoomlaAccountField hidden" id="acc_username_group">
              <label class="control-label left-label-title" for="account_username"><span style='color:red'>*</span> <?php echo JText::_( 'ACCOUNT_USERNAME' ); ?></label>
              <div class="controls">
                  <input class="inputbox detail-info" type="text" name="account_username" id="account_username" size="80" maxlength="250"/>
                  <span class="help-inline hidden" id="account_username_error"><?php echo JText::_( 'REQUIRED' ); ?></span>
              </div>
          </div>
          <div class="control-group newJoomlaAccountField hidden" id="acc_password_group">
              <label class="control-label left-label-title" for="account_password"><span style='color:red'>*</span><?php echo JText::_( 'ACCOUNT_PASSWORD' ); ?></label>
              <div class="controls">
                  <input class="inputbox detail-info" type="password" name="account_password" id="account_password" size="80" maxlength="250"/>
                  <span class="help-inline hidden" id="account_password_error"><?php echo JText::_( 'REQUIRED' ); ?></span>
              </div>
          </div>
          <div class="control-group newJoomlaAccountField hidden" id="acc_repeat_password_group">
              <label class="control-label left-label-title" for="account_password_confirm"><span style='color:red'>*</span>确认密码</label>
              <div class="controls">
                  <input class="inputbox detail-info" type="password" name="account_password_confirm" id="account_password_confirm" size="80" maxlength="250"/>
                  <span class="help-inline hidden" id="account_password_confirm_error"><?php echo JText::_( 'PASSWORDS_NOT_MATCH' ); ?></span>
              </div>
          </div>
        </fieldset>
      <fieldset class="adminform">
        <legend>公司信息(选填)</legend>

        <div class="control-group">
            <label class="control-label left-label-title" for="name"> <?php echo JText::_( 'YOUR_NAME' ); ?></label>
            <div class="controls">
              <input class="inputbox detail-info" type="text" name="name" id="name" size="80" maxlength="250" value="<?php echo $this->account->name;?>" />
            </div>
          </div>
          <div class="control-group">
            <label class="control-label left-label-title" for="company"> <?php echo JText::_( 'COMPANY' ); ?></label>
            <div class="controls">
              <input class="inputbox detail-info" type="text" name="company" id="company" size="80" maxlength="250" value="<?php echo $this->account->company;?>" />
            </div>
          </div>
          <div class="control-group">
            <label class="control-label left-label-title" for="email"> 公司邮箱</label>
            <div class="controls">
              <input class="inputbox detail-info" type="text" name="email" id="email" size="80" maxlength="250" value="<?php echo $this->account->email;?>" />
            </div>
          </div>
          <div class="control-group">
                  <label class="control-label left-label-title" for="email"> 国家</label>
              <div class="controls">
                  <input class="inputbox detail-info" type="text" name="country" id="country" size="80" maxlength="250" value="<?php echo $this->account->country;?>" placeholder="<?php echo JText::_( 'RECIPIENT_COUNTRY_PLACEHOLDER' ); ?>" />
              </div>
          </div>
          <div class="control-group">
              <label class="control-label left-label-title" for="email"> 省份</label>
              <div class="controls">
                  <input class="inputbox detail-info" type="text" name="state" id="state" size="80" maxlength="250" value="<?php echo $this->account->state;?>" placeholder="<?php echo JText::_( 'RECIPIENT_STATE_PLACEHOLDER' ); ?>" />
              </div>
          </div>
          <div class="control-group">
              <label class="control-label left-label-title" for="email"> 城市</label>
              <div class="controls">
                  <input class="inputbox detail-info" type="text" name="city" id="city" size="80" maxlength="250" value="<?php echo $this->account->city;?>" placeholder="<?php echo JText::_( 'RECIPIENT_CITY_PLACEHOLDER' ); ?>" />
              </div>
          </div>

          <div class="control-group"  style="display: none;">
            <label class="control-label left-label-title" for="email"> 联系地址</label>
            <div class="controls">
              <div class='address-select-div'>
                  <div class="xp-right-select" id="location_info">
                      <?php
                      $attribs = array('class' => 'input-medium required', 'data-level-id' => '1', 'onchange' => 'getLocation(this, \'project.getlocationajax\');');

                      echo $select->getSelectLocationCascade('location_level[]', '', '请选择', $attribs, 'level1');
                      ?>
                      <input type="hidden" name="id_location" id="id_location" value="" />
                      <div id="ajax-container" class="dis-inl-blk"></div>
                  </div>
              </div>
            </div>
          </div>

          <div class="control-group">
            <label class="control-label left-label-title" for="address"> 街道门牌</label>
            <div class="controls">
              <textarea class='detail-info-textarea' name="address" id="address" cols="40" rows="4"><?php echo $this->account->address; ?></textarea>
            </div>
          </div>

        <!-- 设计稿没有，暂时隐藏 -->
        <div class="control-group" style='display:none'>
            <label class="control-label" for="city"> <?php echo JText::_( 'LOCATION_CITY' ); ?></label>
            <div class="controls">
              <input class="inputbox input-small" type="text" name="zipcode" id="zipcode" size="80" maxlength="250" value="<?php echo $this->account->zipcode;?>" placeholder="<?php echo JText::_( 'RECIPIENT_ZIPCODE_PLACEHOLDER' ); ?>" />
              <input class="inputbox input-small" type="text" name="city" id="city" size="80" maxlength="250" value="<?php echo $this->account->city;?>" placeholder="<?php echo JText::_( 'RECIPIENT_CITY_PLACEHOLDER' ); ?>" />

            </div>
          </div>
          <!-- 设计稿没有，暂时隐藏 -->
          <div class="control-group" style='display:none'>
            <label class="control-label" for="country"> <?php echo JText::_( 'LOCATION_COUNTRY' ); ?></label>
            <div class="controls">
              <input class="inputbox input-small" type="text" name="state" id="state" size="80" maxlength="250" value="<?php echo $this->account->state;?>" placeholder="<?php echo JText::_( 'RECIPIENT_STATE_PLACEHOLDER' ); ?>" />
              <input class="inputbox input-small" type="text" name="country" id="country" size="80" maxlength="250" value="<?php echo $this->account->country;?>" placeholder="<?php echo JText::_( 'RECIPIENT_COUNTRY_PLACEHOLDER' ); ?>" />
            </div>
          </div>
          <div class="control-group">
            <label class="control-label left-label-title" for="phone"> 邮编</label>
            <div class="controls">
              <input class="inputbox detail-info" type="text" name="zipcode" id="zipcode" size="80" maxlength="250" value="<?php echo $this->account->zipcode;?>" />
            </div>
          </div>

        </fieldset>

        <fieldset class="invoicesfieldset">
        <legend><?php echo JText::_( 'PAYMENT_OPTIONS' ); ?></legend>
        <?php

        $payment_options = json_decode($this->account->payment_options);
        if(!is_object($payment_options)) $payment_options = new stdClass();

        // load the plugin
          $import = JPluginHelper::importPlugin( strtolower( 'Affiliates' ) );
        // fire plugin
          $dispatcher = JDispatcher::getInstance();
          $the_payment_options = $dispatcher->trigger( 'onRenderPaymentInputOptions', array( $payment_options ) );

          if(count($the_payment_options)){

            $pane = '1';

            echo JHtml::_('tabs.start', "pane_$pane");

            foreach($the_payment_options as $method){

              echo JHtml::_('tabs.panel', JText::_( $method[1] ), $method[1]);
              ?>

                        <?php echo $method[0]; ?>
                        <?php

            }

            echo JHtml::_('tabs.end');

          }
          else echo JText::_( 'NO_PAYMENT_OPTIONS_AVAILABLE' );
        ?>
        </fieldset>

        <?php if($params->get('show_terms', 1)){ ?>
        <div class="control-group">
          <div class="controls">
            <div class="checkbox">
              <label >
                <input type="checkbox" value="1" id="terms" name="terms">
                <a href="#" onclick="jQuery('#termsmodal').modal('show')">阅读并接受平台条款与协议。</a>
              </label>
            </div>
          </div>
        </div>
        <?php } ?>

        <div class="form-actions account-foot">
          <a href="<?php echo JRoute::_('index.php?option=com_affiliatetracker&task=cancel'); ?>" class="btn cancel-a">上一步</a>
          <button type="submit" id="submitAccountBtn" class="btn creat-form" ><?php echo JText::_('SUBMIT_ACCOUNT'); ?></button>
        </div>
        <input type="hidden" name="option" value="com_affiliatetracker" />
        <input type="hidden" name="id" value="<?php echo $this->account->id; ?>" />
        <input type="hidden" name="task" value="save_account" />
    </form>
</div>

<?php if($params->get('show_terms', 1)){ ?>
<div class="modal hide fade" id="termsmodal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3><?php echo JText::_('TERMS_AND_CONDITIONS'); ?></h3>
  </div>
  <div class="modal-body">
    <?php echo $params->get('terms'); ?>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('CLOSE'); ?></button>
    <a href="#" class="btn btn-primary" onclick="jQuery('#terms').prop('checked', true);" data-dismiss="modal"><?php echo JText::_('ACCEPT'); ?></a>
  </div>
</div>

<script type="text/javascript">
  jQuery( "#adminForm" ).submit(function( event ) {
    if(!jQuery('#terms').prop('checked')) {
      alert("<?php echo JText::_('PLEASE_ACCEPT_TERMS'); ?>");
      jQuery('#terms').focus();
      event.preventDefault();
    }
  });
</script>
<?php } ?>
