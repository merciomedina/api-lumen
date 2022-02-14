<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class SimulatorController extends BaseController
{

    protected $baseUrl;
    protected $cepUrl;
    protected $baseUrlGoogle;
    protected $apiKey;

    public function __construct() {
        $this->baseUrl = "https://api2.77sol.com.br/busca-cep?";
        $this->cepUrl  = 'viacep.com.br/ws/';
        $this->baseUrlGoogle = "https://maps.googleapis.com/maps/api/geocode/json?";
        $this->apiKey = "AIzaSyBXR8barLfeXOmbc0-USwpSsWCMBAJGJDQ";
    }

    public function handle(Request $request) {   
        
        $this->validate($request, [
            'cep' => 'required|integer',
            'str' => 'required|string',
            'account' => 'required|integer',
        ]);
       
        $address = $this->getCEP($request->cep);        
          
        if ($address == "error"){           
            return response()->json(['status' => 400, 'message' => 'Error when get CEP!']);
        } else if($address == "Invalid CEP"){            
            return response()->json(['status' => 404, 'message' => 'Invalid CEP']);        
        }
        
        $geo = $this->getGoogleAddress($address);

        if (!$geo->lat ?? null){            
            return response()->json(['status' => 404, 'message' => 'Error when get Geolocation']);
        }
        
        $newAdress = (object) array_merge((array) $address, (array) $geo);

        $data = $this->getData($newAdress, $request->str, $request->account);

        return  $data;
    }

    function getCEP($cep) {
        $client = new Client([
            'base_uri' => $this->cepUrl
        ]); 

        $response = $client->request('GET', $cep. '/json');                

        if ($response->getStatusCode() != 200){            
            return "error";
        }
        
        $body = $response->getBody();
        $body_string = (string) $body ; 
        $address = json_decode($body_string);
               
        if ($address->erro ?? null){
            return 'Invalid CEP';
        }

        return $address;
    }

    function getGoogleAddress($address) {        
        $adressGoogle = $address->logradouro . "," . $address->localidade . "," . $address->uf;

        $client2 = new Client(['base_uri' => $this->baseUrlGoogle . 'address='. $adressGoogle . '&key=' . $this->apiKey]);
      
        $response = $client2->request('GET');
        
        $body = $response->getBody();
        $body_string = (string) $body ; 
        $object = json_decode($body_string);

        if ($object->status == "OK"){

            $location = $object->results[0]->geometry->location; 

            return $location;
        }

        return "error when get address";
    }

    function getData($address, $str, $account) {  

        $uf   = $address->uf;        
        $cep  = $address->cep;
        $lat  = $address->lat;
        $lng  = $address->lng;
        $city = $address->localidade;        

        $client3 = new Client(['base_uri' => $this->baseUrl . '&estrutura='. $str . '&valor_conta='. $account . '&cidade='. $city . '&estado='. $uf . '&cep='. $cep . '&latitude='. $lat . '&longitude='. $lng]);
        
        $response = $client3->request('GET');
        $result = $response->getBody();
 
        return $result;
        
    }
} 
