<?php namespace App\Helpers;

use Illuminate\Support\Carbon;
use App\Models\Command;
use App;

use App\Helpers\LogHelper;

class CommandHelper
{
  protected static $COOL_PERIOD = 10;

  public static function start($commandName, $handler)
  {
    \Carbon\Carbon::setLocale(config('app.locale'));
    App::setLocale('hk');

    // check and create command record if not exist
    $command = Command::whereName($commandName)->first();


    if (is_null($command)) {
      $command = Command::create([
        'name' => $commandName,
        'enabled' => 1,
        'loop' => 1,
        'mode' => 'auto',
        'forced' => 0,
        'message' => 'Idle.',
        'reset_log' => 1
      ]);
    }

    $whoami = str_replace("\\", '_', system('whoami'));
    $lckFile = storage_path($whoami.'.lck');
    LogHelper::log('Current User: ' . $whoami);

    // if lock file exists, another instance may be running
    if (file_exists($lckFile)) {
      LogHelper::log('lock file exists.');
//      system('ps -A > /tmp/generate_taxforms_' . $whoami . '.log');
      LogHelper::log('Another instance already running.');
      LogHelper::Log('Command is forced: '.($command->forced ? 'yes' : 'no'));
      if ($command->forced) {
        LogHelper::log('Remove lock file.');
        unlink($lckFile);
        $command->forced = false;
        $command->save();
      } else {
        LogHelper::log('Quit now.');
        return;
      }
    }

    LogHelper::log('Check if command is enabled: '.($command->enabled ? 'yes' : 'no'));
    if (!$command->enabled) {
      LogHelper::log('Lock file removed.');
//      if (file_exists($lckFile)) {
//        unlink($lckFile);
//      }
      LogHelper::log('Set command message => "Unexpected Quit: Command not enabled!"');
      $command->message = 'Quit. (Command not enabled)';
      $command->save();
      return;
    }

    if (isset($command->last_checked_at)) {
      $now = now();
      $lastCheckedAt = Carbon::parse($command->last_checked_at);
      $durationPassed = $now->diffInSeconds($lastCheckedAt);
      if ($durationPassed < static::$COOL_PERIOD && ($command->mode == 'auto')) {
        $msg = "duration since last checking < ".static::$COOL_PERIOD."sec => quit.";
//        unlink($lckFile);
        $command->message = 'Unexpected quit: '.$msg;
        $command->save();
        LogHelper::log($msg);
        return;
      }
    }

    // create lock file
    $fp = fopen($lckFile, 'w');
    fclose($fp);

    while (true) {
      $now = now();
      $command->last_checked_at = $now;
      $command->message = 'Idle.';
      $command->save();

      //******************
      // Command Entry
      //******************
      $res = $handler($command);

      if(!$res) {
        $command->message = 'Unexpected quit: Handler returns false.';
        $command->save();
        unlink($lckFile);
        LogHelper::log('Handler returns false!');
        break;
      }
      sleep(1);

      // Check enabled
      $command = Command::whereName($commandName)->first();
      LogHelper::log('Check if command is enabled: '.($command->enabled ? 'yes' : 'no'));

      // if command not enabled
      if (!$command->enabled) {
        $command->message = 'Quit. (Command not enabled)';
        $command->save();
        unlink($lckFile);
        logConsole('messages.command_not_enabled');
        break;
      }

      // if looping is disabled
      if (!$command->loop) {
        $command->message = 'Unexpected Quit: Command loop not enabled!';
        $command->save();
        unlink($lckFile);
//        logConsole('messages.command_loop_not_enabled');
        break;
      }
    }
  }
}