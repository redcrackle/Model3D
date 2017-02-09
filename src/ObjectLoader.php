<?php

namespace Model3D;


class ObjectLoader {

  public $objects;

  public $error = '';

  public function __construct($filename, $directory) {
    if (!file_exists($directory . '/' . $filename)) {
      $this->error = 'File ' . $filename . ' doesn\'t exist.';
      return;
    }

    $handle = fopen($directory . '/' . $filename, 'r');
    $object = NULL;
    $text = '';
    $materials = array();
    $material_filename = '';
    while (($line = fgets($handle)) !== FALSE) {
      $line = trim($line);

      if (strpos($line, 'o ') === 0) {
        // Start of a new object.
        // Store the old object first.
        if (!empty($text)) {
          $object = new Object($text, $materials[$material_filename], $directory);
          if ($error = $object->getError()) {
            $this->error = $error;
            return;
          }

          $this->objects[] = $object;
        }

        $text = $line;
      }
      elseif (strpos($line, 'mtllib ') === 0) {
        $material_filename = trim(substr($line, 7));
        if (empty($materials[$material_filename])) {
          $materialLoader = new MaterialLoader($material_filename, $directory);
          if ($error = $materialLoader->getError()) {
            $this->error = $error;
            return;
          }

          $materials[$material_filename] = $materialLoader->materials;
        }
      }
      elseif (!empty($line) && strpos($line, '#') !== 0) {
        $text .= PHP_EOL . $line;
      }
    }

    // It's the end of file. Create a new object.
    if (!empty($text)) {
      $object = new Object($text, $materials[$material_filename], $directory);
      if ($error = $object->getError()) {
        $this->error = $error;
        return;
      }

      $this->objects[] = $object;
    }
  }

  public function getError() {
    return $this->error;
  }
}
