<?php

namespace Paulinhoajr\ApiPixSicredi;

class PixSicredi
{
    const urlH = 'https://api-pix-h.sicredi.com.br';
    const urlP = 'https://api-pix.sicredi.com.br';

    public  $url;
    public  $client_id;
    public  $client_secret;
    public  $authorization;
    public  $token;
    public  $crt_file;
    public  $key_file;
    public  $pass;
    public $header;
    public $path;
    public $fields;

    public function __construct($dados=null, $token=null)
    {
        $certificateCER = storage_path('/app/public/certificados/certificado.cer');
        $certificateKEY = storage_path('/app/public/certificados/certificado.key');

        if (!file_exists($certificateCER)) {
            throw new \Exception('certificado CER não encontrado em: '. $certificateCER);
        }
        
        if (!file_exists($certificateKEY)) {
            throw new \Exception('certificado CER não encontrado em: '. $certificateKEY);
        }
        
        /*
                $initPix  = [
                    "producao" => 1, // 0 | 1
                    "client_id" => config('app.sicredi_id'),
                    "client_secret" => config('app.sicredi_secret'),
                    "crt_file" => $certificateCER,
                    "key_file" => $certificateKEY,
                    "pass" => ""
                ];
                */
        /*if ((int) $dados["producao"] == 1) {
            $this->url = self::urlP;
        } else {
            $this->url = self::urlH;
        }*/

        $this->url = self::urlP;

        if ($token){
            $this->token = $token;
        }

        $this->client_id 		= config('app.sicredi_id');
        $this->client_secret 	= config('app.sicredi_secret');

        $this->crt_file = $certificateCER;
        $this->key_file = $certificateKEY;
        $this->pass     = '';

        $this->authorization = base64_encode($this->client_id . ":" . $this->client_secret);
    }

    public function Request($method)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url .  $this->path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->fields);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSLCERT, $this->crt_file);
        curl_setopt($curl, CURLOPT_SSLKEY, $this->key_file);
        curl_setopt($curl, CURLOPT_SSLKEYPASSWD, $this->pass);
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $json = (array)json_decode($response, false);

        $json['httpcode'] = $httpcode;

        return $json;
    }

    public function accessToken()
    {

        $this->path  = '/oauth/token?grant_type=client_credentials&scope=cob.write+cob.read+webhook.read+webhook.write';
        $this->header = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . $this->authorization . ' '
        ];
        $response     = $this->Request("POST");

        return $response;
    }

    public function updateWebhook($url, $chave)
    {

        $this->path  =  '/api/v2/webhook/' . $chave;
        $this->header =  ['Content-Type: application/json', 'Authorization: Bearer ' . $this->token . ''];
        $this->fields =  json_encode(["webhookUrl" => $url]);
        $response     =  $this->Request("PUT");
        return $response;
    }

    public function getUrlWebhook($chave)
    {
        $this->path  =  '/api/v2/webhook/' . $chave;
        $this->header =  ['Authorization: Bearer ' . $this->token . ''];
        $response     =  $this->Request("GET");
        return $response;
    }

    public function deleteUrlWebhook($chave)
    {
        $this->path  =  '/api/v2/webhook/' . $chave;
        $this->header =  ['Authorization: Bearer ' . $this->token . ''];
        $response     =  $this->Request("DELETE");
        return $response;
    }

    public function criarCobranca($data, $txid=null)
    {
        $this->fields = json_encode($data);
        $this->path  =  '/api/v2/cob/'.$txid;
        $this->header =  ['Content-Type: application/json', 'Authorization: Bearer ' . $this->token . ''];

        $response     =  $this->Request("PUT");

        return $response;
    }


    public function dadosDeCobranca($id)
    {
        $this->path  =  '/api/v2/cob/' . $id;
        $this->header =  ['Authorization: Bearer ' . $this->token . ''];
        $response     =  $this->Request("GET");
        return $response;
    }
}
