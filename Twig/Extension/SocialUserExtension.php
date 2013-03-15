<?php

namespace Userfriendly\Bundle\SocialUserBundle\Twig\Extension;

use Twig_Extension;
use Twig_Function_Method;
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
            'oauth_identities' => new Twig_Function_Method( $this, 'getIdentitiesFor' ),
            //'foo' => new Twig_Function_Method( $this, 'foo', array( 'is_safe' => array( 'html' ))),
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
