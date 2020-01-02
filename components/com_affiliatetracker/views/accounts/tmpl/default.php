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
$doc    = JFactory::getDocument();
$doc->addStyleSheet("components/com_jblance/css/customer/accounts.css");

$itemid = $this->params->get('itemid');
if($itemid != "") $itemid = "&Itemid=" . $itemid;

$uri = JFactory::getURI();

?>

<div class="page-header">
  <p class='member-centre-title-p'><?php echo JText::_('AFFILIATE_TITLE'); ?></p>
</div>
<div style='margin-top:16px'>
  <div class='row card-container'>
    <div class="col-md-3 col-lg-3 animation">
      <div class='card'>
        <p>我的邀请</p>
        <div class='card-content'>
          <p><?php
              echo $this->invite_num;?></p>
          <a href="<?php echo JRoute::_('index.php?option=com_invitex&view=stats')?>">邀请记录</a>
          <span>
            <img src="components/com_jblance/images/member-centre/arrows.png" alt="" class='arrows-img'>
          </span>
          <div class='btn-container'>
              <button><a href="<?php echo JRoute::_('index.php?option=com_invitex&view=invites');?>">发送邀请</a></button>
        </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-lg-3 animation">
    <div class='card'>
        <p>活动推广</p>
        <div class='card-content'>
          <p><?php echo $this->logs; ?></p>
          <a href="<?php echo JRoute::_('index.php?option=com_affiliatetracker&view=logs')?>">活动推广</a>
          <span>
            <img src="components/com_jblance/images/member-centre/arrows.png" alt="" class='arrows-img'>
          </span>
          <div class='btn-container'>
              <button><a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=categories&layout=events')?>">推荐活动</a></button>
        </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-lg-3 animation">
      <div class='card'>
        <p>会员贡献</p>
        <div class='card-content'>
          <p><?php echo $this->conversions; ?></p>
          <a href="<?php echo JRoute::_('index.php?option=com_affiliatetracker&view=conversions')?>">贡献记录</a>
          <span>
            <img src="components/com_jblance/images/member-centre/arrows.png" alt="" class='arrows-img'>
          </span>
          <div class='btn-container'>
            <button><a <?php echo JRoute::_('index.php?option=com_affiliatetracker&view=conversions')?>>我的贡献</a></button>
        </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-lg-3 animation">
      <div class='card'>
        <p>我的佣金</p>
        <div class='card-content'>
          <p><?php echo AffiliateHelper::format($this->comission_value); ?></p>
          <a href="<?php echo JRoute::_('index.php?option=com_affiliatetracker&view=payments')?>">赚取记录</a>
          <span>
            <img src="components/com_jblance/images/member-centre/arrows.png" alt="" class='arrows-img'>
          </span>
          <div class='btn-container'>
            <button><a href="<?php echo JRoute::_('index.php?option=com_affiliatetracker&view=payments')?>">提现</a></button>
        </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$user = JFactory::getUser();
