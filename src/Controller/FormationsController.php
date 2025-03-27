<?php
namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Formation;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Playlist;

/**
 * Controleur des formations
 *
 * @author emds
 */

class FormationsController extends AbstractController
{

    private const VIEW_FORMATIONS = "pages/formations.html.twig";
    private const VIEW_FORMATION = "pages/formation_form.html.twig";

            
    /**
     *
     * @var FormationRepository
     */
    private $formationRepository;
    
    /**
     *
     * @var CategorieRepository
     */
    private $categorieRepository;
    
    public function __construct(FormationRepository $formationRepository, CategorieRepository $categorieRepository)
    {
        $this->formationRepository = $formationRepository;
        $this->categorieRepository= $categorieRepository;
    }
    
    #[Route('/formations', name: 'formations')]
    public function index(): Response
    {
        $formations = $this->formationRepository->findAll();
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::VIEW_FORMATIONS, [
            'formations' => $formations,
            'categories' => $categories
        ]);
    }

    #[Route('/formations/tri/{champ}/{ordre}/{table}', name: 'formations.sort')]
    public function sort($champ, $ordre, $table=""): Response
    {
        $formations = $this->formationRepository->findAllOrderBy($champ, $ordre, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::VIEW_FORMATIONS, [
            'formations' => $formations,
            'categories' => $categories
        ]);
    }

    #[Route('/formations/recherche/{champ}/{table}', name: 'formations.findallcontain')]
    public function findAllContain($champ, Request $request, $table=""): Response
    {
        $valeur = $request->get("recherche");
        $formations = $this->formationRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::VIEW_FORMATIONS, [
            'formations' => $formations,
            'categories' => $categories,
            'valeur' => $valeur,
            'table' => $table
        ]);
    }

    #[Route('/formations/formation/{id}', name: 'formations.showone')]
    public function showOne($id): Response
    {
        $formation = $this->formationRepository->find($id);
        return $this->render("pages/formation.html.twig", [
            'formation' => $formation
        ]);
    }
    
      
    #[Route('/formations/ajouter/', name: 'formations.add' )]
    public function add (Request $request) : Response
    {
        $formation = new Formation();
        $form = $this->createFormBuilder($formation)
                     ->add('title', TextType::class)
                     ->add('description', TextareaType::class)
                     ->add('videoId', TextType::class)
                     ->add('playlist', EntityType::class, [
    'class' => Playlist::class,
    'choice_label' => 'id', // Ou 'name' si la playlist a un champ `name`
    'placeholder' => 'Sélectionner une playlist',
    'attr' => ['class' => 'form-control']
])
                     ->add('publishedAt', DateType::class, ['widget' => 'single_text'])
                     ->add('save', SubmitType::class, ['label' => 'Ajouter une formation'])
                     ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formation = $form->getData();
            $this->formationRepository->add($formation);
            return $this->redirectToRoute('formations');
        }
 
        return $this->render('pages/formation_form.html.twig', [
            'form' => $form->createView(),
        ]);
               
                
    }
    // Edit Formation
    #[Route('/formations/editer/{id}', name: 'formations.edit')]
        public function edit(Request $request, $id): Response {
    $formation = $this->formationRepository->find($id);
    if (!$formation) {
        throw $this->createNotFoundException('Formation not found');
    }
 
    $form = $this->createFormBuilder($formation)
        ->add('title', TextType::class)
        ->add('description', TextareaType::class)
        ->add('videoId', TextType::class)
        ->add('playlist', EntityType::class, [
    'class' => Playlist::class,
    'choice_label' => 'id', // Ou 'name' si la playlist a un champ `name`
    'placeholder' => 'Sélectionner une playlist',
    'attr' => ['class' => 'form-control']
])
        ->add('publishedAt', DateType::class, ['widget' => 'single_text'])
        ->add('save', SubmitType::class, ['label' => 'Edit Formation'])
        ->getForm();
 
    $form->handleRequest($request);
 
    if ($form->isSubmitted() && $form->isValid()) {
        $formation = $form->getData();
        $this->formationRepository->add($formation); // Mise à jour
        return $this->redirectToRoute('formations');
    }
 
    return $this->render('pages/formation_form.html.twig', [
        'form' => $form->createView(),
    ]);
    }
    
    
}
