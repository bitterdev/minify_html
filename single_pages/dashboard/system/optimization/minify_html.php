<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Form\Service\Form;
use Concrete\Core\Page\Page;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Core\View\View;
use HtmlObject\Element;

/** @var bool $status */
/** @var bool $enableForRegisteredUsers */
/** @var Page[] $pagesWithMinificationDisabled */

$app = Application::getFacadeApplication();
/** @var Token $token */
/** @noinspection PhpUnhandledExceptionInspection */
$token = $app->make(Token::class);
/** @var Form $form */
/** @noinspection PhpUnhandledExceptionInspection */
$form = $app->make(Form::class);

?>

<div class="ccm-dashboard-header-buttons">
    <?php /** @noinspection PhpUnhandledExceptionInspection */
    View::element("dashboard/help", [], "minify_html"); ?>
</div>

<?php /** @noinspection PhpUnhandledExceptionInspection */
View::element("dashboard/did_you_know", [], "minify_html"); ?>

<form method="post" action="#">
    <?php $token->output('update_settings'); ?>

    <div class="form-group">
        <div class="form-check">
            <?php echo $form->checkbox('status', 1, $status, ["class" => "form-check-input"]); ?>
            <?php echo $form->label('status', t('Enable HTML minification'), ["class" => "form-check-label"]); ?>
        </div>

        <div class="form-check">
            <?php echo $form->checkbox('enableForRegisteredUsers', 1, $enableForRegisteredUsers, ["class" => "form-check-input"]); ?>
            <?php echo $form->label('enableForRegisteredUsers', t('Enable for Registered Users'), ["class" => "form-check-label"]); ?>
        </div>
    </div>

    <?php if (count($pagesWithMinificationDisabled) > 0) { ?>
        <hr/>

        <strong>
            <?php echo t('Pages with minification disabled') ?>
        </strong>

        <ul>
            <?php
            foreach ($pagesWithMinificationDisabled as $page) {
                echo (new Element("li"))
                    ->appendChild(
                        (new Element("a"))
                            ->setAttribute("href", $page->getCollectionLink())
                            ->setAttribute("target", "_blank")
                            ->setValue($page->getCollectionName())
                    )->render();
            }
            ?>
        </ul>
    <?php } ?>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button class="float-end btn btn-primary" type="submit">
                <?php echo t('Save') ?>
            </button>
        </div>
    </div>
</form>
