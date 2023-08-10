<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyTurnover;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use App\Repository\CompanyTurnoverRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/company')]
class CompanyController extends AbstractController
{
    protected $entityManager;
    protected $companyTurnoverRepo;

    public function __construct(EntityManagerInterface $entityManager, CompanyTurnoverRepository $companyTurnoverRepo)
    {
        $this->entityManager = $entityManager;
        $this->companyTurnoverRepo = $companyTurnoverRepo;
    }

    #[Route('/', name: 'app_company_index', methods: ['GET'])]
    public function index(Request $request, CompanyRepository $companyRepository, PaginatorInterface $paginator): Response
    {
        $filter = $request->query->get('filter');

        $query = $companyRepository->createQueryBuilder('c');

        if ($filter === 'name_asc') {
            $query = $query->orderBy('c.name', 'ASC');
        } elseif ($filter === 'name_desc') {
            $query = $query->orderBy('c.name', 'DESC');
        } elseif ($filter === 'turnover_asc') {
            $query = $query->orderBy('c.turnover', 'ASC');
        } elseif ($filter === 'turnover_desc') {
            $query = $query->orderBy('c.turnover', 'DESC');
        } else {
            $query = $query->orderBy('c.id', 'DESC');
        }

        $pagination  = $paginator->paginate(
            $query->getQuery(),
            $request->query->getInt('page', 1),
            10 // items per page
        );
        return $this->render('company/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_company_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ValidatorInterface $validator): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($company);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('company/new.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_company_show', methods: ['GET'])]
    public function show(Company $company): Response
    {
        $companyTurnover = $this->companyTurnoverRepo->findOneBy(['company_id' => $company->getId()]);

        return $this->render('company/show.html.twig', [
            'company' => $company,
            'table'   => $companyTurnover->getData() ?? null
        ]);
    }

    #[Route('/{id}/edit', name: 'app_company_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Company $company): Response
    {
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('company/edit.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_company_delete', methods: ['POST'])]
    public function delete(Request $request, Company $company): Response
    {
        if ($this->isCsrfTokenValid('delete'.$company->getId(), $request->request->get('_token'))) {
            $companyTurnover = $this->companyTurnoverRepo->findOneBy(['company_id' => $company->getId()]);
            $this->entityManager->remove($companyTurnover);
            $this->entityManager->flush();

            $this->entityManager->remove($company);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
    }
}
