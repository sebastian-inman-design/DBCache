<?php

/**
 * DBCache by Sebastian Inman
 *
 * A small PHP library that caches MySQL database responses by
 * converting the response to a JSON file stored on the server.
 *
 * @version 1.0.0
 * @copyright Copyright © 2017 Sebastian Inman (http://sebastianinman.com)
 * @author Sebastian Inman <hello@sebastianinman.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/* include the dbcache file */
include_once('dbcache.php');

$config = array(
  "host"  => "localhost", /* enter your hostname or ip address */
  "user"  => "username",  /* enter your database username */
  "pass"  => "password",  /* enter your database password */
  "table" => "dbcache"    /* enter your database table */
);

$dbcache = new DBCache($config);

$data1 = $dbcache->cache('SELECT * FROM test WHERE id = 1', 'test1');
$data2 = $dbcache->cache('SELECT * FROM test WHERE id = 2', 'test2');
