<?php

use Illuminate\Support\Facades\Schedule;

// 10トピックを2バッチに分けて10分間隔で取得する(JST 9:00 / 9:10)
Schedule::command('zenn:fetch --batch=1')->dailyAt('09:00')->timezone('Asia/Tokyo');
Schedule::command('zenn:fetch --batch=2')->dailyAt('09:10')->timezone('Asia/Tokyo');
