<?php
declare(strict_types=1);

namespace RefactorPhp\Processor;

use LogicException;
use PhpParser\BuilderFactory;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use RefactorPhp\ClassBuilder;
use RefactorPhp\ClassDescription;
use RefactorPhp\ClassMerger;
use RefactorPhp\Filesystem as RefactorPhpFilesystem;
use RefactorPhp\Manifest\FindAndReplaceInterface;
use RefactorPhp\Manifest\FindInterface;
use RefactorPhp\Manifest\ManifestInterface;
use RefactorPhp\Manifest\ManifestResolver;
use RefactorPhp\Manifest\MergeClassInterface;
use RefactorPhp\Node\NodeParser;
use Symfony\Component\Filesystem\Filesystem;

class ProcessorFactory
{
    /**
     * Processor binding to interfaces.
     */
    const PROCESSORS = [
        FindInterface::class            => FindProcessor::class,
        FindAndReplaceInterface::class  => FindAndReplaceProcessor::class,
        MergeClassInterface::class      => MergeClassProcessor::class,
    ];

    /**
     * @var ManifestResolver
     */
    private $resolver;

    /**
     * @param ManifestInterface $manifest
     * @return ProcessorInterface
     */
    public function create(ManifestInterface $manifest): ProcessorInterface
    {
        $this->resolver = new ManifestResolver($manifest);
        $interface = $this->resolver->getManifestInterface();

        if (array_key_exists($interface, self::PROCESSORS)) {
            $processor = self::PROCESSORS[$interface];
            switch ($processor) {
                case FindProcessor::class:
                    return $this->createFindProcessor();
                    break;
                case FindAndReplaceProcessor::class:
                    return $this->createFindAndReplaceProcessor();
                    break;
                case MergeClassProcessor::class:
                    return $this->createMergeClassProcessor();
                    break;
                default:
                    throw new LogicException(
                        "Processor $processor is not implemented."
                    );
            }
        } else {
            throw new LogicException(
                "Unsupported interface: $interface."
            );
        }
    }

    /**
     * @return FindAndReplaceProcessor
     */
    private function createFindAndReplaceProcessor(): FindAndReplaceProcessor
    {
        return new FindAndReplaceProcessor(
            new NodeParser(
                (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
                new NodeTraverser()
            ),
            $this->resolver->getFinder(),
            $this->resolver->getManifest(),
            new RefactorPhpFilesystem(
                new Filesystem(),
                new Standard()
            )
        );
    }

    /**
     * @return FindProcessor
     */
    private function createFindProcessor(): FindProcessor
    {
        return new FindProcessor(
            new NodeParser(
                (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
                new NodeTraverser()
            ),
            $this->resolver->getFinder(),
            $this->resolver->getManifest()
        );
    }

    /**
     * @return MergeClassProcessor
     */
    private function createMergeClassProcessor(): MergeClassProcessor
    {
        $parser = new NodeParser(
            (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
            new NodeTraverser()
        );

        return new MergeClassProcessor(
            $parser,
            $this->resolver->getManifest(),
            new RefactorPhpFilesystem(
                new Filesystem(),
                new Standard()
            ),
            new ClassBuilder(
                new BuilderFactory()
            ),
            new ClassMerger($parser)
        );
    }
}
