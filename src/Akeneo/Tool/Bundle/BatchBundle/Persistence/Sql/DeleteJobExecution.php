<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteJobExecution
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function olderThanDays(int $days): int
    {
        if ($days < 0) {
            throw new \InvalidArgumentException(sprintf('Number of days should be positive or equals to 0, "%s% given', $days));
        }

        $endTime = new \DateTime();
        $endTime->modify(sprintf('- %d days', $days));

        $query = <<<SQL
            DELETE FROM akeneo_batch_job_execution WHERE id IN (
                SELECT id FROM (
                    SELECT id 
                    FROM akeneo_batch_job_execution
                    WHERE akeneo_batch_job_execution.end_time < :end_time AND akeneo_batch_job_execution.id NOT IN (
                        SELECT MAX(last_job_execution.id) 
                        FROM akeneo_batch_job_execution last_job_execution 
                        WHERE last_job_execution.status = :status 
                        GROUP BY last_job_execution.job_instance_id
                    )
                ) as job_execution_to_remove
            )
SQL;

        $numberDeletedJobExecution = $this->connection->executeUpdate(
            $query,
            ['end_time' => $endTime, 'status' => BatchStatus::COMPLETED],
            ['end_time' => Type::DATETIME]
        );

        return $numberDeletedJobExecution;
    }
}
