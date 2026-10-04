<?php

namespace App\Exceptions\Service\Database;

use App\Exceptions\DisplayException;
use Illuminate\Http\Response;

class DatabaseImportInProgressException extends DisplayException
{
    public function __construct()
    {
        parent::__construct('This database is currently being imported into. Please wait for the import to finish before trying again.');
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
