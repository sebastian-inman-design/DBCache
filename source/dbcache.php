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


class DBCache {

  /**
	 * Reference to the database configuration
	 *
	 * @var array
	 */

  protected static $conf;

  /**
	 * Reference to the established database connection
	 *
	 * @var void
	 */

  protected static $mysqli;

  /**
	 * Reference to the temporary cached file
	 *
	 * @var string
	 */

  protected static $tempFile;

  /**
	 * Reference to the temporary cached files path
	 *
	 * @var string
	 */

  protected static $tempPath;

  /**
	 * Reference to the cached file handler
	 *
	 * @var void
	 */

  protected static $tempAuth;

  /**
	 * Reference to contents of the cached file
	 *
	 * @var array
	 */

  protected static $cache;


  /**
	 * Initialize the DBCache Class
	 *
	 * @param	array     $conf = array()    an array of database connection info
   * @param string    $path = "cache/"     server location to store cache files
	 */

  public function __construct($config = array(), $path = "cache/") {

    /* set the temporary path to store cached data */
    if(!isset(self::$tempPath)) self::$tempPath = $path;

    /* establish a secure database connection */
    if(!isset(self::$conf)) self::$conf = $config;

  }


  /**
	 * Begin the caching process
   *
   * The system will check to see if a cache of the passed query already exists
   * in the cache directory. If it finds a matching file, the system will ignore
   * the database query and load data from the cached file instead, unless that
   * cached file has reached its maximum lifespan, at which point the system will
   * call the database query again and replace the existing cached file with the
   * new database response.
   *
   * If the system does not detect an existing cached file, it will perform the
   * database query like normal, then attempt to create a new cache file of the
   * response so the system can use that for future requests.
	 *
	 * @param	string    $query = NULL           MySQL query to request from database
   * @param string    $file                   name of the temporary file to create
   * @param string    $extension = ".json"    extension of the file to create
   * @param int       $timeout = 1800         time in seconds to keep cached files
	 */

  public function cache($query = NULL, $file, $extension = ".json", $timeout = 1800) {

    /* ensure the cache directory exists and is writable */
    if(file_exists(self::$tempPath) && is_writable(self::$tempPath)) {

      /* set the temporary file location */
      self::$tempFile = self::$tempPath.$file.$extension;

      /* check if the cache file does not already exist or if it has expired */
      if(!file_exists(self::$tempFile) || ((time() - filemtime(self::$tempFile)) > $timeout)) {

        /* create a new local cache file */
        self::$tempAuth = fopen(self::$tempFile, "w");
        fwrite(self::$tempAuth, json_encode(self::response($query)));
        fclose(self::$tempAuth);

      }

      /* ensure we can read the cached file */
      if(is_readable(self::$tempFile)) {

        /* get the contents of the local cached file */
        self::$tempAuth = fopen(self::$tempFile, "r");
        self::$cache = fread(self::$tempAuth, filesize(self::$tempFile));
        fclose(self::$tempAuth);

        /* return the cached response */
        return json_decode(self::$cache, TRUE);

      }else{

        /* return the response from the database query */
        return self::response($query);

      }

    }else{

      /* create cache directory */
      mkdir(self::$tempPath);

      /* return the response from the database query */
      return self::response($query);

    }

    /* reset cache variables */
    self::$tempFile = NULL;
    self::$cache = [];

    /* close the database connection */
    self::$mysqli->close();

  }


  /**
	 * Clear the current cache files
   *
   * Forces the system to remove all existing local cache files that may exist.
   * The system will then recache any queries being passed into the constructor.
	 *
	 * @return	void
	 */

  public function clear() {

    /* only run this function if the cache directory exists and contains files */
    if(file_exists(self::$tempPath) && count(glob(self::$tempPath."/*")) > 0) {

      /* loop through each found file */
      foreach(glob(self::$tempPath."/*") as $file) {

        /* delete the file from the system */
        if(is_file($file)) unlink($file);

      }

    }

  }


  /**
	 * Return query response from the database
   *
   * Handles the request to the database when performing queries and returns the
   * response after formatting it using fetch_assoc().
	 *
	 * @param	string    $query    MySQL query to request from database
   * @return array    $rows     Associated array of database tables
	 */

  private function response($query) {

    /* establish a secure mysqli connection to the database */
    if(!isset(self::$mysqli)) self::$mysqli = new mysqli($conf["host"], $conf["user"], $conf["pass"], $conf["table"]);

    /* make sure there's a response before continuing */
    if($response = self::$mysqli->query($query)) {

      /* loop through the database query response */
      while($row = $response->fetch_assoc()) $rows[] = $row;
      return $rows;

    }

  }

}
