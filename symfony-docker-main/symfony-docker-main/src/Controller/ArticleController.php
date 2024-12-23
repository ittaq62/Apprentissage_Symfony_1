<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;
use DateTimeImmutable;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ArticleController extends AbstractController
{
    #[Route('/article/creer', name: 'app_article_create')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $article->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $article->setDate(new DateTimeImmutable());
            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article créé avec succès !');
            return $this->redirectToRoute('app_article_list');
        }

        return $this->render('article/creer.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article', name: 'app_article_list')]
    public function list(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search', '');

        $queryBuilder = $entityManager->getRepository(Article::class)->createQueryBuilder('a');

        if ($search) {
            $queryBuilder
                ->where('a.titre LIKE :search')
                ->orWhere('a.texte LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $articles = $queryBuilder->getQuery()->getResult();

        return $this->render('article/list.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/article/{id}', name: 'app_article_view')]
    public function view($id, EntityManagerInterface $entityManager): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('L\'article n\'existe pas.');
        }

        return $this->render('article/view.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/article/{id}/edit', name: 'app_article_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit($id, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('L\'article n\'existe pas.');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $article->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Article mis à jour avec succès !');
            return $this->redirectToRoute('app_article_list');
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/{id}/delete', name: 'app_article_delete')]
    #[IsGranted('ROLE_USER')]
    public function delete($id, EntityManagerInterface $entityManager): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('L\'article n\'existe pas.');
        }

        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash('success', 'Article supprimé avec succès.');
        return $this->redirectToRoute('app_article_list');
    }
}
