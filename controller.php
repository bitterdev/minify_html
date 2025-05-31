<?php

namespace Concrete\Package\MinifyHtml;

use Bitter\MinifyHtml\Provider\ServiceProvider;
use Concrete\Core\Entity\Package as PackageEntity;
use Concrete\Core\Package\Package;

final class Controller extends Package
{
    protected string $pkgHandle = 'minify_html';
    protected $appVersionRequired = '9.0.0';
    protected string $pkgVersion = '2.1.0';
    protected $pkgAutoloaderRegistries = [
        'src/Bitter/MinifyHtml' => '\Bitter\MinifyHtml',
    ];

    public function getPackageName(): string
    {
        return t('Minify HTML');
    }

    public function getPackageDescription(): string
    {
        return t('Minify HTML output to decrease page load times.');
    }

    public function on_start()
    {
        /** @var ServiceProvider $serviceProvider */
        /** @noinspection PhpUnhandledExceptionInspection */
        $serviceProvider = $this->app->make(ServiceProvider::class);
        $serviceProvider->register();
    }

    public function install(): PackageEntity
    {
        $pkg = parent::install();
        $this->installContentFile("data.xml");
        return $pkg;
    }

    public function upgrade()
    {
        parent::upgrade();
        $this->installContentFile("data.xml");
    }
}
