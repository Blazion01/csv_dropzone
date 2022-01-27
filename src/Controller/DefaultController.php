<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\CsvFile;
use App\Form\FileUploadType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\FileUploader;
use Psr\Log\LoggerInterface;

class DefaultController extends AbstractController
{

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    
    /**
     * @var \Doctrine\Persistence\ObjectRepository
     */
    private $csvFileRepository;

    public function __construct(ParameterBagInterface $parameterBag, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->csvFileRepository = $entityManager->getRepository('App:CsvFile');
        $this->parameterBag = $parameterBag;
    }

    #[Route('/test', name: 'index')]
    public function index(Request $request): Response
    {
        $file = new CsvFile();

        $form = $this->createForm(FileUploadType::class, $file, ['method'=>'POST']);
        //$form->handleRequest($request);

        if ($request->getMethod() === Request::METHOD_POST) {
            $file = $request->files->get("file_upload")["csvFile"];
            if($file != null) {
                $size = filesize($file);
                $size = round($size / 1024 / 1024, 1);
                $name = $file->getClientOriginalName();
                $updatedAt = date("H:i:s d-m-Y");
                $array = [$name, $size, $updatedAt];
                echo "<pre>".print_r($file)."</pre>";
                $this->entityManager->persist($file);
                $this->entityManager->flush($file);
                //$this->addFlash('success', 'file uploaded');
                //$this->redirectToRoute('home');
            }
            dd($request);
        }

//        if ($form->isSubmitted() && $form->isValid()) {
//            echo "<pre>".print_r($_FILES)."<br>".print_r($_POST)."</pre>";
//            $fileNaam = basename($_FILES["csv_upload"]["name"]["csvFile"]["file"]);
//            $fileType = pathinfo($fileNaam,PATHINFO_EXTENSION);
//            if($fileType != "csv") {
//              $this->addFlash('info', 'File must be of type: \'.csv\'');
//              return $this->redirectToRoute('index');
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
        dd($request->query->all());
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

                $returnResponse(null, `$targetPath$fileId.$fileType`, "final return");
            } else {
                $returnResponse(null, null, "chunksending not reached");
            }
        }
    }

    #[Route('/', name: 'index2')]
    public function test(Request $request) {
        $token = $request->get("token");
        if($token == null) {echo "token is empty";}
        $dump = $request;
        return $this->render('base.html.twig', [
            'dump' => $dump,
        ]);
    }

    #[Route('/doUpload', name: 'upload')]
    public function doUpload(Request $request, string $uploadDir, FileUploader $uploader, LoggerInterface $logger)
    {
        $token = $request->get("token");

        if($token == null) {echo "token is empty";}

        if (!$this->isCsrfTokenValid('upload', $token)) {
            echo $token;
            //dd($request);
            $logger->info("CSRF failure");

            $response = new Response("Operation not allowed", Response::HTTP_BAD_REQUEST,
                ['content-type' => 'text/plain']);
            return $this->render('base.html.twig', [
                'dump' => $response,
            ]);
        }

        $file = $request->files->get('myfile');

        if (empty($file)) {
            return new Response("No file specified",
                Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
        }

        $filename = $file->getClientOriginalName();
        $uploader->upload($uploadDir, $file, $filename);


        $content = utf8_encode(file_get_contents($uploadDir.'/'.$filename));  // load with UTF8

        //content bewerken
        dd($content);

        return $this->render('default/index.html.twig', [
            'filename' => $filename,
            'jsonstring' => $content,
            'upload_dir' => $uploadDir,
        ]);
    }
}
