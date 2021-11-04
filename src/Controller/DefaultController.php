<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\CsvFile;
use App\Form\FileUploadType;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'index')]
    #[Route('/home', name: 'home')]
    public function index(Request $request): Response
    {
        $file = new CsvFile();

        $form = $this->createForm(FileUploadType::class, $file);
        $form->handleRequest($request);

//        if ($form->isSubmitted() && $form->isValid()) {
//            echo "<pre>".print_r($_FILES)."</pre>";
//            $fileNaam = basename($_FILES["csv_upload"]["name"]["csvFile"]["file"]);
//            $fileType = pathinfo($fileNaam,PATHINFO_EXTENSION);
//            if($fileType != "csv") {
//              $this->addFlash('info', 'File must be of type: \'.csv\'');
//              return $this->redirectToRoute('file_upload');
//            }
//            $this->entityManager->persist($file);
//            $this->entityManager->flush($file);
//
//            $this->addFlash('success', 'file uploaded');
//            $this->redirectToRoute('home');
//        }

        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/handle-file', name: 'handle-file')]
    public function handleFile(Request $request) 
    {
        if ($request->getMethod() === Request::METHOD_POST) {
            $fileId = $request->request->get('dzuuid');
            $chunkIndex = (int)$request->request->get('dzchunkindex') + 1;
            $chunkIndex = (int)$request->request->get('dztotalchunkcount');

            /** @var UploadedFile $file */
            $file = $request->files->get('file');

            $targetPath = $this->parameterBag->get('kernel.project_dir') . '/assets/media/tmpfolder';
            $fileType = $file->getClientOriginalExtension();
            $filename = vsprintf(s-s.s, [
                $fileId,
                $chunkIndex,
                $fileType,
            ]);
            $targetFile = $targetPath . $filename;

            $returnResponse = function ($info = null, $filelink = null, $status = "error") {
                die (json_encode([
                    "status" => $status,
                    "info" => $info,
                    "file_link" => $filelink,
                ]));
            };

            $file->move(
                $targetPath,
                $filename
            );

            if (!file_exists($targetFile)) {
                $returnResponse("An error occurred and the requested file couldn't be uploaded");
            }

            if ($chunkIndex === $chunkTotal) {
                $file_content = "";
                for ($i = 1; $i <= $chunkTotal; $i++) {
                    $tmp_file_path = realpath(`$targetPath$fileId-$i.$fileType`) or $returnResponse("Your chunk was lost mid-upload");
                    $chunk = file_get_contents($tmp_file_path);
                    if(empty($chunk)) {
                        $returnResponse("Chunks are uploading as empty strings");
                    }
                    $file_content .= $chunk;
                    unlink($tmp_file_path);
                    if(file_exists($tmp_file_path)) {
                        $returnResponse("Your temporary files could not be deleted");
                    }
                }
                file_put_contents(`$targetPath$fileId.$fileType`, $file_content);

                $returnResponse(null, null, "final return");
            } else {
                $returnResponse(null, null, "chunksending not reached");
            }
        }
    }
}
