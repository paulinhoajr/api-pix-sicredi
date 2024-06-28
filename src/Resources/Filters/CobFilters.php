<?php


namespace Paulinhoajr\ApiPixSicredi\Resources\Filters;


use Paulinhoajr\ApiPixSicredi\Util\Support;
use Paulinhoajr\ApiPixSicredi\Resources\Traits\BasicFilter;

class CobFilters
{
    use BasicFilter;


    public function cpf(string $cpf): CobFilters
    {
        $this->filters['cpf'] = Support::onlyNumbers($cpf);

        return $this;
    }


    public function cnpj(string $cnpj): CobFilters
    {
        $this->filters['cnpj'] = Support::onlyNumbers($cnpj);

        return $this;
    }


    public function status(string $status): CobFilters
    {
        $this->filters['status'] = $status;

        return $this;
    }

}
