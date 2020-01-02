<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');
$bgSearch = '';

if($this->params->get('bg-search')) {
	$bgSearch = 'style="background-image: url('.$this->params->get('bg-search').')"';
}

?>
<div class="search ja-search<?php echo $this->pageclass_sfx; ?>">
	<?php if($bgSearch) :?>
		<div class="box-bg">
			<div class="bg-inline" <?php echo $bgSearch ;?> ></div>
		</div>
	<?php endif ;?>
	
	<div class="container">
		<?php if ($this->params->get('title-search')) : ?>
			<h1 class="search-title">
				<?php echo $this->params->get('title-search') ;?>
			</h1>
		<?php endif; ?>

		<?php if ($this->params->get('desc-search')) : ?>
			<span class="search-description">
				<?php echo $this->params->get('desc-search') ;?>
			</span>
		<?php endif; ?>

		<?php echo $this->loadTemplate('form'); ?>
		<?php if ($this->error == null && count($this->results) > 0) : ?>
			<?php echo $this->loadTemplate('results'); ?>
		<?php else : ?>
			<?php echo $this->loadTemplate('error'); ?>
		<?php endif; ?>
	</div>
</div>
