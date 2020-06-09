<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;

use Illuminate\Http\Request;

use Bouncer;
use App\Models\SmtpServer;

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
			'remark' => '',
      'smtp_servers' => [],
		];
	}

	protected function onShowWith($query) {
	  $query = $query->with('smtpServers');
	  return $query;
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

		$query = $this->model;
		if ($this->user->isNotA('supervisor')) {
			$query = $query->whereUserId($this->user->id);
		} else {
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
			$data = $query->get();
			$result = $this->onIndexDataReady($request, $data);
		}
		
		return response()->json([
			'status' => true,
			'result' => $result
		]);
	}

	protected function onAgentUpdated(Request $request, $row) {
	  $smtpServers = $request->get('smtp_servers');
	  $newSmtpServers = array_values(array_filter($smtpServers, function($server) {
	    return $server['id'] == 0;
    }));

	  $updatedSmtpServers = array_values(array_filter($smtpServers, function($server) {
	    return $server['id'] != 0;
    }));

	  $updatedSmtpServerIds = array_map(function($server) {
	    return $server['id'];
    }, $updatedSmtpServers);

	  $existingSmtpServerIds = $row->smtpServers()->pluck('id')->toArray();

	  // obsolate ids
    $obsolateIds = array_diff($existingSmtpServerIds, $updatedSmtpServerIds);

    // remove obsolate
    $row->smtpServers()->whereIn('id', $obsolateIds)->delete();

    // update existing
    for($i = 0; $i < count($updatedSmtpServers); $i++) {
      $server = $updatedSmtpServers[$i];
      $row->smtpServers()->where('id', $server['id'])->update([
        'description' => $server['description'],
        'mail_driver' => $server['mail_driver'],
        'mail_host' => $server['mail_host'],
        'mail_port' => $server['mail_port'],
        'mail_username' => $server['mail_username'],
        'mail_password' => $server['mail_password'],
        'mail_encryption' => $server['mail_encryption'],
        'mail_from_address' => $server['mail_from_address'],
        'mail_from_name' => $server['mail_from_name']
      ]);
    }

    // add new
    for($i = 0; $i < count($newSmtpServers); $i++) {
      $server = $newSmtpServers[$i];

      $newSmtpServer = new SmtpServer([
        'description' => $server['description'],
        'mail_driver' => $server['mail_driver'],
        'mail_host' => $server['mail_host'],
        'mail_port' => $server['mail_port'],
        'mail_username' => $server['mail_username'],
        'mail_password' => $server['mail_password'],
        'mail_encryption' => $server['mail_encryption'],
        'mail_from_address' => $server['mail_from_address'],
        'mail_from_name' => $server['mail_from_name']
      ]);
      $row->smtpServers()->save($newSmtpServer);
    }
  }

	protected function onStoreCompleted($request, $newRow) {
	  $this->onAgentUpdated($request, $newRow);
  }

  protected function onUpdateCompleted($request, $row) {
	  $this->onAgentUpdated($request, $row);
  }

  protected function getSmtpServers($id) {
	  $agent = $this->model->findOrFail($id);
	  return response()->json([
	    'status' => true,
      'result' => [
      	'tag' => empty($agent->alias) ? $agent->name : $agent->alias,
      	'smtpServers' => $agent->smtpServers
      ]
    ]);
  }
}