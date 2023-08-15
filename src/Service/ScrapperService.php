<?php


namespace App\Service;


use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ScrapperService
 * @package App\Service
 */
class ScrapperService
{
    private $params;
    protected $companyService;

    /**
     * ScrapperService constructor.
     * @param CompanyService $companyService
     * @param ContainerBagInterface $params
     */
    public function __construct(
        CompanyService $companyService,
        ContainerBagInterface $params
    ) {
        $this->companyService = $companyService;
        $this->params = $params;
    }

    /**
     * @param $registrationCode
     * @return array
     */
    public function scrap($registrationCode) : array {
        $scrapperApiUrl = $this->params->get('scrapper_api_url');
        $scrapperApiToken = $this->params->get('scrapper_api_token');
        $scrapeUrl = $this->params->get('scape_url');

        try{
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);

            $array = [
                ["Action"=> "Wait", "WaitSelector"=> "#code", "Timeout"=> 5000],
                ["Action"=> "Execute", "Execute"=> "document.querySelector('input[name=\"code\"]').value ='".$registrationCode."';"],
                ["Action"=> "Execute", "Execute"=> "document.querySelector('form').submit();"],
                ["Action"=> "Wait", "WaitSelector"=> ".company-title", "Timeout"=> 5000]
            ];

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_URL, $scrapperApiUrl."?render=true&playWithBrowser=".urlencode(json_encode($array))."&token=".$scrapperApiToken."&url=".urlencode($scrapeUrl) );
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Accept: */*",
            ));

            $searchResponse = curl_exec($curl);
            curl_close($curl);

            $searchCrawler = new Crawler($searchResponse);

            $companyLinkUrl = $searchCrawler->filter('.company-title')->attr('href');

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            $data = [
                "url" => $companyLinkUrl,
                "token" => $scrapperApiToken,
            ];
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_URL, $scrapperApiUrl."?".http_build_query($data));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Accept: */*",
            ));
            $companyPage = curl_exec($curl);
            curl_close($curl);

            $crawler = new Crawler($companyPage);

            $company = array();
            $company['name'] = $title = $crawler->filter('h1.title')->text();
            $company['registration_code'] = $crawler->filterXPath('//td[@class="name" and contains(text(), "Registration code")]/following-sibling::td[@class="value"]')->text();
            $company['vat'] = $crawler->filterXPath('//td[@class="name" and contains(text(), "VAT")]/following-sibling::td[@class="value"]')->text();
            $company['address'] = $crawler->filterXPath('//td[@class="name" and contains(text(), "Address")]/following-sibling::td[@class="value"]')->text();
            $company['mobile_phone'] = $crawler->filterXPath('//td[@class="name" and contains(text(), "Phone")]/following-sibling::td[@class="value"]')->text();
            $financialTabLink = $crawler->filter('a.nav-link[title="Company financial data"]')->attr('href');

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);

            $array = [
                ["Action"=> "Wait", "WaitSelector"=> ".finances-table", "Timeout"=> 5000],
                ["Action"=> "Execute", "Execute"=>
                    "document.querySelectorAll('.finances-table td').forEach(function(td) {
                        td.removeAttribute('style');});"
                ],
                ["Action"=> "Execute", "Execute"=>
                    "document.querySelectorAll('.finances-table th').forEach(function(th) {
                    th.removeAttribute('style');});"
                ],
            ];

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_URL, $scrapperApiUrl."?render=true&playWithBrowser=".urlencode(json_encode($array))."&token=".$scrapperApiToken."&url=".urlencode($financialTabLink) );
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Accept: */*",
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            $crawler = new Crawler($response);

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