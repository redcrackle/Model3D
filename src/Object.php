<?php

namespace Model3D;


class Object {

  public $name = 'default';
  public $uuid;
  public $groups = array();

  public $vertex_data = '';

  public $error = '';

  public function __construct($text, $materials = array(), $directory = '') {
    switch (gettype($text)) {
      case 'string':
        $this->createObjectFromText($text, $materials, $directory);
        break;
      case 'array':
        $this->createObjectFromArray($text);
        break;
      default:
        $this->error = 'Could not parse the object information.';
        return;
    }
  }

  public function getError() {
    return $this->error;
  }

  private function createObjectFromText($text, $materials = array(), $directory = '') {
    $lines = explode(PHP_EOL, $text);

    $text = '';
    $vertex_count = 0;
    $normal_count = 0;
    $texture_count = 0;
    $has_data = FALSE;
    foreach ($lines as $line) {
      $line = trim($line);

      if (strpos($line, 'o ') === 0) {
        $this->name = trim(substr($line, 2));
      }
      elseif (strpos($line, 'v ') === 0) {
        $vertex_count += 1;
        $this->vertex_data .= PHP_EOL . $line;
      }
      elseif (strpos($line, 'vn ') === 0) {
        $normal_count += 1;
        $this->vertex_data .= PHP_EOL . $line;
      }
      elseif (strpos($line, 'vt ') === 0) {
        $texture_count += 1;
        $this->vertex_data .= PHP_EOL . $line;
      }
      elseif (strpos($line, 'g ') === 0 || $line == 'g') {
        // Start a new group.
        // First save the old one.
        if ($has_data) {
          $group = new Group($text, $materials, $vertex_count, $normal_count, $texture_count);
          if ($error = $group->getError()) {
            $this->error = $error;
          }

          $this->groups[] = $group;
        }

        $has_data = FALSE;
        $text = $line;
      }
      elseif (!empty($line) && strpos($line, '#') !== 0) {
        $has_data = TRUE;
        $text .= PHP_EOL . $line;
      }
    }

    // End of file. Create a mesh.
    if ($has_data) {
      $group = new Group($text, $materials, $vertex_count, $normal_count, $texture_count);
      if ($error = $group->getError()) {
        $this->error = $error;
      }

      $this->groups[] = $group;
    }
  }

  private function createObjectFromArray($input) {
    if ($input['type'] != 'object') {
      $this->error = 'Supplied array does not represent an object.';
      return;
    }

    if (!empty($input['name'])) {
      $this->name = $input['name'];
    }

    if (!empty($input['vertex_data'])) {
      $this->vertex_data = $input['vertex_data'];
    }

    if (!empty($input['meshes'])) {
      foreach ($input['meshes'] as $mesh_array) {
        $mesh = new Mesh($mesh_array);
        $this->meshes[] = $mesh;
      }
    }
  }

  public function toJsonObject() {
    $output = new \stdClass();

    $vertices = array();
    $normals = array();
    $uvs = array();
    if (!empty($this->vertex_data)) {
      $lines = explode(PHP_EOL, $this->vertex_data);
      foreach ($lines as $line) {
        $line = trim($line);
        $words = explode(" ", $line);
        if ($words[0] == 'v' && sizeof($words) == 4) {
          $vertices[] = $words[1];
          $vertices[] = $words[2];
          $vertices[] = $words[3];
        }
        elseif ($words[0] == 'vn' && sizeof($words) == 4) {
          $normals[] = $words[1];
          $normals[] = $words[2];
          $normals[] = $words[3];
        }
        elseif ($words[0] == 'vt' && sizeof($words) >= 3) {
          // (u, v, [w]): w is optional.
          $uvs[] = $words[1];
          $uvs[] = $words[2];
          /*if (!empty($words[3])) {
            $uvs[] = $words[3];
          }*/
        }
      }
    }

    foreach ($this->groups as $group) {
      list($group, $material) = $mesh->toJsonObject(sizeof($vertices), sizeof($normals), sizeof($uvs));
    }

    $output->uuid = $this->uuid;

    return $output;
  }
}
