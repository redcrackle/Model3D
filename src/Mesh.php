<?php

namespace Model3D;


class Mesh {

  public $name = 'default_mesh';
  public $uuid;
  public $material = NULL;

  public $face_data = '';

  public $error = '';

  public function __construct($text, $materials = array()) {
    switch (gettype($text)) {
      case 'string':
        $this->createMeshFromText($text, $materials);
        break;
      case 'array':
        $this->createMeshFromArray($text);
        break;
      default:
        $this->error = 'Could not parse the mesh information.';
        return;
    }
  }

  private function createMeshFromText($text, $materials = array()) {
    $lines = explode(PHP_EOL, $text);

    foreach ($lines as $line) {
      $line = trim($line);

      if (strpos($line, 's ') === 0) {
        $this->face_data .= PHP_EOL . $line;
      }
      elseif (strpos($line, 'f ') === 0) {
        $this->face_data .= PHP_EOL . $line;
      }
      elseif (strpos($line, 'usemtl ') === 0) {
        /**
         * @var Material
         */
        $this->material = $materials[trim(substr($line, 7))];
      }
    }
  }

  private function createMeshFromArray($input) {
    if ($input['type'] != 'mesh') {
      $this->error = 'Supplied array does not represent a mesh.';
      return;
    }

    if (!empty($input['name'])) {
      $this->name = $input['name'];
    }

    if (!empty($input['data'])) {
      $this->face_data = $input['data'];
    }

    if (!empty($input['material'])) {
      $material = new Material($input['material']);
      if ($error = $material->getError()) {
        $this->error = $error;
        return;
      }
    }
  }

  public function getError() {
    return $this->error;
  }

  public function toJsonObject($vertices_length, $normals_length, $uvs_length) {
    $output = new \stdClass();

    $output->uuid = $this->uuid;

    if (!empty($this->face_data)) {
      $vertex_index = array();
      $uv_index = array();
      $normal_index = array();

      $lines = explode(PHP_EOL, $this->face_data);
      foreach ($lines as $line) {
        $line = trim($line);
        $words = explode(" ", $line);
        if ($words[0] == 'f' && sizeof($words) >= 3) {
          $index = 0;
          foreach ($words as $word) {
            if (!$index) {
              continue;
            }

            $vals = explode('/', $word);

            $v = $vals[0];
            $v = ($v < 1) ? $v + $vertices_length + 1 : $v;
            $vertex_index[] = $v;

            if (!empty($vals[1])) {
              $t = $vals[1];
              $t = ($t < 1) ? $t + $uvs_length + 1 : $t;
              $uv_index[] = $t;
            }

            if (!empty($vals[2])) {
              $n = $vals[2];
              $n = ($n < 1) ? $n + $normals_length + 1 : $n;
              $normal_index[] = $n;
            }
          }
        }
      }
    }


  }
}
