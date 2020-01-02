<h1 class="row-fluid"><?php
	$link_name = $this->fields->getFieldById(1);
	$this->plugin( 'ahreflisting', $this->link, $link_name->getOutput(1), '', array("edit"=>false,"delete"=>false,"link"=>false) );

	if( isAuthorisedToEditListing($this->link) || isAuthorisedToDeleteListing($this->link) )
	{
		?>
		<div class="btn-group pull-right"> <a class="btn dropdown-toggle" data-toggle="dropdown" href="#" role="button"> <span class="icon-cog"></span> <span class="caret"></span> </a>
			<ul class="dropdown-menu">
				<?php if( isAuthorisedToEditListing($this->link) ) { ?>
					<li class="edit-icon">
						<a href="<?php echo JRoute::_('index.php?option=com_mtree&task=editlisting&link_id='.$this->link->link_id); ?>">
							<span class="icon-edit"></span>
							<?php echo JText::_( 'COM_MTREE_EDIT' ); ?>
						</a>
					</li>
					<?php
				}

				if( $this->link->link_published && $this->link->link_approved && isAuthorisedToDeleteListing($this->link) ) { ?>
					<li class="delete-icon">
						<a href="<?php echo JRoute::_('index.php?option=com_mtree&task=deletelisting&link_id='.$this->link->link_id); ?>">
							<span class="icon-remove"></span>
							<?php echo JText::_( 'COM_MTREE_DELETE' ); ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
		<?php
	}
	?></h1>
<?php

if ( !empty($this->mambotAfterDisplayTitle) )
{
	echo trim( implode( "\n", $this->mambotAfterDisplayTitle ) );
}
