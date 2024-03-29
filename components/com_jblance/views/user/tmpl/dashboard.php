<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	21 March 2012
 * @file name	:	views/user/tmpl/dashboard.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Displays the user Dashboard (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 JHtml::_('jquery.framework');

 $doc = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/utility.js");

 $model 				= $this->getModel();
 $user					= JFactory::getUser();
 $config 				= JblanceHelper::getConfig();
 $showFeedsDashboard 	= $config->showFeedsDashboard;
 $enableEscrowPayment 	= $config->enableEscrowPayment;
 $enableWithdrawFund 	= $config->enableWithdrawFund;

 JText::script('COM_JBLANCE_CLOSE');

 $link_edit_profile = JRoute::_('index.php?option=com_jblance&view=user&layout=editprofile');
 $link_portfolio	= JRoute::_('index.php?option=com_jblance&view=user&layout=editportfolio');
 $link_messages		= JRoute::_('index.php?option=com_jblance&view=message&layout=inbox');
 $link_post_project = JRoute::_('index.php?option=com_jblance&view=project&layout=editproject');
 $link_list_project = JRoute::_('index.php?option=com_jblance&view=project&layout=listproject');
 $link_search_proj  = JRoute::_('index.php?option=com_jblance&view=project&layout=searchproject');
 $link_my_project 	= JRoute::_('index.php?option=com_jblance&view=project&layout=showmyproject');
 $link_my_bid 		= JRoute::_('index.php?option=com_jblance&view=project&layout=showmybid');
 $link_my_services 	= JRoute::_('index.php?option=com_jblance&view=service&layout=myservice');
 $link_service_bght	= JRoute::_('index.php?option=com_jblance&view=service&layout=servicebought');
 $link_deposit		= JRoute::_('index.php?option=com_jblance&view=membership&layout=depositfund');
 $link_withdraw		= JRoute::_('index.php?option=com_jblance&view=membership&layout=withdrawfund');
 $link_escrow		= JRoute::_('index.php?option=com_jblance&view=membership&layout=escrow');
 $link_transaction	= JRoute::_('index.php?option=com_jblance&view=membership&layout=transaction');
 $link_managepay	= JRoute::_('index.php?option=com_jblance&view=membership&layout=managepay');
 $link_subscr_hist	= JRoute::_('index.php?option=com_jblance&view=membership&layout=planhistory');
 $link_buy_subscr	= JRoute::_('index.php?option=com_jblance&view=membership&layout=planadd');

 JblanceHelper::setJoomBriToken();

if(!JBLANCE_FREE_MODE){
	if(!$user->guest){
		$planStatus = JblanceHelper::planStatus($user->id);

		if($planStatus == '1'){ ?>
		<div class="alert">
			<?php echo JText::sprintf('COM_JBLANCE_USER_SUBSCRIPTION_EXPIRED', $link_buy_subscr); ?>
		</div>
	<?php }
	elseif($planStatus == '2'){ ?>
	<div class="alert alert-info">
			<?php echo JText::sprintf('COM_JBLANCE_USER_DONT_HAVE_ACTIVE_PLAN', $link_subscr_hist); ?>
		</div>
	<?php }
	}
} ?>

<script type="text/javascript">
<!--
    jQuery(document).ready(function($){
    	jQuery(document).on('click','.more_feed',function(){
    		var id = $(this).attr('id');
    		var offset = $(this).data('offset');
    		var myRequest = jQuery.ajax({
    			url: "index.php?option=com_jblance&task=ajax.loadmorefeed&"+JoomBriToken,
    			method: "POST",
    			data: {"offset":offset},
    			beforeSend: function(){ $(".more_feed").text('<?php echo JText::_('COM_JBLANCE_LOADING'); ?>'); },
    			success: function(html) {
    				$('#more_feed_main_'+id).remove();
                    $('#feedlist').append(html);
               }
    		});
    	});
    });
//-->
</script>

<div class="jbl_h3title"><?php echo JText::_($this->userInfo->name).' '.JText::_('COM_JBLANCE_DASHBOARD'); ?></div>

<div style="clear:both;"></div>

<div class="row-fluid">
	<div class="span3 well well-small white" style="min-width: 260px; max-width: 340px; padding: 0px;">
		<ul class="nav nav-list">
			<li class="nav-header">
				<?php echo $this->userInfo->ug_name; ?>
			</li>
			<li>
				<a href="<?php echo $link_edit_profile; ?>"><i class="jbf-icon-edit"></i> <?php echo JText::_('COM_JBLANCE_EDIT_PROFILE'); ?> </a>
			</li>
			<li>
				<?php
				$avatars = JblanceHelper::getAvatarIntegration();
				$link_edit_picture = $avatars->getEditURL();
				?>
				<a href="<?php echo $link_edit_picture; ?>"><i class="jbf-icon-picture"></i> <?php echo JText::_('COM_JBLANCE_EDIT_PICTURE'); ?> </a>
			</li>
			<?php if($this->dbElements['allowAddPortfolio']) : ?>
			<li>
				<a href="<?php echo $link_portfolio; ?>"><i class="jbf-icon-book"></i> <?php echo JText::_('COM_JBLANCE_PORTFOLIO'); ?> </a>
			</li>
			<?php endif; ?>
			<li>
				<a href="<?php echo $link_messages; ?>"><i class="jbf-icon-envelope"></i> <?php echo JText::_('COM_JBLANCE_PRIVATE_MESSAGES'); ?> </a>
			</li>
			<li class="divider"></li>
			<li class="nav-header">
				<?php echo JText::_('COM_JBLANCE_PROJECTS'); ?>
			</li>
			<?php if($this->dbElements['allowPostProjects']) : ?>
			<li>
				<a href="<?php echo $link_post_project; ?>"><i class="jbf-icon-plus"></i> <?php echo JText::_('COM_JBLANCE_POST_NEW_PROJECT'); ?> </a>
			</li>
			<li>
				<a href="<?php echo $link_my_project; ?>"><i class="jbf-icon-list"></i> <?php echo JText::_('COM_JBLANCE_MY_PROJECTS'); ?> </a>
			</li>
			<li>
				<a href="<?php echo $link_service_bght; ?>"><i class="jbf-icon-basket"></i> <?php echo JText::_('COM_JBLANCE_SERVICES_BOUGHT'); ?> </a>
			</li>
			<?php endif; ?>
			<?php if($this->dbElements['allowBidProjects']) : ?>
			<li>
				<a href="<?php echo $link_list_project; ?>"><i class="jbf-icon-list"></i> <?php echo JText::_('COM_JBLANCE_LATEST_PROJECTS'); ?> </a>
			</li>
			<li>
				<a href="<?php echo $link_search_proj; ?>"><i class="jbf-icon-search"></i> <?php echo JText::_('COM_JBLANCE_SEARCH_PROJECTS'); ?> </a>
			</li>
			<li>
				<a href="<?php echo $link_my_bid; ?>"><i class="jbf-icon-tags"></i> <?php echo JText::_('COM_JBLANCE_MY_BIDS'); ?> </a>
			</li>
			<li>
				<a href="<?php echo $link_my_services; ?>"><i class="jbf-icon-list-2"></i> <?php echo JText::_('COM_JBLANCE_MY_SERVICES'); ?> </a>
			</li>
			<?php endif; ?>
			<?php
				if(!JBLANCE_FREE_MODE) :
			?>
			<li class="divider"></li>
			<li class="nav-header">
				<?php echo JText::_('COM_JBLANCE_BILLING_AND_FINANCE'); ?>
			</li>
			<li>
				<a href="<?php echo $link_deposit; ?>"><i class="jbf-icon-circle-arrow-right"></i> <?php echo JText::_('COM_JBLANCE_DEPOSIT_FUNDS'); ?> </a>
			</li>
			<!-- check if withdraw fund is enabled -->
			<?php if($enableWithdrawFund) : ?>
			<li>
				<a href="<?php echo $link_withdraw; ?>"><i class="jbf-icon-circle-arrow-left"></i> <?php echo JText::_('COM_JBLANCE_WITHDRAW_FUNDS'); ?> </a>
			</li>
			<?php endif; ?>
			<!-- check if escrow payment is enabled -->
			<?php if($enableEscrowPayment) : ?>
			<li>
				<a href="<?php echo $link_escrow; ?>"><i class="jbf-icon-refresh"></i> <?php echo JText::_('COM_JBLANCE_ESCROW_PAYMENT'); ?> </a>
			</li>
			<?php endif; ?>
			<li>
				<a href="<?php echo $link_transaction; ?>"><i class="jbf-icon-menu"></i> <?php echo JText::_('COM_JBLANCE_TRANSACTION_HISTORY'); ?> </a>
			</li>
			<li>
				<a href="<?php echo $link_managepay; ?>"><i class="jbf-icon-credit"></i> <?php echo JText::_('COM_JBLANCE_MANAGE_PAYMENTS'); ?> </a>
			</li>
			<li>
				<a href="<?php echo $link_subscr_hist; ?>"><i class="jbf-icon-asterisk"></i> <?php echo JText::_('COM_JBLANCE_MY_SUBSCRS'); ?> </a>
			</li>
			<?php endif; ?>
		</ul>
	</div>

	<div class="span8">
		<!-- pending tasks section -->
		<h3><?php echo JText::_('COM_JBLANCE_TASKS_PENDING'); ?></h3>
		<?php
		if(!empty($this->pendings)){
			foreach($this->pendings as $pending){
		?>
		<ul class="unstyled">
			<li><i class="jbf-icon-warning"></i> <?php echo $pending; ?></li>
		</ul>
		<?php
			}
		}
		else { ?>
			<div class="alert alert-info"><?php echo JText::_('COM_JBLANCE_NO_TASK_PENDING_YOUR_ACTION'); ?></div>
		<?php
		}
		?>

		<div class="lineseparator"></div>
		<!-- news feed section -->
		<?php if($showFeedsDashboard) : ?>
		<h3><?php echo JText::_('COM_JBLANCE_NEWS_FEED'); ?></h3>
		<div id="feedlist">
		<?php
		$n=count($this->feeds);
		if($n == 0){ ?>
			<div class="alert alert-info"><?php echo JText::_('COM_JBLANCE_NO_NEWSFEEDS_OR_POSTS'); ?></div>
		<?php
		}
		for($i=0, $n=count($this->feeds); $i < $n; $i++) {
			$feed = $this->feeds[$i]; ?>
			<div class="media jb-borderbtm-dot" id="jbl_feed_item_<?php echo $feed->id; ?>">
				<?php echo $feed->logo; ?>
				<div class="media-body">
					<?php echo $feed->title; ?>
		        	<p>
		        		<i class="jbf-icon-calendar"></i> <?php echo $feed->daysago; ?> |
		        		<span id="feed_hide_<?php echo $feed->id; ?>" class="help-inline">
							<?php if($feed->isMine) : ?>
							<a class="btn btn-mini btn-link" onclick="processFeed('<?php echo $user->id; ?>' , '<?php echo $feed->id; ?>', 'remove');" href="javascript:void(0);">
								<i class="jbf-icon-remove"></i> <?php echo JText::_('COM_JBLANCE_REMOVE'); ?>
							</a>
							<?php endif; ?>
							<a class="btn btn-mini btn-link" onclick="processFeed('<?php echo $user->id; ?>' , '<?php echo $feed->id; ?>', 'hide');" href="javascript:void(0);">
								<i class="jbf-icon-eye-close"></i> <?php echo JText::_('COM_JBLANCE_HIDE'); ?>
							</a>
						</span>
		        	</p>
				</div>
			</div>
		<?php
		}
		?>
    		<div class="more_feed_main" id="more_feed_main_<?php echo $feed->id; ?>">
            	<span id="<?php echo $feed->id; ?>" data-offset="0" class="more_feed btn btn-block"><?php echo JText::_('COM_JBLANCE_LOAD_MORE_FEEDS'); ?></span>
        	</div>
		</div>
		<?php endif; ?>
	</div>
</div>
