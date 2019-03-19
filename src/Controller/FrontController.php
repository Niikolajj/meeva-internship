<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Bestellung;
use App\Entity\Benutzer;

class FrontController extends AbstractController
{



    /**
     * @Route("/order", name="order")
     */
    public function order(Request $request)
    {
        $exit_code = "nothing done";
        $orderNr = $request->request->get('id');
        $user = $request-> request->get('user');
        if($request->isXmlHttpRequest())
        {
            $order = abs($orderNr);
            $eM = $this->getDoctrine()->getManager();
            $order = $eM->getRepository(Bestellung::class)->findBy(array("id"=>$order));
            $zusagen = $order[0]->getZusagen();
            $zusagen = explode(",", $zusagen);

            if (($key = array_search($user, $zusagen)) !== false) {
                unset($zusagen[$key]);
                $exit_code = "removed";
            }
            else{
                $zusagen[] = $user;
                $exit_code = "added";
            }
            $zusagenListe = implode(",", array_filter(array_unique($zusagen)));
            $order[0]->setZusagen($zusagenListe);
            $eM->flush();
        }
        return new Response(
            json_encode(["exit_code"=>$exit_code, "zusagen"=>$this->getZusagenListe($zusagenListe, $eM)]),
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }



    /**
     * @Route("/food", name="food")
     */
    public function food()
    {
        $wochen = [];
        $entityManager = $this->getDoctrine()->getManager();
        for ($i=0;$i<2;$i++)
        {
            $wochen[] = self::getWeek(self::latestWeek()-$i, $entityManager);
        }
        return $this -> render('food/food.html.twig', array('wochen' => $wochen));
    }

    /**
     * @Route("/print/{week}")
     */
    public function print($week)
    {
        $entityManager = $this->getDoctrine()->getManager();
        return $this -> render('print.html.twig', array('woche' => self::getWeekLieferant($week, $entityManager, "Ziff")));
    }

    public function getWeek($week, $entityManager)
    {
        return self::getWeekLieferant($week, $entityManager, null);
        $bestellungen = $entityManager->getRepository(Bestellung::class)->findBy(array("woche"=>$week));

        $bestellTage = [];
        foreach($bestellungen as $bestellung)
        {
            $zusagenListe = $this->getZusagenListe($bestellung->getZusagen(), $entityManager);
            $bestellung->setTagName();

        }

        $bestellWoche = [];
        $bestellWoche += ["wochenNummer"=>$week];
        $bestellWoche += ["bestellTage"=>$bestellTage];  //FINDBY
        return $bestellWoche;

    }
    public function getWeekLieferant($week, $entityManager, $lieferant)
    {
        $bestellungen = $entityManager->getRepository(Bestellung::class)->findBy(array("woche"=>$week));

        $bestellTage = [];
        foreach($bestellungen as $bestellung)
        {
            $zusagenListe = $this->getZusagenListe($bestellung->getZusagen(), $entityManager);
            $bestellung->setTagName();
            if($bestellung->getLieferant() == $lieferant || $lieferant == null)
            {
                $bestellTage[] = array (
                    "bestellung" => $bestellung,
                    "zusagenListe" => $zusagenListe,
                );
            }
        }

        $bestellWoche = [];
        $bestellWoche += ["wochenNummer"=>$week];
        $bestellWoche += ["bestellTage"=>$bestellTage];  //FINDBY
        return $bestellWoche;

    }
    public function getZusagenListe($zusagen, $eM)
    {
        $zusagenListe = [];
        $users = $eM->getRepository(Benutzer::class)->findAll();
        $zusagen = explode(",", $zusagen);
        foreach ($users as $user)
        {
            if(in_Array($user->getId(), $zusagen))
            {
                $zusagenListe[] = $user;
            }
        }
        return $zusagenListe;
    }
    public function getBenutzerListe($eM)
    {
        $benutzerListe = $eM->getRepository(Benutzer::class)->findAll();
        return $benutzerListe;

    }



    /**
     * @Route("/index", name="main_page")
     */
    public function page()
    {

        return $this ->render('index.html.twig');
    }
    /**
     * @Route("/admin/{week}", name="adminW", requirements={"week"="\d+"})
     */
    public function adminW($week)
    {
        $eM = $this->getDoctrine()->getManager();
        $woche = self::getWeek($week, $eM);
        return $this -> render('admin/tables.html.twig', array('woche' => $woche, 'users' => self::getBenutzerListe($eM)));
    }

    /**
     * @Route("/admin/{cmd}", name="processOrder")
     */
    public function processOrder($cmd, Request $r)
    {
        $exit_code = "nothing done";


        $bestellung = new Bestellung();
        if($r->isXmlHttpRequest())
        {
            $eM = $this->getDoctrine()->getManager();
            if($cmd != "add")
            {
                $bestellung = $eM->getRepository(Bestellung::class)->find($r->request->get('id'));
                if($cmd == "delete")
                {
                    $eM->remove($bestellung);
                    $exit_code = "deleted";
                }
                else if($cmd == "update")
                {
                    $bestellung->setTag($r->request->get('tag'));
                    $bestellung->setWoche($r->request->get('woche'));
                    $bestellung->setGericht($r->request->get('gericht'));
                    $bestellung->setLieferant($r->request->get('lieferant'));
                    (empty($r->request->get('zusagen'))? :$bestellung->setZusagen(implode(",", $r->request->get('zusagen'))));
                    $exit_code = "updated";
                }
            }
            else
            {
                $bestellung->setTag($r->request->get('tag'));
                $bestellung->setWoche($r->request->get('woche'));
                $bestellung->setGericht($r->request->get('gericht'));
                $bestellung->setLieferant($r->request->get('lieferant'));
                (empty($r->request->get('zusagen'))? :$bestellung->setZusagen(implode(",", $r->request->get('zusagen'))));
                $eM->persist($bestellung);
                $exit_code = "added";
            }
            $eM->flush();
        }
        return new Response(json_encode(
            ["exit_code"=>$exit_code]),
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }

    /**
     * @Route("/admin"), name="admin")
     */
    public function admin()
    {
        return self::adminW(self::latestWeek());
    }

    public function latestWeek()
    {
        $eM = $this->getDoctrine()->getManager();
        $wochen = $eM->getRepository(Bestellung::class)->findBy(array(),array('woche' => 'DESC'));
        return $wochen[0]->getWoche();
    }
}
