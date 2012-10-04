<?php

/**
 * SiteController is the default controller to handle user requests.
 */
class SiteController extends CController
{
    /**
     * Index action is the default action in a controller.
     */
    public function actionIndex() {
        echo 'Hello Opencorpora world!';
    }
    public function actionPage() {
        $page = $_GET['page'];
        if ($page) {
            $this->render($page, array(
                'active_page' => $page
            ));
        }
    }
}
