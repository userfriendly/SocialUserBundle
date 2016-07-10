<?php

namespace Userfriendly\Bundle\SocialUserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class UserfriendlySocialUserExtension extends Extension implements PrependExtensionInterface
{
    private static $doctrineDrivers = array(
        'orm' => array(
            'registry' => 'doctrine',
            'tag' => 'doctrine.event_subscriber',
        ),
        'mongodb' => array(
            'registry' => 'doctrine_mongodb',
            'tag' => 'doctrine_mongodb.odm.event_subscriber',
        ),
    );

    public function load( array $configs, ContainerBuilder $container )
    {
        // Configuration
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration( $configuration, $configs );

        // Set some parameters
        $container->setParameter( 'uf_sub_mail_subject_emailchange', isset( $config['mailsubject_emailchange'] )
                ?: 'Change of email address requested' );
        $container->setParameter( 'uf_sub_mail_subject_accountdetails', isset( $config['mailsubject_accountdetails'] )
                ?: 'Your account details' );
        $container->setParameter( 'uf_firewall_name', isset( $config['firewall_name'] )
                ?: 'main' );

        // Services and mappings
        $loader = new YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ));
        $loader->load( 'services.yml' );
        if ( isset( self::$doctrineDrivers[$config['db_driver']] ))
        {
            $loader->load( 'doctrine.yml' );
            $container->setAlias( 'userfriendly_social_user.doctrine_registry', new Alias( self::$doctrineDrivers[$config['db_driver']]['registry'], false ));
            $container->setParameter( $this->getAlias() . '.backend_type_' . $config['db_driver'], true );
            $definition = $container->getDefinition( 'userfriendly_social_user.object_manager' );
            $definition->setFactory( array( new Reference( 'userfriendly_social_user.doctrine_registry' ), 'getManager' ));
        }
        else
        {
            throw new InvalidConfigurationException( 'Configured storage is not implemented.' );
        }

        $this->remapParametersNamespaces( $config, $container, array(
                '' => array(
                        'db_driver' => 'userfriendly_social_user.storage',
                        'model_manager_name' => 'userfriendly_social_user.model_manager_name',
                        'user_class' => 'userfriendly_social_user.model.user.class',
                        'user_identity_class' => 'userfriendly_social_user.model.user_identity.class',
                ),
        ));

        if ( !$container->getParameter( "userfriendly_social_user.model.user_identity.class" ))
        {
            $container->setParameter( "userfriendly_social_user.model.user_identity.class", "Userfriendly\Bundle\SocialUserBundle\Model\UserIdentity" );
        }
    }

    public function prepend( ContainerBuilder $container )
    {
        ////////////////////////////////////////////////////////
        // Get registered bundles, ensure the ones we require //
        // are there, and read configuration for this bundle. //
        ////////////////////////////////////////////////////////
        $bundles = $container->getParameter('kernel.bundles');
        $deps = array( 'FOSUserBundle', 'HWIOAuthBundle', 'StofDoctrineExtensionsBundle' );
        foreach ( $deps as $dep )
        {
            if ( !isset( $bundles[$dep] ))
            {
                throw new \Exception( 'You must enable the ' . $dep . ' in your AppKernel!' );
            }
        }
        $configs = $container->getExtensionConfig( $this->getAlias() );
        $config = $this->processConfiguration( new Configuration(), $configs );
        //////////////////////////////////////////////////////////
        // Pass mandatory configuration on to required bundles. //
        //////////////////////////////////////////////////////////
        // write firewall name to FOS User bundle and HWI OAuth bundle configurations
        $container->prependExtensionConfig( 'fos_user', array( 'firewall_name' => $config['firewall_name'] ));
        $container->prependExtensionConfig( 'hwi_oauth', array( 'firewall_names' => array( $config['firewall_name'] )));
        // write DB driver to FOS User bundle configuration
        $dbDriverConfig = array( 'db_driver' => $config['db_driver'] );
        $container->prependExtensionConfig( 'fos_user', $dbDriverConfig );
        // write User class name to FOS User bundle configuration
        $userClassConfig = array( 'user_class' => $config['user_class'] );
        $container->prependExtensionConfig( 'fos_user', $userClassConfig );
        /////////////////////////////////////////////////////////
        // Pass optional configuration on to required bundles. //
        /////////////////////////////////////////////////////////
        // write Group class name to FOS User bundle configuration
        if ( isset( $config['group_class'] ))
        {
            $groupClassConfig = array( 'group_class' => $config['group_class'] );
            $container->prependExtensionConfig( 'fos_user', $groupClassConfig );
        }
        // write OAuth resource owner configuration to HWI OAuth bundle configuration
        if ( isset( $config['resource_owners'] ))
        {
            $resourceOwners = array();
            foreach ( $config['resource_owners'] as $resourceOwnerName => $resourceOwner )
            {
                $resourceOwner['user_response_class'] = 'HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse';
                $resourceOwner['options'] = array( "csrf" => "true" );
                $resourceOwners[$resourceOwnerName] = $resourceOwner;
            }
            $oAuthConfig = array( 'resource_owners' => $resourceOwners );
            $container->prependExtensionConfig( 'hwi_oauth', $oAuthConfig );
        }
        /////////////////////////////////////////////////////////////
        // Add configuration pertinent to this bundle wrapping the //
        // other bundles and otherwise set some sensible defaults. //
        /////////////////////////////////////////////////////////////
        // configure connect provider
        $connectConfig = array(
            'account_connector' => 'userfriendly_social_user.oauth_user_provider',
        );
        $container->prependExtensionConfig( 'hwi_oauth', array( 'connect' => $connectConfig ));
        // configure HTTP client
        $httpClientConfig = array(
            'timeout' => 30,
            'verify_peer' => true,
            'ignore_errors' => true,
            'max_redirects' => 5,
        );
        $container->prependExtensionConfig( 'hwi_oauth', array( 'http_client' => $httpClientConfig ));
        // configure FOS User bundle integration in HWI OAuth bundle configuration
        $fosUbConfig = array(
            'username_iterations' => 30,
            'properties' => array(),
        );
        $container->prependExtensionConfig( 'hwi_oauth', array( 'fosub' => $fosUbConfig ));
        // configure use of Doctrine extensions
        $docExtConfig = array(
            'default' => array(
                'timestampable' => true,
                'sluggable' => true,
            ),
        );
        $container->prependExtensionConfig(
            'stof_doctrine_extensions', array( $config['db_driver'] => $docExtConfig ) // once we enable more DB drivers, this may need looked at
        );
        $container->prependExtensionConfig(
            'doctrine', array( $config['db_driver'] => array( // once we enable more DB drivers, this may need looked at
                'resolve_target_entities' => array(
                    'Userfriendly\Bundle\SocialUserBundle\Model\UserInterface' => $config['user_class'],
                )
            ))
        );
    }

    /*
     *
     */
    protected function remapParametersNamespaces( array $config, ContainerBuilder $container, array $namespaces )
    {
        foreach ( $namespaces as $ns => $map )
        {
            if ( $ns )
            {
                if ( !array_key_exists( $ns, $config ))
                {
                    continue;
                }
                $namespaceConfig = $config[$ns];
            }
            else
            {
                $namespaceConfig = $config;
            }
            if ( is_array( $map ))
            {
                $this->remapParameters( $namespaceConfig, $container, $map );
            }
            else
            {
                foreach ( $namespaceConfig as $name => $value )
                {
                    $container->setParameter( sprintf( $map, $name ), $value );
                }
            }
        }
    }

    /*
     *
     */
    protected function remapParameters( array $config, ContainerBuilder $container, array $map )
    {
        foreach ( $map as $name => $paramName )
        {
            if ( array_key_exists( $name, $config ))
            {
                $container->setParameter( $paramName, $config[$name] );
            }
        }
    }
}
