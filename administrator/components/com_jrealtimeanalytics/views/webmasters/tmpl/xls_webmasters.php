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
$rowSpacer = '<p></p>';
$reportDelimiter = '_________________';
?>
<b><font size="4" color="#0028D3"><?php echo $this->statsDomain; ?></font></b>
<?php echo $rowSpacer;?>
<?php echo $rowSpacer;?>

<!-- GOOGLE SEARCH CONSOLE STATS PAGES -->
<b><font size="4" color="#0028D3"><?php echo JText::_ ('COM_JREALTIME_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_PAGES' ) . $reportDelimiter; ?></font></b>
<table>
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
						<?php  echo (int)$dataGroupedByPage->getPosition(); ?>
					</td>
				</tr>
		<?php }
		}
	?>
</table>
<?php echo $rowSpacer;?>

<!-- GOOGLE SEARCH CONSOLE STATS KEYWORDS -->
<b><font size="4" color="#0028D3"><?php echo JText::_ ('COM_JREALTIME_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_QUERY' ) . $reportDelimiter; ?></font></b>
<table class="adminlist table table-striped table-hover table-webmasters">
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
	
		<?php // Render errors count
			if(!empty($this->googleData['results_query'])){
				foreach ($this->googleData['results_query'] as $dataGroupedByQuery) { ?>
					<tr>
						<td>
							<span class="label label-info label-large">
								<?php $dataGroupedQuery = $dataGroupedByQuery->getKeys();?>
								<?php echo $dataGroupedQuery[0];?>
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
							<?php echo (int)$dataGroupedByQuery->getPosition();?>
						</td>
					</tr>
			<?php }
			}
		?>
</table>