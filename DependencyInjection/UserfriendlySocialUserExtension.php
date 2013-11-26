<?php

namespace Userfriendly\Bundle\SocialUserBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UserfriendlySocialUserExtension extends Extension implements PrependExtensionInterface
{
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
        $firewallConfig = array( 'firewall_name' => $config['firewall_name'] );
        $container->prependExtensionConfig( 'fos_user', $firewallConfig );
        $container->prependExtensionConfig( 'hwi_oauth', $firewallConfig );
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
            'account_connector' => 'uf.security.oauth_user_provider',
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

    public function load( array $configs, ContainerBuilder $container )
    {
        // Configuration
        $configuration = new Configuration();
        $config = $this->processConfiguration( $configuration, $configs );
        // Services
        $loader = new YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ));
        $loader->load( 'services.yml' );
        $loader->load( sprintf('%s.yml', $config['db_driver'] ));
        // Set parameters, e.g.
//        $container->setParameter( 'foo, $config['foo'] );
    }
}
