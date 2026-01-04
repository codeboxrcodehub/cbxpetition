<?php

namespace CbxPetitionScoped\Intervention\Image;

use CbxPetitionScoped\League\Container\ServiceProvider\AbstractServiceProvider;
class ImageServiceProviderLeague extends AbstractServiceProvider
{
    /**
     * @var array $config
     */
    protected $config;
    /**
     * @var array $provides
     */
    protected $provides = ['CbxPetitionScoped\\Intervention\\Image\\ImageManager'];
    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }
    /**
     * Register the server provider.
     *
     * @return void
     */
    public function register()
    {
        $this->getContainer()->share('CbxPetitionScoped\\Intervention\\Image\\ImageManager', function () {
            return new ImageManager($this->config);
        });
    }
}
