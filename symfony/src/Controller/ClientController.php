<?php

namespace App\Controller;

use App\Abstractions\OperationsProcessing\OperationHandlersFactoryInterface;
use App\Helpers\JmsJsonTrait;
use App\Model\Api\FetchDuplicatesApiRequest;
use App\Model\Api\GenerateDbApiRequest;
use App\Model\Operations\Command\FetchDuplicatesPhpOperationCommand;
use App\Model\Operations\Command\FetchDuplicatesSqlOperationCommand;
use App\Model\Operations\Command\TestOperationCommand;
use App\Model\Operations\Command\GenerateDbOperationCommand;
use App\Operations\Common\IdentityRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Psr\Log\LoggerInterface;

class ClientController extends Controller
{
    use JmsJsonTrait;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var OperationHandlersFactoryInterface
     */
    private $handlersFactory;

    public function __construct(
        LoggerInterface $logger,
        OperationHandlersFactoryInterface $handlersFactory
    )
    {
        $this->logger = $logger;
        $this->handlersFactory = $handlersFactory;
    }

    public function index()
    {
        return phpinfo();
    }

    /**
     * @Route("/test", name="test", methods={"POST"})
     * @param GenerateDbApiRequest $request
     * @return Response
     */
    public function test(GenerateDbApiRequest $request){
        $sampleValue = $request->getSampleValue();

        /*
        $handler = $this->handlersFactory->get(IdentityRegistry::getRegistry()->getUpdateStatistics());
        $command = new UpdateStatisticsOperationCommand(333, 111);
        */

        $handler = $this->handlersFactory->get(IdentityRegistry::getRegistry()->getTest());
        $command = new TestOperationCommand($sampleValue);

        $result = $handler->handle($command);

        return $this->jmsJson($result);
    }

    /**
     * @Route("/generateDb", name="generateDb", methods={"POST"})
     * @param GenerateDbApiRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function generateDb(GenerateDbApiRequest $request){
        $handler = $this->handlersFactory->get(IdentityRegistry::getRegistry()->getGenerateDb());
        $command = new GenerateDbOperationCommand($request->getTotalCount(), $request->getDuplicatesCount());

        $result = $handler->handle($command);

        return $this->jmsJson($result);
    }

    /**
     * @Route("/fetchDuplicatesSql", name="fetchDuplicatesSql", methods={"POST"})
     * @param FetchDuplicatesApiRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function fetchDuplicatesSql(FetchDuplicatesApiRequest $request){
        $handler = $this->handlersFactory->get(IdentityRegistry::getRegistry()->getFetchDuplicatesSql());
        $command = new FetchDuplicatesSqlOperationCommand($request->getMatchThreshold());

        $result = $handler->handle($command);

        return $this->jmsJson($result);
    }

    /**
     * @Route("/fetchDuplicatesPhp", name="fetchDuplicatesPhp", methods={"POST"})
     * @param FetchDuplicatesApiRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function fetchDuplicatesPhp(FetchDuplicatesApiRequest $request){
        $handler = $this->handlersFactory->get(IdentityRegistry::getRegistry()->getFetchDuplicatesPhp());
        $command = new FetchDuplicatesPhpOperationCommand($request->getMatchThreshold());

        $result = $handler->handle($command);

        return $this->jmsJson($result);
    }
}
