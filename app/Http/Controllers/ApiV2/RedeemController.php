<?php namespace App\Http\Controllers\ApiV2;

class RedeemController extends BaseModuleController
{
	public function showRedeemPage($code) {
		return view('templates.redeem')->with('redeemCode',$code);
	}
}