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


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SyncCtoPopupDB;
use SyncCtoPopupFiles;

/**
 *
 * @Route("/syncCto", defaults={"_scope" = "backend"})
 *
 * Class PopupController
 * @package MenAtWork\SyncCto\Controller
 */
class PopupController extends AbstractController
{

    /**
     * @Route("/filesPopup", name="maw.sync_cto.popup_files")
     *
     * @return Response
     */
    public function filesPopupAction()
    {
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
        $objPopup = new SyncCtoPopupDB();

        return new Response(
            $objPopup->run()->getOutput()
        );
    }

}