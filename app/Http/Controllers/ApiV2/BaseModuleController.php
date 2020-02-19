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
    // check if for selection purpose (no much details required for selection purpose)
    $isSelection = $request->has('select');

    $query = $this->model;
    $query = $this->prepareIndexQuery($request, $query);
    $query = $this->onIndexOrderBy($query);
    $query = $this->onIndexWith($query);
    $query = $this->onIndexJoin($query);

    // field selection

    if ($isSelection) {
      $selectItems = explode(',', $request->get('select'));
      $query = $query->select(
        array_values(array_filter($selectItems, function ($item) {
          return strpos($item, '.') === false;
        }))
      );
    }

    // Filter
    $filter = $request->get('filter', '');
    if (!empty($filter)) {
      $query = $this->onIndexFilter($query, $filter);
    }

    if ($request->has('page')) {
      $limit = $request->get('limit', 20);
      $pagedData = $query->paginate($limit);
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

  protected function onIndexJoin($query) {
    return $query;
  }
  protected function onIndexWith($query)
  {
    if (!empty($this->indexWith)) {
      $query = $query->with($this->indexWith);
    }
    $query = $query->with('agent');
    return $query;
  }

  protected function onIndexFilter($query, $filter)
  {
    $filterItems = explode('|', $filter);
    foreach ($filterItems as $filterItem) {
      $keyValue = explode(':', $filterItem);
      $query = $this->onIndexFilterField($query, $keyValue[0], $keyValue[1]);
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
        if ($i == 0) {
          if (strpos($fieldValue, '.') === false) {
            $q->where($fieldName, 'like', '%' . $fieldValue . '%');
          } else {
            $q->whereRaw($fieldName . ' like ?', ['%' . $fieldValue . '%']);
          }

        } else {
          if (strpos($fieldValue, '.') === false) {
            $q->orWhere($fieldName, 'like', '%' . $fieldValue . '%');
          } else {
            $q->whereRaw($fieldName . ' like ?', ['%' . $fieldValue . '%'], 'or');
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
              $rel = $rel->{$relName};

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

}