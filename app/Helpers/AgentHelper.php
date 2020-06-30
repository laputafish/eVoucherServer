<?php namespace App\Helpers;

use App\Models\Agent;

class AgentHelper {
	public static function checkCreateSystemAgent() {
		if (!Agent::whereId(0)->exists()) {
			$agent = Agent::create([
				'user_id' => 0,
				'name' => 'System',
				'alias' => 'system'
			]);
			$agent->id = 0;
			$agent->save();
		}
	}
	
	public static function getSystemSmtpServers() {
		$systemAgent = Agent::whereId(0)->first();
		return $systemAgent->smtpServers;
	}
}