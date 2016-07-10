<?php

namespace Userfriendly\Bundle\SocialUserBundle\Model;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Storage agnostic user object
 */
class User extends BaseUser implements UserInterface
{
    /**
     * The purpose of this unique placeholder element is its use in a column
     * like `emailCanonical` which is mapped as `unique` and thus cannot be
     * set to NULL. Since this application implements both a traditional and
     * a social login, the user must have the choice to not provide an email
     * address.
     *
     * By means of overriding the setters for both the `emailCanonical` and
     * `email` properties, this is handled transparently.
     */
    const PLACEHOLDER_SUFFIX = '@not.set';

    protected function createUniquePlaceholder()
    {
        return md5( time() . rand( 0, 99999 )) . self::PLACEHOLDER_SUFFIX;
    }

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $usernameSlug;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $identities;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $emailChangeRequests;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->identities = new ArrayCollection();
        $this->emailChangeRequests = new ArrayCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function setEmail( $email )
    {
        if ( !$email ) $this->setEmailCanonical( NULL );
        if ( NULL === $email ) $email = '';
        return parent::setEmail($email);
    }

    /**
     * {@inheritDoc}
     */
    public function setEmailCanonical( $emailCanonical )
    {
        $emailCanonical = $emailCanonical ?: $this->createUniquePlaceholder();
        return parent::setEmailCanonical( $emailCanonical );
    }

    /**
     * {@inheritDoc}
     */
    public function setUsernameSlug( $usernameSlug )
    {
        $this->usernameSlug = $usernameSlug;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsernameSlug()
    {
        return $this->usernameSlug;
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt( $createdAt )
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt( $updatedAt )
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritDoc}
     */
    public function addIdentity( UserIdentity $identity )
    {
        $this->identities[] = $identity;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeIdentity( UserIdentity $identity )
    {
        $this->identities->removeElement( $identity );
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentities()
    {
        return $this->identities;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentitiesAsStrings()
    {
        $result = array();
        foreach ( $this->identities as $identity )
        {
            $result[] = $identity->getTypeString();
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function addEmailChangeRequest( UserEmailChangeRequest $emailChangeRequest )
    {
        $this->emailChangeRequests[] = $emailChangeRequest;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeEmailChangeRequest( UserEmailChangeRequest $emailChangeRequest )
    {
        $this->emailChangeRequests->removeElement( $emailChangeRequest );
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmailChangeRequests()
    {
        return $this->emailChangeRequests;
    }
}