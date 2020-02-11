<?php namespace App\Imports;

use App\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class AgentCodeImport implements ToCollection
{
  public $data = [];

  public function collection(Collection $rows)
  {
    foreach ($rows as $row) {
      $data[] = $row;
    }
  }
}