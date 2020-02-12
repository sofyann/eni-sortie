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
     * @Route("/sorties/detail/{id}", name="trip_detail")
     */
    public function detail($id)
    {
        $tripRepo = $this->getDoctrine()->getRepository(Trip::class);
        $trip = $tripRepo->find($id);

        if (!$trip) {
            throw $this->createNotFoundException("Cette sortie n'existe pas !");
        }
        $state = $trip->getState();
        $condition = [
            'annuler' => false,
            'creer' => false
        ];
        if ($state->getWording() == 'Annulée') {
            $condition['annuler'] = true;
        }elseif($state->getWording() == 'Créée'){
            $condition['creer'] = true;
        }
        return $this->render('trip/detail.html.twig', [
            'trip' => $trip,
            'condition' => $condition
        ]);
    }

    /**
     * @Route("/sorties", name="trip_list")
     */
    public function list()
    {
        $tripRepo = $this->getDoctrine()->getRepository(Trip::class);

        $trips = $tripRepo->findListTrips();

        return $this->render('trip/list.html.twig', [
            "trips" => $trips
        ]);
    }

    /**
     * @Route("/sorties/supprimer/{id}", name="trip_delete")
     */
    public function delete(Trip $trip)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($trip);
        //on exécute
        $em->flush();

        $this->addFlash("success", "Suppression réussi");

        return $this->redirectToRoute('trip_list');
    }

    /**
     * @Route("/sorties/ajouter", name="trip_add")
     */
    public function add(Request $request)
    {
        $trip = new Trip();

        $form = $this->createForm(TripType::class, $trip);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($trip->getDateBeginning() < $trip->getRegistrationDeadline()) {
                $this->addFlash('danger', 'La date limite d\'inscription ne peut pas être après la date de sortie');
                return $this->render('trip/add.html.twig', [
                    "tripForm" => $form->createView()
                ]);
            }
            /** @var User $connectedUser */
            $connectedUser = $this->getUser();
            $trip->setOrganizer($connectedUser);

            $stateRepo = $this->getDoctrine()->getRepository(State::class);
            $state = $stateRepo->findOneBy(array('wording' => 'Créée'));
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


    /**
     * @Route("/sorties/modifier/{id}", name="trip_edit")
     */
    public function edit(Trip $trip, Request $request)
    {

        $form = $this->createForm(TripType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($trip);
            $em->flush();

            $this->addFlash('success', 'Sortie modifiée !');
            return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);
        }

        return $this->render('trip/edit.html.twig', [
            "tripForm" => $form->createView()
        ]);
    }

    /**
         * @Route("/sorties/ouvrir/{id}", name="trip_open")
     */
    public function open(Trip $trip, Request $request)
    {
        //TODO verifier que la personne a les droit pour annuler
        if($trip->getState()->getWording() !='Créée'){
            $this->addFlash('warning', 'La sortie ne peut pas être ouverte');
            return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);
        }
        $stateRepo = $this->getDoctrine()->getRepository(State::class);
        $state = $stateRepo->findOneBy(array('wording' => 'Ouverte'));

        $trip->setState($state);

        $em = $this->getDoctrine()->getManager();
        $em->persist($trip);
        $em->flush();

        $this->addFlash('success', 'Sortie Ouverte !');
        return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);


    }

    /**
         * @Route("/sorties/annuler/{id}", name="trip_cancel")
     */
    public function cancel(Trip $trip, Request $request)
    {
        //TODO verifier que la personne a les droit pour annuler
            $stateRepo = $this->getDoctrine()->getRepository(State::class);
            $state = $stateRepo->findOneBy(array('wording' => 'Annulée'));

            $trip->setState($state);

            $em = $this->getDoctrine()->getManager();
            $em->persist($trip);
            $em->flush();

            $this->addFlash('warning', 'Sortie annulé ... :(');
            return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);


    }
}