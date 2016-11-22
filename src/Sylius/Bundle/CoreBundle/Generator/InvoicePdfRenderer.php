<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CoreBundle\Generator;

use Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Jan Góralski <jan.goralski@lakion.com>
 */
class InvoicePdfRenderer implements InvoiceRendererInterface
{
    /**
     * @var Pdf
     */
    protected $pdfGenerator;

    /**
     * @var EngineInterface
     */
    protected $templatingEngine;

    /**
     * @param LoggableGenerator $generator
     * @param EngineInterface $templatingEngine
     */
    public function __construct(LoggableGenerator $generator, EngineInterface $templatingEngine)
    {
        $this->pdfGenerator = $generator->getInternalGenerator();
        $this->pdfGenerator->setTimeout(30);
        $this->templatingEngine = $templatingEngine;
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, array $parameters)
    {
        $invoiceResponse = $this->templatingEngine->renderResponse($template, $parameters);

        return $this->generateInvoice($invoiceResponse);
    }

    /**
     * @param Response $invoiceResponse
     *
     * @return string
     */
    private function generateInvoice(Response $invoiceResponse)
    {
        $$this->pdfGenerator->getOutputFromHtml($invoiceResponse);
    }
}
