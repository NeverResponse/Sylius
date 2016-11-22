<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\AdminBundle\Controller;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository;
use Sylius\Bundle\CoreBundle\Generator\InvoiceRendererInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @author Jan Góralski <jan.goralski@lakion.com>
 */
class InvoiceController
{
    /**
     * @var InvoiceRendererInterface
     */
    protected $invoiceRenderer;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @param InvoiceRendererInterface $invoiceRenderer
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        InvoiceRendererInterface $invoiceRenderer,
        OrderRepository $orderRepository
    ) {
        $this->invoiceRenderer = $invoiceRenderer;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Request $request
     * @param mixed $orderId
     *
     * @return Response
     *
     * @throws HttpException
     */
    public function generateInvoiceAction(Request $request, $orderId)
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->find($orderId);
        if (null === $order) {
            throw new HttpException(Response::HTTP_NOT_FOUND);
        }
        if ($order->getPaymentState() !== OrderPaymentStates::STATE_PAID) {
            throw new HttpException(Response::HTTP_BAD_REQUEST);
        }

        $invoice = $this->invoiceRenderer->render(
            '@SyliusAdmin/Order/invoice.html.twig',
            [
                'order' => $order,
                'request' => $request,
            ]
        );

        return new Response(
            $invoice,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf(
                    'attachment; filename="%s_order_no_%s.pdf"',
                    $order->getCustomer()->getEmail(),
                    $order->getNumber()
                ),
            ]
        );
    }
}
