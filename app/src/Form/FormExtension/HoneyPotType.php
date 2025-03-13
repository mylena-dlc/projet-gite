<?php

namespace App\Form\FormExtension;

use Psr\Log\LoggerInterface;
use Symfony\Component\Form\AbstractType;
use App\EventSubscriber\HoneyPotSubscriber;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class HoneyPotType extends AbstractType
{

    private LoggerInterface $honeyPotLogger;
    private RequestStack $requestStack;

    public function __construct(
        LoggerInterface $honeyPotLogger,
        RequestStack $requestStack
    )
    {
        $this->honeyPotLogger = $honeyPotLogger;
        $this->requestStack = $requestStack;
    }

    protected const DELICIOUS_HONEY_CANDY_FOR_BOT = "numberPhone";
    protected const FABULOUS_HONEY_CANDY_FOR_BOT = "numberFax";

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->add(self::DELICIOUS_HONEY_CANDY_FOR_BOT, TextType::class, $this->setHoneyFieldConfiguration())
                ->add(self::FABULOUS_HONEY_CANDY_FOR_BOT, TextType::class, $this->setHoneyFieldConfiguration())
                ->addEventSubscriber(new HoneyPotSubscriber($this->honeyPotLogger, $this->requestStack ));

    }
    protected function setHoneyFieldConfiguration(): array
    {
        return [
            'attr' => [
                'autocomplete' => 'off',
                'tabindex' => '-1',
                'class' => 'sweet-candy',
            ],
            //'data' => 'fake data', // Attention, supprimer cette ligne après les tests
            'mapped' => false,
            'required' => false,
            'label' => false,
        ];
    }
    

}