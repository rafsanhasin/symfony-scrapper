<?php

namespace App\Controller;

use App\Messages\ScrapMessage;
use App\Service\CompanyService;
use App\Service\ScrapperService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Panther\Client;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ScrapperController extends AbstractController
{
    #[Route('/', name: 'app_scrapper')]
    public function index(Request $request, ScrapperService $scrapperSvc, MessageBusInterface $bus): Response
    {
        if (!$request->query->has('registration_code')) {
            return $this->response(null, null, null, null);
        }

        $registrationCode = $request->query->get('registration_code');

        if (strlen($registrationCode) < 9) {
            return $this->response(null, null, 'Registration code must be 9 digits', null);
        }

        try {
            $registrationCodes = explode(',', $registrationCode);

            if (sizeof($registrationCodes) == 1) {
                $response = $scrapperSvc->scrap($registrationCode);

                return $this->response($response['table'], $response['company'], $response['error'], $response['success']);
            }

            $message = new ScrapMessage(json_encode($registrationCodes));
            $bus->dispatch($message);

            return $this->response(null, null, '', 'Request received, will be fetched in the background');
        } catch (\Exception $exception) {
            return $this->response(null, null, 'Invalid Input', null);
        }
    }

    private function response($table, $company, $error, $successMsg) : Response {
        return $this->render('scrapper/index.html.twig', [
            'table' => $table,
            'company' => $company,
            'error'   => $error,
            'success' => $successMsg,
        ]);
    }
}
