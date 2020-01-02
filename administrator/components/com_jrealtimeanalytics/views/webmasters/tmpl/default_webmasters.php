<?php 
/** 
 * @package JREALTIME::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage overview
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<span class='label label-info label-large'><?php echo $this->statsDomain; ?></span> 
	<?php echo $this->hasOwnCredentials ? null : "<span data-content='" . JText::_('COM_JREALTIME_GOOGLE_APP_NOTSET_DESC') . "' class='label label-warning hasPopover google pull-right'>" . JText::_('COM_JREALTIME_GOOGLE_APP_NOTSET') . "</span>"; ?>
	
	<table class="headerlist">
		<tr>
			<td>
				<div class="input-prepend active">
					<span class="add-on"><span class="icon-calendar"></span> <?php echo JText::_('COM_JREALTIME_FILTER_BY_DATE_FROM' ); ?>:</span>
					<input type="text" name="fromperiod" id="fromPeriod" data-role="calendar" autocomplete="off" value="<?php echo $this->dates['start'];?>" class="text_area"/>
				</div>
				
				<div class="input-prepend active">
					<span class="add-on"><span class="icon-calendar"></span> <?php echo JText::_('COM_JREALTIME_FILTER_BY_DATE_TO' ); ?>:</span>
					<input type="text" name="toperiod" id="toPeriod" data-role="calendar" autocomplete="off" value="<?php echo $this->dates['to'];?>" class="text_area"/>
				</div>
				<button class="btn btn-primary btn-mini" onclick="this.form.submit();"><?php echo JText::_('COM_JREALTIME_GO' ); ?></button>
			</td>
		</tr>
	</table>
	
	<!-- GOOGLE SEARCH CONSOLE STATS PAGES -->
	<div class="accordion" id="jrealtime_googleconsole_query_accordion">
		<div class="row tablestats no-margin">
			<div class="accordion-group span12">
				<div class="accordion-heading">
					<div class="accordion-toggle accordion_lightblue" data-toggle="collapse" data-parent="#jrealtime_googleconsole_query_accordion" href="#jrealtime_google_pages">
						<h3><span class="icon-copy"></span> <?php echo JText::_ ('COM_JREALTIME_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_PAGES' ); ?></h3>
					</div>
				</div>
				<div id="jrealtime_google_pages" class="accordion-body accordion-inner collapse" >
					<table id="jrealtime_table_webmasters_pages_stats" class="adminlist table table-striped table-hover table-webmasters">
						<thead>
							<tr>
								<th>
									<span><?php echo JText::_('COM_JREALTIME_GOOGLE_WEBMASTERS_PAGES' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JREALTIME_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JREALTIME_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JREALTIME_GOOGLE_WEBMASTERS_CTR' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JREALTIME_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
								</th>
							</tr>
						</thead>
						
						<tbody>
							<?php // Render errors count
								if(!empty($this->googleData['results_page'])){
									foreach ($this->googleData['results_page'] as $dataGroupedByPage) { ?>
										<tr>
											<td>
												<span class="label-italic">
													<?php $dataGroupedKeys = $dataGroupedByPage->getKeys();?>
													<a href="<?php echo $dataGroupedKeys[0];?>" target="_blank">
														<?php echo $dataGroupedKeys[0];?> <span class="icon-out"></span>
													</a>
												</span>
											</td>
											<td>
												<?php echo $dataGroupedByPage->getClicks();?>
											</td>
											<td>
												<?php echo $dataGroupedByPage->getImpressions();?>
											</td>
											<td>
												<?php echo round(($dataGroupedByPage->getCtr() * 100), 2) . '%';?>
											</td>
											<td>
												<?php 
													$serpPosition = (int)$dataGroupedByPage->getPosition();
													$classLabel = $serpPosition > 30 ? 'label-important' : 'label-success';
												?>
												<span class="label <?php echo $classLabel;?>">
													<?php echo $serpPosition;?>
												</span>
											</td>
										</tr>
								<?php }
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	
	<!-- GOOGLE SEARCH CONSOLE STATS KEYWORDS -->
	<div class="accordion" id="jrealtime_googleconsole_accordion">
		<div class="row tablestats no-margin">
			<div class="accordion-group span12">
				<div class="accordion-heading">
					<div class="accordion-toggle accordion_lightblue" data-toggle="collapse" data-parent="#jrealtime_googleconsole_accordion" href="#jrealtime_google_query">
						<h3><span class="icon-list-view"></span> <?php echo JText::_ ('COM_JREALTIME_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_QUERY' ); ?></h3>
					</div>
				</div>
				<div id="jrealtime_google_query" class="accordion-body accordion-inner collapse" >
					<table id="jrealtime_table_webmasters_keywords_stats" class="adminlist table table-striped table-hover table-webmasters">
						<thead>
							<tr>
								<th>
									<span><?php echo JText::_('COM_JREALTIME_GOOGLE_WEBMASTERS_KEYS' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JREALTIME_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JREALTIME_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JREALTIME_GOOGLE_WEBMASTERS_CTR' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JREALTIME_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
								</th>
							</tr>
						</thead>
						
						<tbody>
							<?php // Render errors count
								if(!empty($this->googleData['results_query'])){
									foreach ($this->googleData['results_query'] as $dataGroupedByQuery) { ?>
										<tr>
											<td>
												<span class="label label-info label-large">
													<?php $dataGroupedQuery = $dataGroupedByQuery->getKeys();?>
													<?php echo htmlspecialchars($dataGroupedQuery[0], ENT_QUOTES, 'UTF-8');?>
												</span>
											</td>
											<td>
												<?php echo $dataGroupedByQuery->getClicks();?>
											</td>
											<td>
												<?php echo $dataGroupedByQuery->getImpressions();?>
											</td>
											<td>
												<?php echo round(($dataGroupedByQuery->getCtr() * 100), 2) . '%';?>
											</td>
											<td>
												<?php 
													$serpPosition = (int)$dataGroupedByQuery->getPosition();
													$classLabel = $serpPosition > 30 ? 'label-important' : 'label-success';
												?>
												<span class="label <?php echo $classLabel;?>">
													<?php echo $serpPosition;?>
												</span>
											</td>
										</tr>
								<?php }
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="webmasters.display" />
</form>