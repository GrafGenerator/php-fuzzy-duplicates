<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 22:02
 */

namespace App\DependencyInjection\Compiler;


use App\Abstractions\OperationsProcessing\OperationHandlerServiceLocatorInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OperationHandlerPass implements CompilerPassInterface
{
    private static $handlersTag = "app.operation_handler";

    public function process(ContainerBuilder $container)
    {
        if (!$container->has(OperationHandlerServiceLocatorInterface::class)) {
            return;
        }

        $locatorDefinition = $container->findDefinition(OperationHandlerServiceLocatorInterface::class);

        // copy-paste from $container->findTaggedServiceIds

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->hasTag(OperationHandlerPass::$handlersTag)) {
                if ($definition->isAbstract()) {
                    throw new \InvalidArgumentException(
                        sprintf('The service "%s" tagged "%s" must not be abstract.',
                        $id,
                        OperationHandlerPass::$handlersTag)
                    );
                }

                $locatorDefinition->addMethodCall('registerHandler', array(
                    new Reference($id),
                    $definition->getClass()
                ));
            }
        }
    }
}