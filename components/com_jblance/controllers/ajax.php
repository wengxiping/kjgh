<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	20 December 2016
 * @file name	:	controllers/ajax.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
defined('_JEXEC') or die('Restricted access');

class JblanceControllerAjax extends JControllerAdmin {

	function __construct(){
		parent::__construct();
	}

	function dzUploadFile(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		JBMediaHelper::dzUploadFile();
	}

	function dzRemoveFile(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		JBMediaHelper::dzRemoveFile();
	}
	
	function loadMoreFeed(){
	    // Check for request forgeries
	    JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
	    
	    $app       = JFactory::getApplication();
	    $config    = JblanceHelper::getConfig();
	    
	    $offset = $app->input->get('offset', '', 'int');
	    $limit  = $config->feedLimitDashboard;
	    $offset = $offset + 1;
	    
	    $feeds = JblanceHelper::getFeeds($limit, '', $offset*$limit);
	    for($i=0, $n=count($feeds); $i < $n; $i++) {
	        $feed = $feeds[$i]; ?>
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
	    <?php if(count($feeds) > 0){ ?>
    		<div class="more_feed_main" id="more_feed_main_<?php echo $feed->id; ?>">
            	<span id="<?php echo $feed->id; ?>" data-offset="<?php echo $offset; ?>" class="more_feed btn btn-block"><?php echo JText::_('COM_JBLANCE_LOAD_MORE_FEEDS'); ?></span>
        	</div>
	    <?php } ?>
	    <?php 
	    $app->close();
	}
}
