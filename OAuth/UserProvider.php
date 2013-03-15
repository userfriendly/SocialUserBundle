<?php

namespace Userfriendly\Bundle\SocialUserBundle\OAuth;

use Userfriendly\Bundle\SocialUserBundle\Model\User;
use Userfriendly\Bundle\SocialUserBundle\Model\UserIdentity;
use Userfriendly\Bundle\SocialUserBundle\Model\StorageAgnosticObjectManager;
use Userfriendly\Bundle\SocialUserBundle\Event\UserAccountMergedEvent;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

class UserProvider implements OAuthAwareUserProviderInterface
{
    /** @var UserManagerInterface */
    protected $userManager;

    /** @var StorageAgnosticObjectManager */
    protected $identityManager;

    /** @var ContainerAwareEventDispatcher */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param UserManagerInterface           $userManager      FOSUB User manager
     * @param StorageAgnosticObjectManager   $identityManager  Identity manager
     * @param ContainerAwareEventDispatcher  $eventDispatcher  Event dispatcher
     */
    public function __construct( UserManagerInterface $userManager, StorageAgnosticObjectManager $identityManager, ContainerAwareEventDispatcher $eventDispatcher )
    {
        $this->userManager = $userManager;
        $this->identityManager = $identityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function connect( $user, UserResponseInterface $response )
    {
        $existingIdentity = $this->getExistingIdentity( $response );
        if ( $existingIdentity )
        {
            $previousUser = $existingIdentity->getUser();
            $event = new UserAccountMergedEvent( 'User accounts merged' );
            $event->setMergedUser( $previousUser );
            $event->setMergingUser( $user );
            $this->eventDispatcher->dispatch( UserAccountMergedEvent::ID, $event );
            $existingIdentity->setUser( $user );
            $existingIdentity->setAccessToken( $this->getAccessToken( $response ));
            $this->identityManager->update( $existingIdentity );
        }
        else
        {
            $this->createIdentity( $user, $response );
        }
    }

    /**
     *
     * @param string $slug
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserInterface
     */
    public function findOneByUsernameSlug( $slug )
    {
        $criteria = array( 'usernameSlug' => $slug );
        return $this->userManager->findUserBy( $criteria );
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse( UserResponseInterface $response )
    {
        $existingIdentity = $this->getExistingIdentity( $response );
        if ( $existingIdentity )
        {
            $existingIdentity->setAccessToken( $this->getAccessToken( $response ));
            return $existingIdentity->getUser();
        }
        return $this->createUser( $response );
    }

    /**
     * Checks whether the authenticating Identity already exists
     *
     * @param \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface $response
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserIdentity
     */
    protected function getExistingIdentity( UserResponseInterface $response )
    {
        return $this->identityManager->findBy( array(
            'identifier' => $response->getUsername(),
            'type' => $response->getResourceOwner()->getName(),
        ));
    }

    /**
     * Creates new User
     *
     * @param \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface $response
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\User
     */
    protected function createUser( UserResponseInterface $response )
    {
        $user = $this->userManager->createUser();
        $user->setUsername( $this->createUniqueUsername( $this->getRealName( $response )));
        $user->setEmail( $this->getEmail( $response ) );
        $user->setPassword( '' );
        $user->setEnabled( true );
        $this->userManager->updateUser( $user );
        $this->createIdentity( $user, $response );
        return $user;
    }

    /**
     * Creates new Identity
     *
     * @param \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface $response
     * @param \Userfriendly\Bundle\SocialUserBundle\Model\User $user
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserIdentity
     */
    protected function createIdentity( User $user, UserResponseInterface $response )
    {
        $identity = $this->identityManager->create();
        $identity->setAccessToken( $this->getAccessToken( $response ));
        $identity->setIdentifier( $response->getUsername() );
        $identity->setType( $response->getResourceOwner()->getName() );
        $identity->setUser( $user );
        $identity->setName( $this->getRealName( $response ));
        $identity->setEmail( $this->getEmail( $response ));
        $this->identityManager->update( $identity );
        return $identity;
    }

    /**
     * Ensures uniqueness of username
     *
     * @param string $username
     * @return string
     */
    protected function createUniqueUsername( $username )
    {
        $originalName = $username;
        $existingUser = $this->userManager->findUserByUsername( $username );
        $suffix = 0;
        while ( $existingUser )
        {
            $suffix++;
            $username = $originalName . $suffix;
            $existingUser = $this->userManager->findUserByUsername( $username );
        }
        return $username;
    }

    /**
     * Workaround method for HWIOAuthBundle issue
     *
     * Waiting for this issue to be fixed upstream
     *
     * @param \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface $response
     * @return string
     */
    protected function getAccessToken( UserResponseInterface $response )
    {
        $accessToken = $response->getAccessToken();
        switch ( $response->getResourceOwner()->getName() )
        {
            case UserIdentity::getReadableType( UserIdentity::TYPE_TWITTER ):
            case UserIdentity::getReadableType( UserIdentity::TYPE_YAHOO ):
                return $accessToken['oauth_token'];
            default:
                return $accessToken;
        }
    }

    /**
     * Workaround method for HWIOAuthBundle issue
     *
     * Waiting for this issue to be fixed upstream
     *
     * @param \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface $response
     * @return string
     */
    protected function getRealName( UserResponseInterface $response )
    {
        switch ( $response->getResourceOwner()->getName() )
        {
            case UserIdentity::getReadableType( UserIdentity::TYPE_YAHOO ):
                $responseArray = $response->getResponse();
                $name = trim( $responseArray['profile']['givenName'] . ' ' . $responseArray['profile']['familyName'] );
                return $name;
            default:
                return $response->getRealName();
        }
    }

    /**
     * Workaround method for HWIOAuthBundle issue
     *
     * Waiting for this issue to be fixed upstream
     *
     * @param \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface $response
     * @return string
     */
    protected function getEmail( UserResponseInterface $response )
    {
        $responseArray = $response->getResponse();
        switch ( $response->getResourceOwner()->getName() )
        {
            case UserIdentity::getReadableType( UserIdentity::TYPE_TWITTER ):
                return NULL;
            case UserIdentity::getReadableType( UserIdentity::TYPE_YAHOO ):
                if ( array_key_exists( 'emails', $responseArray['profile'] ))
                {
                    if ( count( $responseArray['profile']['emails'] ) > 0 )
                    {
                        return $responseArray['profile']['emails'][0]['handle'];
                    }
                }
            default:
                if ( array_key_exists( 'email', $responseArray ))
                {
                    return $responseArray['email'];
                }
        }
    }

}