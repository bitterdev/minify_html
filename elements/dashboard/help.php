<?php

defined('C5_EXECUTE') or die('Access Denied.');
?>

<a  class="btn btn-secondary" href="javascript:void(0);" id="ccm-report-bug">
        <?php echo t('Get Help') ?>
    </a>
<script>
    (function ($) {
        $("#ccm-report-bug").click(function () {
            jQuery.fn.dialog.open({
                href: "<?php echo (string)\Concrete\Core\Support\Facade\Url::to("/ccm/system/dialogs/minify_html/create_ticket"); ?>",
                modal: true,
                width: 500,
                title: "<?php echo h(t("Support"));?>",
                height: '80%'
            });
        });
    })(jQuery);
</script>