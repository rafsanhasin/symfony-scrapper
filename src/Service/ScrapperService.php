<?php


namespace App\Service;


use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Panther\Client;

class ScrapperService
{
    protected $kernel;
    protected $companyService;

    /**
     * ScrapperService constructor.
     * @param CompanyService $companyService
     */
    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function scrap($registrationCode) : array {
        try{
            //$client = Client::createChromeClient( $this->kernel->getProjectDir().'/chrome_binary/chromedriver');
            $client = Client::createChromeClient(
                '/usr/local/bin/chromedriver',
                [
                    '--remote-debugging-port=9222',
                    '--no-sandbox',
                    '--disable-dev-shm-usage',
                    '--headless'
                ]
            );
            $client->get('https://rekvizitai.vz.lt/en/company-search/');

            $client->executeScript("document.querySelector('input[name=\"code\"]').value ='".$registrationCode."';");

            $client->executeScript("document.querySelector('form').submit();");

            $crawler = $client->waitFor('.company-title');

            $href = $crawler->filter('.company-title')->attr('href');

            $client->get($href);

            $crawler = $client->waitFor('#rekvizitai-app');

            $company = array();

            $company['name'] = $title = $crawler->filter('h1.title')->text();
            $company['registration_code'] = $crawler->filterXPath('//td[@class="name" and contains(text(), "Registration code")]/following-sibling::td[@class="value"]')->text();
            $company['vat'] = $crawler->filterXPath('//td[@class="name" and contains(text(), "VAT")]/following-sibling::td[@class="value"]')->text();
            $company['address'] = $crawler->filterXPath('//td[@class="name" and contains(text(), "Address")]/following-sibling::td[@class="value"]')->text();
            $company['mobile_phone'] = $crawler->filterXPath('//td[@class="name" and contains(text(), "Phone")]/following-sibling::td[@class="value"]')->text();

            $financialTab = $crawler->filter('a.nav-link[title="Company financial data"]')->attr('href');
            $client->get($financialTab);

            $client->executeScript("
            document.querySelectorAll('.finances-table td').forEach(function(td) {
                td.removeAttribute('style');
            });"
            );

            $client->executeScript("
            document.querySelectorAll('.finances-table th').forEach(function(th) {
                th.removeAttribute('style');
            });"
            );

            $crawler = $client->waitFor('.finances-table');

            $table = $crawler->filter('.finances-table')->filter('tr')->each(function ($tr, $i) {
                return $tr->filter('th, td')->each(function ($td, $i) {
                    return trim($td->text());
                });
            });

            $this->companyService->store($company, $table);
            return ['table' => $table, 'company' => $company, 'error' => '', 'success' => 'Successfully Fetched'];
        } catch (\Exception $exception) {
            return ['table' => null, 'company' => null, 'error' => 'Internal Server Error', 'success' => ''];
        }
    }
}