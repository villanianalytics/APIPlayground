<?php
namespace App\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use App\Entity\Employee;

class EmployeeListResolver implements ResolverInterface, AliasedInterface {

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function resolve(Argument $args)
    {
        $entities = $this->em->getRepository('App:Employee')->findAll();

        return ['employees' => $entities];
    }

    public static function getAliases(): array
    {
        return [
            'resolve' => 'EmployeeListResolver'
        ];
    }
}