<?php

namespace Userfriendly\Bundle\SocialUserBundle\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFunction;

class SocialUserExtension extends Twig_Extension
{
    protected $userIdentityClass;

    public function __construct( $userIdentityClass )
    {
        $this->userIdentityClass = $userIdentityClass;
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
        $identityRepo = $this->om->getRepository( $this->userIdentityClass );
        $identities = $identityRepo->findBy( array( 'user' => $user ));
        if ( $asStrings )
        {
            $strings = array();
            foreach ( $identities as $identity ) $strings[] = $identity->getType();
            return $strings;
        }
        return $identities;
    }
}
