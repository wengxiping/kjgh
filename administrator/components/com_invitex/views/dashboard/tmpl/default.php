<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restrited access');
$line_chart_data=$this->linechart_data;

$document =JFactory::getDocument();
$document->addStyleSheet(JUri::root(true).'/media/com_invitex/bootstrap3/css/bootstrap.min.css');
$document->addStyleSheet(JUri::root(true).'/media/com_invitex/font-awesome/css/font-awesome.min.css');
$document->addStyleSheet(JUri::root(true).'/media/com_invitex/css/morris.css');
$document->addStyleSheet(JUri::root(true).'/media/com_invitex/css/tjdashboard-sb-admin.css');
$logo_path='<img src="'.JURI::base().'/com_invitex/assets/images/techjoomla.png" alt="TechJoomla" style="vertical-align:text-top;"/>';
$statsforpiesent=$this->statsforpiemethod[0];
$statsforpieacc=$this->statsforpiemethod[1];
// Load jQuery.
if (JVERSION >= '3.0')
{
	JHtml::_('jquery.framework');
}

// Morris chart JS
$document->addScript(JUri::root(true).'/media/com_invitex/js/raphael.min.js');
$document->addScript(JUri::root(true).'/media/com_invitex/js/morris.min.js');
$statsforpiesent=$this->statsforpiemethod[0];
$statsforpieacc=$this->statsforpiemethod[1];
?>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>

<form name="adminForm" id="adminForm" class="form-validate" method="post">
	<div class="<?php echo INVITEX_WRAPPER_CLASS;?> invitex-admin-dashboard">
		<?php
		if(JVERSION >= '3.0'):
			 if (!empty( $this->sidebar)) : ?>
				<div id="j-sidebar-container" class="span2">
					<?php echo $this->sidebar; ?>
				</div>
				<div id="j-main-container" class="span10">
			<?php else : ?>
				<div id="j-main-container">
			<?php endif;
		endif;
		?>
