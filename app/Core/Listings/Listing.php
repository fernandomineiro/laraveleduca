<?php

namespace App\Core\Listings;

abstract class Listing
{
    private $columns;
    private $sorts;
    private $filters;
    
    /**
     * Cria uma nova instancia da classe.
     *
     * @return  static
     */
    public static function new()
    {
        return new static();
    }
        
    /**
     * Voce escolhe quais dados que voce necessita listar para o usuario (select).
     *
     * @param  array  $columns
     * @return  $this
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }
    
    /**
     * Voce escolhe qual coluna quais colunas voce gostaria de ordenar (order by) 
     *
     * @param  array  $sorts
     * @return  $this
     */
    public function setSorts(array $sorts)
    {
        $this->sorts = $sorts;

        return $this;
    }
    
    /**
     * Voce escolhe quais filtros voce gostaria de utilizar para fazer uma condicional (pode ser usado em if ou where)
     *
     * @param  array  $filters
     * @return  $this
     */
    public function setFilters(array $filters)
    {
        $this->filters = $this->clearFilters($filters);

        return $this;
    }
    
    /**
     * Aplica todas as colunas escolhidas, ordenacoes e aplica a paginacao (15 dados).
     *
     * @return  \Illuminate\Support\Collection
     */
    public function paginate()
    {
        return $this->takeColumns($this->applySorts($this->buildQuery()))->paginate(15);
    }
    
    /**
     * Aplica todas as colunas escolhidas, ordenacoes e traz todos os dados em uma collection.
     *
     * @return  \Illuminate\Support\Collection
     */
    public function collect()
    {
        return $this->takeColumns($this->applySorts($this->buildQuery()))->get();
    }

    /**
     * Traz o numero de itens da busca que foi feita no banco.
     *
     * @return  int
     */
    public function count()
    {
        return $this->buildQuery()->count();
    }
    
    /**
     * Verifica se existe o filtro
     *
     * @param  string  $filterName
     * @return  bool
     */
    protected function hasFilter(string $filterName)
    {
        return array_key_exists($filterName, $this->filters);
    }
    
    /**
     * Pega o valor do filtro
     *
     * @param  string  $filterName
     * @return  mixed
     */
    protected function getFilter(string $filterName)
    {
        if (array_key_exists($filterName, $this->filters) && !is_null($this->filters[$filterName])) {
            return $this->filters[$filterName];
        }
    }
    
        
    /**
     * Limpa os dados que foram enviados como filtros
     *
     * @param  array  $filters
     * @return array
     */
    private function clearFilters(array $filters)
    {
        foreach ($filters as $key => $filter) {
            if (trim(strtolower($filter)) === 'true') {
                $filters[$key] = true;
            } elseif (trim(strtolower($filter)) === 'false') {
                $filters[$key] = false;
            } elseif (trim(strtolower($filter)) === 'null') {
                $filters[$key] = null;
            } elseif (trim(filter_var($filter, FILTER_VALIDATE_INT))) {
                $filters[$key] = intval($filter);
            } elseif (trim(filter_var($filter, FILTER_VALIDATE_FLOAT))) {
                $filters[$key] = floatval($filter);
            }
        }

        return $filters;
    }
    
    /**
     * Pega apenas as colunas que foram definidas no setColumns.
     *
     * @param  mixed  $query
     * @return  mixed
     */
    private function takeColumns($query)
    {
        $availableColumns = $this->availableColumns();
        $selects = [];

        foreach ($this->columns as $column) {
            if (array_key_exists($column, $availableColumns)) {
                $columnDBName = $availableColumns[$column];

                array_push($selects, $columnDBName.' as '.$column);
            }
        }

        $query->select($selects);
        
        return $query;
    }
    
    /**
     * Aplica as ordenacoes que foram definidas no setSorts.
     *
     * @param  mixed  $query
     * @return  mixed
     */
    private function applySorts($query)
    {
        foreach ($this->sorts as $key => $direction) {
            $query->orderBy($key, $direction);
        }

        return $query;
    }
}
