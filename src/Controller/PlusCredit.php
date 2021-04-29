<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlusCredit  extends AbstractController {

    
    
    public function Index(): Response{
        return $this->render('Credisimo/layoutPlusCredit.html.twig', ['container' => "Credisimo/containerPlusCredit.html.twig"]);
    }
    

    public function getSchedule(Request $request): Response {
        $data = $_POST;
        //$data['taxes']["tax1"];
        $creditAmount = $data['amount'];
        $periods =$data['numberOfInstallments'];
        $air = $data['air'];
        $irp = $air/100/12;   
        $data['PMT'] = AnnuityInstallments::paymentAmount($air, $periods, $creditAmount);
        $tax1 = round($data['taxes']['tax1']/$periods,2);
        $tax2 = round($data['taxes']['tax2']/$periods,2);
        $tax1ost = $data['taxes']['tax1']/$periods - $tax1;
        $tax2ost = $data['taxes']['tax2']/$periods - $tax2;
        $PMT = $data['PMT'] - round($data['PMT']);
        
        
        for ($x = 0; $x <= $periods -1; $x++) {
            $date = $data['maturityDate'];
            if (in_array(intval(date('d', strtotime($date))),[10,20])){
                $date = date('Y-m-d', strtotime("+$x months", strtotime($date)));
            }
            else {
                $daysInMounth = cal_days_in_month(CAL_GREGORIAN,intval(date('m', strtotime("+$x months", strtotime($date)))), intval(date('Y', strtotime("+$x-1 months", strtotime($date)))));
                //$date = $daysInMounth;
                $date = date("Y-m-$daysInMounth", strtotime("+$x months", strtotime($date)));
            }
            
            if ($x == 0 ){ 
                $data['FV'][$x] = $creditAmount*(1 + $irp) - $data['PMT']; 
                $data['paramsList'][$x] = [
                        'number' => $x+1, 
                        'date' => $date, 
                        'period' => intval(date('d', strtotime($data['utilisationDate']))), 
                        'installmentAmount' => round($data['PMT'],2) + $tax1 + $tax2, 
                        'principal' => round($data['PMT'] - $creditAmount*$irp,2),
                        'interest' => round($creditAmount*$irp,2),
                        'tax1' => $tax1,
                        'tax2' => $tax2,
                ]; 
            }
            else if ($x == ($periods -1) ){ 
                $data['FV'][$x] = $data['FV'][$x-1]*(1 + $irp); 
                $data['paramsList'][$x] = [
                        'number' => $x+1, 
                        'date' => $date, 
                        'period' => cal_days_in_month(CAL_GREGORIAN,intval(date('m', strtotime("+$x months", strtotime($data['utilisationDate'])))), intval(date('Y', strtotime("+$x-1 months", strtotime($data['utilisationDate']))))), 
                        'installmentAmount' => round($data['PMT'],2)  + $tax1 + $tax2 , 
                        'principal' => round($data['PMT'] - $data['FV'][$x-1]*$irp,2),
                        'interest' => round($data['FV'][$x-1]*$irp,2),
                        'tax1' => round($tax1 + $tax1ost*$periods,2),
                        'tax2' => round($tax2 + $tax2ost*$periods,2),
                ]; 
            }
            else{
                $data['FV'][$x] = $data['FV'][$x-1]*(1 + $irp) - $data['PMT'] ;
                $data['paramsList'][$x] = [
                        'number' => $x+1, 
                        'date' => $date, 
                        'period' => cal_days_in_month(CAL_GREGORIAN,intval(date('m', strtotime("+$x months", strtotime($data['utilisationDate'])))), intval(date('Y', strtotime("+$x-1 months", strtotime($data['utilisationDate']))))), 
                        'installmentAmount' => round($data['PMT'],2) + $tax1 + $tax2, 
                        'principal' => round($data['PMT'] - $data['FV'][$x-1]*$irp,2),
                        'interest' => round($data['FV'][$x-1]*$irp,2),
                        'tax1' => $tax1 ,
                        'tax2' => $tax2 ,
                ]; 
            }
        }
        
        return $this->render('Credisimo/layoutPlusCredit.html.twig',['container' => "Credisimo/containerPlusCreditList.html.twig", 'paramsLists' => $data['paramsList']]);
        //return $this->json($data['paramsList']);
    }
}
