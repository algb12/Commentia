<?php

// metadata

namespace Commentia\Metadata;

class Metadata
{
    public $metadata_json = JSON_FILE_METADATA;
    public $metadata = array();

    public function __construct()
    {
        $this->metadata = json_decode(file_get_contents($this->metadata_json), true);

        if (empty($this->metadata_json)) {
            exit('Error: No metadata JSON file set.');
        }

        if (!file_exists($this->metadata_json)) {
            file_put_contents($this->metadata_json, '');
        }
    }

    public function getMetadata($data)
    {
        return @$this->metadata[$data] ? @$this->metadata[$data] : "Error: undefined metadata descriptor";
    }

    public function setMetadata($data, $value)
    {
        $this->metadata[$data] = $value;
        file_put_contents("test", "tost.txt");
        $this->updateMetadata();
    }

    public function updateMetadata() {
        if (!is_writable(dirname($this->metadata_json))) {
            exit('Error: Directory not writable.');
        }

        $fp = fopen($this->metadata_json, 'w+');
        flock($fp, LOCK_EX);
        if (flock($fp, LOCK_EX)) {
            fwrite($fp, json_encode($this->metadata));
        }
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
