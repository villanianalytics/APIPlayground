<?php
namespace App\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use App\Entity\Employee;

class EmployeeDeleteResolver implements ResolverInterface, AliasedInterface {

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

            $this->em->remove($employeeEntity);
            $this->em->flush();
        } catch (\Exception $e) {
            return null;
        }

        return $employeeEntity;
    }

    public static function getAliases(): array
    {
        return [
            'resolve' => 'EmployeeDeleteResolver'
        ];
    }
}