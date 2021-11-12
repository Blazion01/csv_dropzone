<?php

namespace App\Form;

use App\Entity\CsvFile;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
// use Symfony\UX\Dropzone\Form\DropzoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('csvFile', DropzoneType::class, [
                'required' => true,
                'allow_delete' => true,
                'delete_label' => 'Remove File',
                'download_uri' => true,
                'download_label' => true,
                'asset_helper' => true,
                'mapped' => false,
            ])
            ->add(
                'submit',
                SubmitType::class,
                [
                    'attr' => ['class' => 'form-control btn-primary pull-right'],
                    'label' => 'Upload File'
                ]
            )
            ->getForm()
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CsvFile::class,
        ]);
    }
}
