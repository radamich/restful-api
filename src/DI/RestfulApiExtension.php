<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\DI;

use Movisio\RestfulApi\Application\Converters\CamelCaseConverter;
use Movisio\RestfulApi\Application\Converters\DateTimeConverter;
use Movisio\RestfulApi\Application\Converters\ResourceConverter;
use Movisio\RestfulApi\Utils\RequestFilter;
use Nette\Configurator;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\NotImplementedException;
use Nette\Schema;

/**
 * Nette DI extension
 */
class RestfulApiExtension extends CompilerExtension
{
    /** Converter tag name */
    protected const CONVERTER_TAG = 'restful-api.converter';

    /** Camel case convention config name */
    protected const CONVENTION_CAMEL_CASE = 'camelCase';

    /**
     * Define NEON config schema
     * @return Schema\Schema
     */
    public function getConfigSchema() : Schema\Schema
    {
        return Schema\Expect::structure([
            'convention' => Schema\Expect::string(null),
            'timeFormat' => Schema\Expect::string('c')->dynamic(),
        ]);
    }

    /**
     * Load DI configuration
     */
    public function loadConfiguration() : void
    {
        $container = $this->getContainerBuilder();
        $config = (array)$this->getConfig();

        // Additional module
        $this->loadRestful($container, $config);
        $this->loadSecuritySection($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function loadRestful(ContainerBuilder $container, array $config) : void
    {
        if (!is_null($config['convention']) && $config['convention'] !== self::CONVENTION_CAMEL_CASE) {
            throw new NotImplementedException('Only camelCase convention is currently implemented');
        }

        // Input & validation
        $container->addDefinition($this->prefix('inputFactory'))
            ->setFactory(\Drahak\Restful\Http\InputFactory::class);

        $container->addDefinition($this->prefix('httpRequestFactory'))
            ->setFactory(\Drahak\Restful\Http\ApiRequestFactory::class);

        $container->getDefinition('httpRequest')
            ->setFactory($this->prefix('@httpRequestFactory') . '::createHttpRequest');

        $container->addDefinition($this->prefix('camelCaseConverter'))
            ->setFactory(CamelCaseConverter::class);

        $container->addDefinition($this->prefix('resourceConverter'))
            ->setFactory(ResourceConverter::class);

        if ($config['convention'] === self::CONVENTION_CAMEL_CASE) {
            $container->getDefinition($this->prefix('camelCaseConverter'))
                ->addTag(self::CONVERTER_TAG);
        }

        $container->addDefinition($this->prefix('dateTimeConverter'))
            ->setFactory(DateTimeConverter::class)
            ->setArguments([$config['timeFormat']])
            ->addTag(self::CONVERTER_TAG);

        $container->addDefinition($this->prefix('requestFilter'))
            ->setFactory(RequestFilter::class)
            ->setArguments(['@httpRequest']);
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

    /**
     * Before compile
     */
    public function beforeCompile() : void
    {
        $container = $this->getContainerBuilder();

        $resourceConverter = $container->getDefinition($this->prefix('resourceConverter'));
        $services = $container->findByTag(self::CONVERTER_TAG);
        foreach ($services as $service => $args) {
            $resourceConverter->addSetup('$service->addConverter(?)', ['@' . $service]);
        }
    }
}
