<?php

namespace App\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class SwaggerDocBuilder
{
    private const CACHE_NAME = 'swagger-doc-builder.yaml';
    /**
     * @var string
     */
    protected $swaggerDir;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $apiVersion;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(
        Environment $twig,
        RouterInterface $router,
        $kernelProjectDir,
        $environment,
        $apiVersion
    ) {
        $this->swaggerDir = realpath($kernelProjectDir . '/config/swagger');
        $this->environment = $environment;
        $this->twig = $twig;
        $this->router = $router;
        $this->apiVersion = $apiVersion;
    }

    public function getDocs(Request $request)
    {
        $cache = new FilesystemAdapter();
        $cachedYaml = $cache->getItem(self::CACHE_NAME);

        if ($this->environment === 'dev' || !$cachedYaml->isHit()) {
            $paths = $this->getSection("paths");
            $definitions = $this->getSection("definitions");

            $swaggerDefinition = $this->getFrontMatter($request);
            $swaggerDefinition['paths'] = $paths;
            $swaggerDefinition['definitions'] = $definitions;

            $yaml = Yaml::dump($swaggerDefinition);

            $cachedYaml->set($yaml);
            $cache->save($cachedYaml);
        }

        return $cachedYaml->get();
    }

    /**
     * Parse a directory and its YAML files and convert them
     * into an array.
     *
     * @param string $dir
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function getSection($dir)
    {
        $finder = new Finder();
        $path = $this->swaggerDir . DIRECTORY_SEPARATOR . $dir;
        $files = $finder->in($path)->files()->name('*.yml')->sortByName();

        $items = [];
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $contents = Yaml::parse($file->getContents());
            if (!is_array($contents)) {
                throw new \Exception(
                    "{$file->getRealPath()} is not valid YAML"
                );
            }

            $items = array_merge($items, $contents);
        }

        return $items;
    }

    /**
     * Get the information swagger loads at the top of the document
     *
     * @param Request $request
     *
     * @return array
     */
    protected function getFrontMatter(Request $request)
    {
        $arr = [];
        $arr['swagger'] = '2.0';
        $arr['info'] = [
            'title' => 'Ilios API Documentation',
            'description' => $this->getDescription(),
            'version' => $this->apiVersion,
        ];

        $arr['host'] = $request->getHttpHost();
        $arr['schemes'] = ['https'];
        $arr['basePath'] = '/api/v1';
        $arr['produces'] = ['application/json'];

        return $arr;
    }

    protected function getDescription()
    {
        $apiDocsUrl = $this->router->generate(
            'ilios_swagger_index',
            [],
            UrlGenerator::ABSOLUTE_URL
        );
        $myprofileUrl = $this->router->generate(
            'ilios_web_assets',
            [],
            UrlGenerator::ABSOLUTE_URL
        );
        $userApiUrl = $this->router->generate(
            'ilios_api_getall',
            ['version' => 'v1', 'object' => 'users'],
            UrlGenerator::ABSOLUTE_URL
        );
        $template = 'swagger/description.markdown.twig';
        return $this->twig->render($template, [
            'apiDocsUrl' => $apiDocsUrl,
            'myprofileUrl' => $myprofileUrl . 'myprofile',
            'userApiUrl' => $userApiUrl,
        ]);
    }
}
