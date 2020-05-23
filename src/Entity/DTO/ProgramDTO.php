<?php

declare(strict_types=1);

namespace App\Entity\DTO;

use App\Annotation as IS;

/**
 * Class ProgramDTO
 * Data transfer object for a Program
 *
 * @IS\DTO("programs")
 */
class ProgramDTO
{
    /**
     * @var int
     * @IS\Id
     * @IS\Expose
     * @IS\Type("integer")
     */
    public $id;

    /**
     * @var string
     * @IS\Expose
     * @IS\Type("string")
     *
     */
    public $title;

    /**
     * @var string
     * @IS\Expose
     * @IS\Type("string")
     *
     */
    public $shortTitle;

    /**
     * @var int
     * @IS\Expose
     * @IS\Type("string")
     *
     */
    public $duration;

    /**
     * @var bool
     * @IS\Expose
     * @IS\Type("boolean")
     * @deprecated
     */
    public $publishedAsTbd;

    /**
     * @var bool
     * @IS\Expose
     * @IS\Type("boolean")
     * @deprecated
     */
    public $published;

    /**
     * @var int
     * @IS\Expose
     * @IS\Type("string")
     *
     */
    public $school;

    /**
     * @var int[]
     * @IS\Expose
     * @IS\Related
     * @IS\Type("array<string>")
     *
     */
    public $programYears;

    /**
     * @var int[]
     * @IS\Expose
     * @IS\Related
     * @IS\Type("array<string>")
     *
     */
    public $curriculumInventoryReports;

    /**
     * @var int[]
     * @IS\Expose
     * @IS\Related
     * @IS\Type("array<string>")
     */
    public $directors;


    public function __construct(
        $id,
        $title,
        $shortTitle,
        $duration,
        $publishedAsTbd,
        $published
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->shortTitle = $shortTitle;
        $this->duration = $duration;
        $this->publishedAsTbd = $publishedAsTbd;
        $this->published = $published;

        $this->programYears = [];
        $this->curriculumInventoryReports = [];
        $this->directors = [];
    }
}
