<?php

namespace CbxPetitionScoped\Intervention\Image\Gd\Commands;

use CbxPetitionScoped\Intervention\Image\Commands\AbstractCommand;
use CbxPetitionScoped\Intervention\Image\Size;
class GetSizeCommand extends AbstractCommand
{
    /**
     * Reads size of given image instance in pixels
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        $this->setOutput(new Size(\imagesx($image->getCore()), \imagesy($image->getCore())));
        return \true;
    }
}
