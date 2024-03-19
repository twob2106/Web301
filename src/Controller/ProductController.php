<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Form\ProductType;
use App\Entity\Order;
use App\Entity\OrderMap;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Security;

class ProductController extends AbstractController
{
    private $security;
    private $session;

    public function __construct(Security $security, SessionInterface $session)
    {
        $this->security = $security;
        $this->session = $session;
    }

    /**
     * @Route("/product/home",name="product_home")
     */
    public function homeAction()
    {
        $em = $this->getDoctrine()->getManager();

        $product = $em->getRepository(Product::class)->findAll();

        return $this->render('product/index.html.twig', array(
            'product' => $product,
        ));
    }

    /**
     * @Route("/product/admin",name="product_admin")
     */
    public function adminAction()
    {
        $em = $this->getDoctrine()->getManager();

        $product = $em->getRepository(Product::class)->findAll();

        return $this->render('product/admin.html.twig', array(
            'product' => $product,
        ));
    }

    /**
     * @Route("/product/all", name="product_list")
     */
    public function listAction()
    {
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findAll();

        return $this->render('product/allproduct.html.twig', [
            'product' => $product
        ]);
    }

    /**
     * @Route("/product/details/{id}", methods="GET", name="product_details")
     */
    public function detailsAction($id): Response
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();
        $form   = $this->createFormBuilder([
            'action' => $this->generateUrl('add_to_cart'),
            'method' => 'GET'
        ])
            ->getForm();

        return $this->render('product/details.html.twig', [
            'form'     =>  $form->createView(),
            'product'  => $product,
            'orders'   => $orders,
            'cart'     => $this->session->get('cart_item')
        ]);
    }


    /**
     * @Route("/product/delete/{id}", name="product_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository(Product::class)->find($id);
        $em->remove($product);
        $em->flush();
        
        $this->addFlash(
            'error',
            'Product deleted'
        );
        
        return $this->redirectToRoute('product_list');
    }

    /**
     * @Route("/product/edit/{id}", name="product_edit")
     */
    public function editAction($id, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $fileName = md5(uniqid()).'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('image_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    $this->logger->error('Failed to upload image: '.$e->getMessage());
                }
                $product->setImage($fileName);
            }
            $entityManager->flush();

            $this->addFlash(
                'notice',
                'Product Edited'
            );
            return $this->redirectToRoute('product_admin');
        }
        return $this->render('product/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * Creates a new product entity.
     *
     * @Route("/product/new", methods={"GET", "POST"}, name="product_new")
     */
    public function newAction(Request $request)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $fileName = md5(uniqid()).'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('image_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    $this->logger->error('Failed to upload image: '.$e->getMessage());
                }
                $product->setImage($fileName);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('product_admin');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/", methods="GET", name="product")
     */
    public function index(): Response
    {
        $products = $this->getDoctrine()->getRepository(Product::class)
            ->findAll();

        $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();
        $form   = $this->createFormBuilder([
            'action' => $this->generateUrl('add_to_cart'),
            'method' => 'GET'
        ])
            ->getForm();

        return $this->render('product/index.html.twig', [
            'form'     =>  $form->createView(),
            'products' => $products,
            'orders'   => $orders,
            'cart'     => $this->session->get('cart_item')
        ]);
    }

    /**
     * @Route("/product/cart", methods="GET", name="product_cart")
     */
    public function indexCart(): Response
    {
        $products = $this->getDoctrine()->getRepository(Product::class)
            ->findAll();

        $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();
        $form   = $this->createFormBuilder([
            'action' => $this->generateUrl('add_to_cart'),
            'method' => 'GET'
        ])
            ->getForm();

        return $this->render('product/cart.html.twig', [
            'form'     =>  $form->createView(),
            'products' => $products,
            'orders'   => $orders,
            'cart'     => $this->session->get('cart_item')
        ]);
    }

    /**
     * @Route("/product/addToCart", methods="POST", name="add_to_cart")
     */
    public function addToCart(Request $request): Response
    {
        if (!$this->security->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $userId = $request->get('id');
        $cart      = $this->session->get('cart_item');
        $quantity  = $request->get('quantity');
        $productId = $request->get('id');
        $prod      = $this->getDoctrine()->getRepository(Product::class)
            ->find($productId);

        if (isset($cart[$productId])) {
            $quantity = $cart[$productId]['quantity'] + $quantity;
            $cart[$productId]['quantity']   = $quantity;
            $cart[$productId]['item_total'] = $prod->getPrice() * $quantity;
            $cart[$productId]['user_id'] = $userId; 
        } else {
            $cart[$productId]['product_id']    = $productId;
            $cart[$productId]['product_name']  = $prod->getName();
            $cart[$productId]['quantity']      = $quantity;
            $cart[$productId]['unit_price']    = $prod->getPrice();
            $cart[$productId]['item_total']    = $prod->getPrice() * $quantity;
        }
        $this->session->set('cart_item', $cart);

        return $this->redirectToRoute('product');
    }

    /**
     * @Route("/product/checkout", methods="GET", name="checkout")
     */
    public function checkout(Request $request): Response
    {
        $cartItems = $this->session->get('cart_item');
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getConnection()->beginTransaction();
        try {
            if (!empty($cartItems)) {
                $order = new Order();
                $order->setStatus("A");
                $order->setDate(new DateTime());
                $user = $this->getUser();
                if ($user === null) {
                    throw new Exception("Bạn cần đăng nhập để thanh toán");
                }
                $order->setUser($user);
                $entityManager->persist($order);
                foreach ($cartItems as $key => $cartItem) {
                    $orderMap = new OrderMap();
                    $product = $this->getDoctrine()->getRepository(Product::class)
                        ->find($cartItem['product_id']);
                    $orderMap->setProduct($product);
                    $orderMap->setOrder($order);
                    $orderMap->setQuantity($cartItem['quantity']);

                    $entityManager->persist($orderMap);
                }
                $entityManager->flush();
                $entityManager->getConnection()->commit();
                $this->session->remove('cart_item');
            }
        } catch (Exception $e) {
            $entityManager->getConnection()->rollBack();
            throw $e;
        }
        return $this->redirectToRoute('product');
    }

    /**
     * @Route("/product/deleteCartItem/{id}/{action}", methods="GET", name="delete_item")
     */
    public function deleteCartItem(Request $request): Response
    {
        $action = $request->get('action');
        $id     = $request->get('id');
        $cart   = $this->session->get('cart_item');

        if ($action === 'delete_item') {
            unset($cart[$id]);

            $this->session->set('cart_item', $cart);
            return $this->json(['isDelete' => true]);
        } else {
            $this->session->remove('cart_item');
            return $this->redirectToRoute('product');
        }
    }

/**
 * @Route("/order", name="order_list")
 */
public function orderAction()
{
    $orderMaps = $this->getDoctrine()
        ->getRepository(OrderMap::class)
        ->findAll();

    return $this->render('product/order.html.twig', [
        'orderMaps' => $orderMaps
    ]);
}

}

