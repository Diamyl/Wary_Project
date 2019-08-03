<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Partenaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SystemWaryController extends AbstractController
{
    /**
     * @Route("/system/wary", name="system_wary")
     */
    public function index()
    {
        return $this->render('system_wary/index.html.twig', [
            'controller_name' => 'SystemWaryController',
        ]);
    }
       /**
     * @Route("/ajoutsystem", name="ajoutsystem", methods={"POST"})
     */
    public function ajoutsystem(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager)
    {
        $values = json_decode($request->getContent());
        if(isset($values->Email,$values->Password)) {
            $user = new User();
            $user->setEmail($values->Email);
            $user->setPrenom($values->Prenom);
            $user->setNom($values->Nom);
            $user->setCNI($values->CNI);
            $user->setTel($values->Tel);
            $user->setPassword($passwordEncoder->encodePassword($user, $values->Password));
        if ($values->Role==1) {
            $user->setRoles(['ROLE_SUPERADMIN']);
            $data = [
                'status' => 201,
                'message' => 'Le Super Admin a été créé',
            ];
        }
           elseif ($values->Role==2) {
            $user->setRoles(['ROLE_CAISSIER']);
            $data = [
                'status' => 201,
                'message' => 'Le Caissier a été créé',
            ];
           }

            $entityManager->persist($user);
            $entityManager->flush();

           

            return new JsonResponse($data, 201);
        }

            $data = [
            'status' => 500,
            'message' => 'Vous devez renseigner les clés username et password',
             ];
             return new JsonResponse($data, 500);
     }

    /**                                                                     
     * @Route("/ajoutpartenaire", name="ajoutpartenaire", methods={"POST"})
     */
    public function ajoutpartenaire(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager)
    {
        $values = json_decode($request->getContent());
        if(isset($values->Email,$values->Raison_Sociale,$values->NINEA,$values->Tel,$values->Adresse)) {
            $partenaire = new Partenaire();
            $partenaire->setEmail($values->Email);
            $partenaire->setRaisonSocial($values->Raison_Sociale);
            $partenaire->setNINEA($values->NINEA);
            $partenaire->setTel($values->Tel);
            $partenaire->setAdresse($values->Adresse);

            $adminpartenaire = new User();
            $adminpartenaire->setEmail($values->Email);
            $adminpartenaire->setPrenom($values->Prenom);
            $adminpartenaire->setNom($values->Nom);
            $adminpartenaire->setCNI($values->CNI);
            $adminpartenaire->setTel($values->Tel_Admin);
            $adminpartenaire->setPassword($passwordEncoder->encodePassword($adminpartenaire, $values->Password));
            $adminpartenaire->setRoles(['ROLE_ADMIN_PARTENAIRE']);
            
            // lier les deux tables
            $adminpartenaire->setPartenaire($partenaire);

            $entityManager->persist($partenaire);
            $entityManager->persist($adminpartenaire);
            $entityManager->flush();

            $data = [
                'status' => 201,
                'message' => 'Le Partenaire a été créé'
            ];
            return new JsonResponse($data, 201);
        }

            $data = [
            'status' => 500,
            'message' => 'Vous devez renseigner tout les champs',
             ];
             return new JsonResponse($data, 500);
     }
}