<!-- TJ Bootstrap3 -->
		<div class="tjBs3">
			<!-- TJ Dashboard -->
			<div class="tjDB">
				<div id="wrapper">
					<div id="page-wrapper">

						<!-- Start - stat boxes -->
						<div class="row">
							<div class="col-lg-4 col-md-6">
								<div class="panel panel-green">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-3 ">
												<i class="fa fa-4x fa-envelope-o"></i>
											</div>
											<div class="col-xs-9 text-right">
												<div class="huge"><?php echo $this->all_time_invites_count;?> </div>
												<div><?php echo JText::_('COM_INVITEX_ALL_TIME_INVITE');?></div>
											</div>
										</div>
									</div>
									<a href="<?php echo JRoute::_('index.php?option=com_invitex&view=invites&invite_sent=1', false);?>">
										<div class="panel-footer">
											<span class="pull-left">
												<?php echo JText::_('COM_INVITEX_VIEW_DETAILS');?>
											</span>
											<span class="pull-right">
												<i class="fa fa-arrow-circle-right"></i>
											</span>
											<div class="clearfix"></div>
										</div>
									</a>
								</div>
							</div>
							<div class="col-lg-4 col-md-6">
								<div class="panel panel-primary">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-3">
												<i class="fa fa-4x fa-envelope-o"></i>
											</div>
											<div class="col-xs-9 text-right">
												<div class="huge"><?php echo $this->accpeted_invites_count;?> </div>
												<div><?php echo JText::_('COM_INVITEX_INVITE_SENT_SUCCESS');?></div>
											</div>
										</div>
									</div>
									<a href="<?php echo JRoute::_('index.php?option=com_invitex&view=invites&accpepted_invites=1', false);?>">
										<div class="panel-footer">
											<span class="pull-left">
												<?php echo JText::_('COM_INVITEX_VIEW_DETAILS');?>
											</span>
											<span class="pull-right">
												<i class="fa fa-arrow-circle-right"></i>
											</span>
											<div class="clearfix"></div>
										</div>
									</a>
								</div>
							</div>
							<div class="col-lg-4 col-md-6">
								<div class="panel panel-yellow">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-3">
												<i class="fa  fa-4x fa-users"></i>
											</div>
											<div class="col-xs-9 text-right">
												<div class="huge"><?php echo $this->inviters_count; ?></div>
												<div><?php echo JText::_('COM_INVITEX_INVITERS_COUNT');?></div>
											</div>
										</div>
									</div>
									<a href="<?php echo JRoute::_('index.php?option=com_invitex&view=topinviters', false);?>">
										<div class="panel-footer">
											<span class="pull-left">
												<?php echo JText::_('COM_INVITEX_VIEW_DETAILS');?>
											</span>
											<span class="pull-right">
												<i class="fa fa-arrow-circle-right"></i>
											</span>
											<div class="clearfix"></div>
										</div>
									</a>
								</div>
							</div>
						</div>
						<!-- End - stat boxes -->

						<div class="row">
							<div class="col-lg-8">
								<div class="panel panel-default">
									<div class="panel-heading">
										<i class="fa fa-pie-chart fa-fw"></i>
										<?php echo JText::_('COM_INVITEX_PERIODIC_GRAPHS');?>
									</div>
									<div class="panel-body">
										<!-- CALENDER ND REFRESH BTN  -->
										<div class="clearfix row">
											<div class="pull-left col-sm-6">
												<label for="from"><?php echo JText::_('FROM_DATE');?></label>
												<?php echo JHtml::_('calendar', $this->from_date, 'fromdate', 'from', '%Y-%m-%d', array('class' => 'inputbox input-xs', 'style' => 'min-height:35px!important; max-width:90px!important;', 'readonly'=>'true')); ?>
											</div>
											<div class="pull-right col-sm-6">
												<label for="to"><?php echo JText::_('TO_DATE');?></label>
												<?php echo JHtml::_('calendar', $this->to_date, 'todate', 'to', '%Y-%m-%d', array('class' =>'inputbox input-xs', 'style' => 'min-height:35px!important; max-width:90px!important;', 'readonly'=> 'true')); ?>
												<input id="btnRefresh" class="pull-right btn btn-micro btn-primary" type="button" value="<?php echo JText::_('GO'); ?>" style="font-weight: bold;" onclick="checkdatess()"/>
											</div>
											<div class="clearifx"></div>
										</div>
										<!--END::CALENDER ND REFRESH BTN  -->

								<!-- Start - Line Chart Accepted/Non Accepted Status -->
								<div class="panel panel-default">
									<div class="panel-heading">
										<i class="fa-line-chart fa"></i>
										<?php echo JText::_('COM_INVITEX_INVITE_STATS');?>
									</div>
									<div class="panel-body">
										<div id="line_chart_div"></div>
										<div class="center">
											<?php echo JText::_('COM_INVITEX_INVITE_STATS');?>
										</div>
									</div>
								</div>
								<!-- End - Line Chart Accepted/Non Accepted Status -->

								<div class="row inv_pie_chart">
									<div class="col-lg-6">
										<!-- Start - not shipped orders -->
										<div class="panel panel-default">
											<div class="panel-heading">
												<i class="fa fa-pie-chart fa-fw"></i>
												<?php echo JText::_('COM_INVITEX_PERIODIC_INVITES_SENT');?>
											</div>

											<div class="panel-body">
												<?php
												if(!empty($this->statsforpiemethodSent))
												{
													?>
													<div id="graph-sent-invitations"></div>
													<?php
												}
												else
												{
													?>
													<div class="alert alert-info">
														<?php echo JText::_("COM_INVITEX_PERIODIC_INVITES_NO_DATA");?>
													</div>
													<?php
												}
												?>
											</div>
										</div>
										<!-- End - not shipped orders -->


									</div>
									<!-- /.col-lg-6 -->

									<div class="col-lg-6">
										<!-- Start - not shipped orders -->
										<div class="panel panel-default">
											<div class="panel-heading">
												<i class="fa fa-pie-chart fa-fw"></i>
												<?php echo JText::_('COM_INVITEX_PERIODIC_INVITES_ACCEPTED');?>
											</div>
											<div class="panel-body">
												<?php
												if(!empty($this->statsforpiemethodAccepted))
												{
													?>
														<div id="graph-accepted-invitations"></div>



													<?php
												}
												else
												{
													?>
													<div class="alert alert-info">
															<?php echo JText::_("COM_INVITEX_PERIODIC_INVITES_NO_DATA");?>
													</div>
													<?php
												}
												?>
											</div>
										</div>
										<!-- End - not shipped orders -->
									</div>
									<!-- /.col-lg-6 -->
								</div>
								<!-- /.row -->
							</div>
							<!-- /.panel-body -->
							</div>
							</div>
							<!-- /.col-lg-8 -->


							<div class="col-lg-4">
								<div class="panel">
									<div class="panel-heading">
								<div class="row">
									<div class="newVersionNotice" id='newVersionNotice'>
										<span>
											<?php
											$versionHTML = '<span class="label label-info">' .
															JText::_('COM_INVITEX_HAVE_INSTALLED_VER') . ': ' . $this->version .
														'</span>';

												if ($this->latestVersion)
												{
													if ($this->latestVersion->version > $this->version)
														{
															$versionHTML = '<div class="alert alert-error">' .
																				'<i class="icon-puzzle install"></i>' .
																				JText::_('COM_INVITEX_HAVE_INSTALLED_VER') . ': ' . $this->version .
																				'<br/>' .
																				'<i class="icon icon-info"></i>' .
																				JText::_("COM_INVITEX_NEW_VER_AVAIL") . ': ' .
																				'<span class="invitex_latest_version_number">' .
																					$this->latestVersion->version .
																				'</span>
																				<br/>' .
																				'<i class="icon icon-warning"></i>' .
																				'<span class="small">' .
																					JText::_("COM_INVITEX_LIVE_UPDATE_BACKUP_WARNING") . '
																				</span>' . '
																			</div>
																			<div class="left">
																				<a href="index.php?option=com_installer&view=update" class="btn btn-small btn-primary">' .
																					JText::sprintf('COM_INVITEX_LIVE_UPDATE_TEXT', $this->latestVersion->version) . '
																				</a>
																				<a href="' . $this->latestVersion->infourl . '/?utm_source=clientinstallation&utm_medium=dashboard&utm_term=invitex&utm_content=updatedetailslink&utm_campaign=invitex_ci' . '" target="_blank" class="btn btn-small btn-info">' .
																					JText::_('COM_INVITEX_LIVE_UPDATE_KNOW_MORE') . '
																				</a>
																			</div>';
														}
													}
													?>
										</span>
									</div>
									<div>
									<?php if (!$this->downloadid): ?>
										<div class="">
											<div class="clearfix pull-right">
												<div class="alert alert-warning center">
													<?php echo JText::sprintf('COM_INVITEX_LIVE_UPDATE_DOWNLOAD_ID_MSG', '<a href="https://techjoomla.com/my-account/add-on-download-ids" target="_blank">' . JText::_('COM_INVITEX_LIVE_UPDATE_DOWNLOAD_ID_MSG2') . '</a>'); ?>
												</div>
											</div>
										</div>
									<?php endif; ?>

										<div class="">
											<div class="clearfix pull-right">
												<?php echo $versionHTML; ?>
											</div>
										</div>
									</div>
								</div>
								</div>
								</div>

								<!--INFO,HELP + ETC START -->
								<div class="panel panel-default">
									<div class="panel-heading">
										<i class="fa fa-share-alt"></i>
										<?php echo JText::_('COM_INVITEX'); ?>
									</div>
									<div class="panel-body">
										<div class="">
											<blockquote class="blockquote-reverse">
												<p><?php echo JText::_('ABOUT1');?></p>
											</blockquote>
										</div>

										<div class="row">
											<div class="col-lg-12 col-md-12 col-sm-12">
												<p class="pull-right"><span class="label label-info"><?php echo JText::_('COM_INVITEX_LINKS'); ?></span></p>
											</div>
										</div>
										<div class="list-group">
											<a href="http://techjoomla.com/table/extension-documentation/documentation-for-invitex/?utm_source=clientinstallation&utm_medium=dashboard&utm_term=invitex&utm_content=textlink&utm_campaign=invitex_ci" class="list-group-item" target="_blank">
												<i class="fa fa-file fa-fw i-document"></i> <?php echo JText::_('COM_INVITEX_DOCS');?>
											</a>

											<a href="http://techjoomla.com/documentation-for-invitex/installation.html/?utm_source=clientinstallation&utm_medium=dashboard&utm_term=invitex&utm_content=textlink&utm_campaign=invitex_ci" class="list-group-item" target="_blank">
												<i class="fa fa-file fa-fw i-document"></i> <?php echo JText::_('COM_INVITEX_INSTALL_UPGRADDE');?>
											</a>

											<a href="http://techjoomla.com/documentation-for-invitex/setup-a-configuration28.html/?utm_source=clientinstallation&utm_medium=dashboard&utm_term=invitex&utm_content=textlink&utm_campaign=invitex_ci" class="list-group-item" target="_blank">
												<i class="fa fa-file fa-fw i-document"></i> <?php echo JText::_('COM_INVITEX_BASIC_SET_UP');?>
											</a>

											<a href="http://techjoomla.com/documentation-for-invitex/faqs43.html/?utm_source=clientinstallation&utm_medium=dashboard&utm_term=invitex&utm_content=textlink&utm_campaign=invitex_ci" class="list-group-item" target="_blank">
												<i class="fa fa-question fa-fw i-question"></i> <?php echo JText::_('COM_INVITEX_FAQS_GENERAL');?>
											</a>
											<a href="http://techjoomla.com/documentation-for-invitex/faqs43.html/?utm_source=clientinstallation&utm_medium=dashboard&utm_term=invitex&utm_content=textlink&utm_campaign=invitex_ci" class="list-group-item" target="_blank">
												<i class="fa fa-question fa-fw i-question"></i> <?php echo JText::_('COM_INVITEX_FAQS_API');?>
											</a>

											<a href="http://techjoomla.com/support/support-tickets/?utm_source=clientinstallation&utm_medium=dashboard&utm_term=invitex&utm_content=textlink&utm_campaign=invitex_ci" class="list-group-item" target="_blank">
												<i class="fa fa-support fa-fw i-support"></i> <?php echo JText::_('COM_INVITEX_TECHJOOMLA_SUPPORT_CENTER');?>
											</a>

											<a href="http://extensions.joomla.org/extensions/content-sharing/bookmark-a-recommend/12178" class="list-group-item" target="_blank">
												<i class="fa fa-bullhorn fa-fw i-horn"></i> <?php echo JText::_('COM_INVITEX_LEAVE_JED_FEEDBACK');?>
											</a>
										</div>

										<div class="row">
											<div class="col-lg-12 col-md-12 col-sm-12">
												<p class="pull-right">
													<span class="label label-info"><?php echo JText::_('COM_INVITEX_STAY_TUNNED'); ?></span>
												</p>
											</div>
										</div>

										<div class="list-group">
											<div class="list-group-item">
												<div class="pull-left">
													<i class="fa fa-facebook fa-fw i-facebook"></i>
													<?php echo JText::_('COM_INVITEX_FACEBOOK'); ?>
												</div>
												<div class="pull-right">
													<!-- facebook button code -->
													<div id="fb-root"></div>
													<script>(function(d, s, id) {
													  var js, fjs = d.getElementsByTagName(s)[0];
													  if (d.getElementById(id)) return;
													  js = d.createElement(s); js.id = id;
													  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
													  fjs.parentNode.insertBefore(js, fjs);
													}(document, 'script', 'facebook-jssdk'));</script>
													<div class="fb-like" data-href="https://www.facebook.com/techjoomla" data-send="true" data-layout="button_count" data-width="250" data-show-faces="false" data-font="verdana"></div>
												</div>
												<div class="clearfix">&nbsp;</div>
											</div>

											<div class="list-group-item">
												<div class="pull-left">
													<i class="fa fa-twitter fa-fw i-twitter"></i>
													<?php echo JText::_('COM_INVITEX_TWITTER'); ?>
												</div>
												<div class="pull-right">
													<!-- twitter button code -->
													<a href="https://twitter.com/techjoomla" class="twitter-follow-button" data-show-count="false">Follow @techjoomla</a>
													<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
												</div>
												<div class="clearfix">&nbsp;</div>
											</div>

											<div class="list-group-item">
												<div class="pull-left">
													<i class="fa fa-google fa-fw i-google"></i>
													<?php echo JText::_('COM_INVITEX_GPLUS'); ?>
												</div>
												<div class="pull-right">
													<!-- Place this tag where you want the +1 button to render. -->
													<div class="g-plusone" data-annotation="inline" data-width="120" data-href="https://plus.google.com/102908017252609853905"></div>
													<!-- Place this tag after the last +1 button tag. -->
													<script type="text/javascript">
													(function() {
													var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
													po.src = 'https://apis.google.com/js/plusone.js';
													var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
													})();
													</script>
												</div>
												<div class="clearfix">&nbsp;</div>
											</div>
										</div>

										<div class="row">
											<div class="col-lg-12 col-md-12 col-sm-12 center">
												<?php
												$logo = '<img src="' . JUri::base() . 'components/com_invitex/assets/images/techjoomla.png" alt="TechJoomla" class=""/>';
												?>
												<span class="center thumbnail">
													<a href='http://techjoomla.com/' target='_blank'>
														<?php echo $logo;?>
													</a>
												</span>
												<p><?php echo JText::_('COM_INVITEX_COPYRIGHT'); ?></p>
											</div>
										</div>
									<!-- /.panel-body -->
								</div>
								<!-- /.panel -->
							</div>
							<!-- /.col-lg-4 -->
						</div>
						<!-- /.row -->

					</div>
					<!-- /#page-wrapper -->
				</div>
			</div>
			<!-- /.tjDB TJ Dashboard -->
		</div>
		<!-- /.tjBs3TJ TJ Bootstrap3 -->
	</div>
