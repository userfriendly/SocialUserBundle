<?php

namespace Userfriendly\Bundle\SocialUserBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Userfriendly\Bundle\SocialUserBundle\Model\User;

class UserAccountMergedEvent extends Event
{
    const ID = 'security.user_accounts_merged';

    /**
     * @var \Userfriendly\Bundle\SocialUserBundle\Model\User $mergingUser
     */
    protected $mergingUser;

    /**
     * @var \Userfriendly\Bundle\SocialUserBundle\Model\User $mergedUser
     */
    protected $mergedUser;

    /**
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\User
     */
    public function getMergingUser()
    {
        return $this->mergingUser;
    }

    /**
     * @param \Userfriendly\Bundle\SocialUserBundle\Model\User $user
     */
    public function setMergingUser( User $user )
    {
        $this->mergingUser = $user;
    }

    /**
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\User
     */
    public function getMergedUser()
    {
        return $this->mergedUser;
    }

    /**
     * @param \Userfriendly\Bundle\SocialUserBundle\Model\User $user
     */
    public function setMergedUser( User $user )
    {
        $this->mergedUser = $user;
    }
}
