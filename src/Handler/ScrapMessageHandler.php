<?php


namespace App\Handler;


use App\Messages\ScrapMessage;
use App\Service\ScrapperService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ScrapMessageHandler implements MessageHandlerInterface
{
    protected $logger;
    protected $scrapperSvc;

    public function __construct(LoggerInterface $logger, ScrapperService $scrapperSvc)
    {
        $this->logger = $logger;
        $this->scrapperSvc = $scrapperSvc;
    }

    public function __invoke(ScrapMessage $message)
    {
        $this->logger->info("scrap msg received :: ".$message->getContent());

        $registrationCodes = json_decode($message->getContent());

        foreach ($registrationCodes as $registrationCode) {
            if ($registrationCodes !== "") {
                $res = $this->scrapperSvc->scrap($registrationCode);
                $this->logger->info("scrap svc response for reg_code : " . $registrationCode . " :: " . json_encode($res));
            }
        }
    }
}