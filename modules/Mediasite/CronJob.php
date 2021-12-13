<?php

/**
 */
class MediasiteBackup_Mediasite_CronJob extends Bss_Cron_Job
{
    const PROCESS_ACTIVE_JOBS_EVERY = 300; // 5 minutes
    const BACKUP_JOBS_LIMIT = 100;
    
    public function run ($startTime, $lastRun, $timeDelta)
    {
        if ($timeDelta >= self::PROCESS_ACTIVE_JOBS_EVERY)
        {
            $app = $this->getApplication();
            
            set_time_limit(0);
            
            $verifier = new MediasiteBackup_Mediasite_Verifier($app);
            $verifier->execute(self::BACKUP_JOBS_LIMIT);

            $mover = new MediasiteBackup_Mediasite_Mover($app);
            $mover->execute(self::BACKUP_JOBS_LIMIT);
            
            
            return false;
        }
    }
}
