<?php

namespace PaneeDesign\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('PedApiBundle:Default:index.html.twig');
    }
}
