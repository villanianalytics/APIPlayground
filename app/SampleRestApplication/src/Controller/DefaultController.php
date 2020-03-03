<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Reward;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Employee;
use App\Model\EmployeeResponse;
use App\Model\EmployeeRequest;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @Route("/employees")
 */
class DefaultController extends AbstractController
{

    private $token = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c';

    private $requestStack;
    private $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

        $this->request = $this->requestStack->getCurrentRequest();

        if ($this->request != null && $this->request->headers->has('Authorization')) {
            $usedToken = $this->request->headers->get('Authorization');

            if ($usedToken != $this->token && substr($usedToken, 0, 5) != 'Basic') {
                throw new AccessDeniedHttpException("Invalid token");
            }
        }
    }

   /**
     * @Get("")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns a list of employee responses",
     *     @Model(type=EmployeeResponse::class)
     * )
     * 
   	 * @param ParamFetcherInterface $paramFetcher
     */
    public function listAction(ParamFetcherInterface $paramFetcher)
    {
        $employees = $this->getDoctrine()
            ->getRepository(Employee::class)
            ->findAll();

        $records = [];

        foreach ($employees as $employee) {
            $employeeResponse = new EmployeeResponse();
            $employeeResponse->name =$employee->getName();
            $employeeResponse->age = $employee->getAge();
            $employeeResponse->salary = $employee->getSalary();
            $employeeResponse->uuid = $employee->getUuid();

            $records[] = $employeeResponse;
        }

    	$response = new Response(json_encode($records));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Get("/{uuid}")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns a specific employee record",
     *     @Model(type=EmployeeResponse::class)
     * )
     */
    public function detailAction(string $uuid)
    {
        $employee = $this->getDoctrine()
            ->getRepository(Employee::class)
            ->findOneByUuid($uuid);

        if ($employee === null) {
            throw new EntityNotFoundException(sprintf("No employee found with uuid %s", $uuid));
        }
        
        $employeeResponse = new EmployeeResponse();
        $employeeResponse->name =$employee->getName();
        $employeeResponse->age = $employee->getAge();
        $employeeResponse->salary = $employee->getSalary();
        $employeeResponse->uuid = $employee->getUuid();

        $response = new Response(json_encode($employeeResponse));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Post("")
     *
     * @SWG\Post(
     *    @SWG\Response(
     *      response=201,
     *        description="Create a new employee record", 
     *     ),
     *     @SWG\Parameter(name="body", in="body", @Model(type=EmployeeRequest::class))
     * )
     *
     * @ParamConverter(
     *     "employeeRequest",
     *     converter="fos_rest.request_body"
     * )
     */
    public function createAction(EmployeeRequest $employeeRequest, ConstraintViolationListInterface $validationErrors)
    {
        if (\count($validationErrors) > 0) {
             $errorMessages = [];

            /** @var ConstraintViolation $validationError */
            foreach ($validationErrors as $validationError) {
                $errorMessages[] = sprintf(
                    'Property: %s, Parameters: %s, Message: %s',
                    $validationError->getPropertyPath(),
                    implode(',', $validationError->getParameters()),
                    $validationError->getMessage()
                );
            }

            throw new \Exception(json_encode($errorMessages));
        }

        $message = [];
        $message['message'] = 'Created';
        $response = new Response(json_encode($message));

        try {
            $employeeEntity = new Employee();
            $employeeEntity->setName($employeeRequest->name);
            $employeeEntity->setAge($employeeRequest->age);
            $employeeEntity->setSalary($employeeRequest->salary);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($employeeEntity);
            $entityManager->flush();
            
            $response->setStatusCode(201);
            $response->headers->set('location', sprintf('employees/%s', $employeeEntity->getUuid()));
        } catch (\Exception $e) {
            throw new \Exception("Could not create for unexpected reason.");
        }

        return $response;
    }

    /**
     * @Put("/{uuid}")
     *
     * @SWG\Put(
     *    @SWG\Response(
     *      response=200,
     *        description="Update an employee record", 
     *     ),
     *     @SWG\Parameter(name="body", in="body", @Model(type=EmployeeRequest::class))
     * )
     *
     * @ParamConverter(
     *     "employeeRequest",
     *     converter="fos_rest.request_body"
     * )
     */
    public function updateAction(string $uuid, EmployeeRequest $employeeRequest, ConstraintViolationListInterface $validationErrors)
    {
        if (\count($validationErrors) > 0) {
             $errorMessages = [];

            /** @var ConstraintViolation $validationError */
            foreach ($validationErrors as $validationError) {
                $errorMessages[] = sprintf(
                    'Property: %s, Parameters: %s, Message: %s',
                    $validationError->getPropertyPath(),
                    implode(',', $validationError->getParameters()),
                    $validationError->getMessage()
                );
            }

            throw new \Exception(json_encode($errorMessages));
        }

    	$employee = $this->getDoctrine()
            ->getRepository(Employee::class)
            ->findOneByUuid($uuid);

        if ($employee === null) {
            throw new EntityNotFoundException(sprintf("No employee found with uuid %s", $uuid));
        }

        $message = [];
        $message['message'] = 'Updated';
        $response = new Response(json_encode($message));

        try {
            $employee->setName($employeeRequest->name);
            $employee->setAge($employeeRequest->age);
            $employee->setSalary($employeeRequest->salary);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            

            $response->setStatusCode(200);
        } catch (\Exception $e) {
            throw new \Exception("Could not update for unexpected reason.");
        }

        return $response;
    }


    /**
     * @Delete("/{uuid}")
     */
    public function deleteAction(string $uuid)
    {
    	$employee = $this->getDoctrine()
            ->getRepository(Employee::class)
            ->findOneByUuid($uuid);

        if ($employee === null) {
            throw new EntityNotFoundException(sprintf("No employee found with uuid %s", $uuid));
        }

        $message = [];
        $message['message'] = 'Deleted';
        $response = new Response(json_encode($message));

        try {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($employee);
            $entityManager->flush();
            
            $response->setStatusCode(200);
        } catch (\Exception $e) {
            throw new \Exception("Could not delete for unexpected reason.");
        }

        return $response;
    }

    /**
     * @Post("/{uuid}/images")
     *
     * @SWG\Post(
     *    @SWG\Response(
     *      response=201,
     *        description="Create a image for an employee", 
     *     )
     * )
     */
    public function createImageAction(string $uuid)
    {
        $employee = $this->getDoctrine()
            ->getRepository(Employee::class)
            ->findOneByUuid($uuid);

        if ($employee === null) {
            throw new EntityNotFoundException(sprintf("No employee found with uuid %s", $uuid));
        }

        $message = [];
        $message['message'] = 'Created';
        $response = new Response(json_encode($message));

        try {
            $file = $this->request->files->get('image');

            if ($file == null) {
                throw new \Exception("Could not save file for unexpected reason.");
            }

            if ($file->getMimeType() != 'image/png') {
                throw new \Exception("Could not save file for unexpected reason.");
            }

            $myfile = fopen($file->getPathName(), "r") or die("Unable to open file!");
            
            $data = file_get_contents($file->getPathName());

            $employee->setImage(base64_encode($data));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            fclose($myfile);
        } catch (\Exception $e) {
            throw new \Exception("Could not save file for unexpected reason.");
        }

       
        return $response;
    }

    /**
     * @Get("/{uuid}/images")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the image of an employee"
     * )
     */
    public function imageAction(string $uuid)
    {
        $employee = $this->getDoctrine()
            ->getRepository(Employee::class)
            ->findOneByUuid($uuid);

        if ($employee === null) {
            throw new EntityNotFoundException(sprintf("No employee found with uuid %s", $uuid));
        }

        if ($employee->getImage() === null) {
            throw new EntityNotFoundException(sprintf("Employee %s does not have an image yet", $uuid));
        }

        $message = [];
        
        try {
            $message['image'] = $employee->getImage();
        } catch (\Exception $e) {
            throw new \Exception("Could not returnb file for unexpected reason.");
        }

        $response = new Response(json_encode($message));

        return $response;
    }


}