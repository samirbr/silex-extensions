<?php

namespace Samir\Pagination;

class Paginator
{
  private $data;
  private $limit;
  private $count = null;

  public function __construct($data, $limit)
  {
    $this->data = $data;
    $this->limit = $limit;
  }
  
  public function get($page = 1)
  {
    return array_slice($this->data, ($page - 1) * $this->limit, $this->limit);
  }
  
  public function count()
  {
    if ($this->count == null) {
      $this->count = ceil(count($this->data) / $this->limit);
    }
    
    return $this->count;
  }
  
  public function pages()
  {
    return range(1, $this->count());
  }
}