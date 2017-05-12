<?php

namespace Bearns\Pjax;

use think\Request;
use think\Response;
use think\response\Redirect;
use think\exception\HttpException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Filter if pjax
 * Class FilterIfPjax
 * @package app\common\behavior
 */
class FilterIfPjax
{
    /**
     * The DomCrawler instance.
     *
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected $crawler;

    /**
     * Handle an incoming request.
     *
     * @param Response $response
     *
     * @return string
     */
    public function appEnd(&$response)
    {
        $request = Request::instance();

        if (!$request->isPjax() || $response instanceof Redirect) {
            return $response;
        }

        $this->filterResponse($response, $request->header('X-PJAX-Container'))
            ->setUriHeader($response, $request)
            ->setVersionHeader($response, $request);

        return $response;
    }

    /**
     * Filter content
     *
     * @param Response $response
     * @param $container
     *
     * @return $this
     */
    protected function filterResponse(Response $response, $container)
    {
        $crawler = $this->getCrawler($response);

        $response->content($this->makeTitle($crawler) . $this->fetchContainer($crawler, $container));

        return $this;
    }

    /**
     * Get html page title
     *
     * @param Crawler $crawler
     *
     * @return string
     */
    protected function makeTitle(Crawler $crawler)
    {
        $pageTitle = $crawler->filter('head > title');

        if (!$pageTitle->count()) {
            return '';
        }

        return '<title>' . $pageTitle->html() . '</title>';
    }

    /**
     * @param Crawler $crawler
     * @param $container
     *
     * @return string
     */
    protected function fetchContainer(Crawler $crawler, $container)
    {
        $content = $crawler->filter($container);

        if (!$content->count()) {
            throw new HttpException(422);
        }

        return $content->html();
    }

    /**
     * Set pjax uri header
     *
     * @param Response $response
     * @param Request $request
     *
     * @return $this
     */
    protected function setUriHeader(Response $response, Request $request)
    {
        $response->header('X-PJAX-URL', $request->url(true));

        return $this;
    }

    /**
     * Set pjax version header
     *
     * @param Response $response
     * @param Request $request
     *
     * @return $this
     */
    protected function setVersionHeader(Response $response, Request $request)
    {
        $crawler = $this->getCrawler($this->createResponseWithLowerCaseContent($response));

        $node = $crawler->filter('head > meta[http-equiv="x-pjax-version"]');

        if ($node->count()) {
            $response->header('x-pjax-version', $node->attr('content'));
        }

        return $this;
    }

    /**
     * Get the DomCrawler instance.
     *
     * @param Response $response
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function getCrawler(Response $response)
    {
        if ($this->crawler) {
            return $this->crawler;
        }

        return $this->crawler = new Crawler($response->getData());
    }

    /**
     * Make the content of the given response lowercase.
     *
     * @param Response $response
     *
     * @return Response
     */
    protected function createResponseWithLowerCaseContent(Response $response)
    {
        $content = strtolower($response->getData());

        return Response::create($content);
    }
}