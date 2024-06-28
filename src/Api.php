<?php


namespace Paulinhoajr\ApiPixSicredi;

use Exception;
use Paulinhoajr\ApiPixSicredi\Util\Support;
use Paulinhoajr\ApiPixSicredi\Http\Request;
use Paulinhoajr\ApiPixSicredi\Http\Response;
use Paulinhoajr\ApiPixSicredi\Resources\Cob;
use Paulinhoajr\ApiPixSicredi\Resources\Cobv;
use Paulinhoajr\ApiPixSicredi\Resources\Payload;
use Paulinhoajr\ApiPixSicredi\Resources\Webhook;
use Paulinhoajr\ApiPixSicredi\Resources\LoteCobv;
use Paulinhoajr\ApiPixSicredi\Resources\PixRecebido;


class Api
{
    protected string $endpoint = 'https://api-pix.sicredi.com.br';
    protected string $oauth_path = 'oauth/token';
    protected string $api_path = 'api/v2';
    protected string $client_id;
    protected string $client_secret;
    protected string $certificado_psp;
    protected string $certificado_aplicacao;
    protected string $password_certificado_aplicacao;
    protected string $certificado_cadeia_completa;
    protected array $scopes = [
        'cob.read', 'cob.write', // criar e consultar cobranças
        //'cobv.read', 'cobv.write', // criar e consultar cobranças com vencimento
        //'lotecobv.read', 'lotecobv.write', // criar e consultar lotes de cobranças
        'pix.write', 'pix.read', // consultar e criar pix
        'webhook.read', 'webhook.write', // criar e consultar webhook
        //'payloadlocation.read', 'payloadlocation.write' // criar e consultar payload
    ];

    public int $timeout = 5;
    public ?string $token = null;
    public Request $request;
    public Response $response;


    public function setEndpoint(string $url): Api
    {
        if (Support::endsWith($url, '/')){
            $url = substr($url, 0, -1);
        }
        $this->endpoint = $url;

        return $this;
    }


    public function setOauthPath(string $path): Api
    {
        $this->oauth_path = $path;

        return $this;
    }


    public function setApiPath(string $path): Api
    {
        $this->api_path = $path;

        return $this;
    }


    public function getClientId(): string
    {
        return (string)$this->client_id;
    }


    public function setClientId(string $client_id): Api
    {
        $this->client_id = $client_id;
        return $this;
    }


    public function getClientSecret(): string
    {
        return (string)$this->client_secret;
    }


    public function setClientSecret(string $client_secret): Api
    {
        $this->client_secret = $client_secret;
        return $this;
    }


    public function getCertificadoPsp(): string
    {
        return (string)$this->certificado_psp;
    }


    public function setCertificadoPsp(string $certificado_psp): Api
    {
        if (!file_exists($certificado_psp)) {
            throw new Exception('certificado do PSP não encontrado');
        }

        $this->certificado_psp = $certificado_psp;

        return $this;
    }


    public function getCertificadoAplicacao(): string
    {
        return (string)$this->certificado_aplicacao;
    }


    public function setCertificadoAplicacao(string $certificado_aplicacao): Api
    {
        if (!file_exists($certificado_aplicacao)) {
            throw new Exception('certificado da aplicação não encontrado');
        }

        $this->certificado_aplicacao = $certificado_aplicacao;

        return $this;
    }


    public function getPasswordCertificadoAplicacao(): string
    {
        return (string)$this->password_certificado_aplicacao;
    }


    public function setPasswordCertificadoAplicacao(string $password_certificado_aplicacao): Api
    {
        $this->password_certificado_aplicacao = $password_certificado_aplicacao;

        return $this;
    }


    public function getCertificadoCadeiaCompleta(): string
    {
        return (string)$this->certificado_cadeia_completa;
    }


    public function setCertificadoCadeiaCompleta(string $certificado_cadeia_completa): Api
    {
        if (!file_exists($certificado_cadeia_completa)) {
            throw new Exception('certificado cadeia completa não encontrado');
        }

        $this->certificado_cadeia_completa = $certificado_cadeia_completa;

        return $this;
    }


    public function setScopes(array $scopes): Api
    {
        $this->scopes = $scopes;

        return $this;
    }


    public function setTimeout($timeout): Api
    {
        $this->timeout = (int)$timeout;

        return $this;
    }


    public function setToken(string $token): Api
    {
        $this->token = $token;

        return $this;
    }


    public function cob(): Cob
    {
        return new Cob($this);
    }


    public function cobv(): Cobv
    {
        return new Cobv($this);
    }


    public function loteCobv(): LoteCobv
    {
        return new LoteCobv($this);
    }


    public function webhook(): Webhook
    {
        return new Webhook($this);
    }


    public function payload(): Payload
    {
        return new Payload($this);
    }


    public function pixRecebido(): PixRecebido
    {
        return new PixRecebido($this);
    }


    public function requestToken(): Request
    {
        $resquest = (new Request($this))
            ->authenticate(
                $this->getUrl(
                    $this->oauth_path . '?grant_type=client_credentials&scope=' . implode('+', $this->scopes),
                    false
                ),
                $this->client_id,
                $this->client_secret
            );

        if ($resquest->status_code === 200) {
            $this->setToken($resquest->response->getDataValue('access_token'));
        }

        return $resquest;
    }


    public function getUrl(?string $path = null, $includeApiPath = true): string
    {
        if ($includeApiPath)
            return $this->endpoint . Support::start($this->api_path, '/') . Support::start($path, '/');
        else
            return $this->endpoint . Support::start($path, '/');
    }

}
