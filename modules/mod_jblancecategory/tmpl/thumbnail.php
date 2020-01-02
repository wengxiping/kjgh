<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	21 December 2016
 * @file name	:	modules/mod_jblancecategory/tmpl/default.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 // no direct access
 defined('_JEXEC') or die('Restricted access');
 
 $set_Itemid	= intval($params->get('set_itemid', 0));
 $Itemid 		= ($set_Itemid > 0) ? '&Itemid='.$set_Itemid : '';
 ?>
 
 <div class="row-fluid">
	<ul class="thumbnails">
	<?php 
	foreach($rows as $row){
		$attachments = JBMediaHelper::processAttachment($row->category_image, 'category');
		$link_proj_categ = JRoute::_('index.php?option=com_jblance&view=project&layout=searchproject&id_categ='.$row->id.'&type=parentcategory'.$Itemid);
	?>
		<li class="span3 thumbfix jbf-hovereffect">
			<a href="<?php echo $link_proj_categ; ?>" class="">
				<img style="width: 100%; height: 150px;" src="<?php echo $attachments[0]['location']; ?>" />
				<div class="overlay">
					<h2><?php echo $row->category; ?></h2>
				</div>
			</a>
		</li>
	<?php 
	}
	?>
	</ul>
</div>
