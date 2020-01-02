<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

$gd = function_exists('gd_info');
$curl = is_callable('curl_init');

############################################
## MySQL info
############################################
$db = JFactory::getDBO();
$mysqlVersion = $db->getVersion();

############################################
## PHP info
############################################
$phpVersion = phpversion();
$uploadLimit = ini_get('upload_max_filesize');
$memoryLimit = ini_get('memory_limit');
$postSize = ini_get('post_max_size');
$magicQuotes = get_magic_quotes_gpc() && JVERSION > 3;

$postSize = 4;
$hasErrors = false;

if (stripos($memoryLimit, 'G') !== false) {
	list($memoryLimit) = explode('G', $memoryLimit);
	$memoryLimit = $memoryLimit * 1024;
}

if (!$gd || !$curl || $magicQuotes) {
	$hasErrors 	= true;
}

##########################################
## Paths
##########################################
$files = array();

$files['admin']	= new stdClass();
$files['admin']->path = JPATH_ROOT . '/administrator/components';

$files['site']	= new stdClass();
$files['site']->path = JPATH_ROOT . '/components';

$files['tmp'] = new stdClass();
$files['tmp']->path = JPATH_ROOT . '/tmp';

$files['media']	= new stdClass();
$files['media']->path 	= JPATH_ROOT . '/media';

$files['user']	= new stdClass();
$files['user']->path 	= JPATH_ROOT . '/plugins/user';

$files['system']	= new stdClass();
$files['system']->path 	= JPATH_ROOT . '/plugins/system';

$files['user']	= new stdClass();
$files['user']->path 	= JPATH_ROOT . '/plugins/user';

$files['auth']	= new stdClass();
$files['auth']->path 	= JPATH_ROOT . '/plugins/authentication';


##########################################
## Determine states
##########################################
$fileHasErrors = false;

foreach ($files as $file) {
	// The only proper way to test this is to not use is_writable
	$contents = "<body></body>";
	$state = JFile::write($file->path . '/tmp.html', $contents);

	// Initialize this to false by default
	$file->writable = false;

	if ($state) {
		JFile::delete($file->path . '/tmp.html');

		$file->writable = true;
	}

	if (!$file->writable) {
		$fileHasErrors = true;
		$hasErrors = true;
	}
}
?>
<script type="text/javascript">
jQuery(document).ready(function($){

	$('[data-installation-submit]' ).bind('click', function(){
		$('[data-installation-form]').submit();
	});

	<?php if ($hasErrors) { ?>
	$('[data-installation-submit]').hide();
	$('[data-installation-refresh]').removeClass('hide');

	// now we rebind the click.
	$('[data-installation-refresh]').on('click', function() {
		window.location.reload();
	});
	<?php } ?>
});
</script>
<form name="installation" method="post" data-installation-form>

<p>
Thank you for your recent purchase of <a href="https://stackideas.com/payplans">PayPlans</a>! Before proceeding with the Installation, please ensure that the system requirements are met. These are the requirements to ensure that PayPlan runs smoothly on your site.
</p>

<?php if (!$hasErrors) { ?>
<hr />
<p class="alert alert-success">Great! The minimum requirements are met.</p>
<?php } ?>

<div class="alert alert-error <?php echo $hasErrors ? '' : 'hide';?>" data-requirements-error>
	<p>Some of the requirements below are not met. Please ensure that all of the requirements below are met.</p>
</div>

