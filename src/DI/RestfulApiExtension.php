<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\DI;

use Nette\Configurator;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;

/**
 * Nette DI extension
 */
class RestfulApiExtension extends CompilerExtension
{
    /**
     * Default DI settings
     * @var array
     */
    protected $defaults = [
        'security' => [
            'privateKey' => null,
            'requestTimeKey' => 'timestamp',
            'requestTimeout' => 300,
        ]
    ];

    /**
     * Load DI configuration
     */
    public function loadConfiguration() : void
    {
        $container = $this->getContainerBuilder();
        $config = $this->getConfig($this->defaults);

        // Additional module
        $this->loadRestful($container, $config);
        $this->loadSecuritySection($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function loadRestful(ContainerBuilder $container/*, array $config*/) : void
    {

        // Input & validation
        $container->addDefinition($this->prefix('inputFactory'))
            ->setFactory(\Drahak\Restful\Http\InputFactory::class);

        $container->addDefinition($this->prefix('httpRequestFactory'))
            ->setFactory(\Drahak\Restful\Http\ApiRequestFactory::class);

        $container->getDefinition('httpRequest')
            ->setFactory($this->prefix('@httpRequestFactory') . '::createHttpRequest');

        /*$container->addDefinition($this->prefix('requestFilter'))
            ->setFactory(\Drahak\Restful\Utils\RequestFilter::class)
            ->setArguments(['@httpRequest', [$config['jsonpKey'], $config['prettyPrintKey']]]);*/
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadSecuritySection(ContainerBuilder $container) : void
    {
        $container->addDefinition($this->prefix('security.nullAuthentication'))
            ->setFactory(\Drahak\Restful\Security\Process\NullAuthentication::class);

        $container->addDefinition($this->prefix('security.authentication'))
            ->setFactory(\Drahak\Restful\Security\AuthenticationContext::class)
            ->addSetup('$service->setAuthProcess(?)', [$this->prefix('@security.nullAuthentication')]);
    }

    /**
     * Register REST API extension
     * @param Configurator $configurator
     */
    public static function install(Configurator $configurator) : void
    {
        /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
        $configurator->onCompile[] = static function ($configurator, $compiler) : void {
            $compiler->addExtension('restfulApi', new RestfulApiExtension());
        };
    }
}
