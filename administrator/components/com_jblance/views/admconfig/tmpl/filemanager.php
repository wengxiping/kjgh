<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	22 June 2019
 * @file name	:	views/admconfig/tmpl/filemanager.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Manage files (jblance)
 */
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('bootstrap.tooltip');

$ulTarget = str_replace('/', '-', $this->tree['data']->relative);

echo JHtml::_(
    'bootstrap.renderModal',
    'imagePreview',
    array(
        'title'  => JText::_('COM_JBLANCE_PREVIEW'),
        'footer' => '<button type="button" class="btn" data-dismiss="modal">' . JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
    ),
    '<div id="image" style="text-align:center;"><img id="imagePreviewSrc" src="../media/jui/img/alpha.png" alt="preview" style="max-width:100%; max-height:300px;"/></div>'
    );

$doc = JFactory::getDocument();
$doc->addScriptDeclaration(
    "
		jQuery(document).ready(function($){
			$('.img-preview, .preview').each(function(index, value) {
				$(this).on('click', function(e) {
					window.parent.jQuery('#imagePreviewSrc').attr('src', $(this).attr('href'));
					window.parent.jQuery('#imagePreview').modal('show');
					return false;
				});
			});
		});
	"
    );
?>
<div class="row-fluid">
	<!-- Begin Sidebar -->
	<div id="j-sidebar-container" class="span2">
		<div id="treeview" class="sidebar">
			<div id="media-tree_tree" class="tree-holder">
                <ul class="nav nav-list" id="collapseFolder-<?php echo $ulTarget; ?>">
                <li class="nav-header"><?php echo JText::_('COM_JBLANCE_FOLDERS'); ?></li>
                <?php if (isset($this->tree['children'])) :
                	foreach ($this->tree['children'] as $folder) :
                	// Get a sanitised name for the target
                	$target = str_replace('/', '-', $folder['data']->relative); ?>
                	<li id="<?php echo $target; ?>" class="folder">
                		<a href="index.php?option=com_jblance&amp;view=admconfig&amp;layout=filemanager&amp;folder=<?php echo rawurlencode($folder['data']->relative); ?>" target="" class="folder-url" >
                			<span class="icon-folder"></span>
                			<?php echo $this->escape($folder['data']->name); ?>
                		</a>
                	</li>
                <?php endforeach;
                endif; ?>
                </ul>
			</div>
		</div>
		<?php include_once(JPATH_COMPONENT.'/views/configmenu.php'); ?>
	</div>
	<!-- End Sidebar -->
	<!-- Begin Content -->
	<div id="j-main-container" class="span10">
    <form action="index.php?option=com_jblance&amp;view=admconfig&amp;layoutl=filemanager&amp;folder=<?php echo rawurlencode($this->state->folder); ?>" method="post" id="adminForm" name="adminForm">
    	<div class="muted">
    		<p>
    			<span class="icon-folder"></span>
    			<?php
    			echo 'jblance', ($this->escape($this->state->folder) != '') ? '/' . $this->escape($this->state->folder) : '';
    			?>
    		</p>
    	</div>
    	<div class="manager">
    		<table class="table table-striped table-condensed">
    		<thead>
    			<tr>
					<th width="1%">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
    				<th width="1%"><?php echo JText::_('COM_JBLANCE_PREVIEW'); ?></th>
    				<th><?php echo JText::_('COM_JBLANCE_NAME'); ?></th>
    				<th width="15%"><?php echo JText::_('COM_JBLANCE_PIXEL_DIMENSIONS'); ?></th>
    				<th width="8%"><?php echo JText::_('COM_JBLANCE_SIZE'); ?></th>
					<th width="8%">
						<?php echo JText::_('JACTION_DELETE'); ?>
					</th>
    			</tr>
    		</thead>
    		<tbody>
    			<!-- SHOW FOLDER UP -->
                <?php if ($this->state->folder != '') : ?>
                <tr>
                	<td>&#160;</td>
                	<td class="imgTotal">
                		<a href="index.php?option=com_jblance&amp;view=admconfig&amp;layout=filemanager&amp;folder=<?php echo rawurlencode($this->state->parent); ?>" target=""><span class="icon-arrow-up"></span></a>
                	</td>
                	<td class="description">
                		<a href="index.php?option=com_jblance&amp;view=admconfig&amp;layout=filemanager&amp;folder=<?php echo rawurlencode($this->state->parent); ?>" target="">..</a>
                	</td>
                	<td>&#160;</td>
                	<td>&#160;</td>
                	<td>&#160;</td>
                </tr>
                <?php endif; ?>
                
                <!-- SHOW LIST OF FOLDERS -->
            	<?php foreach ($this->folders as $i => $folder) : ?>
            	<?php $link = 'index.php?option=com_jblance&amp;view=admconfig&amp;layout=filemanager&amp;folder=' . rawurlencode($folder->path_relative); ?>
            	<tr>
            		<td>
            		<?php if($folder->canDelete) : ?>
        				<?php echo JHtml::_('grid.id', $i, $this->escape($folder->name), false, 'rm', 'cb-folder'); ?>
            		<?php endif; ?>
            		</td>
            		<td class="imgTotal">
            			<a href="<?php echo $link; ?>" target="folderframe"><span class="icon-folder-2"></span></a>
            		</td>
            
            		<td class="description">
            			<a href="<?php echo $link; ?>" target=""><?php echo $this->escape($folder->name); ?></a>
            		</td>
            		<td>&#160;</td>
            		<td>&#160;</td>
            		<td>&#160;</td>
            	</tr>
            	<?php endforeach; ?>
        		
        		<!-- SHOW LIST OF IMAGE FILES -->
            	<?php foreach ($this->images as $i => $image) : ?>
            	<tr>
            		<td>
            		<?php if($image->canDelete) : ?>
            				<?php echo JHtml::_('grid.id', $i, $this->escape($image->name), false, 'rm', 'cb-image'); ?>
            		<?php endif; ?>
            		</td>
            		<td>
            			<a class="img-preview" href="<?php echo JB_BASE_URL . '/' . str_replace('%2F', '/', rawurlencode($image->path_relative)); ?>" title="<?php echo $this->escape($image->name); ?>">
            				<?php echo JHtml::_('image', JB_BASE_URL . '/' . $this->escape($image->path_relative), JText::sprintf('JTITLE', $this->escape($image->title), JHtml::_('number.bytes', $image->size)), array('width' => $image->width_16, 'height' => $image->height_16)); ?>
            			</a>
            		</td>
            
            		<td class="description">
            			<a href="<?php echo  JB_BASE_URL . '/' . str_replace('%2F', '/', rawurlencode($image->path_relative)); ?>" title="<?php echo $this->escape($image->name); ?>" class="preview">
            				<?php echo $this->escape($image->title); ?>
            			</a>
            		</td>
            
            		<td class="dimensions">
            			<?php echo JText::sprintf('COM_JBLANCE_IMAGE_DIMENSIONS', $image->width, $image->height); ?>
            		</td>
            
            		<td class="filesize">
            			<?php echo JHtml::_('number.bytes', $image->size); ?>
            		</td>
            		<td>
            		<?php if($image->canDelete) : ?>
        				<a class="delete-item" target="_top" href="index.php?option=com_jblance&amp;task=admconfig.deletefile&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo rawurlencode($this->state->folder); ?>&amp;rm[]=<?php echo $this->escape($image->name); ?>" rel="<?php echo $this->escape($image->name); ?>">
        					<span class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE'); ?>"></span>
        				</a>
            		<?php endif; ?>
            		</td>
            	</tr>
            	<?php endforeach; ?>
            	
            	<!-- SHOW LIST OF DOCS FILES -->
            	<?php foreach ($this->documents as $i => $doc) : ?>
            	<tr>
            		<td>
            		<?php if ($doc->canDelete) : ?>
            			<?php echo JHtml::_('grid.id', $i, $this->escape($doc->name), false, 'rm', 'cb-document'); ?>
            		<?php endif; ?>
           	 		</td>
            		<td>
            			<a title="<?php echo $this->escape($doc->name); ?>">
            				<?php echo JHtml::_('image', $doc->icon_16, $this->escape($doc->title), null, true, true) ? JHtml::_('image', $doc->icon_16, $this->escape($doc->title), array('width' => 16, 'height' => 16), true) : JHtml::_('image', 'media/con_info.png', $this->escape($doc->title), array('width' => 16, 'height' => 16), true); ?>
            			</a>
            		</td>
            		<td class="description"  title="<?php echo $this->escape($doc->name); ?>">
            			<?php echo $this->escape($doc->title); ?>
            		</td>
            		<td>&#160;</td>
            		<td class="filesize">
            			<?php echo JHtml::_('number.bytes', $doc->size); ?>
            		</td>
            		<td>
            		<?php if ($doc->canDelete) : ?>
        				<a class="delete-item" target="_top" href="index.php?option=com_jblance&amp;task=admconfig.deletefile&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo rawurlencode($this->state->folder); ?>&amp;rm[]=<?php echo $this->escape($doc->name); ?>" rel="<?php echo $this->escape($doc->name); ?>">
        					<span class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE'); ?>"></span>
        				</a>
            		<?php endif; ?>
           			</td>
            	</tr>
            	<?php endforeach; ?>
    		</tbody>
    		</table>
    	</div>
    	<input type="hidden" name="option" value="com_jblance" />
    	<input type="hidden" name="view" value="admconfig" />
    	<input type="hidden" name="layout" value="showbudget" />
    	<input type="hidden" name="task" value="" />
    	<input type="hidden" name="boxchecked" value="0" />
    	<?php echo JHtml::_('form.token'); ?>
    </form>
	</div>
