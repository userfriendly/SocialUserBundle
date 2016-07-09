<?php

namespace Userfriendly\Bundle\SocialUserBundle\Firewall;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Userfriendly\Bundle\SocialUserBundle\Model\UserInterface;

class RoleRefreshListener implements ListenerInterface
{
    protected $securityTokenStorage;
    protected $firewallName;

    public function __construct( TokenStorageInterface $securityContext, $firewallName )
    {
        $this->securityContext = $securityContext;
        $this->firewallName = $firewallName;
    }

    public function handle( GetResponseEvent $event )
    {
        if ( !$event->isMasterRequest() ) return;
        $token = $this->securityTokenStorage->getToken();
        if ( !$token ) return;
        $user = $token->getUser();
        if ( $user instanceof UserInterface )
        {
            $this->replaceToken( $user );
//             echo '<pre>';
//             print_r( $user->getRoles() );
//             print_r( $token->getRoles() );
//             echo '</pre>';
        }
    }

    protected function replaceToken( UserInterface $user )
    {
        $token = new UsernamePasswordToken( $user, null, $this->firewallName, $user->getRoles() );
        $this->securityTokenStorage->setToken( $token );
    }
}
