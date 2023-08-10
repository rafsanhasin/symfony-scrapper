<?php


namespace App\Service;

use App\Entity\Company;
use App\Entity\CompanyTurnover;
use App\Repository\CompanyRepository;
use App\Repository\CompanyTurnoverRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;

class CompanyService
{
    protected $entityManager;
    protected $companyRepo;
    protected $companyTurnoverRepo;

    public function __construct(
        EntityManagerInterface $entityManager,
        CompanyRepository $companyRepo,
        CompanyTurnoverRepository $companyTurnoverRepo
    ) {
        $this->entityManager = $entityManager;
        $this->companyRepo = $companyRepo;
        $this->companyTurnoverRepo = $companyTurnoverRepo;
    }

    public function store($company, $turnoverData) : bool {
        try {
            $existingCompany = $this->companyRepo->findOneBy(['registration_code' => $company['registration_code']]);

            if (!$existingCompany) {
                $companyEntity = new Company();
                $companyEntity->setName($company['name']);
                $companyEntity->setRegistrationCode($company['registration_code']);
                $companyEntity->setVat($company['vat']);
                $companyEntity->setAddress($company['address']);
                $companyEntity->setMobilePhone($company['mobile_phone']);
                $this->entityManager->persist($companyEntity);
                $this->entityManager->flush();

                $this->storeTurnover($companyEntity->getId(), $turnoverData);
            } else {
                $existingTurnover = $this->companyTurnoverRepo->findOneBy(['company_id' => $existingCompany->getId()]);

                if (!$existingTurnover) {
                    $this->storeTurnover($existingCompany->getId(), $turnoverData);
                }
            }
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }

    private function storeTurnover($companyId, $data) {
        try {
            $companyTurnoverEntity = new CompanyTurnover();
            $companyTurnoverEntity->setCompanyId($companyId);
            $companyTurnoverEntity->setData($data);

            $this->entityManager->persist($companyTurnoverEntity);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }
}