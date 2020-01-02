<?php
defined('_JEXEC') or die;
?>

<?php if ($this->countModules('banner-show')) : ?>
    <div class="bg-out">
        <div class="div-bg">
            <div class="div-left">
                <img src="/images/new_images/new_year.png">
            </div>
            <div class="div-right">
                <div class="block">
                    <div class="background-tj">
                        <jdoc:include type="modules" name="<?php $this->_p('banner-show') ?>" />
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>
