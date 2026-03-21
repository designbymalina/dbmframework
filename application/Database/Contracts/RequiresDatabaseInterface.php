<?php

/**
 * Module: DbM DataTables
 * PHP library for efficient CRUD operations and high-performance database management.
 *
 * This software is proprietary and licensed.
 * Use of this software is subject to the terms of the DbM Platform License.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina
 * @license Proprietary
 *
 * @see /LICENSE_DBM_PLATFORM.txt
 * @link https://www.dbm.org.pl
 *
 * INFO: Interface for workers witch needs a database WorkerRunner -> ExampleWorker
 * This is an interface marker - there is no implementation in it, but what is important is what it is.
 */

declare(strict_types=1);

namespace Dbm\Database\Contracts;

interface RequiresDatabaseInterface {}
