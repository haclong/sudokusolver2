<?php

namespace AppBundle\Utils;

use AppBundle\Exception\MissingSessionContentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Description of SessionCompiler
 *
 * @author haclong
 */
class SessionCompiler implements CompilerPassInterface {
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has('sessionContent')) {
            throw new MissingSessionContentException() ;
        }

        $definition = $container->findDefinition('sessionContent');

        // find all service IDs with the app.mail_transport tag
        $taggedServices = $container->findTaggedServiceIds('session.content');

        foreach ($taggedServices as $id => $tags) {
            // add the transport service to the ChainTransport service
            $definition->addMethodCall('add', array(new Reference($id)));
        }
    }
}
