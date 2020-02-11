<?php


namespace App\Controller;


use App\Entity\Location;
use App\Entity\State;
use App\Entity\User;
use App\Entity\Trip;
use App\Form\TripType;
use App\Security\Voter\TripVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

class TripController extends AbstractController
{
    /**
     * @Route("/sorties", name="trip_list")
     */
    public function list()
    {
        //récupére le repository de sortie
        //le repository permet de faire des SELECT
        $tripRepo = $this->getDoctrine()->getRepository(Trip::class);

        //demande à doctrine de nous retourner toutes les sorties
        //$trips = $tripRepo->findAll();
        $trips = $tripRepo->findListTrips();

        return $this->render('trip/list.html.twig', [
            "trips" => $trips
        ]);
    }

    /**
     * @Route("/ajouter", name="trip_add")
     */
    public function add(Request $request)
    {
        $trip = new Trip();

        $form = $this->createForm(TripType::class, $trip);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($trip->getDateBeginning() < $trip->getRegistrationDeadline()){
                $this->addFlash('danger', 'La date limite d\'inscription ne peut pas être après la date de sortie');
                return $this->render('trip/add.html.twig', [
                    "tripForm" => $form->createView()
                ]);
            }
            /** @var User $connectedUser */
            $connectedUser = $this->getUser();
            $trip->setOrganizer( $connectedUser );

            $stateRepo = $this->getDoctrine()->getRepository(State::class);
            $state = $stateRepo->findOneBy(array('id'=>1));
            $trip->setState($state);


            $em = $this->getDoctrine()->getManager();
            $em->persist($trip);
            $em->flush();

            $this->addFlash('success', 'Sortie ajoutée !');
            return $this->redirectToRoute('trip_list');
        }

        return $this->render('trip/add.html.twig', [
            "tripForm" => $form->createView()
        ]);
    }


}