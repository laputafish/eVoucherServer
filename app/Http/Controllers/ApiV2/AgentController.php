<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use Illuminate\Http\Request;

use Bouncer;

class AgentController extends BaseModuleController
{
	protected $modelName = 'Agent';
	protected $orderBy = 'name';
	
	protected $filterFields = [
		'name',
		'alias',
		'tel_no',
		'fax_no',
		'web_url',
		'email'
	];
	
	protected $updateRules = [
		'name' => 'string',
		'alias' => 'nullable|string',
		'contact' => 'nullable|string',
		'tel_no' => 'nullable|string',
		'fax_no' => 'nullable|string',
		'web_url' => 'nullable|string',
		'email' => 'nullable|string',
		'remark' => 'nullable|string'
	];
	
	protected $storeRules = [
		'name' => 'string',
		'alias' => 'nullable|string',
		'contact' => 'nullable|string',
		'tel_no' => 'nullable|string',
		'fax_no' => 'nullable|string',
		'web_url' => 'nullable|string',
		'email' => 'nullable|string',
		'remark' => 'nullable|string'
	];
	
	
	public function getBlankRecord()
	{
		return [
			'id' => 0,
			'name' => '',
			'alias' => '',
			'contact' => '',
			'teL_no' => '',
			'fax_no' => '',
			'web_url' => '',
			'email' => '',
			'remark' => ''
		];
	}
	
	protected function onStoring($input)
	{
		$result = $input;
		if ($this->user->isNotA('supervisor')) {
			$result['user_id'] = $this->user->id;
		}
		return $result;
	}
	
	protected function onIndexDataReady($request, $rows)
	{
		foreach ($rows as $row) {
			$row->voucher_count = $row->vouchers()->count();
		}
		return $rows;
	}
	
	public function destroy($id)
	{
		$status = true;
		$result = [];
		
		$agent = $this->model->find($id);
		$voucherCount = $agent->vouchers()->count();
		
		if ($voucherCount > 0) {
			$status = false;
			$vouchers = $agent->vouchers()->select('description', 'created_at')->get();
			$result = [
				'messageTag' => 'related_vouchers_exists',
				'message' => 'Related vouchers exists!',
				'data' => $vouchers
			];
		} else {
			$agent->delete();
		}
		
		return response()->json([
			'status' => $status,
			'result' => $result
		]);
	}
	
	//****************
	//    Index
	//****************
	public function index(Request $request)
	{
		Bouncer::refreshFor($this->user);
		
//		echo 'user name = ' . $this->user->name . PHP_EOL;
//		echo 'is supervisor = ' . ($this->user->isA('supervisor') ? 'yes' : 'no') . PHP_EOL;
//		echo 'not supervisor = ' . ($this->user->isNotA('supervisor') ? 'yes' : 'no') . PHP_EOL;
		

		
		$query = $this->model;
		if ($this->user->isNotA('supervisor')) {
//			echo 'not supervisor.'.PHP_EOL;
			$query = $query->whereUserId($this->user->id);
		} else {
//			echo 'is supervisor.'.PHP_EOL;
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
			$lastPage = ceil($totalCount / $limit);
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
//			echo 'sql: '.PHP_EOL;
//			echo $query->toSql().PHP_EOL;
//			return 'ok 1111';
			$data = $query->get();
			
//			echo 'count = '.$query->count().PHP_EOL;
			$result = $this->onIndexDataReady($request, $data);
		}
		
		return response()->json([
			'status' => true,
			'result' => $result
		]);
	}
	
}