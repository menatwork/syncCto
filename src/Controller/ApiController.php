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

use Contao\CoreBundle\Controller\AbstractController;
use MenAtWork\SyncCto\Helper\Ping;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SyncCtoPopupDB;
use SyncCtoPopupFiles;

/**
 * Class PopupController
 *
 * @package MenAtWork\SyncCto\Controller
 */
class ApiController extends AbstractController
{
    /**
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
