<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-cluster-header">
	<div class="es-cluster-header__hd">
		<div class="o-flag">
			<div class="o-flag__image o-flag--top">
				<a href="<?php echo $permalink;?>" class="o-avatar es-cluster-header__avatar">
					<img src="<?php echo $avatar;?>" alt="<?php echo $this->html('string.escape', $title);?>">
				</a>
			</div>
			<div class="o-flag__body">
				<div class="o-grid">
					<div class="o-grid__cell">
						<a href="<?php echo $permalink;?>" class="es-cluster-header__title-link"><?php echo $this->html('string.escape', $title);?></a>
						<div class="es-cluster-header__meta">
							<?php echo $this->html('string.truncate', $description, 200, '', false, false);?>
						</div>

					</div>
				</div>

			</div>

		</div>
	</div>

	<div class="es-cluster-header__ft">
		<div class="o-grid">
			<?php if ($childs) { ?>
			<div class="o-grid__cell">
				<ul class="g-list-inline g-list-inline--delimited">
					<?php if (count($childs) > 5) { ?>
						<?php for ($i = 0; $i < 3; $i++) { ?>
						<li data-breadcrumb="/">
							<a href="<?php echo $childs[$i]->getFilterPermalink(); ?>"><?php echo $childs[$i]->getTitle(); ?></a>
						</li>
						<?php } ?>

						<li data-breadcrumb="/" class="g-list-inline__last-item">
							<div class="o-btn-group">
								<a href="javascript:void(0);" class=" dropdown-toggle_" data-bs-toggle="dropdown">
									<?php echo JText::_('COM_EASYSOCIAL_READMORE'); ?> <i class="i-chevron i-chevron--down"></i>
								</a>

								<ul class="dropdown-menu dropdown-menu-right">
									<?php for ($i=3; $i < count($childs); $i++) { ?>
									<li>
										<a href="<?php echo $childs[$i]->getFilterPermalink(); ?>"><?php echo $childs[$i]->getTitle(); ?></a>
									</li>
									<?php } ?>
								</ul>
							</div>
						</li>
					<?php } else { ?>
						<?php foreach ($childs as $child) { ?>
							<li data-breadcrumb="/">
								<a href="<?php echo $child->getFilterPermalink(); ?>"><?php echo $child->getTitle(); ?></a>
							</li>
						<?php } ?>
					<?php } ?>
				</ul>
			</div>
			<?php } ?>
			<div class="o-grid__cell o-grid__cell--auto-size">
				<a class="btn btn-es-default-o btn-sm" href="<?php echo $permalink;?>"><?php echo JText::_($moreText);?></a>
			</div>
		</div>
	</div>
</div>
