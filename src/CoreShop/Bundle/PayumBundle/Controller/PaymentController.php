<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2017 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
*/

namespace CoreShop\Bundle\PayumBundle\Controller;

use Carbon\Carbon;
use CoreShop\Bundle\PayumBundle\Request\GetStatus;
use CoreShop\Bundle\PayumBundle\Request\ResolveNextRoute;
use CoreShop\Component\Currency\Context\CurrencyContextInterface;
use CoreShop\Component\Order\Model\OrderInterface;
use CoreShop\Component\Payment\Model\PaymentInterface;
use CoreShop\Component\Resource\Factory\FactoryInterface;
use CoreShop\Component\Resource\Pimcore\ObjectServiceInterface;
use CoreShop\Component\Resource\Repository\PimcoreRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Payum;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentController extends Controller
{
    /**
     * @var FactoryInterface
     */
    private $paymentFactory;

    /**
     * @var PimcoreRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ObjectServiceInterface
     */
    private $pimcoreObjectService;

    /**
     * @var CurrencyContextInterface
     */
    private $currencyContext;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * PaymentController constructor.
     *
     * @param FactoryInterface           $paymentFactory
     * @param PimcoreRepositoryInterface $orderRepository
     * @param ObjectServiceInterface     $pimcoreObjectService
     * @param CurrencyContextInterface   $currencyContext
     * @param EntityManagerInterface     $entityManager
     */
    public function __construct(
        FactoryInterface $paymentFactory,
        PimcoreRepositoryInterface $orderRepository,
        ObjectServiceInterface $pimcoreObjectService,
        CurrencyContextInterface $currencyContext,
        EntityManagerInterface $entityManager
    ) {
        $this->paymentFactory = $paymentFactory;
        $this->orderRepository = $orderRepository;
        $this->pimcoreObjectService = $pimcoreObjectService;
        $this->currencyContext = $currencyContext;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function prepareCaptureAction(Request $request)
    {
        /**
         * @var $order OrderInterface
         */
        $orderId = $request->get('order');
        $order = $this->orderRepository->find($orderId);

        if (null === $order) {
            throw new NotFoundHttpException(sprintf('Order with id "%s" does not exist.', $orderId));
        }

        /**
         * Create Payum Payment.
         * @todo: transfer this to a payum capture action?
         *
         * @var $payment PaymentInterface
         */
        $payment = $this->paymentFactory->createNew();
        $payment->setNumber($order->getOrderNumber());
        $payment->setPaymentProvider($order->getPaymentProvider());
        $payment->setTotalAmount($order->getTotal());
        $payment->setState(PaymentInterface::STATE_NEW);
        $payment->setDatePayment(Carbon::now());
        $payment->setOrderId($order->getId());
        $payment->setCurrency($this->currencyContext->getCurrency());

        $description = sprintf(
            'Payment contains %s item(s) for a total of %s.',
            count($order->getItems()),
            round($order->getTotal() / 100, 2)
        );

        //payum setters
        $payment->setCurrencyCode($this->currencyContext->getCurrency()->getIsoCode());
        $payment->setDescription($description);

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $request->getSession()->set('coreshop_order_id', $order->getId());

        $storage = $this->getPayum()->getStorage($payment);
        $storage->update($payment);

        $captureToken = $this->getPayum()->getTokenFactory()->createCaptureToken(
            $payment->getPaymentProvider()->getGatewayConfig()->getGatewayName(),
            $payment,
            'coreshop_payment_after',
            []
        );

        return $this->redirect($captureToken->getTargetUrl());
    }

    /**
     * Here we return from the Payment Provider and process the result.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     * @throws \Payum\Core\Reply\ReplyInterface
     */
    public function afterCaptureAction(Request $request)
    {
        $token = $this->getPayum()->getHttpRequestVerifier()->verify($request);

        $status = new GetStatus($token);
        $this->getPayum()->getGateway($token->getGatewayName())->execute($status);
        $resolveNextRoute = new ResolveNextRoute($status->getFirstModel());
        $this->getPayum()->getGateway($token->getGatewayName())->execute($resolveNextRoute);
        $this->getPayum()->getHttpRequestVerifier()->invalidate($token);

        //if (PaymentInterface::STATE_NEW !== $status->getValue()) {
        //    $request->getSession()->getBag('flashes')->add('info', sprintf('payment.%s', $status->getValue()));
        //}

        //Start Workflow with $status->getStatus()

        /**
         * Further process the status here, kick-off the pimcore workflow for orders?
        */
        return $this->redirectToRoute($resolveNextRoute->getRouteName(), $resolveNextRoute->getRouteParameters());
    }

    /**
     * @return Payum
     */
    protected function getPayum()
    {
        return $this->get('payum');
    }
}
