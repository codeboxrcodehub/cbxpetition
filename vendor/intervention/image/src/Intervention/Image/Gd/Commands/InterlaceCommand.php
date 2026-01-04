<?php

namespace CbxPetitionScoped\Intervention\Image\Gd\Commands;

use CbxPetitionScoped\Intervention\Image\Commands\AbstractCommand;
class InterlaceCommand extends AbstractCommand
{
    /**
     * Toggles interlaced encoding mode
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        $mode = $this->argument(0)->type('bool')->value(\true);
        \imageinterlace($image->getCore(), $mode);
        return \true;
    }
}
