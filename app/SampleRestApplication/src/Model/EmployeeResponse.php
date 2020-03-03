<?php

namespace App\Model;

use JMS\Serializer\Annotation\Type;

class EmployeeResponse
{

    /**
     * @Type("string")
     */
	public $uuid;

    /**
     * @Type("string")
     */
	public $name;

    /**
     * @Type("int")
     */
	public $age;

    /**
     * @Type("int")
     */
	public $salary;

}