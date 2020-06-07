<?php namespace App\Helpers;

class SmtpServerHelper {
	public static function getConfig($server) {
		return [
			'driver' => $server['mail_driver'],
			'host' => $server['mail_host'],
			'port' => $server['mail_port'],
			'username' => $server['mail_username'],
			'password' => $server['mail_password'],
			'encryption' => $server['mail_encryption'],
			'from' => [
				'address' => $server['mail_from_address'],
				'name' => $server['mail_from_name']
			],
      'stream' => [
        'ssl' => [
          'allow_self_signed' => true,
          'verify_peer' => false,
          'verify_peer_name' => false,
        ],
      ],
		];
	}
}