<?php

namespace App\Form;

use App\Entity\Categoria;
use App\Entity\Produto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProdutoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nome', TextType::class, [
                'label' => 'Nome',
                'attr' => [
                    'placeholder' => 'Ex.: Arroz tipo 1',
                    'maxlength' => 50,
                ],
            ])
            ->add('descricao', TextareaType::class, [
                'label' => 'Descrição',
                'attr' => [
                    'placeholder' => 'Descreva o produto',
                    'rows' => 3,
                ],
            ])
            ->add('valor', NumberType::class, [
                'label' => 'Valor (R$)',
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'min' => 0,
                    'step' => '0.01',
                ],
            ])
            ->add('quantidade', IntegerType::class, [
                'label' => 'Quantidade em estoque',
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('categoria', EntityType::class, [
                'label' => 'Categoria',
                'class' => Categoria::class,
                'choice_label' => 'nome',
                'placeholder' => 'Selecione uma categoria',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produto::class,
        ]);
    }
}
