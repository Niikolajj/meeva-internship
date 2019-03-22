<?php

namespace App\Controller;

use App\Entity\Benutzer;
use App\Entity\Bestellung;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    /**
     * @Route("/order", name="order")
     */
    public function order(Request $request)
    {
        $exit_code = 'nothing done';
        $orderNr   = $request->request->get('id');
        $user      = $request->request->get('user');
        $order     = abs($orderNr);
        $eM        = $this->getDoctrine()->getManager();
        $order     = $eM->getRepository(Bestellung::class)->findBy(['id' => $order]);
        $zusagen   = $order[0]->getZusagen();
        $zusagen   = explode(',', $zusagen);

        if (false !== ($key = array_search($user, $zusagen))) {
            unset($zusagen[$key]);
            $exit_code = 'removed';
        } else {
            $zusagen[] = $user;
            $exit_code = 'added';
        }
        $zusagenListe = implode(',', array_filter(array_unique($zusagen)));
        $order[0]->setZusagen($zusagenListe);
        $eM->flush();

        return new Response(
            json_encode(['exit_code' => $exit_code, 'zusagen' => $this->getZusagenListe($zusagenListe, $eM)]),
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }

    /**
     * @Route("/food", name="food")
     */
    public function food()
    {
        $wochen        = [];
        $entityManager = $this->getDoctrine()->getManager();
        for ($i = 0; $i < 2; ++$i) {
            $wochen[] = self::getBestellWoche(self::latestWeek() - $i, $entityManager);
        }

        return $this->render('food/userpage.html.twig', ['bestellwochen' => $wochen]);
        /*
        $wochen        = [];
        $entityManager = $this->getDoctrine()->getManager();
        for ($i = 0; $i < 2; ++$i) {
            $wochen[] = self::getWeek(self::latestWeek() - $i, $entityManager);
        }

        return $this->render('food/food.html.twig', ['wochen' => $wochen]);
        */
    }

    /**
     * @Route("/print/{week}/{lieferant}")
     */
    public function print($week, $lieferant)
    {
        $entityManager = $this->getDoctrine()->getManager();

        return $this->render('print.html.twig', ['woche' => self::getWeekLieferant($week, $entityManager, $lieferant)]);
    }

    public function getWeek($week, $entityManager)
    {
        return self::getWeekLieferant($week, $entityManager, null);
        $bestellungen = $entityManager->getRepository(Bestellung::class)->findBy(['woche' => $week]);

        $bestellTage = [];
        foreach ($bestellungen as $bestellung) {
            $zusagenListe = $this->getZusagenListe($bestellung->getZusagen(), $entityManager);
            $bestellung->setTagName();
        }

        $bestellWoche = [];
        $bestellWoche += ['wochenNummer' => $week];
        $bestellWoche += ['bestellTage' => $bestellTage];  //FINDBY
        return $bestellWoche;
    }

    public function getBestellWoche($woche, $eM)
    {
        $bestellWoche = [];
        $bestellungen = $eM->getRepository(Bestellung::class)->findBy(['woche' => $woche]);
        $lieferanten = [];
        foreach($bestellungen as $bestellung)
        {
            $lieferanten[]=$bestellung->getLieferant();
        }
        $lieferanten = array_unique($lieferanten);
        $lieferWochen = [];
        foreach($lieferanten as $lieferant)
        {
            $lieferbestellungen = $eM->getRepository(Bestellung::class)->findBy(['woche' => $woche,'lieferant'=> $lieferant]);
            dump($lieferbestellungen);
            $lieferbestellTage = [];
            $lieferWoche= [];
            foreach ($lieferbestellungen as $lieferbestellung)
            {
                $zusagenListe = $this->getZusagenListe($lieferbestellung->getZusagen(), $eM);
                $lieferbestellung->setTagName();
                $lieferbestellTage[] = [
                    'bestellung'   => $lieferbestellung,
                    'zusagenListe' => $zusagenListe,
                ];
            }
            $lieferWoche['lieferant'] = $lieferant;
            $lieferWoche['lieferTage'] = $lieferbestellTage;
            $lieferWochen[] = $lieferWoche;
        }
        //dump($lieferWochen);
        $bestellWoche['wochenNummer'] = $woche;
        $bestellWoche['lieferWochen'] = $lieferWochen;
        return $bestellWoche;
    }

    public function getWeekLieferant($week, $entityManager, $lieferant)
    {
        $bestellungen = $entityManager->getRepository(Bestellung::class)->findBy(['woche' => $week]);

        $bestellTage = [];
        foreach ($bestellungen as $bestellung) {
            $zusagenListe = $this->getZusagenListe($bestellung->getZusagen(), $entityManager);
            $bestellung->setTagName();
            if ($bestellung->getLieferant() == $lieferant || null == $lieferant) {
                $bestellTage[] = [
                    'bestellung'   => $bestellung,
                    'zusagenListe' => $zusagenListe,
                ];
            }
        }

        $bestellWoche = [];
        $bestellWoche += ['wochenNummer' => $week];
        $bestellWoche += ['bestellTage' => $bestellTage];  //FINDBY
        return $bestellWoche;
    }

    public function getZusagenListe($zusagen, $eM)
    {
        $zusagenListe = [];
        $users        = $eM->getRepository(Benutzer::class)->findAll();
        $zusagen      = explode(',', $zusagen);
        foreach ($users as $user) {
            if (in_array($user->getId(), $zusagen)) {
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
        return $this->render('index.html.twig');
    }

    /**
     * @Route("/admin/{week}", name="adminW", requirements={"week"="\d+"})
     */
    public function adminW($week)
    {
        $eM    = $this->getDoctrine()->getManager();
        //$woche = self::getWeek($week, $eM);
        dump(self::getBestellWoche($week, $eM));
        $tage = [];
        $tage[] = ["tag"=>"Montag", "id"=>"1"];
        $tage[] = ["tag"=>"Dienstag", "id"=>"2"];
        $tage[] = ["tag"=>"Mittwoch", "id"=>"3"];
        $tage[] = ["tag"=>"Donnerstag", "id"=>"4"];
        $tage[] = ["tag"=>"Freitag", "id"=>"5"];
        return $this->render("admin/admin.html.twig", ['bestellwoche' => self::getBestellWoche($week, $eM), 'users'=> self::getBenutzerListe($eM), 'tage' => $tage ]);
        //return $this->render('admin/admin.html.twig', ['woche' => $woche, 'users' => self::getBenutzerListe($eM)]);
    }
    /*
    public function adminW($week)
    {
        $eM    = $this->getDoctrine()->getManager();
        $woche = self::getWeek($week, $eM);

        return $this->render('admin/tables.html.twig', ['woche' => $woche, 'users' => self::getBenutzerListe($eM)]);
    }
    */

    /**
     * @Route("/admin/user/{cmd}", name="processUser")
     */
    public function processUser($cmd, Request $r)
    {
        $exit_code = 'nothing done';

        $user = new Benutzer();
        $eM   = $this->getDoctrine()->getManager();
        dump($r->request);
        if ('add' != $cmd) {
            $user = $eM->getRepository(Benutzer::class)->find($r->request->get('user'));
            if ('delete' == $cmd) {
                $eM->remove($user);
                $exit_code = 'deleted';
            } elseif ('update' == $cmd) {
                $user->setVorname($r->request->get('vorname'));
                $user->setNachname($r->request->get('nachname'));
                $user->setEmail($r->request->get('email'));
                (null == $r->request->get('roles')) ? $user->setRoles([]) : $user->setRoles($r->request->get('roles'));
                $exit_code = 'updated';
            }
        } else {
            if ($eM->getRepository(Benutzer::class)->findOneBy(['email' => $r->request->get('email')])) {
                $exit_code = 'duplicate email';
            } else {
                $user->setVorname($r->request->get('vorname'));
                $user->setNachname($r->request->get('nachname'));
                $user->setEmail($r->request->get('email'));
                (null == $r->request->get('roles')) ? $user->setRoles([]) : $user->setRoles($r->request->get('roles'));
                $eM->persist($user);
                $exit_code = 'added';
            }
        }
        $eM->flush();

        return new Response(json_encode([
            'exit_code' => $exit_code,
        ]),
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }

    /**
     * @Route("/admin/order", name="processOrder")
     */
    public function processOrder(Request $r)
    {

        $exit_code = "nothing done";


        $weekNr= $r->request->get('woche');
        $eM        = $this->getDoctrine()->getManager();
        $woche = $r->request->get('bestellungen');
        $lieferant = $r->request->get('lieferant');
        foreach ($woche as $tag)
        {
            parse_str($tag, $tag);
            dump($tag);
            if(!isset($tag['zusagen']))
            {
                $tag['zusagen'] = [];
            }
            if(isset($tag['id']))
            {
                $bestellung = $eM->getRepository(Bestellung::class)->find($tag['id']);
                $bestellung->setGericht($tag['gericht']);
                $bestellung->setZusagen(implode(",",$tag['zusagen']));
                $exit_code="updated";
            }
            else
            {
                $bestellung = new Bestellung();
                $bestellung->setGericht($tag['gericht']);
                $bestellung->setZusagen(implode(",",$tag['zusagen']));
                $bestellung->setLieferant($lieferant);
                $bestellung->setWoche($weekNr);
                $bestellung->setTag($tag['tag']);
                $eM->persist($bestellung);
                $exit_code="added";

            }
        }
        $eM->flush();


        return new Response(json_encode([
            'exit_code' => $exit_code,
            'id'        => "FK",
        ]),
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );

        $exit_code = 'nothing done';

        $bestellung = new Bestellung();
        $eM         = $this->getDoctrine()->getManager();
        if ('add' != $cmd) {
            $bestellung = $eM->getRepository(Bestellung::class)->find($r->request->get('id'));
            if ('delete' == $cmd) {
                $eM->remove($bestellung);
                $exit_code = 'deleted';
            } elseif ('update' == $cmd) {
                $bestellung->setTag($r->request->get('tag'));
                $bestellung->setWoche($r->request->get('woche'));
                $bestellung->setGericht($r->request->get('gericht'));
                $bestellung->setLieferant($r->request->get('lieferant'));
                (empty($r->request->get('zusagen')) ?: $bestellung->setZusagen(implode(',', $r->request->get('zusagen'))));
                $exit_code = 'updated';
            }
        } else {
            $bestellung->setTag($r->request->get('tag'));
            $bestellung->setWoche($r->request->get('woche'));
            $bestellung->setGericht($r->request->get('gericht'));
            $bestellung->setLieferant($r->request->get('lieferant'));
            (empty($r->request->get('zusagen')) ?: $bestellung->setZusagen(implode(',', $r->request->get('zusagen'))));
            $eM->persist($bestellung);
            $exit_code = 'added';
        }
        $eM->flush();

        return new Response(json_encode([
            'exit_code' => $exit_code,
            'id'        => $bestellung->getId(),
            ]),
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
        $eM     = $this->getDoctrine()->getManager();
        $wochen = $eM->getRepository(Bestellung::class)->findBy([], ['woche' => 'DESC']);

        return $wochen[0]->getWoche();
    }
}
