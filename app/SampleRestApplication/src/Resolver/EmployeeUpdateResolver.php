<?php
namespace App\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use App\Entity\Employee;

class EmployeeUpdateResolver implements ResolverInterface, AliasedInterface {

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function resolve(Argument $args)
    {
        try {
            $rawArgs = $args->getRawArguments();

            $uuid = $args['uuid'];

            $employeeEntity = $this->em->getRepository('App:Employee')->findOneByUuid($args['uuid']);

            $input = [];
            foreach($rawArgs['input'] as $key => $value){
                $input[$key] = $value;
            }

            $employeeEntity->setName($input['name']);
            $employeeEntity->setAge($input['age']);
            $employeeEntity->setSalary($input['salary']);

            $this->em->persist($employeeEntity);
            $this->em->flush();
        } catch (\Exception $e) {
            return null;
        }

        return $employeeEntity;
    }

    public function createEmployee(Argument $args) {
        return ['name' => 'create'];
    }

    public static function getAliases(): array
    {
        return [
            'resolve' => 'EmployeeUpdateResolver'
        ];
    }
}