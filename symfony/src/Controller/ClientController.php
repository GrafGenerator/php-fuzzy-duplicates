<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ClientController extends Controller
{
    /**
     * @Route("/test", name="test")
     */
    public function index()
    {
        $str1 = "Даниил Михайлович Парчевский";
        $str2 = "Данил Михайлович Парчевский";
        $str3 = "Даниил Михалович Парчевский";
        $str4 = "Данила Михалович Парчевский";

        $hash1 = ssdeep_fuzzy_hash($str1);
        $hash2 = ssdeep_fuzzy_hash($str2);
        $hash3 = ssdeep_fuzzy_hash($str3);
        $hash4 = ssdeep_fuzzy_hash($str4);

        $match11 = ssdeep_fuzzy_compare($hash1, $hash1);
        $match12 = ssdeep_fuzzy_compare($hash1, $hash2);
        $match13 = ssdeep_fuzzy_compare($hash1, $hash3);
        $match14 = ssdeep_fuzzy_compare($hash1, $hash4);

        return $this->json([
            'hashes' => [
                $hash1,
                $hash2,
                $hash3,
                $hash4,
            ],
            'comparison' => [
                $match11,
                $match12,
                $match13,
                $match14,
            ],
        ]);
    }

    public function generateDb(){

    }
}
