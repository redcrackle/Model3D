<?php

namespace Model3D;


class Group {

  public $name = 'default';
  public $uuid;
  public $meshes = array();

  public $vertex_count = 0;
  public $normal_count = 0;
  public $texture_count = 0;

  public $error = '';

  public function __construct($input, $materials = array(), $vertex_count = 0, $normal_count = 0, $texture_count = 0) {
    switch (gettype($input)) {
      case 'string':
        $this->createGroupFromText($input, $materials, $vertex_count, $normal_count, $texture_count);
        break;
      case 'array':
        $this->createGroupFromArray($input);
        break;
      default:
        $this->error = 'Could not parse the group information.';
        return;
    }
  }

  public function getError() {
    return $this->error;
  }

  private function createGroupFromText($text, $materials = array(), $vertex_count = 0, $normal_count = 0, $texture_count = 0) {
    $lines = explode(PHP_EOL, $text);

    $this->vertex_count = $vertex_count;
    $this->normal_count = $normal_count;
    $this->texture_count = $texture_count;

    $mesh_text = '';
    foreach ($lines as $line) {
      $line = trim($line);

      if (strpos($line, 'g ') === 0) {
        $this->name = trim(substr($line, 2));
      }
      elseif ($line == 'g') {
        $this->name = 'default';
      }
      elseif (strpos($line, 'usemtl ') === 0) {
        // Start of a new mesh.
        // Store the old mesh first.
        if (!empty($mesh_text)) {
          $mesh = new Mesh($mesh_text, $materials);
          if ($error = $mesh->getError()) {
            $this->error = $error;
            return;
          }

          $this->meshes[] = $mesh;
        }

        $mesh_text = $line;
      }
      elseif (!empty($line) && strpos($line, '#') !== 0) {
        $mesh_text .= PHP_EOL . $line;
      }
    }

    if (!empty($mesh_text)) {
      $mesh = new Mesh($mesh_text, $materials);
      if ($error = $mesh->getError()) {
        $this->error = $error;
        return;
      }

      $this->meshes[] = $mesh;
    }
  }
}
