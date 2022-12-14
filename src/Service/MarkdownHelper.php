<?php

namespace App\Service;

use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\CacheInterface;

class MarkdownHelper
{
    private MarkdownParserInterface $markdownParser;
    private CacheInterface          $cache;
    private bool                    $isDebug;
    private LoggerInterface         $logger;
    private Security                $security;

    public function __construct(
        MarkdownParserInterface $markdownParser,
        Security $security,
        CacheInterface $cache,
        bool $isDebug,
        LoggerInterface $mdLogger
    ) {
        $this->markdownParser = $markdownParser;
        $this->security = $security;
        $this->cache = $cache;
        $this->isDebug = $isDebug;
        $this->logger = $mdLogger;
    }

    public function parse(string $source): string
    {
        if (stripos($source, 'cat') !== false) {
            $this->logger->info('Meow!');
        }
        // check if the user is logged in
        /*if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // ...
        }*/
        if ($this->security->getUser()) {
            $this->logger->info('Rendering markdown for {user}', [
                'user' => $this->security->getUser()->getUserIdentifier()
            ]);
        }

        if ($this->isDebug) {
            return $this->markdownParser->transformMarkdown($source);
        }

        return $this->cache->get('markdown_' . md5($source), function () use ($source) {
            return $this->markdownParser->transformMarkdown($source);
        });
    }
}
