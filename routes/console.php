<?php

use Illuminate\Support\Facades\Schedule;

// 毎日9時（日本時間）にZenn記事を取得
Schedule::command('zenn:fetch')->dailyAt('09:00')->timezone('Asia/Tokyo');
