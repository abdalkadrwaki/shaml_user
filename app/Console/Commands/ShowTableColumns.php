<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShowTableColumns extends Command
{
    protected $signature = 'table:columns {table}';  // اسم الأمر مع وسيط اسم الجدول
    protected $description = 'عرض جميع أعمدة الجدول المحدد باستخدام DB';

    public function handle()
    {
        $table = $this->argument('table');  // استرجاع اسم الجدول من المستخدم

        // التحقق من وجود الجدول في قاعدة البيانات
        $columns = DB::select("SHOW COLUMNS FROM {$table}");

        if (empty($columns)) {
            $this->error("الجدول '{$table}' غير موجود في قاعدة البيانات.");
            return;
        }

        $this->info("الأعمدة في جدول '{$table}':");

        // عرض الأعمدة
        foreach ($columns as $column) {
            $this->line("- " . $column->Field);
        }
    }
}
