<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Annotation as IS;
use Symfony\Component\Validator\Constraints as Assert;
use App\Traits\NameableEntity;
use App\Traits\IdentifiableEntity;
use App\Traits\StringableIdEntity;
use App\Traits\SchoolEntity;
use App\Repository\CurriculumInventoryInstitutionRepository;

/**
 * Class CurriculumInventoryInstitution
 * @IS\Entity
 */
#[ORM\Table(name: 'curriculum_inventory_institution')]
#[ORM\Entity(repositoryClass: CurriculumInventoryInstitutionRepository::class)]
class CurriculumInventoryInstitution implements CurriculumInventoryInstitutionInterface
{
    use NameableEntity;
    use IdentifiableEntity;
    use StringableIdEntity;
    use SchoolEntity;

    /**
     * @var int
     * @Assert\Type(type="integer")
     * @IS\Expose
     * @IS\Type("integer")
     * @IS\ReadOnly
     */
    #[ORM\Column(name: 'institution_id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 100
     * )
     * @IS\Expose
     * @IS\Type("string")
     */
    #[ORM\Column(type: 'string', length: 100)]
    protected $name;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 10
     * )
     * @IS\Expose
     * @IS\Type("string")
     */
    #[ORM\Column(name: 'aamc_code', type: 'string', length: 10)]
    protected $aamcCode;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 100
     * )
     * @IS\Expose
     * @IS\Type("string")
     */
    #[ORM\Column(name: 'address_street', type: 'string', length: 100)]
    protected $addressStreet;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 100
     * )
     * @IS\Expose
     * @IS\Type("string")
     */
    #[ORM\Column(name: 'address_city', type: 'string', length: 100)]
    protected $addressCity;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 50
     * )
     * @IS\Expose
     * @IS\Type("string")
     */
    #[ORM\Column(name: 'address_state_or_province', type: 'string', length: 50)]
    protected $addressStateOrProvince;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 10
     * )
     * @IS\Expose
     * @IS\Type("string")
     */
    #[ORM\Column(name: 'address_zipcode', type: 'string', length: 10)]
    protected $addressZipCode;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 2
     * )
     * @IS\Expose
     * @IS\Type("string")
     */
    #[ORM\Column(name: 'address_country_code', type: 'string', length: 2)]
    protected $addressCountryCode;

    /**
     * @var SchoolInterface
     * @Assert\NotNull()
     * @IS\Expose
     * @IS\Type("entity")
     */
    #[ORM\OneToOne(inversedBy: 'curriculumInventoryInstitution', targetEntity: 'School')]
    #[ORM\JoinColumn(name: 'school_id', referencedColumnName: 'school_id', unique: true, nullable: false)]
    protected $school;

    /**
     * @param string $aamcCode
     */
    public function setAamcCode($aamcCode)
    {
        $this->aamcCode = $aamcCode;
    }

    /**
     * @return string
     */
    public function getAamcCode()
    {
        return $this->aamcCode;
    }

    /**
     * @param string $addressStreet
     */
    public function setAddressStreet($addressStreet)
    {
        $this->addressStreet = $addressStreet;
    }

    /**
     * @return string
     */
    public function getAddressStreet()
    {
        return $this->addressStreet;
    }

    /**
     * @param string $addressCity
     */
    public function setAddressCity($addressCity)
    {
        $this->addressCity = $addressCity;
    }

    /**
     * @return string
     */
    public function getAddressCity()
    {
        return $this->addressCity;
    }

    /**
     * @param string $addressStateOrProvince
     */
    public function setAddressStateOrProvince($addressStateOrProvince)
    {
        $this->addressStateOrProvince = $addressStateOrProvince;
    }

    /**
     * @return string
     */
    public function getAddressStateOrProvince()
    {
        return $this->addressStateOrProvince;
    }

    /**
     * @param string $addressZipCode
     */
    public function setAddressZipCode($addressZipCode)
    {
        $this->addressZipCode = $addressZipCode;
    }

    /**
     * @return string
     */
    public function getAddressZipCode()
    {
        return $this->addressZipCode;
    }

    /**
     * @param string $addressCountryCode
     */
    public function setAddressCountryCode($addressCountryCode)
    {
        $this->addressCountryCode = $addressCountryCode;
    }

    /**
     * @return string
     */
    public function getAddressCountryCode()
    {
        return $this->addressCountryCode;
    }
}
