<?php

namespace Userfriendly\Bundle\SocialUserBundle\Model;

/**
 * Storage agnostic user identity object
 */
abstract class UserIdentity
{
    const TYPE_GOOGLE   = "google";
    const TYPE_FACEBOOK = "facebook";
    const TYPE_YAHOO    = "yahoo";
    const TYPE_TWITTER  = "twitter";
//    const TYPE_GOOGLE   = 1;
//    const TYPE_FACEBOOK = 2;
//    const TYPE_YAHOO    = 3;
//    const TYPE_TWITTER  = 4;

    /**
     *
     */
    protected $id;

    /**
     * @var integer
     */
    protected $type;

    /**
     * @var \Userfriendly\Bundle\SocialUserBundle\Model\UserInterface
     */
    protected $user;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $createdAt;

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
     * Set type
     *
     * @param integer $type
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserIdentity
     */
    public function setType( $type )
    {
        $this->type = is_int( $type ) ? $type : self::getStorableType( $type );
        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get type in human-readable form
     *
     * @return string
     */
    public function getTypeString()
    {
        return self::getReadableType( $this->type );
    }

    /**
     * Convert type to human-readable form
     *
     * @param integer $type
     * @return string
     */
    static public function getReadableType( $type )
    {
        switch ( $type )
        {
            case self::TYPE_GOOGLE:     return 'google';
            case self::TYPE_FACEBOOK:   return 'facebook';
            case self::TYPE_YAHOO:      return 'yahoo';
            case self::TYPE_TWITTER:    return 'twitter';
        }
    }

    /**
     * Convert human-readable type to integer
     *
     * @param string $type
     * @return integer
     */
    static public function getStorableType( $type )
    {
        switch ( $type )
        {
            case 'google':      return self::TYPE_GOOGLE;
            case 'facebook':    return self::TYPE_FACEBOOK;
            case 'yahoo':       return self::TYPE_YAHOO;
            case 'twitter':     return self::TYPE_TWITTER;
        }
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserIdentity
     */
    public function setIdentifier( $identifier )
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set accessToken
     *
     * @param string $accessToken
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserIdentity
     */
    public function setAccessToken( $accessToken )
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * Get accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set user
     *
     * @param \Userfriendly\Bundle\SocialUserBundle\Model\UserInterface $user
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserIdentity
     */
    public function setUser( User $user = null )
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserIdentity
     */
    public function setName( $name )
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserIdentity
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserIdentity
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
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserIdentity
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
}