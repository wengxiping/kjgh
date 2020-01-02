<?php
require 'setup.php';

?>
<div class="mt-page-ld-style-<?php echo $this->config->getTemParam('listingDetailsStyle',1); ?>">
<div class="row-fluid mt-page-ld link-id-<?php echo $this->link_id; ?> cat-id-<?php echo $this->link->cat_id; ?> tlcat-id-<?php echo $this->link->tlcat_id; ?>">
<?php

include $this->loadTemplate('sub_listingDetailsStyle'.$this->config->getTemParam('listingDetailsStyle',1).'.tpl.php');

?>
</div>
</div>
