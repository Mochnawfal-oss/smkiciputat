<?php

use App\Models\Teacher;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$all = Teacher::query()->get();
echo "all={$all->count()} published=".Teacher::published()->count().PHP_EOL;
foreach ($all as $t) {
    echo $t->id.'|'.$t->name.'|'.$t->status.'|'.($t->published_at ? $t->published_at->toDateTimeString() : 'null').'|sort='.$t->sort_order.PHP_EOL;
}
