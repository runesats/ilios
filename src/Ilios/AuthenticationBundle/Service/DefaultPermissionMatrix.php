<?php

namespace Ilios\AuthenticationBundle\Service;

use Ilios\AuthenticationBundle\Classes\Capabilities;
use Ilios\AuthenticationBundle\Classes\PermissionMatrix;
use Ilios\AuthenticationBundle\Classes\UserRoles;
use Ilios\CoreBundle\Entity\DTO\SchoolDTO;
use Ilios\CoreBundle\Entity\Manager\SchoolManager;

class DefaultPermissionMatrix extends PermissionMatrix
{
    /**
     * @var SchoolManager
     */
    protected $schoolManager;

    /**
     * @param SchoolManager $schoolManager
     */
    public function __construct(SchoolManager $schoolManager)
    {
        $this->schoolManager = $schoolManager;
        $schoolDtos = $this->schoolManager->findDTOsBy([]);

        /** @var SchoolDTO $schoolDto */
        foreach ($schoolDtos as $schoolDto) {
            $schoolId = $schoolDto->id;

            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_SCHOOLS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_PROGRAMS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                    UserRoles::PROGRAM_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_ALL_PROGRAMS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_THEIR_PROGRAMS,
                [
                    UserRoles::PROGRAM_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_ALL_PROGRAMS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_THEIR_PROGRAMS,
                [
                    UserRoles::PROGRAM_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_PROGRAM_YEARS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                    UserRoles::PROGRAM_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_ALL_PROGRAM_YEARS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                    UserRoles::PROGRAM_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_THEIR_PROGRAM_YEARS,
                [
                    UserRoles::PROGRAM_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_ALL_PROGRAM_YEARS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                    UserRoles::PROGRAM_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_THEIR_PROGRAM_YEARS,
                [
                    UserRoles::PROGRAM_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UNLOCK_ALL_PROGRAM_YEARS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                    UserRoles::PROGRAM_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UNLOCK_THEIR_PROGRAM_YEARS,
                [
                    UserRoles::PROGRAM_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UNARCHIVE_ALL_PROGRAM_YEARS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                    UserRoles::PROGRAM_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UNARCHIVE_THEIR_PROGRAM_YEARS,
                [
                    UserRoles::PROGRAM_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_COURSES,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_ALL_COURSES,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                    UserRoles::PROGRAM_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_THEIR_COURSES,
                [
                    UserRoles::COURSE_ADMINISTRATOR,
                    UserRoles::COURSE_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_ALL_COURSES,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UNLOCK_ALL_COURSES,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UNLOCK_THEIR_COURSES,
                [
                    UserRoles::COURSE_ADMINISTRATOR,
                    UserRoles::COURSE_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UNARCHIVE_ALL_COURSES,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_SESSIONS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                    UserRoles::PROGRAM_DIRECTOR,
                    UserRoles::COURSE_ADMINISTRATOR,
                    UserRoles::COURSE_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_ALL_SESSIONS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                    UserRoles::PROGRAM_DIRECTOR,
                    UserRoles::COURSE_ADMINISTRATOR,
                    UserRoles::COURSE_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_THEIR_SESSIONS,
                [
                    UserRoles::SESSION_ADMINISTRATOR,
                    UserRoles::SESSION_INSTRUCTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_ALL_SESSIONS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                    UserRoles::COURSE_ADMINISTRATOR,
                    UserRoles::COURSE_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_COMPETENCIES,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_COMPETENCIES,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_COMPETENCIES,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_SESSION_TYPES,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_SESSION_TYPES,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_SESSION_TYPES,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_VOCABULARIES,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_VOCABULARIES,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_VOCABULARIES,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_TERMS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_TERMS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_TERMS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_INSTRUCTOR_GROUPS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                    UserRoles::PROGRAM_DIRECTOR,
                    UserRoles::COURSE_ADMINISTRATOR,
                    UserRoles::COURSE_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_INSTRUCTOR_GROUPS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::COURSE_ADMINISTRATOR,
                    UserRoles::COURSE_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_INSTRUCTOR_GROUPS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_LEARNER_GROUPS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::COURSE_ADMINISTRATOR,
                    UserRoles::COURSE_DIRECTOR,
                    UserRoles::SESSION_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_LEARNER_GROUPS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::COURSE_ADMINISTRATOR,
                    UserRoles::COURSE_DIRECTOR,
                    UserRoles::SESSION_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_LEARNER_GROUPS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_CURRICULUM_INVENTORY_REPORTS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_ALL_CURRICULUM_INVENTORY_REPORTS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_THEIR_CURRICULUM_INVENTORY_REPORTS,
                [
                    UserRoles::CURRICULUM_INVENTORY_REPORT_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_ALL_CURRICULUM_INVENTORY_REPORTS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_SCHOOL_CONFIGS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_SCHOOL_CONFIGS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_SCHOOL_CONFIGS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_CURRICULUM_INVENTORY_INSTITUTIONS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_CURRICULUM_INVENTORY_INSTITUTIONS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_CURRICULUM_INVENTORY_INSTITUTIONS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_USERS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_USERS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_USERS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_CREATE_DEPARTMENTS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_UPDATE_DEPARTMENTS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
            $this->setPermission(
                $schoolId,
                Capabilities::CAN_DELETE_DEPARTMENTS,
                [
                    UserRoles::SCHOOL_ADMINISTRATOR,
                    UserRoles::SCHOOL_DIRECTOR,
                ]
            );
        }
    }
}
