<?php

namespace Userfriendly\Bundle\SocialUserBundle\Controller;

use Userfriendly\Bundle\SocialUserBundle\Model\StorageAgnosticObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{

    const EDIT = 'edit';
    const SHOW = 'show';

    public function editAction( Request $request )
    {
        return $this->showAndEdit( $request, self::EDIT );
    }

    public function showAction( Request $request )
    {
        return $this->showAndEdit( $request, self::SHOW );
    }

    public function saveUsernameAction( Request $request )
    {
        return $this->saveUserDataAndGuardAgainstNonPermittedEdit( $request, 'username' );
    }

    public function savePasswordAction( Request $request )
    {
        return $this->saveUserDataAndGuardAgainstNonPermittedEdit( $request, 'password' );
    }

    public function requestChangeEmailAction( Request $request )
    {
        $message  = 'Email address change request received.';
        $message .= ' Check your inbox for the confirmation email.';
        if ( $request->isMethod( 'post' ))
        {
            $em = $this->getDoctrine()->getEntityManager();
            $repo = $em->getRepository( 'UserfriendlySocialUserBundle:User' );
            $user = $repo->findOneByUsernameSlug( $request->get( 'username_slug' ));
            if ( false /* $user is allowed request? */ )
            {
                $message  = 'You are currently not allowed to request this email address change.';
                $message .= ' Please contact an administrator for more information.';
            }
            else
            {
                $email = $request->get( 'email' );
                $canonicalEmail = $this->get( 'fos_user.util.email_canonicalizer' )->canonicalize( $email );
                if ( $repo->findOneByEmailCanonical( $canonicalEmail ))
                {
                    $message  = 'Email address already in use.';
                    $message .= ' Please contact an administrator for more information.';
                }
                else
                {
                    $emailChangeRequestManager = $this->get( 'uf.security.email_change_request_manager' );
                    $changeRequest = $emailChangeRequestManager->create();
                    $changeRequest->setUser( $user );
                    $token = substr( $this->get( 'fos_user.util.token_generator' )->generateToken(), 0, 12 );
                    $changeRequest->setConfirmationToken( $token );
                    $changeRequest->setEmail( $email );
                    $em->persist( $changeRequest );
                    $em->flush();
                    // send confirmation email to new address
                    $domain = 'www.' . $this->container->getParameter( 'website_domain' );
                    $url = 'http://' . $domain . str_replace(
                                '/app_dev.php', '',
                                $this->generateUrl( 'uf_profile_confirm_change_email', array(
                                    'confirmation_token' => $token,
                                )
                            ));
                    $this->get( 'fp.email' )->send(
                        $canonicalEmail,
                        $this->container->getParameter( 'mail_subject_emailchange' ),
                        $this->renderView( 'UserfriendlySocialUserBundle:Email:emailchangerequest.html.twig', array(
                            'name' => $user->getUsername(),
                            'website' => $domain,
                            'email' => $email,
                            'url' => $url,
                        ))
                    );
                    // Redirect to self
                    return $this->redirect(
                                $this->generateUrl( 'uf_profile_request_change_email', array(
                                    'username_slug' => $user->getUsernameSlug(),
                                )));
                }
            }
        }
        return $this->render( 'UserfriendlySocialUserBundle:Profile:message.html.twig', array(
                                'message' => $message,
                            ));
    }

    public function confirmChangeEmailAction( Request $request )
    {
        $token = $request->get( 'confirmation_token' );
        if ( !$token ) throw new NotFoundHttpException();
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository( 'UserfriendlySocialUserBundle:UserEmailChangeRequest' );
        $changeRequest = $repo->findOneBy( array(
                                    'confirmationToken' => $token,
                                    'confirmed' => false,
                                ));
        $changeRequest->setConfirmationToken( NULL );
        $changeRequest->setConfirmed( true );
        $changeRequest->getUser()->setEmail( $changeRequest->getEmail() );
        $em->flush();
        return $this->render( 'UserfriendlySocialUserBundle:UserfriendlySocialUserBundle:message.html.twig', array(
                                'message' => 'Your email address has been changed.',
                            ));
    }

    /**
     * AJAX action for checking username availability
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function usernameAvailableAction( Request $request )
    {
        $response = array(
            'cssClass' => 'success',
            'text' => 'username is available',
        );
        $user = $this->get( 'uf.security.oauth_user_provider' )
                     ->findOneByUsernameSlug( $request->get( 'username_slug' ));
        $currentUser = $this->get( 'security.context' )->getToken()->getUser();
        if ( $currentUser->getId() != $user->getId() && !$this->get( 'security.context' )->isGranted( 'ROLE_ADMIN' ))
        {
            throw new NotFoundHttpException();
        }
        $requestedUsername = $request->get( 'username' );
        $canonicalUsername = $this->get( 'fos_user.util.username_canonicalizer' )->canonicalize( $requestedUsername );
        if ( $requestedUsername != $user->getUsername() )
        {
            $users = $repo->findByUsernameCanonical( $canonicalUsername );
            if ( count( $users ) > 0 )
            {
                $response['cssClass'] = 'error';
                $response['text'] = 'username not available';
            }
        }
        return new Response( json_encode( $response ));
    }

    /**
     * Private methods for use in this Controller's public methods
     */

    private function showAndEdit( Request $request, $action )
    {
        $user = $this->get( 'uf.security.oauth_user_provider' )
                     ->findOneByUsernameSlug( $request->get( 'username_slug' ));
        if ( $user )
        {
            $currentUser = $this->get( 'security.context' )->getToken()->getUser();
            if (
                    self::SHOW == $action
                    || $currentUser->getId() == $user->getId()
                    || $this->get( 'security.context' )->isGranted( 'ROLE_ADMIN' )
            )
            {
                return $this->render( 'UserfriendlySocialUserBundle:Profile:' . $action . '.html.twig', array(
                    'user' => $user,
                ));
            }
            throw new AccessDeniedException();
        }
        throw new NotFoundHttpException();
    }

    private function saveUserDataAndGuardAgainstNonPermittedEdit( Request $request, $key )
    {
        // Get the User object in question
        $user = $this->get( 'uf.security.oauth_user_provider' )
                     ->findOneByUsernameSlug( $request->get( 'username_slug' ));
        if ( !$user ) throw new NotFoundHttpException();
        // Make sure the current user has editing rights
        $currentUser = $this->get( 'security.context' )->getToken()->getUser();
        if ( $currentUser->getId() != $user->getId() && !$this->get( 'security.context' )->isGranted( 'ROLE_ADMIN' ))
        {
            throw new AccessDeniedException();
        }
        switch ( $key )
        {
            case 'username':
                $user->setUsername( $request->get( $key ));
                $this->get( 'session' )->getFlashBag()->add( 'success', 'User name was successfully set.' );
                break;
            case 'password':
                $password = $request->get( $key );
                $user->setPlainPassword( $password );
                if ( $request->get( 'send_details' ))
                {
                    // Send email with account details
                    $domain = 'www.' . $this->container->getParameter( 'website_domain' );
                    $url = 'http://' . $domain . str_replace(
                                '/app_dev.php', '',
                                $this->generateUrl( 'fos_user_security_login' )
                            );
                    $this->get( 'fp.email' )->send(
                        $user->getEmailCanonical(),
                        $this->container->getParameter( 'mail_subject_emailchange' ),
                        $this->renderView( 'UserfriendlySocialUserBundle:Email:emailchangerequest.html.twig', array(
                            'name' => $user->getUsername(),
                            'website' => $domain,
                            'email' => $user->getEmailCanonical(),
                            'password' => $password,
                            'url' => $url,
                        ))
                    );
                }
                $this->get( 'session' )->getFlashBag()->add( 'success', 'Password was successfully set.' );
                break;
        }
        $this->get( 'fos_user.user_manager' )->updateUser( $user );
        $this->getDoctrine()->getEntityManager()->flush();
        return $this->redirect(
                    $this->generateUrl( 'uf_profile_edit', array(
                        'username_slug' => $user->getUsernameSlug()
                    )));
    }

    protected function getUserRepository()
    {
//        $em = $this->getDoctrine()->getEntityManager();
//        $associationMapping = $em->getClassMetadata('CiscoSystems\AuditBundle\Entity\Audit')
//                                 ->getAssociationMapping('auditReference');
//        $targetEntity = $associationMapping['targetEntity'];
    }

}
