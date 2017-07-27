<?php
declare(strict_types=1);

namespace RefactorPhp\Processor;

use RefactorPhp\Finder;
use RefactorPhp\Node\NodeParser;
use RefactorPhp\Filesystem;

/**
 * Class RefactorProcessor.
 */
class FindAndReplaceProcessor extends AbstractProcessor
{
    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * FindAndReplaceProcessor constructor.
     * @param Finder $finder
     * @param NodeParser $parser
     * @param Filesystem $fs
     */
    public function __construct(Finder $finder, NodeParser $parser, Filesystem $fs)
    {
        parent::__construct($finder, $parser);

        $this->fs = $fs;
    }
}
