<?php declare(strict_types=1);

namespace Shopware\StorefrontApi\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopware\Api\Entity\Search\SearchCriteriaBuilder;
use Shopware\Api\Product\Definition\ProductDefinition;
use Shopware\Context\Struct\StorefrontContext;
use Shopware\Product\Exception\ProductNotFoundException;
use Shopware\Rest\Context\RestContext;
use Shopware\Rest\Response\ResponseFactory;
use Shopware\StorefrontApi\Product\StorefrontProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * @var \Shopware\StorefrontApi\Product\StorefrontProductRepository
     */
    private $repository;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    public function __construct(
        StorefrontProductRepository $repository,
        ResponseFactory $responseFactory,
        SearchCriteriaBuilder $criteriaBuilder
    ) {
        $this->repository = $repository;
        $this->responseFactory = $responseFactory;
        $this->criteriaBuilder = $criteriaBuilder;
    }

    /**
     * @Route("/storefront-api/product", name="storefront.api.product.list")
     *
     * @param Request $request
     * @param StorefrontContext $context
     *
     * @return Response
     */
    public function listAction(Request $request, StorefrontContext $context): Response
    {
        $criteria = $this->criteriaBuilder->handleRequest($request, ProductDefinition::class, $context->getShopContext());

        $result = $this->repository->search($criteria, $context);

        return $this->responseFactory->createListingResponse(
            $result,
            ProductDefinition::class,
            new RestContext($request, $context->getShopContext(), null)
        );
    }

    /**
     * @Route("/storefront-api/product/{productId}", name="storefront.api.product.detail")
     * @Method({"GET"})
     *
     * @param string $productId
     * @param StorefrontContext $context
     *
     * @param Request $request
     * @return Response
     *
     * @throws ProductNotFoundException
     */
    public function detailAction(string $productId, StorefrontContext $context, Request $request): Response
    {
        $products = $this->repository->readDetail([$productId], $context);
        if (!$products->has($productId)) {
            throw new ProductNotFoundException($productId);
        }

        return $this->responseFactory->createDetailResponse(
            $products->get($productId),
            ProductDefinition::class,
            new RestContext($request, $context->getShopContext(), null)
        );
    }
}
