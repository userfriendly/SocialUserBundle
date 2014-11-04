<?php

namespace Userfriendly\Bundle\SocialUserBundle\Firewall;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Userfriendly\Bundle\SocialUserBundle\Model\UserInterface;

class RoleRefreshListener implements ListenerInterface
{
    protected $securityContext;
    protected $firewallName;

    public function __construct( SecurityContextInterface $securityContext, $firewallName )
    {
        $this->securityContext = $securityContext;
        $this->firewallName = $firewallName;
    }

    public function handle( GetResponseEvent $event )
    {
        if ( !$event->isMasterRequest() ) return;
        $token = $this->securityContext->getToken();
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
        $this->securityContext->setToken( $token );
    }
}
