<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;

use Illuminate\Http\Request;
use Bouncer;

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
  	Bouncer::refreshFor($this->user);
  	
//  	echo 'user name = '.$this->user->name.PHP_EOL;
//  	echo 'supervisor = '.($this->user->isNotA('supervisor') ? 'yes' : 'no').PHP_EOL;
//  	return 'ok';
    $query = $this->model;
    if ($this->user->isNotA('supervisor')) {
    	$query = $query->whereUserId($this->user->id);
    }
    $query = $this->prepareIndexQuery($request, $query);
    $query = $this->onIndexOrderBy($query);
    $query = $this->onIndexWith($query);
    $query = $this->onIndexJoin($query);
    $query = $this->onIndexFilter($request, $query);

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

  protected function onIndexFilter($request, $query, $filterFields=[])
  {
    $filter = $request->get('filter', '');
    if (!empty($filter)) {
      $filterItems = explode('|', $filter);
      foreach ($filterItems as $filterItem) {
        $keyValue = explode(':', $filterItem);
        $query = $this->onIndexFilterField($query, $keyValue[0], $keyValue[1], $filterFields);
      }
    }
    return $query;
  }

  protected function onIndexFilterField($query, $fieldName, $fieldValue, $filterFields=[])
  {
    if ($fieldName == '*') {
      $query = $this->onIndexFilterWildcard($query, $fieldValue, $filterFields);
    } else {
      $query = $query->where($fieldName, 'like', '%' . $fieldValue . '%');
    }
    return $query;
  }

  protected function onIndexFilterWildcard($query, $fieldValue, $filterFields=[])
  {
  	if (empty($filterFields)) {
  		$filterFields = $this->filterFields;
	  }
	  
//	  foreach ($this->filterFields as $i => $fieldName) {
//	    echo 'fieldName #'.$i.': '.$fieldName.PHP_EOL;
//	  }
//	  return 'ok';
	  
    $query = $query->where(function ($q) use ($fieldValue, $filterFields) {
    	
      foreach ($filterFields as $i => $fieldName) {
        if ($i == 0) {
          if (strpos($fieldName, '.') === false) {
            $q = $q->where($fieldName, 'like', '%' . $fieldValue . '%');
          } else {
            $relationAndField = explode(',', $fieldName);

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
    if (!empty($this->orderBy)) {
      $query = $query->orderBy($this->orderBy, $this->orderDirection);
    }
    return $query;
  }

  //****************
  //    Show
  //****************
  protected function getRow($id)
  {
    $query = $this->model;
    $query = $this->onShowWith($query);
    $row = $query->find($id);
    
    return $row;
  }

  public function show(Request $request, $id)
  {
    if ($id == 0) {
      $record = $this->getBlankRecord();
    } else {
      $this->beforeShowData($id); // empty code fields if code count is 0
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
  
  protected function onShowWith($query) {
    return $query;
  }

  protected function beforeShowData($id) {
  }

  protected function beforeShowDataReady($request, $row) {
    return $row;
  }


  //****************
  //    Update
  //****************
  public function update(Request $request, $id)
  {
    $row = $this->model->find($id);
    $input = $request->validate($this->updateRules);
    $input = $this->onUpdating($input, $row);
    $row->update($input);
    $this->onUpdateCompleted($request, $row);

    $row = $this->getRow($id); //$this->model->find($id);

    return response()->json([
      'status' => true,
      'result' => $row
    ]);
  }
	
	public function store(Request $request) {
		$input = $this->getInput($this->storeRules);
		$validator = \Validator::make($input, $this->storeRules);
		if ($validator->fails()) {
			$errors = $validator->messages();
			return response()->json([
				'status' => false,
				'result' => $errors
			]);
		} else {
			$input = $this->onStoring($input);
			$newRow = $this->model->create($input);
			$id = $newRow->id;
			$this->onStoreCompleted($request, $newRow);
		}
		$responseRow = $this->getRow($id);
		return response()->json([
			'status' => true,
			'result' => $responseRow
		]);
	}
	
	protected function onStoring($input) {
  	if ($this->user->isNotA('supervisor')) {
  		$input['user_id'] = $this->user->id;
	  }
  	return $input;
	}

	protected function onStoreCompleted($request, $newRow) {
  }

  protected function onUpdating($input, $row=null) {
    return $input;
  }

  protected function onUpdateCompleted($request, $row)
  {
  }

  protected function getInput($rules) {
    $input = \Input::only(array_keys($rules));
    return $input;
  }
  
	
}