<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;

use Illuminate\Http\Request;

class BaseModuleController extends BaseController
{
  protected $orderBy = '';
  protected $orderDirection = 'asc';

  protected $updateRules = [];
  protected $filterFields = [];
  protected $indexWith = [];

  //****************
  //    Index
  //****************
  public function index(Request $request)
  {
    $query = $this->model;

    $query = $this->prepareIndexQuery($request, $query);

    $query = $this->onIndexOrderBy($query);

    $query = $this->onIndexWith($query);

    $query = $this->onIndexJoin($query);

    $query = $this->onIndexSelect($request, $query);

    $query = $this->onIndexFilter($request, $query);

//    print_r($rows->toArray());
//    echo PHP_EOL.PHP_EOL;
//    echo 'sql='.$query->toSql().PHP_EOL.PHP_EOL;

//    $rows = $query->get();
//    return response()->json($rows);


    if ($request->has('page')) {
      $page = $request->get('page', 1);
      $limit = $request->get('limit', 20);
//      $offset = ($request->get('page', 1)-1)*$limit;
      $totalCount = $query->count();
      $lastPage = ceil($totalCount/$limit);
      if ($page > $lastPage) {
        $page = $lastPage;
      }
      // $data = $query->get();
      $offset = ($page - 1) * $limit;
      $data = $query->skip($offset)->take($limit)->get();

      $pagedData = new \Illuminate\Pagination\LengthAwarePaginator($data, $totalCount, $limit, $page);
//      $offset = ($page - 1) * $limit;

//      $pagedData = $query->skip($offset)->take($limit)->get();

      $pagedData->setCollection($this->onIndexDataReady($request, $pagedData->getCollection()));
      $result = $pagedData;
    } else {
      $data = $query->get();
      $result = $this->onIndexDataReady($request, $data);
    }

    return response()->json([
      'status' => true,
      'result' => $result
    ]);
  }

  protected function onIndexSelect($request, $query) {
    if ($request->has('select')) {
      $fields = explode(',', $request->get('select'));
      $query = $query->select(
        array_values(array_filter($fields, function ($item) {
          return strpos($item, '.') === false;
        }))
      );
    }
    return $query;
  }

  protected function onIndexJoin($query) {
    return $query;
  }
  protected function onIndexWith($query)
  {
    if (!empty($this->indexWith)) {
      $query = $query->with($this->indexWith);
    }
    return $query;
  }

  protected function onIndexFilter($request, $query)
  {
    $filter = $request->get('filter', '');
    if (!empty($filter)) {
      $filterItems = explode('|', $filter);
      foreach ($filterItems as $filterItem) {
        $keyValue = explode(':', $filterItem);
        $query = $this->onIndexFilterField($query, $keyValue[0], $keyValue[1]);
      }
    }
    return $query;
  }

  protected function onIndexFilterField($query, $fieldName, $fieldValue)
  {
    if ($fieldName == '*') {
      $query = $this->onIndexFilterWildcard($query, $fieldValue);
    } else {
      $query = $query->where($fieldName, 'like', '%' . $fieldValue . '%');
    }
    return $query;
  }

  protected function onIndexFilterWildcard($query, $fieldValue)
  {
    $query = $query->where(function ($q) use ($fieldValue) {
      foreach ($this->filterFields as $i => $fieldName) {
//        echo 'i='.$i.'  fieldName='.$fieldName.PHP_EOL;
        if ($i == 0) {
          if (strpos($fieldName, '.') === false) {
            $q = $q->where($fieldName, 'like', '%' . $fieldValue . '%');
          } else {
//            $q->whereRaw($fieldName . ' like ?', ['%' . $fieldValue . '%']);
            $relationAndField = explode(',', $fieldName);
//            $q->whereRaw($fieldName . ' like ?', ['%' . $fieldValue . '%'], 'or');
//            echo 'relationAndField: '.PHP_EOL;
//            echo '0: '.$relationAndField[0].PHP_EOL;
//            echo '1: '.$relationAndField[1].PHP_EOL;

            $q = $q->whereHas($relationAndField[0], function($q) use ($relationAndField, $fieldValue) {
              $q = $q->where($relationAndField[1], 'like', '%'.$fieldValue.'%');
            });

          }

        } else {
          if (strpos($fieldName, '.') === false) {
            $q = $q->orWhere($fieldName, 'like', '%' . $fieldValue . '%');
          } else {
            $relationAndField = explode('.', $fieldName);
//            $q->whereRaw($fieldName . ' like ?', ['%' . $fieldValue . '%'], 'or');
            $q = $q->orWhereHas($relationAndField[0], function($q) use ($relationAndField, $fieldValue) {
              $q = $q->where($relationAndField[1], 'like', '%'.$fieldValue.'%');
            });
          }
        }
      }
    });
    return $query;
  }

  protected function prepareIndexQuery($request, $query)
  {
    if (is_null($query)) {
      echo 'query is null' . PHP_EOL;
    }
    return $query;
  }

  protected function onIndexDataReady($request, $rows)
  {
    $isSelection = $request->has('select');
    if ($isSelection) {
      $relationSelects = $this->getRelationSelect($request);

      if (!empty($relationSelects)) {
        foreach ($rows as $row) {
          $rowData = '';
          foreach ($relationSelects as $selItem) {
            $rowData .= $selItem . '; ';
            $fieldName = str_replace('.', '_', $selItem);

            $rowData .= 'fieldName=' . $fieldName . '; ';
            $segs = explode('.', $selItem);
            $rel = $row;
            for ($i = 0; $i < count($segs); $i++) {
              $relName = $segs[$i];
              $rowData .= 'rel name = ' . $relName . PHP_EOL;

              if (!is_null($rel)) {
                $rel = $rel->{$relName};
              } else {
                $rel = '';
                break;
              }

            }
            $row->{'agent_name'} = $rel;
          }
          $row->rowData = $rowData;
        }
      }
    }
    return $rows;
  }

  protected function getRelationSelect($request)
  {
    $select = $request->get('select', '');
    $result = [];
    if (!empty($select)) {
      $selectItems = explode(',', $select);

      $result = array_values(
        array_filter($selectItems, function ($item) {
          return strpos($item, '.') !== false;
        })
      );
    }

    return $result;
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
  public function show(Request $request, $id)
  {
    if ($id == 0) {
      $record = $this->getBlankRecord();
    } else {
      $row = $this->getRow($id);
      $row = $this->onShowDataReady($request, $row);
      $record = $row->toArray();


    }

    return response()->json([
      'status' => true,
      'result' => [
        'data' => $record
      ]
    ]);
  }

  protected function onShowDataReady($request, $row) {
    return $row;
  }
  public function getRow($id)
  {
    $row = $this->model->find($id);
    return $row;
  }

  //****************
  //    Update
  //****************
  public function update(Request $request, $id)
  {
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

  protected function onUpdateComplete($request, $row)
  {
  }

  protected function getInput($rules) {
    $input = \Input::only(array_keys($rules));
    return $input;
  }
}