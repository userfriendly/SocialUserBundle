<?php

namespace Userfriendly\Bundle\SocialUserBundle\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFunction;
use Userfriendly\Bundle\SocialUserBundle\Model\StorageAgnosticObjectManager;

class SocialUserExtension extends Twig_Extension
{
    protected $identityManager;

    public function __construct( StorageAgnosticObjectManager $identityManager )
    {
        $this->identityManager = $identityManager;
    }

    public function getName()
    {
        return 'social_user_extension';
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction( 'oauth_identities', array( $this, 'getIdentitiesFor' )),
        );
    }

    public function getIdentitiesFor( $user, $asStrings = false )
    {
        $identities = $this->identityManager->findBy( array( 'user' => $user ));
        if ( $asStrings )
        {
            $strings = array();
            foreach ( $identities as $identity ) $strings[] = $identity->getType();
            return $strings;
        }
        return $identities;
    }
}
