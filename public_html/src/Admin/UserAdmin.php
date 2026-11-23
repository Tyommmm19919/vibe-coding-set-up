<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserAdmin extends AbstractAdmin
{
    private UserPasswordHasherInterface $passwordHasher;

    public function setPasswordHasher(UserPasswordHasherInterface $passwordHasher): void
    {
        $this->passwordHasher = $passwordHasher;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('name')->add('email')->add('roles');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add('name')
            ->add('email')
            ->add('roles')
            ->add('createdAt')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => ['edit' => [], 'delete' => []],
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name')
            ->add('email')
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                    'Super admin' => 'ROLE_SUPER_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('plainPassword', PasswordType::class, [
                'required' => $this->isCurrentRoute('create'),
                'help' => 'Leave blank while editing to keep the current password.',
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('id')->add('name')->add('email')->add('roles')->add('createdAt')->add('updatedAt');
    }

    protected function prePersist(object $object): void
    {
        if ($object instanceof User) $this->hashPlainPassword($object);
    }

    protected function preUpdate(object $object): void
    {
        if ($object instanceof User) $this->hashPlainPassword($object);
    }

    private function hashPlainPassword(User $user): void
    {
        $plain = $user->getPlainPassword();
        if ($plain === null || $plain === '') return;

        $user->setPassword($this->passwordHasher->hashPassword($user, $plain));
        $user->setPlainPassword(null);
    }
}
