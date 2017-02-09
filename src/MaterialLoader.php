<?php
/**
 * Created by PhpStorm.
 * User: neeravbm
 * Date: 12/25/15
 * Time: 9:21 AM
 */

namespace Model3D;


class MaterialLoader {

  public $materials;

  public $error = '';

  public function __construct($filename, $directory = '') {
    $found = FALSE;

    // Some OBJ files provide full relative pathname. Extract just the filename.
    $path_info = pathinfo($filename);
    $filename = $path_info['basename'];

    $files = file_scan_directory($directory, '/.*' . $filename . '$/', array('recurse' => TRUE));
    foreach ($files as $uri => $file) {
      if ($file->filename != $filename) {
        continue;
      }

      $found = TRUE;
      $handle = fopen($uri, 'r');
      $material = NULL;
      $text = '';
      while (($line = fgets($handle)) !== FALSE) {
        $line = trim($line);

        // Start of a new material.
        // Store the old material first.
        if (strpos($line, 'newmtl ') === 0) {
          // Start a new material.
          // First close the previous material.
          if (!empty($text)) {
            $material = new Material($text, $directory);
            if ($error = $material->getError()) {
              $this->error = $error;
              return;
            }

            $this->materials[$material->name] = $material;
          }

          $text = $line;
        }
        elseif (!empty($line) && strpos($line, '#') !== 0) {
          $text .= PHP_EOL . $line;
        }
      }

      // End of file. Create the material.
      if (!empty($text)) {
        $material = new Material($text, $directory);
        if ($error = $material->getError()) {
          $this->error = $error;
          return;
        }

        $this->materials[$material->name] = $material;
      }
    }

    if (!$found) {
      $this->error = 'File ' . $filename . ' doesn\'t exist.';
      return;
    }
  }

  public function getError() {
    return $this->error;
  }
}
