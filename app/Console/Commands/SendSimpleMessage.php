<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\SimpleMessageEvent;

class SendSimpleMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:simpleMessage {message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send simple message.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      $message = $this->argument('message');
      event(new SimpleMessageEvent($message));
        //
    }
}
