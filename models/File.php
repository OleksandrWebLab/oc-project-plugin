<?php namespace Bronx\Project\Models;

use System\Models\File as FileBase;

class File extends FileBase
{
    public $table = 'bronx_project_tab_file';

    protected $hidden = [
        'disk_name',
        'file_name',
        'file_size',
        'content_type',
        'title',
        'description',
        'field',
        'attachment_id',
        'attachment_type',
        'is_public',
        'sort_order',
        'extension',
    ];

    protected function getDefaultThumbOptions($overrideOptions = [])
    {
        $defaultOptions = [
            'mode'      => 'auto',
            'offset'    => [0, 0],
            'quality'   => 80,
            'sharpen'   => 0,
            'interlace' => false,
            'extension' => 'auto',
        ];

        if (!is_array($overrideOptions)) {
            $overrideOptions = ['mode' => $overrideOptions];
        }

        $options = array_merge($defaultOptions, $overrideOptions);

        $options['mode'] = strtolower($options['mode']);

        if ((strtolower($options['extension'])) == 'auto') {
            $options['extension'] = strtolower($this->getExtension());
        }

        return $options;
    }

    public function getWidthAttribute()
    {
        if ($this->isImage()) {
            return getimagesize($this->getLocalPath())[0];
        }
    }

    public function getHeightAttribute()
    {
        if ($this->isImage()) {
            return getimagesize($this->getLocalPath())[1];
        }
    }
}