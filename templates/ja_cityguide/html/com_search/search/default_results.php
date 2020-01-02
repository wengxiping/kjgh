<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<dl class="search-results<?php echo $this->pageclass_sfx; ?>">
<?php foreach ($this->results as $result) : ?>
	<dt class="result-title">
		<?php echo $this->pagination->limitstart + $result->count . '. '; ?>
		<?php if ($result->href) : ?>
			<a href="<?php echo JRoute::_($result->href); ?>"<?php if ($result->browsernav == 1) : ?> target="_blank"<?php endif; ?>>
				<?php // $result->title should not be escaped in this case, as it may ?>
				<?php // contain span HTML tags wrapping the searched terms, if present ?>
				<?php // in the title. ?>
				<?php echo $result->title; ?>
			</a>
		<?php else : ?>
			<?php // see above comment: do not escape $result->title ?>
			<?php echo $result->title; ?>
		<?php endif; ?>
	</dt>
	
	<dd class="result-text">
		<?php echo $result->text; ?>
	</dd>

	<dd class="result-info">
		<?php if ($result->section) : ?>
		<span class="result-category">
			<span>
				<?php echo Jtext::_('TPL_IN').' '.$this->escape($result->section); ?>
			</span>
		</span>
		<?php endif; ?>

		<?php if ($this->params->get('show_date')) : ?>
			<span class="result-created<?php echo $this->pageclass_sfx; ?>">
				<?php echo JText::sprintf('JGLOBAL_CREATED_DATE_ON', $result->created); ?>
			</span>
		<?php endif; ?>
	</dd>
<?php endforeach; ?>
</dl>
<div class="pagination">
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
