<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>

<?php if (isset($cluster) && $cluster) { ?>
	<?php echo $this->html('cover.' . $cluster->getType(), $cluster, 'apps'); ?>
<?php } ?>

<div class="es-container">
	<div class="es-content">
		<form action="<?php echo JRoute::_('index.php?option=com_easysocial&view=polls&layout=create');?>" method="post" class="es-forms">

			<div class="es-forms__group">
				<div class="es-forms__title">
					<?php echo $this->html('form.title', 'COM_EASYSOCIAL_START_NEW_POLL', 'h1'); ?>
				</div>

				<div class="es-forms__content">
					<div class="es-polls-form">
						<?php
							$cid = isset($cluster) && $cluster ? $cluster->id : 0;
						?>
						<?php echo $polls->form(SOCIAL_TYPE_STREAM, 0, '', $cid, array('type' => 'form')); ?>
					</div>
				</div>
			</div>

			<div class="es-forms__actions">
				<div class="o-form-actions">
					<a href="<?php echo ESR::polls();?>" class="btn btn-es-default t-lg-pull-left"><?php echo JText::_('COM_ES_CANCEL');?></a>
					<button class="btn btn-es-primary t-lg-pull-right">
						<?php echo JText::_('COM_EASYSOCIAL_CREATE_POLL');?>
					</button>
				</div>
			</div>

			<?php if (isset($cluster) && $cluster) { ?>
			<input type="hidden" name="clusterType" value="<?php echo $cluster->getType(); ?>" />
			<input type="hidden" name="clusterId" value="<?php echo $cluster->id; ?>" />
			<?php } ?>

			<?php echo $this->html('form.action', 'polls', 'create'); ?>
		</form>
	</div>
</div>
