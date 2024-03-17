<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Story extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];

    public function getImage()
    {
        if (empty($this->attributes['image'])) {
            return '';
        }

        if (file_exists(PUBLICPATH . 'uploads/' . $this->attributes['image'])) {
            return base_url('uploads/' . $this->attributes['image']);
        }

        // copy the image from writable/uploads to public/uploads
        if (!empty($this->attributes['image'])) {
            $oldPath = WRITEPATH . 'uploads/' . $this->attributes['image'];
            $newPath = PUBLICPATH . 'uploads/' . $this->attributes['image'];
            copy($oldPath, $newPath);
        }

        return base_url('uploads/' . $this->attributes['image']);
    }
}