$hasaccount = AffiliateHelper::hasAccounts($user->id);
if($hasaccount){

  ?>
  <?php echo AffiliateHelper::nav_tabs(); ?>
  <?php
  $intro = new stdClass();

  $intro->text = $this->params->get('textaccounts');

  $dispatcher = JDispatcher::getInstance();
  $plug_params = new JRegistry('');

  JPluginHelper::importPlugin('content');
  $results = $dispatcher->trigger('onContentPrepare', array ('com_affiliatetracker.accounts', &$intro, &$plug_params, 0));

  echo $intro->text;
  ?>
  <form style="display: none" action="<?php echo JRoute::_("index.php?option=com_affiliatetracker&view=account".$itemid); ?>"
    method="get"
    name="adminForm"
    id="adminForm"
    class="form-horizontal">

    <div class="row-fluid at_module_wrapper" >
      <?php
      $modules = JModuleHelper::getModules("at_account");
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

    <?php echo AffiliateHelper::time_options(); ?>
    <!-- 设计稿没有，暂时隐藏 -->
    <div class="at_totals" style=''>
      <div class="row-fluid ">
        <div class="span4 text-center">
          <div class="big_number"><?php echo $this->logs; ?></div>
          <span class="label label-warning"><?php echo JText::_('REFERRALS'); ?></span><br />
          <span class="muted"><?php echo JText::_('LAST_'.$this->timespan.'_DAYS'); ?></span> </div>
          <div class="span4 text-center">
            <div class="big_number"><?php echo $this->conversions; ?></div>
            <span class="label label-info"><?php echo JText::_('CONVERSIONS'); ?></span><br />
            <span class="muted"><?php echo JText::_('LAST_'.$this->timespan.'_DAYS'); ?></span> </div>
            <div class="span4 text-center">
              <div class="big_number"><?php echo AffiliateHelper::format($this->comission_value); ?></div>
              <span class="label label-success"><?php echo JText::_('COMISSION'); ?></span><br />
              <span class="muted"><?php echo JText::_('LAST_'.$this->timespan.'_DAYS'); ?></span> </div>
            </div>
          </div>
          <br  />
          <!-- 设计稿没有，暂时隐藏 -->
          <div class="table_scrolls" style=''>
            <table class="table table-striped logs_table items_table_responsive">
              <thead>
                <tr>
                  <th colspan="2"> <?php echo JHTML::_( 'grid.sort', 'ACCOUNT', 'acc.id', $this->lists['order_Dir'], $this->lists['order']); ?> </th>
                  <th class=""><?php echo JText::_('YOUR_LINK'); ?></th>
                  <th class=""><?php echo JText::_('STATUS'); ?></th>
                  <th class=""><?php echo JText::_('COMISSION_PER_CONVERSION'); ?></th>
                  <th class=""></th>
                </tr>
              </thead>
              <?php
              $k = 0;
              $total_comission = 0 ;
              $total = 0 ;

              for ($i=0, $n=count( $this->items ); $i < $n; $i++)	{
                $row =$this->items[$i];
                $checked 	= JHTML::_('grid.id',   $i, $row->id );
                $link_account 		= JRoute::_( 'index.php?option=com_affiliatetracker&view=account&layout=form&id='. $row->id .$itemid);

                ?>
                <tr class="<?php echo "row$k"; ?>">
                  <td><?php echo $row->account_name; ?></td>
                  <td width="24"><a href="<?php echo $link_account; ?>" data-original-title="<?php echo JText::_('CLICK_EDIT_ACCOUNT'); ?>" rel="tooltip"><i class="icon-edit hide-icon"></i></a></td>
                  <td class=""><?php echo AffiliateHelper::get_account_link( $row->id , $row->ref_word); ?></td>
                  <td class=""><?php echo AffiliateHelper::account_status( $row ); ?></td>
                  <td><?php

                  switch($row->type){
                    case "percent":
                    echo $row->comission."%";
                    break;
                    default:
                    echo AffiliateHelper::format($row->comission);
                    break;

                  }

                  ?></td>
                  <td>
                    <?php $numChilds = AffiliateHelper::getNumChilds($row->id);
                    if (!empty($row->refer_url) || $numChilds != 0) { ?>
                      <div class="buttonMoreDetails" onclick="showMore('<?php echo $row->id; ?>');"><?php echo JText::_('VIEW_MORE_PROFILE'); ?> <i class="icon-chevron-down"></i></div>
                    <?php } ?>
                  </td>
                </tr>
                <tr id="moreOptionsRow_<?php echo $row->id; ?>" style="">
                  <td colspan="6">
                    <div class="row-fluid moreInfoRow">
                      <div class="span12">
                        <div class="row-fluid">
                          <?php if (!empty($row->refer_url)) { ?>
                            <div class="span6">
                              <span><strong><?php echo JText::_('HTTP_REFER_DISPLAY'); ?></strong> <?php echo $row->refer_url; ?></span>
                            </div>
                          <?php }

                          if ($numChilds != 0) { ?>
                            <div class="span6">
                              <?php echo JText::sprintf( 'NUM_CHILDS_DISPLAY', $numChilds ); ?>
                            </div>
                          <?php } ?>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>

                <?php
                $k = 1 - $k;
              }
              ?>
              <tfoot>
                <tr class="totals">
                  <td class=""></td>
                  <td class=""></td>
                  <td class=""></td>
                  <td class=""></td>
                  <td class=""></td>
                  <td class=""></td>
                </tr>
              </tfoot>
            </table>
          </div>
          <br />
          <?php if ($this->params->get('newaccounts')){
            if (!AffiliateHelper::maxNumAccountsReached($user->id)) { ?>
              <div align="center" class="center">
                <a class="btn btn-large" href="<?php echo JRoute::_( 'index.php?option=com_affiliatetracker&view=account&layout=form&id=0'); ?>"><?php echo JText::_('REQUEST_NEW_ACCOUNT'); ?></a>
              </div>
            <?php } else { ?>
              <div align="center" class="center">
                <p><?php echo JText::_('MAX_ACCOUNTS_REACHED'); ?></p>
              </div>
            <?php } ?>
          <?php } ?>
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
          <input type="hidden" name="option" value="com_affiliatetracker" />
          <input type="hidden" name="view" value="accounts" />
          <input type="hidden" name="Itemid" value="<?php echo $this->params->get('itemid'); ?>" />
          <input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists['order']; ?>" />
          <input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

        </form>
        <?php
      }
      else{
        if($this->params->get('newaccounts')){ ?>
          <?php
          $intro = new stdClass();

          $intro->text = $this->params->get('textaccounts_new');

          $dispatcher = JDispatcher::getInstance();
          $plug_params = new JRegistry('');

          JPluginHelper::importPlugin('content');
          $results = $dispatcher->trigger('onContentPrepare', array ('com_affiliatetracker.accounts', &$intro, &$plug_params, 0));

          echo $intro->text;
          ?>
          <div align="center" class="center">
            <a class="btn btn-large" href="<?php echo JRoute::_( 'index.php?option=com_affiliatetracker&view=account&layout=form&id=0'); ?>"><?php echo JText::_('REQUEST_NEW_ACCOUNT'); ?></a>
          </div>
        <?php }
      }
      ?>
      <div align="center" class="footer"><?php echo AffiliateHelper::showATFooter(); ?></div>
