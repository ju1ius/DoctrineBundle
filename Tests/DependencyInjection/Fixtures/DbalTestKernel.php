<?php

namespace Doctrine\Bundle\DoctrineBundle\Tests\DependencyInjection\Fixtures;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\DoctrineBundle\Tests\TestCaseAllPublicCompilerPass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class DbalTestKernel extends Kernel
{
    /** @var string|null */
    private $projectDir;

    public function __construct()
    {
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(static function (ContainerBuilder $container): void {
            // @todo Setting the kernel.name parameter can be removed once the dependency on DoctrineCacheBundle has been dropped
            $container->setParameter('kernel.name', 'foo');
            $container->loadFromExtension('framework', ['secret' => 'F00']);

            $container->loadFromExtension('doctrine', [
                'dbal' => ['driver' => 'pdo_sqlite'],
            ]);

            // Register a NullLogger to avoid getting the stderr default logger of FrameworkBundle
            $container->register('logger', NullLogger::class);

            // make all Doctrine services public, so we can fetch them in the test
            $container->getCompilerPassConfig()->addPass(new TestCaseAllPublicCompilerPass());
        });
    }

    public function getProjectDir(): string
    {
        if ($this->projectDir === null) {
            $this->projectDir = sys_get_temp_dir() . '/sf_kernel_' . md5(mt_rand());
        }

        return $this->projectDir;
    }

    public function getRootDir(): string
    {
        return $this->getProjectDir();
    }
}
