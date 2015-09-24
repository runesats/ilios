<?php

namespace Ilios\CoreBundle\Entity\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Ilios\CoreBundle\Entity\CourseInterface;
use Ilios\CoreBundle\Entity\PermissionInterface;
use Ilios\CoreBundle\Entity\ProgramInterface;
use Ilios\CoreBundle\Entity\SchoolInterface;
use Ilios\CoreBundle\Entity\UserInterface;

/**
 * Interface PermissionManagerInterface
 * @package Ilios\CoreBundle\Entity\Manager
 */
interface PermissionManagerInterface extends ManagerInterface
{
    /**
     * @param array $criteria
     * @param array $orderBy
     *
     * @return PermissionInterface
     */
    public function findPermissionBy(
        array $criteria,
        array $orderBy = null
    );

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param integer $limit
     * @param integer $offset
     *
     * @return PermissionInterface[]
     */
    public function findPermissionsBy(
        array $criteria,
        array $orderBy = null,
        $limit = null,
        $offset = null
    );

    /**
     * @param PermissionInterface $permission
     * @param bool $andFlush
     * @param bool $forceId
     *
     * @return void
     */
    public function updatePermission(
        PermissionInterface $permission,
        $andFlush = true,
        $forceId = false
    );

    /**
     * @param PermissionInterface $permission
     *
     * @return void
     */
    public function deletePermission(
        PermissionInterface $permission
    );

    /**
     * @return PermissionInterface
     */
    public function createPermission();

    /**
     * Checks if a given user has "read" permissions for a given course.
     * @param UserInterface $user
     * @param CourseInterface $course
     * @return bool
     */
    public function userHasReadPermissionToCourse(UserInterface $user, CourseInterface $course);

    /**
     * Checks if a given user has "read" permissions for a given program.
     * @param UserInterface $user
     * @param ProgramInterface $program
     * @return bool
     */
    public function userHasReadPermissionToProgram(UserInterface $user, ProgramInterface $program);
    
    /**
     * Checks if a given user has "read" permissions for a given school.
     * @param UserInterface $user
     * @param SchoolInterface $school
     * @return bool
     */
    public function userHasReadPermissionToSchool(UserInterface $user, SchoolInterface $school);
    
    /**
     * Checks if a given user has "read" permissions for and in an array of schools.
     * @param UserInterface $user
     * @param ArrayCollection $schools
     * @return bool
     */
    public function userHasReadPermissionToSchools(UserInterface $user, ArrayCollection $schools);
     
    /**
    * Checks if a given user has "write" permissions for a list of schools
    * @param UserInterface $user
    * @param ArrayCollection $schools
    * @return bool
    */
    public function userHasWritePermissionToSchools(UserInterface $user, ArrayCollection $schools);

    /**
     * Checks if a given user has "write" permissions for a given course.
     * @param UserInterface $user
     * @param CourseInterface $course
     * @return bool
     */
    public function userHasWritePermissionToCourse(UserInterface $user, CourseInterface $course);

    /**
     * Checks if a given user has "write" permissions for a given program.
     * @param UserInterface $user
     * @param ProgramInterface $program
     * @return bool
     */
    public function userHasWritePermissionToProgram(UserInterface $user, ProgramInterface $program);

    /**
     * Checks if a given user has "write" permissions for a given school.
     * @param UserInterface $user
     * @param SchoolInterface $school
     * @return bool
     */
    public function userHasWritePermissionToSchool(UserInterface $user, SchoolInterface $school);
}
