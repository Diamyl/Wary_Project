<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Depot;
use App\Entity\Compte;
use App\Entity\Partenaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @Route("/api/ajoutsystem", name="ajoutsystem", methods={"POST"})
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
            'message' => 'Vous devez renseigner Tous les champs',
             ];
             return new JsonResponse($data, 500);
     }

    /**                                                                     
     * @Route("/api/ajoutpartenaire", name="ajoutpartenaire", methods={"POST"})
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
            $partenaire->setStatus($values->Status);

            $adminpartenaire = new User();
            $adminpartenaire->setEmail($values->Email);
            $adminpartenaire->setPrenom($values->Prenom);
            $adminpartenaire->setNom($values->Nom);
            $adminpartenaire->setCNI($values->CNI);
            $adminpartenaire->setTel($values->Tel_Admin);
            $adminpartenaire->setPassword($passwordEncoder->encodePassword($adminpartenaire, $values->Password));
            $adminpartenaire->setRoles(['ROLE_ADMIN_PARTENAIRE']);

            $ncompte = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $compte = new Compte();
            $compte->setNumero($ncompte);
            $compte->setSolde(0);

            
            // lier les tables
            $adminpartenaire->setPartenaire($partenaire);
            $compte->setPartenaire($partenaire);


            $entityManager->persist($partenaire);
            $entityManager->persist($adminpartenaire);
            $entityManager->persist($compte);
            $entityManager->flush();

            $data = [
                'status' => 201,
                'message' => 'Le Partenaire a été créé'
            ];
            return new JsonResponse($data, 201);
        }

            $data = [
            'status' => 500,
            'message' => 'Vous devez renseigner tous les champs',
             ];
             return new JsonResponse($data, 500);
     }
     
       /**
     * @Route("/api/adduserpartenaire", name="adduserpartenaire", methods={"POST"})
     */
    public function adduserpartenaire(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager)
    {
        $values = json_decode($request->getContent());
        if(isset($values->Email,$values->Password, $values->Prenom, $values->Nom, $values->CNI, $values->Tel)) {
            $user = new User();
            $user->setEmail($values->Email);
            $user->setPrenom($values->Prenom);
            $user->setNom($values->Nom);
            $user->setCNI($values->CNI);
            $user->setTel($values->Tel);
            $user->setPassword($passwordEncoder->encodePassword($user, $values->Password));
            $Idpartenaire=$this->getUser()->getPartenaire();
            $partenaire=$this->getDoctrine()->getRepository(Partenaire::class)->find($Idpartenaire);
            $user->setPartenaire($partenaire);
            $user->setRoles(['ROLE_USER_PARTENAIRE']);


            $data = [
                'status' => 201,
                'message' => 'Le User Partenaire a été créé',
            ];
        
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse($data, 201);
        }

            $data = [
            'status' => 500,
            'message' => 'Vous devez renseigner Tous les champs',
             ];
             return new JsonResponse($data, 500);
     }

      /**
     * @Route("/api/adddepot", name="adddepot", methods={"POST"})
     */
    public function adddepot(Request $request, EntityManagerInterface $entityManager)
    {
        $values = json_decode($request->getContent());
        
        if(isset($values->Montant,$values->Idcompte)){
            if($values->Montant >= 75000){
                $depot = new Depot();
                $depot->setMontant($values->Montant);
                $depot->setDateDepot(new \DateTime());
                $compte=$this->getDoctrine()->getRepository(Compte::class)->find($values->Idcompte);
                $depot->setCompte($compte);
                $solde=$compte->getSolde();
                $compte->setSolde($solde+ $values->Montant);


                $data = [
                    'status' => 201,
                    'message' => 'Le dépôt a été créé effectué',
                ];
                
                $entityManager->persist($depot);
                $entityManager->flush();

                return new JsonResponse($data, 201);
            }

            else{
                $data = [
                    'status' => 500,
                    'message' => 'Le montant doit être supérieur ou égal à 75000',
                ];
                return new JsonResponse($data, 500);
            }
        }

            $data = [
                'status' => 501,
                'message' => 'Tous les champs doivent être remplis',
            ];
            return new JsonResponse($data, 501);
    }
}
