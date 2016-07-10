<?php

namespace Userfriendly\Bundle\SocialUserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Array of supported resource owners, indentation is intentional to easily notice
     * which resource is of which type.
     *
     * @var array
     */
    private $resourceOwners = array(
        'oauth2',
            'facebook',
            'foursquare',
            'github',
            'google',
            'sensio_connect',
            'stack_exchange',
            'vkontakte',
            'windows_live',

        'oauth1',
            'linkedin',
            'twitter',
            'yahoo',
    );

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
                ->scalarNode( 'firewall_name' )->isRequired()->cannotBeEmpty()->end()
                ->scalarNode( 'user_class' )->isRequired()->cannotBeEmpty()->end()
                ->scalarNode( 'user_identity_class' )->defaultNull()->end()
                ->scalarNode( 'group_class' )->defaultNull()->end()
                ->scalarNode( 'model_manager_name' )->defaultNull()->end()
                ->scalarNode( 'mailsubject_emailchange' )->end()
                ->scalarNode( 'mailsubject_accountdetails' )->end()
            ->end();

        $this->defineResourceOwnerConfig( $rootNode );

        return $treeBuilder;
    }

    private function defineResourceOwnerConfig( ArrayNodeDefinition $node )
    {
        $node
            ->children()
                ->arrayNode('resource_owners')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('access_token_url')
                                ->validate()
                                    ->ifTrue(function($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('authorization_url')
                                ->validate()
                                    ->ifTrue(function($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('request_token_url')
                                ->validate()
                                    ->ifTrue(function($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('client_id')
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('client_secret')
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('infos_url')
                                ->validate()
                                    ->ifTrue(function($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('scope')
                            ->end()
                            ->scalarNode('user_response_class')
                                ->validate()
                                    ->ifTrue(function($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('service')
                                ->validate()
                                    ->ifTrue(function($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('type')
                                ->validate()
                                    ->ifNotInArray($this->resourceOwners)
                                    ->thenInvalid('Unknown resource owner type %s.')
                                ->end()
                                ->validate()
                                    ->ifTrue(function($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->arrayNode('paths')
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                        ->validate()
                            ->ifTrue(function($c) {
                                // skip if this contains a service
                                if (isset($c['service'])) {
                                    return false;
                                }

                                // for each type at least these have to be set
                                $children = array('type', 'client_id', 'client_secret');
                                foreach ($children as $child) {
                                    if (!isset($c[$child])) {
                                        return true;
                                    }
                                }

                                return false;
                            })
                            ->thenInvalid('You should set at least the type, client_id and the client_secret of a resource owner.')
                        ->end()
                        ->validate()
                            ->ifTrue(function($c) {
                                // skip if this contains a service
                                if (isset($c['service'])) {
                                    return false;
                                }

                                // Only validate the 'oauth2' and 'oauth1' type
                                if ('oauth2' !== $c['type'] && 'oauth1' !== $c['type']) {
                                    return false;
                                }

                                $children = array('authorization_url', 'access_token_url', 'infos_url');
                                foreach ($children as $child) {
                                    if (!isset($c[$child])) {
                                        return true;
                                    }
                                }

                                // one of the two should be set
                                return !isset($c['paths']) && !isset($c['user_response_class']);
                            })
                            ->thenInvalid("All parameters are mandatory for types 'oauth2' and 'oauth1'. Check if you're missing one of: access_token_url, authorization_url, infos_url or paths or user_response_class.")
                        ->end()
                        ->validate()
                            ->ifTrue(function($c) {
                                // skip if this contains a service
                                if (isset($c['service'])) {
                                    return false;
                                }

                                // Only validate the 'oauth2' and 'oauth1' type
                                if ('oauth2' !== $c['type'] && 'oauth1' !== $c['type']) {
                                    return false;
                                }

                                $children = array('identifier', 'nickname', 'realname');
                                foreach ($children as $child) {
                                    if (!isset($c['paths'][$child])) {
                                        return true;
                                    }
                                }

                                // one of the two should be set
                                return !isset($c['paths']) && !isset($c['user_response_class']);
                            })
                            ->thenInvalid("At least the 'identifier', 'nickname' and 'realname' paths should be configured for oauth2 and oauth1 types.")
                        ->end()
                        ->validate()
                            ->ifTrue(function($c) {
                                return isset($c['service']) && 1 !== count($c);
                            })
                            ->thenInvalid("If you're setting a service, no other arguments should be set.")
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
