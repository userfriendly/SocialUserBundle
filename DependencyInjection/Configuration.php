<?php

namespace Userfriendly\Bundle\SocialUserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root( 'userfriendly_social_user' );

        $supportedDrivers = array( 'orm' );
        //$supportedDrivers = array( 'orm', 'mongodb' );

        $rootNode
            ->children()
                ->scalarNode( 'db_driver' )
                    ->validate()
                        ->ifNotInArray( $supportedDrivers )
                        ->thenInvalid( 'The driver %s is not supported. Please choose one of ' . json_encode( $supportedDrivers ))
                    ->end()
                    ->cannotBeOverwritten()
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
//                ->scalarNode( 'firewall_name' )->isRequired()->cannotBeEmpty()->end()
//                ->scalarNode( 'identity_class' )->isRequired()->cannotBeEmpty()->end()
//                ->scalarNode( 'user_class' )->isRequired()->cannotBeEmpty()->end()
//                ->scalarNode( 'group_class' )->end()
            ->end();

        return $treeBuilder;
    }
}
