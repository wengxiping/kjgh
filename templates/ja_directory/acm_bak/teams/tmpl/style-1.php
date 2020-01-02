<?php
/**
 * ------------------------------------------------------------------------
 * JA Directory Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;
?>

<?php
  $count = $helper->count('member-name');
  $col = $helper->get('number_col');
  $fullWidth = $helper->get('full-width');
?>
<div class="acm-teams">
  <?php if(!$fullWidth): ?><div class="container"><?php endif; ?>

	<div class="style-4 team-items">
		<?php
      for ($i=0; $i < $count; $i++) :
        if ($i%$col==0) echo '<div class="row">'; 
    ?>
		<div class="item col-sm-6 col-md-<?php echo (12/$col); ?>">
			<div class="item-inner grid">
			
				<figure class="team-detail">
					<img src="<?php echo $helper->get('member-image', $i); ?>" alt="<?php echo $helper->get('member-name', $i); ?>" />
					
					<figcaption>
						<h2>
							<?php echo $helper->get('member-name', $i); ?>
							<p class="member-title"><?php echo $helper->get('member-position', $i); ?></p>
						</h2>
						
						<p class="social-links">
						<?php
						for($j=1; $j <= 5; $j++) :
						  if(trim($helper->get('member-link-icon'.$j, $i)) != ""):
						?>
							<a href="<?php echo $helper->get('member-link'.$j, $i); ?>" title=""><i class="<?php echo $helper->get('member-link-icon'.$j, $i); ?>"></i></a>
						<?php
						endif;
						endfor;
						?>
						</p>
					</figcaption>			
				</figure>			
			</div>
		</div>
    
    <?php if ( ($i%$col==($col-1)) || $i==($count-1) )  echo '</div>'; ?>
		<?php endfor; ?>
	</div>
  
	<?php if(!$fullWidth) : ?></div><?php endif; ?>
</div>