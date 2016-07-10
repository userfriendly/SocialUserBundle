<?php

namespace Userfriendly\Bundle\SocialUserBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class UserfriendlySocialUserBundle extends Bundle
{
    public function build( ContainerBuilder $container )
    {
        $mappings = array(
            realpath( __DIR__ . '/Resources/config/doctrine-mapping' ) => 'Userfriendly\Bundle\SocialUserBundle\Model',
        );
        if ( class_exists( 'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass' ))
        {
            $container->addCompilerPass( DoctrineOrmMappingsPass::createYamlMappingDriver( $mappings, array( 'userfriendly_social_user.model_manager_name' ), 'userfriendly_social_user.backend_type_orm' ));
        }
        if ( class_exists( 'Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass' ))
        {
            $container->addCompilerPass( DoctrineMongoDBMappingsPass::createYamlMappingDriver( $mappings, array( 'userfriendly_social_user.model_manager_name' ), 'userfriendly_social_user.backend_type_mongodb' ));
        }
    }
}
