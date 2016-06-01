<?php

namespace Ilios\AuthenticationBundle\Voter;

use Ilios\CoreBundle\Entity\CurriculumInventoryReportInterface;
use Ilios\CoreBundle\Entity\Manager\PermissionManager;
use Ilios\CoreBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class CurriculumInventoryReportVoter
 * @package Ilios\AuthenticationBundle\Voter
 */
class CurriculumInventoryReportVoter extends AbstractVoter
{
    /**
     * @var PermissionManager
     */
    protected $permissionManager;

    /**
     * @param PermissionManager $permissionManager
     */
    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof CurriculumInventoryReportInterface && in_array($attribute, array(
            self::VIEW, self::CREATE, self::EDIT, self::DELETE
        ));
    }

    /**
     * @param string $attribute
     * @param CurriculumInventoryReportInterface $report
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $report, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->isViewGranted($report, $user);
                break;
            case self::CREATE:
                return $this->isCreateGranted($report, $user);
                break;
            case self::EDIT:
                return $this->isEditGranted($report, $user);
                break;
            case self::DELETE:
                return $this->isDeleteGranted($report, $user);
        }

        return false;
    }

    /**
     * @param CurriculumInventoryReportInterface $report
     * @param UserInterface $user
     * @return bool
     */
    protected function isViewGranted($report, $user)
    {
        // Only grant VIEW permissions to users with at least one of
        // 'Course Director' and 'Developer' roles.
        // - and -
        // the user must be associated with the school owning the report's program
        // either by its primary school attribute
        //     - or - by READ rights for the school
        // via the permissions system.
        return (
            $this->userHasRole($user, ['Course Director', 'Developer'])
            && (
                $this->schoolsAreIdentical($user->getSchool(), $report->getSchool())
                || $this->permissionManager->userHasReadPermissionToSchool(
                    $user,
                    $report->getSchool()->getId()
                )
            )
        );
    }

    /**
     * @param CurriculumInventoryReportInterface $report
     * @param UserInterface $user
     * @return bool
     */
    protected function isEditGranted($report, $user)
    {
        // HALT!
        // Reports cannot be edited once they have been exported.
        if ($report->getExport()) {
            return false;
        }
        return $this->isCreateGranted($report, $user);
    }

    /**
     * @param CurriculumInventoryReportInterface $report
     * @param UserInterface $user
     * @return bool
     */
    protected function isCreateGranted($report, $user)
    {
        // Only grant CREATE, permissions to users with at least one of
        // 'Course Director' and 'Developer' roles.
        // - and -
        // the user must be associated with the school owning the report's program
        // either by its primary school attribute
        //     - or - by WRITE rights for the school
        // via the permissions system.
        return (
            $this->userHasRole($user, ['Course Director', 'Developer'])
            && (
                $this->schoolsAreIdentical($user->getSchool(), $report->getSchool())
                || $this->permissionManager->userHasWritePermissionToSchool(
                    $user,
                    $report->getSchool()->getId()
                ))
        );
    }

    /**
     * @param CurriculumInventoryReportInterface $report
     * @param UserInterface $user
     * @return bool
     */
    protected function isDeleteGranted($report, $user)
    {
        return $this->isEditGranted($report, $user);
    }
}