</form>
<script type="text/javascript">

		function checkdatess()
		{
			if (document.getElementById('from').value > document.getElementById('to').value)
			{
				alert("<?php echo JText::_("COM_INVITEX_DATE_ERROR_MSG");?>");
				return false;
			}

			document.adminForm.submit();
		}

   Morris.Line({

		   element: 'line_chart_div',
		   data :<?php echo json_encode($line_chart_data);?>,
		   xkey: 'date',
		   ykeys: ['sent','accepted'],
		   labels: ['Sent','Accepted'],
		   xLabels: 'day',
		   lineColors: ['#428BCA','#3EA99F'],
		   hideHover: 'auto',
		   resize: true,
   });


	<?php if (!empty($this->statsforpiemethodAccepted)): ?>
		drawPeriodicOrdersChartAccepted();
	<?php endif; ?>

	<?php if (!empty($this->statsforpiemethodSent)): ?>
		drawPeriodicOrdersChartSent();
	<?php endif; ?>




	function drawPeriodicOrdersChartSent()
	{
		techjoomla.jQuery('#graph-sent-invitations').html('');

		Morris.Donut({
			element: 'graph-sent-invitations',
            data :<?php echo json_encode($this->statsforpiemethodSent);?>,
			colors: ["#f0ad4e", "#5cb85c", "#428bca", "#d9534f"],
			resize:true
			});


	}

	function drawPeriodicOrdersChartAccepted()
	{
			techjoomla.jQuery('#graph-accepted-invitations').html('');
			Morris.Donut({
			element: 'graph-accepted-invitations',
            data :<?php echo json_encode($this->statsforpiemethodAccepted);?>,
			colors: ["#f0ad4e", "#5cb85c", "#428bca", "#d9534f"],
			resize:true
			});

	}
</script>
