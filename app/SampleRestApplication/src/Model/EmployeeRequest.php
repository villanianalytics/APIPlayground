<?php

namespace App\Model;

use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;

class EmployeeRequest
{

    /**
     * @Type("string")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(max = 255)
     */
	public $name;

    /**
     * @Type("int")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
	public $age;

    /**
     * @Type("int")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
	public $salary;

}