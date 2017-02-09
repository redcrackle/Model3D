<?php

namespace Model3D;


class Texture {

  public $name;
  public $uuid;
  public $map_path;
  public $blendu;
  public $blendv;
  public $bm;
  public $boost;
  public $cc;
  public $clamp;
  public $imfchan;
  public $mm;
  public $o;
  public $s;
  public $t;
  public $texres;
  public $reflectionType;

  public $error = 0;

  public function __construct($input, $directory = '') {
    switch (gettype($input)) {
      case 'string':
        $this->createTextureFromText($input, $directory);
        break;
      case 'array':
        $this->createTextureFromArray($input);
        break;
      default:
        $this->error = 'Could not parse the material information.';
        return;
    }

    if (empty($this->map_path)) {
      $this->error = 'Texture image in line ' . $input . ' not found.';
    }
  }

  public function getError() {
    return $this->error;
  }

  private function isImageFilename($filename, $directory) {
    $files = file_scan_directory($directory, '/.*' . $filename . '$/', array('recurse' => TRUE));
    // First look for file with the exact name.
    foreach ($files as $uri => $file) {
      if ($file->filename == $filename && @getimagesize($uri)) {
        return $uri;
      }
    }

    // There is no file with the exact name. Return FALSE.
    return FALSE;
  }

  private function createTextureFromText($text, $directory = '') {
    $words = explode(" ", $text);
    $current_option = '';
    $current_value = '';
    foreach ($words as $word) {
      $path_info = pathinfo($word);
      $word = $path_info['basename'];

      // Do this in case directory uses Windows format. pathinfo doesn't help then.
      $parts = explode('\\', $word);
      $word = array_pop($parts);

      if ($uri = $this->isImageFilename($word, $directory)) {
        $this->map_path = $uri;
        $this->name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $word);
      }
      else {
        if (strpos($word, '-') === 0) {
          // This is a new option name.
          if (!empty($current_option) && !empty($current_value)) {
            $this->$current_option = $current_value;
          }

          $current_option = ltrim($word, "-");
          $current_value = '';
        }
        else {
          // This is option value.
          $current_value .= ' ' . $word;
        }
      }
    }
  }

  private function createTextureFromArray($input) {
    if ($input['type'] != 'texture') {
      $this->error = 'Supplied array does not represent a texture.';
      return;
    }

    if (!empty($input['name'])) {
      $this->name = $input['name'];
    }

    if (!empty($input['map_path'])) {
      $this->map_path = $input['map_path'];
    }

    if (!empty($input['blendu'])) {
      $this->blendu = $input['blendu'];
    }

    if (!empty($input['blendv'])) {
      $this->blendv = $input['blendv'];
    }

    if (!empty($input['bm'])) {
      $this->bm = $input['bm'];
    }

    if (!empty($input['boost'])) {
      $this->boost = $input['boost'];
    }

    if (!empty($input['cc'])) {
      $this->cc = $input['cc'];
    }

    if (!empty($input['clamp'])) {
      $this->clamp = $input['clamp'];
    }

    if (!empty($input['imfchan'])) {
      $this->imfchan = $input['imfchan'];
    }

    if (!empty($input['mm'])) {
      $this->mm = $input['mm'];
    }

    if (!empty($input['o'])) {
      $this->o = $input['o'];
    }

    if (!empty($input['s'])) {
      $this->s = $input['s'];
    }

    if (!empty($input['t'])) {
      $this->t = $input['t'];
    }

    if (!empty($input['texres'])) {
      $this->texres = $input['texres'];
    }

    if (!empty($input['reflectionType'])) {
      $this->reflectionType = $input['reflectionType'];
    }
  }

  public function toJsonObject() {
    return $this->map_path;
  }
}
