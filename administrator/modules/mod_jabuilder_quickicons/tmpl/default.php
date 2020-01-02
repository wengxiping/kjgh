<?php
defined('_JEXEC') or die();

$moduleclass_sfx = $params->get('moduleclass_sfx','');
?>

<div class="ja_builder_quickicons_wrap <?php if (!empty($moduleclass_sfx)) echo $moduleclass_sfx; ?>">
	<div class="jabuilder_create_new">
		<a class="btn btn-success" href="index.php?option=com_jabuilder&view=page&layout=edit">
			<span class="icon-new icon-white"></span>
			<?php echo JTEXT::_('JA_BUILDER_QUICKICONS_NEW'); ?>
		</a>
	</div>
	<div class="jabuilder_page">
		<a class="btn btn-primary" href="index.php?option=com_jabuilder&view=pages">
			<?php echo JText::_('JA_BUILDER_QUICKICONS_PAGES'); ?>
		</a>
	</div>
</div>