<div class="requirements-table" data-system-requirements>
	<table class="table table-striped mt-20 stats">
		<thead>
			<tr>
				<td width="40%">Settings</td>
				<td class="text-center" width="30%">Recommended</td>
				<td class="text-center" width="30%">Current</td>
			</tr>
		</thead>

		<tbody>
			<tr class="<?php echo version_compare($phpVersion, '5.3.10') == -1 ? 'error' : '';?>">
				<td>
					<div class="clearfix">
						<span class="label label-info">PHP</span> PHP Version

						<?php if (version_compare($phpVersion, '5.3.10') == -1) { ?>
						<a href="https://stackideas.com/docs/payplans/welcome/system-requirements" class="pull-right btn btn-es-danger btn-mini"><?php echo JText::_('More Info');?></a>
						<?php } ?>
					</div>
				</td>
				<td class="text-center text-success">
					5.3.10 +
				</td>
				<td class="text-center text-<?php echo version_compare($phpVersion , '5.3.10' ) == -1 ? 'error' : 'success';?>">
					<?php echo $phpVersion;?>
				</td>
			</tr>

			<tr class="<?php echo !$gd ? 'error' : '';?>">
				<td>
					<div class="clearfix">
						<span class="label label-info">PHP</span> GD Library

						<?php if (!$gd) { ?>
						<a href="https://stackideas.com/docs/payplans/welcome/system-requirements" target="_blank" class="pull-right btn btn-es-danger btn-mini"><?php echo JText::_('More Info');?></a>
						<?php } ?>
					</div>
				</td>
				<td class="text-center text-success">
					<i class="icon-checkmark"></i>
				</td>
				
				<?php if ($gd) { ?>
				<td class="text-center text-success">
					<i class="icon-checkmark"></i>
				</td>
				<?php } else { ?>
				<td class="text-center text-error">
					<i class="icon-cancel-2"></i>
				</td>
				<?php } ?>
			</tr>

			<tr class="<?php echo !$curl ? 'error' : '';?>">
				<td>
					<div class="clearfix">
						<span class="label label-info">PHP</span> CURL Library

						<?php if (!$curl) { ?>
						<a href="https://stackideas.com/docs/payplans/welcome/system-requirements" target="_blank" class="pull-right btn btn-es-danger btn-mini"><?php echo JText::_('More Info');?></a>
						<?php } ?>
					</div>
				</td>
				<td class="text-center text-success">
					<i class="icon-checkmark"></i>
				</td>
				
				<?php if ($curl) { ?>
				<td class="text-center text-success">
					<i class="icon-checkmark"></i>
				</td>
				<?php } else { ?>
				<td class="text-center text-error">
					<i class="icon-cancel-2"></i>
				</td>
				<?php } ?>
			</tr>
			<tr class="<?php echo $magicQuotes ? 'error' : '';?>">
				<td>
					<div class="clearfix">
						<span class="label label-info">PHP</span> Magic Quotes GPC

						<?php if ($magicQuotes) { ?>
						<a href="https://stackideas.com/docs/payplans/welcome/system-requirements" target="_blank" class="pull-right btn btn-es-danger btn-mini"><?php echo JText::_('More Info');?></a>
						<?php } ?>
					</div>
				</td>
				<td class="text-center text-success">
					<?php echo JText::_('Disabled');?>
				</td>
				<td class="text-center text-<?php echo $magicQuotes ? 'error' : 'success';?>">
					<?php if( !$magicQuotes ){ ?>
						<?php echo JText::_('Disabled');?>
					<?php } else { ?>
						<?php echo JText::_('Enabled');?>
					<?php } ?>
				</td>
			</tr>
			<tr class="<?php echo $memoryLimit < 64 ? 'error' : '';?>">
				<td>
					<span class="label label-info">PHP</span> memory_limit
				</td>
				<td class="text-center text-success">
					64M
				</td>
				<td class="text-center text-<?php echo $memoryLimit < 64 ? 'error' : 'success';?>">
					<?php echo $memoryLimit; ?>
				</td>
			</tr>
			<tr>
				<td>
					<span class="label label-success">MySQL</span> MySQL Version
				</td>
				<td class="text-center text-success">
					5.0.4
				</td>
				<td class="text-center text-<?php echo !$mysqlVersion || version_compare($mysqlVersion, '5.0.4') == -1 ? 'error' : 'success'; ?>">
					<?php echo !$mysqlVersion ? 'N/A' : $mysqlVersion;?>
				</td>
			</tr>
		</tbody>
	</table>
	
	<?php if ($fileHasErrors) { ?>
	<table class="table table-striped mt-20 stats">
		<thead>
			<tr>
				<td width="75%">Directory</td>
				<td class="text-center" width="25%">State</td>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($files as $file) { ?>
			<tr class="<?php echo !$file->writable ? 'text-error' : '';?>">
				<td>
					<?php echo $file->path;?>
				</td>

				<?php if ($file->writable) { ?>
				<td class="text-center text-success">
					<i class="icon-checkmark"></i>
				</td>
				<?php } else { ?>
				<td class="text-center text-error">
					<i class="icon-cancel-2"></i>&nbsp; Unwritable
				</td>
				<?php } ?>
			</tr>
			<?php } ?>

		</tbody>
	</table>
	<?php } ?>
</div>

<input type="hidden" name="option" value="com_payplans" />
<input type="hidden" name="active" value="<?php echo $active; ?>" />
</form>
