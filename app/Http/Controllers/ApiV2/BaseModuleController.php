<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;

use Illuminate\Http\Request ;

class BaseModuleController extends BaseController
{
  protected $orderBy = '';
  protected $orderDirection = 'asc';

  protected $updateRules = [];

  //****************
  //    Index
  //****************
  public function index()
  {
    $query = $this->model;
//    echo '1. query is null: '.(is_null($query) ? 'yes' : 'no').PHP_EOL;
    $query = $this->prepareIndexQuery($query);
//    echo '2. query is null: '.(is_null($query) ? 'yes' : 'no').PHP_EOL;
    $query = $this->onIndexOrderBy($query);
//    echo '3. query is null: '.(is_null($query) ? 'yes' : 'no').PHP_EOL;

    $rows = $query->get();
    $rows = $this->onIndexDataReady($rows);

    return response()->json([
      'status' => true,
      'result' => [
        'data' => $rows,
        'pageable' => [],
        'total' => 0
      ]
    ]);
  }

  protected function prepareIndexQuery($query)
  {
    if(is_null($query)) {
      echo 'query is null'.PHP_EOL;
    }
    return $query;
  }

  protected function onIndexDataReady($rows)
  {
//    if(is_null($rows)) {
//      echo 'onInexDataReady: null'.PHP_EOL;
//      echo 'query is null'.PHP_EOL;
//    }
    return $rows;
  }

  protected function onIndexOrderBy($query)
  {
//    echo 'onIndexOrderBy: '.PHP_EOL;
//    echo 'query: ';
//    return $query;
//    if(is_null($query)) {
//      echo 'onIndexOrderBy: null'.PHP_EOL;
//    }
    if (!empty($this->orderBy)) {
      $query->orderBy($this->orderBy, $this->orderDirection);
    }
    return $query;
  }

  //****************
  //    Show
  //****************
  public function show($id)
  {
    if ($id == 0) {
      $record = $this->getBlankRecord();
    } else {
      $record = $this->getRow($id)->toArray();
    }
    return response()->json([
      'status' => true,
      'result' => [
        'data' => $record
      ]
    ]);
  }

  public function getRow($id) {
    $row = $this->model->find($id);
    return $row;
  }

  //****************
  //    Update
  //****************
  public function update(Request $request, $id) {
    $row = $this->model->find($id);
    $input = $request->validate($this->updateRules);
    $row->update($input);
    $this->onUpdateComplete($request, $row);

    $row = $this->model->find($id);
    return response()->json([
      'status' => true,
      'result' => $row
    ]);
  }

  protected function onUpdateComplete($request, $row) {
  }

}