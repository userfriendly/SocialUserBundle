<?php

namespace Userfriendly\Bundle\SocialUserBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

class UserIdentityManager
{
    protected $objectManager;
    protected $class;

    public function __construct( ObjectManager $om, $className )
    {
        $this->objectManager = $om;
        $this->class = $className;
    }

    public function findIdentityBy( $criteria )
    {
        if ( !array_key_exists( 'identifier', $criteria ) || !array_key_exists( 'type', $criteria ))
        {
            throw new \InvalidArgumentException( 'Identity objects must be retrieved by identifier and type' );
        }
        if ( !is_int( $criteria['type'] ))
        {
            $criteria['type'] = UserIdentity::getStorableType( $criteria['type'] );
        }
        return $this->objectManager
                    ->getRepository( 'UserfriendlySocialUserBundle:UserIdentity' )
                    ->findOneBy( $criteria );
    }

    public function createIdentity()
    {
        $identity = new $this->class;
        return $identity;
    }

    /**
     * Updates a user identity.
     *
     * @param UserIdentity $identity
     * @param Boolean      $andFlush Whether to flush the changes (default true)
     */
    public function updateIdentity( UserIdentity $identity, $andFlush = true )
    {
        $this->objectManager->persist( $identity );
        if ( $andFlush ) $this->objectManager->flush();
    }
}
