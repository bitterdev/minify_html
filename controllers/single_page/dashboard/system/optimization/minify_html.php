<?php

namespace Concrete\Package\MinifyHtml\Controller\SinglePage\Dashboard\System\Optimization;

use Concrete\Core\Attribute\Exception\InvalidAttributeException;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Page\PageList;
use Concrete\Core\Validation\CSRF\Token;

class MinifyHtml extends DashboardPageController
{
    public function view()
    {
        /** @var Repository $config */
        /** @noinspection PhpUnhandledExceptionInspection */
        $config = $this->app->make(Repository::class);
        /** @var Token $token */
        /** @noinspection PhpUnhandledExceptionInspection */
        $token = $this->app->make(Token::class);

        if ($this->request->getMethod() === "POST") {
            if (!$token->validate('update_settings')) {
                $this->error->add($token->getErrorMessage());
                return;
            }

            $config->save('minify_html.settings.status', (bool)$this->post('status'));
            $config->save('minify_html.settings.enable_for_registered_users', (bool)$this->post('enableForRegisteredUsers'));

            $this->flash('success', t('Settings saved'));
        }

        $pagesWithMinificationDisabled = [];

        $pageList = new PageList();
        try {
            $pageList->filterByAttribute('disable_html_minification', 1);
            $pagesWithMinificationDisabled = $pageList->getResults();
        } catch (InvalidAttributeException) {
        }


        $this->set('pagesWithMinificationDisabled', $pagesWithMinificationDisabled);
        $this->set('status', (bool)$config->get('minify_html.settings.status', true));
        $this->set('enableForRegisteredUsers', (bool)$config->get('minify_html.settings.enable_for_registered_users', false));
    }
}