<?php

namespace Userfriendly\Bundle\SocialUserBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

class UserEmailChangeRequestManager
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
                    ->getRepository( 'UserfriendlySocialUserBundle:UserEmailChangeRequest' )
                    ->findOneBy( $criteria );
    }

    public function createEmailChangeRequest()
    {
        $emailChangeRequest = new $this->class;
        return $emailChangeRequest;
    }

    /**
     * Updates a user email change request.
     *
     * @param UserEmailChangeRequest    $emailChangeRequest
     * @param Boolean                   $andFlush   Whether to flush the changes (default true)
     */
    public function updateIdentity( UserEmailChangeRequest $emailChangeRequest, $andFlush = true )
    {
        $this->objectManager->persist( $emailChangeRequest );
        if ( $andFlush ) $this->objectManager->flush();
    }
}
