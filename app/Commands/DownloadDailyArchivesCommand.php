<?php declare(strict_types=1);

/**
 * This file is part of The Broadcastify Downloader project, by Theodore R. Smith.
 *
 * Copyright © 2020 Theodore R. Smith <theodore@phpexperts.pro>.
 *   GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690
 *   https://github.com/CovidResearch/BroadcastifyDownloader
 *
 * This file is licensed under the MIT License.
 */

namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;
use PHPExperts\ConsolePainter\ConsolePainter;
use PHPExperts\RESTSpeaker\RESTAuth;
use PHPExperts\RESTSpeaker\RESTSpeaker;
use RuntimeException;

class DownloadDailyArchivesCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'download {feedId : e.g., 28416 for Houston FD EMS} {date : YYYYMMDD}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Downloads a feed\'s archives for a particular date.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cp = new ConsolePainter();

        $feedId = $this->argument('feedId');
        $dateString = $this->argument('date');

        try {
            // Attempt to parse the date.
            $carbon = Carbon::parse($dateString);
        } catch (\Exception $e) {
            $this->error("Could not parse '$dateString' into a Carbon date. Try YYYYMMDD.");
            exit(-1);
        }

        // Attempt to find the feed's archive data.
        $usaDate = $carbon->format('m/d/Y');
        $isoDate = $carbon->format('Ymd');
        $isoDateDashes = $carbon->format('Y-m-d');
        $epochNow = Carbon::now()->timestamp;
        $archiveJSONURL = "https://m.broadcastify.com/archives/ajax.php?feedId={$feedId}&date={$usaDate}&_={$epochNow}";

        $noAuth = new class(RESTAuth::AUTH_NONE) extends RESTAuth {
            protected function generateOAuth2TokenOptions(): array
            {
            }

            protected function generatePasskeyOptions(): array
            {
            }
        };

        try {
            $api = new RESTSpeaker($noAuth);
            $archiveInfo = $api->get($archiveJSONURL);

            if (!property_exists($archiveInfo, 'data')) {
                throw new RuntimeException('The Broadcastify API may have changed.');
            }
        } catch (\Exception $e) {
            $this->error("Couldn't download the archive JSON for feed '$feedId' for $usaDate: " . $e->getMessage());
        }

        $storagePath = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
        // Download the MP3 archives...
        foreach (array_reverse($archiveInfo->data) as $info) {
            [$mp3Id, $startTime, $endTime] = $info;

            $mp3URL = "https://m.broadcastify.com/archives/download/$mp3Id";
            $time = str_replace(':', '', Carbon::parse($startTime)->format('H:i') . '_' . Carbon::parse($endTime)->format('H:i'));
            $destMP3 = "{$feedId}-{$isoDate}-{$time}.mp3";
            $mp3Path = "broadcastify/$feedId/$isoDateDashes/$destMP3";
            $mp3FullPath = "{$storagePath}{$mp3Path}";

            $ch = curl_init($mp3URL);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Cookie: ' . env('BROADCASTIFY_COOKIE')]);
            //curl_setopt($ch, CURLOPT_VERBOSE, true);
            $data = curl_exec($ch);
            curl_close($ch);
            Storage::disk('local')->put($mp3Path, $data);

            // Avoid memory leaks.
            unset($data);
            $data = null;
            gc_collect_cycles();

            dump("Downloaded to $mp3Path...");
        }
    }

    /**
     * Define the command's schedule.
     *
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
