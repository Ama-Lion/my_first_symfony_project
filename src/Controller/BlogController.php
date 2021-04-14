<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Form\CommentType;

use App\Form\CategoryType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo): Response
    {
        $article = $repo->findAll();
        return $this->render('blog/index.html.twig', [
            'articles' => $article,
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home(){
        return $this->render('blog/home.html.twig');
    }

    /**
     * @Route("/blog/new", name="blog_create")
     */
    public function create(Article $article = null, Request $request, EntityManagerInterface $manager){

        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);

        // hamdle my form request
        $form->handleRequest($request);

        // check if form is submitted
        if ($form->isSubmitted() && $form->isValid()) {

            // if item dosen't has certain option do this else do nothing
            if(!$article->getId()){
                $article->setCreatedAt(new \DateTime());
            }
            $article->setCreatedAt(new \DateTime());
            $article->setOwner($this->getUser());

            // presist and flush item
            $manager->persist($article);
            $manager->flush();

            // redirect user to the route u want
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null
        ]);
    }
    /**
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function articleForm(Article $article = null, Request $request, EntityManagerInterface $manager){

        // check if there is an item if not the create a new item
        if(!$article){
            $article = new Article();
        } 

        $this->denyAccessUnlessGranted('CAN_EDIT', $article);
        // dd($article->getOwner());

        $form = $this->createForm(ArticleType::class, $article);

        // hamdle my form request
        $form->handleRequest($request);

        // check if form is submitted
        if ($form->isSubmitted() && $form->isValid()) {

            // if item dosen't has certain option do this else do nothing
            if(!$article->getId()){
                $article->setCreatedAt(new \DateTime());
            }
            $article->setCreatedAt(new \DateTime());
            $article->setOwner($this->getUser());

            // presist and flush item
            $manager->persist($article);
            $manager->flush();

            // redirect user to the route u want
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null
        ]);
    }


    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show( Article $article, Request $request, EntityManagerInterface $manager, Security $security){

        // check if there is an item if not the create a new item
        if(!$article){
            $article = new Article();
        } 

                
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        // hamdle my form request
        $form->handleRequest($request);

        // check if form is submitted
        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setCreatedAt(new \DateTime());
            $comment->setArticle($article);

            // presist and flush item
            $manager->persist($comment);
            $manager->flush();

            // redirect user to the route u want
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }
        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'formComment' => $form->createView(),
            'owner' => $article->getOwner()
        ]);
    }

    /**
    * @Route("admin/{id}/delete", name="delete")
    * @param int $id
    * @return Response
    */
    public function delete(int $id): Response {
    $article = $this->getDoctrine()
        ->getRepository(Article::class)
        ->find($id);

    $manager = $this->getDoctrine()->getManager();

    foreach ($article->getComments() as $comment) {
        $manager->remove($comment);
    }

    $manager->remove($article);
    $manager->flush();

    $this->addFlash('deleteArticle', 'L\'article a bien étais supprimer');

    return $this->redirectToRoute('blog');
    }

    /**
    * @Route("admin/create-category", name="create-category")
    */
    public function createCategory(Category $category = null, Request $request,  EntityManagerInterface $manager, CategoryRepository $repo){
        $category = new Category();
        $categories = $repo->findAll();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($category);
            $manager->flush();
            return $this->redirectToRoute('create-category');
        }
        return $this->render('blog/create_category.html.twig', [
            'formCategory' => $form->createView(),
            'categories' => $categories,
        ]);
    }
    
}

