<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require __DIR__ . "/../bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\{PortalMessage, User};
use Illuminate\Support\Facades\Schema;
echo Schema::hasTable("portal_messages") ? "table ok\n" : "no table\n";
$parent = User::where("role", "parent")->first();
$teacher = User::where("role", "teacher")->first();
if (!$parent || !$teacher) { echo "missing users\n"; exit(1); }
try {
  $msg = PortalMessage::create(["sender_id"=>$parent->id,"recipient_id"=>$teacher->id,"body"=>"test","subject"=>"Test"]);
  echo "created {$msg->id}\n";
  $msg->delete();
} catch (Throwable $e) { echo "ERR: ".$e->getMessage()."\n"; }
