<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\IndexableCoursesEntityInterface;

interface CourseLearningMaterialInterface extends
    LearningMaterialRelationshipInterface,
    IndexableCoursesEntityInterface
{
    public function setCourse(CourseInterface $course);
    public function getCourse(): CourseInterface;
}
