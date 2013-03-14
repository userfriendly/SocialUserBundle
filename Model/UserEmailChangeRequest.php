<?php

namespace Userfriendly\Bundle\SocialUserBundle\Model;

/**
 * Storage agnostic user email change request object
 */
abstract class UserEmailChangeRequest
{
    /**
     *
     */
    protected $id;

    /**
     * @var \Userfriendly\Bundle\SocialUserBundle\Model\User
     */
    protected $user;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $confirmationToken;

    /**
     * @var boolean
     */
    protected $confirmed;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->confirmed = false;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserEmailChangeRequest
     */
    public function setEmail( $email )
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set confirmationToken
     *
     * @param string $confirmationToken
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserEmailChangeRequest
     */
    public function setConfirmationToken( $confirmationToken )
    {
        $this->confirmationToken = $confirmationToken;
        return $this;
    }

    /**
     * Get confirmationToken
     *
     * @return string
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserEmailChangeRequest
     */
    public function setConfirmed( $confirmed )
    {
        $this->confirmed = $confirmed;
        return $this;
    }

    /**
     * Get confirmed
     *
     * @return boolean
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserEmailChangeRequest
     */
    public function setUpdatedAt( $updatedAt )
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserEmailChangeRequest
     */
    public function setCreatedAt( $createdAt )
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set user
     *
     * @param \Userfriendly\Bundle\SocialUserBundle\Model\User $user
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserEmailChangeRequest
     */
    public function setUser( User $user = null )
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\User
     */
    public function getUser()
    {
        return $this->user;
    }
}