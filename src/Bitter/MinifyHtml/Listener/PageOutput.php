<?php

namespace Bitter\MinifyHtml\Listener;

use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker;
use Concrete\Core\User\User;
use Symfony\Component\EventDispatcher\GenericEvent;
use DOMDocument;
use DOMXPath;

final class PageOutput implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    public function handle(GenericEvent $event): void
    {
        $contents = $event->getArgument('contents');

        if ($this->shouldMinify()) {
            $contents = $this->minify($contents);
            $event->setArgument('contents', $contents);
        }
    }

    protected function shouldMinify(): bool
    {
        /** @var Page $page */
        $page = Page::getCurrentPage();

        if (!$page or $page->isError()) {
            return false;
        }

        /**
         * Minify HTML is disabled for dashboard pages.
         * The minification could break things, e.g. it conflicts with JavaScript templates.
         */
        if ($page->isAdminArea()) {
            return false;
        }

        /** @var Repository $config */
        /** @noinspection PhpUnhandledExceptionInspection */
        $config = $this->app->make(Repository::class);

        // Check if Minify HTML is disabled globally.
        if ($config->get('minify_html.settings.status') === false) {
            return false;
        }

        $u = new User();

        if ($u->isRegistered()) {
            // Should we minify for logged in users?
            if ($config->get('minify_html.settings.enable_for_registered_users', true) === false) {
                return false;
            }

            $p = new Checker($page);
            /** @noinspection PhpUndefinedMethodInspection */
            if ($p->canEditPageContents()) {
                return false;
            }
        }

        // Is minification disabled for the current page?
        if ($page->getAttribute('disable_html_minification')) {
            return false;
        }

        return true;
    }


    /**
     * This function has been refactored. The original base code is derived from the
     * PHPWee PHP Minifier Package. Below is the full license text:
     *
     * PHPWee PHP Minifier Package - http://searchturbine.com/php/phpwee
     * Copyright (c) 2015, SearchTurbine - Enterprise Search for Everyone
     * http://searchturbine.com/
     *
     * All rights reserved.
     *
     * Redistribution and use in source and binary forms, with or without
     * modification, are permitted provided that the following conditions are met:
     *
     * Redistributions of source code must retain the above copyright notice, this
     * list of conditions and the following disclaimer.
     *
     * Redistributions in binary form must reproduce the above copyright notice,
     * this list of conditions and the following disclaimer in the documentation
     * and/or other materials provided with the distribution.
     *
     * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
     * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
     * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
     * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
     * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
     * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
     * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
     * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
     * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
     * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
     */
    private function minify(string $contents): string
    {
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        @$doc->loadHTML($contents);
        $xpath = new DOMXPath($doc);

        foreach ($xpath->query('//comment()') as $comment) {
            $val = $comment->nodeValue;
            if (!str_starts_with($val, '[')) {
                $comment->parentNode->removeChild($comment);
            }
        }

        $doc->normalizeDocument();

        $textNodes = $xpath->query('//text()');
        $skip = ["style", "pre", "code", "script", "textarea"];

        foreach ($textNodes as $t) {
            $xp = $t->getNodePath();
            $doSkip = false;

            foreach ($skip as $pattern) {
                if (str_contains($xp, "/$pattern")) {
                    $doSkip = true;
                    break;
                }
            }

            if ($doSkip) {
                continue;
            }

            $t->nodeValue = preg_replace("/\s{2,}/", " ", $t->nodeValue);
        }

        $doc->normalizeDocument();

        $divNodes = $xpath->query('//div|//p|//nav|//footer|//article|//script|//hr|//br');

        foreach ($divNodes as $d) {
            $candidates = [];

            if (count($d->childNodes)) {
                $candidates[] = $d->firstChild;
                $candidates[] = $d->lastChild;
                $candidates[] = $d->previousSibling;
                $candidates[] = $d->nextSibling;
            }

            foreach ($candidates as $c) {
                if ($c == null) {
                    continue;
                }

                if ($c->nodeType == 3) {
                    $c->nodeValue = preg_replace('/\s+/', ' ', $c->nodeValue);
                }
            }
        }

        $doc->normalizeDocument();

        return ($doc->saveHTML());
    }
}
