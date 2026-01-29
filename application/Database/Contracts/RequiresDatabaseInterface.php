<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * INFO: Interface for workers witch needs a database WorkerRunner -> ExampleWorker
 * This is an interface marker - there is no implementation in it, but what is important is what it is.
 */

declare(strict_types=1);

namespace Dbm\Database\Contracts;

interface RequiresDatabaseInterface {}
