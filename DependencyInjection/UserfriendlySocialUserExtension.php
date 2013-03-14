<?php

namespace Userfriendly\Bundle\SocialUserBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UserfriendlySocialUserExtension extends Extension
{
    public function load( array $configs, ContainerBuilder $container )
    {
        // Configuration
        $configuration = new Configuration();
        $config = $this->processConfiguration( $configuration, $configs );
        // Services
        $loader = new YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ));
        $loader->load( 'services.yml' );
        $loader->load( sprintf('%s.yml', $config['db_driver'] ));
        // Set parameters
//        $container->setParameter( 'wg_openid.db_driver', $config['db_driver'] );
//        if ( isset( $config['firewall_name'] ) ) $container->setParameter( 'wg_openid.fos_user.firewall_name', $config['firewall_name'] );
//        if ( isset( $config['identity_class'] ) ) $container->setParameter( 'wg_openid.fp_openid.identity_class', $config['identity_class'] );
//        if ( isset( $config['user_class'] ) ) $container->setParameter( 'wg_openid.fos_user.user_class', $config['user_class'] );
//        if ( isset( $config['group_class'] ) ) $container->setParameter( 'wg_openid.fos_user.group_class', $config['group_class'] );
    }
}
