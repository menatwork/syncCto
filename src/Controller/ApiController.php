<?php
/**
 * Phoenix Testlab - Corporate site - Feature bundle
 *
 * Created by MEN AT WORK Werbeagentur GmbH 2019
 *
 * @copyright  MEN AT WORK Werbeagentur GmbH 2019
 * @package    contao-pxt-feature-bundle
 * @author     Sven Meierhans <meierhans@men-at-work.de>
 */

namespace MenAtWork\SyncCto\Controller;


use MenAtWork\SyncCto\Helper\Ping;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SyncCtoPopupDB;
use SyncCtoPopupFiles;

/**
 *
 * @Route("/syncCto", defaults={"_scope" = "backend"})
 *
 * Class PopupController
 *
 * @package MenAtWork\SyncCto\Controller
 */
class ApiController extends AbstractController
{

    /**
     * @Route("/filesPopup", name="maw.sync_cto.popup_files")
     *
     * @return Response
     */
    public function filesPopupAction()
    {
        $this->container->get('contao.framework')->initialize();

        $objPopup = new SyncCtoPopupFiles();

        return new Response(
            $objPopup->run()->getOutput()
        );
    }

    /**
     * @Route("/databasePopup", name="maw.sync_cto.popup_database")
     *
     * @return Response
     */
    public function databasePopupAction()
    {
        $this->container->get('contao.framework')->initialize();

        $objPopup = new SyncCtoPopupDB();

        return new Response(
            $objPopup->run()->getOutput()
        );
    }

    /**
     * @Route("/api/1.0/ping", name="maw.sync_cto.api.1_0.ping")
     *
     * @param Request $request The request.
     *
     * @return JsonResponse
     */
    public function pingAction(Request $request)
    {
        $this->container->get('contao.framework')->initialize();

        $ping = new Ping();

        return new JsonResponse(
            $ping->pingClientStatus($request->get('clientID'))
        );
    }
}
