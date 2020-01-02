<?php
	$helper = require_once (__DIR__ . '/helper.php');	
	JHtml::_('jquery.framework');
	$this->addScript($this->baseurl . '/templates/' . $this->template . '/js/template.js');
	$this->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/template.css', 'text/css');
	//$this->addStyleSheet('https://fonts.googleapis.com/icon?family=Material+Icons', 'text/css');
	$helper->init($this);

	$active = JFactory::getApplication()->getMenu()->getActive();
	$bodyclass = 'jub-page' . ($active && $active->params->get('pageclass_sfx') ? ' jub-page-' . $active->params->get('pageclass_sfx') : '');
?>
<!DOCTYPE html>
<html>
	<head>
		<?php $helper->_param('custom-code-head-top') ?>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<jdoc:include type="head" />
		<?php $helper->_param('custom-code-head-bot') ?>
	</head>
<body class="<?php echo $bodyclass ?>">
<?php $helper->_param('custom-code-body-top') ?>

<?php if ($helper->isLayoutEditMode()): ?>
	<jdoc:include type="component" />	
<?php else: ?>
	<div class="wrapper">
		<div class="header">
			<?php $helper->_layout('header') ?>
		</div>

		<?php if ($helper->pageContent('top')): ?>
		<div class="content-top">
			<?php $helper->_pageContent('top') ?>
		</div>
		<?php endif ?>

		<?php 
		$cols = $helper->mainbody('cols') ;
		if (!$helper->isJUBPage() && is_array($cols)) : ?>
		<div class="jub-block mainbody">
		<div class="<?php $helper->_mainbody('container') ?>">
			<div class="row">
			<?php foreach ($cols as $col) : 
				if (isset($col['trash__'])) continue;
				$type = isset($col['type']) ? $col['type'] : null;
				$name = isset($col['name']) ? $col['name'] : null;
				if (!$type) continue;
				?>
				<?php if ($type == 'content' || $this->countModules($name)): ?>
				<div class="col <?php echo $col['width'] ? 'col-md-'.$col['width'] : 'col-flex' ?>">
					<?php if ($type == 'content'): ?>
					<jdoc:include type="message" />
					<jdoc:include type="component" />
					<?php else: ?>
					<jdoc:include type="modules" name="<?php echo $name ?>" style="JAXhtml" />
					<?php endif ?>
				</div>
				<?php endif ?>
			<?php endforeach ?>
			</div>
		</div>
		</div>
		<?php else: ?>
		<div class="main-content">	
			<jdoc:include type="message" />
			<jdoc:include type="component" />
		</div>
		<?php endif ?>

		<?php if ($helper->pageContent('bottom')): ?>
		<div class="content-bottom">
			<?php $helper->_pageContent('bottom') ?>
		</div>
		<?php endif ?>

		<div class="footer">
			<?php $helper->_layout('footer') ?>
		</div>

	</div>
<?php endif ?>

<?php $helper->_param('custom-code-body-bot') ?>
</body>
</html>
