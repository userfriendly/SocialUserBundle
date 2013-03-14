<?php

namespace Userfriendly\Bundle\SocialUserBundle\Model;

interface UserInterface
{
    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Set usernameSlug
     *
     * @param string $usernameSlug
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserInterface
     */
    public function setUsernameSlug( $usernameSlug );

    /**
     * Get usernameSlug
     *
     * @return string
     */
    public function getUsernameSlug();

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserInterface
     */
    public function setUpdatedAt( $updatedAt );

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserInterface
     */
    public function setCreatedAt( $createdAt );

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Add identities
     *
     * @param \Userfriendly\Bundle\SocialUserBundle\Model\UserIdentity $identity
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserInterface
     */
    public function addIdentity( UserIdentity $identity );

    /**
     * Remove identities
     *
     * @param \Userfriendly\Bundle\SocialUserBundle\Model\UserIdentity $identity
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserInterface
     */
    public function removeIdentity( UserIdentity $identity );

    /**
     * Get identities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIdentities();

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentitiesAsStrings();

    /**
     * Add emailChangeRequest
     *
     * @param \Userfriendly\Bundle\SocialUserBundle\Model\UserEmailChangeRequest $emailChangeRequest
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserInterface
     */
    public function addEmailChangeRequest( UserEmailChangeRequest $emailChangeRequest );

    /**
     * Remove emailChangeRequest
     *
     * @param \Userfriendly\Bundle\SocialUserBundle\Model\UserEmailChangeRequest $emailChangeRequest
     * @return \Userfriendly\Bundle\SocialUserBundle\Model\UserInterface
     */
    public function removeEmailChangeRequest( UserEmailChangeRequest $emailChangeRequest );

    /**
     * Get emailChangeRequest
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmailChangeRequests();
}
