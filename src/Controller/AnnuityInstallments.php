<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class AnnuityInstallments extends AbstractController {

     
    public function paymentAmount($air, $periods, $creditAmount){
    //    Payment Amount (PMT) -- The amount of the annuity payment each period
    //    PMT = FV * ipr / (1 - ( 1 + ipr) ** -n
    //    Credit amount (FV)
    //    n -- number of periods
    //    air -- Annual interest rate in percent
    //    irp -- interest rate per period

        $irp = $air/100/12;
        $PMT = $creditAmount * $irp /(1-(1 + $irp)**(-1*$periods));
        return $PMT;

    }

 
    

}
