<?php
namespace DBCO\Worker\Application\Repositories;

use DBCO\Worker\Application\Models\GeneralTaskList;

interface GeneralTaskGetRepository
{
    /**
     * Retrieve the signed general tasks for pass through usage.
     *
     * The returned object contains the response body from the source plus headers
     * that contain and are required to verify the body contents signature.
     *
     * @return GeneralTaskList
     *
     * @throws Exception
     */
    public function getGeneralTasks(): GeneralTaskList;
}
