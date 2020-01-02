<?php

/*------------------------------------------------------------------------
# com_affiliatetracker - Affiliate Tracker for Joomla
# ------------------------------------------------------------------------
# author        Germinal Camps
# copyright       Copyright (C) 2014 JoomlaThat.com. All Rights Reserved.
# @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites:       http://www.JoomlaThat.com
# Technical Support:  Forum - http://www.JoomlaThat.com/support
-------------------------------------------------------------------------*/

//no direct access
defined('_JEXEC') or die('Restricted access.'); 

$params =JComponentHelper::getParams( 'com_affiliatetracker' );
JHTML::_('behavior.formvalidation');

?>
<script type="text/javascript">
/* Override joomla.javascript, as form-validation not work with ToolBar */
Joomla.submitbutton = function(pressbutton){
    if (pressbutton == 'cancel') {
        submitform(pressbutton);
    }else{
        var f = document.adminForm;
        if (document.formvalidator.isValid(f)) {
            //f.check.value='<?php echo JSession::getFormToken(); ?>'; //send token
            submitform(pressbutton);    
        }
    
    }    
}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-horizontal form-validate">
  <fieldset class="adminform">
    <legend><?php echo JText::_( 'Log details' ); ?></legend>
    
    <div class="control-group">
      <label class="control-label" for="atid"> <?php echo JText::_( 'AFFILIATE_ACCOUNT' ); ?></label>
      <div class="controls">
        <input class="inputbox" type="text" name="account_name" id="account_name" size="30" maxlength="250" disabled="disabled" value="<?php echo $this->log->account_name;?>" placeholder="<?php echo JText::_( 'NOT_ASSIGNED' ); ?>" />
        <input type="hidden" class="required" value="<?php echo $this->log->atid;?>" name="atid" id="atid" />
        <div class="input-append ">
          <input type="text" name="search_account" id="search_account"  value="" size="30" placeholder="<?php echo JText::_('TYPE_SOMETHING'); ?>" />
          <input type="button" class="btn btn-inverse" id="button_search_account" value="<?php echo JText::_('SEARCH_ACCOUNT'); ?>" />
        </div>
      </div>
    </div>
    
    <div class="control-group">
      <div class="controls">
        <div id="log_accounts"></div>
      </div>
    </div>
    
    <div class="control-group">
      <label class="control-label" for="search_user"> <?php echo JText::_( 'USER' ); ?></label>
      <div class="controls">
        <input class="inputbox" type="text" name="username" id="username" size="30" maxlength="250" disabled="disabled" value="<?php echo $this->log->username;?> [<?php echo $this->log->user_id;?>]" />
        <input type="hidden" value="<?php echo $this->log->user_id;?>" name="user_id" id="user_id" />
        <div class="input-append ">
          <input type="text" name="search_user" id="search_user"  value="" size="30" placeholder="<?php echo JText::_('TYPE_SOMETHING'); ?>" />
          <input type="button" class="btn btn-inverse" id="button_search_user" value="<?php echo JText::_('SEARCH_USER'); ?>" />
        </div>
      </div>
    </div>
    
    <div class="control-group">
      <div class="controls">
        <div id="log_users"></div>
      </div>
    </div>
    
    
  </fieldset>
  <input type="hidden" name="option" value="com_affiliatetracker" />
  <input type="hidden" name="id" value="<?php echo $this->log->id; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="controller" value="log" />
</form>
