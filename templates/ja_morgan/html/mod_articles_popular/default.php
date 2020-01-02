<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_popular
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ul class="mostread<?php echo $moduleclass_sfx; ?> mod-list">
<?php foreach ($list as $item) : ?>
	<?php $images = json_decode($item->images); ?>

	<li>
		<?php if($images->image_intro): ?>
		<a class="image-intro" href="<?php echo $item->link; ?>" itemprop="url">
			<img itemprop="image" src="<?php echo $images->image_intro ;?>" alt="<?php echo $item->title; ?>" />
		</a>
		<?php endif ;?>

		<h4 class="title">
			<a href="<?php echo $item->link; ?>" itemprop="url">
				<span itemprop="name">
					<?php echo $item->title; ?>
				</span>
			</a>
		</h4>
	</li>

	<?php
	?>
<?php endforeach; ?>
</ul>
