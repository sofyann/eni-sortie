<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Location;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiController
 * @package App\Controller
 * @Route("/api")
 * @IsGranted("ROLE_USER")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/city/{name}", name="api_city")
     */
    public function cities($name)
    {
        $repo = $this->getDoctrine()->getRepository(City::class);
        $cities = $repo->findByName($name);
        if (sizeof($cities) === 0) {
            throw new NotFoundHttpException();
        }
        return new JsonResponse($cities);
    }

    /**
     * @Route("/locations/{id}", methods={"GET"}, name="api_location_by_cities")
     */
    public function locationsByCity(City $city) {
        if ($city === null) {
            throw new NotFoundHttpException();
        }
        $response = [];
        /**
         * @var $location Location
         */
        foreach ($city->getLocations()->toArray() as $location) {
            $temp = [];
            $temp['name'] = $location->getName();
            $temp['id'] = $location->getId();
            array_push($response, $temp);
        }
        return new JsonResponse(['locations' => $response]);
    }

    /**
     * @Route("/locations/add", methods={"POST"}, name="api_locations")
     */
    public function addLocation(Request $request)
    {
        $datas = $request->request->all();
        $repo = $repo = $this->getDoctrine()->getRepository(City::class);
        $city = $repo->find($datas['cityId']);
        $client = HttpClient::create();
        $query = 'https://api.mapbox.com/geocoding/v5/mapbox.places/'. $datas['street'] . ' ' .  $city->getName() . ' ' . $city->getPostalCode() . '.json?access_token=pk.eyJ1Ijoic29meWFubiIsImEiOiJjazI1aWtuYW4xM3lxM25tcXF3M2t0eHUxIn0.tV0FWZKFylfiVZxdNPxUOQ';
        $response = $client->request('GET', $query, [
            'proxy' => '10.0.0.248:8080',
            'verify_peer' => false
        ]);
        if ($response->getStatusCode() > 400) {
            throw new NotFoundHttpException();
        }
        $responseToArray = $response->toArray()['features'][0];
        $location = new Location();
        $location->setCity($city);
        $location->setName($datas['name']);
        $location->setLat($responseToArray['geometry']['coordinates'][0]);
        $location->setLng($responseToArray['geometry']['coordinates'][1]);
        $location->setStreet($responseToArray['address'] . ' ' . $responseToArray['text']);
        $em = $this->getDoctrine()->getManager();
        $em->persist($location);
        $em->flush();
        return new JsonResponse(['id' => $location->getId()]);
    }
}
