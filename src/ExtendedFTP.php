<?php

namespace ExtendedFTP\App;

use Exception;

class ExtendedFTP {

  private $ftpConnection;
  private $ftpSession = false;
  private $blackList = array('.', '..');

  public function __construct($ftpHost = "") {
      if ($ftpHost != "") $this->ftpConnection = ftp_connect($ftpHost);
  }

  public function __destruct() {
      $this->disconnect();
  }

  public function connect($ftpHost) {     
      $this->disconnect();
      $this->ftpConnection = ftp_connect($ftpHost);
      return $this->ftpConnection;
  }

  public function login($ftpUser, $ftpPass) {
      if (!$this->ftpConnection) throw new Exception("Connection not established.", -1);
      $this->ftpSession = ftp_login($this->ftpConnection, $ftpUser, $ftpPass);
      return $this->ftpSession;
  }

  public function disconnect() {
      if (isset($this->ftpConnection)) {
          ftp_close($this->ftpConnection);
          unset($this->ftpConnection);
      }
  }

  public function sendRecursiveDirectory($localPath, $remotePath) {
      return $this->recursiveDirectory($localPath, $localPath, $remotePath);
  }

  private function recursiveDirectory($rootPath, $localPath, $remotePath) {
      $errorList = array();
      if (!is_dir($localPath)) throw new Exception("Invalid directory: $localPath");
      chdir($localPath);
      $directory = opendir(".");
      while ($file = readdir($directory)) {
          if (in_array($file, $this->blackList)) continue;
          if (is_dir($file)) {
              $errorList["$remotePath/$file"] = $this->makeDirectory("$remotePath/$file");
              $errorList[] = $this->recursiveDirectory($rootPath, "$localPath/$file", "$remotePath/$file");
              chdir($localPath);
          } else {
              $errorList["$remotePath/$file"] = $this->putFile("$localPath/$file", "$remotePath/$file");
          }
      }
      return $errorList;
  }

  public function makeDirectory($remotePath) {
      $error = "";
      try {
          ftp_mkdir($this->ftpConnection, $remotePath);
      } catch (Exception $e) {
          if ($e->getCode() == 2) $error = $e->getMessage(); 
      }
      return $error;
  }

  public function putFile($localPath, $remotePath) {
      $error = "";
      try {
          ftp_put($this->ftpConnection, $remotePath, $localPath, FTP_BINARY); 
      } catch (Exception $e) {
          if ($e->getCode() == 2) $error = $e->getMessage(); 
      }
      return $error;
  }

  public function emptyDirectoryRecursive($remoteDirectory){
    
  }
  
}